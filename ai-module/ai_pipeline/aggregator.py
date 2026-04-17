"""
Step 5 — Final Aggregation Node.

Receives all domain analyses (from Steps 4 nodes) and produces the
global health report:

  - cross_domain_patterns : causal relationships detected across domains
  - global_recommendations: all recommendations merged and ranked by priority
  - alerts                : urgent findings that need immediate attention
  - risk_level            : overall patient risk (low / medium / high)
  - risk_summary          : 2-3 sentence executive summary

This node runs AFTER all domain nodes have completed.
It reads state["domain_analyses"] and state["summaries"].
"""

from __future__ import annotations
import json

from ai_pipeline.state import HealthAnalysisState
from ai_pipeline.llm_client import call_llm_json


# ─────────────────────────────────────────────────────────────
#  PROMPT
# ─────────────────────────────────────────────────────────────

def _build_aggregation_prompt(
    domain_analyses: dict,
    summaries: dict,
    user_context: dict,
    anomalies: list[dict] | None = None,
) -> str:
    """
    Build the aggregation prompt.
    Includes every domain's analysis + the raw summaries for numerical grounding.
    """
    chronic = ", ".join(user_context.get("chronic_diseases") or []) or "none"
    goals   = ", ".join(user_context.get("goals") or []) or "not specified"

    # Render domain analyses — skip empty ones
    analyses_block = ""
    for domain, result in domain_analyses.items():
        if result.get("skipped"):
            analyses_block += f"\n[{domain.upper()}]  — no data available\n"
            continue
        analyses_block += f"""
[{domain.upper()}]  severity={result.get('severity', '?').upper()}  confidence={result.get('confidence', '?')}
  Analysis : {result.get('analysis', '')}
  Issues   : {json.dumps(result.get('issues', []))}
  Recs     : {json.dumps(result.get('recommendations', []))}
"""

    # Render key numeric summaries for cross-domain reference
    def _kv(d: dict, *keys) -> str:
        return "  ".join(f"{k}={d.get(k)}" for k in keys if d.get(k) is not None)

    sleep_s     = summaries.get("sleep", {})
    nutrition_s = summaries.get("nutrition", {})
    activity_s  = summaries.get("activity", {})
    alcohol_s   = summaries.get("alcohol", {})
    smoking_s   = summaries.get("smoking", {})
    vitals_s    = summaries.get("vital_signs", {})

    numbers_block = f"""
Key numeric cross-references:
  Sleep      : {_kv(sleep_s, 'avg_sleep_score', 'avg_stress_score', 'avg_caffeine_cups', 'sleep_trend')}
  Nutrition  : {_kv(nutrition_s, 'avg_daily_calories', 'avg_hydration_liters', 'avg_caffeine_cups')}
  Activity   : {_kv(activity_s, 'active_days_per_week', 'sedentary_days_pct', 'effort_trend')}
  Alcohol    : {_kv(alcohol_s, 'drinking_days', 'avg_glasses_on_drinking_days', 'risky_drinking_days_pct')}
  Smoking    : {_kv(smoking_s, 'smoking_days', 'avg_daily_units', 'smoking_trend')}
  Vital signs: {_kv(vitals_s, 'avg_heart_rate', 'avg_systolic_pressure', 'avg_oxygen_saturation', 'hypertension_episodes_pct')}
"""

    output_schema = """
Return ONLY a valid JSON object — no markdown, no text outside JSON.

Schema:
{
  "cross_domain_patterns": [
    {
      "pattern": "one-sentence description of the inter-domain link",
      "domains": ["domain1", "domain2"],
      "explanation": "why these domains are causally or correlatively linked for this patient",
      "severity": "low | medium | high"
    }
  ],
  "global_recommendations": [
    {
      "priority": 1,
      "action": "specific, concrete action the patient should take",
      "domain": "primary domain this addresses",
      "secondary_domains": ["other domains impacted"],
      "impact": "why this action matters (expected health outcome)"
    }
  ],
  "alerts": [
    {
      "level": "medium | high",
      "message": "specific urgent finding with numbers",
      "domains": ["relevant domains"],
      "suggested_action": "what to do immediately"
    }
  ],
  "risk_level": "low | medium | high",
  "risk_summary": "2–3 sentence overall assessment of the patient's health status"
}

Rules:
- cross_domain_patterns: Only include patterns where a real causal or correlative
  link is supported by the data (e.g., high caffeine + poor sleep + fatigue is valid;
  random co-occurrence is not).
- global_recommendations: Ranked by impact. Priority 1 = highest impact on overall health.
  Maximum 6 recommendations. Each must be actionable (not "consult a doctor" unless urgent).
- alerts: Only include if severity is medium or high AND action is needed now.
  Maximum 4 alerts. Each must cite specific numbers from the data.
- risk_level:
    low    = no urgent issues; lifestyle improvements recommended
    medium = at least one significant health pattern requiring attention
    high   = at least one domain with high severity OR multiple medium-severity domains
             interacting in a dangerous pattern
- risk_summary: Written for the patient. Factual, not alarmist. Specific.
"""

    # Anomaly block — always include, even if empty
    if anomalies:
        crit_high = [a for a in anomalies if a.get("severity") in ("critical", "high")]
        med_low   = [a for a in anomalies if a.get("severity") in ("medium", "low")]
        anomaly_block = "\nDETECTED ANOMALIES (pre-computed, use as evidence for alerts):\n"
        for a in crit_high + med_low:
            rule_tag = "[AI]" if a.get("rule_type") == "ai_based" else "[RULE]"
            anomaly_block += (
                f"  {rule_tag} [{a['severity'].upper()}] {a['code']}: {a['message']}\n"
            )
    else:
        anomaly_block = "\nDETECTED ANOMALIES: none\n"

    return f"""You are a senior physician doing a comprehensive health review for a patient.
You have received independent analyses from specialists in each health domain.
Your role is to synthesise these into a unified, prioritised global health report.

PATIENT PROFILE:
  Age              : {user_context.get('age', 'unknown')}
  Gender           : {user_context.get('gender', 'unknown')}
  BMI              : {user_context.get('bmi', 'unknown')} ({user_context.get('bmi_category', '')})
  Chronic diseases : {chronic}
  Health goals     : {goals}

DOMAIN ANALYSES (from specialist nodes):
{analyses_block}

{numbers_block}

{anomaly_block}

TASK:
1. Identify cross-domain patterns — where findings in one domain reinforce or
   explain findings in another. Examples of valid patterns:
   - High caffeine intake (nutrition) + poor sleep scores + high stress
   - Sedentary lifestyle + hypertension + high BMI
   - Alcohol consumption + poor sleep + elevated liver markers
   - Low treatment adherence + worsening lab results
   - Smoking + low oxygen saturation + tachycardia

2. Merge all recommendations from all domains, remove duplicates, and rank them
   globally by expected health impact — not by domain order.

3. Generate alerts only for urgent, high-risk findings backed by specific numbers.

4. Determine the overall risk level based on the combination of domain severities
   and any dangerous cross-domain interactions you detect.

{output_schema}"""


# ─────────────────────────────────────────────────────────────
#  NODE
# ─────────────────────────────────────────────────────────────

def aggregation_node(state: HealthAnalysisState) -> dict:
    """
    LangGraph node: final aggregation.

    Reads:  state["domain_analyses"], state["summaries"], state["user_context"]
    Writes: state["aggregation_result"]
    """
    domain_analyses = state.get("domain_analyses", {})
    summaries       = state.get("summaries", {})
    user_context    = state.get("user_context", {})
    anomalies       = state.get("anomalies", [])

    # If no domain produced any analysis, return a minimal result
    non_skipped = [v for v in domain_analyses.values() if not v.get("skipped")]
    if not non_skipped:
        return {
            "aggregation_result": {
                "cross_domain_patterns":  [],
                "global_recommendations": [],
                "alerts":                 [],
                "risk_level":             "low",
                "risk_summary":           "Insufficient data across all domains to produce a meaningful health report.",
            },
            "errors": [],
        }

    # ── Call LLM ─────────────────────────────────────────────
    try:
        prompt = _build_aggregation_prompt(domain_analyses, summaries, user_context, anomalies)
        parsed = call_llm_json(prompt, max_tokens=2048)

    except (json.JSONDecodeError, ValueError) as e:
        return {
            "aggregation_result": _fallback_aggregation(domain_analyses),
            "errors": [f"Aggregation node: JSON parse error — {e}"],
        }
    except Exception as e:
        return {
            "aggregation_result": _fallback_aggregation(domain_analyses),
            "errors": [f"Aggregation node failed: {e}"],
        }

    # ── Validate and return ───────────────────────────────────
    result = {
        "cross_domain_patterns":  list(parsed.get("cross_domain_patterns", [])),
        "global_recommendations": list(parsed.get("global_recommendations", [])),
        "alerts":                 list(parsed.get("alerts", [])),
        "risk_level":             str(parsed.get("risk_level", "low")),
        "risk_summary":           str(parsed.get("risk_summary", "")),
    }

    return {
        "aggregation_result": result,
        "errors": [],
    }


# ─────────────────────────────────────────────────────────────
#  FALLBACK (used when LLM call fails)
# ─────────────────────────────────────────────────────────────

def _fallback_aggregation(domain_analyses: dict) -> dict:
    """
    Rule-based fallback aggregation when LLM is unavailable.
    Collects all issues and recommendations from domain nodes,
    deduplicates, and derives risk level from severity counts.
    """
    all_issues   = []
    all_recs     = []
    severities   = []
    alerts       = []

    SEVERITY_RANK = {"high": 3, "medium": 2, "low": 1, "none": 0}

    for domain, result in domain_analyses.items():
        if result.get("skipped"):
            continue
        sev = result.get("severity", "none")
        severities.append(SEVERITY_RANK.get(sev, 0))

        all_issues.extend(result.get("issues", []))
        all_recs.extend(result.get("recommendations", []))

        if sev == "high":
            alerts.append({
                "level":            "high",
                "message":          f"[{domain.upper()}] {'; '.join(result.get('issues', [])[:2])}",
                "domains":          [domain],
                "suggested_action": result.get("recommendations", ["See a specialist"])[0],
            })

    # Derive overall risk from domain severities
    if not severities:
        risk = "low"
    elif max(severities) >= 3 or sum(s >= 2 for s in severities) >= 3:
        risk = "high"
    elif max(severities) >= 2:
        risk = "medium"
    else:
        risk = "low"

    # Deduplicate recommendations (simple substring check)
    seen, unique_recs = set(), []
    for rec in all_recs:
        key = rec[:60].lower()
        if key not in seen:
            seen.add(key)
            unique_recs.append({"priority": len(unique_recs) + 1, "action": rec, "domain": "multiple"})

    return {
        "cross_domain_patterns":  [],
        "global_recommendations": unique_recs[:6],
        "alerts":                 alerts[:4],
        "risk_level":             risk,
        "risk_summary":           (
            f"Rule-based fallback report (AI unavailable). "
            f"Detected {len(all_issues)} issues across {len(domain_analyses)} domains. "
            f"Overall risk assessed as {risk}."
        ),
    }
