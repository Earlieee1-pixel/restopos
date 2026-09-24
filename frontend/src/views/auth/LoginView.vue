<template>
  <!-- Ari mu log-in sa POS -->
  <div class="min-h-screen flex items-center justify-center bg-brand-dark">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-sm">

      <!-- Logo -->
      <div class="text-center mb-6">
        <div class="text-5xl mb-2">🍗</div>
        <h2 class="text-2xl font-bold text-brand-red">RestoPos</h2>
        <p class="text-sm text-gray-500">Sign in to your account</p>
      </div>

      <!-- Error message -->
      <p v-if="error" class="text-red-500 text-sm text-center mb-4">{{ error }}</p>

      <!-- Login form -->
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="admin@restopos.com"
            class="pos-input"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            placeholder="••••••••"
            class="pos-input"
            required
          />
        </div>

        <button
          type="submit"
          class="btn-primary w-full"
          :disabled="loading"
        >
          {{ loading ? 'Logging in...' : 'Log In' }}
        </button>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/modules/authStore'

const authStore = useAuthStore()
const router    = useRouter()

const loading = ref(false)
const error   = ref('')

// Form data
const form = reactive({
  email:    '',
  password: '',
})

// Submit ang login form
async function handleLogin() {
  error.value   = ''
  loading.value = true
  try {
    await authStore.login(form)
    router.push({ name: 'dashboard' })
  } catch (e) {
    // Ipakita ang error kung sayop ang credentials
    error.value = e.response?.data?.message ?? 'Invalid email or password.'
  } finally {
    loading.value = false
  }
}
</script>
