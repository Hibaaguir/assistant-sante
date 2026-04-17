"""
LLM Client — Groq backend.

Single place for all LLM calls in the pipeline.
Swap provider here; nothing else needs to change.

Model used: llama-3.3-70b-versatile
  - Best quality available on Groq free tier
  - Handles structured JSON output reliably
  - Free: ~14,400 requests / day
"""

from __future__ import annotations
import json
import os
import re

from groq import Groq

# ─────────────────────────────────────────────────────────────
#  CLIENT (lazy singleton)
# ─────────────────────────────────────────────────────────────

_client: Groq | None = None
MODEL = "llama-3.3-70b-versatile"


def _get_client() -> Groq:
    global _client
    if _client is None:
        api_key = os.environ.get("GROQ_API_KEY")
        if not api_key:
            raise EnvironmentError(
                "GROQ_API_KEY not set. "
                "Add GROQ_API_KEY=gsk_... to backend/.env"
            )
        _client = Groq(api_key=api_key)
    return _client


# ─────────────────────────────────────────────────────────────
#  PUBLIC CALL FUNCTION
# ─────────────────────────────────────────────────────────────

def call_llm(prompt: str, max_tokens: int = 1024) -> str:
    """
    Send a prompt to Groq and return the raw text response.

    Args:
        prompt:     The full prompt string.
        max_tokens: Max output tokens (default 1024, use 2048 for aggregation).

    Returns:
        Raw text response from the model.

    Raises:
        EnvironmentError: If GROQ_API_KEY is not set.
        Exception:        On API errors (rate limit, network, etc.).
    """
    client = _get_client()
    response = client.chat.completions.create(
        model=MODEL,
        max_tokens=max_tokens,
        temperature=0.3,        # low temp = consistent, factual output
        messages=[
            {
                "role": "system",
                "content": (
                    "You are a medical data analyst. "
                    "Always respond with valid JSON only. "
                    "Never include markdown, explanations, or text outside the JSON."
                ),
            },
            {"role": "user", "content": prompt},
        ],
    )
    return response.choices[0].message.content.strip()


def call_llm_json(prompt: str, max_tokens: int = 1024) -> dict | list:
    """
    Call the LLM and parse the response as JSON.

    Returns:
        Parsed dict or list.

    Raises:
        ValueError: If the model returns non-JSON output.
    """
    raw = call_llm(prompt, max_tokens=max_tokens)
    # Strip markdown fences if the model added them despite instructions
    cleaned = re.sub(r"^```(?:json)?\s*|\s*```$", "", raw, flags=re.DOTALL).strip()
    try:
        return json.loads(cleaned)
    except json.JSONDecodeError as e:
        raise ValueError(
            f"Model returned non-JSON output:\n{raw[:400]}"
        ) from e
