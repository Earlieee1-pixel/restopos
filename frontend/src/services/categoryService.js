import api from './api'

// Tanan API calls para sa mga kategorya
const categoryService = {
  // Kuha sa tanan kategorya
  async getAll() {
    return api.get('/categories')
  },

  // Bag-ong kategorya
  async create(payload) {
    return api.post('/categories', payload)
  },

  // I-update ang kategorya
  async update(id, payload) {
    return api.put(`/categories/${id}`, payload)
  },

  // Tangtangon ang kategorya
  async remove(id) {
    return api.delete(`/categories/${id}`)
  },
}

export default categoryService
