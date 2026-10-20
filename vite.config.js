import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js', 'resources/js/splat-demo.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: { chunkSizeWarningLimit: 800 },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
