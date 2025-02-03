import { glob } from "glob";

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

let input = [
    'resources/css/app.scss',
    'resources/js/app.js',
];

for(let path of glob.sync("resources/js/**/*.js")) {
    if(!path.match(/.test.js$/)) {
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
});
