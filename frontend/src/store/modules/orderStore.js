import { defineStore } from 'pinia'
import { ref } from 'vue'
import orderService from '@/services/orderService'

// Store para sa mga order
export const useOrderStore = defineStore('order', () => {
  const orders  = ref([])
  const loading = ref(false)

  // I-load ang tanan aktibong order
  async function fetchOrders() {
    loading.value = true
    try {
      const { data } = await orderService.getActiveOrders()
      orders.value = data
    } finally {
      loading.value = false
    }
  }

  // Bag-ong order — i-add sa listahan
  async function placeOrder(payload) {
    const { data } = await orderService.createOrder(payload)
    orders.value.unshift(data)
    return data
  }

  // I-update ang status sa usa ka order — i-refresh ang listahan after
  async function updateOrderStatus(id, status, extra = {}) {
    await orderService.updateStatus(id, status, extra)
    // I-refresh para makuha ang pinakabag-o nga estado
    await fetchOrders()
  }

  // I-cancel ang order — tangtangon sa listahan
  async function cancelOrder(id) {
    await orderService.cancelOrder(id)
    orders.value = orders.value.filter((o) => o.id !== id)
  }

  return { orders, loading, fetchOrders, placeOrder, updateOrderStatus, cancelOrder }
})
