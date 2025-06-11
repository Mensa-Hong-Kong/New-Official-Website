function checkCustomerCreated() {
    axios.get(
        route('profile.created-stripe-customer')
    ).then(
        function (response) {
            if(response.data.status) {
                document.getElementById('stripeCustomerUncreatedAlert').remove();
                clearInterval(checkCustomerCreatedInterval);
            }
        }
    );
}

let checkCustomerCreatedInterval = setInterval(checkCustomerCreated, 30000);
