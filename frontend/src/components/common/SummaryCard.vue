<template>
  <!-- Reusable summary card para sa dashboard -->
  <div class="pos-card flex items-center gap-4">
    <div
      class="w-12 h-12 rounded-full flex items-center justify-center text-xl shrink-0"
      :class="color"
    >
      {{ icon }}
    </div>
    <div>
      <div class="text-xs text-gray-500">{{ label }}</div>
      <div class="text-xl font-bold">{{ value }}</div>
    </div>
  </div>
</template>

<script setup>
// Props sa summary card
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  icon:  { type: String, default: '📦' },
  color: { type: String, default: 'bg-gray-100 text-gray-600' },
})
</script>
