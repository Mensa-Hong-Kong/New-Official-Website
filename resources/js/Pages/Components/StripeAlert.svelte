<script>
    let { customer, type } = $props();

    if(! customer.created_stripe_account) {
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
                        customer.created_stripe_account = true;
                        clearInterval(checkCustomerCreatedInterval);
                    }
                }
            );
        }

        let checkCustomerCreatedInterval = setInterval(checkCustomerCreated, 30000);
    }
</script>

{#if ! customer.created_stripe_account}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        We are creating your customer account on stripe, please wait a few minutes, when created, this alert will be close.
    </div>
{:else if ! customer.default_email}
    <div class="alert alert-danger" role="alert">
        You have no default email, the string cannot send the receipt to you. If you added default email, please reload this page to confirm the email has been synced to stripe.
    </div>
{/if}