#!/usr/bin/env python3
"""
Security load test — kalapak-team.space ONLY

WARNING:
- Use ONLY on websites you own (kalapak-team.space)
- This is NOT a DDoS tool — capped requests for rate-limit verification
- Do NOT run from multiple machines or increase limits aggressively
- Render free tier may spin down or throttle under load

Usage:
  python scripts/security-load-test.py
  python scripts/security-load-test.py --mode burst
  python scripts/security-load-test.py --mode stress
  python scripts/security-load-test.py --mode all
  set TARGET=https://kalapak-team.space && python scripts/security-load-test.py --mode burst

Modes:
  baseline  — 5 normal requests (should all be 200)
  burst     — 80 rapid /api/ requests (may trigger 429/403 if limits work)
  empty-ua  — empty User-Agent on homepage (Cloudflare should block → 403)
  scanner   — hit .env / wp-admin paths (Cloudflare should block → 403)
  stress    — 150 mixed requests (heavier test, still capped — NOT real DDoS)
  all       — run every test
"""

from __future__ import annotations

import argparse
import os
import sys
import time
import urllib.error
import urllib.request
from concurrent.futures import ThreadPoolExecutor, as_completed
from typing import Any
from urllib.parse import urlparse

ALLOWED_HOSTS = ("kalapak-team.space", "www.kalapak-team.space", "localhost", "127.0.0.1")
DEFAULT_TARGET = os.environ.get("TARGET", "https://kalapak-team.space")
MAX_BURST = int(os.environ.get("MAX_BURST", "80"))
MAX_STRESS = min(int(os.environ.get("MAX_STRESS", "150")), 200)
CONCURRENCY = min(int(os.environ.get("CONCURRENCY", "8")), 15)


def assert_allowed_target(base_url: str) -> None:
    try:
        host = urlparse(base_url).hostname or ""
    except Exception:
        print(f"Invalid TARGET URL: {base_url}", file=sys.stderr)
        sys.exit(1)

    ok = any(host == h or host.endswith(f".{h}") for h in ALLOWED_HOSTS)
    if not ok:
        print(f'Refused: TARGET host "{host}" is not in allowed list.', file=sys.stderr)
        print("Allowed:", ", ".join(ALLOWED_HOSTS), file=sys.stderr)
        sys.exit(1)


def request(url: str, headers: dict[str, str] | None = None) -> dict[str, Any]:
    hdrs = {"Accept": "application/json"}
    if headers:
        hdrs.update(headers)

    start = time.perf_counter()
    try:
        req = urllib.request.Request(url, headers=hdrs, method="GET")
        with urllib.request.urlopen(req, timeout=30) as res:
            status = res.status
        ms = int((time.perf_counter() - start) * 1000)
        return {"status": status, "ms": ms, "ok": 200 <= status < 300, "error": None}
    except urllib.error.HTTPError as exc:
        ms = int((time.perf_counter() - start) * 1000)
        return {"status": exc.code, "ms": ms, "ok": False, "error": None}
    except Exception as exc:
        ms = int((time.perf_counter() - start) * 1000)
        return {"status": 0, "ms": ms, "ok": False, "error": str(exc)}


def summarize(label: str, results: list[dict[str, Any]]) -> dict[str, int]:
    counts: dict[str, int] = {}
    for r in results:
        key = f"ERR:{r['error'][:40]}" if r["error"] else str(r["status"])
        counts[key] = counts.get(key, 0) + 1

    avg_ms = round(sum(r["ms"] for r in results) / len(results)) if results else 0
    print(f"\n=== {label} ===")
    print(f"Requests: {len(results)} | Avg: {avg_ms}ms")
    print("Status codes:", counts)
    return counts


def interpret(label: str, counts: dict[str, int]) -> None:
    tips: list[str] = []

    if "baseline" in label:
        if counts.get("200") == 5:
            tips.append("Baseline OK - site responds normally")
        else:
            tips.append("Some baseline requests failed - check site health")

    if "burst" in label:
        if counts.get("429") or counts.get("403"):
            tips.append("Rate limiting / WAF triggered (429 or 403) - protection works")
        if counts.get("200") == MAX_BURST:
            tips.append("All burst requests returned 200 - limits may be too loose or not reached")
        if counts.get("200", 0) > 0 and (counts.get("429") or counts.get("403")):
            tips.append("Mixed 200 + block codes - partial limiting (expected)")

    if "empty-ua" in label:
        if counts.get("403"):
            tips.append("Empty User-Agent blocked (Rule: Block empty User-Agent)")
        else:
            tips.append("Empty User-Agent not blocked - check Cloudflare custom rule")

    if "stress" in label:
        blocked = counts.get("429", 0) + counts.get("403", 0)
        ok = counts.get("200", 0)
        if blocked > 0:
            tips.append(f"Stress test: {blocked} requests blocked/limited - some protection triggered")
        if ok > 0 and blocked == 0:
            tips.append("All stress requests returned 200 - site stayed up")
        if counts.get("0"):
            tips.append("Some requests failed (timeout/network) - server may be overloaded")

    if "scanner" in label:
        blocked = counts.get("403", 0) + counts.get("404", 0)
        if blocked >= 2:
            tips.append("Scanner paths blocked or not found")
        else:
            tips.append("Scanner paths may be exposed - check Block attack paths rule")

    for tip in tips:
        print(tip)


def run_parallel(count: int, worker, concurrency: int) -> list[dict[str, Any]]:
    results: list[dict[str, Any]] = []
    with ThreadPoolExecutor(max_workers=concurrency) as pool:
        futures = [pool.submit(worker, i) for i in range(count)]
        for future in as_completed(futures):
            results.append(future.result())
    return results


def run_baseline(base: str) -> None:
    paths = [
        "/api/team",
        "/api/projects?per_page=3",
        "/api/blog/posts?per_page=3",
        "/api/blog/categories",
        "/",
    ]
    results = []
    for path in paths:
        results.append(request(f"{base}{path}"))
        time.sleep(0.3)
    counts = summarize("Baseline (normal traffic)", results)
    interpret("baseline", counts)


def run_burst(base: str) -> None:
    url = f"{base}/api/team"
    print(f"\nBurst: {MAX_BURST} requests -> {url} (concurrency {CONCURRENCY})")

    def worker(_: int) -> dict[str, Any]:
        return request(url)

    results = run_parallel(MAX_BURST, worker, CONCURRENCY)
    counts = summarize("Burst (/api/ rate limit test)", results)
    interpret("burst", counts)


def run_empty_ua(base: str) -> None:
    results = [
        request(f"{base}/", headers={"User-Agent": ""}),
        request(f"{base}/auth/login", headers={"User-Agent": ""}),
    ]
    counts = summarize("Empty User-Agent test (homepage + login page)", results)
    interpret("empty-ua", counts)


def run_stress(base: str) -> None:
    urls = [
        f"{base}/api/team",
        f"{base}/api/blog/categories",
        f"{base}/api/projects?per_page=3",
        f"{base}/",
    ]
    print(f"\nStress: {MAX_STRESS} mixed requests (concurrency {CONCURRENCY})")

    def worker(i: int) -> dict[str, Any]:
        return request(urls[i % len(urls)])

    results = run_parallel(MAX_STRESS, worker, CONCURRENCY)
    counts = summarize("Stress test (mixed endpoints)", results)
    interpret("stress", counts)


def run_scanner(base: str) -> None:
    paths = ["/.env", "/wp-admin", "/phpmyadmin", "/.git/config"]
    results = [request(f"{base}{p}") for p in paths]
    counts = summarize("Scanner paths test", results)
    interpret("scanner", counts)


def main() -> None:
    parser = argparse.ArgumentParser(description="Kalapak security load test (safe, capped)")
    parser.add_argument(
        "--mode",
        default="all",
        choices=["baseline", "burst", "empty-ua", "scanner", "stress", "all"],
        help="Test mode (default: all)",
    )
    args = parser.parse_args()

    base = DEFAULT_TARGET.rstrip("/")
    assert_allowed_target(base)

    print("=" * 56)
    print("  Kalapak Security Load Test (Python, safe, capped)")
    print("=" * 56)
    print(f"Target: {base}")
    print(f"Mode:   {args.mode}")
    print(f"Burst:  max {MAX_BURST} requests\n")

    tests = {
        "baseline": run_baseline,
        "burst": run_burst,
        "empty-ua": run_empty_ua,
        "scanner": run_scanner,
        "stress": run_stress,
    }

    if args.mode == "all":
        for fn in tests.values():
            fn(base)
    else:
        tests[args.mode](base)

    print("\nDone. This test does NOT simulate a real DDoS attack.")
    print("For instant full downtime use Render Dashboard -> Suspend services.")


if __name__ == "__main__":
    main()
