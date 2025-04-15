@if(!auth()->user()->stripe_id)
    <div id="stripeCustomerNotUpToDateAlert" class="alert alert-danger alert-dismissible fade show" role="alert">
        We are creating you customer account on stripe, please wait a few minutes, when created, this alert will be close.
    </div>
@elseif(!auth()->user()->synced_to_stripe)
    <div id="stripeCustomerNotUpToDateAlert" class="alert alert-danger alert-dismissible fade show" role="alert">
        We are syncing you name and email to stripe, if you keep going, you will get a wrong name receipt when you changed you name, or missing receipt when you changed your default email, you will get the receipt when your default email changed to non default. please wait a few minutes, when synced, this alert will be close.
    </div>
@elseif(!auth()->user()->defaultEmail)
    <div class="alert alert-danger" role="alert">
        You have no default email, the string cannot send the receipt to you. If you added default email, please reload this page to confirm the email has been synced to stripe.
    </div>
@endif


@if(!auth()->user()->synced_to_stripe)
    @push('after footer')
        @vite('resources/js/components/stripeAlert.js')
    @endpush
@endif
