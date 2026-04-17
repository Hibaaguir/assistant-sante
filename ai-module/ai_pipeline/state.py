"""
LangGraph state definition for the health analysis pipeline.

The state is a TypedDict that flows through every node.
Each domain node reads from 'summaries' and writes to 'domain_analyses'.
"""

from __future__ import annotations
from typing import Annotated
from typing_extensions import TypedDict
import operator


class DomainAnalysis(TypedDict, total=False):
    """Output contract for every domain node."""
    domain:          str
    analysis:        str          # narrative analysis of the domain
    issues:          list[str]    # detected problems, specific and factual
    recommendations: list[str]    # concrete, actionable steps
    severity:        str          # "none" | "low" | "medium" | "high"
    confidence:      str          # "high" | "partial" | "low" (based on data quality)
    skipped:         bool         # True when domain had no data
    skip_reason:     str          # reason if skipped


class HealthAnalysisState(TypedDict):
    """
    Shared state flowing through the LangGraph pipeline.

    Immutable inputs (set before graph runs):
        user_context   — profile facts (age, BMI, chronic diseases, etc.)
        summaries      — processed domain summaries from Step 3
        data_quality   — completeness + warnings from Step 2
        ready_domains  — domains with enough data for full analysis
        sparse_domains — domains with insufficient data

    Mutable outputs (populated by each node):
        domain_analyses — dict keyed by domain name, filled progressively
        errors          — list of node errors (non-fatal)
    """
    # ── Inputs ────────────────────────────────────────────────
    user_context:   dict
    summaries:      dict
    data_quality:   dict
    ready_domains:  list[str]
    sparse_domains: list[str]

    # ── Outputs (accumulated across nodes) ───────────────────
    # operator.or_ merges dicts: each node adds its key without overwriting others
    domain_analyses:    Annotated[dict, operator.or_]
    errors:             Annotated[list, operator.add]

    # ── Anomalies (injected before graph runs, used by aggregation_node) ──
    anomalies: list[dict]

    # ── Final aggregation output (written by aggregation_node) ─
    aggregation_result: dict
