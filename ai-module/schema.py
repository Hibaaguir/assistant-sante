"""
Step 8 — Strict Output Schema.

Defines the exact shape of the report returned by analyze_user().
Provides enforce_schema() which:
  - Guarantees all required keys are present
  - Removes unknown/extra keys
  - Coerces types (str severity, list fields, etc.)
  - Returns a clean, predictable dict regardless of upstream failures

The guaranteed top-level output shape:
{
    "user_id":    int,
    "user_name":  str | null,
    "status":     "success" | "partial" | "error",
    "domains": {
        "<domain>": {
            "analysis":        str,
            "issues":          list[str],
            "recommendations": list[str],
            "severity":        "none" | "low" | "medium" | "high",
            "confidence":      "high" | "partial" | "low",
            "skipped":         bool
        }
    },
    "anomalies": [
        {
            "code":      str,
            "domain":    str,
            "domains":   list[str],
            "severity":  "low" | "medium" | "high" | "critical",
            "message":   str,
            "evidence":  dict,
            "rule_type": "rule_based" | "ai_based"
        }
    ],
    "cross_domain_patterns": [
        {
            "pattern":     str,
            "domains":     list[str],
            "explanation": str,
            "severity":    str
        }
    ],
    "global_recommendations": [
        {
            "priority":           int,
            "action":             str,
            "domain":             str,
            "secondary_domains":  list[str],
            "impact":             str
        }
    ],
    "alerts": [
        {
            "level":            "medium" | "high" | "critical",
            "message":          str,
            "domains":          list[str],
            "suggested_action": str
        }
    ],
    "risk_level":   "low" | "medium" | "high" | "unknown",
    "risk_summary": str,
    "meta": {
        "steps_completed": list[str],
        "warnings":        list[str],
        "errors":          list[str],
        "timing_seconds":  dict[str, float],
        "data_quality":    dict[str, str]
    }
}
"""

from __future__ import annotations
from typing import Any

# ─────────────────────────────────────────────────────────────
#  VALID ENUM VALUES
# ─────────────────────────────────────────────────────────────

VALID_SEVERITIES      = {"none", "low", "medium", "high", "critical", "unknown"}
VALID_RISK_LEVELS     = {"low", "medium", "high", "unknown"}
VALID_STATUSES        = {"success", "partial", "error"}
VALID_CONFIDENCES     = {"high", "partial", "low"}
KNOWN_DOMAINS         = {"sleep", "nutrition", "activity", "smoking",
                         "alcohol", "vital_signs", "lab_results", "treatments"}


# ─────────────────────────────────────────────────────────────
#  FIELD COERCERS
# ─────────────────────────────────────────────────────────────

def _str(val: Any, default: str = "") -> str:
    if val is None:
        return default
    return str(val)


def _str_enum(val: Any, valid: set, default: str) -> str:
    s = _str(val).lower().strip()
    return s if s in valid else default


def _list_of_str(val: Any) -> list[str]:
    if not val:
        return []
    if isinstance(val, list):
        return [str(v) for v in val if v is not None]
    return []


def _list_of_str_safe(val: Any) -> list[str]:
    """Like _list_of_str but also accepts a single string."""
    if isinstance(val, str):
        return [val] if val else []
    return _list_of_str(val)


def _int(val: Any, default: int = 0) -> int:
    try:
        return int(val)
    except (TypeError, ValueError):
        return default


def _dict(val: Any) -> dict:
    return val if isinstance(val, dict) else {}


# ─────────────────────────────────────────────────────────────
#  SECTION VALIDATORS
# ─────────────────────────────────────────────────────────────

def _validate_domain_entry(raw: Any) -> dict:
    if not isinstance(raw, dict):
        raw = {}
    return {
        "analysis":        _str(raw.get("analysis")),
        "issues":          _list_of_str(raw.get("issues")),
        "recommendations": _list_of_str(raw.get("recommendations")),
        "severity":        _str_enum(raw.get("severity"),   VALID_SEVERITIES,  "none"),
        "confidence":      _str_enum(raw.get("confidence"), VALID_CONFIDENCES, "low"),
        "skipped":         bool(raw.get("skipped", False)),
    }


def _validate_domains(raw: Any) -> dict:
    if not isinstance(raw, dict):
        return {d: _validate_domain_entry({}) for d in KNOWN_DOMAINS}
    result = {}
    for domain in KNOWN_DOMAINS:
        result[domain] = _validate_domain_entry(raw.get(domain, {}))
    return result


def _validate_anomaly(raw: Any) -> dict | None:
    if not isinstance(raw, dict):
        return None
    code = _str(raw.get("code"), "UNKNOWN")
    if not code:
        return None
    return {
        "code":      code,
        "domain":    _str(raw.get("domain"), "unknown"),
        "domains":   _list_of_str_safe(raw.get("domains") or raw.get("domain")),
        "severity":  _str_enum(raw.get("severity"), VALID_SEVERITIES, "low"),
        "message":   _str(raw.get("message")),
        "evidence":  _dict(raw.get("evidence")),
        "rule_type": _str(raw.get("rule_type"), "rule_based"),
    }


def _validate_anomalies(raw: Any) -> list[dict]:
    if not isinstance(raw, list):
        return []
    result = []
    for item in raw:
        validated = _validate_anomaly(item)
        if validated:
            result.append(validated)
    return result


def _validate_cross_domain_pattern(raw: Any) -> dict | None:
    if not isinstance(raw, dict):
        return None
    return {
        "pattern":     _str(raw.get("pattern")),
        "domains":     _list_of_str_safe(raw.get("domains")),
        "explanation": _str(raw.get("explanation")),
        "severity":    _str_enum(raw.get("severity"), VALID_SEVERITIES, "low"),
    }


def _validate_recommendation(raw: Any, index: int) -> dict | None:
    if not isinstance(raw, dict):
        return None
    action = _str(raw.get("action"))
    if not action:
        return None
    return {
        "priority":          _int(raw.get("priority"), index + 1),
        "action":            action,
        "domain":            _str(raw.get("domain"), "general"),
        "secondary_domains": _list_of_str(raw.get("secondary_domains")),
        "impact":            _str(raw.get("impact")),
    }


def _validate_alert(raw: Any) -> dict | None:
    if not isinstance(raw, dict):
        return None
    msg = _str(raw.get("message"))
    if not msg:
        return None
    return {
        "level":            _str_enum(raw.get("level"), {"medium", "high", "critical"}, "medium"),
        "message":          msg,
        "domains":          _list_of_str_safe(raw.get("domains")),
        "suggested_action": _str(raw.get("suggested_action")),
    }


def _validate_meta(raw: Any) -> dict:
    if not isinstance(raw, dict):
        raw = {}
    return {
        "steps_completed": _list_of_str(raw.get("steps_completed")),
        "warnings":        _list_of_str(raw.get("warnings")),
        "errors":          _list_of_str(raw.get("errors")),
        "timing_seconds":  {
            k: float(v) for k, v in _dict(raw.get("timing_seconds")).items()
            if isinstance(v, (int, float))
        },
        "data_quality": {
            k: str(v) for k, v in _dict(raw.get("data_quality")).items()
        },
    }


# ─────────────────────────────────────────────────────────────
#  MAIN ENFORCER
# ─────────────────────────────────────────────────────────────

def enforce_schema(raw: dict) -> dict:
    """
    Validate and enforce the strict output schema on a raw report dict.

    Guarantees:
    - All required top-level keys are present
    - Every list field is a list (never None)
    - All enum fields contain only valid values
    - Unknown keys are stripped
    - Types are coerced, never raises

    Args:
        raw: The raw dict returned by analyze_user() or any intermediate step.

    Returns:
        A schema-compliant report dict.
    """
    if not isinstance(raw, dict):
        raw = {}

    # Build recommendations list
    raw_recs = raw.get("global_recommendations") or []
    recommendations = []
    for i, item in enumerate(raw_recs if isinstance(raw_recs, list) else []):
        validated = _validate_recommendation(item, i)
        if validated:
            recommendations.append(validated)

    # Build alerts list
    raw_alerts = raw.get("alerts") or []
    alerts = []
    for item in (raw_alerts if isinstance(raw_alerts, list) else []):
        validated = _validate_alert(item)
        if validated:
            alerts.append(validated)

    # Build cross-domain patterns list
    raw_patterns = raw.get("cross_domain_patterns") or []
    patterns = []
    for item in (raw_patterns if isinstance(raw_patterns, list) else []):
        validated = _validate_cross_domain_pattern(item)
        if validated:
            patterns.append(validated)

    return {
        # Identity
        "user_id":   _int(raw.get("user_id"), 0),
        "user_name": _str(raw.get("user_name")) or None,
        "status":    _str_enum(raw.get("status"), VALID_STATUSES, "error"),

        # Domain analyses (Step 4)
        "domains":   _validate_domains(raw.get("domains")),

        # Anomaly detection (Step 6)
        "anomalies": _validate_anomalies(raw.get("anomalies")),

        # Global synthesis (Step 5)
        "cross_domain_patterns":  patterns,
        "global_recommendations": recommendations,
        "alerts":                 alerts,
        "risk_level":             _str_enum(raw.get("risk_level"), VALID_RISK_LEVELS, "unknown"),
        "risk_summary":           _str(raw.get("risk_summary")),

        # Pipeline metadata
        "meta": _validate_meta(raw.get("meta")),
    }


# ─────────────────────────────────────────────────────────────
#  SCHEMA INTROSPECTION HELPERS
# ─────────────────────────────────────────────────────────────

def validate_report(report: dict) -> tuple[bool, list[str]]:
    """
    Validate a report dict against the schema without modifying it.

    Returns:
        (is_valid: bool, issues: list[str])

    Useful for unit testing and CI checks.
    """
    issues = []

    # Required top-level keys
    required = [
        "user_id", "status", "domains", "anomalies",
        "global_recommendations", "alerts", "risk_level", "risk_summary", "meta",
    ]
    for key in required:
        if key not in report:
            issues.append(f"Missing required key: '{key}'")

    # Status
    if report.get("status") not in VALID_STATUSES:
        issues.append(f"Invalid status: '{report.get('status')}'")

    # Risk level
    if report.get("risk_level") not in VALID_RISK_LEVELS:
        issues.append(f"Invalid risk_level: '{report.get('risk_level')}'")

    # Domains
    domains = report.get("domains", {})
    for domain in KNOWN_DOMAINS:
        if domain not in domains:
            issues.append(f"Missing domain: '{domain}'")
            continue
        entry = domains[domain]
        for field in ("analysis", "issues", "recommendations", "severity", "skipped"):
            if field not in entry:
                issues.append(f"Domain '{domain}' missing field '{field}'")

    # List fields must be lists
    for key in ("anomalies", "global_recommendations", "alerts", "cross_domain_patterns"):
        if not isinstance(report.get(key), list):
            issues.append(f"Field '{key}' must be a list")

    is_valid = len(issues) == 0
    return is_valid, issues


def schema_summary() -> str:
    """Return a human-readable summary of the output schema."""
    return """
HEALTH ANALYSIS REPORT — OUTPUT SCHEMA
═══════════════════════════════════════
{
  user_id:    int
  user_name:  str | null
  status:     "success" | "partial" | "error"

  domains: {
    sleep | nutrition | activity | smoking |
    alcohol | vital_signs | lab_results | treatments: {
      analysis:        str
      issues:          list[str]
      recommendations: list[str]
      severity:        "none" | "low" | "medium" | "high"
      confidence:      "high" | "partial" | "low"
      skipped:         bool
    }
  }

  anomalies: [{
    code:      str
    domain:    str
    domains:   list[str]
    severity:  "low" | "medium" | "high" | "critical"
    message:   str
    evidence:  dict
    rule_type: "rule_based" | "ai_based"
  }]

  cross_domain_patterns: [{
    pattern:     str
    domains:     list[str]
    explanation: str
    severity:    str
  }]

  global_recommendations: [{
    priority:          int
    action:            str
    domain:            str
    secondary_domains: list[str]
    impact:            str
  }]

  alerts: [{
    level:            "medium" | "high" | "critical"
    message:          str
    domains:          list[str]
    suggested_action: str
  }]

  risk_level:   "low" | "medium" | "high" | "unknown"
  risk_summary: str

  meta: {
    steps_completed: list[str]
    warnings:        list[str]
    errors:          list[str]
    timing_seconds:  dict[str, float]
    data_quality:    dict[str, str]
  }
}
""".strip()
