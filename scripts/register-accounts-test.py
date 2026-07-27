#!/usr/bin/env python3
"""
Register test accounts on pithi-digital-v2 backend.

Usage:
  python scripts/register-accounts-test.py
  python scripts/register-accounts-test.py --count 200
  python scripts/register-accounts-test.py --count 10 --delay-ok 0.5

With proxy:
  python scripts/register-accounts-test.py --proxy http://proxy_ip:port
  set PROXY=http://proxy_ip:port && python scripts/register-accounts-test.py

Requires: pip install requests
"""

from __future__ import annotations

import argparse
import os
import sys
import time

import requests

DEFAULT_URL = "https://pithi-digital-v2-backend.onrender.com/api/auth/register"
DEFAULT_COUNT = 200
DEFAULT_PASSWORD = "admin123"


def build_proxies(proxy_url: str | None) -> dict[str, str] | None:
    if not proxy_url:
        return None
    return {
        "http": proxy_url,
        "https": proxy_url,
    }


def register(
    url: str,
    payload: dict,
    proxies: dict[str, str] | None,
    timeout: float,
) -> tuple[int, str]:
    try:
        res = requests.post(
            url,
            json=payload,
            headers={"Accept": "application/json"},
            proxies=proxies,
            timeout=timeout,
        )
        return res.status_code, res.text[:200]
    except requests.RequestException as exc:
        return 0, str(exc)


def main() -> None:
    parser = argparse.ArgumentParser(description="Create test accounts via /api/auth/register")
    parser.add_argument("--url", default=DEFAULT_URL, help="Register endpoint URL")
    parser.add_argument("--count", type=int, default=DEFAULT_COUNT, help="Accounts to create")
    parser.add_argument("--password", default=DEFAULT_PASSWORD, help="Password for all accounts")
    parser.add_argument(
        "--proxy",
        default=os.environ.get("PROXY"),
        help="Proxy URL, e.g. http://proxy_ip:port (or set PROXY env var)",
    )
    parser.add_argument("--timeout", type=float, default=60.0, help="Request timeout in seconds")
    parser.add_argument("--delay-ok", type=float, default=1.0, help="Seconds to wait after 201")
    parser.add_argument("--delay-429", type=float, default=10.0, help="Seconds to wait after 429")
    parser.add_argument("--delay-other", type=float, default=2.0, help="Seconds to wait on other statuses")
    args = parser.parse_args()

    proxies = build_proxies(args.proxy)
    created = 0
    failed = 0
    blocked = 0
    run_id = int(time.time() * 1000)

    print(f"Target:  {args.url}", flush=True)
    print(f"Count:   {args.count}", flush=True)
    print(f"Proxy:   {args.proxy or '(none)'}", flush=True)
    print(f"Run id:  {run_id}\n", flush=True)

    for i in range(args.count):
        payload = {
            "name": f"User{i}",
            "email": f"user{i}_{run_id}@test.com",
            "phone": f"09{run_id % 10000000}{i:04d}",
            "password": args.password,
        }

        status, body = register(args.url, payload, proxies, args.timeout)
        print(f"{i + 1}/{args.count}  {status}", end="", flush=True)

        if status == 201:
            created += 1
            print("  ok", flush=True)
            time.sleep(args.delay_ok)
        elif status == 429:
            blocked += 1
            print("  blocked -> slow down", flush=True)
            time.sleep(args.delay_429)
        else:
            failed += 1
            print(f"  fail  {body[:120]}", flush=True)
            time.sleep(args.delay_other)

    print("\n=== Summary ===", flush=True)
    print(f"Created: {created}", flush=True)
    print(f"Failed:  {failed}", flush=True)
    print(f"Blocked: {blocked} (429)", flush=True)

    if created < args.count:
        sys.exit(1)


if __name__ == "__main__":
    main()
