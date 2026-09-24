<template>
  <!-- Cart panel — naa sa tuo sa order screen -->
  <div class="flex flex-col h-full">

    <!-- Header -->
    <div class="p-4 border-b font-bold text-lg">🧾 Order</div>

    <!-- Cart items -->
    <div class="flex-1 overflow-auto p-4 space-y-2">
      <div
        v-for="item in cartStore.items"
        :key="item.product_id"
        class="bg-gray-50 rounded-lg p-2 space-y-1"
      >
        <div class="flex items-center gap-2">
          <div class="flex-1 text-sm font-medium leading-tight">{{ item.name }}</div>

          <!-- Qty controls -->
          <div class="flex items-center gap-1">
            <button
              class="w-6 h-6 rounded bg-gray-200 text-sm font-bold hover:bg-gray-300"
              @click="cartStore.removeOne(item.product_id)"
            >-</button>
            <span class="w-6 text-center text-sm font-bold">{{ item.quantity }}</span>
            <button
              class="w-6 h-6 rounded bg-gray-200 text-sm font-bold hover:bg-gray-300"
              @click="cartStore.addItem({ id: item.product_id, price: item.unit_price, name: item.name })"
            >+</button>
          </div>

          <div class="text-sm font-bold w-16 text-right">
            ₱{{ (item.unit_price * item.quantity).toFixed(2) }}
          </div>
        </div>

        <!-- Per-item notes — i-toggle para dili masayang ang space -->
        <div v-if="expandedNotes[item.product_id]" class="pt-0.5">
          <input
            v-model="item.notes"
            type="text"
            class="pos-input text-xs py-1"
            placeholder="Item note (e.g. no onions)..."
            @blur="collapseIfEmpty(item)"
          />
        </div>
        <button
          v-else
          class="text-xs text-gray-400 hover:text-gray-600"
          @click="expandedNotes[item.product_id] = true"
        >
          + add note
        </button>
      </div>

      <!-- Empty cart message -->
      <div v-if="!cartStore.items.length" class="text-center text-gray-400 text-sm py-8">
        No items added yet.
      </div>
    </div>

    <!-- Order summary + place order -->
    <div class="border-t p-4 space-y-2">

      <!-- Order type toggle -->
      <div class="flex gap-2 text-sm">
        <button
          class="flex-1 py-1 rounded-lg border font-medium"
          :class="cartStore.orderType === 'dine-in' ? 'bg-brand-red text-white border-brand-red' : ''"
          @click="cartStore.orderType = 'dine-in'"
        >Dine-In</button>
        <button
          class="flex-1 py-1 rounded-lg border font-medium"
          :class="cartStore.orderType === 'takeout' ? 'bg-brand-red text-white border-brand-red' : ''"
          @click="cartStore.orderType = 'takeout'"
        >Takeout</button>
      </div>

      <!-- Table selector — para sa dine-in orders -->
      <div v-if="cartStore.orderType === 'dine-in'" class="flex items-center gap-2 text-sm">
        <label class="text-gray-500 w-20 shrink-0">Table</label>
        <select v-model="cartStore.tableId" class="pos-input text-sm">
          <option :value="null">— No table —</option>
          <option
            v-for="table in availableTables"
            :key="table.id"
            :value="table.id"
          >
            Table {{ table.table_number }} ({{ table.floor }})
          </option>
        </select>
      </div>

      <!-- Discount input -->
      <div class="flex items-center gap-2 text-sm">
        <label class="text-gray-500 w-20 shrink-0">Discount</label>
        <input
          v-model.number="cartStore.discount"
          type="number"
          min="0"
          class="pos-input text-sm"
          placeholder="0"
        />
      </div>

      <!-- Amount tendered input -->
      <div class="flex items-center gap-2 text-sm">
        <label class="text-gray-500 w-20 shrink-0">Cash</label>
        <input
          v-model.number="cartStore.amountTendered"
          type="number"
          min="0"
          class="pos-input text-sm"
          :placeholder="cartStore.total.toFixed(2)"
        />
      </div>

      <!-- Change display -->
      <div v-if="cartStore.amountTendered > 0" class="flex justify-between text-sm text-green-600 font-medium">
        <span>Change</span>
        <span>₱{{ cartStore.change.toFixed(2) }}</span>
      </div>

      <!-- Order notes -->
      <div class="flex items-start gap-2 text-sm">
        <label class="text-gray-500 w-20 shrink-0 pt-1">Notes</label>
        <textarea
          v-model="cartStore.notes"
          class="pos-input text-sm resize-none"
          rows="2"
          placeholder="Special instructions..."
        />
      </div>

      <!-- Totals -->
      <div class="flex justify-between text-sm text-gray-500">
        <span>Subtotal</span>
        <span>₱{{ cartStore.subtotal.toFixed(2) }}</span>
      </div>
      <div class="flex justify-between font-bold text-base">
        <span>Total</span>
        <span class="text-brand-red">₱{{ cartStore.total.toFixed(2) }}</span>
      </div>

      <!-- Error message -->
      <p v-if="error" class="text-red-500 text-xs">{{ error }}</p>

      <!-- Place order button -->
      <button
        class="btn-primary w-full mt-2"
        :disabled="!cartStore.items.length || loading"
        @click="placeOrder"
      >
        {{ loading ? 'Processing...' : 'Place Order' }}
      </button>

      <!-- Clear cart -->
      <button
        class="btn-secondary w-full text-sm"
        :disabled="!cartStore.items.length"
        @click="confirmClear = true"
      >
        Clear
      </button>
    </div>

  </div>

  <!-- Clear cart confirmation -->
  <div v-if="confirmClear" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-xs mx-4 text-center">
      <div class="text-3xl mb-3">🗑️</div>
      <h2 class="text-base font-bold mb-2">Clear cart?</h2>
      <p class="text-sm text-gray-500 mb-4">All items will be removed.</p>
      <div class="flex gap-2">
        <button class="btn-primary flex-1 bg-red-600 hover:bg-red-700" @click="clearCart">Yes, clear</button>
        <button class="btn-secondary flex-1" @click="confirmClear = false">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Receipt modal — gipakita pagkahuman ug order -->
  <ReceiptModal
    v-if="lastOrder"
    :order="lastOrder"
    @close="lastOrder = null"
  />
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useCartStore } from '@/store/modules/cartStore'
import { useOrderStore } from '@/store/modules/orderStore'
import { useToast } from '@/composables/useToast'
import tableService from '@/services/tableService'
import ReceiptModal from '@/components/order/ReceiptModal.vue'

const emit = defineEmits(['order-placed'])

const cartStore  = useCartStore()
const orderStore = useOrderStore()
const { error: toastError } = useToast()
const loading       = ref(false)
const error         = ref('')
const tables        = ref([])
const lastOrder     = ref(null)
const expandedNotes = reactive({})
const confirmClear  = ref(false)

// Kung blangko ang notes pagkahuman ug blur, i-collapse ang input
function collapseIfEmpty(item) {
  if (!item.notes) expandedNotes[item.product_id] = false
}

// I-clear ang cart after confirmation
function clearCart() {
  cartStore.clearCart()
  confirmClear.value = false
  expandedNotes && Object.keys(expandedNotes).forEach(k => delete expandedNotes[k])
}

// Kuha lang ang available nga mga mesa
const availableTables = computed(() =>
  tables.value.filter((t) => t.status === 'available')
)

onMounted(async () => {
  tables.value = (await tableService.getAll()).data
})

// I-submit ang order sa backend
async function placeOrder() {
  if (!cartStore.items.length) return
  error.value   = ''
  loading.value = true
  try {
    const order = await orderStore.placeOrder({
      order_type:      cartStore.orderType,
      table_id:        cartStore.orderType === 'dine-in' ? cartStore.tableId : null,
      discount:        cartStore.discount,
      amount_tendered: cartStore.amountTendered,
      notes:           cartStore.notes || null,
      items:           cartStore.items,
    })
    // Limpyohan ang cart ug ipakita ang receipt
    cartStore.clearCart()
    lastOrder.value = order
    emit('order-placed')
    // I-refresh ang mesa list
    tables.value = (await tableService.getAll()).data
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Something went wrong. Please try again.'
    toastError(error.value)
  } finally {
    loading.value = false
  }
}
</script>
