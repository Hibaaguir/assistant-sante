"""CLI entry point — called by the Laravel backend via shell_exec."""

import sys
import json

from src.engine import analyze_user

user_id = int(sys.argv[1]) if len(sys.argv) > 1 else 1
result = analyze_user(user_id)
print(json.dumps(result))
