"""
Configuration for the AI module.
Reads credentials from ai-module/.env — no hardcoded secrets.
"""

import os
from functools import lru_cache
from pathlib import Path
from groq import Groq

MODEL                  = "llama-3.3-70b-versatile"
TEMPERATURE            = 0.4
MAX_TOKENS_DOMAINS     = 3500
MAX_TOKENS_AGGREGATION = 2500

_client = None


@lru_cache(maxsize=1)
def _load_env_file() -> dict:
    """Parse ai-module/.env and return key-value pairs."""
    env_path = Path(__file__).parent.parent / ".env"
    if not env_path.exists():
        raise FileNotFoundError(f".env file not found at: {env_path}")

    values = {}
    with open(env_path, encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith("#") or "=" not in line:
                continue
            key, _, value = line.partition("=")
            value = value.strip().strip('"').strip("'")
            values[key.strip()] = value

    return values


def load_groq_api_key() -> None:
    env = _load_env_file()
    api_key = env.get("GROQ_API_KEY", "").strip()
    if api_key:
        os.environ.setdefault("GROQ_API_KEY", api_key)


def get_groq_client() -> Groq:
    global _client
    if _client is None:
        api_key = os.environ.get("GROQ_API_KEY")
        if not api_key:
            raise EnvironmentError("GROQ_API_KEY not set in ai-module/.env")
        _client = Groq(api_key=api_key)
    return _client


def get_db_config() -> dict:
    """Return a mysql-connector-python compatible connection config dict."""
    env = _load_env_file()

    if env.get("DB_CONNECTION", "mysql") != "mysql":
        raise ValueError(
            f"AI module only supports MySQL. "
            f"DB_CONNECTION is set to: {env.get('DB_CONNECTION')}"
        )

    return {
        "host":     env.get("DB_HOST", "localhost"),
        "port":     int(env.get("DB_PORT", 3306)),
        "database": env.get("DB_DATABASE", "assistant_sante"),
        "user":     env.get("DB_USERNAME", "root"),
        "password": env.get("DB_PASSWORD", ""),
    }
