import api from './api'

// Tanan API calls para sa mga order
const orderService = {
  // Kuha sa tanan aktibong order
  async getActiveOrders() {
    return api.get('/orders')
  },

  // Kuha sa usa ka order pinaagi sa ID
  async getOrder(id) {
    return api.get(`/orders/${id}`)
  },

  // Buhatan ug bag-ong order
  async createOrder(payload) {
    return api.post('/orders', payload)
  },

  // I-update ang status sa order, optional extra data (e.g. amount_tendered)
  async updateStatus(id, status, extra = {}) {
    return api.patch(`/orders/${id}/status/${status}`, extra)
  },

  // I-cancel ang order
  async cancelOrder(id) {
    return api.patch(`/orders/${id}/cancel`)
  },

  // Kuha sa order history (served + cancelled), optional date filter
  async getHistory(date = null, page = 1) {
    return api.get('/orders/history', { params: { date, page } })
  },
}

export default orderService
