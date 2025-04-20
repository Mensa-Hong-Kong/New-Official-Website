import { post } from "@/submitForm";

const form = document.getElementById('form');

const name = document.getElementById('validationName');
const nameFeedback = document.getElementById('nameFeedback');
const intervalMonth = document.getElementById('validationIntervalMonth');
const intervalMonthFeedback = document.getElementById('intervalMonthFeedback');
const isActive = document.getElementById('isActive');
const displayOrder = document.getElementById('validationDisplayOrder');
const displayOrderFeedback = document.getElementById('displayOrderFeedback');
const saveButton = document.getElementById('saveButton');
const savingButton = document.getElementById('savingButton');

const inputs = [name, intervalMonth, displayOrder];

const feedbacks = [nameFeedback, intervalMonthFeedback, displayOrderFeedback];

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
    if(intervalMonth.validity.valueMissing) {
        intervalMonth.classList.add('is-invalid');
        intervalMonthFeedback.className = 'invalid-feedback';
        intervalMonthFeedback.innerText = 'The interval month field is required.';
    } else if(intervalMonth.validity.rangeOverflow) {
        intervalMonth.classList.add('is-invalid');
        intervalMonthFeedback.className = 'invalid-feedback';
        intervalMonthFeedback.innerText = `The interval month field must be at least ${intervalMonth.min}.`;
    } else if(intervalMonth.validity.range) {
        intervalMonth.classList.add('is-invalid');
        intervalMonthFeedback.className = 'invalid-feedback';
        intervalMonthFeedback.innerText = `The interval month field must be at least ${intervalMonth.max}.`;
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
                case 'name':
                    input = name;
                    feedback = nameFeedback;
                    break;
                case 'interval_month':
                    input = intervalMonth;
                    feedback = intervalMonthFeedback;
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
    'submit', function (event) {
        event.preventDefault();
        if(savingButton.hidden) {
            if(validation()) {
                saveButton.hidden = true;
                savingButton.hidden = false;
                let data = {
                    name: name.value,
                    interval_month: intervalMonth.value,
                    is_active: isActive.checked,
                    display_order: displayOrder.value,
                }
                post(form.action, successCallback, failCallback, 'put', data);
            }
        }
    }
);
