"""
Anomaly definitions: dataclass + all rule thresholds.

An Anomaly is a specific, measurable deviation from a clinical threshold
or a dangerous cross-domain pattern detected in the patient data.

Anomalies are distinct from domain analyses:
  - Domain analysis  = overall assessment ("sleep quality is poor overall")
  - Anomaly          = specific flag with evidence ("avg sleep score 3.8 over 45 days
                        — chronic sleep deprivation threshold exceeded")
"""

from __future__ import annotations
from dataclasses import dataclass, field


# ─────────────────────────────────────────────────────────────
#  ANOMALY DATACLASS
# ─────────────────────────────────────────────────────────────

@dataclass
class Anomaly:
    code:        str           # unique rule identifier, e.g. "SLEEP_CHRONIC_DEPRIVATION"
    domain:      str           # primary domain
    severity:    str           # "low" | "medium" | "high" | "critical"
    message:     str           # human-readable description with numbers
    evidence:    dict          # supporting data values that triggered the rule
    domains:     list[str] = field(default_factory=list)  # domains involved (for cross-domain)
    rule_type:   str = "rule_based"  # "rule_based" | "ai_based"

    def to_dict(self) -> dict:
        return {
            "code":      self.code,
            "domain":    self.domain,
            "severity":  self.severity,
            "message":   self.message,
            "evidence":  self.evidence,
            "domains":   self.domains or [self.domain],
            "rule_type": self.rule_type,
        }


# ─────────────────────────────────────────────────────────────
#  THRESHOLDS
#  Grouped by domain — change values here to tune sensitivity.
# ─────────────────────────────────────────────────────────────

class SleepThresholds:
    CHRONIC_DEPRIVATION_SCORE   = 5.0   # avg sleep score below this = chronic deprivation
    CHRONIC_DEPRIVATION_PCT     = 30.0  # AND low-sleep nights above this %
    CRITICAL_SCORE              = 3.0   # avg score this low = critical
    HIGH_VARIABILITY            = 2.5   # std dev above this = highly irregular sleep
    HIGH_STRESS                 = 6.5   # avg stress score above this
    HIGH_CAFFEINE               = 3.0   # avg cups/day above this (linked to poor sleep)
    LOW_ENERGY                  = 4.0   # avg energy score below this = chronic fatigue


class NutritionThresholds:
    MIN_CALORIES                = 1200  # below this = severe restriction (clinical risk)
    MAX_CALORIES                = 3500  # above this = caloric excess
    MIN_HYDRATION               = 1.5   # L/day — below this = chronic dehydration
    CRITICAL_HYDRATION          = 1.0   # L/day — critical dehydration
    MAX_CAFFEINE                = 4.0   # cups/day above this = excessive
    HIGH_SUGAR_DAYS_PCT         = 50.0  # % of days with high sugar intake
    NO_MEAL_DAYS_PCT            = 20.0  # % of days with no meals logged


class ActivityThresholds:
    SEDENTARY_DAYS_PCT          = 80.0  # % of tracked days with no activity
    MIN_ACTIVE_DAYS_PER_WEEK    = 1.0   # fewer than this = essentially sedentary
    WHO_MIN_ACTIVE_DAYS         = 3.0   # WHO minimum (moderate intensity)


class SmokingThresholds:
    HEAVY_SMOKER_UNITS          = 20.0  # cigarette-equivalents/day
    ANY_SMOKING_CHRONIC_DAYS    = 14    # 2+ weeks of logged smoking days = chronic


class AlcoholThresholds:
    RISKY_DAYS_PCT              = 50.0  # % of drinking days exceeding 2 glasses
    HIGH_FREQUENCY_DAYS         = 15    # drinking days above this in a tracking period
    HIGH_AVG_GLASSES            = 4.0   # avg glasses/drinking day above this


class VitalSignsThresholds:
    HYPERTENSION_PCT            = 30.0  # % of readings with systolic >= 140
    HYPERTENSIVE_CRISIS_SYSTOLIC = 180  # immediate danger threshold
    TACHYCARDIA_PCT             = 20.0  # % of readings with HR > 100
    BRADYCARDIA_PCT             = 20.0  # % of readings with HR < 60
    LOW_SPO2_PCT                = 10.0  # % of readings with SpO2 < 95%
    CRITICAL_SPO2               = 92.0  # avg SpO2 below this = critical


class TreatmentThresholds:
    CRITICAL_ADHERENCE          = 0.50  # below 50% = critical non-adherence
    LOW_ADHERENCE               = 0.80  # below 80% = clinically significant


# ─────────────────────────────────────────────────────────────
#  CROSS-DOMAIN RULE DEFINITIONS
#  Each is a tuple: (code, domains, description_template)
#  Evaluation logic lives in detector.py
# ─────────────────────────────────────────────────────────────

CROSS_DOMAIN_RULES = [
    {
        "code":        "CROSS_ALCOHOL_SLEEP",
        "domains":     ["alcohol", "sleep"],
        "description": "Frequent alcohol consumption ({drinking_days} drinking days) combined with "
                       "poor sleep quality (avg score {avg_sleep_score}/10) — alcohol disrupts REM sleep.",
        "severity":    "medium",
        "check": lambda s, a: (
            a.get("drinking_days", 0) >= 10
            and (s.get("avg_sleep_score") or 10) < 6.0
        ),
        "evidence_keys": {"sleep": ["avg_sleep_score"], "alcohol": ["drinking_days", "avg_glasses_on_drinking_days"]},
    },
    {
        "code":        "CROSS_CAFFEINE_SLEEP",
        "domains":     ["sleep", "nutrition"],
        "description": "High caffeine intake ({avg_caffeine_cups} cups/day) alongside "
                       "poor sleep score ({avg_sleep_score}/10) — caffeine half-life may be disrupting sleep onset.",
        "severity":    "medium",
        "check": lambda s, n: (
            (n.get("avg_caffeine_cups") or 0) >= SleepThresholds.HIGH_CAFFEINE
            and (s.get("avg_sleep_score") or 10) < 6.0
        ),
        "evidence_keys": {"sleep": ["avg_sleep_score"], "nutrition": ["avg_caffeine_cups"]},
    },
    {
        "code":        "CROSS_SEDENTARY_HYPERTENSION",
        "domains":     ["activity", "vital_signs"],
        "description": "Sedentary lifestyle ({sedentary_days_pct}% inactive days) combined with "
                       "hypertensive readings ({hypertension_episodes_pct}% of measurements ≥140 mmHg).",
        "severity":    "high",
        "check": lambda a, v: (
            (a.get("sedentary_days_pct") or 0) >= ActivityThresholds.SEDENTARY_DAYS_PCT
            and (v.get("hypertension_episodes_pct") or 0) >= VitalSignsThresholds.HYPERTENSION_PCT
        ),
        "evidence_keys": {"activity": ["sedentary_days_pct"], "vital_signs": ["hypertension_episodes_pct", "avg_systolic_pressure"]},
    },
    {
        "code":        "CROSS_SMOKING_SPO2",
        "domains":     ["smoking", "vital_signs"],
        "description": "Active smoking ({avg_daily_units} units/day) with reduced oxygen saturation "
                       "(SpO2={avg_oxygen_saturation}%, {low_spo2_episodes_pct}% readings <95%) — "
                       "possible early respiratory compromise.",
        "severity":    "high",
        "check": lambda sm, v: (
            (sm.get("avg_daily_units") or 0) > 0
            and (v.get("low_spo2_episodes_pct") or 0) >= VitalSignsThresholds.LOW_SPO2_PCT
        ),
        "evidence_keys": {"smoking": ["avg_daily_units"], "vital_signs": ["avg_oxygen_saturation", "low_spo2_episodes_pct"]},
    },
    {
        "code":        "CROSS_STRESS_SEDENTARY",
        "domains":     ["sleep", "activity"],
        "description": "High chronic stress (avg={avg_stress_score}/10) paired with physical inactivity "
                       "({sedentary_days_pct}% sedentary days) — exercise is a primary stress-reduction mechanism.",
        "severity":    "medium",
        "check": lambda s, a: (
            (s.get("avg_stress_score") or 0) >= SleepThresholds.HIGH_STRESS
            and (a.get("sedentary_days_pct") or 0) >= ActivityThresholds.SEDENTARY_DAYS_PCT
        ),
        "evidence_keys": {"sleep": ["avg_stress_score"], "activity": ["sedentary_days_pct", "active_days_per_week"]},
    },
    {
        "code":        "CROSS_LOW_ADHERENCE_CHRONIC",
        "domains":     ["treatments", "lab_results"],
        "description": "Low medication adherence ({low_adherence_medications}) "
                       "alongside tracked lab results — untreated conditions may worsen over time.",
        "severity":    "high",
        "check": lambda tr, lb: (
            bool(tr.get("low_adherence_medications"))
            and lb.get("total_results", 0) > 0
        ),
        "evidence_keys": {"treatments": ["low_adherence_medications", "avg_adherence_rate"], "lab_results": ["types_tracked"]},
    },
    {
        "code":        "CROSS_ALCOHOL_NUTRITION",
        "domains":     ["alcohol", "nutrition"],
        "description": "Frequent alcohol consumption ({drinking_days} days) with poor hydration "
                       "({avg_hydration_liters}L/day) — alcohol accelerates dehydration.",
        "severity":    "medium",
        "check": lambda al, n: (
            (al.get("drinking_days", 0) >= 10)
            and (n.get("avg_hydration_liters") or 3.0) < NutritionThresholds.MIN_HYDRATION
        ),
        "evidence_keys": {"alcohol": ["drinking_days"], "nutrition": ["avg_hydration_liters"]},
    },
]
