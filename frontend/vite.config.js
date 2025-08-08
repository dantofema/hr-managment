import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    host: '0.0.0.0',
    port: process.env.VITE_PORT || 5173,
    watch: {
      usePolling: true,
      interval: 1000
    }
  },
  build: {
    outDir: 'dist',
    rollupOptions: {
      input: './src/main.js',
      output: {
        entryFileNames: 'app.js',
        chunkFileNames: '[name].js',
        assetFileNames: '[name].[ext]'
      }
    }
  }
})
