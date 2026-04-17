"""
AI-based anomaly detection layer.

Runs AFTER the rule-based detector. Takes the rule-based anomalies + summaries
and asks Claude to find patterns that rules cannot catch:

  - Sudden deterioration across multiple domains in the same time window
  - Subtle compounding risks that individually appear minor
  - Patterns specific to the user's chronic diseases or age group
  - Anomalies in lab result trends that cross clinical boundaries

Entry point:
    detect_anomalies_ai(summaries, profile, rule_anomalies) -> list[dict]

Returns NEW anomalies only (does not repeat what rules already found).
"""

from __future__ import annotations
import json
import os


# ─────────────────────────────────────────────────────────────
#  PROMPT
# ─────────────────────────────────────────────────────────────

def _build_ai_anomaly_prompt(
    summaries: dict,
    profile: dict,
    rule_anomalies: list[dict],
) -> str:
    chronic  = ", ".join(profile.get("chronic_diseases") or []) or "none"
    age      = profile.get("age", "unknown")
    gender   = profile.get("gender", "unknown")
    bmi      = f"{profile.get('bmi')} ({profile.get('bmi_category', '')})"

    already_found = [a["code"] for a in rule_anomalies]

    # Filter to non-empty summaries for the prompt
    relevant = {k: v for k, v in summaries.items() if not v.get("_empty")}

    return f"""You are a clinical data analyst specialising in multi-domain health pattern recognition.

PATIENT CONTEXT:
  Age: {age}  |  Gender: {gender}  |  BMI: {bmi}
  Chronic diseases: {chronic}

HEALTH SUMMARIES (computed statistics — not raw records):
{json.dumps(relevant, indent=2, default=str)}

RULE-BASED ANOMALIES ALREADY DETECTED (do NOT repeat these):
{json.dumps(already_found, indent=2)}

YOUR TASK:
Find anomalies that the rule-based system MISSED. Focus on:
1. Subtle compounding risks — combinations of individually borderline values that
   together create a meaningful risk pattern.
2. Age/gender/disease-specific concerns — e.g., a blood glucose trend that is
   normal for a healthy adult but concerning for a diabetic patient.
3. Lab result patterns — values moving toward clinical boundaries even if not
   yet crossing them (e.g., HbA1c rising from 5.4% to 5.9% over 3 readings).
4. Positive anomalies — if something looks unexpectedly good given the overall
   profile, note it (e.g., perfect treatment adherence despite multiple medications).

Return ONLY a JSON array of anomaly objects.
Return an empty array [] if you find nothing significant beyond the rules.

Schema for each anomaly:
{{
  "code":      "AI_DESCRIPTIVE_CODE",
  "domain":    "primary domain name",
  "domains":   ["domain1", "domain2"],
  "severity":  "low | medium | high | critical",
  "message":   "specific finding with numbers from the data",
  "evidence":  {{ "key": value }},
  "rule_type": "ai_based"
}}

Rules:
- Be specific: include numbers from the summaries.
- Do NOT invent data not present in the summaries.
- Limit to maximum 5 new anomalies.
- Only include findings of genuine clinical relevance."""


# ─────────────────────────────────────────────────────────────
#  ENTRY POINT
# ─────────────────────────────────────────────────────────────

def detect_anomalies_ai(
    summaries: dict,
    profile: dict,
    rule_anomalies: list[dict],
) -> list[dict]:
    """
    AI-based anomaly detection.

    Args:
        summaries:      Step 3 processed summaries.
        profile:        Step 2 normalized user profile.
        rule_anomalies: Anomalies already found by detect_anomalies().

    Returns:
        List of NEW anomaly dicts with rule_type="ai_based".
        Returns empty list on API failure (non-fatal).
    """
    api_key = os.environ.get("GROQ_API_KEY")
    if not api_key:
        return []   # AI layer is optional — silently skip if no key

    # Skip if there's nothing to analyse
    non_empty = [k for k, v in summaries.items() if not v.get("_empty")]
    if not non_empty:
        return []

    try:
        from ai_pipeline.llm_client import call_llm_json
        prompt = _build_ai_anomaly_prompt(summaries, profile, rule_anomalies)
        parsed = call_llm_json(prompt, max_tokens=1024)

        if not isinstance(parsed, list):
            return []

        # Validate and normalise each entry
        validated = []
        existing_codes = {a["code"] for a in rule_anomalies}
        for item in parsed:
            if not isinstance(item, dict):
                continue
            code = str(item.get("code", "AI_UNKNOWN"))
            if code in existing_codes:
                continue   # skip duplicates
            validated.append({
                "code":      code,
                "domain":    str(item.get("domain", "unknown")),
                "domains":   list(item.get("domains", [item.get("domain", "unknown")])),
                "severity":  str(item.get("severity", "low")),
                "message":   str(item.get("message", "")),
                "evidence":  dict(item.get("evidence", {})),
                "rule_type": "ai_based",
            })

        return validated

    except Exception:
        # AI anomaly layer is optional — never crash the pipeline
        return []
