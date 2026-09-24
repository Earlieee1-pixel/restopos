// Helper functions para sa currency formatting

// Format ang numero sa peso (e.g. 1500 → ₱1,500.00)
export function formatPeso(amount) {
  return new Intl.NumberFormat('en-PH', {
    style:    'currency',
    currency: 'PHP',
  }).format(amount ?? 0)
}

// Compute ang sukli
export function computeChange(tendered, total) {
  return Math.max(0, tendered - total)
}
