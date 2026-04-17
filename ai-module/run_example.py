"""
Minimal runnable example for the AI Health Analysis Module.

Prerequisites:
    1. MySQL running with the assistant_sante database populated
    2. ANTHROPIC_API_KEY set in backend/.env
    3. Dependencies installed: pip install -r requirements.txt

Run from the ai-module/ directory:
    python run_example.py            # analyse user_id=1
    python run_example.py 5          # analyse user_id=5
    python run_example.py 1 --no-ai  # skip AI (faster, no API cost)
"""

import sys
import json
import logging

# ── minimal logging so you can see what's happening ──────────
logging.basicConfig(level=logging.INFO, format="  %(message)s")

from analyze import analyze_user
from schema  import validate_report, schema_summary


def main():
    user_id = int(sys.argv[1]) if len(sys.argv) > 1 else 1
    skip_ai = "--no-ai" in sys.argv

    print(f"\n{'='*55}")
    print(f"  AI Health Analysis Module")
    print(f"  Analysing user_id={user_id}  |  skip_ai={skip_ai}")
    print(f"{'='*55}\n")

    # ── Run the full pipeline ─────────────────────────────────
    report = analyze_user(user_id=user_id, skip_ai=skip_ai)

    # ── Validate output against the strict schema ─────────────
    is_valid, issues = validate_report(report)
    if not is_valid:
        print("[W] Schema validation issues:")
        for issue in issues:
            print(f"   - {issue}")
    else:
        print("[OK] Output schema validated successfully\n")

    # ── Print the key fields ──────────────────────────────────
    print(f"  status        : {report['status']}")
    print(f"  risk_level    : {report['risk_level']}")
    print(f"  risk_summary  : {report['risk_summary']}")
    print(f"  anomalies     : {len(report['anomalies'])} detected")
    print(f"  alerts        : {len(report['alerts'])}")
    print(f"  recommendations: {len(report['global_recommendations'])}")
    print()

    # ── Print domain severity grid ────────────────────────────
    print("  Domain severities:")
    for domain, analysis in report["domains"].items():
        sev  = analysis.get("severity", "none")
        skip = " (skipped)" if analysis.get("skipped") else ""
        bar  = {"critical": "[!!!]", "high": "[!! ]", "medium": "[ ! ]", "low": "[   ]", "none": "[   ]"}.get(sev, "[   ]")
        print(f"    {domain:<14} [{bar}] {sev}{skip}")

    # ── Top anomalies ─────────────────────────────────────────
    critical_high = [a for a in report["anomalies"] if a["severity"] in ("critical", "high")]
    if critical_high:
        print(f"\n  Critical/High anomalies ({len(critical_high)}):")
        for a in critical_high[:3]:
            print(f"    [{a['severity'].upper()}] {a['message']}")

    # ── Top recommendations ───────────────────────────────────
    recs = report["global_recommendations"]
    if recs:
        print(f"\n  Top recommendations:")
        for r in recs[:3]:
            print(f"    #{r['priority']} {r['action']}")

    # ── Timing ───────────────────────────────────────────────
    timing = report["meta"]["timing_seconds"]
    total  = round(sum(timing.values()), 2)
    print(f"\n  Total time: {total}s")
    for step, t in timing.items():
        print(f"    {step}: {t}s")

    # ── Full JSON output ──────────────────────────────────────
    print(f"\n{'─'*55}")
    print("  Full JSON report:")
    print(f"{'─'*55}")
    print(json.dumps(report, indent=2, ensure_ascii=False, default=str))

    print(f"\n{'─'*55}")
    print("  Output schema reference:")
    print(f"{'─'*55}")
    print(schema_summary())


if __name__ == "__main__":
    main()
