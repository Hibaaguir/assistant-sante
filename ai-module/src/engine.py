"""
AI health analysis engine.

Pipeline (3 steps):
  1. Summarize raw DB data into simple averages
  2. Pre-compute rule-based checks (prevents LLM contradictions)
  3. Two Groq calls: domain analyses → aggregation
"""

from __future__ import annotations
import json, os, statistics
from decimal import Decimal
from datetime import date as Date
from groq import Groq

MODEL = "llama-3.3-70b-versatile"
_client = None


def _get_client() -> Groq:
    global _client
    if _client is None:
        api_key = os.environ.get("GROQ_API_KEY")
        if not api_key:
            raise EnvironmentError("GROQ_API_KEY not set in ai-module/.env")
        _client = Groq(api_key=api_key)
    return _client


def _avg(vals: list) -> float | None:
    nums = [float(v) for v in vals if v is not None]
    return round(statistics.mean(nums), 2) if nums else None


def _dumps(obj) -> str:
    """json.dumps with Decimal support (MySQL returns Decimal for SUM/numeric fields)."""
    return json.dumps(obj, ensure_ascii=False, indent=2,
                      default=lambda x: float(x) if isinstance(x, Decimal) else str(x))


def _parse_json(text: str) -> dict | list:
    """Extract and parse JSON even if the LLM adds text around it."""
    text = text.strip()
    # Try to find JSON boundaries robustly
    for start_char, end_char in [('{', '}'), ('[', ']')]:
        start = text.find(start_char)
        end   = text.rfind(end_char)
        if start != -1 and end != -1 and end > start:
            try:
                return json.loads(text[start:end + 1])
            except json.JSONDecodeError:
                continue
    raise ValueError(f"No valid JSON found in LLM response: {text[:300]}")


# ── Step 1: Summarize raw DB data ──────────────────────────────

def _summarize(raw: dict) -> dict:
    p = raw.get("user_profile", {})
    h, w = p.get("height"), p.get("current_weight")
    bmi = round(w / (h / 100) ** 2, 1) if h and w and h > 0 else None

    # Active days per week (exact same logic as before)
    acts = raw.get("activity", [])
    act_dates = sorted({str(r["entry_date"]) for r in acts if r.get("entry_date")})
    if len(act_dates) >= 2:
        span = max((Date.fromisoformat(act_dates[-1]) - Date.fromisoformat(act_dates[0])).days + 1, 1)
    else:
        span = max(len(act_dates), 1)
    active_days_per_week = round(len(act_dates) / span * 7, 1)

    sleep  = raw.get("sleep", [])
    nut    = raw.get("nutrition", [])
    vitals = raw.get("vital_signs", [])
    labs   = raw.get("lab_results", [])
    treats = raw.get("treatments", [])
    smk    = raw.get("smoking", [])
    alc    = raw.get("alcohol", [])

    return {
        "user": {
            "age":              p.get("age"),
            "gender":           p.get("gender"),
            "bmi":              bmi,
            "chronic_diseases": p.get("chronic_diseases", []),
            "goals":            p.get("goals", []),
            "smoker":           p.get("smoker", False),
            "alcoholic":        p.get("alcoholic", False),
        },
        "sleep": {
            "days_tracked":     len(sleep),
            "avg_sleep_score":  _avg([r.get("sleep")    for r in sleep]),
            "avg_stress_score": _avg([r.get("stress")   for r in sleep]),
            "avg_energy_score": _avg([r.get("energy")   for r in sleep]),
            "avg_caffeine":     _avg([r.get("caffeine") for r in sleep]),
        },
        "nutrition": {
            "days_tracked":      len(nut),
            "avg_calories":      _avg([r.get("total_calories") for r in nut]),
            "avg_hydration_L":   _avg([r.get("hydration")      for r in nut]),
            "avg_meals_per_day": _avg([r.get("meal_count")     for r in nut]),
        },
        "activity": {
            "sessions_tracked":     len(acts),
            "active_days_per_week": active_days_per_week,
            "meets_who_threshold":  active_days_per_week >= 5,
            "avg_duration_min":     _avg([r.get("duration_minutes") for r in acts]),
        },
        "vital_signs": {
            "measurements_tracked": len(vitals),
            "avg_heart_rate":       _avg([r.get("heart_rate")         for r in vitals]),
            "avg_systolic_bp":      _avg([r.get("systolic_pressure")  for r in vitals]),
            "avg_diastolic_bp":     _avg([r.get("diastolic_pressure") for r in vitals]),
            "avg_spo2_pct":         _avg([r.get("oxygen_saturation")  for r in vitals]),
        },
        "smoking": {
            "days_tracked":   len(smk),
            "avg_cigarettes": _avg([r.get("cigarettes_per_day") for r in smk]),
            "avg_puffs":      _avg([r.get("puffs_per_day")      for r in smk]),
        },
        "alcohol": {
            "drinking_days": len(alc),
            "avg_glasses":   _avg([r.get("alcohol_glasses") for r in alc]),
        },
        "lab_results": [
            {"type": r.get("analysis_type"), "name": r.get("result_name"), "value": r.get("value"), "unit": r.get("unit")}
            for r in labs
        ],
        "treatments": [
            {"name": r.get("medication_name"), "adherence": r.get("adherence_rate")}
            for r in treats
        ],
    }


# ── Step 2: Rule-based checks (prevents LLM contradictions) ────

def _rule_checks(s: dict) -> str:
    """Pre-compute key thresholds so the LLM never contradicts math."""
    lines = []

    adpw = s["activity"].get("active_days_per_week")
    if adpw is not None:
        if adpw >= 5:
            lines.append(f"ACTIVITÉ: {adpw} j/semaine — AU-DESSUS seuil OMS. Ne PAS signaler comme insuffisant.")
        else:
            lines.append(f"ACTIVITÉ: {adpw} j/semaine — EN DESSOUS seuil OMS (5 j). À signaler.")

    avg_sleep = s["sleep"].get("avg_sleep_score")
    if avg_sleep is not None:
        lines.append(f"SOMMEIL: {avg_sleep}/10 — {'BON (≥7)' if avg_sleep >= 7 else 'INSUFFISANT (<7)'}.")

    avg_stress = s["sleep"].get("avg_stress_score")
    if avg_stress is not None:
        lines.append(f"STRESS: {avg_stress}/10 — {'ÉLEVÉ (>6)' if avg_stress > 6 else 'NORMAL (≤6)'}.")

    hyd = s["nutrition"].get("avg_hydration_L")
    if hyd is not None:
        lines.append(f"HYDRATATION: {hyd} L/j — {'ADÉQUAT (≥2L)' if hyd >= 2.0 else 'INSUFFISANT (<2L)'}.")

    sys_bp = s["vital_signs"].get("avg_systolic_bp")
    if sys_bp is not None:
        if sys_bp >= 140:   lines.append(f"TENSION: {sys_bp} mmHg — HYPERTENSION STADE 2.")
        elif sys_bp >= 130: lines.append(f"TENSION: {sys_bp} mmHg — HYPERTENSION STADE 1.")
        elif sys_bp >= 120: lines.append(f"TENSION: {sys_bp} mmHg — ÉLEVÉE.")
        else:               lines.append(f"TENSION: {sys_bp} mmHg — NORMALE.")

    spo2 = s["vital_signs"].get("avg_spo2_pct")
    if spo2 is not None:
        if spo2 < 90:   lines.append(f"SPO2: {spo2}% — CRITIQUE (<90%).")
        elif spo2 < 95: lines.append(f"SPO2: {spo2}% — ANORMAL (<95%).")
        else:           lines.append(f"SPO2: {spo2}% — NORMAL (≥95%).")

    return "\n".join(lines) if lines else "Données insuffisantes pour les vérifications."


# ── Step 3a: Domain analyses (Call 1) ──────────────────────────

_NO_DATA = "No data available for this domain."

_DOMAIN_SCHEMA = """{
  "sleep":       {"analysis": "interprétation des tendances en 2-3 phrases", "issues": ["observation nuancée si pertinente"], "severity": "stable|watch|moderate|high|priority"},
  "nutrition":   {"analysis": "...", "issues": [...], "severity": "..."},
  "activity":    {"analysis": "...", "issues": [...], "severity": "..."},
  "vital_signs": {"analysis": "...", "issues": [...], "severity": "..."},
  "smoking":     {"analysis": "...", "issues": [...], "severity": "..."},
  "alcohol":     {"analysis": "...", "issues": [...], "severity": "..."},
  "lab_results": {"analysis": "...", "issues": [...], "severity": "..."},
  "treatments":  {"analysis": "...", "issues": [...], "severity": "..."}
}"""

_AGGREGATION_SCHEMA = """{
  "positive_observations": [
    "point positif observé — ex: Une adhérence thérapeutique satisfaisante a été maintenue sur la période"
  ],
  "anomalies": [
    {"code": "CODE_MAJ", "severity": "watch|moderate|high|priority", "message": "interprétation nuancée en langage accessible", "domains": ["str"]}
  ],
  "global_recommendations": [
    {"priority": 1, "action": "conseil pratique et progressif formulé sans rigidité", "domain": "str", "impact": "bénéfice attendu formulé avec nuance"}
  ],
  "alerts": [
    {"level": "high|priority", "message": "formulation factuelle non alarmiste", "domains": ["str"], "suggested_action": "action concrète recommandée"}
  ],
  "risk_level": "low|medium|high",
  "risk_summary": "2-3 phrases d'interprétation globale nuancée, valorisant les points positifs et contextualisant les risques"
}"""


_SYSTEM_PROMPT = """Tu es un analyste santé expert d'une plateforme de santé numérique premium.
Tu produis des bilans de santé professionnels, accessibles et humains — jamais alarmistes, jamais robotiques.
Retourne UNIQUEMENT du JSON brut valide. Aucun markdown, aucun texte avant ou après le JSON."""

_WRITING_RULES = """STYLE D'ÉCRITURE OBLIGATOIRE:
- Langue: français, tutoiement formel (vous/votre). JAMAIS "le patient" ni "l'utilisateur".
- JAMAIS de parenthèses dans les textes.
- JAMAIS de phrases du type "La valeur X est Y" → TOUJOURS interpréter la signification.
- JAMAIS de ton alarmiste. JAMAIS d'affirmations absolues.
- Préférer: "une tendance à", "des valeurs globalement", "mérite une attention", "à surveiller préventivemant".
- Formulations nuancées obligatoires: "peut contribuer à", "semble indiquer", "un suivi peut être envisagé".
- Ce système ne produit PAS de diagnostic médical. Toujours garder un ton de bilan informatif.
- Valoriser les comportements positifs détectés avant de signaler les points d'attention.
- Interpréter les tendances globales plutôt que réciter les chiffres individuels.

NIVEAUX DE SÉVÉRITÉ (domaines):
- stable    = données dans la norme, tendance favorable
- watch     = légère variation à surveiller à titre préventif
- moderate  = point d'attention méritant une adaptation progressive
- high      = situation nécessitant une attention soutenue
- priority  = situation nécessitant une prise en charge rapide"""


def _call_domains(summary: dict, rule_checks: str) -> dict:
    """Call 1 — analyze each health domain."""

    data_blocks = {}
    for domain in ("sleep", "nutrition", "activity", "vital_signs", "smoking", "alcohol", "lab_results", "treatments"):
        d = summary.get(domain, {})
        count_key = {"sleep": "days_tracked", "nutrition": "days_tracked", "activity": "sessions_tracked",
                     "vital_signs": "measurements_tracked", "smoking": "days_tracked",
                     "alcohol": "drinking_days", "lab_results": None, "treatments": None}.get(domain)
        has_data = (count_key is None and bool(d)) or (count_key and d.get(count_key, 0) > 0)
        if has_data:
            data_blocks[domain] = d

    prompt = f"""Analyse les données de santé suivantes et produis un bilan par domaine.

FORMAT IMPÉRATIF: Retourne DIRECTEMENT un objet JSON dont les clés sont les noms de domaine.
Commence immédiatement par {{ — aucun texte, aucune clé wrapper ("domains", "result", etc.).

{_WRITING_RULES}

PROFIL DE L'USAGER:
{_dumps(summary["user"])}

DONNÉES DE SANTÉ (moyennes calculées sur la période de suivi):
{_dumps(data_blocks)}

VÉRIFICATIONS MATHÉMATIQUES PRÉ-CALCULÉES (ne jamais contredire ces conclusions):
{rule_checks}

INSTRUCTIONS PAR DOMAINE:
- analysis: 2-3 phrases d'interprétation intelligente. Commencer par les points positifs si pertinent.
  Interpréter la tendance globale, ne pas lister des chiffres bruts.
  Contextualiser par rapport au profil (âge, maladies chroniques, objectifs).
- issues: uniquement les observations cliniquement pertinentes (max 3). Ignorer les variations mineures dans la norme.
  Formulation nuancée: "Une tendance à la hausse a été observée" plutôt que "La tension est élevée".
- severity: choisir parmi stable|watch|moderate|high|priority selon l'impact réel sur la santé.
  Ne PAS surestimer la sévérité pour des variations légères ou des indicateurs globalement satisfaisants.
- Domaines sans données: analysis="{_NO_DATA}", issues=[], severity="stable"

SEUILS DE RÉFÉRENCE:
- Sommeil: ≥7/10=satisfaisant, 5-6=à surveiller, <5=insuffisant
- Stress: ≤6/10=géré, >6=élevé, >8=prioritaire
- Hydratation: ≥2L/j=adéquate, 1.5-2=légèrement insuffisante
- Calories: 1800-2500kcal/j=plage normale (selon profil)
- FC: 60-100bpm=normale | SpO2: ≥95%=normale | Tension: <120=optimale, 120-129=élevée, ≥130=HTA
- Adhérence médicaments: ≥80%=bonne, 60-80%=à améliorer, <60%=insuffisante

FORMAT DE SORTIE (commence par {{, sans aucun texte avant):
{_DOMAIN_SCHEMA}"""

    response = _get_client().chat.completions.create(
        model=MODEL, max_tokens=3500, temperature=0.4,
        messages=[
            {"role": "system", "content": _SYSTEM_PROMPT},
            {"role": "user",   "content": prompt},
        ],
    )
    result = _parse_json(response.choices[0].message.content)

    if isinstance(result, dict) and "domains" in result and isinstance(result["domains"], dict):
        result = result["domains"]

    return result


def _call_aggregation(summary: dict, domains: dict, rule_checks: str) -> dict:
    """Call 2 — global synthesis, positive observations, anomalies, recommendations."""

    active_domains = "\n".join(
        f"[{d.upper()}] sévérité={v.get('severity','?')} — {v.get('analysis','')[:150]}"
        for d, v in domains.items()
        if not v.get("analysis", "").startswith("No data")
    )

    prompt = f"""Produis la synthèse globale du bilan de santé.

FORMAT IMPÉRATIF: Retourne DIRECTEMENT un objet JSON.
Commence immédiatement par {{ — aucun texte, aucune clé wrapper.

{_WRITING_RULES}

PROFIL DE L'USAGER:
{_dumps(summary["user"])}

ANALYSES PAR DOMAINE (résumé):
{active_domains}

VÉRIFICATIONS MATHÉMATIQUES (ne jamais contredire):
{rule_checks}

DONNÉES NUMÉRIQUES DE RÉFÉRENCE:
{_dumps({k: v for k, v in summary.items() if k != "user"})}

INSTRUCTIONS:

1. positive_observations (2 à 4 éléments):
   - Identifier les comportements positifs: bonne adhérence, activité régulière, hydratation correcte, absence de tabac, glycémie stable, etc.
   - Formulation encourageante mais sobre: "Une activité physique régulière et soutenue a été maintenue sur la période."
   - NE PAS inclure si aucun point positif réel n'est détecté.

2. anomalies (uniquement les vraies anomalies, pas les variations normales):
   - Basées UNIQUEMENT sur les valeurs EN DESSOUS des seuils dans les vérifications mathématiques.
   - Ne JAMAIS signaler comme anomalie une valeur AU-DESSUS du seuil OMS ou des normes.
   - Formulation nuancée: "Une tendance à..." plutôt que "Vous avez une anomalie de..."
   - Sévérité: watch|moderate|high|priority

3. global_recommendations (3 à 5 recommandations):
   - Personnalisées selon le profil et les anomalies détectées.
   - Progressives et réalistes: pas d'objectifs arbitraires ou rigides.
   - Pratiques: "Envisager d'intégrer..." plutôt que "Vous devez faire X exactement Y fois".
   - Chaque recommandation doit correspondre à une anomalie ou un point d'amélioration identifié.
   - impact: formulé avec nuance ("peut contribuer à", "pourrait aider à")

4. alerts: UNIQUEMENT pour situations nécessitant attention rapide (SpO2<90%, HTA stade2 ≥140mmHg, glycémie critique).
   Formulation factuelle, non alarmiste.

5. risk_level: low si profil globalement sain, medium si quelques points d'attention, high si anomalies significatives.

6. risk_summary: 2-3 phrases équilibrées — mentionner les points positifs ET les points d'attention.
   Exemple: "Votre profil de santé présente plusieurs indicateurs satisfaisants. Une attention particulière à [domaine] pourrait contribuer à optimiser votre bien-être global."

FORMAT DE SORTIE:
{_AGGREGATION_SCHEMA}"""

    response = _get_client().chat.completions.create(
        model=MODEL, max_tokens=2500, temperature=0.4,
        messages=[
            {"role": "system", "content": _SYSTEM_PROMPT},
            {"role": "user",   "content": prompt},
        ],
    )
    result = _parse_json(response.choices[0].message.content)

    # Unwrap common wrapper keys
    for wrapper in ("result", "analysis", "report", "data"):
        if isinstance(result, dict) and list(result.keys()) == [wrapper]:
            result = result[wrapper]
            break

    return result


# ── Public API ──────────────────────────────────────────────────

_EMPTY_DOMAIN = {"analysis": _NO_DATA, "issues": [], "severity": "stable"}
_ALL_DOMAINS  = ("sleep", "nutrition", "activity", "vital_signs", "smoking", "alcohol", "lab_results", "treatments")


def analyze_user(user_id: int) -> dict:
    """Run the full AI health analysis. Returns a report dict ready for the frontend."""
    from .config import load_groq_api_key
    from .db import extract_user_data

    load_groq_api_key()

    try:
        raw = extract_user_data(user_id)
    except Exception as exc:
        return _error_report(user_id, str(exc))

    profile = raw.get("user_profile", {})
    summary = _summarize(raw)
    checks  = _rule_checks(summary)

    try:
        domains = _call_domains(summary, checks)
    except Exception as exc:
        return _error_report(user_id, f"Domain analysis failed: {exc}")

    for d in _ALL_DOMAINS:
        if d not in domains:
            domains[d] = _EMPTY_DOMAIN.copy()

    try:
        agg = _call_aggregation(summary, domains, checks)
    except Exception as exc:
        agg = {"positive_observations": [], "anomalies": [], "global_recommendations": [],
               "alerts": [], "risk_level": "unknown", "risk_summary": f"Aggregation failed: {exc}"}

    return {
        "user_id":                user_id,
        "user_name":              profile.get("name"),
        "status":                 "success",
        "domains":                domains,
        "positive_observations":  agg.get("positive_observations", []),
        "anomalies":              agg.get("anomalies", []),
        "global_recommendations": agg.get("global_recommendations", []),
        "alerts":                 agg.get("alerts", []),
        "risk_level":             agg.get("risk_level", "low"),
        "risk_summary":           agg.get("risk_summary", ""),
        "meta":                   {"errors": []},
    }


def _error_report(user_id: int, error: str) -> dict:
    return {
        "user_id": user_id, "user_name": None, "status": "error",
        "domains": {d: _EMPTY_DOMAIN.copy() for d in _ALL_DOMAINS},
        "positive_observations": [], "anomalies": [],
        "global_recommendations": [], "alerts": [],
        "risk_level": "unknown", "risk_summary": "",
        "meta": {"errors": [error]},
    }
