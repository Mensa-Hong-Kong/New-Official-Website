import { createInertiaApp } from '@inertiajs/svelte'
import createServer from '@inertiajs/svelte/server'
import { render } from 'svelte/server'
import Layout from "@/Pages/Layouts/App.svelte";

createServer(
    page => createInertiaApp({
        page,
        resolve: name => {
            return { default: page.default, layout: page.layout || Layout };
        },
        setup({ App, props }) {
            return render(App, { props })
        },
    }),
    { cluster: true },
)
