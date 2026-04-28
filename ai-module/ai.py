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
                    "You are a medical data analyst speaking directly to the patient. "
                    "Always respond with valid JSON only. "
                    "Never include markdown, explanations, or text outside the JSON. "
                    "All text fields MUST be written in French. "
                    "CRITICAL: Always address the patient directly using 'vous' (formal second person). "
                    "Never use 'le patient doit', 'le patient a', or any third-person reference. "
                    "Use 'vous devez', 'votre', 'vous avez', etc. "
                    "STYLE: Never use parentheses () in any text field. Use a dash or rewrite the sentence instead."
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
            "SCOPE: Analyze ONLY sleep_score, stress_score, energy_score, caffeine_cups, low_sleep_frequency_pct, sleep_trend, stress_trend, energy_trend. "
            "Do NOT mention hydration — it belongs to the nutrition domain. "
            "avg_sleep_score: 0–10 (7+ healthy, <5 concerning). "
            "sleep_variability: std dev >2 = irregular sleep. "
            "avg_stress_score: 0–10 (>6 elevated). "
            "avg_energy_score: 0–10 (<5 = chronic fatigue). "
            "low_sleep_frequency_pct: % nights with score ≤4. "
            "avg_caffeine_cups: >3/day may impair sleep quality. "
        ),
    },
    "nutrition": {
        "role":  "clinical dietitian",
        "title": "NUTRITION",
        "hints": (
            "SCOPE: Analyze ONLY calories, hydration, meal_count, sugar_intake, caffeine in a nutritional context. "
            "Do NOT mention sleep scores, stress, energy levels, or activity data — those belong to other domains. "
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
            "SCOPE: Analyze ONLY activity frequency, duration, effort, and intensity. "
            "Do NOT mention sleep, nutrition, smoking, alcohol, or any other domain. "
            "CRITICAL — READ meets_who_threshold BEFORE writing any issue or recommendation: "
            "  - meets_who_threshold=true means active_days_per_week >= 5 — the user ALREADY exceeds WHO frequency. "
            "    Do NOT write any issue about insufficient frequency. "
            "    Do NOT recommend adding more active days. "
            "    Instead analyze quality, intensity variety, duration, or injury prevention. "
            "  - meets_who_threshold=false means active_days_per_week < 5 — flag as insufficient and recommend reaching 5 days/week. "
            "active_days_per_week: WHO recommends ≥5 days moderate or ≥3 days vigorous. "
            "estimated_weekly_minutes: WHO minimum 150 min/week moderate or 75 min/week vigorous. "
            "avg_effort_score: >3.0 = moderate effort. "
            "Consider age, BMI, and chronic diseases when interpreting results."
        ),
    },
    "smoking": {
        "role":  "pulmonologist and tobacco cessation specialist",
        "title": "SMOKING / TOBACCO",
        "hints": (
            "SCOPE: Analyze ONLY tobacco consumption data. "
            "Do NOT mention nutrition, sleep, activity, alcohol, or any other domain. "
            "avg_daily_units: cigarette-equivalents per day (1 cig = ~15 vape puffs). "
            "heavy_smoking_days_pct: % days with >20 units (WHO heavy smoker threshold). "
            "smoking_trend: direction of use over time. "
            "If no log data but profile shows smoker=true, note the data gap and give cessation recommendations. "
            "Reference chronic diseases like COPD and cardiovascular disease for severity."
        ),
    },
    "alcohol": {
        "role":  "hepatologist and addiction medicine specialist",
        "title": "ALCOHOL CONSUMPTION",
        "hints": (
            "SCOPE: Analyze ONLY alcohol consumption data. "
            "Do NOT mention nutrition, sleep, activity, smoking, or any other domain. "
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
            "SCOPE: Analyze ONLY heart rate, blood pressure, and oxygen saturation. "
            "Do NOT mention sleep, nutrition, activity, or any other domain. "
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
            "SCOPE: Analyze ONLY the laboratory values in the summary. "
            "Do NOT mention sleep, nutrition, activity, vital signs, or any other domain. "
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
            "SCOPE: Analyze ONLY medication adherence data. "
            "Do NOT mention sleep, nutrition, activity, lab results, or any other domain. "
            "avg_adherence_rate: 0.0–1.0; <0.80 = clinically significant non-adherence. "
            "low_adherence_medications: specific medications below 80% adherence. "
            "Non-adherence to antihypertensives, antidiabetics, or statins carries serious risk. "
            "If adherence is uniformly low, consider systemic causes (regimen complexity, side effects). "
            "Reference chronic diseases to assess consequences of missing doses."
        ),
    },
}

_DOMAIN_OUTPUT = """
Return ONLY valid JSON — no markdown, no text outside the JSON. All text in French, addressing the user as "vous".
Never use parentheses in any field. Use a dash to add precision instead.
CRITICAL: Only analyze the fields present in the domain summary provided. Do not mention or infer data from other domains.

IMPORTANT — field types:
- "analysis" MUST be a single plain STRING (not an object, not an array). Write 4 to 6 clinical sentences joined together in one string, each separated by a space. Example: "Votre score est X. Il est en dessous de la norme de Y. La tendance est Z."
- "issues" MUST be a flat array of strings — each string is one problem sentence citing the exact value and clinical threshold.
- "recommendations" MUST be a flat array of strings — each string is one specific action with a number, frequency and method.
- "severity" MUST be one of: "none", "low", "medium", "high"

Only include issues and data directly supported by the fields in the summary above.

{
  "analysis":        "Phrase 1 citant une valeur. Phrase 2 sur la tendance. Phrase 3 comparant à la norme. Phrase 4 sur la signification clinique.",
  "issues":          ["Constat précis avec valeur exacte et seuil clinique — ex: Votre score est de 5,1/10, en dessous du seuil de 7/10"],
  "recommendations": ["Action spécifique avec chiffre, fréquence et méthode — sans parenthèses"],
  "severity":        "none | low | medium | high"
}
"""

_AGGREGATION_OUTPUT = """
Return ONLY valid JSON — no markdown, no text outside the JSON. All text in French, addressing the user as "vous".
IMPORTANT STYLE RULES: Never use parentheses () in any text field. Use a dash or restructure the sentence instead.

RÈGLE ABSOLUE — RESPECTEZ LES PRÉ-ÉVALUATIONS MATHÉMATIQUES FOURNIES:
- Valeur marquée ABOVE threshold = résultat POSITIF — ne pas la signaler comme problème, ne pas recommander de l'augmenter.
- Valeur marquée BELOW threshold = résultat PROBLÉMATIQUE — la signaler et proposer une action corrective.
- Ne JAMAIS contredire les pré-évaluations. Elles sont calculées mathématiquement et sont exactes.
- INTERDIT: écrire qu'une fréquence est "en dessous du seuil" si la pré-évaluation dit ABOVE.

RÈGLE CRITIQUE pour global_recommendations — chaque "action" doit être SPÉCIFIQUE, ACTIONNABLE et s'adresser à l'usager avec "vous":
  MAUVAIS: "Le patient doit arrêter de fumer"
  MAUVAIS: "Arrêter de fumer"
  BON: "Réduisez à moins de 10 cigarettes par jour cette semaine, puis utilisez un patch nicotinique 14 mg pendant 6 semaines pour atteindre l'abstinence"
  MAUVAIS: "Améliorer le sommeil"
  BON: "Couchez-vous à heure fixe à 22h30, évitez les écrans 1 heure avant le coucher et limitez la caféine après 14h"
  MAUVAIS si usager ABOVE seuil OMS: "Augmenter l'activité physique — passer à 5 jours actifs"
  BON si usager BELOW seuil OMS: "Ajoutez 2 séances à votre rythme actuel pour atteindre 5 jours actifs par semaine — conformément aux recommandations de l'OMS"
  BON si usager ABOVE seuil OMS: "Pour optimiser vos séances actuelles, intégrez 1 séance de haute intensité par semaine - type HIIT de 20 minutes - pour améliorer votre endurance cardiovasculaire"

Chaque action doit: s'adresser à l'usager avec "vous", citer un chiffre de l'usager, donner une fréquence/durée précise, indiquer une méthode concrète, ne jamais utiliser de parenthèses.

Format JSON:
{
  "anomalies": [
    {"code": "CODE_MAJUSCULE", "severity": "low|medium|high|critical", "message": "constat précis avec chiffres de l'usager, en s'adressant à l'usager avec 'vous'", "domains": ["domain"]}
  ],
  "global_recommendations": [
    {"priority": 1, "action": "action SPÉCIFIQUE avec 'vous', chiffres, fréquence et méthode concrète", "domain": "domaine principal", "impact": "bénéfice attendu PRÉCIS avec chiffre ou délai estimé"}
  ],
  "alerts": [
    {"level": "high|critical", "message": "constat urgent avec valeur précise, s'adressant à l'usager avec 'vous'", "domains": ["domain"], "suggested_action": "action immédiate très précise à prendre, en s'adressant à l'usager avec 'vous'"}
  ],
  "risk_level":   "low | medium | high",
  "risk_summary": "résumé de 2–3 phrases citant les valeurs clés de l'usager et les 2 priorités absolues, en s'adressant à l'usager avec 'vous'"
}
"""


def _rule_based_checks(summaries: dict) -> str:
    """Pre-compute mathematical threshold evaluations to prevent LLM from generating contradictory messages."""
    lines = []

    act = summaries.get("activity", {})
    adpw = act.get("active_days_per_week")
    if adpw is not None:
        status = "ABOVE" if adpw >= 5 else "BELOW"
        note = "Do NOT flag as sedentary. Do NOT recommend increasing frequency." if adpw >= 5 else "Should be flagged as insufficient activity."
        lines.append(f"ACTIVITY frequency: {adpw} days/week — {status} WHO minimum of 5 days/week. {note}")
    weekly_min = act.get("estimated_weekly_minutes")
    if weekly_min is not None:
        status = "ABOVE" if weekly_min >= 150 else "BELOW"
        lines.append(f"ACTIVITY duration: ~{weekly_min} min/week — {status} WHO minimum of 150 min/week.")

    slp = summaries.get("sleep", {})
    avg_sleep = slp.get("avg_sleep_score")
    if avg_sleep is not None:
        status = "ABOVE" if avg_sleep >= 7 else "BELOW"
        lines.append(f"SLEEP score: {avg_sleep}/10 — {status} healthy threshold of 7/10.")
    avg_stress = slp.get("avg_stress_score")
    if avg_stress is not None:
        status = "ELEVATED" if avg_stress > 6 else "NORMAL"
        lines.append(f"STRESS score: {avg_stress}/10 — {status} (threshold: >6/10 = elevated).")
    avg_energy = slp.get("avg_energy_score")
    if avg_energy is not None:
        status = "BELOW" if avg_energy < 5 else "ADEQUATE"
        lines.append(f"ENERGY score: {avg_energy}/10 — {status} (chronic fatigue threshold: <5/10).")

    nut = summaries.get("nutrition", {})
    avg_hyd = nut.get("avg_hydration_liters")
    if avg_hyd is not None:
        if avg_hyd >= 2.0:
            lines.append(f"HYDRATION: {avg_hyd} L/day — ADEQUATE (minimum: 2.0 L/day).")
        elif avg_hyd >= 1.5:
            lines.append(f"HYDRATION: {avg_hyd} L/day — SLIGHTLY BELOW recommended 2.0 L/day.")
        else:
            lines.append(f"HYDRATION: {avg_hyd} L/day — SIGNIFICANTLY BELOW recommended 2.0 L/day.")
    avg_cal = nut.get("avg_daily_calories")
    if avg_cal is not None:
        if avg_cal < 1500:
            lines.append(f"CALORIES: {avg_cal} kcal/day — BELOW healthy minimum (~1800 kcal/day).")
        elif avg_cal > 3000:
            lines.append(f"CALORIES: {avg_cal} kcal/day — ABOVE healthy maximum (~2500 kcal/day).")
        else:
            lines.append(f"CALORIES: {avg_cal} kcal/day — WITHIN normal range (1800–2500 kcal/day).")

    vit = summaries.get("vital_signs", {})
    avg_hr = vit.get("avg_heart_rate")
    if avg_hr is not None:
        if avg_hr < 60:
            lines.append(f"HEART RATE: {avg_hr} bpm — BRADYCARDIA (below 60 bpm).")
        elif avg_hr > 100:
            lines.append(f"HEART RATE: {avg_hr} bpm — TACHYCARDIA (above 100 bpm).")
        else:
            lines.append(f"HEART RATE: {avg_hr} bpm — NORMAL (60–100 bpm).")
    avg_sys = vit.get("avg_systolic_pressure")
    if avg_sys is not None:
        if avg_sys >= 140:
            lines.append(f"SYSTOLIC BP: {avg_sys} mmHg — STAGE 2 HYPERTENSION (threshold: ≥140 mmHg).")
        elif avg_sys >= 130:
            lines.append(f"SYSTOLIC BP: {avg_sys} mmHg — STAGE 1 HYPERTENSION (130–139 mmHg).")
        elif avg_sys >= 120:
            lines.append(f"SYSTOLIC BP: {avg_sys} mmHg — ELEVATED (120–129 mmHg).")
        else:
            lines.append(f"SYSTOLIC BP: {avg_sys} mmHg — NORMAL (<120 mmHg).")
    avg_spo2 = vit.get("avg_oxygen_saturation")
    if avg_spo2 is not None:
        if avg_spo2 < 90:
            lines.append(f"SPO2: {avg_spo2}% — CRITICALLY LOW (threshold: <90%).")
        elif avg_spo2 < 95:
            lines.append(f"SPO2: {avg_spo2}% — BELOW NORMAL (threshold: <95%).")
        else:
            lines.append(f"SPO2: {avg_spo2}% — NORMAL (≥95%).")

    return "\n".join(lines) if lines else "No threshold data available."


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
        _zero_key = {
            "sleep":       "days_tracked",
            "nutrition":   "days_tracked",
            "activity":    "active_days",
            "smoking":     "smoking_days",
            "alcohol":     "drinking_days",
            "vital_signs": "measurements_tracked",
            "lab_results": "total_results",
            "treatments":  "total_medications",
        }
        key = _zero_key.get(domain)
        is_empty = not summary or (key is not None and summary.get(key, 0) == 0)
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
            result = _call_llm(prompt, max_tokens=1500)
            if not isinstance(result, dict):
                result = {"analysis": str(result), "issues": [], "recommendations": [], "severity": "low"}
            # Flatten analysis if the model returned an object or list instead of a string
            analysis = result.get("analysis", "")
            if isinstance(analysis, dict):
                analysis = " ".join(str(v) for v in analysis.values())
            elif isinstance(analysis, list):
                analysis = " ".join(str(v) for v in analysis)
            result["analysis"] = analysis
            domain_analyses[domain] = result
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

    rule_checks = _rule_based_checks(summaries)
    agg_prompt = (
        f"Tu es un médecin senior synthétisant un rapport de santé complet. Tu t'adresses directement à l'usager avec 'vous'.\n\n"
        f"{user_blk}\n\n"
        f"PRÉ-ÉVALUATIONS MATHÉMATIQUES (calculées avec précision — tu DOIS les respecter impérativement):\n{rule_checks}\n\n"
        f"ANALYSES PAR DOMAINE:\n{analyses_block}\n\n"
        f"DONNÉES NUMÉRIQUES CLÉS (utilise ces chiffres dans tes recommandations):\n{json.dumps(summaries, indent=2)}\n\n"
        f"Tes tâches:\n"
        f"1. Identifier les anomalies cliniquement significatives — UNIQUEMENT pour les valeurs marquées BELOW threshold dans les pré-évaluations, ou absentes des pré-évaluations mais anormales. Ne jamais signaler une valeur ABOVE threshold comme anomalie.\n"
        f"2. Formuler les 5 recommandations prioritaires — CHAQUE recommandation doit répondre directement à une anomalie identifiée à l'étape 1. Chaque recommandation doit citer le chiffre de l'usager concerné, donner une fréquence précise et une méthode concrète, en s'adressant à l'usager avec 'vous'. Utilise le même champ 'domain' que l'anomalie correspondante.\n"
        f"3. Signaler les alertes urgentes avec les valeurs déclenchantes, en s'adressant à l'usager avec 'vous'\n"
        f"4. Attribuer un niveau de risque global et rédiger un résumé citant les valeurs clés, en s'adressant à l'usager avec 'vous'\n\n"
        f"{_AGGREGATION_OUTPUT}"
    )

    try:
        agg = _call_llm(agg_prompt, max_tokens=2048)
    except Exception as exc:
        agg = {
            "anomalies": [],
            "global_recommendations": [], "alerts": [],
            "risk_level": "unknown",
            "risk_summary": f"Aggregation step failed: {exc}",
        }

    return {
        "domains":                domain_analyses,
        "anomalies":              agg.get("anomalies", []),
        "cross_domain_patterns":  [],
        "global_recommendations": agg.get("global_recommendations", []),
        "alerts":                 agg.get("alerts", []),
        "risk_level":             agg.get("risk_level", "low"),
        "risk_summary":           agg.get("risk_summary", ""),
    }
