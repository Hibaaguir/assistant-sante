"""
Database configuration for the AI module.
Reads credentials from backend/.env — no hardcoded secrets.
"""

import os
from pathlib import Path



def _load_env_file() -> dict:
    """Parse backend/.env and return key-value pairs."""
    env_path = Path(__file__).parent.parent / "backend" / ".env"
    values = {}
    if not env_path.exists():
        raise FileNotFoundError(f".env file not found at: {env_path}")

    with open(env_path, encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith("#") or "=" not in line:
                continue
            key, _, value = line.partition("=")
            # Strip surrounding quotes if present
            value = value.strip().strip('"').strip("'")
            values[key.strip()] = value

    return values


def load_groq_api_key() -> None:
    """
    Read GROQ_API_KEY from backend/.env and inject it into os.environ
    so the Groq SDK can find it automatically.
    Call this once at module startup before importing ai_pipeline.
    """
    env = _load_env_file()
    api_key = env.get("GROQ_API_KEY", "").strip()
    if api_key:
        os.environ.setdefault("GROQ_API_KEY", api_key)
    # If not in .env, the SDK will fall back to the already-set env var (if any).


# Keep old name as alias so nothing breaks if referenced elsewhere
load_anthropic_api_key = load_groq_api_key


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
