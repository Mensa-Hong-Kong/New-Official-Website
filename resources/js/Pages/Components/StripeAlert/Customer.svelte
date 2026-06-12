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
            [
                '.StripeCustomerCreated',
                '.SyncedToStripe',
                '.DefaultEmail',
            ],
            (event) => {
                if (event.created_stripe_customer !== undefined) {
                    customer.created_stripe_customer = event.created_stripe_customer;
                }
                if (event.synced_to_stripe !== undefined) {
                    customer.synced_to_stripe = event.synced_to_stripe;
                }
                if (event.default_email !== undefined) {
                    customer.default_email = event.default_email;
                }
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
            You have no default email, the stripe cannot send the receipt to you.
        </Alert>
    {:else if ! customer.synced_to_stripe}
        <Alert color="danger">
            We are syncing your information to stripe, please wait a few minutes, when synced, this alert will be close. You also can keep going to transaction but the receipt may not sent to your expected email or name not same with your profile.
        </Alert>
    {/if}
{/if}
