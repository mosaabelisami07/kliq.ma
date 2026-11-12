import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/admin.css', 'resources/js/admin.js'],
            refresh: true,
            fonts: [
                bunny('Bricolage Grotesque', {
                    alias: 'display',
                    variable: '--kliq-display',
                    weights: [400, 500, 600, 700, 800],
                    preload: [{ weight: 700 }, { weight: 800 }],
                }),
                bunny('Inter Tight', {
                    alias: 'sans',
                    variable: '--kliq-sans',
                    weights: [400, 500, 600],
                    preload: [{ weight: 400 }],
                }),
                bunny('Alexandria', {
                    alias: 'arabic',
                    variable: '--kliq-arabic',
                    weights: [300, 400, 500, 700, 800],
                    subsets: ['arabic', 'latin'],
                    preload: false,
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
