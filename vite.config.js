import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css',
                'resources/css/custom.css',
                'resources/scss/app.scss',
                'resources/js/app.js',
                'resources/js/inertia-index.js'],
            refresh: true,
        }),
        // Keeping these options copy pasted because I don't know if we'll need these
        // https://laravel.com/docs/12.x/vite#vue
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
