<template>
  <!-- Menu item card — i-click para i-add sa cart -->
  <button
    class="pos-card text-left hover:shadow-lg transition-all active:scale-95"
    :class="{ 'opacity-50 cursor-not-allowed': !product.is_available }"
    :disabled="!product.is_available"
    @click="$emit('add', product)"
  >
    <!-- Product image -->
    <div class="aspect-square rounded-lg bg-gray-100 mb-3 overflow-hidden flex items-center justify-center">
      <img
        v-if="product.image"
        :src="imgUrl(product.image)"
        :alt="product.name"
        class="w-full h-full object-cover"
      />
      <span v-else class="text-4xl">🍽️</span>
    </div>

    <!-- Product info -->
    <div class="font-semibold text-sm leading-tight">{{ product.name }}</div>
    <div class="text-brand-red font-bold mt-1">₱{{ product.price }}</div>

    <!-- Unavailable label -->
    <div v-if="!product.is_available" class="text-xs text-red-400 mt-1">Unavailable</div>
  </button>
</template>

<script setup>
// I-emit ang 'add' event kung gi-click ang product
defineProps({
  product: { type: Object, required: true },
})

defineEmits(['add'])

// I-prefix ang backend URL para sa product images
const backendUrl = import.meta.env.VITE_API_URL?.replace('/api', '') ?? 'http://localhost:8000'

function imgUrl(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return backendUrl + path
}
</script>
