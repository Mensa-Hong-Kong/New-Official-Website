function checkSyncedToStripe() {
    axios.get('/profile/synced-to-stripe').then(function (response) {
        if(response.data.status) {
            document.getElementById('stripeCustomerNotUpToDateAlert').remove();
            clearInterval(interval);
        }
    });
}

let interval = setInterval(checkSyncedToStripe, 30000);
