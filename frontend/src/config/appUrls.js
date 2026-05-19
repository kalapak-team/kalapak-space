/** Production origins (override via NUXT_PUBLIC_* / VITE_* when set). */
export const BACKEND_ORIGIN = 'https://api.kalapak-team.space'
export const SITE_ORIGIN = 'https://kalapak-team.space'

/** Base URL for OAuth redirects (no /api suffix). */
export function resolveBackendOrigin() {
  const apiUrl =
    (typeof import.meta !== 'undefined' && import.meta.env?.NUXT_PUBLIC_API_URL) ||
    (typeof import.meta !== 'undefined' && import.meta.env?.VITE_API_URL) ||
    (typeof process !== 'undefined' && process.env?.NUXT_PUBLIC_API_URL)

  if (apiUrl && /^https?:\/\//i.test(apiUrl)) {
    return apiUrl.replace(/\/api\/?$/, '')
  }

  return BACKEND_ORIGIN
}
