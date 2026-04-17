"""
Step 2 — Data Organization & Normalization.

Entry point:
    organize_user_data(raw_data: dict) -> dict

Accepts the raw output from extract_user_data() and returns
a fully cleaned, normalized, domain-separated dict ready for
Step 3 (data processing / feature extraction).

Each domain is handled independently.
"""

from __future__ import annotations
from data_organization.normalizers import (
    normalize_user_profile,
    normalize_sleep,
    normalize_nutrition,
    normalize_activity,
    normalize_smoking,
    normalize_alcohol,
    normalize_vital_signs,
    normalize_lab_results,
    normalize_treatments,
)


def _domain_meta(records: list[dict], date_field: str = "entry_date") -> dict:
    """
    Compute lightweight metadata for a domain:
    - total record count
    - date range (first / last observation)
    """
    if not records:
        return {"record_count": 0, "date_range": None}

    dates = [
        r.get(date_field) for r in records
        if r.get(date_field) is not None
    ]
    dates = sorted(dates)
    return {
        "record_count": len(records),
        "date_range": {
            "from": dates[0]  if dates else None,
            "to":   dates[-1] if dates else None,
        },
    }


def organize_user_data(raw_data: dict) -> dict:
    """
    Organize and normalize raw extracted data into clean domain blocks.

    Args:
        raw_data: Output of extract_user_data(user_id).

    Returns:
        A structured dict with the following shape:
        {
            "user_profile": { ... },
            "domains": {
                "sleep":       { "records": [...], "meta": {...} },
                "nutrition":   { "records": [...], "meta": {...} },
                "activity":    { "records": [...], "meta": {...} },
                "smoking":     { "records": [...], "meta": {...} },
                "alcohol":     { "records": [...], "meta": {...} },
                "vital_signs": { "records": [...], "meta": {...} },
                "lab_results": { "records": [...], "meta": {...} },
                "treatments":  { "records": [...], "meta": {...} },
            },
            "data_quality": { ... }
        }
    """
    # ── 1. Normalize user profile (needed by smoking/alcohol normalizers) ──
    profile = normalize_user_profile(raw_data.get("user_profile", {}))

    # ── 2. Normalize each domain independently ────────────────────────────
    sleep_records      = normalize_sleep(raw_data.get("sleep", []))
    nutrition_records  = normalize_nutrition(raw_data.get("nutrition", []))
    activity_records   = normalize_activity(raw_data.get("activity", []))
    smoking_records    = normalize_smoking(
        raw_data.get("smoking", []),
        smoker_flag=profile.get("smoker", False),
    )
    alcohol_records    = normalize_alcohol(
        raw_data.get("alcohol", []),
        alcoholic_flag=profile.get("alcoholic", False),
    )
    vital_signs_records = normalize_vital_signs(raw_data.get("vital_signs", []))
    lab_results_records = normalize_lab_results(raw_data.get("lab_results", []))
    treatment_records   = normalize_treatments(raw_data.get("treatments", []))

    # ── 3. Assemble domains with metadata ────────────────────────────────
    domains = {
        "sleep": {
            "records": sleep_records,
            "meta":    _domain_meta(sleep_records),
        },
        "nutrition": {
            "records": nutrition_records,
            "meta":    _domain_meta(nutrition_records),
        },
        "activity": {
            "records": activity_records,
            "meta":    _domain_meta(activity_records),
        },
        "smoking": {
            "records": smoking_records,
            "meta":    _domain_meta(smoking_records),
        },
        "alcohol": {
            "records": alcohol_records,
            "meta":    _domain_meta(alcohol_records),
        },
        "vital_signs": {
            "records": vital_signs_records,
            "meta":    _domain_meta(vital_signs_records, date_field="measured_at"),
        },
        "lab_results": {
            "records": lab_results_records,
            "meta":    _domain_meta(lab_results_records, date_field="analysis_date"),
        },
        "treatments": {
            "records": treatment_records,
            "meta":    {
                "record_count": len(treatment_records),
                # Treatments have no single date field, use start_date range
                "date_range": _domain_meta(treatment_records, date_field="start_date")["date_range"],
            },
        },
    }

    # ── 4. Data quality summary ──────────────────────────────────────────
    data_quality = _assess_data_quality(profile, domains)

    return {
        "user_profile": profile,
        "domains":      domains,
        "data_quality": data_quality,
    }


# ─────────────────────────────────────────────────────────────
#  DATA QUALITY ASSESSMENT
# ─────────────────────────────────────────────────────────────

def _assess_data_quality(profile: dict, domains: dict) -> dict:
    """
    Produce a data quality report that the AI pipeline can use
    to weigh its confidence per domain.

    Returns:
        {
            "completeness": {
                "sleep": "good" | "partial" | "empty",
                ...
            },
            "warnings": [ "...", ... ]
        }
    """
    GOOD_THRESHOLD    = 14   # at least 2 weeks of records
    PARTIAL_THRESHOLD = 3    # at least 3 records

    completeness = {}
    warnings = []

    for domain, block in domains.items():
        count = block["meta"]["record_count"]
        if count >= GOOD_THRESHOLD:
            completeness[domain] = "good"
        elif count >= PARTIAL_THRESHOLD:
            completeness[domain] = "partial"
        else:
            completeness[domain] = "empty"
            warnings.append(
                f"Domain '{domain}' has insufficient data ({count} records). "
                "AI analysis will have low confidence."
            )

    # Profile-level warnings
    if profile.get("bmi") is None:
        warnings.append("BMI could not be computed (height or weight missing from profile).")
    if profile.get("age") is None:
        warnings.append("User age is missing — some health thresholds may be imprecise.")
    if not profile.get("chronic_diseases"):
        warnings.append("No chronic diseases listed — recommendations will use general population baselines.")

    # Cross-domain consistency checks
    sleep_count    = domains["sleep"]["meta"]["record_count"]
    activity_count = domains["activity"]["meta"]["record_count"]
    if sleep_count > 0 and activity_count == 0:
        warnings.append(
            "Sleep data exists but no physical activity logged. "
            "Sedentary lifestyle cannot be ruled out."
        )

    smoking_count = domains["smoking"]["meta"]["record_count"]
    if profile.get("smoker") and smoking_count == 0:
        warnings.append(
            "Health profile flags user as smoker but no tobacco logs found. "
            "Smoking analysis will rely on profile flag only."
        )

    alcohol_count = domains["alcohol"]["meta"]["record_count"]
    if profile.get("alcoholic") and alcohol_count == 0:
        warnings.append(
            "Health profile flags user as alcoholic but no alcohol logs found. "
            "Alcohol analysis will rely on profile flag only."
        )

    return {
        "completeness": completeness,
        "warnings":     warnings,
    }
