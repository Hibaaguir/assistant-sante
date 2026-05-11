"""
Rule-based pre-checks for the AI health analysis engine.
Computes key threshold verdicts so the LLM never contradicts math.
"""

from __future__ import annotations


def rule_checks(s: dict) -> str:
    """Pre-compute key thresholds so the LLM never contradicts math."""
    lines = []

    adpw = s["activity"].get("active_days_per_week")
    if adpw is not None:
        if adpw >= 5:
            lines.append(f"ACTIVITY: {adpw} days/week — ABOVE WHO threshold. Do NOT flag as insufficient.")
        else:
            lines.append(f"ACTIVITY: {adpw} days/week — BELOW WHO threshold (5 days). Flag as insufficient.")

    avg_sleep = s["sleep"].get("avg_sleep_score")
    if avg_sleep is not None:
        lines.append(f"SLEEP: {avg_sleep}/10 — {'GOOD (≥7)' if avg_sleep >= 7 else 'INSUFFICIENT (<7)'}.")

    avg_stress = s["sleep"].get("avg_stress_score")
    if avg_stress is not None:
        lines.append(f"STRESS: {avg_stress}/10 — {'ELEVATED (>6)' if avg_stress > 6 else 'NORMAL (≤6)'}.")

    hyd = s["nutrition"].get("avg_hydration_L")
    if hyd is not None:
        lines.append(f"HYDRATION: {hyd} L/day — {'ADEQUATE (≥2L)' if hyd >= 2.0 else 'INSUFFICIENT (<2L)'}.")

    sys_bp = s["vital_signs"].get("avg_systolic_bp")
    if sys_bp is not None:
        if sys_bp >= 140:   lines.append(f"BLOOD PRESSURE: {sys_bp} mmHg — STAGE 2 HYPERTENSION.")
        elif sys_bp >= 130: lines.append(f"BLOOD PRESSURE: {sys_bp} mmHg — STAGE 1 HYPERTENSION.")
        elif sys_bp >= 120: lines.append(f"BLOOD PRESSURE: {sys_bp} mmHg — ELEVATED.")
        else:               lines.append(f"BLOOD PRESSURE: {sys_bp} mmHg — NORMAL.")

    spo2 = s["vital_signs"].get("avg_spo2_pct")
    if spo2 is not None:
        if spo2 < 90:   lines.append(f"SPO2: {spo2}% — CRITICAL (<90%).")
        elif spo2 < 95: lines.append(f"SPO2: {spo2}% — ABNORMAL (<95%).")
        else:           lines.append(f"SPO2: {spo2}% — NORMAL (≥95%).")

    return "\n".join(lines) if lines else "Insufficient data for rule checks."
