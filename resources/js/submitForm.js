import axios from 'axios';
const token = document.querySelector("meta[name='csrf-token']").getAttribute("content");
export default function submitForm(action, method="post", data, successCallback = '', failCallback) {
    data['_token'] = token;
    if(method != 'post') {
        data['_method'] = method;
    }
    axios.post(action, data).then(function (response) {
        if(successCallback != '') {
            successCallback(response);
        } else {
            bootstrapAlert('Success');
        }
    }).catch(function (error) {
        switch(error.status) {
            case 401:
                bootstrapAlert('Unauthorized, please login first');
                window.location.reload();
                break;
            case 403:
                bootstrapAlert('Sorry, you have no permission');
                break;
            case 419:
                bootstrapAlert('Cross-site request forgery alert, may be the domain is not mensa.org.hk, or you hold on this page longer than the CSRF token lifetime');
                break;
            case 422:
                if(failCallback == '') {
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
        if(failCallback != '') {
            failCallback(error);
        }
    });
}
