"""
Step 4 + Step 5 — AI Analysis Pipeline.

Builds and runs the LangGraph workflow.

Graph topology:
    START
      │
      ▼
  [sleep_node]
      │
      ▼
  [nutrition_node]
      │
      ▼
  [activity_node]
      │
      ▼
  [smoking_node]
      │
      ▼
  [alcohol_node]
      │
      ▼
  [vital_signs_node]
      │
      ▼
  [lab_results_node]
      │
      ▼
  [treatments_node]
      │
      ▼
  [aggregation_node]   ← Step 5: reads all domain_analyses, produces final report
      │
      ▼
     END

Entry point:
    run_pipeline(processed_data: dict) -> dict
"""

from __future__ import annotations

from langgraph.graph import StateGraph, START, END

from ai_pipeline.state import HealthAnalysisState
from ai_pipeline.nodes import (
    sleep_node,
    nutrition_node,
    activity_node,
    smoking_node,
    alcohol_node,
    vital_signs_node,
    lab_results_node,
    treatments_node,
)
from ai_pipeline.aggregator import aggregation_node
from anomaly_detection.detector import detect_anomalies
from anomaly_detection.ai_detector import detect_anomalies_ai


# ─────────────────────────────────────────────────────────────
#  GRAPH ASSEMBLY
# ─────────────────────────────────────────────────────────────

def _build_graph() -> StateGraph:
    graph = StateGraph(HealthAnalysisState)

    # ── Domain nodes (Step 4) ─────────────────────────────────
    graph.add_node("sleep",        sleep_node)
    graph.add_node("nutrition",    nutrition_node)
    graph.add_node("activity",     activity_node)
    graph.add_node("smoking",      smoking_node)
    graph.add_node("alcohol",      alcohol_node)
    graph.add_node("vital_signs",  vital_signs_node)
    graph.add_node("lab_results",  lab_results_node)
    graph.add_node("treatments",   treatments_node)

    # ── Aggregation node (Step 5) ─────────────────────────────
    graph.add_node("aggregation",  aggregation_node)

    # ── Sequential edges ──────────────────────────────────────
    graph.add_edge(START,          "sleep")
    graph.add_edge("sleep",        "nutrition")
    graph.add_edge("nutrition",    "activity")
    graph.add_edge("activity",     "smoking")
    graph.add_edge("smoking",      "alcohol")
    graph.add_edge("alcohol",      "vital_signs")
    graph.add_edge("vital_signs",  "lab_results")
    graph.add_edge("lab_results",  "treatments")
    graph.add_edge("treatments",   "aggregation")
    graph.add_edge("aggregation",  END)

    return graph.compile()


# Compile once at module load — reused across calls
_COMPILED_GRAPH = _build_graph()


# ─────────────────────────────────────────────────────────────
#  ENTRY POINT
# ─────────────────────────────────────────────────────────────

def run_pipeline(processed_data: dict) -> dict:
    """
    Run the full AI analysis pipeline (Steps 4 + 5) on pre-processed user data.

    Args:
        processed_data: Output of process_user_data() (Step 3).

    Returns:
        The complete health analysis report:
        {
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
            "cross_domain_patterns": [
                { "pattern": "...", "domains": [...], "explanation": "...", "severity": "..." }
            ],
            "global_recommendations": [
                { "priority": 1, "action": "...", "domain": "...", "impact": "..." }
            ],
            "alerts": [
                { "level": "high", "message": "...", "domains": [...], "suggested_action": "..." }
            ],
            "risk_level":   "low | medium | high",
            "risk_summary": "...",
            "errors":       [...]
        }
    """
    summaries = processed_data.get("summaries", {})
    profile   = processed_data.get("user_context", {})

    # ── Run anomaly detection BEFORE the graph ────────────────
    # Rule-based layer (always runs, no API needed)
    rule_anomalies = detect_anomalies(summaries, profile)
    # AI-based layer (adds subtle patterns rules miss; skipped silently if no API key)
    ai_anomalies   = detect_anomalies_ai(summaries, profile, rule_anomalies)
    all_anomalies  = rule_anomalies + ai_anomalies

    initial_state: HealthAnalysisState = {
        "user_context":      profile,
        "summaries":         summaries,
        "data_quality":      processed_data.get("data_quality", {}),
        "ready_domains":     processed_data.get("ready_domains", []),
        "sparse_domains":    processed_data.get("sparse_domains", []),
        "domain_analyses":   {},
        "errors":            [],
        "anomalies":         all_anomalies,
        "aggregation_result": {},
    }

    final_state = _COMPILED_GRAPH.invoke(initial_state)

    agg = final_state.get("aggregation_result", {})

    return {
        # Per-domain analyses (Step 4 outputs)
        "domains": final_state.get("domain_analyses", {}),

        # Global synthesis (Step 5 outputs)
        "cross_domain_patterns":  agg.get("cross_domain_patterns", []),
        "global_recommendations": agg.get("global_recommendations", []),
        "alerts":                 agg.get("alerts", []),
        "risk_level":             agg.get("risk_level", "low"),
        "risk_summary":           agg.get("risk_summary", ""),

        # Anomalies (rule-based + AI-based, pre-graph)
        "anomalies": all_anomalies,

        # Non-fatal pipeline errors
        "errors": final_state.get("errors", []),
    }
