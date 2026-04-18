import sys
import json
from analyze import analyze_user

user_id = int(sys.argv[1]) if len(sys.argv) > 1 else 1
report = analyze_user(user_id)

print(f"Status  : {report['status']}")
print(f"Risk    : {report['risk_level']}")
print(f"Summary : {report['risk_summary']}")

print("\n── Domains ──────────────────────────────────────")
for domain, data in report['domains'].items():
    print(f"  {domain:12} [{data['severity']}] {data['analysis'][:80]}...")
    for issue in data.get('issues', []):
        print(f"             issue: {issue}")
    for rec in data.get('recommendations', []):
        print(f"             rec  : {rec}")

print("\n── Anomalies ─────────────────────────────────────")
for a in report['anomalies']:
    print(f"  [{a['severity']}] {a['message']}")

print("\n── Cross-domain patterns ─────────────────────────")
for p in report['cross_domain_patterns']:
    print(f"  - {p}")

print("\n── Global recommendations ────────────────────────")
for r in report['global_recommendations']:
    print(f"  #{r['priority']} [{r['domain']}] {r['action']}")

print("\n── Alerts ────────────────────────────────────────")
for a in report['alerts']:
    print(f"  [{a['level']}] {a['message']} → {a['suggested_action']}")

print("\nTiming  :", report['meta']['timing_seconds'])
