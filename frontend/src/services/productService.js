import api from './api'

// Tanan API calls para sa mga produkto/menu
const productService = {
  // Kuha sa tanan produkto
  async getAll() {
    return api.get('/products')
  },

  // Bag-ong produkto
  async create(payload) {
    return api.post('/products', payload)
  },

  // I-edit ang produkto
  async update(id, payload) {
    return api.put(`/products/${id}`, payload)
  },

  // Tangtangon ang produkto (soft delete)
  async remove(id) {
    return api.delete(`/products/${id}`)
  },

  // I-toggle ang availability (available/unavailable)
  async toggleAvailability(id) {
    return api.patch(`/products/${id}/availability`)
  },

  // I-upload ang product image
  async uploadImage(id, file) {
    const formData = new FormData()
    formData.append('image', file)
    return api.post(`/products/${id}/image`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },

  // Kuha sa tanan soft-deleted nga produkto
  async getTrashed() {
    return api.get('/products/trashed')
  },

  // I-restore ang produkto
  async restore(id) {
    return api.patch(`/products/${id}/restore`)
  },

  // Permanenteng tangtangon
  async forceDelete(id) {
    return api.delete(`/products/${id}/force`)
  },
}

export default productService
