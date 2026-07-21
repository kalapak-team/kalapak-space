<template>
  <div
    class="min-h-screen flex flex-col bg-white dark:bg-dark-900 transition-colors duration-500"
  >
    <StarfieldBg />
    <div
      class="pointer-events-none fixed inset-0 z-0 bg-gradient-mesh opacity-70 dark:opacity-100"
      aria-hidden="true"
    />
    <AppNavbar />
    <main class="flex-1 relative z-10">
      <router-view />
    </main>
    <AppFooter />
  </div>
</template>

<script setup>
import AppNavbar from "@/components/layout/AppNavbar.vue";
import AppFooter from "@/components/layout/AppFooter.vue";
import StarfieldBg from "@/components/common/StarfieldBg.vue";
</script>
