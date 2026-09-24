import { ref } from 'vue'

// Shared toast state — gamiton sa tibuok app
const toasts = ref([])
let nextId = 0

export function useToast() {
  // I-add ang toast sa listahan, auto-remove after timeout
  function show(message, type = 'success', duration = 3000) {
    const id = ++nextId
    toasts.value.push({ id, message, type })
    setTimeout(() => {
      toasts.value = toasts.value.filter((t) => t.id !== id)
    }, duration)
  }

  // Shortcut methods
  const success = (msg) => show(msg, 'success')
  const error   = (msg) => show(msg, 'error')
  const info    = (msg) => show(msg, 'info')

  return { toasts, show, success, error, info }
}
