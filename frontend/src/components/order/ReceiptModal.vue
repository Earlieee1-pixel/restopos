<template>
  <!-- Receipt modal — gipakita pagkahuman ug order -->
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 print:hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 overflow-hidden">

      <!-- Header -->
      <div class="bg-brand-red text-white text-center py-4 px-6 print:bg-white print:text-black">
        <div class="text-2xl mb-1">🧾</div>
        <h2 class="font-bold text-lg">Order Placed!</h2>
        <p class="text-sm opacity-80 print:opacity-100">{{ order.order_number }}</p>
      </div>

      <!-- Receipt body -->
      <div class="p-5 space-y-3 text-sm" id="receipt-content">

        <!-- Restaurant name -->
        <p class="text-center font-bold text-base hidden print:block">🍗 RestoPos</p>
        <p class="text-center text-xs text-gray-500 hidden print:block">{{ new Date().toLocaleString('en-PH') }}</p>

        <!-- Order type + table -->
        <div class="flex justify-between text-gray-500">
          <span>Type</span>
          <span class="capitalize font-medium text-gray-800">{{ order.order_type }}</span>
        </div>
        <div v-if="order.table" class="flex justify-between text-gray-500">
          <span>Table</span>
          <span class="font-medium text-gray-800">Table {{ order.table.table_number }}</span>
        </div>

        <hr />

        <!-- Items list -->
        <div v-for="item in order.items" :key="item.id" class="flex justify-between">
          <span class="text-gray-700">
            {{ item.quantity }}x {{ item.product?.name }}
            <span v-if="item.notes" class="block text-xs text-gray-400 italic">{{ item.notes }}</span>
          </span>
          <span class="font-medium">₱{{ (item.unit_price * item.quantity).toFixed(2) }}</span>
        </div>

        <hr />

        <!-- Totals -->
        <div v-if="order.discount > 0" class="flex justify-between text-gray-500">
          <span>Discount</span>
          <span>- ₱{{ Number(order.discount).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between font-bold text-base">
          <span>Total</span>
          <span class="text-brand-red print:text-black">₱{{ Number(order.total_amount).toFixed(2) }}</span>
        </div>
        <div v-if="order.amount_tendered > 0" class="flex justify-between text-gray-500">
          <span>Cash</span>
          <span>₱{{ Number(order.amount_tendered).toFixed(2) }}</span>
        </div>
        <div v-if="order.amount_tendered > 0" class="flex justify-between text-green-600 font-semibold">
          <span>Change</span>
          <span>₱{{ Number(order.change).toFixed(2) }}</span>
        </div>

        <hr />

        <!-- Order notes -->
        <div v-if="order.notes" class="text-xs text-gray-500 italic">
          📝 {{ order.notes }}
        </div>

        <!-- Footer message -->
        <p class="text-center text-gray-400 text-xs pt-1">{{ receiptHeader }}</p>
      </div>

      <!-- Action buttons -->
      <div class="px-5 pb-5 flex gap-2">
        <button class="btn-secondary flex-1" @click="printReceipt">🖨️ Print</button>
        <button class="btn-primary flex-1" @click="$emit('close')">Done</button>
      </div>

    </div>
  </div>
</template>

<script setup>
// Props sa receipt modal
defineProps({
  order: { type: Object, required: true },
})

defineEmits(['close'])

// Kuha ang receipt header gikan sa env, fallback sa default
const receiptHeader = import.meta.env.VITE_RECEIPT_HEADER ?? 'Thank you for your order!'

// I-print ang receipt gamit ang browser print dialog
function printReceipt() {
  window.print()
}
</script>
