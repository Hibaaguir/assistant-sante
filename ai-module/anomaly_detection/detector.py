"""
Rule-based anomaly detection engine.

Entry point:
    detect_anomalies(summaries: dict, profile: dict) -> list[dict]

Runs all per-domain rules AND cross-domain rules.
Returns a deduplicated, severity-sorted list of anomaly dicts.

Inputs come from Step 3 (processed_data["summaries"]) and
Step 2 (organized_data["user_profile"]).
"""

from __future__ import annotations
from anomaly_detection.rules import (
    Anomaly,
    SleepThresholds      as ST,
    NutritionThresholds  as NT,
    ActivityThresholds   as AT,
    SmokingThresholds    as SMT,
    AlcoholThresholds    as ALT,
    VitalSignsThresholds as VT,
    TreatmentThresholds  as TT,
    CROSS_DOMAIN_RULES,
)

_SEVERITY_RANK = {"critical": 0, "high": 1, "medium": 2, "low": 3}


# ─────────────────────────────────────────────────────────────
#  PER-DOMAIN RULE RUNNERS
# ─────────────────────────────────────────────────────────────

def _check_sleep(s: dict, profile: dict) -> list[Anomaly]:
    anomalies = []
    if s.get("_empty"):
        return anomalies

    avg_sleep  = s.get("avg_sleep_score")
    low_pct    = s.get("low_sleep_frequency_pct") or 0
    variability = s.get("sleep_variability") or 0
    stress     = s.get("avg_stress_score") or 0
    energy     = s.get("avg_energy_score") or 10
    caffeine   = s.get("avg_caffeine_cups") or 0
    trend      = s.get("sleep_trend", "")

    # Chronic sleep deprivation
    if avg_sleep is not None and avg_sleep < ST.CRITICAL_SCORE:
        anomalies.append(Anomaly(
            code="SLEEP_CRITICAL_DEPRIVATION",
            domain="sleep",
            severity="critical",
            message=(f"User shows critical sleep deprivation: average sleep score {avg_sleep}/10 "
                     f"with {low_pct}% of nights rated ≤4."),
            evidence={"avg_sleep_score": avg_sleep, "low_sleep_frequency_pct": low_pct},
        ))
    elif avg_sleep is not None and avg_sleep < ST.CHRONIC_DEPRIVATION_SCORE and low_pct >= ST.CHRONIC_DEPRIVATION_PCT:
        anomalies.append(Anomaly(
            code="SLEEP_CHRONIC_DEPRIVATION",
            domain="sleep",
            severity="high",
            message=(f"User shows chronic sleep deprivation: average score {avg_sleep}/10 "
                     f"over {s.get('days_tracked')} days, with {low_pct}% low-sleep nights."),
            evidence={"avg_sleep_score": avg_sleep, "low_sleep_frequency_pct": low_pct,
                      "days_tracked": s.get("days_tracked")},
        ))

    # Worsening trend + already poor sleep
    if trend == "worsening" and avg_sleep is not None and avg_sleep < 6.0:
        anomalies.append(Anomaly(
            code="SLEEP_WORSENING_TREND",
            domain="sleep",
            severity="medium",
            message=(f"Sleep quality is actively worsening (trend: {trend}) "
                     f"from an already poor baseline of {avg_sleep}/10."),
            evidence={"sleep_trend": trend, "avg_sleep_score": avg_sleep},
        ))

    # Highly irregular sleep (high variability)
    if variability >= ST.HIGH_VARIABILITY:
        anomalies.append(Anomaly(
            code="SLEEP_HIGH_VARIABILITY",
            domain="sleep",
            severity="medium",
            message=(f"Sleep is highly irregular: score variability={variability:.1f} "
                     f"(threshold ≥{ST.HIGH_VARIABILITY}) — suggests inconsistent sleep schedule."),
            evidence={"sleep_variability": variability},
        ))

    # Elevated chronic stress
    if stress >= ST.HIGH_STRESS:
        anomalies.append(Anomaly(
            code="SLEEP_HIGH_CHRONIC_STRESS",
            domain="sleep",
            severity="medium",
            message=f"Chronic elevated stress detected: average stress score {stress}/10 (threshold ≥{ST.HIGH_STRESS}).",
            evidence={"avg_stress_score": stress},
        ))

    # Chronic fatigue
    if energy <= ST.LOW_ENERGY and avg_sleep is not None and avg_sleep < 6.0:
        anomalies.append(Anomaly(
            code="SLEEP_CHRONIC_FATIGUE",
            domain="sleep",
            severity="medium",
            message=(f"Chronic fatigue pattern: energy score {energy}/10 combined "
                     f"with poor sleep ({avg_sleep}/10)."),
            evidence={"avg_energy_score": energy, "avg_sleep_score": avg_sleep},
        ))

    return anomalies


def _check_nutrition(n: dict, profile: dict) -> list[Anomaly]:
    anomalies = []
    if n.get("_empty"):
        return anomalies

    calories   = n.get("avg_daily_calories")
    hydration  = n.get("avg_hydration_liters") or 0
    caffeine   = n.get("avg_caffeine_cups") or 0
    sugar_dist = n.get("sugar_intake_distribution") or {}
    no_meal    = n.get("no_meal_days_pct") or 0

    # Severe caloric restriction
    if calories is not None and calories < NT.MIN_CALORIES:
        anomalies.append(Anomaly(
            code="NUTRITION_SEVERE_RESTRICTION",
            domain="nutrition",
            severity="high",
            message=(f"Severe caloric restriction detected: average {calories} kcal/day "
                     f"(minimum safe threshold: {NT.MIN_CALORIES} kcal/day)."),
            evidence={"avg_daily_calories": calories},
        ))
    elif calories is not None and calories > NT.MAX_CALORIES:
        anomalies.append(Anomaly(
            code="NUTRITION_CALORIC_EXCESS",
            domain="nutrition",
            severity="medium",
            message=f"Persistent caloric excess: average {calories} kcal/day (threshold: {NT.MAX_CALORIES} kcal/day).",
            evidence={"avg_daily_calories": calories},
        ))

    # Critical dehydration
    if hydration < NT.CRITICAL_HYDRATION and hydration > 0:
        anomalies.append(Anomaly(
            code="NUTRITION_CRITICAL_DEHYDRATION",
            domain="nutrition",
            severity="high",
            message=f"Critical dehydration: average {hydration}L/day (critical threshold: {NT.CRITICAL_HYDRATION}L).",
            evidence={"avg_hydration_liters": hydration},
        ))
    elif hydration < NT.MIN_HYDRATION and hydration > 0:
        anomalies.append(Anomaly(
            code="NUTRITION_CHRONIC_DEHYDRATION",
            domain="nutrition",
            severity="medium",
            message=f"Chronic dehydration: average {hydration}L/day (recommended minimum: {NT.MIN_HYDRATION}L).",
            evidence={"avg_hydration_liters": hydration},
        ))

    # Excessive caffeine
    if caffeine > NT.MAX_CAFFEINE:
        anomalies.append(Anomaly(
            code="NUTRITION_EXCESSIVE_CAFFEINE",
            domain="nutrition",
            severity="low",
            message=f"Excessive caffeine consumption: {caffeine} cups/day average (threshold: {NT.MAX_CAFFEINE}).",
            evidence={"avg_caffeine_cups": caffeine},
        ))

    # Frequent high sugar days
    high_sugar_pct = sugar_dist.get("high", 0)
    if high_sugar_pct >= NT.HIGH_SUGAR_DAYS_PCT:
        anomalies.append(Anomaly(
            code="NUTRITION_HIGH_SUGAR_FREQUENCY",
            domain="nutrition",
            severity="medium",
            message=f"High sugar intake on {high_sugar_pct}% of tracked days (threshold: {NT.HIGH_SUGAR_DAYS_PCT}%).",
            evidence={"high_sugar_days_pct": high_sugar_pct},
        ))

    # Missing meals
    if no_meal >= NT.NO_MEAL_DAYS_PCT:
        anomalies.append(Anomaly(
            code="NUTRITION_FREQUENT_SKIPPED_MEALS",
            domain="nutrition",
            severity="medium",
            message=f"Meals not logged on {no_meal}% of tracked days — suggests frequent meal skipping.",
            evidence={"no_meal_days_pct": no_meal},
        ))

    return anomalies


def _check_activity(a: dict, profile: dict) -> list[Anomaly]:
    anomalies = []

    sedentary_pct       = a.get("sedentary_days_pct") or 0
    active_days_per_week = a.get("active_days_per_week") or 0

    if a.get("_empty") or active_days_per_week == 0:
        bmi_cat = profile.get("bmi_category", "")
        severity = "high" if bmi_cat in ("overweight", "obese") else "medium"
        anomalies.append(Anomaly(
            code="ACTIVITY_COMPLETELY_SEDENTARY",
            domain="activity",
            severity=severity,
            message="No physical activity recorded. Patient appears completely sedentary.",
            evidence={"active_days_per_week": 0, "bmi_category": bmi_cat},
        ))
        return anomalies

    # Sedentary majority of days
    if sedentary_pct >= AT.SEDENTARY_DAYS_PCT:
        anomalies.append(Anomaly(
            code="ACTIVITY_HIGHLY_SEDENTARY",
            domain="activity",
            severity="medium",
            message=(f"Highly sedentary lifestyle: {sedentary_pct}% of tracked days with no activity "
                     f"({active_days_per_week} active days/week)."),
            evidence={"sedentary_days_pct": sedentary_pct, "active_days_per_week": active_days_per_week},
        ))

    # Below WHO minimum
    if 0 < active_days_per_week < AT.WHO_MIN_ACTIVE_DAYS:
        anomalies.append(Anomaly(
            code="ACTIVITY_BELOW_WHO_MINIMUM",
            domain="activity",
            severity="low",
            message=(f"Physical activity below WHO minimum: {active_days_per_week} active days/week "
                     f"(WHO recommends ≥{AT.WHO_MIN_ACTIVE_DAYS})."),
            evidence={"active_days_per_week": active_days_per_week},
        ))

    return anomalies


def _check_smoking(sm: dict, profile: dict) -> list[Anomaly]:
    anomalies = []

    if sm.get("_empty"):
        if profile.get("smoker"):
            anomalies.append(Anomaly(
                code="SMOKING_PROFILE_NO_LOGS",
                domain="smoking",
                severity="low",
                message="Health profile marks user as smoker but no tobacco logs found — data gap.",
                evidence={"profile_smoker": True},
            ))
        return anomalies

    avg_units     = sm.get("avg_daily_units") or 0
    smoking_days  = sm.get("smoking_days") or 0
    heavy_pct     = sm.get("heavy_smoking_days_pct") or 0
    trend         = sm.get("smoking_trend", "")

    # Heavy smoker
    if avg_units > SMT.HEAVY_SMOKER_UNITS:
        anomalies.append(Anomaly(
            code="SMOKING_HEAVY_CHRONIC",
            domain="smoking",
            severity="high",
            message=(f"Heavy chronic smoker: {avg_units} cigarette-equivalents/day average, "
                     f"{heavy_pct}% of days exceeding 20 units."),
            evidence={"avg_daily_units": avg_units, "heavy_smoking_days_pct": heavy_pct},
        ))
    elif smoking_days >= SMT.ANY_SMOKING_CHRONIC_DAYS:
        anomalies.append(Anomaly(
            code="SMOKING_CHRONIC",
            domain="smoking",
            severity="medium",
            message=f"Chronic tobacco use logged over {smoking_days} days ({avg_units} units/day avg).",
            evidence={"smoking_days": smoking_days, "avg_daily_units": avg_units},
        ))

    # Increasing trend
    if trend == "worsening" and smoking_days >= 7:
        anomalies.append(Anomaly(
            code="SMOKING_INCREASING_TREND",
            domain="smoking",
            severity="medium",
            message=f"Tobacco use is increasing (trend: worsening) over the tracked period.",
            evidence={"smoking_trend": trend, "avg_daily_units": avg_units},
        ))

    return anomalies


def _check_alcohol(al: dict, profile: dict) -> list[Anomaly]:
    anomalies = []

    if al.get("_empty"):
        if profile.get("alcoholic"):
            anomalies.append(Anomaly(
                code="ALCOHOL_PROFILE_NO_LOGS",
                domain="alcohol",
                severity="low",
                message="Health profile marks user as alcoholic but no alcohol logs found — data gap.",
                evidence={"profile_alcoholic": True},
            ))
        return anomalies

    drinking_days = al.get("drinking_days") or 0
    avg_glasses   = al.get("avg_glasses_on_drinking_days") or 0
    risky_pct     = al.get("risky_drinking_days_pct") or 0
    trend         = al.get("drinking_trend", "")

    # Majority of drinking days are risky
    if risky_pct >= ALT.RISKY_DAYS_PCT:
        severity = "high" if risky_pct >= 75 else "medium"
        anomalies.append(Anomaly(
            code="ALCOHOL_HIGH_RISKY_FREQUENCY",
            domain="alcohol",
            severity=severity,
            message=(f"High-risk alcohol pattern: {risky_pct}% of drinking days exceed 2 glasses "
                     f"(avg {avg_glasses} glasses/drinking day, {drinking_days} drinking days total)."),
            evidence={"risky_drinking_days_pct": risky_pct, "avg_glasses_on_drinking_days": avg_glasses,
                      "drinking_days": drinking_days},
        ))

    # High frequency
    if drinking_days >= ALT.HIGH_FREQUENCY_DAYS:
        anomalies.append(Anomaly(
            code="ALCOHOL_HIGH_FREQUENCY",
            domain="alcohol",
            severity="medium",
            message=f"Frequent alcohol consumption: {drinking_days} drinking days logged.",
            evidence={"drinking_days": drinking_days},
        ))

    # Increasing trend
    if trend == "worsening" and drinking_days >= 7:
        anomalies.append(Anomaly(
            code="ALCOHOL_INCREASING_TREND",
            domain="alcohol",
            severity="medium",
            message=f"Alcohol consumption is increasing over the tracked period.",
            evidence={"drinking_trend": trend, "avg_glasses_on_drinking_days": avg_glasses},
        ))

    return anomalies


def _check_vital_signs(v: dict, profile: dict) -> list[Anomaly]:
    anomalies = []
    if v.get("_empty"):
        return anomalies

    hypert_pct  = v.get("hypertension_episodes_pct") or 0
    tachy_pct   = v.get("tachycardia_episodes_pct") or 0
    brady_pct   = v.get("bradycardia_episodes_pct") or 0
    spo2_pct    = v.get("low_spo2_episodes_pct") or 0
    avg_sys     = v.get("avg_systolic_pressure") or 0
    avg_spo2    = v.get("avg_oxygen_saturation") or 100

    # Persistent hypertension
    if avg_sys >= 180:
        anomalies.append(Anomaly(
            code="VITALS_HYPERTENSIVE_CRISIS",
            domain="vital_signs",
            severity="critical",
            message=(f"Average systolic blood pressure {avg_sys} mmHg — "
                     f"hypertensive crisis threshold (≥180 mmHg). Immediate medical attention required."),
            evidence={"avg_systolic_pressure": avg_sys, "hypertension_episodes_pct": hypert_pct},
        ))
    elif hypert_pct >= VT.HYPERTENSION_PCT:
        anomalies.append(Anomaly(
            code="VITALS_PERSISTENT_HYPERTENSION",
            domain="vital_signs",
            severity="high",
            message=(f"Persistent hypertension: {hypert_pct}% of BP measurements ≥140 mmHg "
                     f"(avg systolic: {avg_sys} mmHg)."),
            evidence={"hypertension_episodes_pct": hypert_pct, "avg_systolic_pressure": avg_sys},
        ))

    # Frequent tachycardia
    if tachy_pct >= VT.TACHYCARDIA_PCT:
        anomalies.append(Anomaly(
            code="VITALS_FREQUENT_TACHYCARDIA",
            domain="vital_signs",
            severity="medium",
            message=f"Frequent tachycardia: {tachy_pct}% of HR readings >100 bpm.",
            evidence={"tachycardia_episodes_pct": tachy_pct, "avg_heart_rate": v.get("avg_heart_rate")},
        ))

    # Frequent bradycardia
    if brady_pct >= VT.BRADYCARDIA_PCT:
        anomalies.append(Anomaly(
            code="VITALS_FREQUENT_BRADYCARDIA",
            domain="vital_signs",
            severity="medium",
            message=f"Frequent bradycardia: {brady_pct}% of HR readings <60 bpm.",
            evidence={"bradycardia_episodes_pct": brady_pct, "avg_heart_rate": v.get("avg_heart_rate")},
        ))

    # Low oxygen saturation
    if avg_spo2 < VT.CRITICAL_SPO2:
        anomalies.append(Anomaly(
            code="VITALS_CRITICAL_LOW_SPO2",
            domain="vital_signs",
            severity="critical",
            message=(f"Critical average oxygen saturation: {avg_spo2}% "
                     f"(critical threshold <{VT.CRITICAL_SPO2}%). Urgent respiratory evaluation needed."),
            evidence={"avg_oxygen_saturation": avg_spo2},
        ))
    elif spo2_pct >= VT.LOW_SPO2_PCT:
        anomalies.append(Anomaly(
            code="VITALS_LOW_SPO2_EPISODES",
            domain="vital_signs",
            severity="high",
            message=(f"Repeated low oxygen saturation episodes: {spo2_pct}% of readings <95% "
                     f"(avg SpO2: {avg_spo2}%)."),
            evidence={"low_spo2_episodes_pct": spo2_pct, "avg_oxygen_saturation": avg_spo2},
        ))

    return anomalies


def _check_treatments(tr: dict, profile: dict) -> list[Anomaly]:
    anomalies = []
    if tr.get("_empty"):
        return anomalies

    avg_adh  = tr.get("avg_adherence_rate")
    low_meds = tr.get("low_adherence_medications") or []

    for med in low_meds:
        severity = "critical" if (avg_adh or 1.0) < TT.CRITICAL_ADHERENCE else "high"
        anomalies.append(Anomaly(
            code="TREATMENT_LOW_ADHERENCE",
            domain="treatments",
            severity=severity,
            message=(f"Low medication adherence for '{med}': "
                     f"overall avg adherence {round((avg_adh or 0)*100)}% "
                     f"(threshold: {int(TT.LOW_ADHERENCE*100)}%)."),
            evidence={"medication": med, "avg_adherence_rate": avg_adh},
        ))

    return anomalies


# ─────────────────────────────────────────────────────────────
#  CROSS-DOMAIN RULE ENGINE
# ─────────────────────────────────────────────────────────────

def _check_cross_domain(summaries: dict) -> list[Anomaly]:
    anomalies = []
    s_map = {
        "sleep":       summaries.get("sleep",       {}),
        "nutrition":   summaries.get("nutrition",   {}),
        "activity":    summaries.get("activity",    {}),
        "smoking":     summaries.get("smoking",     {}),
        "alcohol":     summaries.get("alcohol",     {}),
        "vital_signs": summaries.get("vital_signs", {}),
        "lab_results": summaries.get("lab_results", {}),
        "treatments":  summaries.get("treatments",  {}),
    }

    for rule in CROSS_DOMAIN_RULES:
        domains = rule["domains"]
        if len(domains) != 2:
            continue
        d1, d2 = domains
        s1, s2 = s_map.get(d1, {}), s_map.get(d2, {})

        # Skip if either domain is empty
        if s1.get("_empty") or s2.get("_empty"):
            continue

        try:
            triggered = rule["check"](s1, s2)
        except Exception:
            continue

        if not triggered:
            continue

        # Build evidence dict from specified keys
        evidence = {}
        for domain, keys in rule.get("evidence_keys", {}).items():
            src = s_map.get(domain, {})
            for k in keys:
                val = src.get(k)
                if val is not None:
                    evidence[k] = val

        # Fill description template
        try:
            msg = rule["description"].format(**{**s1, **s2, **evidence})
        except KeyError:
            msg = rule["description"]

        anomalies.append(Anomaly(
            code=rule["code"],
            domain=domains[0],
            severity=rule["severity"],
            message=msg,
            evidence=evidence,
            domains=domains,
        ))

    return anomalies


# ─────────────────────────────────────────────────────────────
#  MAIN ENTRY POINT
# ─────────────────────────────────────────────────────────────

def detect_anomalies(summaries: dict, profile: dict) -> list[dict]:
    """
    Run all rule-based anomaly checks.

    Args:
        summaries: Output of process_user_data()["summaries"] (Step 3).
        profile:   Output of organize_user_data()["user_profile"] (Step 2).

    Returns:
        Deduplicated, severity-sorted list of anomaly dicts.
    """
    all_anomalies: list[Anomaly] = []

    checkers = [
        ("sleep",       _check_sleep,       summaries.get("sleep",       {})),
        ("nutrition",   _check_nutrition,   summaries.get("nutrition",   {})),
        ("activity",    _check_activity,    summaries.get("activity",    {})),
        ("smoking",     _check_smoking,     summaries.get("smoking",     {})),
        ("alcohol",     _check_alcohol,     summaries.get("alcohol",     {})),
        ("vital_signs", _check_vital_signs, summaries.get("vital_signs", {})),
        ("treatments",  _check_treatments,  summaries.get("treatments",  {})),
    ]

    for _domain, checker_fn, domain_summary in checkers:
        try:
            all_anomalies.extend(checker_fn(domain_summary, profile))
        except Exception as exc:
            # Never let one bad checker crash the whole detection pass
            all_anomalies.append(Anomaly(
                code=f"{_domain.upper()}_CHECK_ERROR",
                domain=_domain,
                severity="low",
                message=f"Anomaly check failed for domain '{_domain}': {exc}",
                evidence={},
            ))

    # Cross-domain rules
    all_anomalies.extend(_check_cross_domain(summaries))

    # Deduplicate by code
    seen_codes: set[str] = set()
    unique: list[Anomaly] = []
    for a in all_anomalies:
        if a.code not in seen_codes:
            seen_codes.add(a.code)
            unique.append(a)

    # Sort: critical → high → medium → low
    unique.sort(key=lambda x: _SEVERITY_RANK.get(x.severity, 99))

    return [a.to_dict() for a in unique]
