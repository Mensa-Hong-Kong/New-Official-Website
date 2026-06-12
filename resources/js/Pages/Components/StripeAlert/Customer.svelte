<script>
    import { Alert } from '@sveltestrap/sveltestrap';
    import { useEcho } from "@laravel/echo-svelte";

    let { customer = $bindable(), type } = $props();

    let modelName;

    switch(type) {
        case 'user':
            modelName = `App.Models.User`;
    }

    if (modelName && customer.id) {
        useEcho(
            `${modelName}.${customer.id}`,
            '.StripeCustomerCreated',
            (event) => {
                customer.created_stripe_customer = event.created_stripe_customer;
            }
        );
    }
</script>

{#if modelName && customer.id}
    {#if ! customer.created_stripe_customer}
        <Alert color="danger">
            We are creating your customer account on stripe, please wait a few minutes, when created, this alert will be close.
        </Alert>
    {:else if ! customer.default_email}
        <Alert color="danger">
            You have no default email, the stripe cannot send the receipt to you. If you added default email, please reload this page to confirm the email has been synced to stripe.
        </Alert>
    {/if}
{/if}
