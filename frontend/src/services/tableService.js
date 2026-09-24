import api from './api'

// Tanan API calls para sa mga mesa
const tableService = {
  // Kuha sa tanan mesa ug ilang status
  async getAll() {
    return api.get('/tables')
  },

  // Buhatan ug bag-ong mesa
  async create(payload) {
    return api.post('/tables', payload)
  },

  // I-update ang status sa mesa
  async updateStatus(id, status) {
    return api.patch(`/tables/${id}/status`, { status })
  },
}

export default tableService
