import {
  defineEventHandler,
  getRequestHeader,
  getRequestURL,
  setResponseHeader,
} from "h3"

const SOCIAL_BOT_RE =
  /facebookexternalhit|Facebot|Twitterbot|TelegramBot|LinkedInBot|WhatsApp|Slackbot|Discordbot|Pinterest|Viber|Embedly|vkShare|Applebot|Line|ZaloBot|SkypeUriPreview|Iframely/i

export default defineEventHandler(async (event) => {
  const ua = getRequestHeader(event, "user-agent") || ""
  if (!SOCIAL_BOT_RE.test(ua)) return

  const match = getRequestURL(event).pathname.match(/^\/blog\/([^/]+)\/?$/)
  if (!match) return

  const config = useRuntimeConfig()
  const apiBase = String(config.apiProxyTarget || "https://api.kalapak-team.space").replace(
    /\/$/,
    "",
  )
  const slug = decodeURIComponent(match[1])

  try {
    const html = await $fetch<string>(
      `${apiBase}/api/og/blog/${encodeURIComponent(slug)}`,
      { responseType: "text" },
    )
    setResponseHeader(event, "content-type", "text/html; charset=utf-8")
    setResponseHeader(event, "cache-control", "public, max-age=300")
    return html
  } catch {
    // Fall through to normal Nuxt SSR (BlogPostView sets useKalapakSeo).
  }
})
