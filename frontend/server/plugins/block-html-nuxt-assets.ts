/**
 * Missing build assets must NEVER fall through to Nuxt SSR.
 * Otherwise Nitro returns text/html with Cache-Control: immutable (1 year),
 * and browsers cache HTML under a .js URL → MIME module errors.
 */
export default defineNitroPlugin((nitroApp) => {
  nitroApp.hooks.hook('render:response', (response, { event }) => {
    const pathname = event.path?.split('?')[0] || ''
    if (!pathname.startsWith('/_nuxt/') && !pathname.startsWith('/_assets/')) return

    response.statusCode = 404
    response.statusMessage = 'Not Found'
    response.headers = {
      ...(response.headers || {}),
      'content-type': 'text/plain; charset=utf-8',
      'cache-control': 'no-store, must-revalidate',
    }
    response.body = 'Nuxt build asset not found'
  })
})
