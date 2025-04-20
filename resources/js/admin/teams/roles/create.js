import { post } from "@/submitForm";

const form = document.getElementById('form');
const name = document.getElementById('validationName');
const nameFeedback = document.getElementById('nameFeedback');
const displayOrder = document.getElementById('validationDisplayOrder');
const displayOrderFeedback = document.getElementById('displayOrderFeedback');
const permissions = document.getElementsByClassName('permission');
const createButton = document.getElementById('createButton');
const creatingButton = document.getElementById('creatingButton');

const inputs = [name, displayOrder];

const feedbacks = [nameFeedback, displayOrderFeedback];

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
        input.classList.remove('is-valid');
        input.classList.remove('is-invalid');
    }
    for(let feedback of feedbacks) {
        feedback.className = 'valid-feedback';
        feedback.innerText = 'Looks good!'
    }
    if(name.validity.valueMissing) {
        name.classList.add('is-invalid');
        nameFeedback.className = 'invalid-feedback';
        nameFeedback.innerText = 'The name field is required.';
    } else if(name.validity.tooShort) {
        name.classList.add('is-invalid');
        nameFeedback.className = 'invalid-feedback';
        nameFeedback.innerText = `The name field must be at least ${name.minLength} characters.`;
    } else if(name.validity.tooLong) {
        name.classList.add('is-invalid');
        nameFeedback.className = 'invalid-feedback';
        nameFeedback.innerText = `The name field must not be greater than ${name.maxLength} characters.`;
    } else if(name.validity.patternMismatch) {
        name.classList.add('is-invalid');
        nameFeedback.className = 'invalid-feedback';
        nameFeedback.innerText = `The name field cannot has ";".`;
    }
    if(displayOrder.validity.valueMissing) {
        displayOrder.classList.add('is-invalid');
        displayOrderFeedback.className = 'invalid-feedback';
        displayOrderFeedback.innerText = 'The display order field is required.';
    }
    return !hasError();
}

function successCallback(response) {
    creatingButton.hidden = true;
    createButton.hidden = false;
    window.location.href = response.request.responseURL;
}

function failCallback(error) {
    for(let input of inputs) {
        input.classList.remove('is-valid');
    }
    for(let feedback of feedbacks) {
        feedback.className = 'valid-feedback';
        feedback.innerText = 'Looks good!'
    }
    if(error.status == 422) {
        for(let key in error.response.data.errors) {
            if(key == 'message') {
                bootstrapAlert(error.response.data.errors.message);
            } else {
                let value = error.response.data.errors[key];
                let feedback;
                let input;
                switch(key) {
                    case 'name':
                        input = name;
                        feedback = nameFeedback;
                        break;
                    case 'display_order':
                        input = displayOrder;
                        feedback = displayOrderFeedback;
                        break;
                }
                if(feedback) {
                    input.classList.add('is-invalid');
                    feedback.className = "invalid-feedback";
                    feedback.innerText = value;
                } else {
                    alert('undefine feedback key');
                }
            }
        }
    }
    for(let input of inputs) {
        if(!input.classList.contains('is-invalid')) {
            input.classList.add('is-valid');
        }
    }
    creatingButton.hidden = true;
    createButton.hidden = false;
}

form.addEventListener(
    'submit', function (event) {
        event.preventDefault();
        if(creatingButton.hidden) {
            if(validation()) {
                createButton.hidden = true;
                creatingButton.hidden = false;
                let data = {
                    name: name.value,
                    display_order: displayOrder.value,
                }
                let modulePermissions = [];
                for(let permission of permissions) {
                    if(permission.checked) {
                        module_permissions.push(permission.value);
                    }
                }
                if(modulePermissions.length) {
                    data[module_permissions] = modulePermissions;
                }
                post(form.action, successCallback, failCallback, 'post', data);
            }
        }
    }
);
