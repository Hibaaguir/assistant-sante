"""
Analyse les données du journal quotidien pour détecter :
- energy  : niveau d'énergie (1-10) basé sur sommeil, stress, hydratation, activité
- sugar   : apport en sucre (low/medium/high) basé sur les repas

Appelé par le backend PHP :
    python meal_analysis.py '<json>'

Sortie JSON : {"sugar": "high", "energy": 3}
"""

from __future__ import annotations
import json
import sys

from config import load_groq_api_key
from ai import _call_llm


def analyze_journal(data: dict) -> dict:
    sleep    = float(data.get("sleep") or 0)
    stress   = int(data.get("stress") or 0)
    hydration = float(data.get("hydration") or 0)
    activity = int(data.get("activity_duration") or 0)
    meals    = data.get("meals") or []

    meals_text = "\n".join(
        f"- {m.get('label') or m.get('description') or '?'}"
        + (f" ({int(m['calories'])} kcal)" if m.get("calories") else "")
        for m in meals
    ) if meals else "Aucun repas renseigné"

    prompt = f"""Tu es un assistant médical. Analyse ces données de santé journalières :

Sommeil     : {sleep} heures (idéal = 8h)
Stress      : {stress}/10 (0 = aucun stress, 10 = stress extrême)
Hydratation : {hydration} litres
Activité    : {activity} minutes
Repas :
{meals_text}

Règles pour "energy" (1 à 10) :
- Sommeil < 5h → énergie très basse (1-3)
- Sommeil 5-7h → énergie moyenne (4-6)
- Sommeil >= 7h → bonne base d'énergie (6-8)
- Stress >= 7 → réduit l'énergie de 2-3 points
- Hydratation < 1L → réduit l'énergie de 1 point
- Activité physique modérée → ajoute 1 point

Règles pour "sugar" :
- Repas sucrés, sodas, pâtisseries, fast food → "high"
- Repas équilibrés avec quelques sucres → "medium"
- Repas sains, légumes, protéines → "low"
- Aucun repas → "low"

Réponds UNIQUEMENT avec ce JSON sans explication :
{{"energy": 4, "sugar": "medium"}}"""

    result = _call_llm(prompt, max_tokens=32)

    sugar = result.get("sugar", "low")
    if sugar not in ("low", "medium", "high"):
        sugar = "low"

    try:
        energy = max(1, min(10, int(result.get("energy", 5))))
    except (TypeError, ValueError):
        energy = 5

    return {"sugar": sugar, "energy": energy}


if __name__ == "__main__":
    load_groq_api_key()
    try:
        raw  = sys.argv[1] if len(sys.argv) > 1 else "{}"
        data = json.loads(raw)
        print(json.dumps(analyze_journal(data)))
    except Exception as e:
        print(json.dumps({"error": str(e)}))
