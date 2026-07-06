const DEFAULT_OG_IMAGE =
  "https://res.cloudinary.com/kalapak/image/upload/q_auto/f_auto/v1775860922/Logo_kalapak_om1ygl.png"

interface KalapakSeoInput {
  title?: string
  description?: string
  image?: string
  url?: string
  type?: "website" | "article"
}

/** Resize Cloudinary URLs to the 1200×630 OG card ratio. */
function resolveOgImageUrl(image?: string | null): string {
  if (!image) return DEFAULT_OG_IMAGE
  if (image.includes("res.cloudinary.com") && image.includes("/upload/")) {
    return image.replace("/upload/", "/upload/c_fill,w_1200,h_630,g_auto,q_auto,f_jpg/")
  }
  return image
}

export function useKalapakSeo(input: KalapakSeoInput = {}) {
  const config = useRuntimeConfig()

  const title = input.title || "Kalapak Code Team"
  const description =
    input.description || "Modern tech solutions from Cambodia by Kalapak Code Team."
  const image = resolveOgImageUrl(input.image)
  const url = input.url || config.public.siteUrl
  const type = input.type || "website"

  useSeoMeta({
    title,
    description,
    ogTitle: title,
    ogDescription: description,
    ogType: type,
    ogImage: image,
    ogImageWidth: 1200,
    ogImageHeight: 630,
    ogUrl: url,
    twitterCard: "summary_large_image",
    twitterTitle: title,
    twitterDescription: description,
    twitterImage: image,
  })
}

