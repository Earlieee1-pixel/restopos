<template>
  <!-- Ari ang archive sa soft-deleted nga mga produkto -->
  <div class="p-6 overflow-auto flex-1">
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Product Archive</h1>
      <p class="text-sm text-gray-500 mt-1">Deleted products. Restore to bring them back or permanently delete them.</p>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="text-center text-gray-400 py-12">Loading archive...</div>

    <!-- Empty state -->
    <div v-else-if="!products.length" class="pos-card text-center py-12 text-gray-400">
      <div class="text-4xl mb-3">🗂️</div>
      <p class="text-sm">No archived products.</p>
    </div>

    <!-- Products table -->
    <div v-else class="pos-card overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="border-b">
          <tr class="text-left text-gray-500">
            <th class="pb-3 pr-2 w-12"></th>
            <th class="pb-3 pr-4">Name</th>
            <th class="pb-3 pr-4">Category</th>
            <th class="pb-3 pr-4">Price</th>
            <th class="pb-3 pr-4">Deleted</th>
            <th class="pb-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="product in products" :key="product.id" class="opacity-75">
            <!-- Image thumbnail -->
            <td class="py-3 pr-4">
              <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                <img v-if="product.image" :src="imgUrl(product.image)" class="w-full h-full object-cover" :alt="product.name" />
                <span v-else class="text-lg">🍽️</span>
              </div>
            </td>
            <td class="py-3 pr-4 font-medium text-gray-500 line-through">{{ product.name }}</td>
            <td class="py-3 pr-4 text-gray-400">{{ product.category?.name ?? '—' }}</td>
            <td class="py-3 pr-4 text-gray-400">₱{{ product.price }}</td>
            <td class="py-3 pr-4 text-gray-400 text-xs">{{ formatDate(product.deleted_at) }}</td>
            <td class="py-3 space-x-2">
              <button
                class="text-green-600 hover:underline text-xs font-medium"
                @click="handleRestore(product)"
              >Restore</button>
              <button
                v-if="isAdmin"
                class="text-red-500 hover:underline text-xs"
                @click="confirmForceDelete(product)"
              >Delete Permanently</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Force delete confirmation -->
    <div v-if="deletingProduct" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4 text-center">
        <div class="text-4xl mb-3">⚠️</div>
        <h2 class="text-lg font-bold mb-2">Permanently Delete?</h2>
        <p class="text-sm text-gray-500 mb-1">
          "{{ deletingProduct.name }}" will be gone forever.
        </p>
        <p class="text-xs text-red-400 mb-4">This cannot be undone.</p>
        <p v-if="deleteError" class="text-red-500 text-sm mb-3">{{ deleteError }}</p>
        <div class="flex gap-2">
          <button
            class="btn-primary flex-1 bg-red-600 hover:bg-red-700"
            :disabled="deleteLoading"
            @click="doForceDelete"
          >
            {{ deleteLoading ? 'Deleting...' : 'Yes, delete forever' }}
          </button>
          <button class="btn-secondary flex-1" @click="deletingProduct = null">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/store/modules/authStore'
import { useToast } from '@/composables/useToast'
import { useImgUrl } from '@/composables/useImgUrl'
import productService from '@/services/productService'
import { formatDateTime } from '@/utils/date'

const authStore = useAuthStore()
const { success, error: toastError } = useToast()
const { imgUrl } = useImgUrl()

// Admin lang pwede mag-permanent delete
const isAdmin = computed(() => authStore.user?.role === 'admin')

const products      = ref([])
const loading       = ref(false)
const deletingProduct = ref(null)
const deleteLoading   = ref(false)
const deleteError     = ref('')

function formatDate(dateStr) {
  return dateStr ? formatDateTime(dateStr) : '—'
}

onMounted(() => fetchTrashed())

// I-load ang tanan archived products
async function fetchTrashed() {
  loading.value = true
  try {
    const { data } = await productService.getTrashed()
    products.value = data
  } finally {
    loading.value = false
  }
}

// I-restore ang produkto
async function handleRestore(product) {
  try {
    await productService.restore(product.id)
    products.value = products.value.filter((p) => p.id !== product.id)
    success(`"${product.name}" restored to menu.`)
  } catch (e) {
    toastError(e.response?.data?.message ?? 'Failed to restore product.')
  }
}

function confirmForceDelete(product) {
  deletingProduct.value = product
  deleteError.value     = ''
}

// Permanenteng tangtangon ang produkto
async function doForceDelete() {
  deleteLoading.value = true
  deleteError.value   = ''
  try {
    await productService.forceDelete(deletingProduct.value.id)
    products.value = products.value.filter((p) => p.id !== deletingProduct.value.id)
    success(`"${deletingProduct.value.name}" permanently deleted.`)
    deletingProduct.value = null
  } catch (e) {
    deleteError.value = e.response?.data?.message ?? 'Failed to delete product.'
    toastError(deleteError.value)
  } finally {
    deleteLoading.value = false
  }
}
</script>
