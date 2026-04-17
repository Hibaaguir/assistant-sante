"""
All SQL queries for the AI data extraction module, organized by health domain.
Each query takes a single parameter: user_id (int).
No ORM — pure SQL against the existing assistant_sante schema.
"""

# ─────────────────────────────────────────────
#  USER PROFILE
#  Tables: users, health_profiles
# ─────────────────────────────────────────────
QUERY_USER_PROFILE = """
SELECT
    u.id                    AS user_id,
    u.name,
    u.age,
    u.date_of_birth,
    u.role,
    hp.gender,
    hp.height,
    hp.initial_weight,
    hp.current_weight,
    hp.blood_type,
    hp.goals,
    hp.allergies,
    hp.chronic_diseases,
    hp.smoker,
    hp.alcoholic
FROM users u
LEFT JOIN health_profiles hp ON hp.user_id = u.id
WHERE u.id = %s
LIMIT 1
"""

# ─────────────────────────────────────────────
#  SLEEP / STRESS / ENERGY
#  Table: journal_entries
#  Fields: sleep (score 0-10), stress (0-10),
#          energy (0-10), caffeine (cups/day)
# ─────────────────────────────────────────────
QUERY_SLEEP = """
SELECT
    entry_date,
    sleep,
    stress,
    energy,
    caffeine,
    hydration
FROM journal_entries
WHERE user_id = %s
  AND sleep IS NOT NULL
ORDER BY entry_date DESC
"""

# ─────────────────────────────────────────────
#  NUTRITION
#  Tables: journal_entries + meals
#  Aggregates meal calories per entry_date
# ─────────────────────────────────────────────
QUERY_NUTRITION = """
SELECT
    je.entry_date,
    je.hydration,
    je.sugar_intake,
    je.caffeine,
    COUNT(m.id)              AS meal_count,
    SUM(m.calories)          AS total_calories,
    GROUP_CONCAT(
        CONCAT(m.meal_type, ':', COALESCE(m.calories, 0))
        ORDER BY m.meal_type
        SEPARATOR '|'
    )                        AS meals_detail
FROM journal_entries je
LEFT JOIN meals m ON m.journal_entry_id = je.id
WHERE je.user_id = %s
GROUP BY je.id, je.entry_date, je.hydration, je.sugar_intake, je.caffeine
ORDER BY je.entry_date DESC
"""

# ─────────────────────────────────────────────
#  PHYSICAL ACTIVITY
#  Tables: physical_activities -> journal_entries
# ─────────────────────────────────────────────
QUERY_ACTIVITY = """
SELECT
    je.entry_date,
    pa.activity_type,
    pa.duration_minutes,
    pa.intensity
FROM physical_activities pa
JOIN journal_entries je ON je.id = pa.journal_entry_id
WHERE je.user_id = %s
ORDER BY je.entry_date DESC
"""

# ─────────────────────────────────────────────
#  SMOKING (TOBACCO)
#  Tables: tobacco -> journal_entries
# ─────────────────────────────────────────────
QUERY_SMOKING = """
SELECT
    je.entry_date,
    t.tobacco_type,
    t.cigarettes_per_day,
    t.puffs_per_day
FROM tobacco t
JOIN journal_entries je ON je.id = t.journal_entry_id
WHERE je.user_id = %s
ORDER BY je.entry_date DESC
"""

# ─────────────────────────────────────────────
#  ALCOHOL
#  Table: journal_entries
#  Fields: alcohol (bool), alcohol_glasses (int)
# ─────────────────────────────────────────────
QUERY_ALCOHOL = """
SELECT
    entry_date,
    alcohol,
    alcohol_glasses
FROM journal_entries
WHERE user_id = %s
  AND alcohol = TRUE
ORDER BY entry_date DESC
"""

# ─────────────────────────────────────────────
#  VITAL SIGNS
#  Tables: vital_signs -> health_data
# ─────────────────────────────────────────────
QUERY_VITAL_SIGNS = """
SELECT
    hd.date,
    vs.measured_at,
    vs.heart_rate,
    vs.systolic_pressure,
    vs.diastolic_pressure,
    vs.oxygen_saturation
FROM vital_signs vs
JOIN health_data hd ON hd.id = vs.health_data_id
WHERE hd.user_id = %s
ORDER BY vs.measured_at DESC
"""

# ─────────────────────────────────────────────
#  LAB / ANALYSIS RESULTS
#  Tables: analysis_results -> health_data
# ─────────────────────────────────────────────
QUERY_LAB_RESULTS = """
SELECT
    hd.date,
    ar.analysis_date,
    ar.analysis_type,
    ar.result_name,
    ar.value,
    ar.unit
FROM analysis_results ar
JOIN health_data hd ON hd.id = ar.health_data_id
WHERE hd.user_id = %s
ORDER BY ar.analysis_date DESC
"""

# ─────────────────────────────────────────────
#  TREATMENTS + ADHERENCE
#  Tables: treatments -> health_data
#          treatment_checks (with user_id since migration 2026-04-14)
#          treatment_catalogs (medication name)
# ─────────────────────────────────────────────
QUERY_TREATMENTS = """
SELECT
    tc_cat.treatment_name                AS medication_name,
    tc_cat.treatment_type                AS medication_category,
    t.dose,
    t.frequency,
    t.daily_doses,
    t.start_date,
    t.end_date,
    COUNT(tchk.id)                       AS total_checks,
    SUM(CASE WHEN tchk.taken THEN 1 ELSE 0 END) AS taken_count
FROM treatments t
JOIN health_data hd      ON hd.id = t.health_data_id
LEFT JOIN treatment_catalogs tc_cat ON tc_cat.id = t.treatment_catalog_id
LEFT JOIN treatment_checks tchk     ON tchk.treatment_id = t.id
                                    AND tchk.user_id = %s
WHERE hd.user_id = %s
GROUP BY
    t.id,
    tc_cat.treatment_name,
    tc_cat.treatment_type,
    t.dose,
    t.frequency,
    t.daily_doses,
    t.start_date,
    t.end_date
ORDER BY t.start_date DESC
"""
