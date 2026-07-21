<template>
  <div ref="pageRoot" class="blog-page">
    <!-- ═══════════════════ HERO ═══════════════════ -->
    <section
      class="relative min-h-[70svh] sm:min-h-[75svh] flex items-center justify-center overflow-hidden"
    >
      <div class="absolute inset-0 blog-hero-grid opacity-[0.04] dark:opacity-[0.06]" />
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
          Insights
        </p>

        <h1
          data-hero
          class="font-display text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold tracking-tightest mb-5 sm:mb-7 leading-[1.02]"
        >
          <span class="text-gray-900 dark:text-white">Insights &</span>
          <span class="gradient-text"> Stories</span>
        </h1>

        <p
          data-hero
          class="text-base sm:text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed"
        >
          Tutorials, team updates, and thoughts on building software — from the Kalapak team.
        </p>
      </div>
    </section>

    <!-- ═══════════════════ FILTERS ═══════════════════ -->
    <section class="pb-10 sm:pb-12 relative z-10 -mt-4">
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div
          data-reveal
          class="surface-panel flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4 p-3 sm:p-4"
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
              placeholder="Search articles..."
              class="input-field !rounded-xl pl-10"
              @input="debouncedFetch"
            />
          </div>
          <div class="w-full sm:w-48">
            <CustomSelect
              v-model="selectedCategory"
              :options="[
                { label: 'All Categories', value: '' },
                ...categories.map((c) => ({ label: c.name, value: c.slug })),
              ]"
              placeholder="All Categories"
              @change="fetchPosts"
            />
          </div>
          <button
            v-if="search || selectedCategory"
            class="btn-ghost whitespace-nowrap"
            @click="clearFilters"
          >
            Clear
          </button>
        </div>

        <div
          v-if="categories.length"
          data-reveal
          class="flex flex-wrap items-center gap-2 mt-4 px-1"
        >
          <button
            v-for="cat in categories"
            :key="cat.id"
            class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-all duration-300 ease-premium"
            :class="
              selectedCategory === cat.slug
                ? 'bg-brand-violet text-white border-brand-violet dark:bg-brand-cyan dark:border-brand-cyan dark:text-dark-900'
                : 'border-black/[0.08] dark:border-white/[0.08] text-gray-600 dark:text-gray-400 hover:border-black/[0.16] dark:hover:border-white/20 hover:text-brand-violet dark:hover:text-brand-cyan'
            "
            @click="
              selectedCategory = selectedCategory === cat.slug ? '' : cat.slug;
              fetchPosts();
            "
          >
            {{ cat.name }}
          </button>
        </div>

        <p
          v-if="!loading"
          data-reveal
          class="text-xs text-gray-400 dark:text-gray-500 mt-3 px-1"
        >
          {{ posts.length }} {{ posts.length === 1 ? 'article' : 'articles' }}
          <span v-if="search || selectedCategory"> matching your filters</span>
        </p>
      </div>
    </section>

    <!-- ═══════════════════ FEATURED ═══════════════════ -->
    <section
      v-if="featuredPost && !search && !selectedCategory && meta.current_page === 1"
      class="pb-14 sm:pb-16 relative z-10"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="mb-6" data-reveal>
          <span class="section-label">Featured</span>
          <h2 class="section-heading text-3xl md:text-4xl lg:text-5xl">
            Spotlight <span class="gradient-text">article</span>
          </h2>
        </div>

        <router-link
          :to="`/blog/${featuredPost.slug}`"
          data-reveal
          class="group block relative surface-panel overflow-hidden hover:shadow-xl dark:hover:shadow-glow/10"
        >
          <div class="grid lg:grid-cols-2 gap-0">
            <div
              class="relative aspect-video lg:aspect-auto lg:min-h-[360px] overflow-hidden bg-gray-100 dark:bg-dark-700"
            >
              <img
                v-if="featuredPost.cover_image"
                :src="featuredPost.cover_image"
                :alt="featuredPost.title"
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
              <div class="flex flex-wrap items-center gap-3 mb-4 text-xs">
                <span
                  v-if="featuredPost.category"
                  class="px-2.5 py-1 rounded-md font-medium bg-brand-violet/10 text-brand-violet dark:bg-brand-cyan/10 dark:text-brand-cyan"
                >
                  {{ featuredPost.category.name }}
                </span>
                <span class="text-gray-400 dark:text-gray-500">{{
                  formatDate(featuredPost.published_at)
                }}</span>
                <span v-if="featuredPost.reading_time" class="text-gray-400 dark:text-gray-500">
                  · {{ featuredPost.reading_time }} min read
                </span>
              </div>

              <h2
                class="font-display text-2xl md:text-3xl font-bold tracking-tight text-gray-900 dark:text-white mb-4 group-hover:text-brand-violet dark:group-hover:text-brand-cyan transition-colors leading-snug"
              >
                {{ featuredPost.title }}
              </h2>
              <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6 line-clamp-3">
                {{ featuredPost.excerpt }}
              </p>

              <div class="flex items-center justify-between gap-4">
                <button
                  v-if="featuredPost.author?.username"
                  type="button"
                  class="flex items-center gap-3 text-left rounded-xl -m-1 p-1 hover:bg-gray-100/80 dark:hover:bg-dark-700/50 transition-colors"
                  @click.stop.prevent="goProfile(featuredPost.author.username)"
                >
                  <div
                    class="w-9 h-9 rounded-full overflow-hidden ring-2 ring-brand-violet/20 dark:ring-white/10"
                  >
                    <img
                      v-if="featuredPost.author?.avatar"
                      :src="featuredPost.author.avatar"
                      :alt="featuredPost.author.name"
                      class="w-full h-full object-cover"
                    />
                    <div
                      v-else
                      class="w-full h-full bg-gradient-brand flex items-center justify-center text-white text-sm font-bold"
                    >
                      {{ featuredPost.author?.name?.charAt(0) }}
                    </div>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ featuredPost.author?.name }}
                    </p>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">
                      Author · @{{ featuredPost.author.username }}
                    </p>
                  </div>
                </button>
                <div v-else class="flex items-center gap-3">
                  <div
                    class="w-9 h-9 rounded-full overflow-hidden ring-2 ring-brand-violet/20 dark:ring-white/10"
                  >
                    <img
                      v-if="featuredPost.author?.avatar"
                      :src="featuredPost.author.avatar"
                      :alt="featuredPost.author.name"
                      class="w-full h-full object-cover"
                    />
                    <div
                      v-else
                      class="w-full h-full bg-gradient-brand flex items-center justify-center text-white text-sm font-bold"
                    >
                      {{ featuredPost.author?.name?.charAt(0) }}
                    </div>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ featuredPost.author?.name }}
                    </p>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">Author</p>
                  </div>
                </div>
                <span
                  class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-violet dark:text-brand-cyan"
                >
                  Read Article
                </span>
              </div>
            </div>
          </div>
        </router-link>
      </div>
    </section>

    <!-- ═══════════════════ GRID ═══════════════════ -->
    <section class="pb-20 sm:pb-28 relative z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div
          v-if="!loading && displayPosts.length"
          class="mb-8 sm:mb-10"
          data-reveal
        >
          <span class="section-label">All articles</span>
          <h2 class="section-heading text-3xl md:text-4xl lg:text-5xl">
            Latest from the <span class="gradient-text">team</span>
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
          v-else-if="displayPosts.length"
          class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6"
        >
          <router-link
            v-for="(post, i) in displayPosts"
            :key="post.id"
            :to="`/blog/${post.slug}`"
            class="group flex flex-col surface-panel overflow-hidden hover:-translate-y-1.5 hover:shadow-xl dark:hover:shadow-glow/10"
            data-reveal
            :data-reveal-delay="(i % 6) * 70"
          >
            <div class="relative aspect-[16/10] overflow-hidden bg-gray-100 dark:bg-dark-700">
              <img
                v-if="post.cover_image"
                :src="post.cover_image"
                :alt="post.title"
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
                    d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6V7.5z"
                  />
                </svg>
              </div>

              <div v-if="post.category" class="absolute top-3 left-3">
                <span
                  class="px-2.5 py-1 text-[10px] rounded-md font-bold uppercase tracking-wider bg-white/95 dark:bg-dark-800/95 text-brand-violet dark:text-brand-cyan backdrop-blur-sm"
                >
                  {{ post.category.name }}
                </span>
              </div>
              <div v-if="post.reading_time" class="absolute top-3 right-3">
                <span
                  class="px-2 py-1 text-[10px] rounded-md font-medium bg-black/40 text-white backdrop-blur-sm"
                >
                  {{ post.reading_time }} min
                </span>
              </div>
            </div>

            <div class="flex flex-col flex-1 p-6">
              <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500 mb-3">
                <span>{{ formatDate(post.published_at) }}</span>
                <span v-if="post.views_count">· {{ post.views_count }} views</span>
              </div>

              <h3
                class="font-display text-lg font-bold tracking-tight text-gray-900 dark:text-white mb-2 group-hover:text-brand-violet dark:group-hover:text-brand-cyan transition-colors leading-snug line-clamp-2"
              >
                {{ post.title }}
              </h3>

              <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-2 mb-5 flex-1">
                {{ post.excerpt }}
              </p>

              <div
                class="flex items-center justify-between pt-4 border-t border-black/[0.08] dark:border-white/[0.08]"
              >
                <button
                  v-if="post.author?.username"
                  type="button"
                  class="flex items-center gap-2.5 text-left rounded-lg -m-1 p-1 hover:bg-gray-50 dark:hover:bg-dark-700/50 transition-colors"
                  @click.stop.prevent="goProfile(post.author.username)"
                >
                  <div
                    class="w-7 h-7 rounded-full overflow-hidden ring-1 ring-brand-violet/20 dark:ring-white/10"
                  >
                    <img
                      v-if="post.author?.avatar"
                      :src="post.author.avatar"
                      :alt="post.author.name"
                      class="w-full h-full object-cover"
                    />
                    <div
                      v-else
                      class="w-full h-full bg-gradient-brand flex items-center justify-center text-white text-[10px] font-bold"
                    >
                      {{ post.author?.name?.charAt(0) }}
                    </div>
                  </div>
                  <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{
                    post.author?.name
                  }}</span>
                </button>
                <div v-else class="flex items-center gap-2.5">
                  <div
                    class="w-7 h-7 rounded-full overflow-hidden ring-1 ring-brand-violet/20 dark:ring-white/10"
                  >
                    <img
                      v-if="post.author?.avatar"
                      :src="post.author.avatar"
                      :alt="post.author.name"
                      class="w-full h-full object-cover"
                    />
                    <div
                      v-else
                      class="w-full h-full bg-gradient-brand flex items-center justify-center text-white text-[10px] font-bold"
                    >
                      {{ post.author?.name?.charAt(0) }}
                    </div>
                  </div>
                  <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{
                    post.author?.name
                  }}</span>
                </div>
                <span
                  class="text-xs font-semibold text-brand-violet dark:text-brand-cyan"
                >
                  Read
                </span>
              </div>
            </div>
          </router-link>
        </div>

        <EmptyState
          v-else
          title="No articles found"
          message="Try adjusting your search or filter."
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
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { publicApi } from '@/services/api'
import dayjs from 'dayjs'
import Pagination from '@/components/common/Pagination.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import CustomSelect from '@/components/common/CustomSelect.vue'
import { useHomeMotion } from '../../../composables/usePremiumMotion.js'

const pageRoot = ref(null)
const { refreshReveals } = useHomeMotion(pageRoot)

const router = useRouter()

function goProfile(username) {
  router.push({ name: 'user-profile', params: { username } })
}

const posts = ref([])
const categories = ref([])
const loading = ref(true)
const search = ref('')
const selectedCategory = ref('')
const meta = ref({ current_page: 1, last_page: 1 })

const featuredPost = computed(() => posts.value.find((p) => p.is_featured))
const displayPosts = computed(() => {
  if (search.value || selectedCategory.value || meta.value.current_page > 1) {
    return posts.value
  }
  const featured = featuredPost.value
  if (!featured) return posts.value
  return posts.value.filter((p) => p.id !== featured.id)
})

let debounceTimer = null

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchPosts, 300)
}

function formatDate(date) {
  return date ? dayjs(date).format('MMM D, YYYY') : ''
}

function clearFilters() {
  search.value = ''
  selectedCategory.value = ''
  fetchPosts()
}

async function fetchPosts(page = 1) {
  loading.value = true
  try {
    const pageNum = typeof page === 'number' ? page : 1
    const reserveHeroSlot = pageNum === 1 && !search.value && !selectedCategory.value
    const { data } = await publicApi.getBlogPosts({
      page: pageNum,
      per_page: reserveHeroSlot ? 10 : 9,
      search: search.value || undefined,
      category: selectedCategory.value || undefined,
    })
    posts.value = data.data || []
    meta.value = data.meta || { current_page: 1, last_page: 1 }
  } catch {
    posts.value = []
  } finally {
    loading.value = false
    await nextTick()
    refreshReveals()
  }
}

function goToPage(page) {
  fetchPosts(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(async () => {
  try {
    const { data } = await publicApi.getBlogCategories()
    categories.value = data.data || []
  } catch {
    // ignore
  }
  fetchPosts()
})
</script>

<style scoped>
.blog-hero-grid {
  background-image:
    linear-gradient(rgba(123, 47, 255, 0.08) 1px, transparent 1px),
    linear-gradient(90deg, rgba(123, 47, 255, 0.08) 1px, transparent 1px);
  background-size: 72px 72px;
  mask-image: radial-gradient(ellipse 75% 65% at 50% 45%, black 15%, transparent 72%);
}
</style>
