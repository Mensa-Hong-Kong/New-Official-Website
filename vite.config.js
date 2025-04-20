
import { glob } from "glob";

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

let input = [
    'resources/css/app.scss',
    'resources/css/ckEditor.css',
];

const exclude = [
    'resources/js/bootstrap.js',
    'resources/js/clearInputHistory.js',
    'resources/js/stringToBoolean.js',
    'resources/js/submitForm.js',
];

for(let path of glob.sync("resources/js/**/*.js")) {
    if(!path.match(/.test.js$/) && !exclude.includes(path)) {
        input.push(path);
    }
}

export default defineConfig({
    plugins: [
        laravel({
            input: input,
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '~': path.resolve(__dirname, 'node_modules'),
            '^': path.resolve(__dirname, 'public'),
        },
    },
});
