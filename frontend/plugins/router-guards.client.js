import { useAuthStore } from "@/stores/auth";

export default defineNuxtPlugin((nuxtApp) => {
  const router = nuxtApp.$router;

  router.beforeEach(async (to) => {
    const authStore = useAuthStore();
    authStore.restoreFromStorage();

    if (authStore.token && !authStore.user) {
      try {
        await authStore.fetchMe();
      } catch {
        // Keep auth state on transient network/backend errors.
      }
    }

    // Per-resource permissions live only in memory (not localStorage). Refetch on
    // every admin navigation so changes made by Super Admin apply immediately
    // without the target admin refreshing repeatedly.
    const enteringAdminArea = to.matched.some(
      (record) => record.meta.requiresAdmin,
    );
    if (
      enteringAdminArea &&
      authStore.isAuthenticated &&
      authStore.isAdmin &&
      !authStore.isSuperAdmin
    ) {
      try {
        await authStore.fetchPermissions();
      } catch {
        /* keep previous permissions on transient errors */
      }
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      return { name: "login", query: { redirect: to.fullPath } };
    }

    if (to.meta.requiresAdmin && !authStore.isAdmin) {
      return { name: "home" };
    }

    if (to.meta.requiresSuperAdmin && !authStore.isSuperAdmin) {
      return { name: "admin-dashboard" };
    }

    if (to.meta.guestOnly && authStore.isAuthenticated) {
      return authStore.isAdmin ? { name: "admin-dashboard" } : { name: "home" };
    }

    return true;
  });

  router.afterEach((to) => {
    document.title =
      to.meta.title || "Kalapak Code Team | Modern Tech Solutions from Cambodia";
  });
});
