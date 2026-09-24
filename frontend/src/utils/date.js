// Helper functions para sa date formatting

// I-format ang date (e.g. 2024-01-15 → Jan 15, 2024)
export function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-PH', {
    year:  'numeric',
    month: 'long',
    day:   'numeric',
  })
}

// I-format ang date + time
export function formatDateTime(dateStr) {
  return new Date(dateStr).toLocaleString('en-PH', {
    year:   'numeric',
    month:  'short',
    day:    'numeric',
    hour:   '2-digit',
    minute: '2-digit',
  })
}

// Karong adlaw sa YYYY-MM-DD format
export function today() {
  return new Date().toISOString().split('T')[0]
}
