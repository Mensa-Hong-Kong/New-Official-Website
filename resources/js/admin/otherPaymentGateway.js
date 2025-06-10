import { mount } from 'svelte';
import OtherPaymentGateway from './OtherPaymentGateway.svelte'

mount(
    OtherPaymentGateway,
    {
        target: document.getElementById('container'),
        props: {paymentGateways: paymentGateways},
    }
);
