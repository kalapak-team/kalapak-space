<template>
  <footer class="relative z-10">

    <!-- ── Main content ── -->
    <div class="bg-gray-50 dark:bg-dark-900 relative overflow-hidden">

      <!-- subtle radial glow in dark mode -->
      <div class="pointer-events-none absolute inset-0 dark:opacity-100 opacity-0">
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-brand-cyan/10 blur-3xl" />
      </div>

      <div class="relative max-w-7xl mx-auto px-6 lg:px-8 pt-16 pb-10">

        <!-- top row: brand left, links right -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-12 pb-12">

          <!-- Brand (2 cols) -->
          <div class="lg:col-span-2 flex flex-col gap-5">
            <div class="flex items-center gap-3">
              <img
                src="https://res.cloudinary.com/kalapak/image/upload/q_auto/f_auto/v1775860922/Logo_kalapak_om1ygl.png"
                alt="Kalapak Logo"
                class="w-9 h-9 rounded-xl object-contain"
              />
              <span class="font-bold text-gray-900 dark:text-white tracking-tight text-lg">Kalapak</span>
            </div>
            <p class="text-sm leading-7 text-gray-500 dark:text-gray-400 max-w-xs">
              Crafting Digital Futures from Cambodia to the World. We build products that matter.
            </p>
            <!-- Social icons -->
            <div class="flex items-center gap-2 mt-1">
              <a href="#" aria-label="GitHub" class="social-btn">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                </svg>
              </a>
              <a href="#" aria-label="Discord" class="social-btn">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 00-.041-.106 13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.892.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03z"/>
                </svg>
              </a>
              <a href="#" aria-label="X" class="social-btn">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
              </a>
            </div>
            <!-- Copyright -->
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
              &copy; {{ new Date().getFullYear() }} Kalapak
            </p>
          </div>

          <!-- Links (3 cols) -->
          <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-8">

            <!-- Quick Links -->
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-gray-400 dark:text-dark-500 mb-4">
                Quick Links
              </p>
              <ul class="space-y-2.5">
                <li v-for="link in quickLinks" :key="link.to">
                  <router-link :to="link.to" class="footer-link">{{ link.label }}</router-link>
                </li>
              </ul>
            </div>

            <!-- Resources -->
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-gray-400 dark:text-dark-500 mb-4">
                Resources
              </p>
              <ul class="space-y-2.5">
                <li v-for="link in resourceLinks" :key="link.to">
                  <router-link :to="link.to" class="footer-link">{{ link.label }}</router-link>
                </li>
              </ul>
            </div>

          </div>
        </div>


      </div>
    </div>

    <!-- ── Animated marquee wordmark ── -->
    <div class="marquee-root" aria-hidden="true">

      <!-- Row 1 — scrolls left -->
      <div class="marquee-row r1">
        <div class="marquee-track anim-left">
          <template v-for="n in 14" :key="'r1a-'+n">
            <span class="r1-word">KALAPAK</span><span class="r1-sep"></span>
          </template>
        </div>
        <div class="marquee-track anim-left" aria-hidden="true">
          <template v-for="n in 14" :key="'r1b-'+n">
            <span class="r1-word">KALAPAK</span><span class="r1-sep"></span>
          </template>
        </div>
      </div>

      <!-- Row 2 — giant bold, scrolls right -->
      <div class="marquee-row r2">
        <div class="marquee-track anim-right">
          <template v-for="n in 10" :key="'r2a-'+n">
            <span class="r2-word">KALAPAK</span><span class="r2-sep"></span>
          </template>
        </div>
        <div class="marquee-track anim-right" aria-hidden="true">
          <template v-for="n in 10" :key="'r2b-'+n">
            <span class="r2-word">KALAPAK</span><span class="r2-sep"></span>
          </template>
        </div>
      </div>

      <!-- Row 3 — scrolls left -->
      <div class="marquee-row r3">
        <div class="marquee-track anim-left-slow">
          <template v-for="n in 14" :key="'r3a-'+n">
            <span class="r3-word">KALAPAK</span><span class="r3-sep">✦</span>
          </template>
        </div>
        <div class="marquee-track anim-left-slow" aria-hidden="true">
          <template v-for="n in 14" :key="'r3b-'+n">
            <span class="r3-word">KALAPAK</span><span class="r3-sep">✦</span>
          </template>
        </div>
      </div>

    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { useThemeStore } from '@/stores/theme'

const themeStore = useThemeStore()
const d = computed(() => themeStore.isDark)

// Row 1
const r1Bg    = computed(() => d.value ? '#0d0a3e' : '#f4f4f6')
const r1Color = computed(() => d.value ? '#2a2570' : '#c0bec8')
const r1SepBg = computed(() => d.value ? '#1e1a5a' : '#dddbe4')

// Row 2
const r2Bg        = computed(() => d.value ? '#020024' : '#ffffff')
const r2Border    = computed(() => d.value ? '#1e1a5a' : '#e2e0ea')
const r2Color     = computed(() => d.value ? '#e8e8ff' : '#111')
const r2SepBorder = computed(() => d.value ? '#7b2fff' : '#7b2fff')

// Row 3
const r3Bg       = computed(() => d.value ? '#020024' : '#111')
const r3Color    = computed(() => d.value ? '#2a2570' : '#555')
const r3SepColor = computed(() => d.value ? '#1e1a5a' : '#333')

const quickLinks = [
  { to: '/', label: 'Home' },
  { to: '/about', label: 'About' },
  { to: '/projects', label: 'Projects' },
  { to: '/contact', label: 'Contact' },
]

const resourceLinks = [
  { to: '/blog', label: 'Blog' },
  { to: '/join', label: 'Join Our Team' },
  { to: '/privacy', label: 'Privacy Policy' },
  { to: '/terms', label: 'Terms of Service' },
]
</script>

<style scoped>
/* ── Social icon button ── */
.social-btn {
  @apply w-8 h-8 rounded-lg flex items-center justify-center
         bg-white dark:bg-dark-700
         border border-gray-200 dark:border-dark-600
         text-gray-500 dark:text-gray-400
         hover:bg-brand-violet hover:border-brand-violet hover:text-white
         dark:hover:bg-brand-violet dark:hover:border-brand-violet dark:hover:text-white
         transition-all duration-200 shadow-sm;
}

/* ── Footer link ── */
.footer-link {
  @apply text-sm text-gray-600 dark:text-gray-400
         hover:text-brand-violet dark:hover:text-brand-cyan
         transition-colors duration-150;
}

/* ── Marquee mechanics ── */
.marquee-root { overflow: hidden; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }

.marquee-row  { display: flex; overflow: hidden; white-space: nowrap; }

.marquee-track {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  min-width: 100%;
}

@keyframes toLeft  { from { transform: translateX(0) }    to { transform: translateX(-100%) } }
@keyframes toRight { from { transform: translateX(-100%) } to { transform: translateX(0) } }

.anim-left      { animation: toLeft  70s linear infinite; }
.anim-right     { animation: toRight 55s linear infinite; }
.anim-left-slow { animation: toLeft  45s linear infinite; }

/* Row 1 */
.r1 { background: v-bind(r1Bg); height: 38px; align-items: center; }
.r1-word {
  font-size: 9px; font-weight: 700;
  color: v-bind(r1Color);
  letter-spacing: 0.55em; text-transform: uppercase; padding: 0 28px;
}
.r1-sep {
  display: inline-block; width: 14px; height: 1px;
  background: v-bind(r1SepBg); flex-shrink: 0; vertical-align: middle;
}

/* Row 2 */
.r2 {
  background: v-bind(r2Bg); height: 112px; align-items: center;
  border-top: 1px solid v-bind(r2Border);
  border-bottom: 1px solid v-bind(r2Border);
}
.r2-word {
  font-size: 82px; font-weight: 900;
  color: v-bind(r2Color);
  letter-spacing: -0.025em; text-transform: uppercase;
  padding: 0 40px; line-height: 1;
}
.r2-sep {
  display: inline-block; width: 12px; height: 12px; border-radius: 50%;
  background: v-bind(r2SepBorder);
  flex-shrink: 0; margin: 0 6px; vertical-align: middle;
}

/* Row 3 */
.r3 { background: v-bind(r3Bg); height: 50px; align-items: center; }
.r3-word {
  font-size: 10px; font-weight: 300; font-style: italic;
  color: v-bind(r3Color);
  letter-spacing: 0.4em; text-transform: uppercase; padding: 0 24px;
}
.r3-sep { font-size: 7px; color: v-bind(r3SepColor); flex-shrink: 0; padding: 0 2px; line-height: 1; }
</style>
