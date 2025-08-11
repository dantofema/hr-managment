import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [vue()],
  server: {
    host: '0.0.0.0', // Permitir conexiones externas
    port: 5173,
    strictPort: true,
    allowedHosts: ['node', 'localhost', '.localhost'],
    cors: {
      origin: true,
      credentials: true
    },
    hmr: process.env.NODE_ENV === 'test' ? false : {
      port: 5173,
      host: '0.0.0.0'
    },
    watch: {
      usePolling: true // Para Docker en algunos sistemas
    },
    proxy: {
      '/api': {
        target: 'http://app:8000',
        changeOrigin: true,
        secure: false
      }
    }
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src')
    }
  },
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    sourcemap: true
  },
  preview: {
    host: '0.0.0.0',
    port: 5173
  }
})