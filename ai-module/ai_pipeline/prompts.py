"""
Prompt templates for each domain node.

Each function returns a complete, self-contained prompt string.
The prompt:
  1. Gives the AI the user profile context
  2. Passes ONLY the computed summary (no raw records)
  3. Demands specific, actionable output in strict JSON
  4. Forbids vague advice

Output JSON schema required from every node:
{
    "analysis":        "2–4 sentence narrative",
    "issues":          ["specific finding 1", ...],
    "recommendations": ["actionable step 1", ...],
    "severity":        "none | low | medium | high"
}
"""

from __future__ import annotations
import json


# ─────────────────────────────────────────────────────────────
#  SHARED PREAMBLE
# ─────────────────────────────────────────────────────────────

_OUTPUT_INSTRUCTIONS = """
Return ONLY a valid JSON object — no markdown, no explanation outside the JSON.
Schema:
{
    "analysis":        "2–4 sentence factual narrative about what the data shows",
    "issues":          ["specific issue 1", "specific issue 2"],
    "recommendations": ["concrete actionable recommendation 1", "..."],
    "severity":        "none | low | medium | high"
}

Rules:
- "analysis" must be factual and grounded in the numbers provided.
- "issues" must be specific (e.g., "Sleep score averages 5.1/10 with 40% low-sleep nights"
  not "Poor sleep quality").
- "recommendations" must be concrete actions, not vague advice.
  BAD: "Try to sleep better."
  GOOD: "Set a fixed wake-up time at 7 AM daily to regulate circadian rhythm."
- "severity" reflects overall health risk for this domain:
    none   = no concerning pattern
    low    = minor issue, worth monitoring
    medium = clear pattern that needs attention
    high   = urgent concern, possible clinical risk
- If data is sparse, still analyse with a low confidence note in "analysis".
"""


def _user_context_block(ctx: dict) -> str:
    """Render user context as a readable string for the prompt."""
    chronic = ", ".join(ctx.get("chronic_diseases") or []) or "none declared"
    allergies = ", ".join(ctx.get("allergies") or []) or "none declared"
    goals = ", ".join(ctx.get("goals") or []) or "not specified"
    return f"""
USER PROFILE:
  Age            : {ctx.get('age', 'unknown')}
  Gender         : {ctx.get('gender', 'unknown')}
  BMI            : {ctx.get('bmi', 'unknown')} ({ctx.get('bmi_category', '')})
  Chronic diseases: {chronic}
  Allergies       : {allergies}
  Health goals    : {goals}
  Smoker (profile): {ctx.get('smoker', False)}
  Alcoholic (profile): {ctx.get('alcoholic', False)}
""".strip()


# ─────────────────────────────────────────────────────────────
#  DOMAIN PROMPTS
# ─────────────────────────────────────────────────────────────

def prompt_sleep(summary: dict, user_context: dict) -> str:
    return f"""You are a clinical sleep and stress analyst. Analyse the following data for a patient.

{_user_context_block(user_context)}

SLEEP / STRESS / ENERGY SUMMARY (computed from patient logs):
{json.dumps(summary, indent=2)}

Key metrics to interpret:
- avg_sleep_score: 0–10 scale (7+ is healthy, <5 is concerning)
- sleep_variability: std deviation of nightly scores (>2 indicates irregular sleep)
- avg_stress_score: 0–10 (>6 is elevated)
- avg_energy_score: 0–10 (<5 suggests chronic fatigue)
- low_sleep_frequency_pct: % of nights with score ≤4
- sleep_trend: direction of sleep quality over time
- avg_caffeine_cups: daily average (>3 cups may impair sleep)

Consider the patient's chronic diseases and age when assessing risk.
Consider the relationship between caffeine intake and sleep quality.
Consider stress–sleep feedback loops.

{_OUTPUT_INSTRUCTIONS}"""


def prompt_nutrition(summary: dict, user_context: dict) -> str:
    return f"""You are a clinical dietitian. Analyse the following nutritional data for a patient.

{_user_context_block(user_context)}

NUTRITION SUMMARY (computed from patient food logs):
{json.dumps(summary, indent=2)}

Key metrics to interpret:
- avg_daily_calories: healthy range is ~1800–2500 kcal depending on age/gender/BMI
- calorie_variability: high std deviation (>400) suggests inconsistent eating
- avg_hydration_liters: recommended 2.0–2.5 L/day; <1.5 is concerning
- avg_meal_count: <2 meals/day is a red flag for metabolic health
- sugar_intake_distribution: % of days with none/low/medium/high sugar
- no_meal_days_pct: % of days with no logged meals
- avg_caffeine_cups: >3/day can indicate dependency or anxiety-related patterns
- calorie_trend: direction over time

Reference the patient's BMI, chronic diseases (e.g., diabetes → sugar intake critical),
and stated health goals when forming recommendations.

{_OUTPUT_INSTRUCTIONS}"""


def prompt_activity(summary: dict, user_context: dict) -> str:
    return f"""You are a sports medicine physician. Analyse the following physical activity data for a patient.

{_user_context_block(user_context)}

PHYSICAL ACTIVITY SUMMARY (computed from patient activity logs):
{json.dumps(summary, indent=2)}

Key metrics to interpret:
- active_days_per_week: WHO recommends ≥5 days of moderate or ≥3 days of vigorous activity
- avg_duration_minutes: WHO minimum is 150 min/week moderate OR 75 min/week vigorous
- avg_effort_score: MET-hours per session (>3.0 = moderate effort)
- sedentary_days_pct: % of tracked days with no activity (>60% is concerning)
- intensity_distribution: balance of low/medium/high intensity
- effort_trend: improving or worsening over time

Take the patient's age, BMI, and chronic diseases into account.
If sedentary_days_pct is high, specify what type of activity to add and how often.
If already active, identify gaps (e.g., no strength training, only cardio).

{_OUTPUT_INSTRUCTIONS}"""


def prompt_smoking(summary: dict, user_context: dict) -> str:
    return f"""You are a pulmonologist and tobacco cessation specialist. Analyse the following smoking data for a patient.

{_user_context_block(user_context)}

SMOKING SUMMARY (computed from patient tobacco logs):
{json.dumps(summary, indent=2)}

Key metrics to interpret:
- smoking_days: total days tobacco use was logged
- avg_daily_units: average cigarette-equivalents per day
  (1 unit = 1 cigarette = ~15 vape puffs)
- heavy_smoking_days_pct: % of days with >20 units/day (WHO heavy smoker threshold)
- tobacco_type_distribution: types of tobacco used
- smoking_trend: increasing or decreasing pattern
- profile_flag_only: if true, profile says smoker but no daily logs

If no smoking data but profile flags smoker=true, note the data gap and still
give cessation recommendations.
Reference chronic diseases (e.g., COPD, cardiovascular disease) for severity.

{_OUTPUT_INSTRUCTIONS}"""


def prompt_alcohol(summary: dict, user_context: dict) -> str:
    return f"""You are a hepatologist and addiction medicine specialist. Analyse the following alcohol consumption data for a patient.

{_user_context_block(user_context)}

ALCOHOL CONSUMPTION SUMMARY (computed from patient logs):
{json.dumps(summary, indent=2)}

Key metrics to interpret:
- drinking_days: total days with alcohol consumption logged
- avg_glasses_on_drinking_days: average drinks per drinking day
- risky_drinking_days_pct: % of drinking days exceeding 2 standard drinks
  (WHO: >14 drinks/week for men or >7 for women is hazardous)
- drinking_trend: increasing or decreasing
- profile_flag_only: if true, profile flags alcoholic but no logs

Consider liver function implications, interaction with any medications in the
treatment records, and the patient's stated goals.
If no data but profile flags alcoholic=true, flag this as a high-priority gap.

{_OUTPUT_INSTRUCTIONS}"""


def prompt_vital_signs(summary: dict, user_context: dict) -> str:
    return f"""You are a cardiologist. Analyse the following vital signs data for a patient.

{_user_context_block(user_context)}

VITAL SIGNS SUMMARY (computed from patient measurements):
{json.dumps(summary, indent=2)}

Key metrics and clinical thresholds:
- avg_heart_rate: normal 60–100 bpm; <60 = bradycardia risk, >100 = tachycardia risk
- heart_rate_variability: high std (>15 bpm) may indicate arrhythmia risk
- avg_systolic_pressure: normal <120; 120–129 elevated; 130–139 stage 1 HBP; ≥140 stage 2 HBP
- avg_diastolic_pressure: normal <80; 80–89 elevated; ≥90 HBP
- avg_oxygen_saturation: normal ≥95%; 90–94% requires monitoring; <90% critical
- hypertension_episodes_pct: % of readings with systolic ≥140
- tachycardia/bradycardia_episodes_pct: % of readings outside normal HR range
- low_spo2_episodes_pct: % of readings with SpO2 <95%

Consider the patient's age, chronic diseases (especially cardiovascular or respiratory),
and BMI when assessing risk level.

{_OUTPUT_INSTRUCTIONS}"""


def prompt_lab_results(summary: dict, user_context: dict) -> str:
    return f"""You are an internist reviewing laboratory results for a patient.

{_user_context_block(user_context)}

LAB RESULTS SUMMARY (grouped by analysis type, showing trends):
{json.dumps(summary, indent=2)}

For each analysis type in by_type:
- Compare latest_value to standard reference ranges for the patient's age/gender
- Note the trend: "worsening" means the value is moving away from normal range
- Flag values that are likely out of normal range based on the unit and type name

Common thresholds (adjust based on unit):
- blood_glucose / glycémie: normal fasting 3.9–5.6 mmol/L or 70–100 mg/dL
- HbA1c: normal <5.7%; prediabetes 5.7–6.4%; diabetes ≥6.5%
- total_cholesterol: desirable <5.2 mmol/L or <200 mg/dL
- LDL: optimal <2.6 mmol/L or <100 mg/dL
- creatinine: normal 53–106 µmol/L (men), 44–97 µmol/L (women)
- hemoglobin: normal 130–170 g/L (men), 120–160 g/L (women)

Reference the patient's chronic diseases heavily — lab values mean different
things depending on conditions like diabetes, kidney disease, etc.

{_OUTPUT_INSTRUCTIONS}"""


def prompt_treatments(summary: dict, user_context: dict) -> str:
    return f"""You are a clinical pharmacist reviewing a patient's medication adherence.

{_user_context_block(user_context)}

TREATMENT / MEDICATION SUMMARY:
{json.dumps(summary, indent=2)}

Key metrics to interpret:
- total_medications: total prescribed medications tracked
- active_treatments: medications without an end date (currently active)
- avg_adherence_rate: overall average (0.0–1.0); <0.80 = clinically significant non-adherence
- low_adherence_medications: list of meds where adherence < 80%
- adherence_distribution: count of high vs low adherence medications

Clinical context:
- Non-adherence to chronic disease medications (antihypertensives, antidiabetics,
  statins) carries significant risk — identify category from medication name if possible.
- Consider patient's chronic diseases when assessing consequences of non-adherence.
- If adherence is uniformly low across all medications, consider systemic causes
  (complex regimen, side effects, cost) vs. specific medication issues.

{_OUTPUT_INSTRUCTIONS}"""
