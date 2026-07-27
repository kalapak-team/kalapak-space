/**
 * k6 load test — pithi-digital-v2.vercel.app
 *
 * Install k6 (Windows):
 *   winget install GrafanaLabs.k6
 *   (restart terminal, or refresh PATH — see below)
 *
 * Usage (safe defaults — 20 users, 10 seconds):
 *   k6 run scripts/k6-load-test.js
 *
 * Site base URL:
 *   k6 run -e URL=https://pithi-digital-v2.vercel.app scripts/k6-load-test.js
 *
 * Single endpoint:
 *   k6 run -e URL=https://pithi-digital-v2.vercel.app/api/team scripts/k6-load-test.js
 *
 * Heavier (may slow site — your risk):
 *   k6 run --vus 50 --duration 10s scripts/k6-load-test.js
 *
 * ⚠️  Do NOT use 500+ VUs on Render free tier — site may go down.
 */

import http from 'k6/http'
import { check, sleep } from 'k6'

const BASE = (__ENV.URL || 'https://pithi-digital-v2.vercel.app').replace(/\/$/, '')
const SINGLE_ENDPOINT = BASE.includes('/api/') || BASE.includes('/auth/')

export const options = {
  vus: Number(__ENV.VUS || 20),
  duration: __ENV.DURATION || '10s',
  thresholds: {
    http_req_failed: ['rate<0.9'],
    http_req_duration: ['p(95)<5000'],
  },
}

const ENDPOINTS = [
  '/api/team',
  '/api/blog/categories',
  '/api/projects?per_page=3',
]

export default function () {
  const url = SINGLE_ENDPOINT
    ? BASE
    : `${BASE}${ENDPOINTS[Math.floor(Math.random() * ENDPOINTS.length)]}`
  const res = http.get(url, {
    headers: { Accept: 'application/json' },
  })
  check(res, {
    'status 200 or 429': (r) => r.status === 200 || r.status === 429,
  })
  sleep(0.05)
}

export function handleSummary(data) {
  const passed = data.metrics.checks?.values?.passes ?? 0
  const failed = data.metrics.checks?.values?.fails ?? 0
  const avg = data.metrics.http_req_duration?.values?.avg ?? 0
  const p95 = data.metrics.http_req_duration?.values['p(95)'] ?? 0
  const reqs = data.metrics.http_reqs?.values?.count ?? 0

  return {
    stdout: [
      '',
      '=== k6 Load Test Summary ===',
      `Target:   ${BASE}`,
      `Requests: ${reqs}`,
      `Checks:   ${passed} passed, ${failed} failed`,
      `Avg:      ${Math.round(avg)}ms`,
      `P95:      ${Math.round(p95)}ms`,
      '',
    ].join('\n'),
  }
}
