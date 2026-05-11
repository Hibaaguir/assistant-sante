"""Shared utility functions for the AI health module."""

from __future__ import annotations
import json, statistics
from decimal import Decimal


def _avg(vals: list) -> float | None:
    nums = [float(v) for v in vals if v is not None]
    return round(statistics.mean(nums), 2) if nums else None


def _dumps(obj) -> str:
    """json.dumps with Decimal support (MySQL returns Decimal for SUM/numeric fields)."""
    return json.dumps(obj, ensure_ascii=False, indent=2,
                      default=lambda x: float(x) if isinstance(x, Decimal) else str(x))


def _parse_json(text: str) -> dict | list:
    """Extract and parse JSON even if the LLM wraps it in extra text."""
    text = text.strip()
    for start_char, end_char in [('{', '}'), ('[', ']')]:
        start = text.find(start_char)
        end   = text.rfind(end_char)
        if start != -1 and end != -1 and end > start:
            try:
                return json.loads(text[start:end + 1])
            except json.JSONDecodeError:
                continue
    raise ValueError(f"No valid JSON found in LLM response: {text[:300]}")
