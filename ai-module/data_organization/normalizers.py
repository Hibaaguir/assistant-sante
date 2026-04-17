"""
Domain-specific normalization functions.

Each function receives the raw list of dicts for its domain (from the extractor)
and returns a cleaned, validated, consistently-typed list — no nulls left
ambiguous, no impossible values, no mixed types.

Normalization rules are logic-based (derived from medical/domain knowledge),
not hardcoded per user.
"""

from __future__ import annotations
from typing import Any


# ─────────────────────────────────────────────────────────────
#  GENERIC HELPERS
# ─────────────────────────────────────────────────────────────

def _safe_int(value: Any, default: int | None = None) -> int | None:
    """Cast to int safely; return default on failure."""
    if value is None:
        return default
    try:
        return int(value)
    except (ValueError, TypeError):
        return default


def _safe_float(value: Any, default: float | None = None) -> float | None:
    """Cast to float safely; return default on failure."""
    if value is None:
        return default
    try:
        return float(value)
    except (ValueError, TypeError):
        return default


def _clamp(value: float | int | None, lo: float, hi: float) -> float | None:
    """Return value clamped to [lo, hi]; return None if value is None."""
    if value is None:
        return None
    return max(lo, min(hi, value))


def _normalize_intensity(raw: str | None) -> str:
    """
    Map intensity strings to a canonical 3-level scale: low / medium / high.
    Handles French and English variants from the database.
    """
    if not raw:
        return "medium"
    mapping = {
        # English
        "low": "low", "light": "low", "easy": "low", "mild": "low",
        "medium": "medium", "moderate": "medium", "normal": "medium",
        "high": "high", "hard": "high", "intense": "high", "vigorous": "high",
        # French
        "faible": "low", "légère": "low", "légere": "low", "facile": "low",
        "moyen": "medium", "moyenne": "medium", "modéré": "medium", "modere": "medium",
        "élevée": "high", "elevee": "high", "intense": "high", "fort": "high",
    }
    return mapping.get(raw.lower().strip(), "medium")


def _normalize_sugar_intake(raw: str | None) -> str | None:
    """Map sugar_intake strings to canonical: none / low / medium / high."""
    if not raw:
        return None
    mapping = {
        "none": "none", "aucun": "none", "aucune": "none",
        "low": "low", "faible": "low", "peu": "low",
        "medium": "medium", "moyen": "medium", "moyenne": "medium", "modéré": "medium",
        "high": "high", "élevé": "high", "eleve": "high", "beaucoup": "high",
    }
    return mapping.get(raw.lower().strip(), raw.lower().strip())


# ─────────────────────────────────────────────────────────────
#  DOMAIN NORMALIZERS
# ─────────────────────────────────────────────────────────────

def normalize_sleep(records: list[dict]) -> list[dict]:
    """
    Clean sleep/stress/energy records.

    Scales expected: 0–10 (from journal_entries).
    Caffeine: cups/day, must be >= 0.
    Hydration: liters, must be >= 0.
    """
    result = []
    for r in records:
        sleep_score = _clamp(_safe_int(r.get("sleep")), 0, 10)
        result.append({
            "entry_date":   r.get("entry_date"),
            "sleep_score":  sleep_score,                          # 0–10
            "stress_score": _clamp(_safe_int(r.get("stress")), 0, 10),
            "energy_score": _clamp(_safe_int(r.get("energy")), 0, 10),
            "caffeine_cups": _clamp(_safe_int(r.get("caffeine"), 0), 0, 20),
            "hydration_liters": _clamp(_safe_float(r.get("hydration"), 0.0), 0.0, 20.0),
            # Flag entries with very low sleep for quick access downstream
            "low_sleep_flag": sleep_score is not None and sleep_score <= 4,
        })
    return result


def normalize_nutrition(records: list[dict]) -> list[dict]:
    """
    Clean nutrition records.

    Calories: must be in realistic range (0–6000 kcal/day).
    Hydration: 0–20 L.
    """
    result = []
    for r in records:
        calories = _safe_int(r.get("total_calories"))
        # Discard physiologically impossible values
        if calories is not None and (calories < 0 or calories > 6000):
            calories = None

        result.append({
            "entry_date":       r.get("entry_date"),
            "total_calories":   calories,
            "meal_count":       _clamp(_safe_int(r.get("meal_count"), 0), 0, 20),
            "hydration_liters": _clamp(_safe_float(r.get("hydration"), 0.0), 0.0, 20.0),
            "sugar_intake":     _normalize_sugar_intake(r.get("sugar_intake")),
            "caffeine_cups":    _clamp(_safe_int(r.get("caffeine"), 0), 0, 20),
            "meals":            r.get("meals", []),
            # Flag days with zero meals recorded
            "no_meal_flag":     _safe_int(r.get("meal_count"), 0) == 0,
        })
    return result


def normalize_activity(records: list[dict]) -> list[dict]:
    """
    Clean physical activity records.

    Duration: 0–480 minutes (8h max per session).
    Intensity mapped to canonical scale.
    """
    result = []
    for r in records:
        duration = _clamp(_safe_int(r.get("duration_minutes")), 0, 480)
        intensity = _normalize_intensity(r.get("intensity"))

        # Compute a simple MET-based effort score (dimensionless, for Step 3)
        met_map = {"low": 3.0, "medium": 5.0, "high": 8.0}
        effort_score = None
        if duration is not None:
            effort_score = round((met_map[intensity] * duration) / 60, 2)

        result.append({
            "entry_date":       r.get("entry_date"),
            "activity_type":    (r.get("activity_type") or "unknown").lower().strip(),
            "duration_minutes": duration,
            "intensity":        intensity,
            "effort_score":     effort_score,   # MET-hours, useful for Step 3
            # Flag sessions shorter than 10 minutes as negligible
            "negligible_flag":  duration is not None and duration < 10,
        })
    return result


def normalize_smoking(records: list[dict], smoker_flag: bool = False) -> list[dict]:
    """
    Clean tobacco records.

    Normalizes both cigarettes and vape puffs to a common 'daily_units' field
    so downstream processing can treat them uniformly.
    Puffs are converted at ~1 cigarette = 15 puffs (standard equivalence).
    """
    result = []
    for r in records:
        cigs = _clamp(_safe_int(r.get("cigarettes_per_day")), 0, 200)
        puffs = _clamp(_safe_int(r.get("puffs_per_day")), 0, 1000)
        tobacco_type = (r.get("tobacco_type") or "cigarette").lower().strip()

        # Unified daily units
        if cigs is not None:
            daily_units = cigs
        elif puffs is not None:
            daily_units = round(puffs / 15, 1)
        else:
            daily_units = None

        result.append({
            "entry_date":   r.get("entry_date"),
            "tobacco_type": tobacco_type,
            "cigarettes_per_day": cigs,
            "puffs_per_day": puffs,
            "daily_units":  daily_units,   # normalized equivalent cigarettes
            # Heavy smoker: > 20 cig-equivalents/day
            "heavy_smoker_flag": daily_units is not None and daily_units > 20,
        })

    # If health profile says smoker but no tobacco records logged, add a marker
    if not result and smoker_flag:
        result.append({
            "entry_date":   None,
            "tobacco_type": "unknown",
            "cigarettes_per_day": None,
            "puffs_per_day": None,
            "daily_units":  None,
            "heavy_smoker_flag": False,
            "_note": "Profile marks user as smoker but no daily logs found",
        })

    return result


def normalize_alcohol(records: list[dict], alcoholic_flag: bool = False) -> list[dict]:
    """
    Clean alcohol records.

    Glasses: 0–30 per day (medical upper bound for logging).
    WHO risky threshold: > 2 standard drinks/day.
    """
    result = []
    for r in records:
        glasses = _clamp(_safe_int(r.get("alcohol_glasses")), 0, 30)
        result.append({
            "entry_date":  r.get("entry_date"),
            "consumed":    bool(r.get("alcohol", False)),
            "glasses":     glasses,
            # WHO risky drinking: > 2 glasses/day for women, > 3 for men
            # We use 2 as a conservative universal threshold
            "risky_flag":  glasses is not None and glasses > 2,
        })

    if not result and alcoholic_flag:
        result.append({
            "entry_date":  None,
            "consumed":    True,
            "glasses":     None,
            "risky_flag":  False,
            "_note": "Profile marks user as alcoholic but no daily logs found",
        })

    return result


def normalize_vital_signs(records: list[dict]) -> list[dict]:
    """
    Clean vital sign measurements.

    Clinical reference ranges used for flag generation:
    - Heart rate: 40–200 bpm
    - Systolic BP: 60–250 mmHg
    - Diastolic BP: 40–150 mmHg
    - SpO2: 70–100 %
    """
    result = []
    for r in records:
        hr  = _clamp(_safe_int(r.get("heart_rate")),          40, 200)
        sys = _clamp(_safe_int(r.get("systolic_pressure")),   60, 250)
        dia = _clamp(_safe_int(r.get("diastolic_pressure")),  40, 150)
        spo2 = _clamp(_safe_int(r.get("oxygen_saturation")), 70, 100)

        result.append({
            "date":                r.get("date"),
            "measured_at":         r.get("measured_at"),
            "heart_rate":          hr,
            "systolic_pressure":   sys,
            "diastolic_pressure":  dia,
            "oxygen_saturation":   spo2,
            # Flags based on standard clinical thresholds
            "hypertension_flag":   sys is not None and sys >= 140,
            "bradycardia_flag":    hr  is not None and hr < 60,
            "tachycardia_flag":    hr  is not None and hr > 100,
            "low_spo2_flag":       spo2 is not None and spo2 < 95,
        })
    return result


def normalize_lab_results(records: list[dict]) -> list[dict]:
    """
    Clean lab/analysis results.

    Groups by analysis_type for easy downstream lookup.
    Values are kept as-is (units vary too much per test to normalize uniformly).
    """
    result = []
    for r in records:
        value = _safe_float(r.get("value"))
        result.append({
            "date":          r.get("date"),
            "analysis_date": r.get("analysis_date"),
            "analysis_type": (r.get("analysis_type") or "unknown").strip(),
            "result_name":   (r.get("result_name") or "").strip() or None,
            "value":         value,
            "unit":          (r.get("unit") or "").strip() or None,
        })
    return result


def normalize_treatments(records: list[dict]) -> list[dict]:
    """
    Clean treatment/medication records.

    Adherence rate: 0.0–1.0 (already computed by extractor).
    Flags low adherence (< 0.8 = missing > 20% of doses).
    """
    result = []
    for r in records:
        adherence = _clamp(_safe_float(r.get("adherence_rate")), 0.0, 1.0)
        result.append({
            "medication_name":     (r.get("medication_name") or "unknown").strip(),
            "medication_category": (r.get("medication_category") or "").strip() or None,
            "dose":                r.get("dose"),
            "frequency":           r.get("frequency"),
            "daily_doses":         _safe_int(r.get("daily_doses")),
            "start_date":          r.get("start_date"),
            "end_date":            r.get("end_date"),
            "total_checks":        _safe_int(r.get("total_checks"), 0),
            "taken_count":         _safe_int(r.get("taken_count"), 0),
            "adherence_rate":      adherence,
            # Low adherence threshold: < 80 %
            "low_adherence_flag":  adherence is not None and adherence < 0.80,
        })
    return result


def normalize_user_profile(profile: dict) -> dict:
    """
    Clean and enrich the user profile.

    Computes BMI if height + weight are available.
    """
    if not profile:
        return {}

    height_cm = _safe_float(profile.get("height"))
    weight_kg  = _safe_float(profile.get("current_weight"))

    bmi = None
    bmi_category = None
    if height_cm and weight_kg and height_cm > 0:
        height_m = height_cm / 100
        bmi = round(weight_kg / (height_m ** 2), 1)
        if bmi < 18.5:
            bmi_category = "underweight"
        elif bmi < 25.0:
            bmi_category = "normal"
        elif bmi < 30.0:
            bmi_category = "overweight"
        else:
            bmi_category = "obese"

    return {
        "user_id":          profile.get("user_id"),
        "name":             profile.get("name"),
        "age":              _safe_int(profile.get("age")),
        "date_of_birth":    profile.get("date_of_birth"),
        "gender":           profile.get("gender"),
        "height_cm":        height_cm,
        "weight_kg":        weight_kg,
        "bmi":              bmi,
        "bmi_category":     bmi_category,
        "blood_type":       profile.get("blood_type"),
        "goals":            profile.get("goals", []),
        "allergies":        profile.get("allergies", []),
        "chronic_diseases": profile.get("chronic_diseases", []),
        "smoker":           bool(profile.get("smoker", False)),
        "alcoholic":        bool(profile.get("alcoholic", False)),
    }
