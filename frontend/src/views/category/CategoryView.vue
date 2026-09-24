<template>
  <!-- Ari ang management sa mga kategorya sa menu -->
  <div class="p-6 overflow-auto flex-1">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Categories</h1>
      <button class="btn-primary" @click="openCreate">+ New Category</button>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="text-center text-gray-400 py-12">Loading categories...</div>

    <!-- Categories table -->
    <div v-else class="pos-card overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="border-b">
          <tr class="text-left text-gray-500">
            <th class="pb-3 pr-4">Icon</th>
            <th class="pb-3 pr-4">Name</th>
            <th class="pb-3 pr-4">Sort Order</th>
            <th class="pb-3 pr-4">Products</th>
            <th class="pb-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="cat in categories" :key="cat.id">
            <td class="py-3 pr-4 text-xl">{{ cat.icon ?? '📦' }}</td>
            <td class="py-3 pr-4 font-medium">{{ cat.name }}</td>
            <td class="py-3 pr-4 text-gray-500">{{ cat.sort_order }}</td>
            <td class="py-3 pr-4 text-gray-500">{{ cat.products_count ?? '—' }}</td>
            <td class="py-3 space-x-2">
              <button class="text-blue-600 hover:underline text-xs" @click="openEdit(cat)">Edit</button>
              <button class="text-red-500 hover:underline text-xs" @click="confirmDelete(cat)">Delete</button>
            </td>
          </tr>
          <tr v-if="!categories.length">
            <td colspan="5" class="py-8 text-center text-gray-400">No categories found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4">
        <h2 class="text-lg font-bold mb-4">{{ editingCat ? 'Edit Category' : 'New Category' }}</h2>

        <form @submit.prevent="submitCategory" class="space-y-3">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input v-model="form.name" type="text" class="pos-input" required />
          </div>

          <!-- Icon -->
          <div>
            <label class="block text-sm font-medium mb-1">Icon (emoji)</label>
            <input v-model="form.icon" type="text" class="pos-input" placeholder="e.g. 🍔" maxlength="4" />
          </div>

          <!-- Sort order -->
          <div>
            <label class="block text-sm font-medium mb-1">Sort Order</label>
            <input v-model.number="form.sort_order" type="number" min="0" class="pos-input" placeholder="0" />
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

    <!-- Delete confirmation modal -->
    <div v-if="deletingCat" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4 text-center">
        <div class="text-4xl mb-3">⚠️</div>
        <h2 class="text-lg font-bold mb-2">Delete Category?</h2>
        <p class="text-sm text-gray-500 mb-4">
          "{{ deletingCat.name }}" will be permanently deleted. Categories with existing products cannot be deleted.
        </p>
        <p v-if="deleteError" class="text-red-500 text-sm mb-3">{{ deleteError }}</p>
        <div class="flex gap-2">
          <button
            class="btn-primary flex-1 bg-red-600 hover:bg-red-700"
            :disabled="deleteLoading"
            @click="doDelete"
          >
            {{ deleteLoading ? 'Deleting...' : 'Delete' }}
          </button>
          <button class="btn-secondary flex-1" @click="deletingCat = null">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import categoryService from '@/services/categoryService'

const { success, error: toastError } = useToast()

const categories  = ref([])
const loading     = ref(false)
const showModal   = ref(false)
const editingCat  = ref(null)
const formLoading = ref(false)
const formError   = ref('')

const deletingCat   = ref(null)
const deleteLoading = ref(false)
const deleteError   = ref('')

const form = reactive({ name: '', icon: '', sort_order: 0 })

onMounted(() => fetchCategories())

// I-load ang tanan kategorya with product count
async function fetchCategories() {
  loading.value = true
  try {
    const { data } = await categoryService.getAll()
    categories.value = data
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingCat.value = null
  Object.assign(form, { name: '', icon: '', sort_order: categories.value.length + 1 })
  formError.value = ''
  showModal.value = true
}

function openEdit(cat) {
  editingCat.value = cat
  Object.assign(form, { name: cat.name, icon: cat.icon ?? '', sort_order: cat.sort_order })
  formError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value  = false
  editingCat.value = null
}

// I-submit ang form (create o update)
async function submitCategory() {
  formError.value   = ''
  formLoading.value = true
  try {
    if (editingCat.value) {
      const { data } = await categoryService.update(editingCat.value.id, { ...form })
      const idx = categories.value.findIndex((c) => c.id === editingCat.value.id)
      if (idx !== -1) categories.value[idx] = data
      success('Category updated.')
    } else {
      const { data } = await categoryService.create({ ...form })
      categories.value.push(data)
      success('Category created.')
    }
    closeModal()
  } catch (e) {
    formError.value = e.response?.data?.message ?? 'Failed to save category.'
    toastError(formError.value)
  } finally {
    formLoading.value = false
  }
}

function confirmDelete(cat) {
  deletingCat.value = cat
  deleteError.value = ''
}

// I-execute ang delete
async function doDelete() {
  deleteLoading.value = true
  deleteError.value   = ''
  try {
    await categoryService.remove(deletingCat.value.id)
    categories.value = categories.value.filter((c) => c.id !== deletingCat.value.id)
    success('Category deleted.')
    deletingCat.value = null
  } catch (e) {
    deleteError.value = e.response?.data?.message ?? 'Failed to delete category.'
    toastError(deleteError.value)
  } finally {
    deleteLoading.value = false
  }
}
</script>
