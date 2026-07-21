import { onMounted, onBeforeUnmount } from 'vue'

/**
 * Premium entrance + scroll reveals (GSAP), with reduced-motion fallback.
 */
export function usePremiumMotion() {
  const nuxtApp = useNuxtApp()
  const gsap = nuxtApp.$gsap
  const reduceMotion = !!nuxtApp.$reduceMotion

  const triggers = []

  function heroEntrance(root) {
    const el = root && typeof root === 'object' && 'value' in root ? root.value : root
    if (!el || !gsap) return

    const parts = el.querySelectorAll('[data-hero]')
    if (!parts.length) return

    if (reduceMotion) {
      gsap.set(parts, { opacity: 1, y: 0, clearProps: 'filter' })
      return
    }

    gsap.fromTo(
      parts,
      { opacity: 0, y: 40, filter: 'blur(6px)' },
      {
        opacity: 1,
        y: 0,
        filter: 'blur(0px)',
        duration: 1.1,
        stagger: 0.1,
        ease: 'expo.out',
        delay: 0.12,
      },
    )
  }

  function revealOnScroll(selector = '[data-reveal]', scope) {
    if (!gsap) return
    const root = scope || (typeof document !== 'undefined' ? document : null)
    if (!root) return

    const nodes = root.querySelectorAll(selector)
    if (!nodes.length) return

    if (reduceMotion) {
      gsap.set(nodes, { opacity: 1, y: 0, clearProps: 'filter' })
      return
    }

    nodes.forEach((node) => {
      const delay = Number(node.dataset.revealDelay || 0)
      const tween = gsap.fromTo(
        node,
        { opacity: 0, y: 36, filter: 'blur(3px)' },
        {
          opacity: 1,
          y: 0,
          filter: 'blur(0px)',
          duration: 0.9,
          delay: delay / 1000,
          ease: 'expo.out',
          scrollTrigger: {
            trigger: node,
            start: 'top 90%',
            toggleActions: 'play none none none',
          },
        },
      )
      if (tween.scrollTrigger) triggers.push(tween.scrollTrigger)
    })
  }

  function kill() {
    triggers.forEach((t) => t.kill())
    triggers.length = 0
  }

  onBeforeUnmount(kill)

  return { heroEntrance, revealOnScroll, kill, gsap, reduceMotion }
}

export function useHomeMotion(rootRef) {
  const motion = usePremiumMotion()

  function runReveals() {
    const root = rootRef?.value
    if (!root) return
    const fresh = root.querySelectorAll('[data-reveal]:not([data-revealed])')
    if (!fresh.length) return
    motion.revealOnScroll('[data-reveal]:not([data-revealed])', root)
    fresh.forEach((node) => node.setAttribute('data-revealed', '1'))
  }

  onMounted(() => {
    requestAnimationFrame(() => {
      motion.heroEntrance(rootRef)
      runReveals()
    })
  })

  function refreshReveals() {
    requestAnimationFrame(runReveals)
  }

  return { ...motion, refreshReveals }
}
