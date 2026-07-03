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
    return new URL(value).href
  } catch {
    return null
  }
}

export function useMediaUrl() {
  return { resolveMediaUrl }
}
