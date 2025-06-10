import { mount } from 'svelte';
import Show from './Show.svelte'

mount(
    Show,
    {
        target: document.getElementById('container'),
        props: {product: product},
    }
);
