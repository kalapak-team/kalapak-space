import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

export default defineNuxtPlugin(() => {
  if (typeof window === 'undefined') return

  gsap.registerPlugin(ScrollTrigger)

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches

  return {
    provide: {
      gsap,
      ScrollTrigger,
      reduceMotion,
    },
  }
})
