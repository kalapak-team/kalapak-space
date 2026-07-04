import { defineEventHandler, getRequestIP, createError } from 'h3'

interface RateLimitEntry {
  count: number
  resetAt: number
  blocked: boolean
  blockedUntil: number
}

const ipStore = new Map<string, RateLimitEntry>()

const MAX_REQUESTS_PER_WINDOW = 60
const WINDOW_MS = 10_000 // 10 seconds
const BLOCK_DURATION_MS = 300_000 // 5 minutes
const CLEANUP_INTERVAL_MS = 60_000 // clean stale entries every minute

let lastCleanup = Date.now()

function cleanup() {
  const now = Date.now()
  if (now - lastCleanup < CLEANUP_INTERVAL_MS) return
  lastCleanup = now
  for (const [ip, entry] of ipStore) {
    if (now > entry.resetAt && !entry.blocked) {
      ipStore.delete(ip)
    } else if (entry.blocked && now > entry.blockedUntil) {
      ipStore.delete(ip)
    }
  }
}

export default defineEventHandler((event) => {
  const ip = getRequestIP(event, { xForwardedFor: true }) || 'unknown'

  cleanup()

  const now = Date.now()
  let entry = ipStore.get(ip)

  if (entry?.blocked) {
    if (now < entry.blockedUntil) {
      throw createError({
        statusCode: 429,
        statusMessage: 'Too Many Requests',
        message: 'Your IP has been temporarily blocked due to excessive requests.',
      })
    }
    ipStore.delete(ip)
    entry = undefined
  }

  if (!entry || now > entry.resetAt) {
    entry = { count: 1, resetAt: now + WINDOW_MS, blocked: false, blockedUntil: 0 }
    ipStore.set(ip, entry)
    return
  }

  entry.count++

  if (entry.count > MAX_REQUESTS_PER_WINDOW) {
    entry.blocked = true
    entry.blockedUntil = now + BLOCK_DURATION_MS
    throw createError({
      statusCode: 429,
      statusMessage: 'Too Many Requests',
      message: 'Your IP has been temporarily blocked due to excessive requests.',
    })
  }
})
