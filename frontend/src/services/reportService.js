import api from './api'

// Tanan API calls para sa mga report
const reportService = {
  // Sales karong adlaw
  async getDailySales(date) {
    const { data } = await api.get('/reports/daily', { params: { date } })
    return data
  },

  // Sales sa usa ka bulan
  async getMonthlySales(month) {
    const { data } = await api.get('/reports/monthly', { params: { month } })
    return data
  },

  // Top-selling nga produkto, optional date filter
  async getTopProducts(limit = 10, date = null) {
    const { data } = await api.get('/reports/top-products', { params: { limit, date } })
    return data
  },
}

export default reportService
