<script>
    import { Alert } from '@sveltestrap/sveltestrap';
    let { customer = $bindable(), type } = $props();

    if(! customer.created_stripe_customer) {
        let routeName;
        switch(type) {
            case 'user':
                routeName = "profile.created-stripe-customer";
                break;
        }
        function checkCustomerCreated() {
            axios.get(
                route(routeName)
            ).then(
                function (response) {
                    if(response.data.status) {
                        customer.created_stripe_customer = true;
                        clearInterval(checkCustomerCreatedInterval);
                    }
                }
            );
        }

        let checkCustomerCreatedInterval = setInterval(checkCustomerCreated, 30000);
    }
</script>

{#if ! customer.created_stripe_customer}
    <Alert color="danger">
        We are creating your customer account on stripe, please wait a few minutes, when created, this alert will be close.
    </Alert>
{:else if ! customer.default_email}
    <Alert color="danger">
        You have no default email, the stripe cannot send the receipt to you. If you added default email, please reload this page to confirm the email has been synced to stripe.
    </Alert>
{/if}
