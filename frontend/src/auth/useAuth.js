import { useAuthStore } from '@/store/modules/authStore'

// Composable para sa auth — gamiton sa bisan asa nga component
export function useAuth() {
  const authStore = useAuthStore()

  return {
    // Karon nga user
    user: authStore.user,
    isLoggedIn: authStore.isLoggedIn,

    // Mag-login
    login: authStore.login,

    // Mag-logout
    logout: authStore.logout,

    // I-check ang role
    hasRole: (role) => authStore.user?.role === role,
  }
}
