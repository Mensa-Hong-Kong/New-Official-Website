import { createInertiaApp } from '@inertiajs/svelte'
import createServer from '@inertiajs/svelte/server'
import { render } from 'svelte/server'

createServer(
    page => createInertiaApp({
        page,
        resolve: name => {
            return { default: page.default };
        },
        setup({ App, props }) {
            return render(App, { props })
        },
    }),
    { cluster: true },
)
