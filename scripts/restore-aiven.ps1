# Restore a plain pg_dump SQL file to Aiven PostgreSQL.
# Requires Docker Desktop running OR psql on PATH.
#
# Usage (PowerShell):
#   $env:DATABASE_URL = "postgresql://avnadmin:PASSWORD@kalapak-db-kalapak-space.j.aivencloud.com:16826/defaultdb?sslmode=require"
#   .\scripts\restore-aiven.ps1

param(
    [string]$BackupFile = (Join-Path $PSScriptRoot "..\backup-2026-07-16-1118.sql"),
    [string]$ReplaceOwner = "avnadmin",
    [string]$EnvFile = (Join-Path $PSScriptRoot "restore.env.local"),
    [switch]$SkipDrop
)

$ErrorActionPreference = "Stop"

if ((Test-Path -LiteralPath $EnvFile)) {
    Get-Content -LiteralPath $EnvFile | ForEach-Object {
        $line = $_.Trim()
        if ($line -match '^\s*#') { return }
        if ($line -match '^\s*DATABASE_URL\s*=\s*(.+)\s*$') {
            $env:DATABASE_URL = $matches[1].Trim().Trim('"').Trim("'")
        }
    }
}

if (-not $env:DATABASE_URL) {
    Write-Error "Set DATABASE_URL in environment or in $EnvFile (see restore.env.local.example)."
}

$BackupFile = (Resolve-Path $BackupFile).Path
$tmp = Join-Path $env:TEMP "kalapak-restore-$(Get-Date -Format 'yyyyMMddHHmmss').sql"
$BundledPsql = Join-Path $PSScriptRoot ".tools\pgsql\pgsql\bin\psql.exe"

function Get-PsqlPath {
    $psql = Get-Command psql -ErrorAction SilentlyContinue
    if ($psql) { return $psql.Source }
    if (Test-Path -LiteralPath $BundledPsql) { return $BundledPsql }
    return $null
}

Write-Host "Preparing dump -> $tmp"
$content = [System.IO.File]::ReadAllText($BackupFile, [System.Text.UTF8Encoding]::new($false))
$content = $content -replace 'neondb_owner', $ReplaceOwner
$content = $content -replace 'neon_superuser', $ReplaceOwner
$content = $content -replace '(?m)^--\\restrict.*\r?\n', ''
$content = $content -replace '(?m)^\\unrestrict.*\r?\n', ''
[System.IO.File]::WriteAllText($tmp, $content, [System.Text.UTF8Encoding]::new($false))

function Run-PsqlFile([string]$File) {
    $env:PGSSLMODE = "require"
    $psqlExe = Get-PsqlPath
    if ($psqlExe) {
        & $psqlExe $env:DATABASE_URL -v ON_ERROR_STOP=0 -f $File
        return
    }
    docker info 2>&1 | Out-Null
    if ($LASTEXITCODE -ne 0) {
        throw "Install bundled psql (run restore once after extract) or start Docker Desktop."
    }
    docker run --rm `
        -e PGSSLMODE=require `
        -e "DATABASE_URL=$($env:DATABASE_URL)" `
        -v "${tmp}:/restore.sql:ro" `
        postgres:17 `
        bash -lc 'psql "$DATABASE_URL" -v ON_ERROR_STOP=0 -f /restore.sql'
}

function Run-PsqlSql([string]$Sql) {
    $env:PGSSLMODE = "require"
    $psqlExe = Get-PsqlPath
    if ($psqlExe) {
        & $psqlExe $env:DATABASE_URL -v ON_ERROR_STOP=1 -c $Sql
        return
    }
    docker run --rm `
        -e PGSSLMODE=require `
        -e "DATABASE_URL=$($env:DATABASE_URL)" `
        postgres:17 `
        bash -lc "psql `"`$DATABASE_URL`" -v ON_ERROR_STOP=1 -c `"$($Sql.Replace('"', '\"'))`""
}

Write-Host "Testing connection..."
Run-PsqlSql "SELECT current_database(), current_user;"

if (-not $SkipDrop) {
    Write-Host "Resetting public schema..."
    $drop = @"
DROP SCHEMA IF EXISTS public CASCADE;
CREATE SCHEMA public;
GRANT ALL ON SCHEMA public TO $ReplaceOwner;
GRANT ALL ON SCHEMA public TO public;
"@
    Run-PsqlSql $drop
}

Write-Host "Restoring data (several minutes)..."
Run-PsqlFile $tmp

Write-Host "Restore finished. Spot-check:"
Run-PsqlSql "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY 1 LIMIT 5;"

Remove-Item -LiteralPath $tmp -ErrorAction SilentlyContinue
