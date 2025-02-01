import { post } from "../../submitForm";
import stringToBoolean from "../../stringToBoolean";

const editForm = document.getElementById('form');
const savingButton = document.getElementById('savingButton');
const saveButton = document.getElementById('saveButton');
const cancelButton = document.getElementById('cancelButton');
const editButton = document.getElementById('editButton');
const showTestingAt = document.getElementById('showTestingAt');
const showLocation = document.getElementById('showLocation');
const showDistrict = document.getElementById('showDistrict');
const showAddress = document.getElementById('showAddress');
const showMaximumCandidates = document.getElementById('showMaximumCandidates');
const showIsPublic = document.getElementById('showIsPublic');
const testingAtInput = document.getElementById('validationTestingAt');
const testingAtFeedback = document.getElementById('testingAtFeedback');
const locationInput = document.getElementById('validationLocation');
const locationFeedback = document.getElementById('locationFeedback');
const districtInput = document.getElementById('validationDistrict');
const districtFeedback = document.getElementById('districtFeedback');
const addressInput = document.getElementById('validationAddress');
const addressFeedback = document.getElementById('addressFeedback');
const maximumCandidatesInput = document.getElementById('validationMaximumCandidates');
const maximumCandidatesFeedback = document.getElementById('maximumCandidatesFeedback');
const isPublicInput = document.getElementById('isPublic');

let submitting = 'loading';
const submitButtons = document.getElementsByClassName('submitButton');

const inputs = [testingAtInput, locationInput, districtInput, addressInput, maximumCandidatesInput, isPublicInput];

const feedbacks = [testingAtFeedback, locationFeedback, districtFeedback, addressFeedback, maximumCandidatesFeedback];

const showInfos = [showTestingAt, showLocation, showDistrict, showAddress, showMaximumCandidates, showIsPublic];

let districts = {};

for(let district of districtInput) {
    if(! district.disabled) {
        districts[district.value] = district.innerText.trim();
    }
}

function fillInputValues() {
    for(let input of inputs) {
        input.hidden = true;
        input.disabled = false;
        input.classList.remove('is-valid');
        input.classList.remove('is-invalid');
    }
    for(let feedback of feedbacks) {
        feedback.className = 'valid-feedback';
        feedback.innerText = 'Looks good!'
    }
    testingAtInput.value = testingAtInput.dataset.value;
    locationInput.value = locationInput.dataset.value;
    districtInput.value = districtInput.dataset.value;
    addressInput.value = addressInput.dataset.value;
    maximumCandidatesInput.value = maximumCandidatesInput.dataset.value;
    isPublicInput.checked = stringToBoolean(isPublicInput.dataset.value);
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
    if(testingAtInput.validity.valueMissing) {
        testingAtInput.classList.add('is-invalid');
        testingAtFeedback.className = 'invalid-feedback';
        testingAtFeedback.innerText = 'The testing at field is required.';
    }
    if(locationInput.validity.valueMissing) {
        locationInput.classList.add('is-invalid');
        locationFeedback.className = 'invalid-feedback';
        locationFeedback.innerText = 'The location field is required.';
    } else if(locationInput.validity.tooLong) {
        locationInput.classList.add('is-invalid');
        locationFeedback.className = 'invalid-feedback';
        locationFeedback.innerText = 'The location field must not be greater than 255 characters.';
    }
    if(districtInput.validity.valueMissing) {
        districtInput.classList.add('is-invalid');
        districtFeedback.className = 'invalid-feedback';
        districtFeedback.innerText = 'The district field is required.';
    }
    if(addressInput.validity.valueMissing) {
        addressInput.classList.add('is-invalid');
        addressFeedback.className = 'invalid-feedback';
        addressFeedback.innerText = 'The address field is required.';
    } else if(addressInput.validity.tooLong) {
        addressInput.classList.add('is-invalid');
        addressFeedback.className = 'invalid-feedback';
        addressFeedback.innerText = 'The address field must not be greater than 255 characters.';
    }
    if(maximumCandidatesInput.validity.valueMissing) {
        maximumCandidatesInput.classList.add('is-invalid');
        maximumCandidatesFeedback.className = 'invalid-feedback';
        maximumCandidatesFeedback.innerText = 'The maximum candidates field is required.';
    } else if(maximumCandidatesInput.validity.rangeUnderflow) {
        maximumCandidatesInput.classList.add('is-invalid');
        maximumCandidatesFeedback.className = 'invalid-feedback';
        maximumCandidatesFeedback.innerText = 'The maximum candidates field must be at least 1.';
    }
    for(let input of inputs) {
        if(
            input != isPublicInput &&
            !input.classList.contains('is-invalid')
        ) {
            input.classList.add('is-valid');
        }
    }
    return !hasError();
}

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

function saveSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    testingAtInput.dataset.value = response.data.testing_at;
    locationInput.dataset.value = response.data.location;
    districtInput.dataset.value = response.data.district_id;
    addressInput.dataset.value = response.data.address;
    maximumCandidatesInput.dataset.value = response.data.maximum_candidates;
    isPublicInput.dataset.value = response.data.is_public;
    fillInputValues()
    showTestingAt.innerText = response.data.testing_at;
    showLocation.innerText = response.data.location;
    showDistrict.innerText = districts[response.data.district_id];
    showAddress.innerText = response.data.address;
    showMaximumCandidates.innerText = response.data.maximum_candidates;
    showIsPublic = response.data.is_public ? 'Public' : 'Private';
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
                case 'testing_at':
                    input = testingAtInput;
                    feedback = testingAtFeedback;
                    break;
                case 'location':
                    input = locationInput;
                    feedback = locationFeedback;
                    break;
                case 'location':
                    input = locationInput;
                    feedback = locationFeedback;
                    break;
                case 'district':
                    input = districtInput;
                    feedback = districtFeedback;
                    break;
                case 'address':
                    input = addressInput;
                    feedback = addressFeedback;
                    break;
                case 'maximum_candidates':
                    input = maximumCandidatesInput;
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
            submitting = 'updateAdmissionTest'+submitAt;
            disableSubmitting();
            if(submitting == 'updateAdmissionTest'+submitAt) {
                if(validation()) {
                    saveButton.hidden = true;
                    cancelButton.hidden = true;
                    for(let input of inputs) {
                        input.disabled = true;
                    }
                    let data = {
                        testing_at: testingAtInput.value,
                        location: locationInput.value,
                        district_id: districtInput.value,
                        address: addressInput.value,
                        maximum_candidates: maximumCandidatesInput.value,
                        is_public: isPublicInput.checked,
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

const proctor = document.getElementById('proctor');
const createProctorForm = document.getElementById('createProctorForm');
const proctorUserIdInput = document.getElementById('proctorUserIdInput');
const proctorName = document.getElementById('proctorName');
const addProctorButton = document.getElementById('addProctorButton');
const addingProctorButton = document.getElementById('addingProctorButton');

let users = {};
for(let option of document.getElementById('users').options) {
    users[option.value] = option.innerText;
}

proctorUserIdInput.addEventListener(
    'keyup', function(event) {
        proctorName.innerText = users[event.target.value] ?? '';
    }
)

function userIdValidation(input)
{
    if(input.validity.valueMissing) {
        bootstrapAlert('The user id field is required.');
        return false;
    }
    if(input.validity.patternMismatch) {
        bootstrapAlert('The user id field must be an integer.');
        return false;
    }
    if(typeof users[input.value] == undefined) {
        bootstrapAlert('The selected user id is invalid.');
        return false;
    }
    return true;
}

function createProctorSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    let rowElement = document.createElement('div');
    rowElement.className = 'row g-3';
    rowElement.innerHTML = `
        <div class="col-md-2">${response.data.user_id}</div>
        <div class="col-md-4">${response.data.name}</div>
        <div class="col-md-2">
            <a href="${response.data.user_show_url}" class="btn btn-primary">Show</a>
        </div>
    `;
    proctor.insertBefore(rowElement, createProctorForm);
    addingProctorButton.hidden = true;
    addProctorButton.hidden = false;
    proctorUserIdInput.value = '';
    proctorName.innerText = '';
    proctorUserIdInput.disabled = false;
    enableSubmitting();
}

function createProctorFailCallback(error) {
    if(error.status == 422) {
        bootstrapAlert(error.response.data.errors.user_id);
    }
    addingProctorButton.hidden = true;
    addProctorButton.hidden = false;
    proctorUserIdInput.disabled = false;
    enableSubmitting();
}

createProctorForm.addEventListener(
    'submit', function(event) {
        event.preventDefault();
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'addProctor'+submitAt;
            disableSubmitting();
            if(submitting == 'addProctor'+submitAt) {
                if(userIdValidation(proctorUserIdInput)) {
                    proctorUserIdInput.disabled = true;
                    addProctorButton.hidden = true;
                    addingProctorButton.hidden = false;
                    let data = {user_id: proctorUserIdInput.value};
                    post(event.target.action, createProctorSuccessCallback, createProctorFailCallback, 'post', data);
                } else {
                    enableSubmitting();
                }
            }
        }
    }
);

submitting = '';
