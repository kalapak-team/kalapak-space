/** API base URL that works in both Nuxt SSR and the browser. */
export function useApiBase(): string {
  const config = useRuntimeConfig()

  if (import.meta.server) {
    const proxyTarget = config.apiProxyTarget as string | undefined
    if (proxyTarget) {
      return `${proxyTarget.replace(/\/$/, "")}/api`
    }
  }

  return config.public.apiUrl as string
}
