@if(!auth()->user()->stripe)
    <div id="stripeCustomerNotUpToDateAlert" class="alert alert-danger alert-dismissible fade show" role="alert">
        We are creating you customer account on stripe, please wait a few minutes, when created, this alert will be close.
    </div>
    @push('after footer')
        @vite('resources/js/components/stripeAlert.js')
    @endpush
@elseif(!auth()->user()->defaultEmail)
    <div class="alert alert-danger" role="alert">
        You have no default email, the string cannot send the receipt to you. If you added default email, please reload this page to confirm the email has been synced to stripe.
    </div>
@endif
