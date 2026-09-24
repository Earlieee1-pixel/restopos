import api from './api'

// Tanan API calls para sa user management (admin only)
const userService = {
  // Kuha sa tanan users
  async getAll() {
    return api.get('/users')
  },

  // Bag-ong user account
  async create(payload) {
    return api.post('/users', payload)
  },

  // I-update ang user (role, is_active, etc.)
  async update(id, payload) {
    return api.put(`/users/${id}`, payload)
  },

  // I-toggle ang is_active sa user
  async toggleActive(id) {
    return api.patch(`/users/${id}/toggle-active`)
  },

  // I-change ang password sa current user
  async changePassword(payload) {
    return api.patch('/profile/password', payload)
  },
}

export default userService
