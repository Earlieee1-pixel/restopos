<template>
  <!-- Ari ang sales reports — para sa manager/admin lang -->
  <div class="p-6 overflow-auto flex-1">
    <h1 class="text-2xl font-bold mb-6">Sales Reports</h1>

    <!-- Tabs -->
    <div class="flex border-b mb-6">
      <button
        class="px-5 py-2 text-sm font-medium border-b-2 transition-colors"
        :class="activeTab === 'daily' ? 'border-brand-red text-brand-red' : 'border-transparent text-gray-500'"
        @click="activeTab = 'daily'"
      >Daily</button>
      <button
        class="px-5 py-2 text-sm font-medium border-b-2 transition-colors"
        :class="activeTab === 'monthly' ? 'border-brand-red text-brand-red' : 'border-transparent text-gray-500'"
        @click="activeTab = 'monthly'"
      >Monthly</button>
    </div>

    <!-- ── Daily tab ── -->
    <div v-if="activeTab === 'daily'">

      <!-- Date picker -->
      <div class="mb-6">
        <label class="block text-sm font-medium mb-1">Date</label>
        <input v-model="selectedDate" type="date" class="pos-input w-auto" @change="loadDaily" />
      </div>

      <!-- Loading state -->
      <div v-if="dailyLoading" class="text-center text-gray-400 py-12">Loading...</div>

      <template v-else>
        <!-- Summary cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
          <div class="pos-card">
            <div class="text-sm text-gray-500">Total Sales</div>
            <div class="text-2xl font-bold text-green-600">₱ {{ Number(dailySales.total_sales ?? 0).toFixed(2) }}</div>
          </div>
          <div class="pos-card">
            <div class="text-sm text-gray-500">Total Orders</div>
            <div class="text-2xl font-bold text-blue-600">{{ dailySales.total_orders ?? 0 }}</div>
          </div>
          <div class="pos-card">
            <div class="text-sm text-gray-500">Total Discount</div>
            <div class="text-2xl font-bold text-orange-600">₱ {{ Number(dailySales.total_discount ?? 0).toFixed(2) }}</div>
          </div>
        </div>

        <!-- Top 10 products -->
        <div class="pos-card">
          <h2 class="text-lg font-semibold mb-4">Top 10 Products</h2>
          <ul class="divide-y">
            <li
              v-for="(item, i) in topProducts"
              :key="item.product_id"
              class="flex justify-between py-2 text-sm"
            >
              <span>{{ i + 1 }}. {{ item.product?.name }}</span>
              <span class="text-gray-500">{{ item.total_qty }} sold · ₱{{ Number(item.total_revenue).toFixed(2) }}</span>
            </li>
            <li v-if="!topProducts.length" class="py-4 text-center text-gray-400 text-sm">
              No data available.
            </li>
          </ul>
        </div>
      </template>
    </div>

    <!-- ── Monthly tab ── -->
    <div v-if="activeTab === 'monthly'">

      <!-- Month picker -->
      <div class="mb-6">
        <label class="block text-sm font-medium mb-1">Month</label>
        <input v-model="selectedMonth" type="month" class="pos-input w-auto" @change="loadMonthly" />
      </div>

      <!-- Loading state -->
      <div v-if="monthlyLoading" class="text-center text-gray-400 py-12">Loading...</div>

      <template v-else>
        <!-- Summary cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
          <div class="pos-card">
            <div class="text-sm text-gray-500">Total Sales</div>
            <div class="text-2xl font-bold text-green-600">₱ {{ Number(monthlySales.total_sales ?? 0).toFixed(2) }}</div>
          </div>
          <div class="pos-card">
            <div class="text-sm text-gray-500">Total Orders</div>
            <div class="text-2xl font-bold text-blue-600">{{ monthlySales.total_orders ?? 0 }}</div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="!monthlySales.total_orders" class="pos-card text-center text-gray-400 py-8 text-sm">
          No orders found for this month.
        </div>
      </template>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import reportService from '@/services/reportService'

const activeTab = ref('daily')

// Daily state
const selectedDate = ref(new Date().toISOString().split('T')[0])
const dailySales   = ref({})
const topProducts  = ref([])
const dailyLoading = ref(false)

// Monthly state
const selectedMonth   = ref(new Date().toISOString().slice(0, 7))
const monthlySales    = ref({})
const monthlyLoading  = ref(false)

// I-load ang daily report
async function loadDaily() {
  dailyLoading.value = true
  try {
    const [sales, top] = await Promise.all([
      reportService.getDailySales(selectedDate.value),
      // I-filter ang top products base sa selected date
      reportService.getTopProducts(10, selectedDate.value),
    ])
    dailySales.value  = sales
    topProducts.value = top
  } finally {
    dailyLoading.value = false
  }
}
async function loadMonthly() {
  monthlyLoading.value = true
  try {
    monthlySales.value = await reportService.getMonthlySales(selectedMonth.value)
  } finally {
    monthlyLoading.value = false
  }
}

onMounted(async () => {
  // I-load ang duha pagka-mount
  await Promise.all([loadDaily(), loadMonthly()])
})
</script>
