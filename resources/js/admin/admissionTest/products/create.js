import { post } from "@/submitForm";

const form = document.getElementById('form');

const name = document.getElementById('validationName');
const nameFeedback = document.getElementById('nameFeedback');
const minimumAge = document.getElementById('validationMinimumAge');
const minimumAgeFeedback = document.getElementById('minimumAgeFeedback');
const maximumAge = document.getElementById('validationMaximumAge');
const maximumAgeFeedback = document.getElementById('maximumAgeFeedback');
const createButton = document.getElementById('createButton');
const creatingButton = document.getElementById('creatingButton');

const inputs = [name, minimumAge, maximumAge];

const feedbacks = [nameFeedback, minimumAgeFeedback, maximumAgeFeedback];

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
    } else if(name.validity.tooLong) {
        name.classList.add('is-invalid');
        nameFeedback.className = 'invalid-feedback';
        nameFeedback.innerText = 'The name field must not be greater than 255 characters.';
    }
    if(minimumAge.value) {
        if(minimumAge.validity.rangeUnderflow) {
            minimumAge.classList.add('is-invalid');
            minimumAgeFeedback.className = 'invalid-feedback';
            minimumAgeFeedback.innerText = `The minimum age field must be at least ${minimumAge.min}.`;
        } else if(minimumAge.validity.rangeOverflow) {
            minimumAge.classList.add('is-invalid');
            minimumAgeFeedback.className = 'invalid-feedback';
            minimumAgeFeedback.innerText = `The minimum age field must not be greater than ${minimumAge.max}.`;
        } else if(maximumAge.value && minimumAge.value >= maximumAge.value) {
            minimumAge.classList.add('is-invalid');
            minimumAgeFeedback.className = 'invalid-feedback';
            minimumAgeFeedback.innerText = `The minimum age field must be less than maximum age.`;
        }
    }
    if(maximumAge.value) {
        if(maximumAge.validity.rangeUnderflow) {
            maximumAge.classList.add('is-invalid');
            maximumAgeFeedback.className = 'invalid-feedback';
            maximumAgeFeedback.innerText = `The maximum age field must be at least ${maximumAge.min}.`;
        } else if(maximumAge.validity.rangeOverflow) {
            maximumAge.classList.add('is-invalid');
            maximumAgeFeedback.className = 'invalid-feedback';
            maximumAgeFeedback.innerText = `The maximum age field must not be greater than ${maximumAge.max}.`;
        } else if(minimumAge.value >= maximumAge.value) {
            maximumAge.classList.add('is-invalid');
            maximumAgeFeedback.className = 'invalid-feedback';
            maximumAgeFeedback.innerText = `The maximum age field must be greater than minimum age.`;
        }
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
            let value = error.response.data.errors[key];
            let feedback;
            let input;
            switch(key) {
                case 'name':
                    input = name;
                    feedback = nameFeedback;
                    break;
                case 'minimum_age':
                    input = minimumAge;
                    feedback = minimumAgeFeedback;
                    break;
                case 'maximum_age':
                    input = maximumAge;
                    feedback = maximumAgeFeedback;
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
                let data = {name: name.value};
                if(minimumAge.value) {
                    data['minimum_age'] = minimumAge.value;
                }
                if(minimumAge.value) {
                    data['maximum_age'] = maximumAge.value;
                }
                post(form.action, successCallback, failCallback, 'post', data);
            }
        }
    }
);
