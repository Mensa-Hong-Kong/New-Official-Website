import { post } from "@/submitForm";

let submitting = 'loading';

const submitButtons = document.getElementsByClassName('submitButton');

function disableSubmitting(){
    for(let button of submitButtons) {
        button.disabled = true;
    }
}

function enableSubmitting(){
    submitting = '';
    for(let button of submitButtons) {
        button.disabled = false;
    }
}

const editForm = document.getElementById('form');
const savingButton = document.getElementById('savingButton');
const saveButton = document.getElementById('saveButton');
const cancelButton = document.getElementById('cancelButton');
const editButton = document.getElementById('editButton');
const showName = document.getElementById('showName');
const showMinimumAge = document.getElementById('showMinimumAge');
const showMaximumAge = document.getElementById('showMaximumAge');
const showStartAt = document.getElementById('showStartAt');
const showEndAt = document.getElementById('showEndAt');
const showQuota = document.getElementById('showQuota');
const nameInput = document.getElementById('validationName');
const nameFeedback = document.getElementById('nameFeedback');
const minimumAgeInput = document.getElementById('validationMinimumAge');
const minimumAgeFeedback = document.getElementById('minimumAgeFeedback');
const maximumAgeInput = document.getElementById('validationMaximumAge');
const maximumAgeFeedback = document.getElementById('maximumAgeFeedback');
const startAtInput = document.getElementById('validationStartAt');
const startAtFeedback = document.getElementById('startAtFeedback');
const endAtInput = document.getElementById('validationEndAt');
const endAAtFeedback = document.getElementById('endAtFeedback');
const quotaInput = document.getElementById('validationQuota');
const quotaFeedback = document.getElementById('quotaFeedback');

const inputs = [
    nameInput, minimumAgeInput, maximumAgeInput,
    startAtInput, endAtInput, quotaInput,
];

const feedbacks = [
    nameFeedback, minimumAgeFeedback, maximumAgeFeedback,
    startAtFeedback, endAAtFeedback, quotaFeedback,
];

const showInfos = [
    showName, showMinimumAge, showMaximumAge,
    showStartAt, showEndAt, showQuota,
];

function fillInputValues() {
    for(let input of inputs) {
        input.hidden = true;
        input.disabled = false;
        input.classList.remove('is-valid');
        input.classList.remove('is-invalid');
        input.value = input.dataset.value;
    }
    for(let feedback of feedbacks) {
        feedback.className = 'valid-feedback';
        feedback.innerText = 'Looks good!'
    }
    for(let showDiv of showInfos) {
        showDiv.hidden = false;
    }
}

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
    for(let input of inputs) {
        input.classList.remove('is-valid');
        input.classList.remove('is-invalid');
    }
    for(let feedback of feedbacks) {
        feedback.className = 'valid-feedback';
        feedback.innerText = 'Looks good!'
    }
    if(nameInput.validity.valueMissing) {
        nameInput.classList.add('is-invalid');
        nameFeedback.className = 'invalid-feedback';
        nameFeedback.innerText = 'The name field is required.';
    } else if(nameInput.validity.tooLong) {
        nameInput.classList.add('is-invalid');
        nameFeedback.className = 'invalid-feedback';
        nameFeedback.innerText = 'The name field must not be greater than 255 characters.';
    }
    if(minimumAgeInput.value) {
        if(minimumAgeInput.validity.rangeUnderflow) {
            minimumAgeInput.classList.add('is-invalid');
            minimumAgeFeedback.className = 'invalid-feedback';
            minimumAgeFeedback.innerText = `The minimum age field must be at least ${minimumAge.min}.`;
        } else if(minimumAgeInput.validity.rangeOverflow) {
            minimumAgeInput.classList.add('is-invalid');
            minimumAgeFeedback.className = 'invalid-feedback';
            minimumAgeFeedback.innerText = `The minimum age field must not be greater than ${minimumAge.max}.`;
        } else if(maximumAgeInput.value && minimumAgeInput.value >= maximumAgeInput.value) {
            minimumAgeInput.classList.add('is-invalid');
            minimumAgeFeedback.className = 'invalid-feedback';
            minimumAgeFeedback.innerText = `The minimum age field must be less than maximum age.`;
        }
    }
    if(maximumAgeInput.value) {
        if(maximumAgeInput.validity.rangeUnderflow) {
            maximumAgeInput.classList.add('is-invalid');
            maximumAgeFeedback.className = 'invalid-feedback';
            maximumAgeFeedback.innerText = `The maximum age field must be at least ${maximumAge.min}.`;
        } else if(maximumAgeInput.validity.rangeOverflow) {
            maximumAgeInput.classList.add('is-invalid');
            maximumAgeFeedback.className = 'invalid-feedback';
            maximumAgeFeedback.innerText = `The maximum age field must not be greater than ${maximumAge.max}.`;
        } else if(minimumAgeInput.value >= maximumAgeInput.value) {
            maximumAgeInput.classList.add('is-invalid');
            maximumAgeFeedback.className = 'invalid-feedback';
            maximumAgeFeedback.innerText = `The maximum age field must be greater than minimum age.`;
        }
    }
    if(endAtInput.value && startAtInput.value >= endAtInput.value) {
        startAtInput.classList.add('is-invalid');
        startAtFeedback.className = 'invalid-feedback';
        startAtFeedback.innerText = `The start at field must be a date before end at field.`;
        endAtInput.classList.add('is-invalid');
        endAtFeedback.className = 'invalid-feedback';
        endAtFeedback.innerText = `The end at field must be a date after start at field.`;
    }
    if(quotaInput.validity.valueMissing) {
        quotaInput.classList.add('is-invalid');
        quotaFeedback.className = 'invalid-feedback';
        quotaFeedback.innerText = 'The quota field is required.';
    } else if(quotaInput.validity.rangeUnderflow) {
        quotaInput.classList.add('is-invalid');
        quotaFeedback.className = 'invalid-feedback';
        quotaFeedback.innerText = `The quota field must be at least ${quotaInput.min}.`;
    } else if(quotaInput.validity.rangeOverflow) {
        quotaInput.classList.add('is-invalid');
        quotaFeedback.className = 'invalid-feedback';
        quotaFeedback.innerText = `The quota field must not be greater than ${quotaInput.max}.`;
    }
    return !hasError();
}

function saveSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    nameInput.dataset.value = response.data.name ?? '';
    minimumAgeInput.dataset.value = response.data.minimum_age ?? '';
    maximumAgeInput.dataset.value = response.data.maximum_age ?? '';
    startAtInput.dataset.value = response.data.start_at ?? '';
    endAtInput.dataset.value = response.data.end_at ?? '';
    quotaInput.dataset.value = response.data.quota ?? '';
    fillInputValues();
    showName.innerText = response.data.name;
    showMinimumAge.innerText = response.data.minimum_age;
    showMaximumAge.innerText = response.data.maximum_age;
    startAtInput.innerText = response.data.start_at;
    endAtInput.innerText = response.data.end_at;
    quotaInput.innerText = response.data.quota;
    enableSubmitting();
    for(let showDiv of showInfos) {
        showDiv.hidden = false;
    }
    savingButton.hidden = true;
    editButton.hidden = false;
}

function saveFailCallback(error) {
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
                    input = nameInput;
                    feedback = nameFeedback;
                    break;
                case 'minimum_age':
                    input = minimumAgeInput;
                    feedback = minimumAgeFeedback;
                    break;
                case 'maximum_age':
                    input = maximumAgeInput;
                    feedback = maximumAgeFeedback;
                    break;
                case 'start_at':
                    input = startAtInput;
                    feedback = startAtInput;
                    break;
                case 'end_at':
                    input = endAtInput;
                    feedback = endAAtFeedback;
                    break;
                case 'quota':
                    input = quotaInput;
                    feedback = quotaFeedback;
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
        if(
            input != isPublicInput &&
            !input.classList.contains('is-invalid')
        ) {
            input.classList.add('is-valid');
        }
        input.disabled = false;
    }
    enableSubmitting();
    savingButton.hidden = true;
    saveButton.hidden = false;
    cancelButton.hidden = false;
}

editForm.addEventListener(
    'submit', function(event) {
        event.preventDefault();
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'updateProduct'+submitAt;
            disableSubmitting();
            if(submitting == 'updateProduct'+submitAt) {
                if(validation()) {
                    saveButton.hidden = true;
                    cancelButton.hidden = true;
                    for(let input of inputs) {
                        input.disabled = true;
                    }
                    let data = {
                        name: nameInput.value,
                        quota: quotaInput.value,
                    };
                    if(minimumAgeInput.value) {
                        data['minimum_age'] = minimumAgeInput.value;
                    }
                    if(maximumAgeInput.value) {
                        data['maximum_age'] = maximumAgeInput.value;
                    }
                    if(startAtInput.value) {
                        data['start_at'] = maximumAge.value;
                    }
                    if(endAtInput.value) {
                        data['end_at'] = maximumAge.value;
                    }
                    post(editForm.action, saveSuccessCallback, saveFailCallback, 'put', data);
                } else {
                    enableSubmitting();
                }
            }
        }
    }
);

cancelButton.addEventListener(
    'click', function() {
        saveButton.hidden = true;
        cancelButton.hidden = true;
        for(let input of inputs) {
            input.hidden = true;
            input.classList.remove('is-valid');
            input.classList.remove('is-invalid');
        }
        for(let feedback of feedbacks) {
            feedback.className = 'valid-feedback';
            feedback.innerText = 'Looks good!'
        }
        fillInputValues();
        for(let showDiv of showInfos) {
            showDiv.hidden = false;
        }
        editButton.hidden = false;
    }
);

editButton.addEventListener(
    'click', function() {
        editButton.hidden = true;
        for(let showSpan of showInfos) {
            showSpan.hidden = true;
        }
        for(let input of inputs) {
            input.hidden = false;
        }
        saveButton.hidden = false;
        cancelButton.hidden = false;
    }
);

submitting = '';
