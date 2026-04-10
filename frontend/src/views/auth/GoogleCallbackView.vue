<template>
  <div class="min-h-screen flex items-center justify-center bg-dark-900">
    <div class="text-center">
      <div class="w-14 h-14 mx-auto mb-4 border-[3px] border-gray-200 dark:border-dark-600 border-t-brand-violet dark:border-t-brand-cyan rounded-full animate-spin" />
      <p class="text-gray-400">{{ message }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const message = ref('Signing you in...')

onMounted(async () => {
  const token = route.query.token
  const error = route.query.error

  if (error) {
    message.value = 'Google sign-in failed. Redirecting...'
    setTimeout(() => router.push({ name: 'login', query: { error: 'google_failed' } }), 2000)
    return
  }

  if (token) {
    localStorage.setItem('auth_token', token)
    authStore.token = token
    await authStore.fetchMe()
    const redirect = authStore.isAdmin ? '/admin' : '/'
    router.push(redirect)
  } else {
    message.value = 'Invalid callback. Redirecting...'
    setTimeout(() => router.push({ name: 'login' }), 2000)
  }
})
</script>
