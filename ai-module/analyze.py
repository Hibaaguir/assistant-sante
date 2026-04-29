"""
AI Health Analysis Module — single entry point.

Public API:
    from analyze import analyze_user
    report = analyze_user(user_id=1)

Report schema:
{
    "user_id":   int,
    "user_name": str,
    "status":    "success" | "error",
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
    "anomalies":              [{ "code": "...", "severity": "...", "message": "...", "domains": [...] }],
    "cross_domain_patterns":  [...],
    "global_recommendations": [{ "priority": 1, "action": "...", "domain": "...", "impact": "..." }],
    "alerts":                 [{ "level": "...", "message": "...", "suggested_action": "..." }],
    "risk_level":   "low | medium | high",
    "risk_summary": "...",
    "meta": { "timing_seconds": {...}, "errors": [...] }
}
"""

from __future__ import annotations
import logging
import time

from config import load_groq_api_key
from schema import enforce_schema

logger = logging.getLogger(__name__)


def analyze_user(user_id: int) -> dict:
    """Run the full AI health analysis for a user. Returns a schema-compliant report dict."""
    load_groq_api_key()
    timing: dict[str, float] = {}

    def step(name: str, fn, *args):
        t0 = time.perf_counter()
        result = fn(*args)
        timing[name] = round(time.perf_counter() - t0, 3)
        logger.info(f"[{name}] done in {timing[name]}s")
        return result

    try:
        from db import extract_user_data
        raw = step("extract", extract_user_data, user_id)
    except Exception as exc:
        logger.error(f"Extraction failed: {exc}")
        return enforce_schema({
            "user_id": user_id, "status": "error",
            "domains": {}, "anomalies": [], "cross_domain_patterns": [],
            "global_recommendations": [], "alerts": [],
            "risk_level": "unknown", "risk_summary": "",
            "meta": {"timing_seconds": timing, "errors": [str(exc)]},
        })

    from processing import process_user_data
    processed = step("process", process_user_data, raw)

    from ai import analyze_with_ai
    ai_result = step("ai_analysis", analyze_with_ai, processed["summaries"], processed["user_context"])

    profile = raw.get("user_profile", {})
    report = {
        "user_id":                user_id,
        "user_name":              profile.get("name"),
        "status":                 "success",
        "domains":                ai_result["domains"],
        "anomalies":              ai_result["anomalies"],
        "cross_domain_patterns":  ai_result["cross_domain_patterns"],
        "global_recommendations": ai_result["global_recommendations"],
        "alerts":                 ai_result["alerts"],
        "risk_level":             ai_result["risk_level"],
        "risk_summary":           ai_result["risk_summary"],
        "meta": {
            "timing_seconds": timing,
            "errors": [],
        },
    }

    logger.info(f"Analysis complete for user_id={user_id} — risk={report['risk_level']} total={round(sum(timing.values()), 3)}s")
    return enforce_schema(report)
