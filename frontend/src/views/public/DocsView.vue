<template>
  <div ref="pageRoot" class="docs-page min-h-screen bg-white dark:bg-dark-900 flex relative">
    <div class="pointer-events-none absolute inset-0 bg-gradient-mesh opacity-[0.35] dark:opacity-50" />

    <!-- ══ Left Sidebar ══ -->
    <aside
      class="docs-sidebar scrollbar-docs fixed top-0 left-0 h-full w-64 flex-shrink-0 z-20 overflow-y-auto pt-[68px] transition-transform duration-300 ease-premium"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <div class="px-4 py-6">
        <div class="mb-6 px-2">
          <p class="section-label mb-1">Browse</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
            Guides & references
          </p>
        </div>

        <!-- Nav Groups -->
        <div v-if="loading" class="space-y-4 px-1">
          <div v-for="i in 4" :key="i" class="animate-pulse">
            <div class="h-3 bg-brand-violet/15 dark:bg-white/10 rounded w-2/3 mb-3"></div>
            <div v-for="j in 3" :key="j" class="h-3 bg-gray-100 dark:bg-white/5 rounded mb-2 ml-2"></div>
          </div>
        </div>

        <nav v-else>
          <div v-for="mainMenu in filteredNavTree" :key="mainMenu.id" class="mb-5">
            <!-- Main Menu header (collapsible) -->
            <button
              @click="toggleMenu(mainMenu.id)"
              class="w-full flex items-center justify-between px-2 mb-2 group"
            >
              <span
                class="text-[11px] font-display font-bold uppercase tracking-[0.18em] text-gray-400 dark:text-gray-500 group-hover:text-brand-violet dark:group-hover:text-brand-cyan transition-colors"
              >{{ mainMenu.name }}</span>
              <svg
                class="w-3 h-3 text-gray-400 dark:text-gray-500 transition-transform duration-200 ease-premium"
                :class="collapsedMenus.has(mainMenu.id) ? '' : '-rotate-90'"
                fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
              ><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div v-show="collapsedMenus.has(mainMenu.id)">
              <!-- Direct pages of main menu (no sub-menu) -->
              <ul v-if="mainMenu.pages?.length" class="space-y-0.5 mb-1">
                <li v-for="page in mainMenu.pages" :key="page.slug">
                  <button
                    @click="loadDoc(page.slug)"
                    class="docs-nav-link w-full text-left px-3 py-1.5 rounded-lg text-[13.5px] transition-all duration-200 ease-premium"
                    :class="currentSlug === page.slug ? 'docs-nav-link-active' : 'docs-nav-link-idle'"
                  >{{ page.title }}</button>
                  <ul v-if="page.children?.length" class="mt-0.5 ml-3 pl-3 border-l border-black/[0.08] dark:border-white/[0.08] space-y-0.5">
                    <li v-for="sub in page.children" :key="sub.slug">
                      <button
                        @click="loadDoc(sub.slug)"
                        class="docs-nav-link w-full text-left px-2 py-1.5 rounded-lg text-[13px] transition-all duration-200 ease-premium"
                        :class="currentSlug === sub.slug ? 'docs-nav-link-active' : 'docs-nav-link-idle-muted'"
                      >{{ sub.title }}</button>
                    </li>
                  </ul>
                </li>
              </ul>

              <!-- Sub-menus with their pages -->
              <div v-for="subMenu in mainMenu.children" :key="subMenu.id" class="mb-2">
                <p class="px-3 pt-1.5 pb-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">{{ subMenu.name }}</p>
                <ul class="space-y-0.5">
                  <li v-for="page in subMenu.pages" :key="page.slug">
                    <button
                      @click="loadDoc(page.slug)"
                      class="docs-nav-link w-full text-left px-3 py-1.5 rounded-lg text-[13.5px] transition-all duration-200 ease-premium"
                      :class="currentSlug === page.slug ? 'docs-nav-link-active' : 'docs-nav-link-idle'"
                    >{{ page.title }}</button>
                    <!-- Subpages (indented under page) -->
                    <ul v-if="page.children?.length" class="mt-0.5 ml-3 pl-3 border-l border-black/[0.08] dark:border-white/[0.08] space-y-0.5">
                      <li v-for="sub in page.children" :key="sub.slug">
                        <button
                          @click="loadDoc(sub.slug)"
                          class="docs-nav-link w-full text-left px-2 py-1.5 rounded-lg text-[13px] transition-all duration-200 ease-premium"
                          :class="currentSlug === sub.slug ? 'docs-nav-link-active' : 'docs-nav-link-idle-muted'"
                        >{{ sub.title }}</button>
                      </li>
                    </ul>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <div v-if="filteredNavTree.length === 0 && !loading" class="text-center py-8 text-sm text-gray-400">
            No docs found.
          </div>
        </nav>
      </div>
    </aside>

    <!-- Mobile sidebar backdrop -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-10 bg-black/40 backdrop-blur-sm lg:hidden"
      @click="sidebarOpen = false"
    />

    <!-- ══ Main Content ══ -->
    <div class="flex-1 lg:ml-64 min-w-0 relative z-10">
      <div class="px-6 sm:px-10 py-10 xl:pr-[320px]">

        <!-- Mobile: sidebar toggle -->
        <button
          @click="sidebarOpen = !sidebarOpen"
          class="lg:hidden mb-6 inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-brand-violet dark:hover:text-brand-cyan transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
          Browse Docs
        </button>

        <!-- Loading -->
        <div v-if="docLoading && !currentDoc" class="space-y-4 animate-pulse max-w-3xl">
          <div class="h-3 bg-brand-violet/20 dark:bg-white/10 rounded w-24"></div>
          <div class="h-10 bg-gray-200 dark:bg-white/10 rounded w-2/3"></div>
          <div class="h-4 bg-gray-100 dark:bg-white/5 rounded w-full"></div>
          <div class="h-4 bg-gray-100 dark:bg-white/5 rounded w-5/6"></div>
          <div class="h-4 bg-gray-100 dark:bg-white/5 rounded w-3/4"></div>
        </div>

        <!-- No doc selected -->
        <div v-else-if="!currentDoc && !docLoading" class="text-center py-20 max-w-lg mx-auto" data-hero>
          <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-gradient-brand flex items-center justify-center shadow-glow/20">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
          </div>
          <p class="section-label">Documentation</p>
          <h2 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-white mb-3">
            Select a document
          </h2>
          <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
            Choose a topic from the sidebar to get started.
          </p>
        </div>

        <!-- Doc Content -->
        <article v-else-if="currentDoc" class="doc-article max-w-3xl">
          <!-- Header -->
          <div
            data-doc-hero
            class="mb-8 pb-6 border-b border-black/[0.08] dark:border-white/[0.08]"
          >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2 text-xs mb-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-brand-violet/10 dark:bg-brand-cyan/10 text-brand-violet dark:text-brand-cyan font-semibold tracking-wide uppercase text-[10px]">
                    {{ currentDoc.category }}
                  </span>
                  <span class="text-gray-300 dark:text-white/20">·</span>
                  <span class="text-gray-400 dark:text-gray-500">Updated {{ formatDate(currentDoc.updated_at) }}</span>
                </div>
                <h1 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white tracking-tightest leading-[1.1]">
                  {{ currentDoc.title }}
                </h1>
              </div>
              <button
                type="button"
                class="xl:hidden shrink-0 inline-flex items-center gap-2 text-[13px] font-medium text-gray-400 dark:text-gray-500 hover:text-brand-violet dark:hover:text-brand-cyan transition-colors self-start"
                @click="copyDocAsMarkdown"
              >
                <svg class="w-4 h-4 shrink-0 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                {{ copyMarkdownStatus === 'copied' ? 'Copied!' : copyMarkdownStatus === 'error' ? 'Copy failed' : 'Copy as markdown' }}
              </button>
            </div>
          </div>

          <!-- Rendered content: sections-based or legacy single-content -->
          <div data-doc-body>
            <template v-if="currentDoc.sections && currentDoc.sections.length">
              <div v-for="(section, idx) in currentDoc.sections" :key="idx" class="mb-10">
                <h2
                  :id="sectionAnchor(section.heading, idx)"
                  class="font-display text-2xl font-bold text-gray-900 dark:text-white mt-0 mb-4 pb-3 border-b border-black/[0.08] dark:border-white/[0.08] scroll-mt-24"
                >{{ section.heading }}</h2>
                <div class="prose-doc" v-html="renderContent(section.content)" />
              </div>
            </template>
            <div v-else class="prose-doc" v-html="renderContent(currentDoc.content)" />
          </div>

          <!-- Prev / Next -->
          <nav
            v-if="prevDoc || nextDoc"
            class="mt-14 pt-8 border-t border-black/[0.08] dark:border-white/[0.08] grid sm:grid-cols-2 gap-4"
          >
            <button
              v-if="prevDoc"
              type="button"
              class="group surface-panel p-4 text-left hover:-translate-y-0.5"
              @click="loadDoc(prevDoc.slug)"
            >
              <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Previous</span>
              <span class="mt-1 block font-display font-semibold text-gray-900 dark:text-white group-hover:text-brand-violet dark:group-hover:text-brand-cyan transition-colors">
                {{ prevDoc.title }}
              </span>
            </button>
            <button
              v-if="nextDoc"
              type="button"
              class="group surface-panel p-4 text-left sm:text-right sm:col-start-2 hover:-translate-y-0.5"
              :class="{ 'sm:col-start-2': !prevDoc }"
              @click="loadDoc(nextDoc.slug)"
            >
              <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Next</span>
              <span class="mt-1 block font-display font-semibold text-gray-900 dark:text-white group-hover:text-brand-violet dark:group-hover:text-brand-cyan transition-colors">
                {{ nextDoc.title }}
              </span>
            </button>
          </nav>
        </article>
      </div>
    </div>

    <!-- ══ Scroll to Top ══ -->
    <Transition name="scroll-top">
      <button
        v-if="showScrollTop"
        @click="scrollToTop"
        aria-label="Scroll to top"
        class="fixed bottom-8 right-8 z-50 w-11 h-11 flex items-center justify-center rounded-xl bg-white dark:bg-dark-800 border border-black/[0.08] dark:border-white/[0.08] shadow-md text-gray-500 dark:text-gray-400 hover:text-brand-violet dark:hover:text-brand-cyan hover:border-black/[0.16] dark:hover:border-white/20 hover:shadow-lg transition-all duration-200 ease-premium"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
        </svg>
      </button>
    </Transition>

    <!-- ══ Right: Copy markdown + On this page (TOC) ══ -->
    <aside
      v-if="currentDoc"
      class="docs-toc scrollbar-docs hidden xl:block fixed right-0 top-0 w-72 h-full overflow-y-auto pt-[68px] z-10"
    >
      <div class="px-6 py-8">
        <button
          type="button"
          class="w-full flex items-center gap-2.5 text-left text-[13px] font-medium text-gray-500 dark:text-gray-400 hover:text-brand-violet dark:hover:text-brand-cyan transition-colors mb-7 group surface-panel px-3.5 py-2.5"
          @click="copyDocAsMarkdown"
        >
          <svg class="w-4 h-4 shrink-0 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
          </svg>
          <span>{{ copyMarkdownStatus === 'copied' ? 'Copied!' : copyMarkdownStatus === 'error' ? 'Copy failed' : 'Copy as markdown' }}</span>
        </button>

        <template v-if="tocItems.length">
          <p class="section-label mb-3">On this page</p>
          <nav class="space-y-0.5 border-l border-black/[0.08] dark:border-white/[0.08]">
            <a
              v-for="item in tocItems"
              :key="item.id"
              :href="`#${item.id}`"
              @click.prevent="scrollToSection(item.id)"
              class="block text-[13px] py-1.5 transition-colors duration-150 hover:text-brand-violet dark:hover:text-brand-cyan relative"
              :class="[
                item.level === 2
                  ? 'pl-3.5 font-medium'
                  : 'pl-6 text-[12px]',
                activeToc === item.id
                  ? 'text-brand-violet dark:text-brand-cyan font-semibold'
                  : 'text-gray-500 dark:text-gray-400'
              ]"
            >
              <span
                v-if="activeToc === item.id"
                class="absolute left-[-2px] top-1 bottom-1 w-0.5 rounded-full bg-brand-violet dark:bg-brand-cyan"
              />
              {{ item.text }}
            </a>
          </nav>
        </template>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { publicApi } from '@/services/api'
import { marked } from 'marked'
import { markedHighlight } from 'marked-highlight'
import hljs from 'highlight.js'
import DOMPurify from 'dompurify'
import TurndownService from 'turndown'
import { gfm } from 'turndown-plugin-gfm'
import { usePremiumMotion } from '../../../composables/usePremiumMotion.js'

const turndownService = new TurndownService({
  headingStyle: 'atx',
  codeBlockStyle: 'fenced',
  bulletListMarker: '-',
})
turndownService.use(gfm)

// Configure marked to use highlight.js for syntax highlighting
marked.use(markedHighlight({
  langPrefix: 'hljs language-',
  highlight(code, lang) {
    const language = hljs.getLanguage(lang) ? lang : 'plaintext'
    return hljs.highlight(code, { language }).value
  }
}))

// Render content: detect whether it's rich HTML (Tiptap-formatted) or markdown typed as plain text
function renderContent(content) {
  if (!content) return ''
  const trimmed = content.trim()

  if (!trimmed.startsWith('<')) {
    // Plain text — parse as markdown
    return DOMPurify.sanitize(marked.parse(trimmed))
  }

  // If the HTML has actual formatting tags, it's genuine Tiptap rich HTML — use as-is
  const hasRichHtml = /<(strong|em|b|i|h[1-6]|pre|code|ul|ol|li|blockquote|table|thead|tbody|tr|td|th|img|a)\b/i.test(trimmed)
  if (hasRichHtml) {
    return DOMPurify.sanitize(trimmed)
  }

  // Only bare <p> tags wrapping text — extract text preserving paragraph breaks
  const textContent = trimmed
    .replace(/<\/p>\s*<p[^>]*>/gi, '\n\n')
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<[^>]+>/g, '')
    .replace(/&nbsp;/g, ' ')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&amp;/g, '&')
    .replace(/&quot;/g, '"')
    .trim()

  const hasMarkdown = /(\*\*|#{1,6} |`|\[.+\]\(.+\))/.test(textContent)
  if (hasMarkdown) {
    return DOMPurify.sanitize(marked.parse(textContent))
  }

  return DOMPurify.sanitize(trimmed)
}

/** Turn stored doc body (markdown or Tiptap HTML) into markdown for clipboard — mirrors renderContent branching. */
function rawStorageToMarkdown(raw) {
  if (!raw) return ''
  const trimmed = String(raw).trim()
  if (!trimmed) return ''

  if (!trimmed.startsWith('<')) {
    return trimmed
  }

  const hasRichHtml = /<(strong|em|b|i|h[1-6]|pre|code|ul|ol|li|blockquote|table|thead|tbody|tr|td|th|img|a)\b/i.test(trimmed)
  if (hasRichHtml) {
    return turndownService.turndown(trimmed)
  }

  const textContent = trimmed
    .replace(/<\/p>\s*<p[^>]*>/gi, '\n\n')
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<[^>]+>/g, '')
    .replace(/&nbsp;/g, ' ')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&amp;/g, '&')
    .replace(/&quot;/g, '"')
    .trim()

  const hasMarkdown = /(\*\*|#{1,6} |`|\[.+\]\(.+\))/.test(textContent)
  if (hasMarkdown) {
    return textContent
  }

  return turndownService.turndown(trimmed)
}

function buildDocMarkdown() {
  const doc = currentDoc.value
  if (!doc) return ''
  const chunks = [`# ${doc.title}`, '']

  if (doc.sections?.length) {
    for (const section of doc.sections) {
      const heading = (section.heading || '').trim()
      const body = rawStorageToMarkdown(section.content || '').trim()
      if (heading) {
        chunks.push(`## ${heading}`, '')
      }
      if (body) {
        chunks.push(body, '')
      }
    }
  } else {
    const body = rawStorageToMarkdown(doc.content || '').trim()
    if (body) chunks.push(body, '')
  }

  return chunks.join('\n').trimEnd() + '\n'
}

const copyMarkdownStatus = ref('idle')

async function copyDocAsMarkdown() {
  try {
    await navigator.clipboard.writeText(buildDocMarkdown())
    copyMarkdownStatus.value = 'copied'
  } catch {
    copyMarkdownStatus.value = 'error'
  }
  setTimeout(() => {
    copyMarkdownStatus.value = 'idle'
  }, 2000)
}

const route = useRoute()
const router = useRouter()
const pageRoot = ref(null)
const { heroEntrance, gsap, reduceMotion } = usePremiumMotion()

const navTree = ref([])
const loading = ref(true)
const currentDoc = ref(null)
const docLoading = ref(false)
const currentSlug = ref('')
const searchQuery = ref('')
const sidebarOpen = ref(false)
const tocItems = ref([])
const activeToc = ref('')
const collapsedMenus = ref(new Set())
const showScrollTop = ref(false)
const docCache = new Map()

function animateDocEntrance() {
  if (!gsap || !pageRoot.value) return
  const parts = pageRoot.value.querySelectorAll('[data-doc-hero], [data-doc-body]')
  if (!parts.length) return
  if (reduceMotion) {
    gsap.set(parts, { opacity: 1, y: 0, clearProps: 'filter' })
    return
  }
  gsap.fromTo(
    parts,
    { opacity: 0, y: 18, filter: 'blur(4px)' },
    {
      opacity: 1,
      y: 0,
      filter: 'blur(0px)',
      duration: 0.75,
      stagger: 0.08,
      ease: 'expo.out',
    },
  )
}

function toggleMenu(id) {
  if (collapsedMenus.value.has(id)) {
    collapsedMenus.value.delete(id)
  } else {
    collapsedMenus.value.add(id)
  }
  collapsedMenus.value = new Set(collapsedMenus.value)
}

// Flat ordered list of all pages (for prev/next navigation)
const flatDocs = computed(() => {
  const list = []
  for (const mainMenu of navTree.value) {
    for (const page of (mainMenu.pages || [])) {
      list.push(page)
      for (const sub of (page.children || [])) list.push(sub)
    }
    for (const subMenu of (mainMenu.children || [])) {
      for (const page of (subMenu.pages || [])) {
        list.push(page)
        for (const sub of (page.children || [])) list.push(sub)
      }
    }
  }
  return list
})

const filteredNavTree = computed(() => {
  if (!searchQuery.value.trim()) return navTree.value
  const q = searchQuery.value.toLowerCase()
  const matchPage = (p) => p.title.toLowerCase().includes(q) || (p.children || []).some(c => c.title.toLowerCase().includes(q))
  return navTree.value.map(mainMenu => ({
    ...mainMenu,
    pages: (mainMenu.pages || []).filter(matchPage),
    children: (mainMenu.children || []).map(sub => ({
      ...sub,
      pages: (sub.pages || []).filter(matchPage),
    })).filter(sub => sub.pages.length > 0),
  })).filter(m => m.pages.length > 0 || m.children.length > 0)
})

const prevDoc = computed(() => {
  const idx = flatDocs.value.findIndex(d => d.slug === currentSlug.value)
  return idx > 0 ? flatDocs.value[idx - 1] : null
})

const nextDoc = computed(() => {
  const idx = flatDocs.value.findIndex(d => d.slug === currentSlug.value)
  return idx >= 0 && idx < flatDocs.value.length - 1 ? flatDocs.value[idx + 1] : null
})

async function fetchAllDocs() {
  try {
    loading.value = true
    const { data } = await publicApi.getDocsNav()
    navTree.value = data.data || []
    // Expand first main menu by default
    if (navTree.value.length > 0) {
      collapsedMenus.value = new Set([navTree.value[0].id])
    }
  } catch {
    navTree.value = []
  } finally {
    loading.value = false
  }
}

async function loadDoc(slug) {
  if (!slug) return
  currentSlug.value = slug
  router.replace({ name: 'docs', query: { page: slug } })
  sidebarOpen.value = false

  // Serve from cache instantly — zero API call, zero loading spinner
  if (docCache.has(slug)) {
    currentDoc.value = docCache.get(slug)
    window.scrollTo({ top: 0, behavior: 'smooth' })
    await nextTick()
    buildToc()
    addHeadingIds()
    addCopyButtons()
    animateDocEntrance()
    setTimeout(() => buildToc(), 60)
    return
  }

  try {
    docLoading.value = true
    // Keep previous doc visible while loading (no blank flash)
    const { data } = await publicApi.getDoc(slug)
    const doc = data.data
    docCache.set(slug, doc)   // cache for instant future access
    currentDoc.value = doc
    await nextTick()
    buildToc()
    addHeadingIds()
    addCopyButtons()
    animateDocEntrance()
    setTimeout(() => buildToc(), 60)
    window.scrollTo({ top: 0, behavior: 'smooth' })
    prefetchAdjacentDocs(slug)
  } catch {
    currentDoc.value = null
  } finally {
    docLoading.value = false
  }
}

// Silently pre-fetch prev/next docs into cache so they open instantly
function prefetchAdjacentDocs(slug) {
  const idx = flatDocs.value.findIndex(d => d.slug === slug)
  const toFetch = []
  if (idx > 0) toFetch.push(flatDocs.value[idx - 1].slug)
  if (idx >= 0 && idx < flatDocs.value.length - 1) toFetch.push(flatDocs.value[idx + 1].slug)
  for (const s of toFetch) {
    if (!docCache.has(s)) {
      publicApi.getDoc(s).then(({ data }) => {
        docCache.set(s, data.data)
      }).catch(() => {})
    }
  }
}

// Convert section heading to a stable DOM id
function sectionAnchor(heading, idx) {
  return heading
    ? heading.toLowerCase().replace(/\s+/g, '-').replace(/[^\w-]/g, '') || `section-${idx}`
    : `section-${idx}`
}

function buildToc() {
  if (currentDoc.value?.sections?.length) {
    // Sections-based TOC: h2 section headings + h3 sub-headings from each section's content
    const items = []
    const proseDocs = document.querySelectorAll('.doc-article .prose-doc')
    currentDoc.value.sections.forEach((s, i) => {
      const sectionId = sectionAnchor(s.heading, i)
      items.push({ id: sectionId, text: s.heading, level: 2 })
      if (proseDocs[i]) {
        proseDocs[i].querySelectorAll('h3').forEach((h3, j) => {
          const id = h3.id || `${sectionId}-sub-${j}`
          h3.id = id
          items.push({ id, text: h3.textContent.trim(), level: 3 })
        })
      }
    })
    tocItems.value = items
  } else {
    // Legacy: auto-extract h2/h3 from rendered HTML
    const container = document.querySelector('.prose-doc')
    if (!container) { tocItems.value = []; return }
    const headings = container.querySelectorAll('h2, h3')
    tocItems.value = Array.from(headings).map((h, i) => {
      const id = h.id || `heading-${i}`
      h.id = id
      return { id, text: h.textContent, level: parseInt(h.tagName[1]) }
    })
  }
}

function scrollToSection(id) {
  const el = document.getElementById(id)
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  activeToc.value = id
}

function addHeadingIds() {
  if (currentDoc.value?.sections?.length) return // sections use sectionAnchor, already have ids
  const container = document.querySelector('.prose-doc')
  if (!container) return
  container.querySelectorAll('h2, h3').forEach((h, i) => {
    if (!h.id) h.id = `heading-${i}`
  })
}

function addCopyButtons() {
  // Use setTimeout to ensure v-html DOM is fully painted
  setTimeout(() => {
    document.querySelectorAll('.prose-doc pre').forEach(pre => {
      if (pre.parentElement?.classList.contains('code-block-wrapper')) return

      // Detect language from hljs class
      const codeEl = pre.querySelector('code')
      const lang = (codeEl?.className.match(/language-(\w+)/) || [])[1] || ''

      // Wrap pre in a container
      const wrapper = document.createElement('div')
      wrapper.className = 'code-block-wrapper'
      pre.parentNode.insertBefore(wrapper, pre)
      wrapper.appendChild(pre)

      // Header bar with lang label + copy button
      const header = document.createElement('div')
      header.className = 'code-block-header'
      header.innerHTML = `
        <span class="code-block-lang">${lang}</span>
        <button class="copy-code-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
          Copy
        </button>`

      const btn = header.querySelector('.copy-code-btn')
      btn.addEventListener('click', async () => {
        const code = codeEl?.innerText || ''
        try {
          await navigator.clipboard.writeText(code)
          btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Copied!`
          btn.classList.add('copied')
        } catch {}
        setTimeout(() => {
          btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg> Copy`
          btn.classList.remove('copied')
        }, 2000)
      })

      wrapper.insertBefore(header, pre)
    })
  }, 50)
}

function handleScroll() {
  showScrollTop.value = window.scrollY > 300

  if (currentDoc.value?.sections?.length) {
    // Use section heading anchors for scroll-spy
    const ids = tocItems.value.map(t => t.id)
    let current = ids[0] || ''
    for (const id of ids) {
      const el = document.getElementById(id)
      if (el && el.getBoundingClientRect().top < 120) current = id
    }
    activeToc.value = current
  } else {
    const headings = document.querySelectorAll('.prose-doc h2, .prose-doc h3')
    let current = ''
    headings.forEach(h => {
      if (h.getBoundingClientRect().top < 120) current = h.id
    })
    activeToc.value = current
  }
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function formatDate(str) {
  if (!str) return ''
  return new Date(str).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(async () => {
  const slug = route.query.page

  if (slug) {
    // ── Fire nav + doc in PARALLEL — no sequential waiting ──
    loading.value = true
    docLoading.value = true
    currentSlug.value = slug
    router.replace({ name: 'docs', query: { page: slug } })

    const [navResult, docResult] = await Promise.allSettled([
      publicApi.getDocsNav(),
      publicApi.getDoc(slug),
    ])

    // Process nav
    if (navResult.status === 'fulfilled') {
      navTree.value = navResult.value.data.data || []
      if (navTree.value.length > 0) {
        collapsedMenus.value = new Set([navTree.value[0].id])
      }
    } else {
      navTree.value = []
    }
    loading.value = false

    // Process doc
    if (docResult.status === 'fulfilled') {
      const doc = docResult.value.data.data
      docCache.set(slug, doc)
      currentDoc.value = doc
      await nextTick()
      buildToc()
      addHeadingIds()
      addCopyButtons()
      animateDocEntrance()
      setTimeout(() => buildToc(), 60)
      window.scrollTo({ top: 0, behavior: 'smooth' })
      prefetchAdjacentDocs(slug)
    } else {
      currentDoc.value = null
      await nextTick()
      heroEntrance(pageRoot)
    }
    docLoading.value = false
  } else {
    // No slug in URL — need nav first to find the first doc
    await fetchAllDocs()
    if (flatDocs.value.length) {
      await loadDoc(flatDocs.value[0].slug)
    } else {
      await nextTick()
      heroEntrance(pageRoot)
    }
  }

  window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
.docs-sidebar {
  background: rgba(255, 255, 255, 0.92);
  border-right: 1px solid rgba(0, 0, 0, 0.08);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
:root.dark .docs-sidebar,
.dark .docs-sidebar {
  background: rgba(5, 5, 8, 0.88);
  border-right-color: rgba(255, 255, 255, 0.08);
}

.docs-toc {
  background: rgba(255, 255, 255, 0.92);
  border-left: 1px solid rgba(0, 0, 0, 0.08);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
:root.dark .docs-toc,
.dark .docs-toc {
  background: rgba(5, 5, 8, 0.88);
  border-left-color: rgba(255, 255, 255, 0.08);
}

.docs-nav-link-idle {
  @apply text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-brand-violet/5 dark:hover:bg-white/[0.04];
}
.docs-nav-link-idle-muted {
  @apply text-gray-500 dark:text-gray-500 hover:text-gray-900 dark:hover:text-white hover:bg-brand-violet/5 dark:hover:bg-white/[0.04];
}
.docs-nav-link-active {
  @apply bg-brand-violet/10 dark:bg-brand-cyan/10 text-brand-violet dark:text-brand-cyan font-semibold border-l-2 border-brand-violet dark:border-brand-cyan;
}

/* ── Doc prose styles ── */
.prose-doc {
  color: #374151;
  line-height: 1.75;
  font-size: 15px;
}
:root.dark .prose-doc {
  color: #d1d5db;
}

.prose-doc :deep(h1),
.prose-doc :deep(h2),
.prose-doc :deep(h3),
.prose-doc :deep(h4) {
  font-family: Outfit, 'Google Sans', system-ui, sans-serif;
  font-weight: 700;
  color: #111827;
  scroll-margin-top: 90px;
  line-height: 1.3;
  letter-spacing: -0.02em;
}
:root.dark .prose-doc :deep(h1),
:root.dark .prose-doc :deep(h2),
:root.dark .prose-doc :deep(h3),
:root.dark .prose-doc :deep(h4) {
  color: #f9fafb;
}

.prose-doc :deep(h2) {
  font-size: 1.5rem;
  margin-top: 2.5rem;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}
:root.dark .prose-doc :deep(h2) {
  border-bottom-color: rgba(255, 255, 255, 0.08);
}

.prose-doc :deep(h3) {
  font-size: 1.15rem;
  margin-top: 1.75rem;
  margin-bottom: 0.75rem;
}

.prose-doc :deep(p) {
  margin-bottom: 1.25rem;
}

.prose-doc :deep(a) {
  color: #7b2fff;
  text-decoration: underline;
  text-underline-offset: 2px;
}
:root.dark .prose-doc :deep(a) {
  color: #00d4ff;
}

.prose-doc :deep(ul),
.prose-doc :deep(ol) {
  padding-left: 1.5rem;
  margin-bottom: 1.25rem;
}
.prose-doc :deep(ul) { list-style-type: disc; }
.prose-doc :deep(ol) { list-style-type: decimal; }
.prose-doc :deep(li) { margin-bottom: 0.4rem; }

.prose-doc :deep(code):not(pre code) {
  color: #7b2fff;
  font-family: 'Fira Code', monospace;
  font-size: 0.875em;
}
:root.dark .prose-doc :deep(code):not(pre code) {
  color: #00d4ff;
}

.prose-doc :deep(.code-block-wrapper) {
  margin-bottom: 1.5rem;
}

.prose-doc :deep(pre) {
  background: #0d1117;
  border-radius: 0;
  overflow-x: auto;
  font-family: 'Fira Code', 'Consolas', monospace;
  font-size: 0.875rem;
  line-height: 1.7;
  margin-bottom: 0;
  scrollbar-width: thin;
  scrollbar-color: #30363d #161b22;
}
.prose-doc :deep(pre)::-webkit-scrollbar {
  height: 6px;
}
.prose-doc :deep(pre)::-webkit-scrollbar-track {
  background: #161b22;
}
.prose-doc :deep(pre)::-webkit-scrollbar-thumb {
  background: #30363d;
  border-radius: 3px;
}
.prose-doc :deep(pre)::-webkit-scrollbar-thumb:hover {
  background: #484f58;
}

.prose-doc :deep(pre code.hljs) {
  background: transparent;
  padding: 1.25rem 1.5rem;
  display: block;
}

.prose-doc :deep(blockquote) {
  border-left: 3px solid #7b2fff;
  padding: 0.5rem 1rem;
  margin: 1.5rem 0;
  background: #f5f3ff;
  border-radius: 0 8px 8px 0;
  color: #5b21b6;
  font-style: italic;
}
:root.dark .prose-doc :deep(blockquote) {
  border-left-color: #00d4ff;
  background: rgba(0, 212, 255, 0.05);
  color: #a5f3fc;
}

.prose-doc :deep(table) {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
}
.prose-doc :deep(th) {
  background: #f9fafb;
  text-align: left;
  padding: 0.6rem 0.8rem;
  font-weight: 600;
  border: 1px solid rgba(0, 0, 0, 0.08);
  color: #374151;
}
:root.dark .prose-doc :deep(th) {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.08);
  color: #e5e7eb;
}
.prose-doc :deep(td) {
  padding: 0.55rem 0.8rem;
  border: 1px solid rgba(0, 0, 0, 0.08);
  vertical-align: top;
}
:root.dark .prose-doc :deep(td) {
  border-color: rgba(255, 255, 255, 0.08);
}

.prose-doc :deep(hr) {
  border: none;
  border-top: 1px solid rgba(0, 0, 0, 0.08);
  margin: 2rem 0;
}
:root.dark .prose-doc :deep(hr) {
  border-top-color: rgba(255, 255, 255, 0.08);
}

.prose-doc :deep(img) {
  max-width: 100%;
  border-radius: 8px;
  margin: 1rem 0;
}

.doc-article {
  min-height: 60vh;
}

/* Scroll-to-top button transitions */
.scroll-top-enter-active,
.scroll-top-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.scroll-top-enter-from,
.scroll-top-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>

<!-- Code block styles are in main.css (global) -->
