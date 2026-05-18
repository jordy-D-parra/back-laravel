import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/bootstrap.js',
                'resources/css/bootstrap.css',
                'resources/js/app.js',
                'resources/css/auth/login.css',
                'resources/css/dashboard-layout.css',
                'resources/css/dashboard-home.css',
                'resources/css/admin-usuarios.css',
                'resources/css/admin-trabajadores.css',
                'resources/js/auth/login.js',
                'resources/js/dashboard-layout.js',
                'resources/js/admin-trabajadores.js',
                'resources/js/admin-usuarios.js',
                'resources/js/app.jsx',
                'resources/css/admin-entidades.css',
                'resources/js/admin-entidades.js',
            ],
            refresh: true,
        }),
        react(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});
