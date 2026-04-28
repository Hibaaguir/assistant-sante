"""Normalize and summarize raw DB data into compact dicts for the AI."""

from __future__ import annotations
import math
import statistics
from collections import Counter
from datetime import date as Date
from typing import Any


# ── Math helpers ───────────────────────────────────────────────

def _safe_num(v: Any, default=None) -> float | None:
    if v is None:
        return default
    try:
        f = float(v)
        return None if (math.isnan(f) or math.isinf(f)) else f
    except (TypeError, ValueError):
        return default


def _clamp(v, lo, hi):
    return None if v is None else max(lo, min(hi, v))


def _nums(records: list[dict], field: str) -> list[float]:
    return [f for r in records if (f := _safe_num(r.get(field))) is not None]


def _avg(vals: list[float]) -> float | None:
    return round(statistics.mean(vals), 2) if vals else None


def _std(vals: list[float]) -> float | None:
    return round(statistics.stdev(vals), 2) if len(vals) >= 2 else None


def _pct(n: int, total: int) -> float | None:
    return None if total == 0 else round(n / total * 100, 2)


def _trend(vals: list[float]) -> str:
    if len(vals) < 3:
        return "insufficient_data"
    n = len(vals)
    xm = (n - 1) / 2
    ym = statistics.mean(vals)
    num = sum((i - xm) * (v - ym) for i, v in enumerate(vals))
    den = sum((i - xm) ** 2 for i in range(n))
    if den == 0:
        return "stable"
    slope = num / den
    return "stable" if abs(slope) < 0.05 else ("improving" if slope > 0 else "worsening")


def _dist(records: list[dict], field: str) -> dict:
    vals = [r.get(field) for r in records if r.get(field)]
    if not vals:
        return {}
    total = len(vals)
    return {k: round(v / total * 100, 2) for k, v in Counter(vals).most_common()}


# ── Lookup tables ──────────────────────────────────────────────

_INTENSITY_MAP = {
    "low": "low", "light": "low", "easy": "low", "mild": "low",
    "faible": "low", "légère": "low", "légere": "low", "facile": "low",
    "medium": "medium", "moderate": "medium", "normal": "medium",
    "moyen": "medium", "moyenne": "medium", "modéré": "medium", "modere": "medium",
    "high": "high", "hard": "high", "intense": "high", "vigorous": "high",
    "élevée": "high", "elevee": "high", "fort": "high",
}

_EFFORT = {"low": 1.5, "medium": 3.0, "high": 5.0}

_SUGAR_MAP = {
    "none": "none", "aucun": "none", "aucune": "none",
    "low": "low", "faible": "low", "peu": "low",
    "medium": "medium", "moyen": "medium", "moyenne": "medium", "modéré": "medium",
    "high": "high", "élevé": "high", "eleve": "high", "beaucoup": "high",
}


# ── Normalizers (field rename + flags) ────────────────────────

def _norm_sleep(records: list[dict]) -> list[dict]:
    out = []
    for r in records:
        s = _clamp(_safe_num(r.get("sleep")), 0, 10)
        out.append({
            "sleep_score":    s,
            "stress_score":   _clamp(_safe_num(r.get("stress")), 0, 10),
            "energy_score":   _clamp(_safe_num(r.get("energy")), 0, 10),
            "caffeine_cups":  _clamp(_safe_num(r.get("caffeine"), 0), 0, 20),
            "low_sleep_flag": s is not None and s <= 4,
        })
    return out


def _norm_nutrition(records: list[dict]) -> list[dict]:
    out = []
    for r in records:
        cal = _safe_num(r.get("total_calories"), 0)
        out.append({
            "total_calories":   _clamp(cal, 0, 10000),
            "hydration_liters": _clamp(_safe_num(r.get("hydration"), 0), 0, 10),
            "caffeine_cups":    _clamp(_safe_num(r.get("caffeine"), 0), 0, 20),
            "meal_count":       max(0, int(r.get("meal_count") or 0)),
            "sugar_intake":     _SUGAR_MAP.get((r.get("sugar_intake") or "").lower().strip()),
            "no_meal_flag":     not r.get("total_calories"),
        })
    return out


def _norm_activity(records: list[dict]) -> list[dict]:
    result = []
    for r in records:
        intensity = _INTENSITY_MAP.get((r.get("intensity") or "").lower().strip(), "medium")
        result.append({
            "entry_date":       r.get("entry_date"),
            "activity_type":    r.get("activity_type"),
            "duration_minutes": _clamp(_safe_num(r.get("duration_minutes")), 0, 600),
            "intensity":        intensity,
            "effort_score":     _EFFORT.get(intensity, 3.0),
        })
    return result


def _norm_smoking(records: list[dict]) -> list[dict]:
    out = []
    for r in records:
        cigs  = _safe_num(r.get("cigarettes_per_day"), 0) or 0
        puffs = _safe_num(r.get("puffs_per_day"), 0) or 0
        units = cigs + puffs / 15
        out.append({
            "tobacco_type":      r.get("tobacco_type"),
            "daily_units":       round(units, 1),
            "heavy_smoker_flag": units > 20,
        })
    return out


def _norm_alcohol(records: list[dict]) -> list[dict]:
    return [{
        "glasses":     _clamp(_safe_num(r.get("alcohol_glasses"), 0), 0, 50),
        "risky_flag":  (r.get("alcohol_glasses") or 0) > 2,
    } for r in records]


def _norm_vitals(records: list[dict]) -> list[dict]:
    return [{
        "heart_rate":           _clamp(_safe_num(r.get("heart_rate")), 0, 300),
        "systolic_pressure":    _clamp(_safe_num(r.get("systolic_pressure")), 0, 300),
        "diastolic_pressure":   _clamp(_safe_num(r.get("diastolic_pressure")), 0, 200),
        "oxygen_saturation":    _clamp(_safe_num(r.get("oxygen_saturation")), 0, 100),
        "hypertension_flag":    (r.get("systolic_pressure") or 0) >= 140,
        "tachycardia_flag":     (r.get("heart_rate") or 0) > 100,
        "bradycardia_flag":     0 < (r.get("heart_rate") or 0) < 60,
        "low_spo2_flag":        0 < (r.get("oxygen_saturation") or 0) < 95,
    } for r in records]


def _norm_treatments(records: list[dict]) -> list[dict]:
    return [{
        **r,
        "low_adherence_flag": r.get("adherence_rate") is not None and r["adherence_rate"] < 0.8,
    } for r in records]


# ── Summarizers ────────────────────────────────────────────────

def _summarize_sleep(records: list[dict]) -> dict:
    if not records:
        return {"days_tracked": 0}
    sleep_v  = _nums(records, "sleep_score")
    stress_v = _nums(records, "stress_score")
    raw_trend = _trend(stress_v)
    stress_trend = {"improving": "worsening", "worsening": "improving"}.get(raw_trend, raw_trend)
    return {
        "avg_sleep_score":         _avg(sleep_v),
        "sleep_variability":       _std(sleep_v),
        "avg_stress_score":        _avg(stress_v),
        "avg_energy_score":        _avg(_nums(records, "energy_score")),
        "avg_caffeine_cups":       _avg(_nums(records, "caffeine_cups")),
        "low_sleep_frequency_pct": _pct(sum(1 for r in records if r.get("low_sleep_flag")), len(records)),
        "sleep_trend":             _trend(sleep_v),
        "stress_trend":            stress_trend,
        "energy_trend":            _trend(_nums(records, "energy_score")),
        "days_tracked":            len(records),
    }


def _summarize_nutrition(records: list[dict]) -> dict:
    if not records:
        return {"days_tracked": 0}
    cal_v = _nums(records, "total_calories")
    return {
        "avg_daily_calories":        _avg(cal_v),
        "calorie_variability":       _std(cal_v),
        "avg_hydration_liters":      _avg(_nums(records, "hydration_liters")),
        "avg_meal_count":            _avg(_nums(records, "meal_count")),
        "avg_caffeine_cups":         _avg(_nums(records, "caffeine_cups")),
        "sugar_intake_distribution": _dist(records, "sugar_intake"),
        "no_meal_days_pct":          _pct(sum(1 for r in records if r.get("no_meal_flag")), len(records)),
        "calorie_trend":             _trend(cal_v),
        "days_tracked":              len(records),
    }


def _summarize_activity(records: list[dict]) -> dict:
    if not records:
        return {"active_days": 0, "sedentary_days_pct": None}
    dates = {r.get("entry_date") for r in records if r.get("entry_date")}
    dates_sorted = sorted(str(d) for d in dates if d)
    # +1 to include both endpoints (Mon→Sun = 7 days, not 6)
    span_days = 1
    if len(dates_sorted) >= 2:
        d0 = Date.fromisoformat(dates_sorted[0])
        d1 = Date.fromisoformat(dates_sorted[-1])
        span_days = max((d1 - d0).days + 1, 1)
    types = [r.get("activity_type") for r in records if r.get("activity_type")]
    active_days_count = len(dates)
    active_days_per_week = round((active_days_count / span_days) * 7, 2)
    avg_dur = _avg(_nums(records, "duration_minutes"))
    weekly_minutes = round(avg_dur * active_days_per_week) if avg_dur is not None else None
    return {
        "active_days":             active_days_count,
        "tracking_period_days":    span_days,
        "active_days_per_week":    active_days_per_week,
        "meets_who_threshold":     active_days_per_week >= 5,
        "estimated_weekly_minutes": weekly_minutes,
        "avg_duration_minutes":    avg_dur,
        "avg_effort_score":        _avg(_nums(records, "effort_score")),
        "most_common_activity":    Counter(types).most_common(1)[0][0] if types else None,
        "intensity_distribution":  _dist(records, "intensity"),
        "effort_trend":            _trend(_nums(records, "effort_score")),
        "sessions_tracked":        len(records),
    }


def _summarize_smoking(records: list[dict]) -> dict:
    if not records:
        return {"smoking_days": 0}
    units_v = _nums(records, "daily_units")
    return {
        "smoking_days":              len(records),
        "avg_daily_units":           _avg(units_v),
        "heavy_smoking_days_pct":    _pct(sum(1 for r in records if r.get("heavy_smoker_flag")), len(records)),
        "tobacco_type_distribution": _dist(records, "tobacco_type"),
        "smoking_trend":             _trend(units_v),
    }


def _summarize_alcohol(records: list[dict]) -> dict:
    if not records:
        return {"drinking_days": 0}
    glasses_v = _nums(records, "glasses")
    return {
        "drinking_days":                len(records),
        "avg_glasses_on_drinking_days": _avg(glasses_v),
        "max_glasses_in_one_day":       round(max(glasses_v), 2) if glasses_v else None,
        "risky_drinking_days_pct":      _pct(sum(1 for r in records if r.get("risky_flag")), len(records)),
        "drinking_trend":               _trend(glasses_v),
    }


def _summarize_vitals(records: list[dict]) -> dict:
    if not records:
        return {"measurements_tracked": 0}
    n = len(records)
    return {
        "avg_heart_rate":            _avg(_nums(records, "heart_rate")),
        "heart_rate_variability":    _std(_nums(records, "heart_rate")),
        "avg_systolic_pressure":     _avg(_nums(records, "systolic_pressure")),
        "avg_diastolic_pressure":    _avg(_nums(records, "diastolic_pressure")),
        "avg_oxygen_saturation":     _avg(_nums(records, "oxygen_saturation")),
        "hypertension_episodes_pct": _pct(sum(1 for r in records if r.get("hypertension_flag")), n),
        "tachycardia_episodes_pct":  _pct(sum(1 for r in records if r.get("tachycardia_flag")), n),
        "bradycardia_episodes_pct":  _pct(sum(1 for r in records if r.get("bradycardia_flag")), n),
        "low_spo2_episodes_pct":     _pct(sum(1 for r in records if r.get("low_spo2_flag")), n),
        "measurements_tracked":      n,
    }


def _summarize_labs(records: list[dict]) -> dict:
    if not records:
        return {"types_tracked": [], "total_results": 0}
    groups: dict[str, list] = {}
    for r in records:
        groups.setdefault(r.get("analysis_type", "unknown"), []).append(r)
    by_type = {}
    for atype, group in groups.items():
        sorted_g = sorted(group, key=lambda r: r.get("analysis_date") or "")
        vals = _nums(sorted_g, "value")
        latest = sorted_g[-1]
        by_type[atype] = {
            "latest_value": _safe_num(latest.get("value")),
            "result_name":  latest.get("result_name"),
            "unit":         latest.get("unit"),
            "mean":         _avg(vals),
            "trend":        _trend(vals),
            "count":        len(group),
        }
    return {"by_type": by_type, "types_tracked": list(by_type.keys()), "total_results": len(records)}


def _summarize_treatments(records: list[dict]) -> dict:
    if not records:
        return {"total_medications": 0}
    adh_v   = _nums(records, "adherence_rate")
    low_adh = [r.get("medication_name") for r in records if r.get("low_adherence_flag")]
    return {
        "total_medications":         len(records),
        "active_treatments":         sum(1 for r in records if not r.get("end_date")),
        "avg_adherence_rate":        _avg(adh_v),
        "min_adherence_rate":        round(min(adh_v), 2) if adh_v else None,
        "low_adherence_medications": low_adh,
        "adherence_distribution": {
            "high_>=80pct": len(records) - len(low_adh),
            "low_<80pct":   len(low_adh),
        },
    }


# ── Public API ─────────────────────────────────────────────────

def process_user_data(raw: dict) -> dict:
    """Normalize and summarize raw DB data. Returns {summaries, user_context}."""
    profile = raw.get("user_profile", {})

    # Compute BMI from height/weight if available
    height_cm = _safe_num(profile.get("height"))
    weight_kg = _safe_num(profile.get("current_weight"))
    bmi, bmi_category = None, None
    if height_cm and weight_kg and height_cm > 0:
        bmi = round(weight_kg / (height_cm / 100) ** 2, 1)
        bmi_category = (
            "underweight" if bmi < 18.5 else
            "normal"      if bmi < 25.0 else
            "overweight"  if bmi < 30.0 else
            "obese"
        )

    return {
        "summaries": {
            "sleep":       _summarize_sleep(_norm_sleep(raw.get("sleep", []))),
            "nutrition":   _summarize_nutrition(_norm_nutrition(raw.get("nutrition", []))),
            "activity":    _summarize_activity(_norm_activity(raw.get("activity", []))),
            "smoking":     _summarize_smoking(_norm_smoking(raw.get("smoking", []))),
            "alcohol":     _summarize_alcohol(_norm_alcohol(raw.get("alcohol", []))),
            "vital_signs": _summarize_vitals(_norm_vitals(raw.get("vital_signs", []))),
            "lab_results": _summarize_labs(raw.get("lab_results", [])),
            "treatments":  _summarize_treatments(_norm_treatments(raw.get("treatments", []))),
        },
        "user_context": {
            "age":              profile.get("age"),
            "gender":           profile.get("gender"),
            "bmi":              bmi,
            "bmi_category":     bmi_category,
            "blood_type":       profile.get("blood_type"),
            "chronic_diseases": profile.get("chronic_diseases", []),
            "allergies":        profile.get("allergies", []),
            "goals":            profile.get("goals", []),
            "smoker":           profile.get("smoker", False),
            "alcoholic":        profile.get("alcoholic", False),
        },
    }
