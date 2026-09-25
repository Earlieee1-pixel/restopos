<template>
  <!-- Main layout wrapper — sidebar + content area -->
  <div class="flex h-screen overflow-hidden bg-gray-100">

    <!-- Sidebar navigation -->
    <aside class="w-64 bg-brand-dark text-white flex flex-col shrink-0">

      <!-- Logo/brand -->
      <div class="p-5 border-b border-gray-700">
        <h1 class="text-xl font-bold text-brand-yellow tracking-wide">🍗 RestoPos</h1>
        <div class="mt-2 flex items-center gap-2">
          <p class="text-sm text-gray-300 truncate">{{ user?.name }}</p>
          <!-- Role badge -->
          <span
            class="text-xs px-2 py-0.5 rounded-full font-semibold shrink-0"
            :class="roleBadgeClass"
          >{{ user?.role }}</span>
        </div>
        <!-- Change password link -->
        <button
          class="mt-2 text-xs text-gray-500 hover:text-gray-300 transition-colors"
          @click="showPasswordModal = true"
        >🔑 Change Password</button>
      </div>

      <!-- Nav links -->
      <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <RouterLink
          v-for="link in navLinks"
          :key="link.name"
          :to="link.to"
          class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-700 transition-colors"
          active-class="bg-brand-red text-white"
        >
          <span>{{ link.icon }}</span>
          {{ link.label }}
        </RouterLink>

        <!-- Settings section — manager/admin lang -->
        <template v-if="settingsLinks.length">
          <div class="pt-4 pb-1 px-3 text-xs text-gray-500 uppercase tracking-wider font-semibold">
            Settings
          </div>
          <RouterLink
            v-for="link in settingsLinks"
            :key="link.name"
            :to="link.to"
            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-700 transition-colors"
            active-class="bg-brand-red text-white"
          >
            <span>{{ link.icon }}</span>
            {{ link.label }}
          </RouterLink>
        </template>
      </nav>

      <!-- Logout button sa ubos -->
      <div class="p-4 border-t border-gray-700">
        <button
          class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-gray-700 transition-colors"
          @click="confirmLogout = true"
        >
          🚪 Logout
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 overflow-hidden flex flex-col">
      <RouterView />
    </main>

  </div>

  <!-- Logout confirmation dialog -->
  <div v-if="confirmLogout" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4 text-center">
      <div class="text-4xl mb-3">🚪</div>
      <h2 class="text-lg font-bold mb-2">Log out?</h2>
      <p class="text-sm text-gray-500 mb-6">You will be returned to the login screen.</p>
      <div class="flex gap-2">
        <button class="btn-primary flex-1" :disabled="loggingOut" @click="handleLogout">
          {{ loggingOut ? 'Logging out...' : 'Yes, log out' }}
        </button>
        <button class="btn-secondary flex-1" @click="confirmLogout = false">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Change password modal -->
  <div v-if="showPasswordModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4">
      <h2 class="text-lg font-bold mb-4">🔑 Change Password</h2>

      <form @submit.prevent="submitPasswordChange" class="space-y-3">
        <div>
          <label class="block text-sm font-medium mb-1">Current Password</label>
          <input v-model="pwForm.current_password" type="password" class="pos-input" required />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">New Password</label>
          <input v-model="pwForm.new_password" type="password" class="pos-input" required minlength="8" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Confirm New Password</label>
          <input v-model="pwForm.new_password_confirmation" type="password" class="pos-input" required />
        </div>

        <p v-if="pwError" class="text-red-500 text-sm">{{ pwError }}</p>
        <p v-if="pwSuccess" class="text-green-600 text-sm">{{ pwSuccess }}</p>

        <div class="flex gap-2 pt-2">
          <button type="submit" class="btn-primary flex-1" :disabled="pwLoading">
            {{ pwLoading ? 'Saving...' : 'Save' }}
          </button>
          <button type="button" class="btn-secondary flex-1" @click="closePasswordModal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/store/modules/authStore'
import { useToast } from '@/composables/useToast'
import userService from '@/services/userService'

const authStore     = useAuthStore()
const router        = useRouter()
const { success: toastSuccess, error: toastError } = useToast()
const user          = computed(() => authStore.user)
const confirmLogout = ref(false)
const loggingOut    = ref(false)

// Password change state
const showPasswordModal = ref(false)
const pwLoading         = ref(false)
const pwError           = ref('')
const pwSuccess         = ref('')
const pwForm = reactive({
  current_password:      '',
  new_password:          '',
  new_password_confirmation: '',
})

function closePasswordModal() {
  showPasswordModal.value = false
  pwError.value           = ''
  pwSuccess.value         = ''
  Object.assign(pwForm, { current_password: '', new_password: '', new_password_confirmation: '' })
}

// I-submit ang password change
async function submitPasswordChange() {
  pwError.value   = ''
  pwSuccess.value = ''

  // I-check kung match ang bag-ong password
  if (pwForm.new_password !== pwForm.new_password_confirmation) {
    pwError.value = 'New passwords do not match.'
    return
  }

  pwLoading.value = true
  try {
    await userService.changePassword({ ...pwForm })
    toastSuccess('Password changed. Please log in again.')
    closePasswordModal()
    // I-clear ang session ug i-redirect sa login — tanan sessions gi-delete sa backend
    await authStore.logout()
    router.push({ name: 'login' })
  } catch (e) {
    pwError.value = e.response?.data?.message ?? 'Failed to change password.'
    toastError(pwError.value)
  } finally {
    pwLoading.value = false
  }
}

// Kulor sa role badge base sa role
const roleBadgeClass = computed(() => ({
  admin:   'bg-purple-500 text-white',
  manager: 'bg-blue-500 text-white',
  cashier: 'bg-green-600 text-white',
}[user.value?.role] ?? 'bg-gray-600 text-white'))

// Nav items — ipakita base sa role
const navLinks = computed(() => {
  const role  = user.value?.role
  const main = [
    { name: 'dashboard', to: '/',          icon: '📊', label: 'Dashboard' },
    { name: 'orders',    to: '/orders',    icon: '🧾', label: 'Orders'   },
    { name: 'products',  to: '/products',  icon: '🍔', label: 'Menu'     },
    { name: 'tables',    to: '/tables',    icon: '🪑', label: 'Tables'   },
  ]

  if (['manager', 'admin'].includes(role)) {
    main.push({ name: 'reports',    to: '/reports',    icon: '📈', label: 'Reports'    })
    main.push({ name: 'categories', to: '/categories', icon: '🗂️', label: 'Categories' })
  }

  if (role === 'admin') {
    main.push({ name: 'users', to: '/users', icon: '👥', label: 'Users' })
  }

  return main
})

// Settings nav links — manager/admin lang
const settingsLinks = computed(() => {
  const role = user.value?.role
  if (!['manager', 'admin'].includes(role)) return []
  return [
    { name: 'archive', to: '/settings/archive', icon: '🗂️', label: 'Archive' },
  ]
})

// Mag-logout dayon i-redirect sa login
async function handleLogout() {
  loggingOut.value = true
  try {
    await authStore.logout()
    router.push({ name: 'login' })
  } finally {
    loggingOut.value    = false
    confirmLogout.value = false
  }
}
</script>
