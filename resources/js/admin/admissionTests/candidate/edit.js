import { post } from "@/submitForm";

const form = document.getElementById('form');
const familyName = document.getElementById('validationFamilyName');
const familyNameFeedback = document.getElementById('familyNameFeedback');
const middleName = document.getElementById('validationMiddleName');
const middleNameFeedback = document.getElementById('middleNameFeedback');
const givenName = document.getElementById('validationGivenName');
const givenNameFeedback = document.getElementById('givenNameFeedback');
const passportType = document.getElementById('validationPassportType');
const passportTypeFeedback = document.getElementById('passportTypeFeedback');
const passportNumber = document.getElementById('validationPassportNumber');
const passportNumberFeedback = document.getElementById('passportNumberFeedback');
const gender = document.getElementById('validationGender');
const genderFeedback = document.getElementById('genderFeedback');
const saveButton = document.getElementById('saveButton');
const savingButton = document.getElementById('savingButton');

const inputs = [familyName, middleName, givenName, passportType, passportNumber, gender];
const feedbacks = [
    familyNameFeedback, middleNameFeedback, givenNameFeedback,
    passportTypeFeedback, passportNumberFeedback, genderFeedback,
];

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
    if(familyName.validity.valueMissing) {
        familyName.classList.add('is-invalid');
        familyNameFeedback.className = 'invalid-feedback';
        familyNameFeedback.innerText = 'The family name field is required.';
    } else if(familyName.validity.tooLong) {
        familyName.classList.add('is-invalid');
        familyNameFeedback.className = 'invalid-feedback';
        familyNameFeedback.innerText = `The family name must not be greater than ${familyName.maxLength} characters.`;
    }
    if(middleName.value && middleName.validity.tooLong) {
        middleName.classList.add('is-invalid');
        middleNameFeedback.className = 'invalid-feedback';
        middleNameFeedback.innerText = `The middle name must not be greater than ${middleName.maxLength} characters.`;
    }
    if(givenName.validity.valueMissing) {
        givenName.classList.add('is-invalid');
        givenNameFeedback.className = 'invalid-feedback';
        givenNameFeedback.innerText = 'The given name field is required.';
    } else if(givenName.validity.tooLong) {
        givenName.classList.add('is-invalid');
        givenNameFeedback.className = 'invalid-feedback';
        givenNameFeedback.innerText = `The given name must not be greater than ${givenName.maxLength} characters.`;
    }
    if(passportType.validity.valueMissing) {
        passportType.classList.add('is-invalid');
        passportTypeFeedback.className = 'invalid-feedback';
        passportTypeFeedback.innerText = 'The passport type field is required.';
    }
    if(passportNumber.validity.valueMissing) {
        passportNumber.classList.add('is-invalid');
        passportNumberFeedback.className = 'invalid-feedback';
        passportNumberFeedback.innerText = 'The passport number field is required.';
    } else if(passportNumber.validity.tooShort) {
        passportNumber.classList.add('is-invalid');
        passportNumberFeedback.className = 'invalid-feedback';
        passportNumberFeedback.innerText = `The passport number must be at least ${passportNumber.minLength} characters.`;
    } else if(passportNumber.validity.tooLong) {
        passportNumber.classList.add('is-invalid');
        passportNumberFeedback.className = 'invalid-feedback';
        passportNumberFeedback.innerText = `The passport number must not be greater than ${passportNumber.maxLength} characters.`;
    }
    if(gender.validity.valueMissing) {
        gender.classList.add('is-invalid');
        genderFeedback.className = 'invalid-feedback';
        genderFeedback.innerText = 'The gender field is required.';
    } else if(gender.validity.tooLong) {
        gender.classList.add('is-invalid');
        genderFeedback.className = 'invalid-feedback';
        genderFeedback.innerText = `The gender must not be greater than ${gender.maxLength} characters.`;
    }
    for(let feedback of feedbacks) {
        feedback.className = 'valid-feedback';
        feedback.innerText = 'Looks good!'
    }
    for(let input of inputs) {
        if(!input.classList.contains('is-invalid')) {
            input.classList.add('is-valid');
        }
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
            switch(key) {
                case 'family_name':
                    input = familyName;
                    feedback = familyNameFeedback;
                    break;
                case 'middle_name':
                    input = middleName;
                    feedback = middleNameFeedback;
                    break;
                case 'given_name':
                    input = givenName;
                    feedback = givenNameFeedback;
                    break;
                case 'passport_type_id':
                    input = passportType;
                    feedback = passportTypeFeedback;
                    break;
                case 'passport_number':
                    input = passportNumber;
                    feedback = passportNumberFeedback;
                    break;
                case 'gender':
                    input = gender;
                    feedback = genderFeedback;
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
                    family_name: familyName.value,
                    middle_name: middleName.value,
                    given_name: givenName.value,
                    gender: gender.value,
                    passport_type_id: passportType.value,
                    passport_number: passportNumber.value,
                };
                post(form.action, successCallback, failCallback, 'put', data);
            }
        }
    }
);
