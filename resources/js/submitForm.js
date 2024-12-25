const token = document.querySelector("meta[name='csrf-token']").getAttribute("content");

function successHandle(response, callback) {
    if(callback != '') {
        callback(response);
    } else {
        bootstrapAlert('Success');
    }
}

function failHandle(error, callback) {
    switch(error.status) {
        case 401:
            bootstrapAlert('Unauthorized, please login first');
            window.location.pathname = '/login';
            break;
        case 403:
            bootstrapAlert('Sorry, you have no permission');
            break;
        case 403:
            if(error.response.data.message) {
                bootstrapAlert(error.response.data.message);
            }
            break;
        case 410:
            if(error.response.data.message) {
                bootstrapAlert(error.response.data.message);
            }
            break;
        case 419:
            bootstrapAlert('Cross-site request forgery alert, may be the domain is not mensa.org.hk, or you hold on this page longer than the CSRF token lifetime');
            break;
        case 429:
            if(error.response.data.message) {
                bootstrapAlert(error.response.data.message);
            }
            break;
        case 422:
            if(callback == '') {
                bootstrapAlert('Some field type checking failed but I.T. have no set the handle, please check your typing or see GitHub what checking failed or contact I.T.');
            }
            break;
        case 500:
            bootstrapAlert('Unexpected error, please contact I.T.');
            break;
        case 503:
            bootstrapAlert('Database connect fail, please try again later, or contact I.T.');
            break;
    }
    if(callback != '') {
        callback(error);
    }
}

export function post(action, method="post", data = {}, successCallback = '', failCallback = '') {
    data['_token'] = token;
    if(method != 'post') {
        data['_method'] = method;
    }
    axios.post(action, data).then(function (response) {
        successHandle(response, successCallback);
    }).catch(function(error) {
        failHandle(error, failCallback)}
    );
}

export function get(action, parameters = {}, successCallback = '', failCallback = '') {
    axios.get(action, {params: parameters}).then(function (response) {
        successHandle(response, successCallback);
    }).catch(function(error) {
        failHandle(error, failCallback)}
    );
}
