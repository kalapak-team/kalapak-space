#!/usr/bin/env python3
"""Re-import docs tables from pg_dump with UTF-8 (fixes Khmer mojibake after bad restore)."""

from __future__ import annotations

import os
import re
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BACKUP = ROOT / "backup-2026-07-16-1118.sql"
PSQL = ROOT / "scripts" / ".tools" / "pgsql" / "pgsql" / "bin" / "psql.exe"
ENV_FILE = ROOT / "scripts" / "restore.env.local"


def load_database_url() -> str:
    if os.environ.get("DATABASE_URL"):
        return os.environ["DATABASE_URL"].strip()
    if ENV_FILE.is_file():
        for line in ENV_FILE.read_text(encoding="utf-8").splitlines():
            m = re.match(r"^\s*DATABASE_URL\s*=\s*(.+)\s*$", line)
            if m:
                return m.group(1).strip().strip('"').strip("'")
    sys.exit("Set DATABASE_URL or create scripts/restore.env.local")


def extract_copy_block(text: str, table: str) -> str:
    marker = f"COPY public.{table} "
    start = text.index(marker)
    end = text.index("\n\\.\n", start) + len("\n\\.\n")
    return text[start:end].replace("neondb_owner", "avnadmin")


def run_psql(url: str, sql: str) -> None:
    env = {**os.environ, "PGSSLMODE": "require"}
    if PSQL.is_file():
        cmd = [str(PSQL), url, "-v", "ON_ERROR_STOP=1", "-c", sql]
    else:
        cmd = ["psql", url, "-v", "ON_ERROR_STOP=1", "-c", sql]
    subprocess.run(cmd, check=True, env=env)


def run_psql_file(url: str, path: Path) -> None:
    env = {**os.environ, "PGSSLMODE": "require"}
    if PSQL.is_file():
        cmd = [str(PSQL), url, "-v", "ON_ERROR_STOP=0", "-f", str(path)]
    else:
        cmd = ["psql", url, "-v", "ON_ERROR_STOP=0", "-f", str(path)]
    subprocess.run(cmd, check=True, env=env)


def main() -> None:
    if not BACKUP.is_file():
        sys.exit(f"Missing backup: {BACKUP}")

    url = load_database_url()
    text = BACKUP.read_text(encoding="utf-8")
    tmp = ROOT / "scripts" / ".tmp-docs-reimport"
    tmp.mkdir(parents=True, exist_ok=True)

    print("Truncating docs tables...")
    run_psql(
        url,
        "TRUNCATE doc_sections, docs, doc_menus RESTART IDENTITY CASCADE;",
    )

    for table in ("doc_menus", "docs", "doc_sections"):
        chunk = extract_copy_block(text, table)
        out = tmp / f"{table}.sql"
        out.write_text(chunk, encoding="utf-8", newline="\n")
        print(f"Importing {table}...")
        run_psql_file(url, out)

    print("Verifying release-notes section...")
    run_psql(
        url,
        "SELECT id, left(heading, 40) FROM doc_sections WHERE doc_id = 4 ORDER BY order_num LIMIT 1;",
    )
    print("Done. Clear Redis cache (docs.nav, docs.show.*) or wait for TTL.")


if __name__ == "__main__":
    main()
