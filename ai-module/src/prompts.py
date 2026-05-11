"""
Prompt constants and builder functions for the AI health analysis engine.
Instructions are in English for optimal LLM comprehension.
All JSON output values must be written in French for the end user.
"""

from __future__ import annotations

_NO_DATA = "Aucune donnée disponible pour ce domaine."

_SYSTEM_PROMPT = """You are an expert health analyst for a premium digital health platform.
You produce professional, accessible, and human health reports — never alarmist, never robotic.
All text values in the JSON output must be written in French.
Return ONLY valid raw JSON. No markdown, no text before or after the JSON."""

_WRITING_RULES = """MANDATORY WRITING STYLE:
- Language: French, formal second person (vous/votre). NEVER "le patient" or "l'utilisateur".
- NEVER use parentheses in text fields.
- NEVER write "La valeur X est Y" → ALWAYS interpret the meaning.
- NEVER use alarmist tone. NEVER make absolute statements.
- Prefer phrasings like: "une tendance à", "des valeurs globalement", "mérite une attention", "à surveiller préventivemant".
- Nuanced phrasing required: "peut contribuer à", "semble indiquer", "un suivi peut être envisagé".
- This system does NOT produce medical diagnoses. Always keep an informative report tone.
- Highlight positive behaviors detected before flagging areas of attention.
- Interpret global trends rather than listing individual figures.

SEVERITY LEVELS (domains):
- stable    = data within normal range, favorable trend
- watch     = slight variation worth monitoring preventively
- moderate  = area of attention warranting gradual adaptation
- high      = situation requiring sustained attention
- priority  = situation requiring prompt management"""

_DOMAIN_SCHEMA = """{
  "sleep":       {"analysis": "interprétation des tendances en 2-3 phrases", "issues": ["observation nuancée si pertinente"], "severity": "stable|watch|moderate|high|priority"},
  "nutrition":   {"analysis": "...", "issues": [...], "severity": "..."},
  "activity":    {"analysis": "...", "issues": [...], "severity": "..."},
  "vital_signs": {"analysis": "...", "issues": [...], "severity": "..."},
  "smoking":     {"analysis": "...", "issues": [...], "severity": "..."},
  "alcohol":     {"analysis": "...", "issues": [...], "severity": "..."},
  "lab_results": {"analysis": "...", "issues": [...], "severity": "..."},
  "treatments":  {"analysis": "...", "issues": [...], "severity": "..."}
}"""

_AGGREGATION_SCHEMA = """{
  "positive_observations": [
    "point positif observé — ex: Une bonne adhérence thérapeutique a été maintenue sur la période"
  ],
  "anomalies": [
    {"code": "CODE_EN_MAJUSCULES", "severity": "watch|moderate|high|priority", "message": "interprétation nuancée en langage accessible", "domains": ["str"]}
  ],
  "global_recommendations": [
    {"priority": 1, "action": "conseil pratique et progressif, sans rigidité", "domain": "str", "impact": "bénéfice attendu formulé avec nuance"}
  ],
  "alerts": [
    {"level": "high|priority", "message": "formulation factuelle et non alarmiste", "domains": ["str"], "suggested_action": "action concrète recommandée"}
  ],
  "risk_level": "low|medium|high",
  "risk_summary": "2-3 phrases d'interprétation globale nuancée, valorisant les points positifs et contextualisant les risques"
}"""


def build_domain_prompt(user_json: str, data_blocks_json: str, checks: str) -> str:
    return f"""Analyze the following health data and produce a per-domain assessment.

MANDATORY FORMAT: Return DIRECTLY a JSON object whose keys are domain names.
Start immediately with {{ — no text, no wrapper key ("domains", "result", etc.).

{_WRITING_RULES}

USER PROFILE:
{user_json}

HEALTH DATA (averages computed over the tracking period):
{data_blocks_json}

PRE-COMPUTED MATHEMATICAL CHECKS (never contradict these conclusions):
{checks}

PER-DOMAIN INSTRUCTIONS:
- analysis: 2-3 sentences of intelligent interpretation in French. Start with positive points where relevant.
  Interpret the global trend, do not list raw numbers.
  Contextualize against the profile (age, chronic conditions, goals).
- issues: only clinically relevant observations (max 3). Ignore minor variations within normal range.
  Nuanced phrasing: "Une tendance à la hausse a été observée" rather than "La tension artérielle est élevée".
- severity: choose from stable|watch|moderate|high|priority based on actual health impact.
  Do NOT overestimate severity for slight variations or globally satisfactory indicators.
- Domains with no data: analysis="{_NO_DATA}", issues=[], severity="stable"

REFERENCE THRESHOLDS:
- Sleep: ≥7/10=satisfactory, 5-6=watch, <5=insufficient
- Stress: ≤6/10=managed, >6=elevated, >8=priority
- Hydration: ≥2L/day=adequate, 1.5-2=slightly insufficient
- Calories: 1800-2500kcal/day=normal range (depending on profile)
- Heart rate: 60-100bpm=normal | SpO2: ≥95%=normal | Blood pressure: <120=optimal, 120-129=elevated, ≥130=hypertension
- Medication adherence: ≥80%=good, 60-80%=needs improvement, <60%=insufficient

OUTPUT FORMAT (start with {{, no text before):
{_DOMAIN_SCHEMA}"""


def build_aggregation_prompt(user_json: str, active_domains: str, checks: str, numeric_data_json: str) -> str:
    return f"""Produce the global synthesis of the health report.

MANDATORY FORMAT: Return DIRECTLY a JSON object.
Start immediately with {{ — no text, no wrapper key.

{_WRITING_RULES}

USER PROFILE:
{user_json}

DOMAIN ANALYSES (summary):
{active_domains}

MATHEMATICAL CHECKS (never contradict):
{checks}

NUMERIC REFERENCE DATA:
{numeric_data_json}

INSTRUCTIONS:

1. positive_observations (2 to 4 items):
   - Identify positive behaviors: good adherence, regular activity, adequate hydration, no smoking, stable blood glucose, etc.
   - Encouraging but measured phrasing: "Une activité physique régulière et soutenue a été maintenue sur la période."
   - DO NOT include if no genuine positive point is detected.

2. anomalies (only true anomalies, not normal variations):
   - Based ONLY on values BELOW thresholds in the mathematical checks.
   - NEVER flag as an anomaly a value ABOVE the WHO threshold or normal ranges.
   - Nuanced phrasing: "Une tendance à..." rather than "Vous présentez une anomalie de..."
   - Severity: watch|moderate|high|priority

3. global_recommendations (3 to 5 recommendations):
   - Personalized based on the profile and detected anomalies.
   - Progressive and realistic: no arbitrary or rigid targets.
   - Practical: "Envisager d'intégrer..." rather than "Vous devez faire X exactement Y fois".
   - Each recommendation must correspond to a detected anomaly or improvement area.
   - impact: phrased with nuance ("peut contribuer à", "pourrait aider à")

4. alerts: ONLY for situations requiring prompt attention (SpO2<90%, stage 2 hypertension ≥140mmHg, critical blood glucose).
   Factual, non-alarmist phrasing.

5. risk_level: low if overall healthy profile, medium if a few areas of attention, high if significant anomalies.

6. risk_summary: 2-3 balanced sentences — mention positive points AND areas of attention.
   Exemple: "Votre profil de santé présente plusieurs indicateurs satisfaisants. Une attention particulière à [domaine] pourrait contribuer à optimiser votre bien-être global."

OUTPUT FORMAT:
{_AGGREGATION_SCHEMA}"""
