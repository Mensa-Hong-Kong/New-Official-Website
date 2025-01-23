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
                'resources/js/admin/users/index.js',
                'resources/js/admin/users/show.js',
                'resources/js/user/forgetPassword.js',
                'resources/js/admin/teamType.js',
                'resources/js/admin/teams/index.js',
                'resources/js/admin/teams/create.js',
                'resources/js/admin/teams/show.js',
                'resources/js/admin/teams/edit.js',
                'resources/js/admin/teams/roles/create.js',
                'resources/js/admin/teams/roles/edit.js',
                'resources/js/admin/permission.js',
                'resources/js/admin/module.js',
                'resources/js/admin/admissionTests/create.js',
            ],
            refresh: true,
        }),
    ],
});
