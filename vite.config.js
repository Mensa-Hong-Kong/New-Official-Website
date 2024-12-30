import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.scss',
                'resources/js/app.js',
                'resources/js/user/register.js',
                'resources/js/user/profile.js',
                'resources/js/user/login.js',
                'resources/js/admin/users/index.js'
            ],
            refresh: true,
        }),
    ],
});
