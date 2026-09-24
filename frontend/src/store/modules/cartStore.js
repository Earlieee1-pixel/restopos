import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

// Store sa cart — mga gi-add nga pagkaon sa order
export const useCartStore = defineStore('cart', () => {
  const items           = ref([])
  const discount        = ref(0)
  const amountTendered  = ref(0)
  const tableId         = ref(null)
  const orderType       = ref('dine-in')
  const notes           = ref('')

  // Total subtotal sa tanan items
  const subtotal = computed(() =>
    items.value.reduce((sum, item) => sum + item.unit_price * item.quantity, 0)
  )

  const total = computed(() => Math.max(0, subtotal.value - discount.value))

  // Sukli sa customer
  const change = computed(() => Math.max(0, amountTendered.value - total.value))

  // I-add ang produkto sa cart
  function addItem(product) {
    const existing = items.value.find((i) => i.product_id === product.id)
    if (existing) {
      // Kung naa na, dagdagan lang ang qty
      existing.quantity++
    } else {
      items.value.push({
        product_id: product.id,
        name:       product.name,
        unit_price: product.price,
        quantity:   1,
        notes:      '',
      })
    }
  }

  // Kuha-an ug usa ka qty
  function removeOne(productId) {
    const item = items.value.find((i) => i.product_id === productId)
    if (!item) return
    if (item.quantity > 1) {
      item.quantity--
    } else {
      // Kung 1 na lang, tangtangon na
      items.value = items.value.filter((i) => i.product_id !== productId)
    }
  }

  // Tangtangon ang item sa cart
  function removeItem(productId) {
    items.value = items.value.filter((i) => i.product_id !== productId)
  }

  // Limpyohan ang cart pagkahuman ug order
  function clearCart() {
    items.value          = []
    discount.value       = 0
    amountTendered.value = 0
    tableId.value        = null
    orderType.value      = 'dine-in'
    notes.value          = ''
  }

  return {
    items,
    discount,
    amountTendered,
    tableId,
    orderType,
    notes,
    subtotal,
    total,
    change,
    addItem,
    removeOne,
    removeItem,
    clearCart,
  }
})
