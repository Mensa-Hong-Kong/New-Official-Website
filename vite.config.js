import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { svelte } from '@sveltejs/vite-plugin-svelte';

export default defineConfig({
    build: { manifest: true },
    plugins: [
        laravel({
			input: ['resources/css/app.scss', 'resources/js/app.js'],
            ssr: 'resources/js/ssr.js',
            refresh: true,
        }),
        svelte(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '~': path.resolve(__dirname, 'node_modules'),
            '^': path.resolve(__dirname, 'public'),
        },
    },
});
