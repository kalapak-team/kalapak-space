<template>
  <div ref="pageRoot" class="home-page">
    <!-- ═══════════════════ HERO ═══════════════════ -->
    <section
      class="relative min-h-[100svh] flex items-center justify-center overflow-hidden"
    >
      <MilkyWayGalaxy />

      <div class="absolute inset-0 hero-grid opacity-[0.04] dark:opacity-[0.06]" />

      <div
        class="absolute top-1/4 -left-32 w-[280px] sm:w-[480px] h-[280px] sm:h-[480px] rounded-full bg-brand-violet/12 blur-[100px] sm:blur-[140px] animate-float pointer-events-none"
      />
      <div
        class="absolute bottom-1/3 -right-32 w-[240px] sm:w-[420px] h-[240px] sm:h-[420px] rounded-full bg-brand-cyan/10 blur-[100px] sm:blur-[140px] animate-float pointer-events-none"
        style="animation-delay: 3s"
      />

      <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center relative z-10 pt-16 pb-28">
        <p
          data-hero
          class="font-display text-sm sm:text-base font-semibold uppercase tracking-[0.35em] text-brand-violet dark:text-brand-cyan mb-6 sm:mb-8"
        >
          Kalapak
        </p>

        <h1
          data-hero
          class="font-display text-5xl sm:text-6xl md:text-7xl lg:text-8xl xl:text-[5.5rem] font-bold tracking-tightest mb-5 sm:mb-7 leading-[1.02]"
        >
          <span class="text-gray-900 dark:text-white">Learning to be</span><br />
          <span class="gradient-text">a pillar for one another</span>
        </h1>

        <p
          data-hero
          class="text-base sm:text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto mb-10 sm:mb-12 leading-relaxed"
        >
          A student-driven engineering collective from Cambodia — building real-world
          software with purpose, collaboration, and pride.
        </p>

        <div
          data-hero
          class="flex flex-row flex-wrap justify-center gap-3 sm:gap-4"
        >
          <router-link to="/projects" class="btn-primary !rounded-full">
            Explore Projects
          </router-link>
          <router-link to="/about" class="btn-secondary !rounded-full">
            Meet the Team
          </router-link>
        </div>
      </div>
    </section>

    <!-- ═══════════════════ STATS (x.ai-style) ═══════════════════ -->
    <section
      ref="statsSection"
      class="stats-section"
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

      <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
        <div class="stats-row grid grid-cols-2 md:grid-cols-4">
          <div
            v-for="(stat, i) in stats"
            :key="stat.label"
            class="stat-item"
            :style="{ '--stat-i': i }"
          >
            <p class="stat-value" :data-infinite="stat.infinite || undefined">
              {{ animatedStats[i]?.display }}
            </p>
            <p class="stat-label" v-html="stat.labelHtml || stat.label" />
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════ WHAT WE DO ═══════════════════ -->
    <section class="py-16 sm:py-20 lg:py-28 relative z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div
          class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-14 sm:mb-16"
          data-reveal
        >
          <div>
            <span class="section-label">What we do</span>
            <h2 class="section-heading">
              Turning ideas into<br /><span class="gradient-text">reality</span>
            </h2>
          </div>
          <p class="section-subheading md:text-right md:max-w-md">
            We specialize in building full-stack applications, from concept to
            deployment, using the latest technologies and best practices.
          </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
          <div
            v-for="(service, i) in services"
            :key="i"
            data-reveal
            :data-reveal-delay="i * 100"
            class="group surface-panel p-7 sm:p-8 hover:-translate-y-1 hover:shadow-lg dark:hover:shadow-glow/10"
          >
            <div
              class="w-12 h-12 rounded-xl mb-5 flex items-center justify-center transition-transform duration-500 ease-expo group-hover:scale-110"
              :class="service.bgClass"
            >
              <span v-html="service.icon" />
            </div>
            <h3
              class="text-lg font-display font-bold text-gray-900 dark:text-white mb-2.5 group-hover:text-brand-violet dark:group-hover:text-brand-cyan transition-colors duration-300 ease-premium"
            >
              {{ service.title }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
              {{ service.description }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════ FEATURED PROJECTS ═══════════════════ -->
    <section class="py-16 sm:py-20 lg:py-28 relative z-10 bg-gray-50/60 dark:bg-dark-900/50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div
          class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-14 sm:mb-16"
          data-reveal
        >
          <div>
            <span class="section-label">Our work</span>
            <h2 class="section-heading">
              Featured <span class="gradient-text">Projects</span>
            </h2>
          </div>
          <router-link
            to="/projects"
            class="group inline-flex items-center gap-2 text-sm font-semibold text-brand-violet dark:text-brand-cyan hover:gap-3 transition-all duration-300 ease-premium"
          >
            View all projects
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </router-link>
        </div>

        <div
          v-if="projects.length"
          class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8"
        >
          <router-link
            v-for="(project, i) in projects"
            :key="project.id"
            :to="`/projects/${project.slug}`"
            class="group block"
            data-reveal
            :data-reveal-delay="i * 120"
          >
            <div
              class="surface-panel overflow-hidden hover:-translate-y-1.5 hover:shadow-xl dark:hover:shadow-glow/10"
            >
              <div class="aspect-[16/10] overflow-hidden bg-gray-100 dark:bg-dark-700">
                <img
                  v-if="resolveMediaUrl(project.cover_image)"
                  :src="resolveMediaUrl(project.cover_image)"
                  :alt="project.title"
                  class="w-full h-full object-cover transition-transform duration-700 ease-expo group-hover:scale-105"
                />
                <div
                  v-else
                  class="w-full h-full flex items-center justify-center"
                >
                  <div class="w-14 h-14 rounded-xl bg-gradient-brand/10 flex items-center justify-center">
                    <svg
                      class="w-7 h-7 text-brand-violet/40 dark:text-brand-cyan/40"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"
                      />
                    </svg>
                  </div>
                </div>
              </div>
              <div class="p-6">
                <div class="flex flex-wrap gap-2 mb-3">
                  <span
                    v-for="tag in (project.tags || []).slice(0, 3)"
                    :key="tag.id"
                    class="px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded-full bg-brand-violet/8 text-brand-violet/80 dark:bg-brand-cyan/8 dark:text-brand-cyan/80"
                  >
                    {{ tag.name }}
                  </span>
                </div>
                <h3
                  class="text-lg font-display font-bold text-gray-900 dark:text-white mb-2 group-hover:text-brand-violet dark:group-hover:text-brand-cyan transition-colors duration-300 ease-premium"
                >
                  {{ project.title }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-4">
                  {{ project.description }}
                </p>
                <span
                  class="inline-flex items-center gap-1 text-xs font-semibold text-brand-violet dark:text-brand-cyan group-hover:gap-2 transition-all duration-300 ease-premium"
                >
                  View project
                </span>
              </div>
            </div>
          </router-link>
        </div>

        <div
          v-else
          class="text-center py-20 text-gray-400 dark:text-gray-500"
          data-reveal
        >
          <svg
            class="w-16 h-16 mx-auto mb-4 opacity-40"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
            />
          </svg>
          <p class="text-sm">Projects coming soon</p>
        </div>
      </div>
    </section>

    <!-- ═══════════════════ TEAM PREVIEW ═══════════════════ -->
    <section class="py-16 sm:py-20 lg:py-28 relative z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
          <div data-reveal>
            <span class="section-label">Our team</span>
            <h2 class="section-heading mb-4 sm:mb-6">
              Meet the people<br />behind the
              <span class="gradient-text">code</span>
            </h2>
            <p class="section-subheading mb-8">
              We're a group of passionate students and developers from Phnom
              Penh, Cambodia. United by our love for clean code and innovative
              solutions.
            </p>
            <div class="flex flex-wrap items-center gap-4 sm:gap-6 mb-8">
              <div class="flex -space-x-3">
                <div
                  v-for="(member, i) in team.slice(0, 4)"
                  :key="i"
                  class="w-12 h-12 rounded-full border-2 border-white dark:border-dark-800 overflow-hidden bg-gradient-brand flex items-center justify-center ring-2 ring-transparent hover:ring-brand-violet dark:hover:ring-brand-cyan transition-all duration-300 ease-premium hover:z-10 hover:scale-110"
                >
                  <img
                    v-if="resolveMediaUrl(member.avatar)"
                    :src="resolveMediaUrl(member.avatar)"
                    :alt="member.name"
                    class="w-full h-full object-cover"
                  />
                  <span v-else class="text-white text-sm font-bold">{{
                    member.name?.charAt(0)
                  }}</span>
                </div>
                <div
                  v-if="team.length > 4"
                  class="w-12 h-12 rounded-full border-2 border-white dark:border-dark-800 bg-gray-100 dark:bg-dark-700 flex items-center justify-center"
                >
                  <span class="text-xs font-bold text-gray-500"
                    >+{{ team.length - 4 }}</span
                  >
                </div>
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ team.length }}+ Members
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Full-stack developers</p>
              </div>
            </div>
            <router-link to="/about" class="btn-secondary text-sm">
              About our team
            </router-link>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="(val, i) in values"
              :key="i"
              data-reveal
              class="surface-panel p-6 hover:-translate-y-1"
              :class="i === 0 ? 'sm:col-span-2' : ''"
              :data-reveal-delay="i * 80"
            >
              <div
                class="w-10 h-10 rounded-lg mb-4 flex items-center justify-center font-display text-xs font-bold"
                :class="val.bgClass"
              >
                {{ val.mark }}
              </div>
              <h4 class="font-display font-bold text-gray-900 dark:text-white mb-1.5">
                {{ val.title }}
              </h4>
              <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ val.desc }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════ LATEST BLOG ═══════════════════ -->
    <section class="py-16 sm:py-20 lg:py-28 relative z-10 bg-gray-50/60 dark:bg-dark-900/50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div
          class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-14 sm:mb-16"
          data-reveal
        >
          <div>
            <span class="section-label">Blog</span>
            <h2 class="section-heading">
              Latest <span class="gradient-text">Articles</span>
            </h2>
          </div>
          <router-link
            to="/blog"
            class="group inline-flex items-center gap-2 text-sm font-semibold text-brand-violet dark:text-brand-cyan hover:gap-3 transition-all duration-300 ease-premium"
          >
            Read all articles
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </router-link>
        </div>

        <div
          v-if="posts.length"
          class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8"
        >
          <router-link
            v-for="(post, i) in posts"
            :key="post.id"
            :to="`/blog/${post.slug}`"
            class="group block"
            data-reveal
            :data-reveal-delay="i * 120"
          >
            <div
              class="surface-panel overflow-hidden h-full flex flex-col hover:-translate-y-1.5 hover:shadow-xl dark:hover:shadow-glow/10"
            >
              <div
                v-if="resolveMediaUrl(post.cover_image)"
                class="aspect-[16/9] overflow-hidden"
              >
                <img
                  :src="resolveMediaUrl(post.cover_image)"
                  :alt="post.title"
                  class="w-full h-full object-cover transition-transform duration-700 ease-expo group-hover:scale-105"
                />
              </div>
              <div class="p-6 flex flex-col flex-1">
                <div class="flex items-center gap-3 mb-4">
                  <span
                    v-if="post.category?.name"
                    class="px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded-full bg-brand-violet/8 text-brand-violet/80 dark:bg-brand-cyan/8 dark:text-brand-cyan/80"
                  >
                    {{ post.category.name }}
                  </span>
                  <span class="text-[11px] text-gray-400">{{
                    formatDate(post.published_at)
                  }}</span>
                </div>
                <h3
                  class="text-lg font-display font-bold text-gray-900 dark:text-white mb-2 group-hover:text-brand-violet dark:group-hover:text-brand-cyan transition-colors duration-300 ease-premium line-clamp-2"
                >
                  {{ post.title }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-3 mb-4 flex-1">
                  {{ post.excerpt }}
                </p>
                <div
                  class="flex items-center gap-3 mt-auto pt-4 border-t border-black/[0.08] dark:border-white/[0.08]"
                >
                  <button
                    v-if="post.author?.username"
                    type="button"
                    class="flex items-center gap-2 text-left rounded-full -m-1 p-1 hover:bg-gray-50 dark:hover:bg-dark-700/50 transition-colors duration-300 ease-premium"
                    @click.stop.prevent="goProfile(post.author.username)"
                  >
                    <div
                      class="w-7 h-7 rounded-full bg-gradient-brand flex items-center justify-center overflow-hidden"
                    >
                      <img
                        v-if="resolveMediaUrl(post.author.avatar)"
                        :src="resolveMediaUrl(post.author.avatar)"
                        class="w-full h-full object-cover"
                      />
                      <span v-else class="text-white text-[10px] font-bold">{{
                        post.author.name?.charAt(0)
                      }}</span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{
                      post.author.name
                    }}</span>
                  </button>
                  <div v-else-if="post.author" class="flex items-center gap-2">
                    <div
                      class="w-7 h-7 rounded-full bg-gradient-brand flex items-center justify-center overflow-hidden"
                    >
                      <img
                        v-if="resolveMediaUrl(post.author.avatar)"
                        :src="resolveMediaUrl(post.author.avatar)"
                        class="w-full h-full object-cover"
                      />
                      <span v-else class="text-white text-[10px] font-bold">{{
                        post.author.name?.charAt(0)
                      }}</span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{
                      post.author.name
                    }}</span>
                  </div>
                  <span
                    v-if="post.reading_time"
                    class="text-[10px] text-gray-400 ml-auto"
                    >{{ post.reading_time }} min read</span
                  >
                </div>
              </div>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- ═══════════════════ TECH STACK — BLACK HOLE ORBIT ═══════════════════ -->
    <section class="tech-section relative z-10">
      <div class="max-w-7xl mx-auto px-4 text-center mb-14" data-reveal>
        <span class="section-label">Technologies</span>
        <h2 class="section-heading mb-4">
          Our <span class="gradient-text">Tech Stack</span>
        </h2>
        <p class="section-subheading mx-auto">
          The tools and frameworks we use to build exceptional digital products.
        </p>
      </div>

      <div class="orbit-shell" data-reveal data-reveal-delay="150">
        <div class="blackhole-glow blackhole-glow-violet" />
        <div class="blackhole-glow blackhole-glow-cyan" />
        <div class="blackhole-ring ring-outer" />
        <div class="blackhole-ring ring-mid" />
        <div class="blackhole-ring ring-inner" />

        <div class="event-horizon">
          <div class="event-horizon-core" />
        </div>

        <div class="orbit-track">
          <div
            v-for="(tech, i) in techStack"
            :key="tech.name"
            class="orbit-node group"
            :style="{
              '--i': i,
              '--count': techStack.length,
            }"
          >
            <div class="orbit-node-inner">
              <img
                :src="tech.logo"
                :alt="tech.name"
                class="tech-logo"
                loading="lazy"
              />
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import { publicApi } from "@/services/api";
import dayjs from "dayjs";
import MilkyWayGalaxy from "@/components/common/MilkyWayGalaxy.vue";
import { resolveMediaUrl } from "../../../composables/useMediaUrl.js";
import { useHomeMotion } from "../../../composables/usePremiumMotion.js";

const pageRoot = ref(null);
useHomeMotion(pageRoot);

const router = useRouter();

function goProfile(username) {
  router.push({ name: "user-profile", params: { username } });
}

const projects = ref([]);
const posts = ref([]);
const team = ref([]);

const statsSection = ref(null);
const statsInView = ref(false);
const statsAnimated = ref(false);
const spotlight = ref({ x: 50, y: 50, active: false });

const stats = [
  { value: "4+", label: "team members", target: 4, suffix: "+" },
  { value: "10+", label: "projects shipped", target: 10, suffix: "+" },
  { value: "100%", label: "passion", target: 100, suffix: "%" },
  {
    value: "∞",
    label: "lines of code",
    infinite: true,
    labelHtml: "lines of <span class=\"stat-accent\">code</span>",
  },
];

const animatedStats = ref(
  stats.map((stat) => ({
    ...stat,
    display: stat.infinite ? "" : "0",
  })),
);

const statsSpotlightStyle = computed(() => ({
  opacity: spotlight.value.active ? "1" : "0",
  "--spot-x": `${spotlight.value.x}%`,
  "--spot-y": `${spotlight.value.y}%`,
}));

function onStatsPointer(e) {
  const el = statsSection.value;
  if (!el) return;
  const rect = el.getBoundingClientRect();
  spotlight.value = {
    x: ((e.clientX - rect.left) / rect.width) * 100,
    y: ((e.clientY - rect.top) / rect.height) * 100,
    active: true,
  };
}

function onStatsPointerLeave() {
  spotlight.value = { ...spotlight.value, active: false };
}

const services = [
  {
    title: "Web Development",
    description:
      "Modern, responsive web applications built with Vue.js, Laravel, and cutting-edge frameworks for optimal user experience.",
    bgClass: "bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400",
    icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5a17.92 17.92 0 01-8.716-2.247m0 0A8.966 8.966 0 013 12c0-1.264.26-2.467.732-3.558"/></svg>',
  },
  {
    title: "Mobile Apps",
    description:
      "Cross-platform mobile applications with Flutter, delivering native performance on iOS and Android from a single codebase.",
    bgClass:
      "bg-purple-100 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400",
    icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>',
  },
  {
    title: "API & Backend",
    description:
      "Scalable RESTful APIs and robust backend systems with Laravel, PostgreSQL, and Docker for enterprise-grade reliability.",
    bgClass:
      "bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400",
    icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z"/></svg>',
  },
  {
    title: "UI/UX Design",
    description:
      "Beautiful, intuitive interfaces designed with Tailwind CSS, focusing on accessibility, responsiveness, and delightful interactions.",
    bgClass: "bg-pink-100 dark:bg-pink-900/20 text-pink-700 dark:text-pink-400",
    icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/></svg>',
  },
  {
    title: "DevOps & Cloud",
    description:
      "Containerized deployments with Docker, CI/CD pipelines, and cloud infrastructure management for seamless operations.",
    bgClass:
      "bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400",
    icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z"/></svg>',
  },
  {
    title: "Open Source",
    description:
      "Contributing to the developer community with open-source projects, libraries, and tools that solve real problems.",
    bgClass: "bg-cyan-100 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-400",
    icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/></svg>',
  },
];

const values = [
  {
    mark: "01",
    title: "Innovation First",
    desc: "We push boundaries with modern tech, always exploring new solutions to challenge the status quo.",
    bgClass:
      "bg-brand-violet/15 text-brand-violet dark:bg-brand-violet/20 dark:text-brand-cyan",
  },
  {
    mark: "02",
    title: "Collaboration",
    desc: "Open-source mindset, team-driven development.",
    bgClass:
      "bg-brand-cyan/15 text-brand-cyan dark:bg-brand-cyan/20 dark:text-brand-cyan",
  },
  {
    mark: "03",
    title: "Continuous Learning",
    desc: "Always growing through mentorship & practice.",
    bgClass:
      "bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400",
  },
  {
    mark: "04",
    title: "Cambodia Proud",
    desc: "Building world-class tech from Phnom Penh.",
    bgClass:
      "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400",
  },
];

const techStack = [
  {
    name: "Vue.js",
    logo: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/vuejs/vuejs-original.svg",
  },
  {
    name: "Laravel",
    logo: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-original.svg",
  },
  {
    name: "Tailwind CSS",
    logo: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/tailwindcss/tailwindcss-original.svg",
  },
  {
    name: "PostgreSQL",
    logo: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/postgresql/postgresql-original.svg",
  },
  {
    name: "Docker",
    logo: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/docker/docker-original.svg",
  },
  {
    name: "Flutter",
    logo: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/flutter/flutter-original.svg",
  },
  {
    name: "Node.js",
    logo: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nodejs/nodejs-original.svg",
  },
  {
    name: "Python",
    logo: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/python/python-original.svg",
  },
  {
    name: "TypeScript",
    logo: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/typescript/typescript-original.svg",
  },
  {
    name: "Nginx",
    logo: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nginx/nginx-original.svg",
  },
  {
    name: "Git",
    logo: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/git/git-original.svg",
  },
  {
    name: "GitHub",
    logo: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/github/github-original.svg",
  },
];

function formatDate(date) {
  return date ? dayjs(date).format("MMM D, YYYY") : "";
}

function easeOutExpo(t) {
  return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
}

function animateStatValue(index, stat) {
  if (stat.infinite) {
    animatedStats.value[index].display = "∞";
    return;
  }

  const target = stat.target;
  const suffix = stat.suffix || "";
  const duration = 1600 + index * 120;
  const start = performance.now();

  const tick = (now) => {
    const progress = Math.min((now - start) / duration, 1);
    const eased = easeOutExpo(progress);
    const current = Math.round(target * eased);
    animatedStats.value[index].display = `${current}${suffix}`;

    if (progress < 1) {
      requestAnimationFrame(tick);
    } else {
      animatedStats.value[index].display = stat.value;
    }
  };

  requestAnimationFrame(tick);
}

function runStatsAnimation() {
  if (statsAnimated.value) return;
  statsAnimated.value = true;
  statsInView.value = true;

  const reduce =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  if (reduce) {
    stats.forEach((stat, index) => {
      animatedStats.value[index].display = stat.value;
    });
    return;
  }

  stats.forEach((stat, index) => {
    setTimeout(() => animateStatValue(index, stat), 80 + index * 140);
  });
}

let statsObserver = null;

onMounted(async () => {
  try {
    const [projectsRes, postsRes, teamRes] = await Promise.all([
      publicApi.getProjects({ per_page: 3, is_featured: true }),
      publicApi.getBlogPosts({ per_page: 3 }),
      publicApi.getTeam(),
    ]);
    projects.value = projectsRes.data.data || [];
    posts.value = postsRes.data.data || [];
    team.value = teamRes.data.data || teamRes.data || [];
  } catch {
    // Silently fail for public page
  }

  if (statsSection.value && typeof IntersectionObserver !== "undefined") {
    statsObserver = new IntersectionObserver(
      (entries) => {
        if (entries.some((e) => e.isIntersecting)) {
          runStatsAnimation();
          statsObserver?.disconnect();
          statsObserver = null;
        }
      },
      { threshold: 0.35, rootMargin: "0px 0px -8% 0px" },
    );
    statsObserver.observe(statsSection.value);
  } else {
    runStatsAnimation();
  }
});

onBeforeUnmount(() => {
  statsObserver?.disconnect();
  statsObserver = null;
});
</script>

<style scoped>
.hero-grid {
  background-image:
    linear-gradient(rgba(123, 47, 255, 0.12) 1px, transparent 1px),
    linear-gradient(90deg, rgba(123, 47, 255, 0.12) 1px, transparent 1px);
  background-size: 56px 56px;
  mask-image: radial-gradient(
    ellipse 75% 65% at 50% 45%,
    black 15%,
    transparent 72%
  );
  -webkit-mask-image: radial-gradient(
    ellipse 75% 65% at 50% 45%,
    black 15%,
    transparent 72%
  );
}

/* ── Stats (x.ai-inspired) ── */
.stats-section {
  position: relative;
  z-index: 10;
  margin-top: 0;
  padding: 4.5rem 0 5rem;
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
  padding: 1.75rem 1rem;
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
  font-size: clamp(2.75rem, 6vw, 4.25rem);
  font-weight: 700;
  letter-spacing: -0.04em;
  line-height: 1;
  color: #0a0a0a;
  margin: 0 0 0.65rem;
  font-variant-numeric: tabular-nums;
  will-change: contents;
}

.dark .stat-value {
  color: #f5f5f7;
}

.stat-value[data-infinite] {
  background: linear-gradient(105deg, #7b2fff 0%, #00d4ff 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.stat-label {
  margin: 0;
  max-width: 12rem;
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

.stat-accent {
  font-family: ui-monospace, "Fira Code", SFMono-Regular, Menlo, monospace;
  font-size: 0.78em;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #6b6b76;
}

.dark .stat-accent {
  color: #b0b0ba;
}

.stat-item:hover .stat-value {
  transform: translateY(-1px);
}

.stat-value {
  transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}

@media (max-width: 767px) {
  .stat-item:nth-child(n + 3) {
    border-top: 1px solid rgba(0, 0, 0, 0.06);
  }

  .dark .stat-item:nth-child(n + 3) {
    border-top-color: rgba(255, 255, 255, 0.06);
  }
}

@media (prefers-reduced-motion: reduce) {
  .stat-item {
    opacity: 1;
    transform: none;
    filter: none;
    transition: none;
  }
}

/* ── Tech Stack Black Hole ── */
.tech-section {
  --space-1: #f4f8ff;
  --space-2: #e9f1ff;
  --footer-blend: #f8fafc;
  --orbit-size: min(86vw, 780px);
  --orbit-node-size: 60px;
  --orbit-node-gap: 24px;
  --orbit-radius: calc(
    var(--orbit-size) / 2 - var(--orbit-node-size) / 2 - var(--orbit-node-gap)
  );
  position: relative;
  padding: 64px 0 84px;
  overflow: hidden;
  background:
    linear-gradient(
      to bottom,
      rgba(255, 255, 255, 0.98) 0%,
      rgba(248, 250, 252, 0.85) 14%,
      rgba(248, 250, 252, 0) 30%
    ),
    radial-gradient(
      circle at 16% 18%,
      rgba(120, 95, 255, 0.14),
      transparent 34%
    ),
    radial-gradient(
      circle at 82% 24%,
      rgba(68, 178, 255, 0.13),
      transparent 40%
    ),
    radial-gradient(
      circle at 50% 55%,
      #ffffff 0%,
      #f4f8ff 30%,
      #e4efff 62%,
      #d9e8ff 100%
    );
}

.tech-section::after {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 120px;
  pointer-events: none;
  background: linear-gradient(
    to bottom,
    rgba(248, 250, 252, 0) 0%,
    rgba(248, 250, 252, 0.65) 58%,
    var(--footer-blend) 100%
  );
}

.tech-section::before {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  top: 0;
  height: 120px;
  pointer-events: none;
  background: linear-gradient(
    to bottom,
    rgba(248, 250, 252, 0.9) 0%,
    rgba(248, 250, 252, 0.25) 55%,
    rgba(248, 250, 252, 0) 100%
  );
}

:root.dark .tech-section::after,
.dark .tech-section::after {
  height: 170px;
  background: linear-gradient(
    to bottom,
    rgba(5, 5, 8, 0) 0%,
    rgba(5, 5, 8, 0.58) 64%,
    rgba(5, 5, 8, 1) 100%
  );
}

:root.dark .tech-section::before,
.dark .tech-section::before {
  height: 150px;
  background: linear-gradient(
    to bottom,
    rgba(5, 5, 8, 1) 0%,
    rgba(5, 5, 8, 0.65) 45%,
    rgba(5, 5, 8, 0) 100%
  );
}

@media (min-width: 640px) {
  .tech-section {
    padding: 90px 0 110px;
  }
}

.orbit-shell {
  position: relative;
  width: var(--orbit-size);
  height: var(--orbit-size);
  margin: 0 auto;
  border-radius: 50%;
  display: grid;
  place-items: center;
  box-shadow:
    0 0 0 1px rgba(118, 137, 246, 0.28),
    inset 0 0 0 1px rgba(146, 176, 255, 0.24),
    0 28px 70px rgba(66, 108, 198, 0.2);
}

.blackhole-glow {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(75px);
  opacity: 0.55;
}

.blackhole-glow-violet {
  width: 55%;
  height: 55%;
  top: 8%;
  left: 18%;
  background: rgba(129, 97, 255, 0.28);
}

.blackhole-glow-cyan {
  width: 48%;
  height: 48%;
  right: 16%;
  bottom: 14%;
  background: rgba(57, 186, 255, 0.25);
}

.blackhole-ring {
  position: absolute;
  inset: 50%;
  border-radius: 50%;
  transform: translate(-50%, -50%);
  pointer-events: none;
  border: 1px solid rgba(132, 160, 248, 0.24);
  box-shadow: inset 0 0 42px rgba(89, 132, 255, 0.1);
  animation: ringPulse 8s ease-in-out infinite;
}

.ring-outer {
  width: calc(var(--orbit-size) - 8px);
  height: calc(var(--orbit-size) - 8px);
}

.ring-mid {
  width: calc(var(--orbit-size) * 0.72);
  height: calc(var(--orbit-size) * 0.72);
  opacity: 0.55;
  animation-delay: 1.8s;
}

.ring-inner {
  width: calc(var(--orbit-size) * 0.46);
  height: calc(var(--orbit-size) * 0.46);
  opacity: 0.48;
  animation-delay: 3.2s;
}

.event-horizon {
  width: 44%;
  height: 44%;
  border-radius: 50%;
  background: radial-gradient(
    circle at 50% 40%,
    rgba(232, 244, 255, 1) 0%,
    rgba(201, 223, 255, 0.85) 24%,
    rgba(145, 181, 242, 0.62) 56%,
    rgba(86, 126, 206, 0.78) 78%,
    rgba(46, 84, 160, 0.92) 100%
  );
  display: grid;
  place-items: center;
  box-shadow:
    0 0 0 1px rgba(146, 198, 255, 0.5),
    0 0 54px rgba(68, 109, 255, 0.34),
    inset 0 10px 24px rgba(255, 255, 255, 0.35),
    inset 0 -16px 32px rgba(24, 60, 137, 0.62);
}

.event-horizon-core {
  width: 63%;
  height: 63%;
  border-radius: 50%;
  background: radial-gradient(
    circle at 46% 42%,
    rgba(214, 231, 255, 0.99),
    rgba(113, 149, 224, 0.75) 58%,
    rgba(47, 84, 161, 0.92)
  );
  box-shadow: 0 0 0 1px rgba(109, 182, 255, 0.5);
}

.orbit-track {
  position: absolute;
  inset: 0;
  animation: orbitalSpin 82s linear infinite;
}

.orbit-shell:hover .orbit-track {
  animation-play-state: paused;
}

.orbit-node {
  position: absolute;
  top: 50%;
  left: 50%;
  width: var(--orbit-node-size);
  height: var(--orbit-node-size);
  display: flex;
  flex-direction: column;
  align-items: center;
  transform: translate(-50%, -50%)
    rotate(calc((360deg / var(--count)) * var(--i)))
    translateY(calc(var(--orbit-radius) * -1))
    rotate(calc((360deg / var(--count)) * var(--i) * -1));
}

.orbit-node-inner {
  width: var(--orbit-node-size);
  height: var(--orbit-node-size);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.92);
  border: 1px solid rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(6px);
  box-shadow:
    inset 0 0 0 1px rgba(255, 255, 255, 0.86),
    0 6px 14px rgba(80, 125, 220, 0.16);
  transition:
    transform 0.35s ease,
    border-color 0.35s ease,
    box-shadow 0.35s ease,
    background 0.35s ease;
}

.orbit-node:hover .orbit-node-inner {
  transform: scale(1.12);
  border-color: rgba(165, 202, 255, 0.78);
  background: rgba(245, 251, 255, 0.99);
  box-shadow:
    inset 0 0 0 1px rgba(255, 255, 255, 0.95),
    0 10px 24px rgba(92, 164, 255, 0.32);
}

.tech-logo {
  width: 34px;
  height: 34px;
  object-fit: contain;
  transition:
    filter 0.3s ease,
    transform 0.3s ease;
}

.orbit-node:hover .tech-logo {
  transform: scale(1.08);
  filter: drop-shadow(0 0 9px rgba(88, 186, 255, 0.54));
}

:root.dark .tech-section,
.dark .tech-section {
  --space-1: #0a0a12;
  --space-2: #050508;
  --footer-blend: #050508;
  background:
    linear-gradient(
      to bottom,
      rgba(5, 5, 8, 0.96) 0%,
      rgba(10, 10, 18, 0.8) 14%,
      rgba(10, 10, 18, 0) 30%
    ),
    radial-gradient(
      circle at 20% 20%,
      rgba(107, 85, 255, 0.16),
      transparent 32%
    ),
    radial-gradient(
      circle at 75% 30%,
      rgba(0, 212, 255, 0.13),
      transparent 38%
    ),
    radial-gradient(
      circle at 50% 55%,
      #1a2548 0%,
      #0f1630 34%,
      var(--space-1) 66%,
      var(--space-2) 100%
    );
}

:root.dark .orbit-shell,
.dark .orbit-shell {
  box-shadow:
    0 0 0 1px rgba(97, 71, 255, 0.35),
    inset 0 0 0 1px rgba(120, 146, 255, 0.2),
    0 45px 120px rgba(5, 5, 8, 0.75);
}

:root.dark .blackhole-glow-violet,
.dark .blackhole-glow-violet {
  background: rgba(117, 70, 255, 0.45);
}

:root.dark .blackhole-glow-cyan,
.dark .blackhole-glow-cyan {
  background: rgba(17, 184, 255, 0.36);
}

:root.dark .blackhole-ring,
.dark .blackhole-ring {
  border-color: rgba(120, 146, 255, 0.24);
  box-shadow: inset 0 0 40px rgba(77, 122, 255, 0.14);
}

:root.dark .event-horizon,
.dark .event-horizon {
  background: radial-gradient(
    circle at 50% 40%,
    rgba(199, 225, 255, 0.92) 0%,
    rgba(154, 187, 255, 0.55) 25%,
    rgba(88, 114, 185, 0.25) 54%,
    rgba(13, 29, 71, 0.8) 74%,
    rgba(5, 5, 8, 1) 100%
  );
  box-shadow:
    0 0 0 1px rgba(146, 198, 255, 0.38),
    0 0 50px rgba(68, 109, 255, 0.45),
    inset 0 12px 26px rgba(255, 255, 255, 0.2),
    inset 0 -20px 40px rgba(5, 5, 8, 0.75);
}

:root.dark .event-horizon-core,
.dark .event-horizon-core {
  background: radial-gradient(
    circle at 46% 42%,
    rgba(183, 210, 255, 0.95),
    rgba(34, 51, 100, 0.65) 58%,
    rgba(5, 5, 8, 0.92)
  );
  box-shadow: 0 0 0 1px rgba(109, 182, 255, 0.38);
}

:root.dark .orbit-node-inner,
.dark .orbit-node-inner {
  background: rgba(10, 10, 18, 0.9);
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow:
    inset 0 0 0 1px rgba(255, 255, 255, 0.06),
    0 0 24px rgba(84, 132, 255, 0.25);
}

:root.dark .orbit-node:hover .orbit-node-inner,
.dark .orbit-node:hover .orbit-node-inner {
  background: rgba(18, 18, 28, 0.96);
  box-shadow:
    inset 0 0 0 1px rgba(255, 255, 255, 0.16),
    0 0 36px rgba(99, 174, 255, 0.56);
}

@keyframes orbitalSpin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@keyframes ringPulse {
  0%,
  100% {
    transform: translate(-50%, -50%) scale(1);
    opacity: 0.35;
  }
  50% {
    transform: translate(-50%, -50%) scale(1.025);
    opacity: 0.62;
  }
}

@media (max-width: 768px) {
  .tech-section {
    --orbit-size: min(95vw, 560px);
    --orbit-node-size: 50px;
    --orbit-node-gap: 18px;
  }
  .tech-logo {
    width: 28px;
    height: 28px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .orbit-track,
  .blackhole-ring {
    animation: none;
  }
  .animate-float {
    animation: none;
  }
}
</style>
