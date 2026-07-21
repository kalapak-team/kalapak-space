<template>
  <div ref="pageRoot" class="projects-page">
    <!-- ═══════════════════ HERO ═══════════════════ -->
    <section
      class="relative min-h-[70svh] sm:min-h-[75svh] flex items-center justify-center overflow-hidden"
    >
      <div class="absolute inset-0 projects-hero-grid opacity-[0.04] dark:opacity-[0.06]" />
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
          Portfolio
        </p>

        <h1
          data-hero
          class="font-display text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold tracking-tightest mb-5 sm:mb-7 leading-[1.02]"
        >
          <span class="text-gray-900 dark:text-white">Our</span>
          <span class="gradient-text"> Projects</span>
        </h1>

        <p
          data-hero
          class="text-base sm:text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed"
        >
          Real-world applications built with passion — from concept to deployment.
        </p>
      </div>
    </section>

    <!-- ═══════════════════ STATS (x.ai-style) ═══════════════════ -->
    <section
      ref="statsSection"
      class="stats-section mb-12 sm:mb-16"
      :class="{ 'is-inview': statsInView }"
      @pointermove="onStatsPointer"
      @pointerleave="onStatsPointerLeave"
    >
      <div class="stats-grid-bg" aria-hidden="true" />
      <div
        class="stats-spotlight"
        aria-hidden="true"
        :style="statsSpotlightStyle"
      />

      <div class="max-w-5xl mx-auto px-4 sm:px-6 relative z-10">
        <div class="stats-row grid grid-cols-3">
          <div
            v-for="(stat, i) in projectStats"
            :key="stat.label"
            class="stat-item"
            :style="{ '--stat-i': i }"
          >
            <p class="stat-value">{{ stat.display }}</p>
            <p class="stat-label">{{ stat.label }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════ FILTERS ═══════════════════ -->
    <section class="pb-10 sm:pb-12 relative z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div
          data-reveal
          class="surface-panel flex flex-col md:flex-row items-stretch md:items-center gap-3 sm:gap-4 p-3 sm:p-4"
        >
          <div class="relative flex-1">
            <svg
              class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
              />
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Search projects..."
              class="input-field !rounded-xl pl-10"
              @input="debouncedFetch"
            />
          </div>
          <div class="w-full md:w-44">
            <CustomSelect
              v-model="selectedTag"
              :options="[{ label: 'All Tags', value: '' }, ...tags.map((t) => ({ label: t, value: t }))]"
              placeholder="All Tags"
              @change="fetchProjects"
            />
          </div>
          <div class="w-full md:w-44">
            <CustomSelect
              v-model="selectedStatus"
              :options="[
                { label: 'All Status', value: '' },
                { label: 'Completed', value: 'completed' },
                { label: 'In Progress', value: 'in_progress' },
                { label: 'Active', value: 'active' },
                { label: 'Planned', value: 'planned' },
                { label: 'Archived', value: 'archived' },
              ]"
              placeholder="All Status"
              @change="fetchProjects"
            />
          </div>
          <button
            v-if="search || selectedTag || selectedStatus"
            class="btn-ghost whitespace-nowrap"
            @click="clearFilters"
          >
            Clear filters
          </button>
        </div>
        <p
          v-if="!loading"
          data-reveal
          class="text-xs text-gray-400 dark:text-gray-500 mt-3 px-1"
        >
          Showing {{ projects.length }}
          {{ projects.length === 1 ? 'project' : 'projects' }}
          <span v-if="search || selectedTag || selectedStatus"> with active filters</span>
        </p>
      </div>
    </section>

    <!-- ═══════════════════ FEATURED ═══════════════════ -->
    <section
      v-if="featuredProject && !search && !selectedTag && !selectedStatus && meta.current_page === 1"
      class="pb-14 sm:pb-16 relative z-10"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="mb-6" data-reveal>
          <span class="section-label">Featured</span>
          <h2 class="section-heading text-3xl md:text-4xl lg:text-5xl">
            Spotlight <span class="gradient-text">project</span>
          </h2>
        </div>

        <div
          data-reveal
          class="group relative surface-panel overflow-hidden hover:shadow-xl dark:hover:shadow-glow/10"
        >
          <div class="grid lg:grid-cols-2 gap-0">
            <div class="relative aspect-video lg:aspect-auto lg:min-h-[360px] overflow-hidden bg-gray-100 dark:bg-dark-700">
              <img
                v-if="featuredProject.cover_image"
                :src="featuredProject.cover_image"
                :alt="featuredProject.title"
                class="w-full h-full object-cover transition-transform duration-700 ease-premium group-hover:scale-[1.04]"
              />
              <div v-else class="w-full h-full bg-gradient-brand opacity-20" />
              <div
                class="absolute top-4 left-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand-violet/90 dark:bg-brand-cyan/90 text-white text-[10px] font-bold uppercase tracking-wider backdrop-blur-sm"
              >
                Featured
              </div>
            </div>

            <div class="p-7 sm:p-9 lg:p-12 flex flex-col justify-center">
              <div class="flex flex-wrap items-center gap-2 mb-4">
                <span
                  :class="statusClasses(featuredProject.status)"
                  class="px-2.5 py-1 text-xs rounded-md font-medium"
                >
                  {{ formatStatus(featuredProject.status) }}
                </span>
                <span
                  v-if="featuredProject.is_open_source"
                  class="px-2.5 py-1 text-xs rounded-md font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400"
                >
                  Open Source
                </span>
              </div>

              <h2
                class="font-display text-2xl md:text-3xl font-bold tracking-tight text-gray-900 dark:text-white mb-3 group-hover:text-brand-violet dark:group-hover:text-brand-cyan transition-colors duration-300"
              >
                {{ featuredProject.title }}
              </h2>
              <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6 line-clamp-3">
                {{ featuredProject.description }}
              </p>

              <div class="flex flex-wrap gap-2 mb-8">
                <span
                  v-for="tag in (featuredProject.tags || []).slice(0, 5)"
                  :key="tag.id"
                  class="px-2.5 py-1 text-xs rounded-md font-medium bg-brand-violet/10 text-brand-violet dark:bg-brand-cyan/10 dark:text-brand-cyan"
                >
                  {{ tag.name }}
                </span>
              </div>

              <div class="flex flex-wrap items-center gap-3">
                <router-link
                  :to="`/projects/${featuredProject.slug}`"
                  class="btn-primary !rounded-full text-sm"
                >
                  View Project
                </router-link>
                <a
                  v-if="featuredProject.demo_url"
                  :href="featuredProject.demo_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="btn-secondary !rounded-full text-sm"
                >
                  Live Demo
                </a>
                <a
                  v-if="featuredProject.repo_url"
                  :href="featuredProject.repo_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="btn-ghost !rounded-full text-sm"
                >
                  Source
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════ GRID ═══════════════════ -->
    <section class="pb-20 sm:pb-28 relative z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div
          v-if="!loading && displayProjects.length"
          class="mb-8 sm:mb-10"
          data-reveal
        >
          <span class="section-label">All work</span>
          <h2 class="section-heading text-3xl md:text-4xl lg:text-5xl">
            Explore the <span class="gradient-text">collection</span>
          </h2>
        </div>

        <div v-if="loading" class="flex items-center justify-center py-20">
          <div class="relative">
            <div
              class="w-14 h-14 border-[3px] border-brand-violet/20 dark:border-white/10 border-t-brand-violet dark:border-t-brand-cyan rounded-full animate-spin"
            />
            <div class="absolute inset-0 flex items-center justify-center">
              <div
                class="w-6 h-6 border-[3px] border-brand-violet/20 dark:border-white/10 border-b-brand-cyan dark:border-b-brand-violet rounded-full animate-spin"
                style="animation-direction: reverse"
              />
            </div>
          </div>
        </div>

        <div
          v-else-if="displayProjects.length"
          class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6"
        >
          <router-link
            v-for="(project, i) in displayProjects"
            :key="project.id"
            :to="`/projects/${project.slug}`"
            class="group relative flex flex-col surface-panel overflow-hidden hover:-translate-y-1.5 hover:shadow-xl dark:hover:shadow-glow/10"
            data-reveal
            :data-reveal-delay="(i % 6) * 70"
          >
            <div class="relative aspect-[16/10] overflow-hidden bg-gray-100 dark:bg-dark-700">
              <img
                v-if="project.cover_image"
                :src="project.cover_image"
                :alt="project.title"
                class="w-full h-full object-cover transition-transform duration-700 ease-premium group-hover:scale-[1.04]"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg
                  class="w-12 h-12 text-gray-300 dark:text-dark-500"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"
                  />
                </svg>
              </div>

              <div
                class="absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"
              />

              <div class="absolute top-3 right-3">
                <span
                  :class="statusClasses(project.status)"
                  class="px-2.5 py-1 text-[10px] rounded-md font-bold uppercase tracking-wider backdrop-blur-sm"
                >
                  {{ formatStatus(project.status) }}
                </span>
              </div>
              <div v-if="project.is_open_source" class="absolute top-3 left-3">
                <span
                  class="px-2 py-1 text-[10px] rounded-md font-bold uppercase tracking-wider bg-emerald-500/90 text-white backdrop-blur-sm"
                >
                  OSS
                </span>
              </div>

              <div
                class="absolute bottom-3 left-3 right-3 flex items-center gap-2 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-500 ease-premium"
              >
                <span
                  v-if="project.demo_url"
                  class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/95 dark:bg-dark-800/95 backdrop-blur-sm text-xs font-medium text-gray-700 dark:text-gray-200"
                  @click.prevent="openLink(project.demo_url)"
                >
                  Demo
                </span>
                <span
                  v-if="project.repo_url"
                  class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/95 dark:bg-dark-800/95 backdrop-blur-sm text-xs font-medium text-gray-700 dark:text-gray-200"
                  @click.prevent="openLink(project.repo_url)"
                >
                  Code
                </span>
              </div>
            </div>

            <div class="flex flex-col flex-1 p-6">
              <div class="flex flex-wrap gap-1.5 mb-3">
                <span
                  v-for="tag in (project.tags || []).slice(0, 3)"
                  :key="tag.id"
                  class="px-2 py-0.5 text-[10px] rounded-md font-medium bg-brand-violet/10 text-brand-violet dark:bg-brand-cyan/10 dark:text-brand-cyan"
                >
                  {{ tag.name }}
                </span>
                <span
                  v-if="(project.tags || []).length > 3"
                  class="px-2 py-0.5 text-[10px] rounded-md font-medium text-gray-400"
                >
                  +{{ project.tags.length - 3 }}
                </span>
              </div>

              <h3
                class="font-display text-lg font-bold tracking-tight text-gray-900 dark:text-white mb-2 group-hover:text-brand-violet dark:group-hover:text-brand-cyan transition-colors leading-snug"
              >
                {{ project.title }}
              </h3>

              <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-2 mb-4 flex-1">
                {{ project.description }}
              </p>

              <div
                class="flex items-center justify-between pt-4 border-t border-black/[0.08] dark:border-white/[0.08]"
              >
                <div class="flex items-center gap-3 text-xs text-gray-400 dark:text-gray-500">
                  <span v-if="project.stars_count" class="flex items-center gap-1">
                    {{ project.stars_count }} stars
                  </span>
                  <span v-if="project.created_at">
                    {{ formatDate(project.created_at) }}
                  </span>
                </div>
                <span
                  class="text-xs font-semibold text-brand-violet dark:text-brand-cyan group-hover:gap-2 inline-flex items-center gap-1 transition-all duration-300"
                >
                  Details
                </span>
              </div>
            </div>
          </router-link>
        </div>

        <EmptyState
          v-else
          title="No projects found"
          message="Try adjusting your search or filters."
        />

        <div v-if="meta.last_page > 1" class="mt-12" data-reveal>
          <Pagination
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            @page-change="goToPage"
          />
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { publicApi } from '@/services/api'
import Pagination from '@/components/common/Pagination.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import CustomSelect from '@/components/common/CustomSelect.vue'
import { useHomeMotion } from '../../../composables/usePremiumMotion.js'

const pageRoot = ref(null)
const { refreshReveals } = useHomeMotion(pageRoot)

const projects = ref([])
const loading = ref(true)
const search = ref('')
const selectedTag = ref('')
const selectedStatus = ref('')
const meta = ref({ current_page: 1, last_page: 1 })
const tags = ref([
  'Vue.js',
  'Laravel',
  'Flutter',
  'Docker',
  'PostgreSQL',
  'React',
  'Node.js',
  'Python',
  'Tailwind CSS',
  'TypeScript',
])

const statsSection = ref(null)
const statsInView = ref(false)
const statsReady = ref(false)
const spotlight = ref({ x: 50, y: 50, active: false })
const displayCounts = ref([0, 0, 0])

const totalProjects = computed(() => Number(meta.value.total || projects.value.length || 0))
const activeCount = computed(
  () =>
    projects.value.filter((p) => p.status === 'active' || p.status === 'in_progress').length,
)
const openSourceCount = computed(
  () => projects.value.filter((p) => p.is_open_source).length,
)

const statTargets = computed(() => [
  totalProjects.value,
  activeCount.value,
  openSourceCount.value,
])

const projectStats = computed(() => [
  { label: 'total projects', display: displayCounts.value[0] },
  { label: 'active', display: displayCounts.value[1] },
  { label: 'open source', display: displayCounts.value[2] },
])

const statsSpotlightStyle = computed(() => ({
  opacity: spotlight.value.active ? '1' : '0',
  '--spot-x': `${spotlight.value.x}%`,
  '--spot-y': `${spotlight.value.y}%`,
}))

function onStatsPointer(e) {
  const el = statsSection.value
  if (!el) return
  const rect = el.getBoundingClientRect()
  spotlight.value = {
    x: ((e.clientX - rect.left) / rect.width) * 100,
    y: ((e.clientY - rect.top) / rect.height) * 100,
    active: true,
  }
}

function onStatsPointerLeave() {
  spotlight.value = { ...spotlight.value, active: false }
}

function easeOutExpo(t) {
  return t === 1 ? 1 : 1 - Math.pow(2, -10 * t)
}

function animateCount(index, target) {
  const duration = 1400 + index * 120
  const start = performance.now()
  const from = 0

  const tick = (now) => {
    const progress = Math.min((now - start) / duration, 1)
    const eased = easeOutExpo(progress)
    displayCounts.value[index] = Math.round(from + (target - from) * eased)
    if (progress < 1) requestAnimationFrame(tick)
    else displayCounts.value[index] = target
  }

  requestAnimationFrame(tick)
}

function runStatsAnimation() {
  if (!statsReady.value) return
  statsInView.value = true

  const reduce =
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches

  const targets = statTargets.value

  if (reduce) {
    displayCounts.value = [...targets]
    return
  }

  targets.forEach((target, index) => {
    setTimeout(() => animateCount(index, target), 80 + index * 140)
  })
}

let statsObserver = null
let hasAnimatedStats = false

function tryAnimateStats() {
  if (!statsReady.value || !statsInView.value) return
  if (hasAnimatedStats) {
    displayCounts.value = [...statTargets.value]
    return
  }
  hasAnimatedStats = true
  runStatsAnimation()
}

watch(statTargets, () => {
  if (loading.value) return
  statsReady.value = true
  tryAnimateStats()
})

watch(loading, (isLoading) => {
  if (isLoading) return
  statsReady.value = true
  tryAnimateStats()
})

const featuredProject = computed(() => projects.value.find((p) => p.is_featured))
const displayProjects = computed(() => {
  if (search.value || selectedTag.value || selectedStatus.value || meta.value.current_page > 1) {
    return projects.value
  }
  const featured = featuredProject.value
  if (!featured) return projects.value
  return projects.value.filter((p) => p.id !== featured.id)
})

let debounceTimer = null

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchProjects, 300)
}

async function fetchProjects(page = 1) {
  loading.value = true
  try {
    const pageNum = typeof page === 'number' ? page : 1
    const reserveHeroSlot =
      pageNum === 1 && !search.value && !selectedTag.value && !selectedStatus.value
    const { data } = await publicApi.getProjects({
      page: pageNum,
      per_page: reserveHeroSlot ? 10 : 9,
      search: search.value || undefined,
      tag: selectedTag.value || undefined,
      status: selectedStatus.value || undefined,
    })
    projects.value = data.data || []
    meta.value = data.meta || { current_page: 1, last_page: 1 }
  } catch {
    projects.value = []
  } finally {
    loading.value = false
    await nextTick()
    refreshReveals()
  }
}

function clearFilters() {
  search.value = ''
  selectedTag.value = ''
  selectedStatus.value = ''
  fetchProjects()
}

function goToPage(page) {
  fetchProjects(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function formatStatus(status) {
  if (!status) return ''
  return status.replace(/_/g, ' ')
}

function statusClasses(status) {
  const map = {
    completed: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
    active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
    in_progress: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
    planned: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
    archived: 'bg-gray-100 text-gray-600 dark:bg-gray-800/40 dark:text-gray-400',
  }
  return map[status] || 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
}

function openLink(url) {
  window.open(url, '_blank', 'noopener,noreferrer')
}

onMounted(() => {
  fetchProjects()

  if (statsSection.value && typeof IntersectionObserver !== 'undefined') {
    statsObserver = new IntersectionObserver(
      (entries) => {
        if (entries.some((e) => e.isIntersecting)) {
          statsInView.value = true
          tryAnimateStats()
          statsObserver?.disconnect()
          statsObserver = null
        }
      },
      { threshold: 0.35, rootMargin: '0px 0px -8% 0px' },
    )
    statsObserver.observe(statsSection.value)
  } else {
    statsInView.value = true
    tryAnimateStats()
  }
})

onBeforeUnmount(() => {
  statsObserver?.disconnect()
  statsObserver = null
})
</script>

<style scoped>
.projects-hero-grid {
  background-image:
    linear-gradient(rgba(123, 47, 255, 0.08) 1px, transparent 1px),
    linear-gradient(90deg, rgba(123, 47, 255, 0.08) 1px, transparent 1px);
  background-size: 72px 72px;
  mask-image: radial-gradient(ellipse 75% 65% at 50% 45%, black 15%, transparent 72%);
}

/* ── Stats (x.ai-inspired) ── */
.stats-section {
  position: relative;
  z-index: 10;
  padding: 3.5rem 0 4rem;
  isolation: isolate;
  overflow: hidden;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  background: #fff;
}

.dark .stats-section {
  border-top-color: rgba(255, 255, 255, 0.06);
  border-bottom-color: rgba(255, 255, 255, 0.06);
  background: #050508;
}

.stats-grid-bg {
  position: absolute;
  inset: 0;
  pointer-events: none;
  background-image:
    linear-gradient(rgba(15, 23, 42, 0.055) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15, 23, 42, 0.055) 1px, transparent 1px);
  background-size: 48px 48px;
  mask-image: linear-gradient(
    to bottom,
    transparent,
    black 18%,
    black 82%,
    transparent
  );
  -webkit-mask-image: linear-gradient(
    to bottom,
    transparent,
    black 18%,
    black 82%,
    transparent
  );
}

.dark .stats-grid-bg {
  background-image:
    linear-gradient(rgba(255, 255, 255, 0.045) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.045) 1px, transparent 1px);
}

.stats-spotlight {
  position: absolute;
  inset: 0;
  pointer-events: none;
  transition: opacity 0.45s ease;
  mix-blend-mode: multiply;
  background: radial-gradient(
    420px circle at var(--spot-x, 50%) var(--spot-y, 50%),
    rgba(123, 47, 255, 0.12),
    transparent 55%
  );
}

.dark .stats-spotlight {
  mix-blend-mode: screen;
  background: radial-gradient(
    420px circle at var(--spot-x, 50%) var(--spot-y, 50%),
    rgba(0, 212, 255, 0.1),
    transparent 55%
  );
}

.stats-row {
  position: relative;
}

.stat-item {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 1.5rem 0.75rem;
  opacity: 0;
  transform: translateY(18px);
  transition:
    opacity 0.9s cubic-bezier(0.16, 1, 0.3, 1),
    transform 0.9s cubic-bezier(0.16, 1, 0.3, 1),
    filter 0.9s cubic-bezier(0.16, 1, 0.3, 1);
  transition-delay: calc(var(--stat-i, 0) * 90ms);
  filter: blur(4px);
}

.stats-section.is-inview .stat-item {
  opacity: 1;
  transform: translateY(0);
  filter: blur(0);
}

.stat-value {
  font-family: var(--font-display, Outfit, system-ui, sans-serif);
  font-size: clamp(2.5rem, 5.5vw, 4rem);
  font-weight: 700;
  letter-spacing: -0.04em;
  line-height: 1;
  color: #0a0a0a;
  margin: 0 0 0.65rem;
  font-variant-numeric: tabular-nums;
  transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}

.dark .stat-value {
  color: #f5f5f7;
}

.stat-item:hover .stat-value {
  transform: translateY(-1px);
}

.stat-label {
  margin: 0;
  font-size: 0.8125rem;
  line-height: 1.45;
  color: #8b8b93;
  letter-spacing: 0.01em;
  text-transform: none;
  font-weight: 400;
}

.dark .stat-label {
  color: #9a9aa3;
}

@media (prefers-reduced-motion: reduce) {
  .stat-item {
    opacity: 1;
    transform: none;
    filter: none;
    transition: none;
  }
}
</style>
