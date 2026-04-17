"""
Main data extraction service for the AI health analysis module.

Entry point:
    extract_user_data(user_id: int) -> dict

Returns a structured JSON-ready dict organized by health domain.
No raw ORM, no Laravel dependency — pure Python + MySQL.
"""

import json
import math
from datetime import date, datetime
from typing import Any

import mysql.connector
from mysql.connector import Error as MySQLError

# Local imports (within ai-module)
import sys
from pathlib import Path
sys.path.insert(0, str(Path(__file__).parent.parent))

from config import get_db_config
from data_extraction.queries import (
    QUERY_USER_PROFILE,
    QUERY_SLEEP,
    QUERY_NUTRITION,
    QUERY_ACTIVITY,
    QUERY_SMOKING,
    QUERY_ALCOHOL,
    QUERY_VITAL_SIGNS,
    QUERY_LAB_RESULTS,
    QUERY_TREATMENTS,
)


# ─────────────────────────────────────────────────────────────
#  HELPERS
# ─────────────────────────────────────────────────────────────
#convertion des valeurs en json pour l analyse de l ia 
def _serialize(value: Any) -> Any:
    """Make any value JSON-serializable (handles date, datetime, Decimal, etc.)."""
    if isinstance(value, (date, datetime)):
        return value.isoformat()#isoformat(format mtaa date comprehensible par l ia )
    if isinstance(value, float) and (math.isnan(value) or math.isinf(value)):
        return None
    return value


def _rows_to_dicts(cursor) -> list[dict]:
    """Convert cursor fetchall() rows to a list of clean dicts."""
    columns = [col[0] for col in cursor.description]
    rows = []
    for row in cursor.fetchall():
        rows.append({col: _serialize(val) for col, val in zip(columns, row)})
    return rows


def _run_query(cursor, query: str, params: tuple) -> list[dict]:
    """Execute a query and return results as a list of dicts."""
    cursor.execute(query, params)
    return _rows_to_dicts(cursor)


# ─────────────────────────────────────────────────────────────
#  DOMAIN-SPECIFIC POST-PROCESSING
# ─────────────────────────────────────────────────────────────

def _process_user_profile(rows: list[dict]) -> dict:
    """Return single user profile dict with parsed JSON fields."""
    if not rows:
        return {}

    profile = rows[0]

    for json_field in ("goals", "allergies", "chronic_diseases"):
        raw = profile.get(json_field)
        if isinstance(raw, str):
            try:
                profile[json_field] = json.loads(raw)
            except (json.JSONDecodeError, TypeError):
                profile[json_field] = []
        elif raw is None:
            profile[json_field] = []
    return profile


def _process_nutrition(rows: list[dict]) -> list[dict]:
    """Expand the meals_detail string into a structured list per row."""
    result = []
    for row in rows:
        # meals_detail uses a compact format: "type:calories|type:calories".
        meals_detail = row.pop("meals_detail", None) or ""
        meals = []
        if meals_detail:
            for item in meals_detail.split("|"):
                if ":" in item:
                    meal_type, cal_str = item.split(":", 1)
                    try:
                        cal = int(cal_str)
                    except ValueError:
                        cal = 0
                    meals.append({"meal_type": meal_type, "calories": cal})
        row["meals"] = meals
        result.append(row)
    return result


def _process_treatments(rows: list[dict]) -> list[dict]:
    """Compute adherence_rate from total_checks / taken_count."""
    for row in rows:
        total = row.get("total_checks") or 0
        taken = row.get("taken_count") or 0
        row["adherence_rate"] = round(taken / total, 2) if total > 0 else None
    return rows


# ─────────────────────────────────────────────────────────────
#  MAIN EXTRACTION FUNCTION
# ─────────────────────────────────────────────────────────────

def extract_user_data(user_id: int) -> dict:
    """
    Extract all health-related data for a given user_id from the database.

    Args:
        user_id: The integer primary key from the `users` table.

    Returns:
        A dict organized by health domain, ready for the AI processing pipeline.

    Raises:
        ValueError: If user_id is not found.
        MySQLError: On any database connectivity issue.
    """
    config = get_db_config()
    connection = None

    try:
        connection = mysql.connector.connect(**config)
        cursor = connection.cursor()

        # ── 1. User profile ──────────────────────────────────
        profile_rows = _run_query(cursor, QUERY_USER_PROFILE, (user_id,))
        if not profile_rows:
            raise ValueError(f"No user found with user_id={user_id}")
        user_profile = _process_user_profile(profile_rows)

        # ── 2. Sleep / Stress / Energy ────────────────────────
        sleep_rows = _run_query(cursor, QUERY_SLEEP, (user_id,))

        # ── 3. Nutrition ──────────────────────────────────────
        nutrition_rows = _process_nutrition(
            _run_query(cursor, QUERY_NUTRITION, (user_id,))
        )

        # ── 4. Physical Activity ──────────────────────────────
        activity_rows = _run_query(cursor, QUERY_ACTIVITY, (user_id,))

        # ── 5. Smoking ────────────────────────────────────────
        smoking_rows = _run_query(cursor, QUERY_SMOKING, (user_id,))

        # ── 6. Alcohol ────────────────────────────────────────
        alcohol_rows = _run_query(cursor, QUERY_ALCOHOL, (user_id,))

        # ── 7. Vital Signs ────────────────────────────────────
        vital_signs_rows = _run_query(cursor, QUERY_VITAL_SIGNS, (user_id,))

        # ── 8. Lab / Analysis Results ─────────────────────────
        lab_results_rows = _run_query(cursor, QUERY_LAB_RESULTS, (user_id,))

        # ── 9. Treatments + Adherence ─────────────────────────
        treatment_rows = _process_treatments(
            _run_query(cursor, QUERY_TREATMENTS, (user_id, user_id))
        )

    except MySQLError as exc:
        raise MySQLError(f"Database error during extraction: {exc}") from exc
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()

    # ── Assemble final structured output ──────────────────────
    return {
        "user_profile":  user_profile,
        "sleep":         sleep_rows,
        "nutrition":     nutrition_rows,
        "activity":      activity_rows,
        "smoking":       smoking_rows,
        "alcohol":       alcohol_rows,
        "vital_signs":   vital_signs_rows,
        "lab_results":   lab_results_rows,
        "treatments":    treatment_rows,
    }
