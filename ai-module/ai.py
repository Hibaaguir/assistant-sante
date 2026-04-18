"""AI health analysis using Groq. Handles domain analysis, anomaly detection, and aggregation."""

from __future__ import annotations
import json
import os
import re

from groq import Groq

MODEL = "llama-3.3-70b-versatile"
_client = None


def _get_client() -> Groq:
    global _client
    if _client is None:
        api_key = os.environ.get("GROQ_API_KEY")
        if not api_key:
            raise EnvironmentError("GROQ_API_KEY not set. Add GROQ_API_KEY=gsk_... to backend/.env")
        _client = Groq(api_key=api_key)
    return _client


def _call_llm(prompt: str, max_tokens: int = 1024) -> dict | list:
    """Call Groq and return parsed JSON."""
    response = _get_client().chat.completions.create(
        model=MODEL,
        max_tokens=max_tokens,
        temperature=0.3,
        messages=[
            {
                "role": "system",
                "content": (
                    "You are a medical data analyst. "
                    "Always respond with valid JSON only. "
                    "Never include markdown, explanations, or text outside the JSON. "
                    "All text fields (analysis, issues, recommendations, messages, summaries) MUST be written in French."
                ),
            },
            {"role": "user", "content": prompt},
        ],
    )
    raw = response.choices[0].message.content.strip()
    cleaned = re.sub(r"^```(?:json)?\s*|\s*```$", "", raw, flags=re.DOTALL).strip()
    try:
        return json.loads(cleaned)
    except json.JSONDecodeError as e:
        raise ValueError(f"Model returned non-JSON: {raw[:400]}") from e


# ── Domain hints ───────────────────────────────────────────────

_DOMAIN_HINTS: dict[str, dict] = {
    "sleep": {
        "role":  "clinical sleep and stress analyst",
        "title": "SLEEP / STRESS / ENERGY",
        "hints": (
            "avg_sleep_score: 0–10 (7+ healthy, <5 concerning). "
            "sleep_variability: std dev >2 = irregular sleep. "
            "avg_stress_score: 0–10 (>6 elevated). "
            "avg_energy_score: 0–10 (<5 = chronic fatigue). "
            "low_sleep_frequency_pct: % nights with score ≤4. "
            "avg_caffeine_cups: >3/day may impair sleep. "
            "Consider caffeine-sleep interactions and stress-sleep feedback loops."
        ),
    },
    "nutrition": {
        "role":  "clinical dietitian",
        "title": "NUTRITION",
        "hints": (
            "avg_daily_calories: healthy range ~1800–2500 kcal (varies by age/gender/BMI). "
            "calorie_variability: std dev >400 = inconsistent eating. "
            "avg_hydration_liters: recommended 2.0–2.5 L/day; <1.5 is concerning. "
            "avg_meal_count: <2/day is a metabolic red flag. "
            "sugar_intake_distribution: % days with none/low/medium/high sugar. "
            "Reference BMI and chronic diseases (diabetes → sugar intake critical)."
        ),
    },
    "activity": {
        "role":  "sports medicine physician",
        "title": "PHYSICAL ACTIVITY",
        "hints": (
            "active_days_per_week: WHO recommends ≥5 days moderate or ≥3 days vigorous. "
            "avg_duration_minutes: WHO minimum 150 min/week moderate or 75 min/week vigorous. "
            "avg_effort_score: >3.0 = moderate effort. "
            "sedentary_days_pct: >60% is concerning. "
            "Consider age, BMI, and chronic diseases when interpreting results."
        ),
    },
    "smoking": {
        "role":  "pulmonologist and tobacco cessation specialist",
        "title": "SMOKING / TOBACCO",
        "hints": (
            "avg_daily_units: cigarette-equivalents per day (1 cig = ~15 vape puffs). "
            "heavy_smoking_days_pct: % days with >20 units (WHO heavy smoker threshold). "
            "smoking_trend: direction of use over time. "
            "If no log data but profile shows smoker=true, note the data gap and still give cessation recommendations. "
            "Reference chronic diseases like COPD and cardiovascular disease for severity."
        ),
    },
    "alcohol": {
        "role":  "hepatologist and addiction medicine specialist",
        "title": "ALCOHOL CONSUMPTION",
        "hints": (
            "avg_glasses_on_drinking_days: average drinks per drinking day. "
            "risky_drinking_days_pct: % days exceeding 2 standard drinks. "
            "WHO: >14 drinks/week for men or >7 for women is hazardous. "
            "Consider liver function, medication interactions, and patient goals. "
            "If no data but profile flags alcoholic=true, treat this as a high-priority gap."
        ),
    },
    "vital_signs": {
        "role":  "cardiologist",
        "title": "VITAL SIGNS",
        "hints": (
            "avg_heart_rate: normal 60–100 bpm; <60 bradycardia; >100 tachycardia. "
            "avg_systolic_pressure: normal <120; elevated 120–129; stage 1 HBP 130–139; stage 2 ≥140. "
            "avg_diastolic_pressure: normal <80; HBP ≥90. "
            "avg_oxygen_saturation: normal ≥95%; <90% critical. "
            "hypertension_episodes_pct: % readings with systolic ≥140. "
            "Consider age, BMI, and cardiovascular/respiratory chronic diseases."
        ),
    },
    "lab_results": {
        "role":  "internist reviewing laboratory results",
        "title": "LAB RESULTS",
        "hints": (
            "For each analysis type compare latest_value to standard reference ranges. "
            "blood_glucose: normal fasting 3.9–5.6 mmol/L or 70–100 mg/dL. "
            "HbA1c: normal <5.7%; prediabetes 5.7–6.4%; diabetes ≥6.5%. "
            "total_cholesterol: desirable <5.2 mmol/L or <200 mg/dL. "
            "hemoglobin: normal 130–170 g/L (men), 120–160 g/L (women). "
            "Note the trend field: 'worsening' means value moving away from normal range. "
            "Reference chronic diseases heavily — context changes the interpretation."
        ),
    },
    "treatments": {
        "role":  "clinical pharmacist",
        "title": "MEDICATIONS & ADHERENCE",
        "hints": (
            "avg_adherence_rate: 0.0–1.0; <0.80 = clinically significant non-adherence. "
            "low_adherence_medications: specific medications below 80% adherence. "
            "Non-adherence to antihypertensives, antidiabetics, or statins carries serious risk. "
            "If adherence is uniformly low, consider systemic causes (regimen complexity, side effects). "
            "Reference chronic diseases to assess consequences of missing doses."
        ),
    },
}

_DOMAIN_OUTPUT = """
Return ONLY valid JSON — no markdown, no text outside the JSON. All text in French.

RÈGLE CRITIQUE: chaque recommendation doit être SPÉCIFIQUE — citer les valeurs du patient, donner une fréquence/durée/méthode précise.
  ❌ MAUVAIS: "Améliorer l'hydratation"
  ✅ BON: "Boire 2,5 L d'eau par jour (actuellement 1.2 L/j en moyenne) — poser une alarme à 9h, 13h et 17h comme rappel"

{
  "analysis":        "narrative factuelle de 2–4 phrases citant les valeurs numériques du patient",
  "issues":          ["problème précis avec la valeur du patient, ex: 'Score de sommeil moyen de 5.1/10, avec 40% de nuits <4/10'"],
  "recommendations": ["action SPÉCIFIQUE avec chiffre du patient + fréquence/méthode, ex: 'Fixer un réveil à 7h tous les jours pour réguler le rythme circadien — votre variabilité de sommeil de 2.3 points indique un rythme irrégulier'"],
  "severity":        "none | low | medium | high"
}
"""

_AGGREGATION_OUTPUT = """
Return ONLY valid JSON — no markdown, no text outside the JSON. All text in French.

RÈGLE CRITIQUE pour global_recommendations — chaque "action" doit être SPÉCIFIQUE et ACTIONNABLE:
  ❌ MAUVAIS: "Arrêter de fumer"
  ✅ BON: "Réduire à moins de 10 cigarettes/jour cette semaine, puis utiliser un patch nicotinique 14mg pendant 6 semaines pour atteindre l'abstinence"
  ❌ MAUVAIS: "Améliorer le sommeil"
  ✅ BON: "Se coucher à heure fixe (22h30), éviter les écrans 1h avant, limiter la caféine après 14h — votre score moyen de 6.97/10 peut atteindre 8/10 en 3 semaines"
  ❌ MAUVAIS: "Augmenter l'activité physique"
  ✅ BON: "Ajouter 2 séances de marche rapide de 30 min par semaine (lundi et jeudi) pour passer de 3 à 5 jours actifs/semaine, conformément aux recommandations OMS"

Chaque action doit: citer un chiffre du patient, donner une fréquence/durée précise, indiquer une méthode concrète.

Format JSON:
{
  "anomalies": [
    {"code": "CODE_MAJUSCULE", "severity": "low|medium|high|critical", "message": "constat précis avec chiffres du patient", "domains": ["domain"]}
  ],
  "cross_domain_patterns": [
    {"pattern": "titre court du lien", "domains": ["domain1", "domain2"], "explanation": "explication causale détaillée avec chiffres", "severity": "low|medium|high"}
  ],
  "global_recommendations": [
    {"priority": 1, "action": "action SPÉCIFIQUE avec chiffres, fréquence et méthode concrète", "domain": "domaine principal", "impact": "bénéfice attendu PRÉCIS avec chiffre ou délai estimé"}
  ],
  "alerts": [
    {"level": "high|critical", "message": "constat urgent avec valeur précise", "domains": ["domain"], "suggested_action": "action immédiate très précise à prendre"}
  ],
  "risk_level":   "low | medium | high",
  "risk_summary": "résumé de 2–3 phrases citant les valeurs clés du patient et les 2 priorités absolues"
}
"""


def _user_block(ctx: dict) -> str:
    chronic   = ", ".join(ctx.get("chronic_diseases") or []) or "none"
    allergies = ", ".join(ctx.get("allergies") or []) or "none"
    goals     = ", ".join(ctx.get("goals") or []) or "not specified"
    return (
        f"USER PROFILE:\n"
        f"  Age: {ctx.get('age', 'unknown')}  Gender: {ctx.get('gender', 'unknown')}  "
        f"BMI: {ctx.get('bmi', 'unknown')} ({ctx.get('bmi_category', '')})\n"
        f"  Chronic diseases: {chronic}\n"
        f"  Allergies: {allergies}\n"
        f"  Goals: {goals}\n"
        f"  Smoker: {ctx.get('smoker', False)}  Alcoholic: {ctx.get('alcoholic', False)}"
    )


# ── Public API ─────────────────────────────────────────────────

def analyze_with_ai(summaries: dict, user_context: dict) -> dict:
    """
    Analyze health summaries with the LLM.
    Steps:
      1. Per-domain analysis (8 sequential LLM calls)
      2. Aggregation + anomaly detection (1 LLM call)
    Returns full result dict ready for the report.
    """
    user_blk = _user_block(user_context)
    domain_analyses: dict[str, dict] = {}

    for domain, hints in _DOMAIN_HINTS.items():
        summary = summaries.get(domain, {})

        # Skip empty domains — but if profile flags the behaviour, still ask the LLM
        is_empty = (
            not summary
            or summary.get("days_tracked") == 0
            or summary.get("smoking_days") == 0
            or summary.get("drinking_days") == 0
            or summary.get("total_medications") == 0
            or summary.get("measurements_tracked") == 0
            or summary.get("total_results") == 0
        )
        if is_empty:
            flag = (
                (domain == "smoking"  and user_context.get("smoker"))
                or (domain == "alcohol" and user_context.get("alcoholic"))
            )
            if not flag:
                domain_analyses[domain] = {
                    "analysis": "No data available for this domain.",
                    "issues": [], "recommendations": [], "severity": "none",
                }
                continue

        prompt = (
            f"You are a {hints['role']}. Analyze the following health data.\n\n"
            f"{user_blk}\n\n"
            f"{hints['title']} SUMMARY:\n{json.dumps(summary, indent=2)}\n\n"
            f"Key interpretation guidelines:\n{hints['hints']}\n\n"
            f"{_DOMAIN_OUTPUT}"
        )

        try:
            result = _call_llm(prompt, max_tokens=800)
            domain_analyses[domain] = result if isinstance(result, dict) else {
                "analysis": str(result), "issues": [], "recommendations": [], "severity": "low"
            }
        except Exception as exc:
            domain_analyses[domain] = {
                "analysis": f"Analysis failed: {exc}", "issues": [], "recommendations": [], "severity": "none"
            }

    # Aggregation + anomaly detection
    analyses_block = ""
    for domain, result in domain_analyses.items():
        analyses_block += (
            f"\n[{domain.upper()}] severity={result.get('severity', '?').upper()}\n"
            f"  {result.get('analysis', '')}\n"
            f"  Issues: {result.get('issues', [])}\n"
        )

    agg_prompt = (
        f"Tu es un médecin senior synthétisant un rapport de santé complet pour un patient.\n\n"
        f"{user_blk}\n\n"
        f"ANALYSES PAR DOMAINE:\n{analyses_block}\n\n"
        f"DONNÉES NUMÉRIQUES CLÉS (utilise ces chiffres dans tes recommandations):\n{json.dumps(summaries, indent=2)}\n\n"
        f"Tes tâches:\n"
        f"1. Identifier les anomalies cliniquement significatives avec leurs valeurs précises\n"
        f"2. Trouver les connexions causales entre domaines (ex: stress élevé → mauvais sommeil → fatigue)\n"
        f"3. Formuler les 5 recommandations les plus impactantes — CHACUNE doit citer un chiffre du patient, une fréquence précise et une méthode concrète\n"
        f"4. Signaler les alertes urgentes avec les valeurs déclenchantes\n"
        f"5. Attribuer un niveau de risque global et rédiger un résumé citant les valeurs clés\n\n"
        f"{_AGGREGATION_OUTPUT}"
    )

    try:
        agg = _call_llm(agg_prompt, max_tokens=2048)
    except Exception as exc:
        agg = {
            "anomalies": [], "cross_domain_patterns": [],
            "global_recommendations": [], "alerts": [],
            "risk_level": "unknown",
            "risk_summary": f"Aggregation step failed: {exc}",
        }

    return {
        "domains":                domain_analyses,
        "anomalies":              agg.get("anomalies", []),
        "cross_domain_patterns":  agg.get("cross_domain_patterns", []),
        "global_recommendations": agg.get("global_recommendations", []),
        "alerts":                 agg.get("alerts", []),
        "risk_level":             agg.get("risk_level", "low"),
        "risk_summary":           agg.get("risk_summary", ""),
    }
