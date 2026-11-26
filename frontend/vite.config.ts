import react from '@vitejs/plugin-react';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    base: '/frontend/',
    plugins: [react()],
    build: {
        manifest: 'manifest.json',
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        proxy: {
            '/api': {
                target: process.env.VITE_BACKEND_URL ?? 'http://localhost:8000',
                changeOrigin: true,
            },
        },
    },
    test: {
        environment: 'jsdom',
        setupFiles: './test/setup.ts',
        clearMocks: true,
    },
});
