import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import productService from '@/services/productService'
import categoryService from '@/services/categoryService'

// Store para sa mga produkto/menu
export const useProductStore = defineStore('product', () => {
  const products         = ref([])
  const categories       = ref([])
  const selectedCategory = ref(null)
  const loading          = ref(false)
  const error            = ref(null)

  // Filter products base sa category
  const filteredProducts = computed(() => {
    if (!selectedCategory.value) return products.value
    return products.value.filter((p) => p.category_id === selectedCategory.value)
  })

  // I-load ang tanan produkto ug kategorya — skip kung naa na
  async function fetchProducts(force = false) {
    if (!force && products.value.length > 0) return
    loading.value = true
    error.value   = null
    try {
      const [{ data: prods }, { data: cats }] = await Promise.all([
        productService.getAll(),
        categoryService.getAll(),
      ])
      products.value   = Array.isArray(prods) ? prods : []
      categories.value = Array.isArray(cats) ? cats : []
    } catch (e) {
      error.value = e.response?.data?.message ?? e.message ?? 'Failed to load products.'
    } finally {
      loading.value = false
    }
  }

  // I-toggle availability sa menu
  async function toggleAvailability(id) {
    const { data: updated } = await productService.toggleAvailability(id)
    const idx = products.value.findIndex((p) => p.id === id)
    if (idx !== -1) products.value[idx] = updated
  }

  // Bag-ong produkto — i-add sa listahan
  async function createProduct(payload) {
    const { data: created } = await productService.create(payload)
    products.value.push(created)
    return created
  }

  // I-update ang produkto sa listahan
  async function updateProduct(id, payload) {
    const { data: updated } = await productService.update(id, payload)
    const idx = products.value.findIndex((p) => p.id === id)
    if (idx !== -1) products.value[idx] = updated
    return updated
  }

  // Tangtangon ang produkto sa listahan
  async function deleteProduct(id) {
    await productService.remove(id)
    products.value = products.value.filter((p) => p.id !== id)
  }

  // I-upload ang product image
  async function uploadImage(id, file) {
    const { data: updated } = await productService.uploadImage(id, file)
    const idx = products.value.findIndex((p) => p.id === id)
    if (idx !== -1) products.value[idx] = updated
    return updated
  }

  return {
    products,
    categories,
    selectedCategory,
    filteredProducts,
    loading,
    error,
    fetchProducts,
    toggleAvailability,
    createProduct,
    updateProduct,
    deleteProduct,
    uploadImage,
  }
})
