<template>
  <div ref="pageRoot" class="contact-page">
    <!-- ═══════════════════ HERO ═══════════════════ -->
    <section
      class="relative min-h-[70svh] sm:min-h-[75svh] flex items-center justify-center overflow-hidden"
    >
      <div class="absolute inset-0 contact-hero-grid opacity-[0.04] dark:opacity-[0.06]" />
      <div
        class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-white dark:to-dark-900 pointer-events-none"
      />
      <div
        class="absolute top-1/4 -left-32 w-[280px] sm:w-[480px] h-[280px] sm:h-[480px] rounded-full bg-brand-violet/12 blur-[100px] sm:blur-[140px] animate-float pointer-events-none"
      />
      <div
        class="absolute bottom-1/3 -right-32 w-[240px] sm:w-[420px] h-[240px] sm:h-[420px] rounded-full bg-brand-cyan/10 blur-[100px] sm:blur-[140px] animate-float pointer-events-none"
        style="animation-delay: 3s"
      />

      <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center relative z-10 pt-20 pb-16">
        <p
          data-hero
          class="font-display text-sm sm:text-base font-semibold uppercase tracking-[0.35em] text-brand-violet dark:text-brand-cyan mb-6 sm:mb-8"
        >
          Contact
        </p>

        <h1
          data-hero
          class="font-display text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold tracking-tightest mb-5 sm:mb-7 leading-[1.02]"
        >
          <span class="text-gray-900 dark:text-white">Let's</span>
          <span class="gradient-text"> Connect</span>
        </h1>

        <p
          data-hero
          class="text-base sm:text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed"
        >
          Have a question, idea, or want to collaborate? We'd love to hear from you.
        </p>
      </div>
    </section>

    <!-- ═══════════════════ CONTACT CARDS ═══════════════════ -->
    <section class="pb-10 sm:pb-14 relative z-10 -mt-4">
      <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <div class="mb-8 text-center" data-reveal>
          <span class="section-label">Reach us</span>
          <h2 class="section-heading text-3xl md:text-4xl">
            Ways to <span class="gradient-text">get in touch</span>
          </h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
          <div
            v-for="(info, i) in contactInfo"
            :key="i"
            data-reveal
            :data-reveal-delay="i * 70"
            class="group relative surface-panel p-6 text-center hover:-translate-y-1.5 hover:shadow-xl dark:hover:shadow-glow/10"
          >
            <div
              class="w-12 h-12 rounded-xl mx-auto mb-4 flex items-center justify-center transition-transform duration-500 ease-premium group-hover:scale-105"
              :class="info.bgClass"
            >
              <div v-html="info.icon" />
            </div>
            <h3
              class="text-xs font-display font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-1.5"
            >
              {{ info.label }}
            </h3>
            <a
              v-if="info.href"
              :href="info.href"
              target="_blank"
              rel="noopener noreferrer"
              class="text-sm text-gray-600 dark:text-gray-400 hover:text-brand-violet dark:hover:text-brand-cyan transition-colors break-all"
            >
              {{ info.value }}
            </a>
            <p v-else class="text-sm text-gray-600 dark:text-gray-400">{{ info.value }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════ FORM + SIDEBAR ═══════════════════ -->
    <section class="pb-20 sm:pb-28 relative z-10">
      <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="grid lg:grid-cols-[1fr_400px] gap-6 lg:gap-8">
          <!-- Form -->
          <div data-reveal class="surface-panel overflow-hidden">
            <div class="relative p-5 sm:p-8 md:p-10 pb-0">
              <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-brand" />
              <div class="flex items-center gap-3 mb-1">
                <div
                  class="w-10 h-10 rounded-xl bg-gradient-brand flex items-center justify-center"
                >
                  <svg
                    class="w-5 h-5 text-white"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"
                    />
                  </svg>
                </div>
                <div>
                  <h2 class="font-display text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Send a Message
                  </h2>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    We typically respond within 24 hours
                  </p>
                </div>
              </div>
            </div>

            <div v-if="submitted" class="p-8 md:p-10">
              <div class="text-center py-10">
                <div class="relative w-20 h-20 mx-auto mb-6">
                  <div class="absolute inset-0 bg-green-400/20 rounded-full animate-ping" />
                  <div
                    class="relative w-20 h-20 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center"
                  >
                    <svg
                      class="w-10 h-10 text-white"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2.5"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4.5 12.75l6 6 9-13.5"
                      />
                    </svg>
                  </div>
                </div>
                <h3 class="font-display text-2xl font-bold text-gray-900 dark:text-white mb-3">
                  Message Sent!
                </h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-6 leading-relaxed">
                  Thank you for reaching out. We'll get back to you as soon as possible.
                </p>
                <button type="button" class="btn-secondary text-sm" @click="resetForm">
                  Send another message
                </button>
              </div>
            </div>

            <form v-else class="p-5 sm:p-8 md:p-10 space-y-5 sm:space-y-6" @submit.prevent="handleSubmit">
              <div class="grid sm:grid-cols-2 gap-5">
                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Your Name <span class="text-red-400">*</span>
                  </label>
                  <div class="relative">
                    <svg
                      class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="1.5"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                      />
                    </svg>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      placeholder="John Doe"
                      class="input-field pl-10"
                    />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Email Address <span class="text-red-400">*</span>
                  </label>
                  <div class="relative">
                    <svg
                      class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="1.5"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"
                      />
                    </svg>
                    <input
                      v-model="form.email"
                      type="email"
                      required
                      placeholder="john@example.com"
                      class="input-field pl-10"
                    />
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                  Subject <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                  <svg
                    class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"
                    />
                  </svg>
                  <input
                    v-model="form.subject"
                    type="text"
                    required
                    placeholder="What's this about?"
                    class="input-field pl-10"
                  />
                </div>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                  Message <span class="text-red-400">*</span>
                </label>
                <textarea
                  v-model="form.message"
                  required
                  rows="6"
                  placeholder="Tell us what's on your mind..."
                  class="input-field resize-none"
                />
                <p class="text-[10px] text-gray-400 mt-1 text-right">
                  {{ form.message.length }} / 2000
                </p>
              </div>

              <div v-if="isTurnstileReady" class="flex justify-center">
                <VueTurnstile :site-key="turnstileSiteKey" v-model="turnstileToken" />
              </div>

              <button type="submit" class="btn-primary w-full text-sm" :disabled="submitting">
                <span v-if="!submitting">Send Message</span>
                <span v-else class="inline-flex items-center gap-2">
                  <span
                    class="w-4 h-4 border-2 border-current/30 border-t-current rounded-full animate-spin"
                  />
                  Sending...
                </span>
              </button>

              <p
                v-if="error"
                class="text-sm text-red-500 text-center px-4 py-2 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800"
              >
                {{ error }}
              </p>
            </form>
          </div>

          <!-- Sidebar -->
          <aside class="flex flex-col gap-5">
            <div data-reveal data-reveal-delay="80" class="surface-panel p-7">
              <h3
                class="text-xs font-display font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-5 flex items-center gap-2"
              >
                <svg
                  class="w-4 h-4 text-brand-violet dark:text-brand-cyan"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"
                  />
                </svg>
                Our Location
              </h3>

              <div
                class="rounded-xl overflow-hidden mb-5 border border-black/[0.08] dark:border-white/[0.08]"
              >
                <iframe
                  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d125422.55623628148!2d104.84837849726563!3d11.5563738!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3109513dc76a6be3%3A0x9c010ee85ab525bb!2sPhnom%20Penh%2C%20Cambodia!5e0!3m2!1sen!2s!4v1700000000000!5m2!1sen!2s"
                  width="100%"
                  height="180"
                  style="border: 0"
                  allowfullscreen=""
                  loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade"
                  class="grayscale hover:grayscale-0 transition-[filter] duration-500"
                />
              </div>

              <div class="space-y-3">
                <div class="flex items-start gap-3">
                  <svg
                    class="w-4 h-4 text-brand-violet dark:text-brand-cyan mt-0.5 flex-shrink-0"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 0V5.625c0-.621.504-1.125 1.125-1.125h.375"
                    />
                  </svg>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      Phnom Penh, Cambodia
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kingdom of Cambodia</p>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <svg
                    class="w-4 h-4 text-brand-violet dark:text-brand-cyan mt-0.5 flex-shrink-0"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Available Hours</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      Mon - Sat, 8:00 AM - 8:00 PM (ICT)
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div
              data-reveal
              data-reveal-delay="140"
              class="flex-1 flex flex-col surface-panel p-7"
            >
              <h3
                class="text-xs font-display font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-5 flex items-center gap-2"
              >
                <svg
                  class="w-4 h-4 text-brand-violet dark:text-brand-cyan"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"
                  />
                </svg>
                Follow Us
              </h3>
              <div class="space-y-2">
                <a
                  v-for="social in socials"
                  :key="social.name"
                  :href="social.url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="group flex items-center gap-3 px-4 py-3 rounded-xl border border-black/[0.08] dark:border-white/[0.08] hover:border-black/[0.16] dark:hover:border-white/20 hover:bg-brand-violet/5 dark:hover:bg-brand-cyan/5 transition-all duration-300 ease-premium"
                >
                  <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center"
                    :class="social.bgClass"
                  >
                    <div v-html="social.icon" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ social.name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                      {{ social.handle }}
                    </p>
                  </div>
                  <svg
                    class="w-4 h-4 text-gray-300 dark:text-dark-500 group-hover:text-brand-violet dark:group-hover:text-brand-cyan group-hover:translate-x-0.5 transition-all duration-300"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"
                    />
                  </svg>
                </a>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { publicApi } from '@/services/api'
import { TURNSTILE_SITE_KEY } from '@/config/appUrls'
import VueTurnstile from 'vue-turnstile'
import { useHomeMotion } from '../../../composables/usePremiumMotion.js'

const pageRoot = ref(null)
useHomeMotion(pageRoot)

const form = reactive({ name: '', email: '', subject: '', message: '' })
const submitting = ref(false)
const submitted = ref(false)
const turnstileSiteKey = TURNSTILE_SITE_KEY
const turnstileToken = ref('')
const error = ref('')
const mounted = ref(false)
const isTurnstileReady = computed(() => mounted.value && !!turnstileSiteKey)

onMounted(() => {
  mounted.value = true
})

const contactInfo = [
  {
    label: 'Email',
    value: 'kalapakteam@gmail.com',
    href: 'mailto:kalapakteam@gmail.com',
    bgClass: 'bg-violet-100 dark:bg-violet-900/20',
    icon: '<svg class="w-5 h-5 text-brand-violet" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>',
  },
  {
    label: 'Location',
    value: 'Phnom Penh, Cambodia',
    href: null,
    bgClass: 'bg-cyan-100 dark:bg-cyan-900/20',
    icon: '<svg class="w-5 h-5 text-brand-cyan" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>',
  },
  {
    label: 'GitHub',
    value: 'Kalapak Code',
    href: 'https://github.com',
    bgClass: 'bg-violet-100 dark:bg-violet-900/20',
    icon: '<svg class="w-5 h-5 text-brand-violet" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>',
  },
  {
    label: 'Availability',
    value: 'Mon - Sat, 8AM - 8PM',
    href: null,
    bgClass: 'bg-cyan-100 dark:bg-cyan-900/20',
    icon: '<svg class="w-5 h-5 text-brand-cyan" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
  },
]

const socials = [
  {
    name: 'GitHub',
    handle: '@kalapak-code',
    url: 'https://github.com',
    bgClass: 'bg-gray-100 dark:bg-dark-600',
    icon: '<svg class="w-4 h-4 text-gray-700 dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>',
  },
  {
    name: 'Telegram',
    handle: '@kalapakteam',
    url: 'https://t.me/kalapakteam',
    bgClass: 'bg-sky-50 dark:bg-sky-900/20',
    icon: '<svg class="w-4 h-4 text-sky-500" fill="currentColor" viewBox="0 0 24 24"><path d="m20.665 3.717-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192l-8.533 7.701h-.002l.002.001-.314 4.692c.46 0 .663-.211.921-.46l2.211-2.15 4.599 3.397c.848.467 1.457.227 1.668-.787l3.019-14.228c.309-1.239-.473-1.8-1.282-1.432z"/></svg>',
  },
  {
    name: 'Facebook',
    handle: 'Kalapak Code Team',
    url: 'https://facebook.com',
    bgClass: 'bg-blue-50 dark:bg-blue-900/20',
    icon: '<svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
  },
]

function resetForm() {
  submitted.value = false
  Object.assign(form, { name: '', email: '', subject: '', message: '' })
}

async function handleSubmit() {
  submitting.value = true
  error.value = ''
  try {
    await publicApi.sendContact({ ...form, turnstile_token: turnstileToken.value })
    submitted.value = true
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to send. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.contact-hero-grid {
  background-image:
    linear-gradient(rgba(123, 47, 255, 0.08) 1px, transparent 1px),
    linear-gradient(90deg, rgba(123, 47, 255, 0.08) 1px, transparent 1px);
  background-size: 72px 72px;
  mask-image: radial-gradient(ellipse 75% 65% at 50% 45%, black 15%, transparent 72%);
}
</style>
