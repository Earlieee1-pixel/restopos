<template>
  <!-- Ari mu log-in sa POS -->
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden">

    <!-- Background image with dark overlay -->
    <div
      class="absolute inset-0 bg-cover bg-center bg-no-repeat"
      style="background-image: url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1920&q=80');"
    ></div>
    <!-- Dark overlay para readable ang form -->
    <div class="absolute inset-0 bg-black/65 backdrop-blur-sm"></div>

    <!-- Login card -->
    <div class="relative z-10 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-8 w-full max-w-sm mx-4">

      <!-- Logo -->
      <div class="text-center mb-6">
        <div class="text-5xl mb-2">🍗</div>
        <h2 class="text-2xl font-bold text-brand-red">RestoPos</h2>
        <p class="text-sm text-gray-500">Sign in to your account</p>
      </div>

      <!-- Error message -->
      <p v-if="error" class="text-sm text-center mb-4" :class="rateLimited ? 'text-orange-500' : 'text-red-500'">
        {{ error }}
      </p>

      <!-- Login form -->
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="pos-input"
            required
            :disabled="rateLimited"
          />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="pos-input"
            required
            :disabled="rateLimited"
          />
        </div>

        <button
          type="submit"
          class="btn-primary w-full"
          :disabled="loading || rateLimited"
        >
          {{ loading ? 'Logging in...' : rateLimited ? `Try again in ${countdown}s` : 'Log In' }}
        </button>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/modules/authStore'

const authStore   = useAuthStore()
const router      = useRouter()
const loading     = ref(false)
const error       = ref('')
const rateLimited = ref(false)
const countdown   = ref(60)

const form = reactive({ email: '', password: '' })

// I-start ang countdown base sa Retry-After header kung naa, fallback 60s
function startCooldown(retryAfter = 60) {
  rateLimited.value = true
  countdown.value   = Math.ceil(retryAfter)
  const interval = setInterval(() => {
    countdown.value--
    if (countdown.value <= 0) {
      clearInterval(interval)
      rateLimited.value = false
      error.value       = ''
    }
  }, 1000)
}

// Submit ang login form
async function handleLogin() {
  error.value   = ''
  loading.value = true
  try {
    await authStore.login(form)
    router.push({ name: 'dashboard' })
  } catch (e) {
    // I-check kung rate limited ba
    if (e.response?.status === 429) {
      const retryAfter = e.response?.headers?.['retry-after'] ?? 60
      error.value = `Too many login attempts. Please wait ${Math.ceil(retryAfter)} seconds.`
      startCooldown(retryAfter)
    } else {
      error.value = e.response?.data?.message ?? 'Invalid email or password.'
    }
  } finally {
    loading.value = false
  }
}
</script>
