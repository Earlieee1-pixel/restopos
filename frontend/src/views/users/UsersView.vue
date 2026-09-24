<template>
  <!-- User management — admin only -->
  <div class="p-6 overflow-auto flex-1">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">User Management</h1>
      <button class="btn-primary" @click="openCreate">+ New User</button>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="text-center text-gray-400 py-12">Loading users...</div>

    <!-- Users table -->
    <div v-else class="pos-card overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="border-b">
          <tr class="text-left text-gray-500">
            <th class="pb-3 pr-4">Name</th>
            <th class="pb-3 pr-4">Email</th>
            <th class="pb-3 pr-4">Role</th>
            <th class="pb-3 pr-4">Status</th>
            <th class="pb-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="user in users" :key="user.id">
            <td class="py-3 pr-4 font-medium">
              {{ user.name }}
              <span v-if="user.id === authStore.user?.id" class="ml-1 text-xs text-gray-400">(you)</span>
            </td>
            <td class="py-3 pr-4 text-gray-500">{{ user.email }}</td>
            <td class="py-3 pr-4">
              <span
                class="text-xs px-2 py-0.5 rounded-full font-semibold"
                :class="roleBadge(user.role)"
              >{{ user.role }}</span>
            </td>
            <td class="py-3 pr-4">
              <!-- Toggle active status -->
              <button
                class="text-xs px-2 py-1 rounded-full font-medium"
                :class="user.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                :disabled="user.id === authStore.user?.id"
                @click="handleToggleActive(user)"
              >
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </button>
            </td>
            <td class="py-3 space-x-2">
              <button class="text-blue-600 hover:underline text-xs" @click="openEdit(user)">Edit</button>
            </td>
          </tr>
          <tr v-if="!users.length">
            <td colspan="5" class="py-8 text-center text-gray-400">No users found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
        <h2 class="text-lg font-bold mb-4">{{ editingUser ? 'Edit User' : 'New User' }}</h2>

        <form @submit.prevent="submitUser" class="space-y-3">
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input v-model="form.name" type="text" class="pos-input" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input v-model="form.email" type="email" class="pos-input" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">
              Password {{ editingUser ? '(leave blank to keep current)' : '' }}
            </label>
            <input v-model="form.password" type="password" class="pos-input" :required="!editingUser" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Role</label>
            <select v-model="form.role" class="pos-input" required>
              <option value="cashier">Cashier</option>
              <option value="manager">Manager</option>
              <option value="admin">Admin</option>
            </select>
          </div>

          <p v-if="formError" class="text-red-500 text-sm">{{ formError }}</p>

          <div class="flex gap-2 pt-2">
            <button type="submit" class="btn-primary flex-1" :disabled="formLoading">
              {{ formLoading ? 'Saving...' : 'Save' }}
            </button>
            <button type="button" class="btn-secondary flex-1" @click="closeModal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/store/modules/authStore'
import { useToast } from '@/composables/useToast'
import userService from '@/services/userService'

const authStore = useAuthStore()
const { success, error: toastError } = useToast()

const users      = ref([])
const loading    = ref(false)
const showModal  = ref(false)
const editingUser = ref(null)
const formLoading = ref(false)
const formError   = ref('')

const form = reactive({ name: '', email: '', password: '', role: 'cashier' })

onMounted(() => fetchUsers())

// I-load ang tanan users
async function fetchUsers() {
  loading.value = true
  try {
    const { data } = await userService.getAll()
    users.value = data
  } finally {
    loading.value = false
  }
}

// Kulor sa role badge
function roleBadge(role) {
  return {
    admin:   'bg-purple-100 text-purple-700',
    manager: 'bg-blue-100 text-blue-700',
    cashier: 'bg-green-100 text-green-700',
  }[role] ?? 'bg-gray-100 text-gray-600'
}

function openCreate() {
  editingUser.value = null
  Object.assign(form, { name: '', email: '', password: '', role: 'cashier' })
  formError.value = ''
  showModal.value = true
}

function openEdit(user) {
  editingUser.value = user
  Object.assign(form, { name: user.name, email: user.email, password: '', role: user.role })
  formError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value   = false
  editingUser.value = null
}

// I-submit ang form (create o update)
async function submitUser() {
  formError.value   = ''
  formLoading.value = true
  try {
    const payload = { ...form }
    // Kung nag-edit ug walay password, tangtangon sa payload
    if (editingUser.value && !payload.password) delete payload.password

    if (editingUser.value) {
      const { data } = await userService.update(editingUser.value.id, payload)
      const idx = users.value.findIndex((u) => u.id === editingUser.value.id)
      if (idx !== -1) users.value[idx] = data
      success('User updated.')
    } else {
      const { data } = await userService.create(payload)
      users.value.push(data)
      success('User created.')
    }
    closeModal()
  } catch (e) {
    formError.value = e.response?.data?.message ?? 'Failed to save user.'
    toastError(formError.value)
  } finally {
    formLoading.value = false
  }
}

// I-toggle ang active status sa user
async function handleToggleActive(user) {
  try {
    const { data } = await userService.toggleActive(user.id)
    const idx = users.value.findIndex((u) => u.id === user.id)
    if (idx !== -1) users.value[idx] = data
    success(`${data.name} is now ${data.is_active ? 'active' : 'inactive'}.`)
  } catch (e) {
    toastError(e.response?.data?.message ?? 'Failed to update user.')
  }
}
</script>
