#!/usr/bin/env node
/**
 * Security load test — kalapak-team.space ONLY
 *
 * ⚠️  WARNING / ព្រមាន:
 * - Use ONLY on websites you own (kalapak-team.space)
 * - This is NOT a DDoS tool — capped requests for rate-limit verification
 * - Do NOT run from multiple machines or increase limits aggressively
 * - Render free tier may spin down or throttle under load
 *
 * Usage:
 *   node scripts/security-load-test.mjs
 *   node scripts/security-load-test.mjs --mode burst
 *   node scripts/security-load-test.mjs --mode all
 *   TARGET=https://kalapak-team.space node scripts/security-load-test.mjs --mode burst
 *
 * Modes:
 *   baseline  — 5 normal requests (should all be 200)
 *   burst     — 80 rapid /api/ requests (may trigger 429/403 if limits work)
 *   empty-ua  — request with empty User-Agent (Cloudflare should block → 403)
 *   scanner   — hit .env / wp-admin paths (Cloudflare should block → 403)
 *   all       — run every test
 */

const ALLOWED_HOSTS = ['kalapak-team.space', 'www.kalapak-team.space', 'localhost', '127.0.0.1']

const DEFAULT_TARGET = process.env.TARGET || 'https://kalapak-team.space'
const MAX_BURST = Number(process.env.MAX_BURST || 80)
const CONCURRENCY = Math.min(Number(process.env.CONCURRENCY || 8), 15)

function parseArgs() {
  const args = process.argv.slice(2)
  let mode = 'all'
  for (let i = 0; i < args.length; i++) {
    if (args[i] === '--mode' && args[i + 1]) mode = args[i + 1]
  }
  return { mode }
}

function assertAllowedTarget(baseUrl) {
  let host
  try {
    host = new URL(baseUrl).hostname
  } catch {
    console.error('Invalid TARGET URL:', baseUrl)
    process.exit(1)
  }
  const ok = ALLOWED_HOSTS.some((h) => host === h || host.endsWith(`.${h}`))
  if (!ok) {
    console.error(`Refused: TARGET host "${host}" is not in allowed list.`)
    console.error('Allowed:', ALLOWED_HOSTS.join(', '))
    process.exit(1)
  }
}

async function request(url, options = {}) {
  const start = Date.now()
  try {
    const res = await fetch(url, {
      redirect: 'follow',
      ...options,
      headers: {
        Accept: 'application/json',
        ...options.headers,
      },
    })
    const ms = Date.now() - start
    return { status: res.status, ms, ok: res.ok, error: null }
  } catch (err) {
    return { status: 0, ms: Date.now() - start, ok: false, error: String(err.message || err) }
  }
}

function summarize(label, results) {
  const counts = {}
  for (const r of results) {
    const key = r.error ? `ERR:${r.error.slice(0, 40)}` : String(r.status)
    counts[key] = (counts[key] || 0) + 1
  }
  const avgMs = Math.round(results.reduce((s, r) => s + r.ms, 0) / results.length)
  console.log(`\n=== ${label} ===`)
  console.log(`Requests: ${results.length} | Avg: ${avgMs}ms`)
  console.log('Status codes:', counts)
  return counts
}

function interpret(label, counts) {
  const tips = []
  if (label.includes('baseline')) {
    if (counts['200'] === 5) tips.push('✅ Baseline OK — site responds normally')
    else tips.push('⚠️  Some baseline requests failed — check site health')
  }
  if (label.includes('burst')) {
    if (counts['429'] || counts['403']) tips.push('✅ Rate limiting / WAF triggered (429 or 403) — protection works')
    if (counts['200'] === MAX_BURST) tips.push('⚠️  All burst requests returned 200 — limits may be too loose or not reached')
    if (counts['200'] > 0 && (counts['429'] || counts['403'])) tips.push('ℹ️  Mixed 200 + block codes — partial limiting (expected)')
  }
  if (label.includes('empty-ua')) {
    if (counts['403']) tips.push('✅ Empty User-Agent blocked (Rule: Block empty User-Agent)')
    else tips.push('⚠️  Empty User-Agent not blocked — check Cloudflare custom rule')
  }
  if (label.includes('scanner')) {
    const blocked = (counts['403'] || 0) + (counts['404'] || 0)
    if (blocked >= 2) tips.push('✅ Scanner paths blocked or not found')
    else tips.push('⚠️  Scanner paths may be exposed — check Block attack paths rule')
  }
  tips.forEach((t) => console.log(t))
}

async function runBaseline(base) {
  const paths = [
    '/api/team',
    '/api/projects?per_page=3',
    '/api/blog/posts?per_page=3',
    '/api/blog/categories',
    '/',
  ]
  const results = []
  for (const p of paths) {
    results.push(await request(`${base}${p}`))
    await sleep(300)
  }
  const counts = summarize('Baseline (normal traffic)', results)
  interpret('baseline', counts)
}

async function runBurst(base) {
  const url = `${base}/api/team`
  console.log(`\nBurst: ${MAX_BURST} requests → ${url} (concurrency ${CONCURRENCY})`)
  const results = []
  let i = 0

  async function worker() {
    while (i < MAX_BURST) {
      const n = i++
      if (n >= MAX_BURST) break
      results.push(await request(url))
    }
  }

  await Promise.all(Array.from({ length: CONCURRENCY }, () => worker()))
  const counts = summarize('Burst (/api/ rate limit test)', results)
  interpret('burst', counts)
}

async function runEmptyUa(base) {
  const url = `${base}/api/team`
  const results = [
    await request(url, { headers: { 'User-Agent': '' } }),
    await request(url, { headers: { 'User-Agent': '   ' } }),
  ]
  const counts = summarize('Empty User-Agent test', results)
  interpret('empty-ua', counts)
}

async function runScanner(base) {
  const paths = ['/.env', '/wp-admin', '/phpmyadmin', '/.git/config']
  const results = []
  for (const p of paths) {
    results.push(await request(`${base}${p}`))
  }
  const counts = summarize('Scanner paths test', results)
  interpret('scanner', counts)
}

function sleep(ms) {
  return new Promise((r) => setTimeout(r, ms))
}

async function main() {
  const { mode } = parseArgs()
  const base = DEFAULT_TARGET.replace(/\/$/, '')

  assertAllowedTarget(base)

  console.log('╔══════════════════════════════════════════════════════╗')
  console.log('║  Kalapak Security Load Test (safe, capped)           ║')
  console.log('╚══════════════════════════════════════════════════════╝')
  console.log(`Target: ${base}`)
  console.log(`Mode:   ${mode}`)
  console.log(`Burst:  max ${MAX_BURST} requests\n`)

  const tests = {
    baseline: runBaseline,
    burst: runBurst,
    'empty-ua': runEmptyUa,
    scanner: runScanner,
  }

  if (mode === 'all') {
    for (const fn of Object.values(tests)) await fn(base)
  } else if (tests[mode]) {
    await tests[mode](base)
  } else {
    console.error('Unknown mode:', mode)
    console.error('Use: baseline | burst | empty-ua | scanner | all')
    process.exit(1)
  }

  console.log('\nDone. This test does NOT simulate a real DDoS attack.')
  console.log('For heavy load testing use k6 or Apache Bench with care on your own infra.')
}

main().catch((e) => {
  console.error(e)
  process.exit(1)
})
