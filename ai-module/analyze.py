"""
AI Health Analysis Module — Single Entry Point.

Public API:
    from analyze import analyze_user

    report = analyze_user(user_id=1)

The returned report follows the Step 8 output format:
{
    "user_id":   int,
    "status":    "success" | "partial" | "error",
    "domains": {
        "sleep":       { "analysis": "...", "issues": [...], "recommendations": [...], "severity": "..." },
        "nutrition":   { ... },
        "activity":    { ... },
        "smoking":     { ... },
        "alcohol":     { ... },
        "vital_signs": { ... },
        "lab_results": { ... },
        "treatments":  { ... },
    },
    "anomalies": [
        { "code": "...", "severity": "...", "message": "...", "domains": [...] }
    ],
    "cross_domain_patterns": [ ... ],
    "global_recommendations": [
        { "priority": 1, "action": "...", "domain": "...", "impact": "..." }
    ],
    "alerts": [
        { "level": "high", "message": "...", "suggested_action": "..." }
    ],
    "risk_level":   "low | medium | high",
    "risk_summary": "...",
    "meta": {
        "steps_completed": [...],
        "warnings":        [...],
        "errors":          [...],
        "timing_seconds":  { "step1": ..., "step2": ..., ... }
    }
}
"""

from __future__ import annotations

import logging
import time
from typing import Any

from config import load_groq_api_key
from schema import enforce_schema

logger = logging.getLogger(__name__)


# ─────────────────────────────────────────────────────────────
#  INTERNAL STEP RUNNER
# ─────────────────────────────────────────────────────────────

def _run_step(name: str, fn, *args, **kwargs) -> tuple[Any, float, str | None]:
    """
    Execute a pipeline step, measuring wall-clock time.

    Returns:
        (result, elapsed_seconds, error_message_or_None)
    """
    t0 = time.perf_counter()
    try:
        result = fn(*args, **kwargs)
        elapsed = round(time.perf_counter() - t0, 3)
        logger.info(f"[{name}] completed in {elapsed}s")
        return result, elapsed, None
    except Exception as exc:
        elapsed = round(time.perf_counter() - t0, 3)
        logger.error(f"[{name}] failed after {elapsed}s: {exc}")
        return None, elapsed, str(exc)


# ─────────────────────────────────────────────────────────────
#  PUBLIC ENTRY POINT
# ─────────────────────────────────────────────────────────────

def analyze_user(
    user_id: int,
    skip_ai: bool = False,
) -> dict:
    """
    Run the full AI health analysis pipeline for a given user.

    Flow:
        Step 1 — Extract all health data from MySQL
        Step 2 — Organize and normalize by domain
        Step 3 — Compute domain summaries (feature extraction)
        Step 4 — AI analysis nodes (one per domain)
        Step 5 — Final aggregation (cross-domain synthesis)
        Step 6 — Anomaly detection (rule-based + AI) [runs inside pipeline]

    Args:
        user_id:  Integer primary key from the `users` table.
        skip_ai:  If True, skip Steps 4–6 (returns summaries only). Useful
                  for testing data extraction without API costs.

    Returns:
        Complete health analysis report dict (see module docstring for schema).

    Raises:
        ValueError: If user_id does not exist in the database.
    """
    load_groq_api_key()

    steps_completed: list[str] = []
    all_warnings:    list[str] = []
    all_errors:      list[str] = []
    timing:          dict[str, float] = {}

    logger.info(f"Starting health analysis for user_id={user_id}")

    # ── STEP 1: Data Extraction ──────────────────────────────
    from data_extraction.extractor import extract_user_data

    raw_data, t, err = _run_step("step1_extract", extract_user_data, user_id)
    timing["step1_extract"] = t

    if err:
        # user not found or DB error — unrecoverable
        return enforce_schema(_error_report(user_id, f"Step 1 failed: {err}", timing))

    steps_completed.append("step1_extract")

    # ── STEP 2: Organize & Normalize ─────────────────────────
    from data_organization.organizer import organize_user_data

    organized_data, t, err = _run_step("step2_organize", organize_user_data, raw_data)
    timing["step2_organize"] = t

    if err:
        return enforce_schema(_error_report(user_id, f"Step 2 failed: {err}", timing))

    steps_completed.append("step2_organize")
    all_warnings.extend(organized_data.get("data_quality", {}).get("warnings", []))

    # ── STEP 3: Process / Feature Extraction ─────────────────
    from data_processing.processor import process_user_data

    processed_data, t, err = _run_step("step3_process", process_user_data, organized_data)
    timing["step3_process"] = t

    if err:
        return enforce_schema(_error_report(user_id, f"Step 3 failed: {err}", timing))

    steps_completed.append("step3_process")

    # ── Early return if AI is skipped ─────────────────────────
    if skip_ai:
        logger.info("skip_ai=True — returning after Step 3")
        return enforce_schema(
            _partial_report(user_id, organized_data, processed_data, timing, steps_completed, all_warnings)
        )

    # ── STEPS 4 + 5 + 6: AI Pipeline ─────────────────────────
    from ai_pipeline.pipeline import run_pipeline

    pipeline_result, t, err = _run_step("steps4_5_6_pipeline", run_pipeline, processed_data)
    timing["steps4_5_6_pipeline"] = t

    if err:
        # Pipeline failure is recoverable — return partial report with data up to Step 3
        all_errors.append(f"AI pipeline failed: {err}")
        logger.warning("AI pipeline failed — returning partial report")
        return enforce_schema(
            _partial_report(
                user_id, organized_data, processed_data, timing,
                steps_completed, all_warnings, all_errors,
            )
        )

    steps_completed.append("steps4_5_6_pipeline")
    all_errors.extend(pipeline_result.get("errors", []))

    # ── Assemble Final Report ─────────────────────────────────
    profile = organized_data.get("user_profile", {})

    report = {
        "user_id":   user_id,
        "user_name": profile.get("name"),
        "status":    "success",

        # Per-domain AI analyses (Step 4)
        "domains": pipeline_result.get("domains", {}),

        # Anomalies (Step 6 — rule-based + AI)
        "anomalies": pipeline_result.get("anomalies", []),

        # Global synthesis (Step 5)
        "cross_domain_patterns":  pipeline_result.get("cross_domain_patterns", []),
        "global_recommendations": pipeline_result.get("global_recommendations", []),
        "alerts":                 pipeline_result.get("alerts", []),
        "risk_level":             pipeline_result.get("risk_level", "low"),
        "risk_summary":           pipeline_result.get("risk_summary", ""),

        # Pipeline metadata
        "meta": {
            "steps_completed": steps_completed,
            "warnings":        all_warnings,
            "errors":          all_errors,
            "timing_seconds":  timing,
            "data_quality":    organized_data.get("data_quality", {}).get("completeness", {}),
        },
    }

    total = round(sum(timing.values()), 3)
    report = enforce_schema(report)
    logger.info(f"Analysis complete for user_id={user_id} — risk={report['risk_level']} total={total}s")

    return report


# ─────────────────────────────────────────────────────────────
#  REPORT BUILDERS
# ─────────────────────────────────────────────────────────────

def _error_report(user_id: int, message: str, timing: dict) -> dict:
    """Unrecoverable failure — return a minimal error report."""
    return {
        "user_id": user_id,
        "status":  "error",
        "domains": {},
        "anomalies": [],
        "cross_domain_patterns":  [],
        "global_recommendations": [],
        "alerts": [],
        "risk_level":   "unknown",
        "risk_summary": "",
        "meta": {
            "steps_completed": [],
            "warnings":        [],
            "errors":          [message],
            "timing_seconds":  timing,
            "data_quality":    {},
        },
    }


def _partial_report(
    user_id: int,
    organized_data: dict,
    processed_data: dict,
    timing: dict,
    steps_completed: list[str],
    warnings: list[str],
    errors: list[str] | None = None,
) -> dict:
    """
    Partial report when AI pipeline is skipped or failed.
    Still includes summaries and rule-based anomalies.
    """
    from anomaly_detection.detector import detect_anomalies

    summaries = processed_data.get("summaries", {})
    profile   = organized_data.get("user_profile", {})

    # Rule-based anomalies still run (no API needed)
    rule_anomalies, t, _ = _run_step(
        "step6_rule_anomalies", detect_anomalies, summaries, profile
    )
    timing["step6_rule_anomalies"] = t
    rule_anomalies = rule_anomalies or []

    return {
        "user_id":   user_id,
        "user_name": profile.get("name"),
        "status":    "partial",
        "domains":   {},           # no AI analysis
        "anomalies": rule_anomalies,
        "cross_domain_patterns":  [],
        "global_recommendations": [],
        "alerts":    [
            {
                "level":            a["severity"],
                "message":          a["message"],
                "domains":          a.get("domains", [a.get("domain")]),
                "suggested_action": "See domain recommendations.",
            }
            for a in rule_anomalies
            if a.get("severity") in ("critical", "high")
        ],
        "risk_level":   _derive_risk_from_anomalies(rule_anomalies),
        "risk_summary":  (
            f"Partial analysis (AI pipeline unavailable). "
            f"{len(rule_anomalies)} anomaly/anomalies detected from rule-based checks."
        ),
        "meta": {
            "steps_completed": steps_completed,
            "warnings":        warnings,
            "errors":          errors or [],
            "timing_seconds":  timing,
            "data_quality":    organized_data.get("data_quality", {}).get("completeness", {}),
            "summaries":       summaries,   # include summaries for partial reports
        },
    }


def _derive_risk_from_anomalies(anomalies: list[dict]) -> str:
    """Derive overall risk level from anomaly severities (fallback when AI unavailable)."""
    if not anomalies:
        return "low"
    has_critical = any(a.get("severity") == "critical" for a in anomalies)
    has_high     = any(a.get("severity") == "high"     for a in anomalies)
    medium_count = sum(1 for a in anomalies if a.get("severity") == "medium")
    if has_critical or (has_high and medium_count >= 2):
        return "high"
    if has_high or medium_count >= 2:
        return "medium"
    return "low"
