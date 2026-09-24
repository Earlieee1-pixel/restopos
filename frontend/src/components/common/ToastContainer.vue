<template>
  <!-- Toast notifications — naka-fixed sa bottom-right -->
  <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none">
    <transition-group name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium min-w-64 max-w-sm"
        :class="toastClass(toast.type)"
      >
        <span class="text-base">{{ toastIcon(toast.type) }}</span>
        <span>{{ toast.message }}</span>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToast } from '@/composables/useToast'

const { toasts } = useToast()

// Kulor base sa type sa toast
function toastClass(type) {
  return {
    success: 'bg-green-600 text-white',
    error:   'bg-red-600 text-white',
    info:    'bg-blue-600 text-white',
  }[type] ?? 'bg-gray-800 text-white'
}

// Icon base sa type
function toastIcon(type) {
  return { success: '✅', error: '❌', info: 'ℹ️' }[type] ?? '📢'
}
</script>

<style scoped>
/* Slide-in animation para sa toast */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
