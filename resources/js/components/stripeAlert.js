function checkSyncedToStripe() {
    axios.get(
        route('profile.created-stripe-customer')
    ).then(
        function (response) {
            if(response.data.status) {
                document.getElementById('stripeCustomerNotUpToDateAlert').remove();
                clearInterval(interval);
            }
        }
    );
}

let interval = setInterval(checkSyncedToStripe, 30000);
