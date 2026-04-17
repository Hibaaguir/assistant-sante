"""
Full pipeline test -- invokes analyze_user(user_id) directly.

Usage (run from ai-module/ directory):

    python test_extraction.py <user_id>              # full pipeline
    python test_extraction.py <user_id> --skip-ai    # Steps 1-3 + rule anomalies only
    python test_extraction.py <user_id> --json        # + full JSON to stdout

Example:
    python test_extraction.py 1
    python test_extraction.py 1 --skip-ai
    python test_extraction.py 1 --json
"""

import sys
import json
import logging
from analyze import analyze_user

# Show pipeline log lines in the terminal
logging.basicConfig(
    level=logging.INFO,
    format="  [%(levelname)s] %(message)s",
)


# ---------------------------------------------
#  DISPLAY HELPERS
# ---------------------------------------------

def _banner(title: str, width: int = 62) -> None:
    print("\n" + "=" * width)
    print(f"  {title}")
    print("=" * width)


def print_report(report: dict) -> None:
    status   = report.get("status", "?")
    risk     = report.get("risk_level", "unknown").upper()
    uid      = report.get("user_id")
    name     = report.get("user_name") or "?"
    meta     = report.get("meta", {})
    timing   = meta.get("timing_seconds", {})

    RISK_ICON   = {"HIGH": "[HIGH]", "MEDIUM": "[MED]", "LOW": "[LOW]", "UNKNOWN": "[?]"}
    STATUS_ICON = {"success": "OK", "partial": "~", "error": "ERR"}

    _banner(f"HEALTH ANALYSIS REPORT  --  user_id={uid}  ({name})")
    print(f"  Status      : {STATUS_ICON.get(status,'?')} {status.upper()}")
    print(f"  Risk level  : {RISK_ICON.get(risk,'-')} {risk}")
    print(f"  Summary     : {report.get('risk_summary', '')}")

    # -- Timing -----------------------------------------------
    total = round(sum(timing.values()), 2)
    parts = "  |  ".join(f"{k.replace('step','').replace('steps','')}: {v}s"
                         for k, v in timing.items())
    print(f"\n  Timing ({total}s total): {parts}")

    # -- Data quality -----------------------------------------
    quality = meta.get("data_quality", {})
    if quality:
        print("\n  Data quality:")
        for domain, level in quality.items():
            icon = {"good": "OK", "partial": "~", "empty": "X"}.get(level, "?")
            print(f"    [{icon}] {domain:<14} {level}")

    # -- Anomalies ---------------------------------------------
    anomalies = report.get("anomalies", [])
    if anomalies:
        _banner(f"ANOMALIES  ({len(anomalies)} detected)")
        ICON = {"critical": "[!!!]", "high": "[!!]", "medium": "[!]", "low": "[ ]"}
        for a in anomalies:
            sev  = a.get("severity", "low")
            tag  = "[AI]" if a.get("rule_type") == "ai_based" else "[RULE]"
            doms = " + ".join(a.get("domains") or [a.get("domain", "?")])
            print(f"  {ICON.get(sev,'-')} {tag} [{sev.upper():8}] {a['code']}  ({doms})")
            print(f"       {a['message']}")

    # -- Alerts ------------------------------------------------
    alerts = report.get("alerts", [])
    if alerts:
        _banner(f"ALERTS  ({len(alerts)})")
        for a in alerts:
            lvl  = a.get("level", "").upper()
            icon = "[!!!]" if lvl == "CRITICAL" else ("[!!]" if lvl == "HIGH" else "[!]")
            print(f"  {icon} [{lvl}] {a.get('message', '')}")
            print(f"       >> {a.get('suggested_action', '')}")

    # -- Domain analyses ---------------------------------------
    domains = report.get("domains", {})
    if domains:
        _banner("DOMAIN ANALYSES  (sorted by severity)")
        SRANK = {"high": 0, "medium": 1, "low": 2, "none": 3}
        DICON = {"HIGH": "[!!]", "MEDIUM": "[!]", "LOW": "[ok]", "NONE": "[--]"}
        for domain, result in sorted(domains.items(),
                                     key=lambda x: SRANK.get(x[1].get("severity", "none"), 4)):
            if result.get("skipped"):
                print(f"\n  [{domain.upper():12}] (skip) skipped ({result.get('skip_reason','no data')})")
                continue
            sev  = result.get("severity", "none").upper()
            conf = result.get("confidence", "?")
            print(f"\n  {DICON.get(sev,'-')} [{domain.upper():12}] severity={sev}  confidence={conf}")
            print(f"     {result.get('analysis', '')}")
            for issue in result.get("issues", []):
                print(f"       - {issue}")
            for rec in result.get("recommendations", []):
                print(f"       >> {rec}")

    # -- Cross-domain patterns ---------------------------------
    patterns = report.get("cross_domain_patterns", [])
    if patterns:
        _banner(f"CROSS-DOMAIN PATTERNS  ({len(patterns)})")
        for p in patterns:
            sev  = p.get("severity", "").upper()
            doms = " + ".join(p.get("domains", []))
            print(f"  [{sev}] {p.get('pattern', '')}  ({doms})")
            print(f"         {p.get('explanation', '')}")

    # -- Global recommendations --------------------------------
    recs = report.get("global_recommendations", [])
    if recs:
        _banner(f"GLOBAL RECOMMENDATIONS  (ranked by impact)")
        for r in recs:
            pri    = r.get("priority", "?")
            domain = r.get("domain", "")
            sec    = r.get("secondary_domains", [])
            tag    = f"{domain}" + (f" + {', '.join(sec)}" if sec else "")
            print(f"\n  #{pri}  [{tag}]")
            print(f"      {r.get('action', '')}")
            if r.get("impact"):
                print(f"      Impact: {r.get('impact')}")

    # -- Warnings / Errors -------------------------------------
    if meta.get("warnings"):
        _banner("WARNINGS")
        for w in meta["warnings"]:
            print(f"  [W]  {w}")

    if meta.get("errors"):
        _banner("PIPELINE ERRORS")
        for e in meta["errors"]:
            print(f"  [E]  {e}")

    print("\n" + "=" * 62)


# ---------------------------------------------
#  MAIN
# ---------------------------------------------

def main():
    if len(sys.argv) < 2:
        print("Usage: python test_extraction.py <user_id> [--skip-ai] [--json]")
        sys.exit(1)

    try:
        user_id = int(sys.argv[1])
    except ValueError:
        print(f"Error: user_id must be an integer, got '{sys.argv[1]}'")
        sys.exit(1)

    skip_ai   = "--skip-ai" in sys.argv
    show_json = "--json"    in sys.argv

    print(f"\nAnalysing user_id={user_id}  (skip_ai={skip_ai}) ...")

    try:
        report = analyze_user(user_id=user_id, skip_ai=skip_ai)
    except ValueError as e:
        print(f"\n[NOT FOUND] {e}")
        sys.exit(1)
    except Exception as e:
        print(f"\n[FATAL ERROR] {e}")
        sys.exit(1)

    print_report(report)

    if show_json:
        print("\n--- Full JSON Report ---\n")
        print(json.dumps(report, indent=2, ensure_ascii=False, default=str))


if __name__ == "__main__":
    main()
