let csrf_token = $state('');

export function setCsrfToken(csrfToken) {
    csrf_token = csrfToken;
}

function failHandle(error, callback) {
    switch(error.status) {
        case 401:
            bootstrapAlert('Unauthorized, please login first');
            window.location = route('login');
            break;
        case 403:
            bootstrapAlert('Sorry, you have no permission');
            break;
        case 419:
            bootstrapAlert('Cross-site request forgery alert, may be the domain is not mensa.org.hk, or you hold on this page longer than the CSRF token lifetime');
            break;
        case 500:
            bootstrapAlert('Unexpected error, please contact I.T.');
            break;
        case 503:
            bootstrapAlert('Database connect fail, please try again later, or contact I.T.');
            break;
        default:
            if(error.response.data.message) {
                bootstrapAlert(error.response.data.message);
            }
            break;
    }
    callback(error);
}

export function post(action, successCallback, failCallback, method="POST", data = {}) {
    data['_token'] = csrf_token;
    if(method.toUpperCase() != 'POST') {
        data['_method'] = method;
    }
    axios.post(action, data).then(function (response) {
        successCallback(response);
    }).catch(function(error) {
        failHandle(error, failCallback)}
    );
}

export function get(action, successCallback, failCallback, parameters = {}) {
    axios.get(action, {params: parameters}).then(function (response) {
        successCallback(response);
    }).catch(function(error) {
        failHandle(error, failCallback)}
    );
}
