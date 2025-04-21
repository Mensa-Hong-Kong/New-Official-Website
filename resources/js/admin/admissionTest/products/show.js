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
const nameInput = document.getElementById('validationName');
const nameFeedback = document.getElementById('nameFeedback');
const minimumAgeInput = document.getElementById('validationMinimumAge');
const minimumAgeFeedback = document.getElementById('minimumAgeFeedback');
const maximumAgeInput = document.getElementById('validationMaximumAge');
const maximumAgeFeedback = document.getElementById('maximumAgeFeedback');

const inputs = [nameInput, minimumAgeInput, maximumAgeInput];

const feedbacks = [nameFeedback, minimumAgeFeedback, maximumAgeFeedback];

const showInfos = [showName, showMinimumAge, showMaximumAge];

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
    return !hasError();
}

function saveSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    nameInput.dataset.value = response.data.name;
    minimumAgeFeedback.dataset.value = response.data.minimum_age;
    maximumAgeFeedback.dataset.value = response.data.maximum_age;
    fillInputValues();
    showName.innerText = response.data.name;
    showMinimumAge.innerText = response.data.minimum_age;
    showMaximumAge.innerText = response.data.maximum_age;
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
                    let data = {name: nameInput.value};
                    if(minimumAgeInput.value) {
                        data['minimum_age'] = minimumAgeInput.value;
                    }
                    if(maximumAgeInput.value) {
                        data['maximum_age'] = maximumAgeInput.value;
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
