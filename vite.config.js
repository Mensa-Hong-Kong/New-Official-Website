
import { glob } from "glob";

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

let input = [
    'resources/css/app.scss',
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
});
