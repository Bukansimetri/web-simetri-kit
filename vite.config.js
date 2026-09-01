import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
                // Daftar font kurasi Theme Settings (FR-004) — semua di-preload di build time
                // supaya ganti pilihan admin cukup ganti CSS variable, tanpa request jaringan baru.
                bunny('Manrope', {
                    weights: [600, 700, 800],
                }),
                bunny('Be Vietnam Pro', {
                    weights: [400, 500, 600],
                }),
                bunny('Inter', {
                    weights: [400, 500, 600, 700],
                }),
                bunny('Poppins', {
                    weights: [400, 500, 600, 700],
                }),
                bunny('Plus Jakarta Sans', {
                    weights: [400, 500, 600, 700],
                }),
                bunny('Nunito Sans', {
                    weights: [400, 600, 700],
                }),
                bunny('Work Sans', {
                    weights: [400, 500, 600, 700],
                }),
                bunny('Lato', {
                    weights: [400, 700],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
