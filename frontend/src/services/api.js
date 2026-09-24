import axios from 'axios'
import { getActivePinia } from 'pinia'

// Base axios instance para sa tanan API calls
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept':       'application/json',
  },
})

// I-attach ang token sa matag request kung naa
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('pos_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Kung 401 — limpyohan ang state then i-redirect sa login
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Tangtangon ang token sa localStorage
      localStorage.removeItem('pos_token')

      // I-reset ang auth store kung naka-init na ang Pinia
      try {
        const pinia = getActivePinia()
        if (pinia) {
          // Kuha ang auth store nga wala mag-import para malikayan ang circular dep
          const authStore = pinia.state.value['auth']
          if (authStore) {
            authStore.user  = null
            authStore.token = null
          }
        }
      } catch {
        // Dili i-throw — basta limpyohan lang ang localStorage ug redirect
      }

      // I-redirect sa login kung dili pa naa didto
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api
