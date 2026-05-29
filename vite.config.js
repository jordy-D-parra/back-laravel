import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // JS principal
                'resources/js/bootstrap.js',
                'resources/js/app.js',
                'resources/js/dashboard-layout.js',

                // CSS principal
                'resources/css/bootstrap.css',
                'resources/css/dashboard-layout.css',
                'resources/css/dashboard-home.css',

                // Módulo de Entidades
                'resources/css/admin-entidades.css',
                'resources/js/admin-entidades.js',

                // Módulo de Equipos
                'resources/css/admin-equipos.css',
                'resources/js/admin-equipos.js',

                // Módulo de Inventario
                'resources/css/admin-inventario.css',
                'resources/js/admin-inventario.js',

                // Módulo de Roles
                'resources/css/admin-roles.css',
                'resources/js/admin-roles.js',

                // Módulo de Trabajadores
                'resources/css/admin-trabajadores.css',
                'resources/js/admin-trabajadores.js',

                // Módulo de Usuarios
                'resources/css/admin-usuarios.css',
                'resources/js/admin-usuarios.js',

                // NUEVO: Módulo de Solicitudes
                'resources/css/admin-solicitud.css',
                'resources/js/admin-solicitud.js',

                // NUEVO: Módulo de Fichas de Soporte
                'resources/css/admin-soporte.css',
                'resources/js/admin-soporte.js',

                // Login y utilidades
                'resources/css/auth/login.css',
                'resources/js/auth/login.js',

                // React y otros
                'resources/js/app.jsx',
                'resources/css/contrast-system.css',
                'resources/css/skeleton-loading.css',
                'resources/css/smooth-modals.css',
                'resources/js/validations.js',
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
