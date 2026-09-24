<template>
  <!-- Ari ang POS ordering screen -->
  <div class="flex h-full overflow-hidden">

    <!-- Left: Menu + Active Orders + History -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <!-- Tabs -->
      <div class="flex border-b bg-white px-4">
        <button
          class="px-4 py-3 text-sm font-medium border-b-2 transition-colors"
          :class="activeTab === 'menu' ? 'border-brand-red text-brand-red' : 'border-transparent text-gray-500'"
          @click="activeTab = 'menu'"
        >New Order</button>
        <button
          class="px-4 py-3 text-sm font-medium border-b-2 transition-colors"
          :class="activeTab === 'orders' ? 'border-brand-red text-brand-red' : 'border-transparent text-gray-500'"
          @click="activeTab = 'orders'"
        >
          Active Orders
          <span v-if="orderStore.orders.length" class="ml-1 bg-red-500 text-white text-xs rounded-full px-1.5">
            {{ orderStore.orders.length }}
          </span>
        </button>
        <button
          class="px-4 py-3 text-sm font-medium border-b-2 transition-colors"
          :class="activeTab === 'history' ? 'border-brand-red text-brand-red' : 'border-transparent text-gray-500'"
          @click="activeTab = 'history'; loadHistory(1)"
        >History</button>
      </div>

      <!-- Menu tab -->
      <div v-if="activeTab === 'menu'" class="flex-1 p-4 overflow-auto">
        <div class="flex gap-2 mb-4 flex-wrap">
          <button
            class="px-3 py-1 rounded-full text-sm font-medium border"
            :class="selectedCategory === null ? 'bg-brand-red text-white border-brand-red' : 'bg-white'"
            @click="selectedCategory = null"
          >All</button>
          <button
            v-for="cat in categories"
            :key="cat.id"
            class="px-3 py-1 rounded-full text-sm font-medium border"
            :class="selectedCategory === cat.id ? 'bg-brand-red text-white border-brand-red' : 'bg-white'"
            @click="selectedCategory = cat.id"
          >{{ cat.icon }} {{ cat.name }}</button>
        </div>

        <div v-if="productStore.loading" class="text-center text-gray-400 py-12">Loading menu...</div>
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
          <ProductCard
            v-for="product in filteredProducts"
            :key="product.id"
            :product="product"
            @add="cartStore.addItem(product)"
          />
        </div>
      </div>

      <!-- Active Orders tab -->
      <div v-if="activeTab === 'orders'" class="flex-1 p-4 overflow-auto space-y-3">
        <div v-if="orderStore.loading" class="text-center text-gray-400 py-12">Loading orders...</div>
        <div v-else-if="!orderStore.orders.length" class="text-center text-gray-400 py-12">No active orders.</div>

        <div v-for="order in orderStore.orders" :key="order.id" class="pos-card space-y-2">
          <div class="flex justify-between items-start">
            <div>
              <span class="font-bold text-sm">{{ order.order_number }}</span>
              <span class="ml-2 text-xs text-gray-500 capitalize">{{ order.order_type }}</span>
              <span v-if="order.table" class="ml-2 text-xs text-gray-500">· Table {{ order.table.table_number }}</span>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="statusBadge(order.status)">
              {{ order.status }}
            </span>
          </div>

          <ul class="text-xs text-gray-600 space-y-0.5">
            <li v-for="item in order.items" :key="item.id">
              {{ item.quantity }}x {{ item.product?.name }}
              <span v-if="item.notes" class="text-gray-400 italic"> — {{ item.notes }}</span>
            </li>
          </ul>

          <div v-if="order.notes" class="text-xs text-gray-400 italic">📝 {{ order.notes }}</div>

          <div class="flex justify-between items-center pt-1">
            <span class="font-bold text-sm text-brand-red">₱{{ order.total_amount }}</span>
            <div class="flex gap-2">
              <button
                v-if="order.status === 'pending'"
                class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded-lg font-medium hover:bg-blue-200"
                @click="updateStatus(order.id, 'preparing')"
              >Mark Preparing</button>
              <button
                v-if="order.status === 'preparing'"
                class="text-xs px-3 py-1 bg-green-100 text-green-700 rounded-lg font-medium hover:bg-green-200"
                @click="openServeModal(order)"
              >Mark Served</button>
              <button
                v-if="['pending', 'preparing'].includes(order.status)"
                class="text-xs px-3 py-1 bg-red-100 text-red-600 rounded-lg font-medium hover:bg-red-200"
                @click="handleCancel(order.id)"
              >Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <!-- History tab -->
      <div v-if="activeTab === 'history'" class="flex-1 flex flex-col overflow-hidden">

        <!-- Date filter -->
        <div class="p-4 border-b flex items-center gap-3 bg-white">
          <label class="text-sm font-medium text-gray-600">Date</label>
          <input
            v-model="historyDate"
            type="date"
            class="pos-input w-auto text-sm"
            @change="loadHistory(1)"
          />
          <button
            v-if="historyDate"
            class="btn-secondary text-xs px-3 py-1"
            @click="historyDate = ''; loadHistory(1)"
          >Clear</button>
        </div>

        <div class="flex-1 overflow-auto p-4 space-y-3">
          <div v-if="historyLoading" class="text-center text-gray-400 py-12">Loading history...</div>
          <div v-else-if="!history.length" class="text-center text-gray-400 py-12">No order history found.</div>

          <div v-for="order in history" :key="order.id" class="pos-card space-y-2">
            <div class="flex justify-between items-start">
              <div>
                <span class="font-bold text-sm">{{ order.order_number }}</span>
                <span class="ml-2 text-xs text-gray-500 capitalize">{{ order.order_type }}</span>
                <span v-if="order.table" class="ml-2 text-xs text-gray-500">· Table {{ order.table.table_number }}</span>
                <span class="ml-2 text-xs text-gray-400">· {{ order.cashier?.name }}</span>
              </div>
              <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="statusBadge(order.status)">
                {{ order.status }}
              </span>
            </div>

            <ul class="text-xs text-gray-500 space-y-0.5">
              <li v-for="item in order.items" :key="item.id">
                {{ item.quantity }}x {{ item.product?.name }}
              </li>
            </ul>

            <div class="flex justify-between items-center text-xs pt-1">
              <span class="text-gray-400">{{ formatDate(order.created_at) }}</span>
              <span class="font-bold text-gray-700">₱{{ order.total_amount }}</span>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="historyLastPage > 1" class="flex justify-center gap-2 pt-2">
            <button
              class="btn-secondary text-xs px-3 py-1"
              :disabled="historyPage === 1"
              @click="loadHistory(historyPage - 1)"
            >← Prev</button>
            <span class="text-xs text-gray-500 self-center">Page {{ historyPage }} of {{ historyLastPage }}</span>
            <button
              class="btn-secondary text-xs px-3 py-1"
              :disabled="historyPage === historyLastPage"
              @click="loadHistory(historyPage + 1)"
            >Next →</button>
          </div>
        </div>
      </div>

    </div>

    <!-- Right: Cart -->
    <div class="w-80 border-l bg-white flex flex-col shrink-0">
      <CartPanel @order-placed="refreshTables" />
    </div>

  </div>

  <!-- Mark Served modal — i-collect ang bayad ug compute ang sukli -->
  <div v-if="serveModal.open" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4">
      <h2 class="text-lg font-bold mb-1">Collect Payment</h2>
      <p class="text-sm text-gray-500 mb-4">{{ serveModal.order?.order_number }}</p>

      <div class="space-y-3">
        <!-- Total -->
        <div class="flex justify-between text-sm font-medium">
          <span>Total</span>
          <span class="text-brand-red font-bold">₱{{ Number(serveModal.order?.total_amount).toFixed(2) }}</span>
        </div>

        <!-- Cash tendered -->
        <div>
          <label class="block text-sm font-medium mb-1">Cash Received</label>
          <input
            v-model.number="serveModal.amountTendered"
            type="number"
            min="0"
            class="pos-input"
            :placeholder="serveModal.order?.total_amount"
            @input="serveModal.amountTendered = $event.target.value"
          />
        </div>

        <!-- Change -->
        <div class="flex justify-between text-sm font-semibold text-green-600">
          <span>Change</span>
          <span>₱{{ Math.max(0, (serveModal.amountTendered || 0) - Number(serveModal.order?.total_amount)).toFixed(2) }}</span>
        </div>
      </div>

      <div class="flex gap-2 mt-5">
        <button
          class="btn-primary flex-1"
          :disabled="serveModal.loading"
          @click="confirmServe"
        >
          {{ serveModal.loading ? 'Processing...' : 'Confirm Served' }}
        </button>
        <button class="btn-secondary flex-1" @click="serveModal.open = false">Cancel</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue'
import { useProductStore } from '@/store/modules/productStore'
import { useCartStore } from '@/store/modules/cartStore'
import { useOrderStore } from '@/store/modules/orderStore'
import tableService from '@/services/tableService'
import orderService from '@/services/orderService'
import ProductCard from '@/components/product/ProductCard.vue'
import CartPanel from '@/components/order/CartPanel.vue'
import { formatDateTime } from '@/utils/date'

const productStore = useProductStore()
const cartStore    = useCartStore()
const orderStore   = useOrderStore()

const activeTab = ref('menu')
const tables    = ref([])

// Serve modal state — para sa cash collection
const serveModal = reactive({
  open:           false,
  order:          null,
  amountTendered: 0,
  loading:        false,
})

// History state
const history         = ref([])
const historyLoading  = ref(false)
const historyDate     = ref('')
const historyPage     = ref(1)
const historyLastPage = ref(1)

const selectedCategory = computed({
  get: () => productStore.selectedCategory,
  set: (val) => (productStore.selectedCategory = val),
})

const categories       = computed(() => productStore.categories)
const filteredProducts = computed(() => productStore.filteredProducts)

async function refreshTables() {
  const { data } = await tableService.getAll()
  tables.value = data
}

async function updateStatus(id, status) {
  await orderStore.updateOrderStatus(id, status)
  if (status === 'served') await refreshTables()
}

// I-open ang serve modal para sa cash collection
function openServeModal(order) {
  serveModal.order          = order
  serveModal.amountTendered = Number(order.total_amount)
  serveModal.loading        = false
  serveModal.open           = true
}

// I-confirm ang serve — i-pass ang amount_tendered sa backend
async function confirmServe() {
  serveModal.loading = true
  try {
    await orderStore.updateOrderStatus(serveModal.order.id, 'served', {
      amount_tendered: serveModal.amountTendered,
    })
    serveModal.open = false
    await refreshTables()
  } finally {
    serveModal.loading = false
  }
}

async function handleCancel(id) {
  await orderStore.cancelOrder(id)
  await refreshTables()
}

// I-load ang order history — optional date filter + pagination
async function loadHistory(page = 1) {
  historyLoading.value = true
  historyPage.value    = page
  try {
    const { data } = await orderService.getHistory(historyDate.value || null, page)
    history.value         = data.data
    historyLastPage.value = data.last_page
  } finally {
    historyLoading.value = false
  }
}

function formatDate(dateStr) {
  return formatDateTime(dateStr)
}

let refreshInterval = null

onMounted(async () => {
  // I-load ang products ug orders parallel — await both
  await Promise.all([
    productStore.fetchProducts(),
    orderStore.fetchOrders(),
  ])

  // Auto-refresh active orders kada 30 segundos
  refreshInterval = setInterval(() => {
    orderStore.fetchOrders()
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
})

function statusBadge(status) {
  return {
    pending:   'bg-yellow-100 text-yellow-700',
    preparing: 'bg-blue-100 text-blue-700',
    served:    'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-600',
  }[status] ?? 'bg-gray-100 text-gray-600'
}
</script>
