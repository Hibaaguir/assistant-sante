"""
Step 3 — Data Processing / Feature Extraction.

Entry point:
    process_user_data(organized_data: dict) -> dict

Accepts the output of organize_user_data() (Step 2).
Returns clean summaries per domain — the ONLY payload sent to AI nodes.

Nothing in this output contains raw database records.
"""

from __future__ import annotations

from data_processing.summarizers import (
    summarize_sleep,
    summarize_nutrition,
    summarize_activity,
    summarize_smoking,
    summarize_alcohol,
    summarize_vital_signs,
    summarize_lab_results,
    summarize_treatments,
    summarize_user_context,
)


def process_user_data(organized_data: dict) -> dict:
    """
    Convert normalized domain records into compact feature summaries.

    Args:
        organized_data: Output of organize_user_data() — contains
                        'user_profile', 'domains', and 'data_quality'.

    Returns:
        {
            "user_context": { ... },          # profile facts for AI nodes
            "summaries": {
                "sleep":       { ... },
                "nutrition":   { ... },
                "activity":    { ... },
                "smoking":     { ... },
                "alcohol":     { ... },
                "vital_signs": { ... },
                "lab_results": { ... },
                "treatments":  { ... },
            },
            "data_quality": { ... },          # passed through from Step 2
            "ready_domains": [ ... ],         # domains with enough data for AI
            "sparse_domains": [ ... ],        # domains that will get low-confidence analysis
        }
    """
    profile   = organized_data.get("user_profile", {})
    domains   = organized_data.get("domains", {})
    quality   = organized_data.get("data_quality", {})
    completeness = quality.get("completeness", {})

    # ── Total tracked days (from sleep domain — best coverage proxy) ──────
    sleep_days = domains.get("sleep", {}).get("meta", {}).get("record_count", 0)

    # ── Build summaries ───────────────────────────────────────────────────
    summaries = {
        "sleep": summarize_sleep(
            domains.get("sleep", {})
        ),
        "nutrition": summarize_nutrition(
            domains.get("nutrition", {})
        ),
        "activity": summarize_activity(
            domains.get("activity", {}),
            total_tracked_days=sleep_days,
        ),
        "smoking": summarize_smoking(
            domains.get("smoking", {})
        ),
        "alcohol": summarize_alcohol(
            domains.get("alcohol", {})
        ),
        "vital_signs": summarize_vital_signs(
            domains.get("vital_signs", {})
        ),
        "lab_results": summarize_lab_results(
            domains.get("lab_results", {})
        ),
        "treatments": summarize_treatments(
            domains.get("treatments", {})
        ),
    }

    # ── Classify domains by data richness ────────────────────────────────
    ready_domains  = [d for d, lvl in completeness.items() if lvl in ("good", "partial")]
    sparse_domains = [d for d, lvl in completeness.items() if lvl == "empty"]

    return {
        "user_context":  summarize_user_context(profile),
        "summaries":     summaries,
        "data_quality":  quality,
        "ready_domains": ready_domains,
        "sparse_domains": sparse_domains,
    }
