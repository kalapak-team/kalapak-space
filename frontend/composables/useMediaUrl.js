const DEAD_STORAGE_HOSTS = ['hiucucocvvhgmszgqnxc.supabase.co']

/** @param {unknown} url */
export function resolveMediaUrl(url) {
  if (!url || typeof url !== 'string') {
    return null
  }

  let value = url.trim()
  if (!value) {
    return null
  }

  if (!/^https?:\/\//i.test(value)) {
    if (value.includes('.supabase.co/')) {
      value = `https://${value.replace(/^\/+/, '')}`
    } else {
      return null
    }
  }

  try {
    const host = new URL(value).hostname.toLowerCase()
    if (DEAD_STORAGE_HOSTS.includes(host)) {
      return null
    }
  } catch {
    return null
  }

  return value
}

export function useMediaUrl() {
  return { resolveMediaUrl }
}
