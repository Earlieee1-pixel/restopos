<template>
  <!-- Ari ang overview sa tanan mesa -->
  <div class="p-6 overflow-auto flex-1">
    <h1 class="text-2xl font-bold mb-6">Tables</h1>

    <!-- Loading state -->
    <div v-if="loading" class="text-center text-gray-400 py-12">Loading tables...</div>

    <!-- Table grid -->
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
      <div
        v-for="table in tables"
        :key="table.id"
        class="pos-card text-center transition-shadow"
        :class="statusColor(table.status)"
      >
        <!-- Clickable area — navigate to orders -->
        <div
          class="cursor-pointer hover:opacity-80"
          @click="selectTable(table)"
        >
          <div class="text-3xl mb-1">🪑</div>
          <div class="font-bold text-lg">Table {{ table.table_number }}</div>
          <div class="text-xs capitalize mt-1">{{ table.status }}</div>
          <div class="text-xs text-gray-500">{{ table.capacity }} seats · {{ table.floor }}</div>

          <!-- Active order badge -->
          <div v-if="table.active_order" class="mt-2 text-xs bg-red-100 text-red-600 rounded-full px-2 py-0.5 inline-block">
            #{{ table.active_order.order_number }}
          </div>
        </div>

        <!-- Status action buttons -->
        <div class="flex gap-1 mt-3 justify-center flex-wrap">
          <button
            v-if="table.status !== 'available'"
            class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 hover:bg-green-200"
            @click.stop="updateStatus(table, 'available')"
          >Set Available</button>
          <button
            v-if="table.status !== 'reserved'"
            class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 hover:bg-yellow-200"
            @click.stop="updateStatus(table, 'reserved')"
          >Reserve</button>
          <button
            v-if="table.status === 'reserved'"
            class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600 hover:bg-red-200"
            @click.stop="updateStatus(table, 'available')"
          >Unreserve</button>
        </div>
      </div>

      <!-- No tables found -->
      <div v-if="!tables.length" class="col-span-full text-center text-gray-400 py-12">
        No tables found.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/store/modules/cartStore'
import { useToast } from '@/composables/useToast'
import tableService from '@/services/tableService'

const router    = useRouter()
const cartStore = useCartStore()
const { error: toastError } = useToast()
const tables    = ref([])
const loading   = ref(false)

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await tableService.getAll()
    tables.value = data
  } finally {
    loading.value = false
  }
})

// Kulor base sa status sa mesa
function statusColor(status) {
  return {
    available: 'border-l-4 border-green-500',
    occupied:  'border-l-4 border-red-500',
    reserved:  'border-l-4 border-yellow-500',
  }[status] ?? ''
}

// I-select ang mesa — i-set sa cart then adto sa order screen
function selectTable(table) {
  if (table.status === 'reserved') return
  cartStore.orderType = 'dine-in'
  cartStore.tableId   = table.id
  router.push({ name: 'orders' })
}

// I-update ang status sa mesa manually
async function updateStatus(table, status) {
  try {
    const { data } = await tableService.updateStatus(table.id, status)
    const idx = tables.value.findIndex((t) => t.id === table.id)
    if (idx !== -1) tables.value[idx] = { ...tables.value[idx], ...data }
  } catch (e) {
    toastError(e.response?.data?.message ?? 'Failed to update table status.')
  }
}
</script>
