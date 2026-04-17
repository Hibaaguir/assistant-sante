"""
Domain-specific feature extraction / summarization.

Each function receives a normalized domain block:
    { "records": [...], "meta": {...} }

And returns a compact summary dict — the ONLY thing sent to the AI nodes.
No raw records ever leave this layer.

Uses pandas for all statistical operations.
"""

from __future__ import annotations
import pandas as pd
import numpy as np
from typing import Any


# ─────────────────────────────────────────────────────────────
#  HELPERS
# ─────────────────────────────────────────────────────────────

def _round(value: Any, decimals: int = 2) -> float | None:
    """Round a numeric value; return None if not finite."""
    if value is None:
        return None
    try:
        f = float(value)
        if np.isnan(f) or np.isinf(f):
            return None
        return round(f, decimals)
    except (TypeError, ValueError):
        return None


def _pct(count: int, total: int) -> float | None:
    """Return percentage as 0–100 float, or None if total is zero."""
    if total == 0:
        return None
    return _round((count / total) * 100)


def _series(records: list[dict], field: str) -> pd.Series:
    """Extract a numeric pandas Series from a list of dicts, dropping nulls."""
    return pd.Series(
        [r.get(field) for r in records],
        dtype="float64"
    ).dropna()


def _value_counts_pct(records: list[dict], field: str) -> dict[str, float]:
    """Return percentage distribution of a categorical field."""
    vals = [r.get(field) for r in records if r.get(field) is not None]
    if not vals:
        return {}
    s = pd.Series(vals)
    return {
        k: _round((v / len(vals)) * 100)
        for k, v in s.value_counts().to_dict().items()
    }


def _trend(series: pd.Series) -> str:
    """
    Compute a simple linear trend direction for a time-ordered numeric series.
    Returns: 'improving' | 'worsening' | 'stable'
    For sleep/energy: higher = better. Caller must invert for stress etc.
    """
    if len(series) < 3:
        return "insufficient_data"
    x = np.arange(len(series))
    slope = np.polyfit(x, series.values, 1)[0]
    if abs(slope) < 0.05:
        return "stable"
    return "improving" if slope > 0 else "worsening"


# ─────────────────────────────────────────────────────────────
#  DOMAIN SUMMARIZERS
# ─────────────────────────────────────────────────────────────

def summarize_sleep(block: dict) -> dict:
    """
    Output example:
    {
        "avg_sleep_score": 6.4,
        "sleep_variability": 1.8,
        "avg_stress_score": 5.1,
        "avg_energy_score": 5.8,
        "avg_caffeine_cups": 2.1,
        "avg_hydration_liters": 1.9,
        "low_sleep_frequency_pct": 23.3,
        "sleep_trend": "worsening",
        "stress_trend": "stable",
        "days_tracked": 30
    }
    """
    records = block.get("records", [])
    if not records:
        return {"days_tracked": 0, "_empty": True}

    sleep_s   = _series(records, "sleep_score")
    stress_s  = _series(records, "stress_score")
    energy_s  = _series(records, "energy_score")
    caffeine_s = _series(records, "caffeine_cups")
    hydration_s = _series(records, "hydration_liters")

    low_sleep_days = sum(1 for r in records if r.get("low_sleep_flag"))

    # Stress trend: higher stress = worsening (invert slope logic)
    raw_stress_trend = _trend(stress_s)
    stress_trend = (
        "worsening"  if raw_stress_trend == "improving" else
        "improving"  if raw_stress_trend == "worsening" else
        raw_stress_trend
    )

    return {
        "avg_sleep_score":        _round(sleep_s.mean()),
        "sleep_variability":      _round(sleep_s.std()),
        "min_sleep_score":        _round(sleep_s.min()),
        "max_sleep_score":        _round(sleep_s.max()),
        "avg_stress_score":       _round(stress_s.mean()),
        "avg_energy_score":       _round(energy_s.mean()),
        "avg_caffeine_cups":      _round(caffeine_s.mean()),
        "avg_hydration_liters":   _round(hydration_s.mean()),
        "low_sleep_frequency_pct": _pct(low_sleep_days, len(records)),
        "sleep_trend":            _trend(sleep_s),
        "stress_trend":           stress_trend,
        "energy_trend":           _trend(energy_s),
        "days_tracked":           len(records),
    }


def summarize_nutrition(block: dict) -> dict:
    """
    Output example:
    {
        "avg_daily_calories": 1850,
        "calorie_variability": 420,
        "avg_hydration_liters": 2.1,
        "avg_meal_count": 3.2,
        "avg_caffeine_cups": 1.8,
        "sugar_intake_distribution": {"low": 40.0, "medium": 35.0, "high": 25.0},
        "no_meal_days_pct": 5.0,
        "calorie_trend": "stable",
        "days_tracked": 30
    }
    """
    records = block.get("records", [])
    if not records:
        return {"days_tracked": 0, "_empty": True}

    cal_s    = _series(records, "total_calories")
    hydra_s  = _series(records, "hydration_liters")
    meal_s   = _series(records, "meal_count")
    caff_s   = _series(records, "caffeine_cups")
    no_meal_days = sum(1 for r in records if r.get("no_meal_flag"))

    return {
        "avg_daily_calories":       _round(cal_s.mean()),
        "calorie_variability":      _round(cal_s.std()),
        "min_daily_calories":       _round(cal_s.min()),
        "max_daily_calories":       _round(cal_s.max()),
        "avg_hydration_liters":     _round(hydra_s.mean()),
        "avg_meal_count":           _round(meal_s.mean()),
        "avg_caffeine_cups":        _round(caff_s.mean()),
        "sugar_intake_distribution": _value_counts_pct(records, "sugar_intake"),
        "no_meal_days_pct":         _pct(no_meal_days, len(records)),
        "calorie_trend":            _trend(cal_s),
        "days_tracked":             len(records),
    }


def summarize_activity(block: dict, total_tracked_days: int = 0) -> dict:
    """
    Output example:
    {
        "active_days": 18,
        "active_days_per_week": 4.2,
        "avg_duration_minutes": 38,
        "avg_effort_score": 3.2,
        "most_common_activity": "running",
        "intensity_distribution": {"low": 20, "medium": 50, "high": 30},
        "sedentary_days_pct": 40.0,
        "effort_trend": "improving"
    }
    """
    records = block.get("records", [])
    if not records:
        sedentary = _pct(total_tracked_days, total_tracked_days) if total_tracked_days else None
        return {
            "active_days": 0,
            "sedentary_days_pct": 100.0 if total_tracked_days else None,
            "_empty": True,
        }

    # Unique active days (multiple sessions per day are possible)
    active_dates = {r.get("entry_date") for r in records if r.get("entry_date")}
    active_days  = len(active_dates)

    # Date range span to compute per-week rate
    dates_sorted = sorted(active_dates)
    span_days = 1
    if len(dates_sorted) >= 2:
        d0 = pd.to_datetime(dates_sorted[0])
        d1 = pd.to_datetime(dates_sorted[-1])
        span_days = max((d1 - d0).days, 1)
    active_per_week = _round((active_days / span_days) * 7)

    duration_s   = _series(records, "duration_minutes")
    effort_s     = _series(records, "effort_score")
    non_negligible = [r for r in records if not r.get("negligible_flag")]

    # Most common activity
    activity_types = [r.get("activity_type") for r in records if r.get("activity_type")]
    most_common = (
        pd.Series(activity_types).value_counts().idxmax()
        if activity_types else None
    )

    sedentary_pct = _pct(
        max(total_tracked_days - active_days, 0), total_tracked_days
    ) if total_tracked_days else None

    return {
        "active_days":             active_days,
        "active_days_per_week":    active_per_week,
        "avg_duration_minutes":    _round(duration_s.mean()),
        "max_duration_minutes":    _round(duration_s.max()),
        "avg_effort_score":        _round(effort_s.mean()),
        "most_common_activity":    most_common,
        "intensity_distribution":  _value_counts_pct(records, "intensity"),
        "sedentary_days_pct":      sedentary_pct,
        "effort_trend":            _trend(effort_s),
        "sessions_tracked":        len(records),
    }


def summarize_smoking(block: dict) -> dict:
    """
    Output example:
    {
        "smoking_days": 22,
        "avg_daily_units": 12.5,
        "heavy_smoking_days_pct": 18.0,
        "tobacco_type_distribution": {"cigarette": 80.0, "vape": 20.0},
        "smoking_trend": "stable"
    }
    """
    records = block.get("records", [])
    if not records or all(r.get("_note") for r in records):
        # Only profile flag, no real logs
        profile_only = any(r.get("_note") for r in records)
        return {
            "smoking_days": 0,
            "profile_flag_only": profile_only,
            "_empty": True,
        }

    units_s = _series(records, "daily_units")
    heavy_days = sum(1 for r in records if r.get("heavy_smoker_flag"))

    return {
        "smoking_days":               len(records),
        "avg_daily_units":            _round(units_s.mean()),
        "max_daily_units":            _round(units_s.max()),
        "heavy_smoking_days_pct":     _pct(heavy_days, len(records)),
        "tobacco_type_distribution":  _value_counts_pct(records, "tobacco_type"),
        "smoking_trend":              _trend(units_s),
    }


def summarize_alcohol(block: dict) -> dict:
    """
    Output example:
    {
        "drinking_days": 14,
        "avg_glasses_on_drinking_days": 2.3,
        "risky_drinking_days_pct": 42.8,
        "drinking_trend": "worsening"
    }
    """
    records = block.get("records", [])
    if not records or all(r.get("_note") for r in records):
        profile_only = any(r.get("_note") for r in records)
        return {
            "drinking_days": 0,
            "profile_flag_only": profile_only,
            "_empty": True,
        }

    glasses_s  = _series(records, "glasses")
    risky_days = sum(1 for r in records if r.get("risky_flag"))

    return {
        "drinking_days":               len(records),
        "avg_glasses_on_drinking_days": _round(glasses_s.mean()),
        "max_glasses_in_one_day":       _round(glasses_s.max()),
        "risky_drinking_days_pct":      _pct(risky_days, len(records)),
        "drinking_trend":               _trend(glasses_s),
    }


def summarize_vital_signs(block: dict) -> dict:
    """
    Output example:
    {
        "avg_heart_rate": 74,
        "heart_rate_variability": 8.2,
        "avg_systolic": 128,
        "avg_diastolic": 82,
        "avg_spo2": 97.4,
        "hypertension_episodes_pct": 15.0,
        "tachycardia_episodes_pct": 5.0,
        "low_spo2_episodes_pct": 0.0,
        "measurements_tracked": 45
    }
    """
    records = block.get("records", [])
    if not records:
        return {"measurements_tracked": 0, "_empty": True}

    hr_s   = _series(records, "heart_rate")
    sys_s  = _series(records, "systolic_pressure")
    dia_s  = _series(records, "diastolic_pressure")
    spo2_s = _series(records, "oxygen_saturation")

    hypert    = sum(1 for r in records if r.get("hypertension_flag"))
    tachy     = sum(1 for r in records if r.get("tachycardia_flag"))
    brady     = sum(1 for r in records if r.get("bradycardia_flag"))
    low_spo2  = sum(1 for r in records if r.get("low_spo2_flag"))
    n = len(records)

    return {
        "avg_heart_rate":              _round(hr_s.mean()),
        "heart_rate_variability":      _round(hr_s.std()),
        "avg_systolic_pressure":       _round(sys_s.mean()),
        "avg_diastolic_pressure":      _round(dia_s.mean()),
        "avg_oxygen_saturation":       _round(spo2_s.mean()),
        "hypertension_episodes_pct":   _pct(hypert, n),
        "tachycardia_episodes_pct":    _pct(tachy, n),
        "bradycardia_episodes_pct":    _pct(brady, n),
        "low_spo2_episodes_pct":       _pct(low_spo2, n),
        "measurements_tracked":        n,
    }


def summarize_lab_results(block: dict) -> dict:
    """
    Groups results by analysis_type.
    For each type: latest value, mean, trend.

    Output example:
    {
        "by_type": {
            "blood_glucose": {
                "latest_value": 5.6,
                "unit": "mmol/L",
                "mean": 5.4,
                "trend": "stable",
                "count": 4
            },
            ...
        },
        "types_tracked": ["blood_glucose", "cholesterol"],
        "total_results": 8
    }
    """
    records = block.get("records", [])
    if not records:
        return {"types_tracked": [], "total_results": 0, "_empty": True}

    df = pd.DataFrame(records)
    by_type = {}

    for analysis_type, group in df.groupby("analysis_type"):
        group_sorted = group.sort_values("analysis_date")
        values = pd.to_numeric(group_sorted["value"], errors="coerce").dropna()
        latest_row = group_sorted.iloc[-1]

        by_type[analysis_type] = {
            "latest_value": _round(float(latest_row["value"])) if pd.notna(latest_row["value"]) else None,
            "result_name":  latest_row.get("result_name") or None,
            "unit":         latest_row.get("unit") or None,
            "mean":         _round(values.mean()),
            "std":          _round(values.std()),
            "trend":        _trend(values),
            "count":        len(group),
        }

    return {
        "by_type":       by_type,
        "types_tracked": list(by_type.keys()),
        "total_results": len(records),
    }


def summarize_treatments(block: dict) -> dict:
    """
    Output example:
    {
        "total_medications": 3,
        "avg_adherence_rate": 0.87,
        "low_adherence_medications": ["metformin"],
        "adherence_distribution": {
            "high (>=80%)": 2,
            "low (<80%)": 1
        },
        "active_treatments": 2
    }
    """
    records = block.get("records", [])
    if not records:
        return {"total_medications": 0, "_empty": True}

    adherence_s = _series(records, "adherence_rate")
    low_adh = [
        r.get("medication_name")
        for r in records
        if r.get("low_adherence_flag")
    ]

    # Active = no end_date or end_date in future (simple check: end_date is None)
    active = [r for r in records if not r.get("end_date")]

    high_count = sum(1 for r in records if not r.get("low_adherence_flag") and r.get("adherence_rate") is not None)
    low_count  = len(low_adh)

    return {
        "total_medications":         len(records),
        "active_treatments":         len(active),
        "avg_adherence_rate":        _round(adherence_s.mean()),
        "min_adherence_rate":        _round(adherence_s.min()),
        "low_adherence_medications": low_adh,
        "adherence_distribution": {
            "high_>=80pct": high_count,
            "low_<80pct":   low_count,
        },
    }


def summarize_user_context(profile: dict) -> dict:
    """
    Extract key user context facts that should accompany all AI node prompts.
    This gives each node just enough profile context without raw data.
    """
    return {
        "age":              profile.get("age"),
        "gender":           profile.get("gender"),
        "bmi":              profile.get("bmi"),
        "bmi_category":     profile.get("bmi_category"),
        "blood_type":       profile.get("blood_type"),
        "chronic_diseases": profile.get("chronic_diseases", []),
        "allergies":        profile.get("allergies", []),
        "goals":            profile.get("goals", []),
        "smoker":           profile.get("smoker", False),
        "alcoholic":        profile.get("alcoholic", False),
    }
