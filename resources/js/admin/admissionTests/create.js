import { post } from "../../submitForm";

const form = document.getElementById('form');
const testingAt = document.getElementById('validationTestingAt');
const testingAtFeedback = document.getElementById('testingAtFeedback');
const location = document.getElementById('validationLocation');
const locationFeedback = document.getElementById('locationFeedback');
const district = document.getElementById('validationDistrict');
const districtFeedback = document.getElementById('districtFeedback');
const address = document.getElementById('validationAddress');
const addressFeedback = document.getElementById('addressFeedback');
const maximumCandidates = document.getElementById('validationMaximumCandidates');
const maximumCandidatesFeedback = document.getElementById('maximumCandidatesFeedback');
const isPublic = document.getElementById('isPublic');
const createButton = document.getElementById('createButton');
const creatingButton = document.getElementById('creatingButton');

const inputs = [testingAt, location, district, address, maximumCandidates];

const feedbacks = [
    testingAtFeedback, locationFeedback, districtFeedback,
    addressFeedback, maximumCandidatesFeedback];

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
    if(testingAt.validity.valueMissing) {
        testingAt.classList.add('is-invalid');
        testingAtFeedback.className = 'invalid-feedback';
        testingAtFeedback.innerText = 'The testing at field is required.';
    } else if(testingAt.validity.rangeUnderflow) {
        testingAt.classList.add('is-invalid');
        testingAtFeedback.className = 'invalid-feedback';
        testingAtFeedback.innerText = 'The testing at field must be a date after now.';
    }
    if(location.validity.valueMissing) {
        location.classList.add('is-invalid');
        locationFeedback.className = 'invalid-feedback';
        locationFeedback.innerText = 'The location field is required.';
    } else if(location.validity.tooLong) {
        location.classList.add('is-invalid');
        locationFeedback.className = 'invalid-feedback';
        locationFeedback.innerText = 'The location field must not be greater than 255 characters.';
    }
    if(district.validity.valueMissing) {
        district.classList.add('is-invalid');
        districtFeedback.className = 'invalid-feedback';
        districtFeedback.innerText = 'The district field is required.';
    }
    if(address.validity.valueMissing) {
        address.classList.add('is-invalid');
        addressFeedback.className = 'invalid-feedback';
        addressFeedback.innerText = 'The address field is required.';
    } else if(address.validity.tooLong) {
        address.classList.add('is-invalid');
        addressFeedback.className = 'invalid-feedback';
        addressFeedback.innerText = 'The address field must not be greater than 255 characters.';
    }
    if(maximumCandidates.validity.valueMissing) {
        maximumCandidates.classList.add('is-invalid');
        maximumCandidatesFeedback.className = 'invalid-feedback';
        maximumCandidatesFeedback.innerText = 'The maximum candidates field is required.';
    } else if(maximumCandidates.validity.rangeUnderflow) {
        maximumCandidates.classList.add('is-invalid');
        maximumCandidatesFeedback.className = 'invalid-feedback';
        maximumCandidatesFeedback.innerText = 'The maximum candidates field must be at least 1.';
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
                case 'testing_at':
                    input = testingAt;
                    feedback = testingAtFeedback;
                    break;
                case 'location':
                    input = location;
                    feedback = locationFeedback;
                    break;
                case 'district_id':
                    input = district;
                    feedback = districtFeedback;
                    break;
                case 'address':
                    input = address;
                    feedback = addressFeedback;
                    break;
                case 'maximum_candidates':
                    input = maximumCandidates;
                    feedback = maximumCandidatesFeedback;
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
                    testing_at: testingAt.value,
                    location: location.value,
                    district_id: district.value,
                    address: address.value,
                    maximum_candidates: maximumCandidates.value,
                    is_public: isPublic.checked,
                }
                post(form.action, successCallback, failCallback, 'post', data);
            }
        }
    }
);
