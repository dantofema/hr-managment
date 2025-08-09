import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src')
    }
  },
  server: {
    host: '0.0.0.0',
    port: process.env.VITE_PORT || 5173,
    watch: {
      usePolling: true,
      interval: 1000
    }
  },
  build: {
    outDir: '../public',
    rollupOptions: {
      input: './src/main.js',
      output: {
        entryFileNames: 'js/app.js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            return 'css/app.css';
          }
          return 'assets/[name].[ext]';
        }
      }
    }
  }
})
