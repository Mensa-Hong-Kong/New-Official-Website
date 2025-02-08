import { post } from "../../submitForm";

const form = document.getElementById('form');
const master = document.getElementById('validationMaster');
const masterFeedback = document.getElementById('masterFeedback');
const name = document.getElementById('validationName');
const nameFeedback = document.getElementById('nameFeedback');
const url = document.getElementById('validationUrl');
const urlFeedback = document.getElementById('urlFeedback');
const displayOrder = document.getElementById('validationDisplayOrder');
const displayOrderFeedback = document.getElementById('displayOrderFeedback');
const saveButton = document.getElementById('saveButton');
const savingButton = document.getElementById('savingButton');

master.addEventListener(
    'change', function(event) {
        displayOrder.disabled = ! event.target.value;
        for(let option of displayOrder.options) {
            if(!option.disabled) {
                option.hidden = option.dataset.masterid != event.target.value;
            }
        }
        displayOrder.value = "";
    }
);

const inputs = [master, name, url, displayOrder];

const feedbacks = [masterFeedback, nameFeedback, urlFeedback, displayOrderFeedback];

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
    if(master.validity.valueMissing) {
        master.classList.add('is-invalid');
        masterFeedback.className = 'invalid-feedback';
        masterFeedback.innerText = 'The master field is required.';
    }
    if(name.validity.valueMissing) {
        name.classList.add('is-invalid');
        nameFeedback.className = 'invalid-feedback';
        nameFeedback.innerText = 'The name field is required.';
    } else if(name.validity.tooLong) {
        name.classList.add('is-invalid');
        nameFeedback.className = 'invalid-feedback';
        nameFeedback.innerText = `The name field must not be greater than ${name.maxLength} characters.`;
    }
    if(url.value) {
        if(url.validity.tooLong) {
            url.classList.add('is-invalid');
            urlFeedback.className = 'invalid-feedback';
            urlFeedback.innerText = `The url field must not be greater than ${url.maxLength} characters.`;
        } else if(url.validity.typeMismatch) {
            url.classList.add('is-invalid');
            urlFeedback.className = 'invalid-feedback';
            urlFeedback.innerText = 'The url field is not a valid URL.';
        }
    }
    if(displayOrder.validity.valueMissing) {
        displayOrder.classList.add('is-invalid');
        displayOrderFeedback.className = 'invalid-feedback';
        displayOrderFeedback.innerText = 'The display order field is required.';
    }
    return !hasError();
}

function successCallback(response) {
    savingButton.hidden = true;
    saveButton.hidden = false;
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
            let value = error.response.data.errors[key];
            let feedback;
            let input;
            switch(key) {
                case 'master_id':
                    input = master;
                    feedback = masterFeedback;
                    break;
                case 'name':
                    input = name;
                    feedback = nameFeedback;
                    break;
                case 'url':
                    input = url;
                    feedback = urlFeedback;
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
    for(let input of inputs) {
        if(!input.classList.contains('is-invalid')) {
            input.classList.add('is-valid');
        }
    }
    savingButton.hidden = true;
    saveButton.hidden = false;
}

form.addEventListener(
    'submit', function(event) {
        event.preventDefault();
        if(savingButton.hidden) {
            if(validation()) {
                saveButton.hidden = true;
                savingButton.hidden = false;
                let data = {
                    master_id: master.value,
                    name: name.value,
                    url: url.value,
                    display_order: displayOrder.value,
                }
                post(form.action, successCallback, failCallback, 'put', data);
            }
        }
    }
);
