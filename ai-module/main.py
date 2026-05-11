"""CLI entry point — called by the Laravel backend via shell_exec."""

import sys
import json

from src import analyze_user
from src.config import load_groq_api_key


def main() -> None:
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Usage: python main.py <user_id>"}))
        sys.exit(1)

    try:
        user_id = int(sys.argv[1])
    except ValueError:
        print(json.dumps({"status": "error", "message": f"Invalid user_id: {sys.argv[1]!r}"}))
        sys.exit(1)

    load_groq_api_key()
    result = analyze_user(user_id)
    print(json.dumps(result))

    if result.get("status") == "error":
        sys.exit(1)


if __name__ == "__main__":
    main()
