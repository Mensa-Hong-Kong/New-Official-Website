import { post } from "@/submitForm";

const form = document.getElementById('form');

const name = document.getElementById('validationName');
const nameFeedback = document.getElementById('nameFeedback');
const minimumAge = document.getElementById('validationMinimumAge');
const minimumAgeFeedback = document.getElementById('minimumAgeFeedback');
const maximumAge = document.getElementById('validationMaximumAge');
const maximumAgeFeedback = document.getElementById('maximumAgeFeedback');
const startAt = document.getElementById('validationStartAt');
const startAtFeedback = document.getElementById('startAtFeedback');
const endAt = document.getElementById('validationEndAt');
const endAtFeedback = document.getElementById('endAtFeedback');
const quota = document.getElementById('validationQuota');
const quotaFeedback = document.getElementById('quotaFeedback');
const priceName = document.getElementById('validationPriceName');
const priceNameFeedback = document.getElementById('priceNameFeedback');
const price = document.getElementById('validationPrice');
const priceFeedback = document.getElementById('priceFeedback');
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
            minimumAge.classList.add('is-invalid');
            minimumAgeFeedback.className = 'invalid-feedback';
            minimumAgeFeedback.innerText = `The minimum age field must be less than maximum age.`;
            maximumAge.classList.add('is-invalid');
            maximumAgeFeedback.className = 'invalid-feedback';
            maximumAgeFeedback.innerText = `The maximum age field must be greater than minimum age.`;
        }
    }
    if(endAt.value && startAt.value >= endAt.value) {
        startAt.classList.add('is-invalid');
        startAtFeedback.className = 'invalid-feedback';
        startAtFeedback.innerText = `The start at field must be a date before end at field.`;
        endAt.classList.add('is-invalid');
        endAtFeedback.className = 'invalid-feedback';
        endAtFeedback.innerText = `The end at field must be a date after start at field.`;
    }
    if(quota.validity.valueMissing) {
        quota.classList.add('is-invalid');
        quotaFeedback.className = 'invalid-feedback';
        quotaFeedback.innerText = 'The quota field is required.';
    } else if(quota.validity.rangeUnderflow) {
        quota.classList.add('is-invalid');
        quotaFeedback.className = 'invalid-feedback';
        quotaFeedback.innerText = `The quota field must be at least ${quota.min}.`;
    } else if(quota.validity.rangeOverflow) {
        quota.classList.add('is-invalid');
        quotaFeedback.className = 'invalid-feedback';
        quotaFeedback.innerText = `The quota field must not be greater than ${quota.max}.`;
    }
    if(priceName.validity.valueMissing) {
        priceName.classList.add('is-invalid');
        priceNameFeedback.className = 'invalid-feedback';
        priceNameFeedback.innerText = 'The price name field is required.';
    } else if(priceName.validity.tooLong) {
        priceName.classList.add('is-invalid');
        priceNameFeedback.className = 'invalid-feedback';
        priceNameFeedback.innerText = 'The price name field must not be greater than 255 characters.';
    }
    if(price.validity.valueMissing) {
        price.classList.add('is-invalid');
        priceFeedback.className = 'invalid-feedback';
        priceFeedback.innerText = 'The price field is required.';
    } else if(price.validity.rangeUnderflow) {
        price.classList.add('is-invalid');
        priceFeedback.className = 'invalid-feedback';
        priceFeedback.innerText = `The price field must be at least ${price.min}.`;
    } else if(price.validity.rangeOverflow) {
        price.classList.add('is-invalid');
        priceFeedback.className = 'invalid-feedback';
        priceFeedback.innerText = `The price field must not be greater than ${price.max}.`;
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
                case 'start_at':
                    input = startAt;
                    feedback = startAtFeedback;
                    break;
                case 'end_at':
                    input = endAt;
                    feedback = endAtFeedback;
                    break;
                case 'quota':
                    input = quota;
                    feedback = quotaFeedback;
                    break;
                case 'price_name':
                    input = priceName;
                    feedback = priceNameFeedback;
                    break;
                case 'price':
                    input = price;
                    feedback = priceFeedback;
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
                let data = {
                    name: name.value,
                    quota: quota.value,
                    price: price.value
                };
                if(minimumAge.value) {
                    data['minimum_age'] = minimumAge.value;
                }
                if(minimumAge.value) {
                    data['maximum_age'] = maximumAge.value;
                }
                if(startAt.value) {
                    data['start_at'] = maximumAge.value;
                }
                if(endAt.value) {
                    data['end_at'] = maximumAge.value;
                }
                if(priceName.value) {
                    data['price_name'] = maximumAge.value;
                }
                post(form.action, successCallback, failCallback, 'post', data);
            }
        }
    }
);
