<template>
  <!-- Overview sa sales ug orders karong adlaw -->
  <div class="p-6 overflow-auto flex-1">
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    <!-- Loading state -->
    <div v-if="loading" class="text-center text-gray-400 py-24">Loading dashboard...</div>

    <template v-else>
      <!-- Summary cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <SummaryCard
          label="Today's Sales"
          :value="isManager ? `₱ ${Number(dailySales.total_sales ?? 0).toFixed(2)}` : '—'"
          icon="💰"
          color="bg-green-100 text-green-700"
        />
        <SummaryCard
          label="Today's Orders"
          :value="isManager ? (dailySales.total_orders ?? 0) : '—'"
          icon="🧾"
          color="bg-blue-100 text-blue-700"
        />
        <SummaryCard
          label="Active Orders"
          :value="activeOrderCount"
          icon="🔥"
          color="bg-orange-100 text-orange-700"
        />
        <SummaryCard
          label="Available Tables"
          :value="availableTableCount"
          icon="🪑"
          color="bg-purple-100 text-purple-700"
        />
      </div>

      <!-- Top products — manager/admin lang -->
      <div class="pos-card">
        <h2 class="text-lg font-semibold mb-4">Top-Selling Items</h2>

        <!-- Cashier — walay access sa reports -->
        <div v-if="!isManager" class="py-6 text-center text-gray-400 text-sm">
          Report data is only available to managers and admins.
        </div>

        <ul v-else class="divide-y">
          <li
            v-for="(item, i) in topProducts"
            :key="item.product_id"
            class="flex justify-between items-center py-2 text-sm"
          >
            <span class="font-medium">{{ i + 1 }}. {{ item.product?.name }}</span>
            <span class="text-gray-500">{{ item.total_qty }} sold</span>
          </li>
          <li v-if="!topProducts.length" class="py-4 text-center text-gray-400 text-sm">
            No data available.
          </li>
        </ul>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import SummaryCard from '@/components/common/SummaryCard.vue'
import reportService from '@/services/reportService'
import { useOrderStore } from '@/store/modules/orderStore'
import { useAuthStore } from '@/store/modules/authStore'
import tableService from '@/services/tableService'

const orderStore = useOrderStore()
const authStore  = useAuthStore()

// I-check kung manager o admin ang naka-login
const isManager = computed(() =>
  ['manager', 'admin'].includes(authStore.user?.role)
)

const loading             = ref(false)
const dailySales          = ref({})
const topProducts         = ref([])
const availableTableCount = ref(0)

// I-compute directly gikan sa store para dili mag-stale
const activeOrderCount = computed(() => orderStore.orders.length)

onMounted(async () => {
  loading.value = true
  try {
    const today = new Date().toISOString().split('T')[0]

    const [{ data: tables }] = await Promise.all([
      tableService.getAll(),
      orderStore.fetchOrders(),
    ])

    availableTableCount.value = tables.filter((t) => t.status === 'available').length

    if (isManager.value) {
      const [sales, top] = await Promise.all([
        reportService.getDailySales(today),
        reportService.getTopProducts(5),
      ])
      dailySales.value  = sales
      topProducts.value = top
    }
  } catch {
    // I-show ang empty state — dili i-crash ang dashboard
  } finally {
    loading.value = false
  }
})
</script>
