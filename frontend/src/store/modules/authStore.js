import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authService from '@/services/authService'

// Store para sa authentication state
export const useAuthStore = defineStore('auth', () => {
  const user  = ref(null)
  const token = ref(localStorage.getItem('pos_token') ?? null)

  // Naka-login ba?
  const isLoggedIn = computed(() => !!token.value)

  // Mag-login, i-save ang token
  async function login(credentials) {
    const { data } = await authService.login(credentials)
    user.value  = data.user
    token.value = data.token
    localStorage.setItem('pos_token', data.token)
  }

  // Mag-logout, limpyohan ang state
  async function logout() {
    try {
      await authService.logout()
    } finally {
      user.value  = null
      token.value = null
      localStorage.removeItem('pos_token')
    }
  }

  // Kuha sa user info gikan sa server (para sa page refresh)
  async function fetchUser() {
    if (!token.value) return
    const { data } = await authService.getMe()
    user.value = data
  }

  return { user, token, isLoggedIn, login, logout, fetchUser }
})
