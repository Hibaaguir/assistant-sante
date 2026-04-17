"""
LangGraph domain node functions.

Each node:
  1. Reads its domain summary from state['summaries']
  2. Checks if enough data exists (skips gracefully if not)
  3. Calls the Claude API with a structured prompt
  4. Parses the JSON response
  5. Returns a partial state update: { "domain_analyses": { "domain_name": {...} } }

All 8 nodes follow the exact same pattern — only the domain name and prompt differ.
"""

from __future__ import annotations

from ai_pipeline.state import HealthAnalysisState
from ai_pipeline.llm_client import call_llm_json
from ai_pipeline.prompts import (
    prompt_sleep,
    prompt_nutrition,
    prompt_activity,
    prompt_smoking,
    prompt_alcohol,
    prompt_vital_signs,
    prompt_lab_results,
    prompt_treatments,
)

# ─────────────────────────────────────────────────────────────
#  SHARED LLM CALLER
# ─────────────────────────────────────────────────────────────

def _call_llm(prompt: str) -> dict:
    """
    Call Groq, parse the JSON response.
    Returns a validated DomainAnalysis-shaped dict.
    Raises on API or parse error.
    """
    parsed = call_llm_json(prompt, max_tokens=1024)
    # Validate and normalise required keys
    return {
        "analysis":        str(parsed.get("analysis", "")),
        "issues":          list(parsed.get("issues", [])),
        "recommendations": list(parsed.get("recommendations", [])),
        "severity":        str(parsed.get("severity", "low")),
    }


# ─────────────────────────────────────────────────────────────
#  NODE FACTORY
# ─────────────────────────────────────────────────────────────

def _build_node(domain: str, prompt_fn):
    """
    Returns a LangGraph node function for the given domain.

    The returned function:
    - Is named after the domain (for LangGraph graph visualization)
    - Skips gracefully if domain is empty
    - Catches all exceptions without crashing the pipeline
    """
    def node(state: HealthAnalysisState) -> dict:
        summary      = state["summaries"].get(domain, {})
        user_context = state["user_context"]
        sparse       = state.get("sparse_domains", [])

        # ── Skip if empty ─────────────────────────────────────
        if summary.get("_empty") or domain in sparse:
            return {
                "domain_analyses": {
                    domain: {
                        "domain":      domain,
                        "analysis":    f"Insufficient data for {domain} domain analysis.",
                        "issues":      [],
                        "recommendations": [],
                        "severity":    "none",
                        "confidence":  "low",
                        "skipped":     True,
                        "skip_reason": "no_data",
                    }
                },
                "errors": [],
            }

        # ── Determine confidence from data quality ────────────
        completeness = state.get("data_quality", {}).get("completeness", {})
        level = completeness.get(domain, "empty")
        confidence = {"good": "high", "partial": "partial", "empty": "low"}.get(level, "low")

        # ── Call LLM ──────────────────────────────────────────
        try:
            prompt  = prompt_fn(summary, user_context)
            result  = _call_llm(prompt)
            result["domain"]     = domain
            result["confidence"] = confidence
            result["skipped"]    = False
        except Exception as exc:
            error_msg = f"Node '{domain}' failed: {exc}"
            return {
                "domain_analyses": {
                    domain: {
                        "domain":      domain,
                        "analysis":    f"Analysis unavailable due to an error: {exc}",
                        "issues":      [],
                        "recommendations": [],
                        "severity":    "none",
                        "confidence":  "low",
                        "skipped":     True,
                        "skip_reason": "llm_error",
                    }
                },
                "errors": [error_msg],
            }

        return {
            "domain_analyses": {domain: result},
            "errors": [],
        }

    node.__name__ = f"{domain}_node"
    return node


# ─────────────────────────────────────────────────────────────
#  DOMAIN NODES  (exposed for import in pipeline.py)
# ─────────────────────────────────────────────────────────────

sleep_node        = _build_node("sleep",       prompt_sleep)
nutrition_node    = _build_node("nutrition",   prompt_nutrition)
activity_node     = _build_node("activity",    prompt_activity)
smoking_node      = _build_node("smoking",     prompt_smoking)
alcohol_node      = _build_node("alcohol",     prompt_alcohol)
vital_signs_node  = _build_node("vital_signs", prompt_vital_signs)
lab_results_node  = _build_node("lab_results", prompt_lab_results)
treatments_node   = _build_node("treatments",  prompt_treatments)
