function checkSyncedToStripe() {
    axios.get('/profile/created-stripe-user').then(function (response) {
        if(response.data.status) {
            document.getElementById('stripeCustomerNotUpToDateAlert').remove();
            clearInterval(interval);
        }
    });
}

let interval = setInterval(checkSyncedToStripe, 30000);
