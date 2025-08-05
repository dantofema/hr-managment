import {defineConfig} from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': path.resolve('./assets/js'),
        },
    },
    build: {
        outDir: '../public/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: '/home/alejandro/projects/hr-managment/assets/js/main.js',
        },
    },
    server: {
        port: 5173,
        strictPort: true,
        origin: 'http://localhost:5173',
        cors: true,
    },
});
