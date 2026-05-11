"""
AI health analyser.

Pipeline (3 steps):
  1. Summarize raw DB data into simple averages
  2. Domain analyses — first Groq call, per-domain breakdown
  3. Global aggregation — second Groq call, synthesis and recommendations
"""

from __future__ import annotations
from datetime import date as Date
from .config import MODEL, TEMPERATURE, MAX_TOKENS_DOMAINS, MAX_TOKENS_AGGREGATION, get_groq_client
from .db import extract_user_data
from .prompts import _SYSTEM_PROMPT, _NO_DATA, build_domain_prompt, build_aggregation_prompt
from .rules import rule_checks
from .utils import _avg, _dumps, _parse_json


# ── Step 1: Summarize raw DB data ─────────────────────────────

def _summarize(raw: dict) -> dict:
    p = raw.get("user_profile", {})
    h, w = p.get("height"), p.get("current_weight")
    bmi = round(w / (h / 100) ** 2, 1) if h and w and h > 0 else None

    acts = raw.get("activity", [])
    act_dates = sorted({str(r["entry_date"]) for r in acts if r.get("entry_date")})
    if len(act_dates) >= 2:
        span = max((Date.fromisoformat(act_dates[-1]) - Date.fromisoformat(act_dates[0])).days + 1, 1)
    else:
        span = max(len(act_dates), 1)
    active_days_per_week = round(len(act_dates) / span * 7, 1)

    sleep  = raw.get("sleep", [])
    nut    = raw.get("nutrition", [])
    vitals = raw.get("vital_signs", [])
    labs   = raw.get("lab_results", [])
    treats = raw.get("treatments", [])
    smk    = raw.get("smoking", [])
    alc    = raw.get("alcohol", [])

    return {
        "user": {
            "age":              p.get("age"),
            "gender":           p.get("gender"),
            "bmi":              bmi,
            "chronic_diseases": p.get("chronic_diseases", []),
            "goals":            p.get("goals", []),
            "smoker":           p.get("smoker", False),
            "alcoholic":        p.get("alcoholic", False),
        },
        "sleep": {
            "days_tracked":     len(sleep),
            "avg_sleep_score":  _avg([r.get("sleep")    for r in sleep]),
            "avg_stress_score": _avg([r.get("stress")   for r in sleep]),
            "avg_energy_score": _avg([r.get("energy")   for r in sleep]),
            "avg_caffeine":     _avg([r.get("caffeine") for r in sleep]),
        },
        "nutrition": {
            "days_tracked":      len(nut),
            "avg_calories":      _avg([r.get("total_calories") for r in nut]),
            "avg_hydration_L":   _avg([r.get("hydration")      for r in nut]),
            "avg_meals_per_day": _avg([r.get("meal_count")     for r in nut]),
        },
        "activity": {
            "sessions_tracked":     len(acts),
            "active_days_per_week": active_days_per_week,
            "meets_who_threshold":  active_days_per_week >= 5,
            "avg_duration_min":     _avg([r.get("duration_minutes") for r in acts]),
        },
        "vital_signs": {
            "measurements_tracked": len(vitals),
            "avg_heart_rate":       _avg([r.get("heart_rate")         for r in vitals]),
            "avg_systolic_bp":      _avg([r.get("systolic_pressure")  for r in vitals]),
            "avg_diastolic_bp":     _avg([r.get("diastolic_pressure") for r in vitals]),
            "avg_spo2_pct":         _avg([r.get("oxygen_saturation")  for r in vitals]),
        },
        "smoking": {
            "days_tracked":   len(smk),
            "avg_cigarettes": _avg([r.get("cigarettes_per_day") for r in smk]),
            "avg_puffs":      _avg([r.get("puffs_per_day")      for r in smk]),
        },
        "alcohol": {
            "drinking_days": len(alc),
            "avg_glasses":   _avg([r.get("alcohol_glasses") for r in alc]),
        },
        "lab_results": [
            {"type": r.get("analysis_type"), "name": r.get("result_name"), "value": r.get("value"), "unit": r.get("unit")}
            for r in labs
        ],
        "treatments": [
            {"name": r.get("medication_name"), "adherence": r.get("adherence_rate")}
            for r in treats
        ],
    }


# ── Step 2: Domain analyses (LLM call 1) ──────────────────────

def _call_domains(summary: dict, checks: str) -> dict:
    """Call 1 — analyze each health domain."""

    data_blocks = {}
    for domain in ("sleep", "nutrition", "activity", "vital_signs", "smoking", "alcohol", "lab_results", "treatments"):
        d = summary.get(domain, {})
        count_key = {"sleep": "days_tracked", "nutrition": "days_tracked", "activity": "sessions_tracked",
                     "vital_signs": "measurements_tracked", "smoking": "days_tracked",
                     "alcohol": "drinking_days", "lab_results": None, "treatments": None}.get(domain)
        has_data = (count_key is None and bool(d)) or (count_key and d.get(count_key, 0) > 0)
        if has_data:
            data_blocks[domain] = d

    prompt = build_domain_prompt(
        user_json=_dumps(summary["user"]),
        data_blocks_json=_dumps(data_blocks),
        checks=checks,
    )

    response = get_groq_client().chat.completions.create(
        model=MODEL, max_tokens=MAX_TOKENS_DOMAINS, temperature=TEMPERATURE,
        messages=[
            {"role": "system", "content": _SYSTEM_PROMPT},
            {"role": "user",   "content": prompt},
        ],
    )
    result = _parse_json(response.choices[0].message.content)

    if isinstance(result, dict) and "domains" in result and isinstance(result["domains"], dict):
        result = result["domains"]

    return result


# ── Step 3: Global aggregation (LLM call 2) ───────────────────

def _call_aggregation(summary: dict, domains: dict, checks: str) -> dict:
    """Call 2 — global synthesis, positive observations, anomalies, recommendations."""

    active_domains = "\n".join(
        f"[{d.upper()}] severity={v.get('severity','?')} — {v.get('analysis','')[:150]}"
        for d, v in domains.items()
        if not v.get("analysis", "").startswith("Aucune")
    )

    prompt = build_aggregation_prompt(
        user_json=_dumps(summary["user"]),
        active_domains=active_domains,
        checks=checks,
        numeric_data_json=_dumps({k: v for k, v in summary.items() if k != "user"}),
    )

    response = get_groq_client().chat.completions.create(
        model=MODEL, max_tokens=MAX_TOKENS_AGGREGATION, temperature=TEMPERATURE,
        messages=[
            {"role": "system", "content": _SYSTEM_PROMPT},
            {"role": "user",   "content": prompt},
        ],
    )
    result = _parse_json(response.choices[0].message.content)

    # Unwrap common wrapper keys
    for wrapper in ("result", "analysis", "report", "data"):
        if isinstance(result, dict) and list(result.keys()) == [wrapper]:
            result = result[wrapper]
            break

    return result


# ── Public API ──────────────────────────────────────────────────

_EMPTY_DOMAIN = {"analysis": _NO_DATA, "issues": [], "severity": "stable"}
_ALL_DOMAINS  = ("sleep", "nutrition", "activity", "vital_signs", "smoking", "alcohol", "lab_results", "treatments")


def analyze_user(user_id: int) -> dict:
    """Run the full AI health analysis. Returns a report dict ready for the frontend."""
    try:
        raw = extract_user_data(user_id)
    except Exception as exc:
        return _error_report(user_id, str(exc))

    profile = raw.get("user_profile", {})
    summary = _summarize(raw)
    checks  = rule_checks(summary)

    try:
        domains = _call_domains(summary, checks)
    except Exception as exc:
        return _error_report(user_id, f"Analyse par domaine échouée : {exc}")

    for d in _ALL_DOMAINS:
        if d not in domains:
            domains[d] = _EMPTY_DOMAIN.copy()

    try:
        agg = _call_aggregation(summary, domains, checks)
    except Exception as exc:
        agg = {"positive_observations": [], "anomalies": [], "global_recommendations": [],
               "alerts": [], "risk_level": "unknown", "risk_summary": f"Aggregation failed: {exc}"}

    return {
        "user_id":                user_id,
        "user_name":              profile.get("name"),
        "status":                 "success",
        "domains":                domains,
        "positive_observations":  agg.get("positive_observations", []),
        "anomalies":              agg.get("anomalies", []),
        "global_recommendations": agg.get("global_recommendations", []),
        "alerts":                 agg.get("alerts", []),
        "risk_level":             agg.get("risk_level", "low"),
        "risk_summary":           agg.get("risk_summary", ""),
        "meta":                   {"errors": []},
    }


def _error_report(user_id: int, error: str) -> dict:
    return {
        "user_id": user_id, "user_name": None, "status": "error",
        "domains": {d: _EMPTY_DOMAIN.copy() for d in _ALL_DOMAINS},
        "positive_observations": [], "anomalies": [],
        "global_recommendations": [], "alerts": [],
        "risk_level": "unknown", "risk_summary": "",
        "meta": {"errors": [error]},
    }
