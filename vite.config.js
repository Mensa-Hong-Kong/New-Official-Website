import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import path from 'path';

export default defineConfig(
    ({ mode }) => {
        const fileEnv = loadEnv(mode, process.cwd(), '',);
        const env = { ...fileEnv, ...process.env };
        const viteCustomEnv = {};
        for (const [key, value] of Object.entries(env)) {
            if (key.startsWith('VITE_')) {
                const resolvedValue = value.replace(/\${([^}]+)}/g, (_, g) => env[g] || '');
                viteCustomEnv[`import.meta.env.${key}`] = JSON.stringify(resolvedValue);
            }
        }

        // 5. 確保在 `testing` 模式下，import.meta.env.MODE 也能正確顯示
        viteCustomEnv['import.meta.env.MODE'] = JSON.stringify(mode);

        return {
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
                watch: {
                    ignored: ['**/storage/framework/views/**'],
                },
            },
        };
    });
