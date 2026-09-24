import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

// Vite config sa frontend
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      // Para dili na mag-type ug '../../../'
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: 5173,
    // I-proxy ang API calls sa Laravel backend
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
})
