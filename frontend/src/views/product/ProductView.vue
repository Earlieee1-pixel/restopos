<template>
  <!-- Ari ang management sa mga produkto sa menu -->
  <div class="p-6 overflow-auto flex-1">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Menu Management</h1>
      <button v-if="canManage" class="btn-primary" @click="openCreate">+ New Product</button>
    </div>

    <!-- Loading state -->
    <div v-if="productStore.loading" class="text-center text-gray-400 py-12">Loading products...</div>

    <!-- Error state -->
    <div v-else-if="productStore.error" class="text-center text-red-400 py-12">
      ⚠️ {{ productStore.error }}
      <br />
      <button class="btn-secondary mt-4 text-sm" @click="productStore.fetchProducts(true)">Retry</button>
    </div>

    <!-- Search + Products table -->
    <div v-else class="pos-card overflow-x-auto">
      <!-- Search bar -->
      <div class="mb-4">
        <input
          v-model="search"
          type="text"
          placeholder="Search products..."
          class="pos-input max-w-xs"
        />
      </div>

      <table class="w-full text-sm">
        <thead class="border-b">
          <tr class="text-left text-gray-500">
            <th class="pb-3 pr-2 w-12"></th>
            <th class="pb-3 pr-4">Name</th>            <th class="pb-3 pr-4">Category</th>
            <th class="pb-3 pr-4">Price</th>
            <th class="pb-3 pr-4">Status</th>
            <th v-if="canManage" class="pb-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="product in filteredProducts" :key="product.id">
            <td class="py-3 pr-4">
              <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                <img v-if="product.image" :src="imgUrl(product.image)" class="w-full h-full object-cover" :alt="product.name" />
                <span v-else class="text-lg">🍽️</span>
              </div>
            </td>
            <td class="py-3 pr-4 font-medium">{{ product.name }}</td>
            <td class="py-3 pr-4 text-gray-500">{{ product.category?.name }}</td>
            <td class="py-3 pr-4">₱{{ product.price }}</td>
            <td class="py-3 pr-4">
              <!-- Toggle availability with loading state -->
              <button
                class="text-xs px-2 py-1 rounded-full font-medium disabled:opacity-50"
                :class="product.is_available ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                :disabled="togglingId === product.id"
                @click="handleToggle(product)"
              >
                {{ togglingId === product.id ? '...' : product.is_available ? 'Available' : 'Unavailable' }}
              </button>
            </td>
            <td v-if="canManage" class="py-3 space-x-2">
              <button class="text-blue-600 hover:underline text-xs" @click="openEdit(product)">Edit</button>
              <button class="text-red-500 hover:underline text-xs" @click="confirmDelete(product)">Delete</button>
            </td>
          </tr>
          <tr v-if="!filteredProducts.length">
            <td colspan="6" class="py-8 text-center text-gray-400">
              {{ search ? 'No products match your search.' : 'No products found.' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Product modal (create + edit) -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
        <h2 class="text-lg font-bold mb-4">{{ editingProduct ? 'Edit Product' : 'New Product' }}</h2>

        <form @submit.prevent="submitProduct" class="space-y-3">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input v-model="form.name" type="text" class="pos-input" required />
          </div>

          <!-- Category -->
          <div>
            <label class="block text-sm font-medium mb-1">Category</label>
            <select v-model="form.category_id" class="pos-input" required>
              <option value="">— Select category —</option>
              <option v-for="cat in productStore.categories" :key="cat.id" :value="cat.id">
                {{ cat.icon }} {{ cat.name }}
              </option>
            </select>
          </div>

          <!-- Price -->
          <div>
            <label class="block text-sm font-medium mb-1">Price (₱)</label>
            <input v-model.number="form.price" type="number" min="0" step="0.01" class="pos-input" required />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea v-model="form.description" class="pos-input" rows="2" />
          </div>

          <!-- Image upload — available sa create ug edit -->
          <div>
            <label class="block text-sm font-medium mb-1">Product Image</label>
            <div class="flex items-center gap-3">
              <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                <img
                  v-if="imagePreview || editingProduct?.image"
                  :src="imagePreview || imgUrl(editingProduct?.image)"
                  class="w-full h-full object-cover"
                  alt="Preview"
                />
                <span v-else class="text-2xl">🍽️</span>
              </div>
              <div class="flex-1">
                <input
                  type="file"
                  accept="image/jpg,image/jpeg,image/png,image/webp"
                  class="text-xs text-gray-600 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 w-full"
                  @change="handleImageSelect"
                />
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — max 2MB</p>
              </div>
            </div>
            <p v-if="imageError" class="text-red-500 text-xs mt-1">{{ imageError }}</p>
          </div>

          <!-- Availability -->
          <div class="flex items-center gap-2">
            <input v-model="form.is_available" type="checkbox" id="is_available" class="w-4 h-4" />
            <label for="is_available" class="text-sm">Available on menu</label>
          </div>

          <!-- Error -->
          <p v-if="formError" class="text-red-500 text-sm">{{ formError }}</p>

          <!-- Actions -->
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
    <div v-if="deletingProduct" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4 text-center">
        <div class="text-4xl mb-3">⚠️</div>
        <h2 class="text-lg font-bold mb-2">Delete Product?</h2>
        <p class="text-sm text-gray-500 mb-4">
          "{{ deletingProduct.name }}" will be removed from the menu. This cannot be undone.
        </p>
        <p v-if="deleteError" class="text-red-500 text-sm mb-3">{{ deleteError }}</p>
        <div class="flex gap-2">
          <button class="btn-primary flex-1 bg-red-600 hover:bg-red-700" :disabled="deleteLoading" @click="doDelete">
            {{ deleteLoading ? 'Deleting...' : 'Delete' }}
          </button>
          <button class="btn-secondary flex-1" @click="deletingProduct = null">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useProductStore } from '@/store/modules/productStore'
import { useAuthStore } from '@/store/modules/authStore'
import { useToast } from '@/composables/useToast'
import { useImgUrl } from '@/composables/useImgUrl'

const productStore = useProductStore()
const authStore    = useAuthStore()
const { success, error: toastError } = useToast()
const { imgUrl } = useImgUrl()

// Manager ug admin lang pwede mag-edit/delete
const canManage = computed(() =>
  ['manager', 'admin'].includes(authStore.user?.role)
)

// Search filter
const search = ref('')
const filteredProducts = computed(() => {
  if (!search.value.trim()) return productStore.products
  const q = search.value.toLowerCase()
  return productStore.products.filter(
    (p) => p.name.toLowerCase().includes(q) ||
           p.category?.name.toLowerCase().includes(q)
  )
})

// Modal state
const showModal      = ref(false)
const editingProduct = ref(null)
const formLoading    = ref(false)
const formError      = ref('')

// Image state
const imageFile    = ref(null)
const imagePreview = ref('')
const imageError   = ref('')

// Toggle loading state — para mapugong ang double-click
const togglingId = ref(null)

async function handleToggle(product) {
  if (togglingId.value) return
  togglingId.value = product.id
  try {
    await productStore.toggleAvailability(product.id)
  } catch (e) {
    toastError(e.response?.data?.message ?? 'Failed to update availability.')
  } finally {
    togglingId.value = null
  }
}

// Delete state
const deletingProduct = ref(null)
const deleteLoading   = ref(false)
const deleteError     = ref('')

// Form data
const form = reactive({
  name:         '',
  category_id:  '',
  price:        '',
  description:  '',
  is_available: true,
})

onMounted(() => productStore.fetchProducts(true))

function openCreate() {
  editingProduct.value = null
  Object.assign(form, { name: '', category_id: '', price: '', description: '', is_available: true })
  formError.value  = ''
  imageFile.value  = null
  imagePreview.value = ''
  imageError.value = ''
  showModal.value  = true
}

// I-open ang modal para sa pag-edit
function openEdit(product) {
  editingProduct.value = product
  Object.assign(form, {
    name:         product.name,
    category_id:  product.category_id,
    price:        product.price,
    description:  product.description ?? '',
    is_available: product.is_available,
  })
  formError.value    = ''
  imageFile.value    = null
  imagePreview.value = ''
  imageError.value   = ''
  showModal.value    = true
}

function closeModal() {
  showModal.value      = false
  editingProduct.value = null
  imageFile.value      = null
  imagePreview.value   = ''
  imageError.value     = ''
}

// I-handle ang image file selection
function handleImageSelect(event) {
  const file = event.target.files[0]
  if (!file) return

  // I-validate ang size — max 2MB
  if (file.size > 2 * 1024 * 1024) {
    imageError.value = 'Image must be under 2MB.'
    return
  }

  imageError.value = ''
  imageFile.value  = file

  // I-show ang preview
  const reader = new FileReader()
  reader.onload = (e) => { imagePreview.value = e.target.result }
  reader.readAsDataURL(file)
}

// I-submit ang form (create o update)
async function submitProduct() {
  formError.value   = ''
  formLoading.value = true
  try {
    let saved
    if (editingProduct.value) {
      saved = await productStore.updateProduct(editingProduct.value.id, { ...form })
    } else {
      saved = await productStore.createProduct({ ...form })
    }

    // I-upload ang image kung naa
    if (imageFile.value && saved?.id) {
      await productStore.uploadImage(saved.id, imageFile.value)
    }

    success(editingProduct.value ? 'Product updated.' : 'Product added to menu.')
    closeModal()
  } catch (e) {
    formError.value = e.response?.data?.message ?? 'Failed to save product.'
    toastError(formError.value)
  } finally {
    formLoading.value = false
  }
}

// I-confirm ang pag-delete
function confirmDelete(product) {
  deletingProduct.value = product
  deleteError.value     = ''
}

// I-execute ang delete
async function doDelete() {
  deleteLoading.value = true
  deleteError.value   = ''
  try {
    await productStore.deleteProduct(deletingProduct.value.id)
    success('Product deleted.')
    deletingProduct.value = null
  } catch (e) {
    deleteError.value = e.response?.data?.message ?? 'Failed to delete product.'
    toastError(deleteError.value)
  } finally {
    deleteLoading.value = false
  }
}
</script>
