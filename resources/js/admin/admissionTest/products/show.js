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

const priceForms = document.getElementsByClassName('priceForm');
const priceRoot = document.getElementById('prices');

function urlGetPriceID(url) {
    return (new URL(url).pathname).match(/^\/admin\/admission-test\/products\/([0-9]+)\/prices\/([0-9]+).*/i)[2];
}

function priceValidation(priceNameInput) {
    if(priceNameInput.validity.tooLong) {
        bootstrapAlert('The price name field must not be greater than 255 characters.');
        return false;
    }
    return true;
}

function updatePriceSuccess(response) {
    bootstrapAlert(response.data.success);
    let id = urlGetPriceID(response.request.responseURL);
    let priceStartAtInput = document.getElementById('priceStartAtInput'+id);
    let priceNameInput = document.getElementById('priceNameInput'+id);
    priceStartAtInput.hidden = true;
    priceNameInput.hidden = true;
    priceStartAtInput.disabled = false;
    priceNameInput.disabled = false;
    priceStartAtInput.value = response.data.start_at;
    priceStartAtInput.dataset.value = response.data.start_at;
    priceNameInput.value = response.data.name;
    priceNameInput.dataset.value = response.data.name;
    let showPriceStartAt = document.getElementById('showPriceStartAt'+id);
    let showPriceName = document.getElementById('showPriceName'+id);
    showPriceStartAt.innerText = response.data.start_at;
    showPriceName.innerText = response.data.name;
    for(let priceForm of priceForms) {
        let thisID = priceForm.id.replace('priceForm', '');
        if(thisID != id && document.getElementById('priceStartAtInput'+id).value <= response.data.start_at) {
            priceRoot.insertBefore(document.getElementById('priceForm'+id), priceForm);
            break;
        }
    }
    showPriceStartAt.hidden = false;
    showPriceName.hidden = false;
    document.getElementById('savingPrice'+id).hidden = true;
    document.getElementById('editPrice'+id).hidden = false;
    enableSubmitting();
}

function updatePriceFail(error) {
    let id = urlGetPriceID(error.request.responseURL);
    if(error.status == 422) {
        bootstrapAlert(error.response.data.errors.join("\r\n"));
    }
    document.getElementById('savingPrice'+id).hidden = true;
    priceStartAtInput.disabled = false;
    priceNameInput.disabled = false;
    document.getElementById('savePrice'+id).hidden = false;
    document.getElementById('cancelEditPrice'+id).disabled = false;
    enableSubmitting();
}

function savePrice(event) {
    event.preventDefault();
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'updatePrice'+submitAt;
        let id = event.target.id.replace('priceForm', '');
        let priceStartAtInput = document.getElementById('priceStartAtInput'+id);
        let priceNameInput = document.getElementById('priceNameInput'+id);
        disableSubmitting();
        if(submitting == 'updatePrice'+submitAt) {
            if(priceValidation(priceNameInput)) {
                priceStartAtInput.disabled = true;
                priceNameInput.disabled = true;
                document.getElementById('savePrice'+id).hidden = true;
                document.getElementById('cancelEditPrice'+id).hidden = true;
                document.getElementById('savingPrice'+id).hidden = false;
                let data = {};
                if(priceStartAtInput.value) {
                    data['start_at'] = priceStartAtInput.value;
                }
                if(priceNameInput.value) {
                    data['name'] = priceStartAtInput.value;
                }
                post(event.target.action, updatePriceSuccess, updatePriceFail, 'put', data);
            } else {
                enableSubmitting();
            }
        }
    }
}

function cancelEditPrice(event) {
    let id = event.target.id.replace('cancelEditPrice', '');
    document.getElementById('savePrice'+id).hidden = true;
    document.getElementById('cancelEditPrice'+id).hidden = true;
    let startAtInput = document.getElementById('priceStartAtInput'+id);
    startAtInput.hidden = true;
    startAtInput.value = startAtInput.dataset.value;
    let nameAtInput = document.getElementById('priceNameInput'+id);
    nameAtInput.hidden = true;
    nameAtInput.value = nameAtInput.dataset.value;
    document.getElementById('showPriceStartAt'+id).hidden = false;
    document.getElementById('showPriceName'+id).hidden = false;
    document.getElementById('editPrice'+id).hidden = false;
}

function editPrice(event) {
    let id = event.target.id.replace('editPrice', '');
    document.getElementById('editPrice'+id).hidden = true;
    document.getElementById('showPriceStartAt'+id).hidden = true;
    document.getElementById('showPriceName'+id).hidden = true;
    document.getElementById('priceStartAtInput'+id).hidden = false;
    document.getElementById('priceNameInput'+id).hidden = false;
    document.getElementById('savePrice'+id).hidden = false;
    document.getElementById('cancelEditPrice'+id).hidden = false;
}

function setPriceEventListeners(loader) {
    let id = loader.id.replace('priceLoader', '');
    document.getElementById('priceForm'+id).addEventListener(
        'submit', savePrice
    );
    document.getElementById('cancelEditPrice'+id).addEventListener(
        'click', cancelEditPrice
    );
    let editButton = document.getElementById('editPrice'+id);
    editButton.addEventListener(
        'click', editPrice
    );
    loader.remove();
    editButton.hidden = false;
}

document.querySelectorAll('.priceLoader').forEach(
    (loader) => {
        setPriceEventListeners(loader);
    }
);

submitting = '';
