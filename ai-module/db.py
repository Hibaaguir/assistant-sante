"""Database extraction for the AI health module."""

from __future__ import annotations
import json
import math
from datetime import date, datetime
from typing import Any

import mysql.connector
from mysql.connector import Error as MySQLError

from config import get_db_config

# ── SQL Queries ────────────────────────────────────────────────

_QUERY_PROFILE = """
SELECT u.id AS user_id, u.name, u.age, u.date_of_birth, u.role,
    hp.gender, hp.height, hp.initial_weight, hp.current_weight,
    hp.blood_type, hp.goals, hp.allergies, hp.chronic_diseases,
    hp.smoker, hp.alcoholic
FROM users u
LEFT JOIN health_profiles hp ON hp.user_id = u.id
WHERE u.id = %s LIMIT 1
"""

_QUERY_SLEEP = """
SELECT entry_date, sleep, stress, energy, caffeine, hydration
FROM journal_entries WHERE user_id = %s AND sleep IS NOT NULL ORDER BY entry_date DESC
"""

_QUERY_NUTRITION = """
SELECT je.entry_date, je.hydration, je.sugar_intake, je.caffeine,
    COUNT(m.id) AS meal_count, SUM(m.calories) AS total_calories,
    GROUP_CONCAT(CONCAT(m.meal_type, ':', COALESCE(m.calories, 0)) ORDER BY m.meal_type SEPARATOR '|') AS meals_detail
FROM journal_entries je
LEFT JOIN meals m ON m.journal_entry_id = je.id
WHERE je.user_id = %s
GROUP BY je.id, je.entry_date, je.hydration, je.sugar_intake, je.caffeine
ORDER BY je.entry_date DESC
"""

_QUERY_ACTIVITY = """
SELECT je.entry_date, pa.activity_type, pa.duration_minutes, pa.intensity
FROM physical_activities pa
JOIN journal_entries je ON je.id = pa.journal_entry_id
WHERE je.user_id = %s ORDER BY je.entry_date DESC
"""

_QUERY_SMOKING = """
SELECT je.entry_date, t.tobacco_type, t.cigarettes_per_day, t.puffs_per_day
FROM tobacco t
JOIN journal_entries je ON je.id = t.journal_entry_id
WHERE je.user_id = %s ORDER BY je.entry_date DESC
"""

_QUERY_ALCOHOL = """
SELECT entry_date, alcohol, alcohol_glasses
FROM journal_entries WHERE user_id = %s AND alcohol = TRUE ORDER BY entry_date DESC
"""

_QUERY_VITAL_SIGNS = """
SELECT hd.date, vs.measured_at, vs.heart_rate, vs.systolic_pressure,
    vs.diastolic_pressure, vs.oxygen_saturation
FROM vital_signs vs
JOIN health_data hd ON hd.id = vs.health_data_id
WHERE hd.user_id = %s ORDER BY vs.measured_at DESC
"""

_QUERY_LAB_RESULTS = """
SELECT hd.date, ar.analysis_date, ar.analysis_type, ar.result_name, ar.value, ar.unit
FROM analysis_results ar
JOIN health_data hd ON hd.id = ar.health_data_id
WHERE hd.user_id = %s ORDER BY ar.analysis_date DESC
"""

_QUERY_TREATMENTS = """
SELECT tc_cat.treatment_name AS medication_name, tc_cat.treatment_type AS medication_category,
    t.dose, t.frequency, t.daily_doses, t.start_date, t.end_date,
    COUNT(tchk.id) AS total_checks,
    SUM(CASE WHEN tchk.taken THEN 1 ELSE 0 END) AS taken_count
FROM treatments t
JOIN health_data hd ON hd.id = t.health_data_id
LEFT JOIN treatment_catalogs tc_cat ON tc_cat.id = t.treatment_catalog_id
LEFT JOIN treatment_checks tchk ON tchk.treatment_id = t.id AND tchk.user_id = %s
WHERE hd.user_id = %s
GROUP BY t.id, tc_cat.treatment_name, tc_cat.treatment_type,
    t.dose, t.frequency, t.daily_doses, t.start_date, t.end_date
ORDER BY t.start_date DESC
"""

# ── Helpers ────────────────────────────────────────────────────

def _serialize(value: Any) -> Any:
    if isinstance(value, (date, datetime)):
        return value.isoformat()
    if isinstance(value, float) and (math.isnan(value) or math.isinf(value)):
        return None
    return value


def _rows_to_dicts(cursor) -> list[dict]:
    columns = [col[0] for col in cursor.description]
    return [{col: _serialize(val) for col, val in zip(columns, row)} for row in cursor.fetchall()]


def _parse_profile(rows: list[dict]) -> dict:
    if not rows:
        return {}
    profile = rows[0]
    for field in ("goals", "allergies", "chronic_diseases"):
        raw = profile.get(field)
        if isinstance(raw, str):
            try:
                profile[field] = json.loads(raw)
            except (json.JSONDecodeError, TypeError):
                profile[field] = []
        elif raw is None:
            profile[field] = []
    return profile


def _parse_nutrition(rows: list[dict]) -> list[dict]:
    result = []
    for row in rows:
        meals_detail = row.pop("meals_detail", None) or ""
        meals = []
        for item in meals_detail.split("|"):
            if ":" in item:
                meal_type, cal_str = item.split(":", 1)
                try:
                    meals.append({"meal_type": meal_type, "calories": int(cal_str)})
                except ValueError:
                    pass
        row["meals"] = meals
        result.append(row)
    return result


def _parse_treatments(rows: list[dict]) -> list[dict]:
    for row in rows:
        total = row.get("total_checks") or 0
        taken = row.get("taken_count") or 0
        row["adherence_rate"] = round(taken / total, 2) if total > 0 else None
    return rows


# ── Public API ─────────────────────────────────────────────────

def extract_user_data(user_id: int) -> dict:
    """Extract all health data for a user from MySQL. Raises ValueError if user not found."""
    config = get_db_config()
    conn = None
    try:
        conn = mysql.connector.connect(**config)
        cur = conn.cursor()

        def q(query, params):
            cur.execute(query, params)
            return _rows_to_dicts(cur)

        profile_rows = q(_QUERY_PROFILE, (user_id,))
        if not profile_rows:
            raise ValueError(f"No user found with user_id={user_id}")

        return {
            "user_profile": _parse_profile(profile_rows),
            "sleep":        q(_QUERY_SLEEP, (user_id,)),
            "nutrition":    _parse_nutrition(q(_QUERY_NUTRITION, (user_id,))),
            "activity":     q(_QUERY_ACTIVITY, (user_id,)),
            "smoking":      q(_QUERY_SMOKING, (user_id,)),
            "alcohol":      q(_QUERY_ALCOHOL, (user_id,)),
            "vital_signs":  q(_QUERY_VITAL_SIGNS, (user_id,)),
            "lab_results":  q(_QUERY_LAB_RESULTS, (user_id,)),
            "treatments":   _parse_treatments(q(_QUERY_TREATMENTS, (user_id, user_id))),
        }
    except MySQLError as exc:
        raise MySQLError(f"Database error: {exc}") from exc
    finally:
        if conn and conn.is_connected():
            cur.close()
            conn.close()
