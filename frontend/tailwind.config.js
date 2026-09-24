/** @type {import('tailwindcss').Config} */
// Tailwind config — i-scan ang tanan Vue files
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts}',
  ],
  theme: {
    extend: {
      // Brand colors (Jollibee-inspired)
      colors: {
        brand: {
          red:    '#D52B1E',
          yellow: '#FFC200',
          dark:   '#1a1a1a',
        },
      },
    },
  },
  plugins: [],
}
