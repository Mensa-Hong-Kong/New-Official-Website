import ClearInputHistory from "./clearInputHistory";
import submitForm from "./submitForm";

const form = document.getElementById('form');
const username = document.getElementById('validationUsername');
const usernameFeedback = document.getElementById('usernameFeedback');
const password = document.getElementById('validationPassword');
const passwordFeedback = document.getElementById('passwordFeedback');
const rememberMe = document.getElementById('rememberMe');
const loginButton = document.getElementById('loginButton');
const loggingInButton = document.getElementById('loggingInButton');
const loginFeedback = document.getElementById('loginFeedback');

var inputs = [username,  password];

new ClearInputHistory(inputs);

const feedbacks = [usernameFeedback, passwordFeedback];

function hasError() {
    for(let feedback of feedbacks) {
        if(feedback.className == 'invalid-feedback') {
            return true;
        }
    }
    return false;
}

function validation() {
    for(let input of inputs) {
        input.classList.remove('is-valid"');
        input.classList.remove('is-invalid');
    }
    for(let feedback of feedbacks) {
        feedback.className = 'valid-feedback';
        feedback.innerText = 'Looks good!'
    }
    if(username.validity.valueMissing) {
        username.classList.add('is-invalid');
        usernameFeedback.className = 'invalid-feedback';
        usernameFeedback.innerText = 'The username field is required.';
    } else if(username.validity.tooShort) {
        username.classList.add('is-invalid');
        usernameFeedback.className = 'invalid-feedback';
        usernameFeedback.innerText = `The username field must be at least ${username.minLength} characters.`;
    } else if(username.validity.tooLong) {
        username.classList.add('is-invalid');
        usernameFeedback.className = 'invalid-feedback';
        usernameFeedback.innerText = `The username field must not be greater than ${username.maxLength} characters.`;
    }
    if(password.validity.valueMissing) {
        password.classList.add('is-invalid');
        passwordFeedback.className = 'invalid-feedback';
        passwordFeedback.innerText = 'The password field is required.';
    } else if(password.validity.tooShort) {
        password.classList.add('is-invalid');
        passwordFeedback.className = 'invalid-feedback';
        passwordFeedback.innerText = `The password field must be at least ${password.minLength} characters.`;
    } else if(password.validity.tooLong) {
        password.classList.add('is-invalid');
        passwordFeedback.className = 'invalid-feedback';
        passwordFeedback.innerText = `The password field must not be greater than ${password.maxLength} characters.`;
    }
    for(let input of inputs) {
        if(!input.classList.contains('is-invalid')) {
            input.classList.add('is-valid');
        }
    }
    return !hasError();
}

function successCallback(response) {
    window.location.href = response.request.responseURL;
}

function failCallback(error) {
    for(let input of inputs) {
        input.classList.remove('is-valid"');
    }
    for(let feedback of feedbacks) {
        feedback.className = 'valid-feedback';
        feedback.innerText = 'Looks good!'
    }
    if(error.status == 422) {
        for(let key in error.response.data.errors) {
            let value = error.response.data.errors[key];
            switch(key) {
                case 'username':
                    username.classList.add('is-invalid');
                    usernameFeedback.className = "invalid-feedback";
                    usernameFeedback.innerText = value;
                    break;
                case 'password':
                    password.classList.add('is-invalid');
                    passwordFeedback.className = "invalid-feedback";
                    passwordFeedback.innerText = value;
                    break;
                case 'failed':
                    for(let input of inputs) {
                        input.classList.add('is-invalid');
                    }
                    loginFeedback.hidden = false;
                    loginFeedback.innerText = value;
                    break;
                case 'throttle':
                    loginFeedback.hidden = false;
                    loginFeedback.innerText = value;
                    break;
                default:
                    alert('undefine feedback key');
                    break;
            }
        }
    }
    for(let input of inputs) {
        if(!input.classList.contains('is-invalid')) {
            input.classList.add('is-valid');
        }
    }
    loggingInButton.hidden = true;
    loginButton.hidden = false;
}

form.addEventListener(
    'submit', function (event) {
        event.preventDefault();
        loginFeedback.hidden = true;
        if(loggingInButton.hidden) {
            if(validation()) {
                loginButton.hidden = true;
                loggingInButton.hidden = false;
                let data = {
                    username: username.value,
                    password: password.value,
                }
                if(rememberMe.checked) {
                    data['remember_me'] = true;
                }
                submitForm(form.action, 'post', data, successCallback, failCallback);
            }
        }
    }
);
