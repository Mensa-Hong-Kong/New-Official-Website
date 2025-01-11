import ClearInputHistory from "../clearInputHistory";
import { post } from "../submitForm";

const form = document.getElementById('form');
const passportType = document.getElementById('validationPassportType');
const passportTypeFeedback = document.getElementById('passportTypeFeedback');
const passportNumber = document.getElementById('validationPassportNumber');
const passportNumberFeedback = document.getElementById('passportNumberFeedback');
const birthday = document.getElementById('validationBirthday');
const birthdayFeedback = document.getElementById('birthdayFeedback');
const verifiedContactType = document.getElementById('validationVerifiedContactType');
const verifiedContactTypeFeedback = document.getElementById('verifiedContactTypeFeedback');
const verifiedContact = document.getElementById('validationVerifiedContact');
const verifiedContactFeedback = document.getElementById('verifiedContactFeedback');
const resetButton = document.getElementById('resetButton');
const resettingButton = document.getElementById('resettingButton');
const resetFeedback = document.getElementById('resetFeedback');
const login = document.getElementById('login');
const disabledLogin = document.getElementById('disabledLogin');
const register = document.getElementById('register');
const disabledRegister = document.getElementById('disabledRegister');

const inputs = [
    passportType,  passportNumber,
    birthday,
    verifiedContactType, verifiedContact];

const feedbacks = [
    passportTypeFeedback, passportNumberFeedback,
    birthdayFeedback,
    verifiedContactTypeFeedback, verifiedContactFeedback
];

new ClearInputHistory(inputs);

verifiedContact.disabled = true;

verifiedContactType.addEventListener(
    'change', function(event) {
        verifiedContact.value = '';
        verifiedContact.disabled = false;
        switch(event.target.value) {
            case 'email':
                verifiedContact.type = 'email';
                if(verifiedContact.hasAttribute('minlength')) {
                    verifiedContact.removeAttribute('minlength');
                }
                verifiedContact.maxLength = 320;
                verifiedContact.placeholder = 'dammy@example.com';
                break;
            case 'mobile':
                verifiedContact.type = 'tel';
                verifiedContact.minLength = 5;
                verifiedContact.maxLength = 15;
                verifiedContact.placeholder = '85298765432';
                break;
            default:
                verifiedContact.disabled = true;
                verifiedContact.placeholder = 'Verified Contact';
        }
    }
);

function hasError() {
    for(let feedback of feedbacks) {
        if(feedback.className == 'invalid-feedback') {
            return true;
        }
    }
    return false;
}

function validation() {
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
    if(birthday.validity.valueMissing) {
        birthday.classList.add('is-invalid');
        birthdayFeedback.className = 'invalid-feedback';
        birthdayFeedback.innerText = 'The birthday field is required.';
    } else if(birthday.validity.rangeOverflow) {
        birthday.classList.add('is-invalid');
        birthdayFeedback.className = 'invalid-feedback';
        birthdayFeedback.innerText = `The birthday not be greater than ${birthday.max} characters.`;
    }
    if(verifiedContactType.validity.valueMissing) {
        verifiedContactType.classList.add('is-invalid');
        verifiedContactTypeFeedback.className = 'invalid-feedback';
        verifiedContactTypeFeedback.innerText = 'The verified contact type field is required.';
    } else if(verifiedContact.validity.valueMissing) {
        verifiedContact.classList.add('is-invalid');
        verifiedContactFeedback.className = 'invalid-feedback';
        verifiedContactFeedback.innerText = 'The verified contact field is required.';
    } else {
        switch(verifiedContactType.value) {
            case 'email':
                if(verifiedContact.validity.tooLong) {
                    verifiedContact.classList.add('is-invalid');
                    verifiedContactFeedback.className = 'invalid-feedback';
                    verifiedContactFeedback.innerText = `The email must not be greater than ${email.maxLength} characters.`;
                } else if(verifiedContact.validity.typeMismatch) {
                    verifiedContact.classList.add('is-invalid');
                    verifiedContactFeedback.className = 'invalid-feedback';
                    verifiedContactFeedback.innerText = `The email must be a valid email address.`;
                }
                break;
            case 'mobile':
                if(verifiedContact.validity.tooShort) {
                    verifiedContact.classList.add('is-invalid');
                    verifiedContactFeedback.className = 'invalid-feedback';
                    verifiedContactFeedback.innerText = `The mobile must be at least ${mobile.minLength} characters.`;
                } else if(verifiedContact.validity.tooLong) {
                    verifiedContact.classList.add('is-invalid');
                    verifiedContactFeedback.className = 'invalid-feedback';
                    verifiedContactFeedback.innerText = `The mobile must not be greater than ${mobile.maxLength} characters.`;
                } else if(verifiedContact.validity.typeMismatch) {
                    verifiedContact.classList.add('is-invalid');
                    verifiedContactFeedback.className = 'invalid-feedback';
                    verifiedContactFeedback.innerText = `The email must be a valid email address.`;
                }
                break;
        }
    }
    return !hasError();
}

function successCallback(response) {
    bootstrapAlert(response.data.success);
    resetFeedback.classList.remove('alert-danger');
    resetFeedback.classList.add('alert-success')
    resetFeedback.hidden = false;
    resetFeedback.innerText = response.data.success;
    resettingButton.hidden = true;
    disabledLogin.hidden = true;
    disabledRegister.hidden = true;
    resetButton.hidden = false;
    login.hidden = false;
    register.hidden = false;
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
                case 'passport_type_id':
                    passportType.classList.add('is-invalid');
                    passportTypeFeedback.className = "invalid-feedback";
                    passportTypeFeedback.innerText = value;
                    break;
                case 'passport_number':
                    passportNumber.classList.add('is-invalid');
                    passportNumberFeedback.className = "invalid-feedback";
                    passportNumberFeedback.innerText = value;
                    break;
                case 'birthday':
                    birthday.classList.add('is-invalid');
                    birthdayFeedback.className = "invalid-feedback";
                    birthdayFeedback.innerText = value;
                    break;
                case 'verified_contact_type':
                    verifiedContactType.classList.add('is-invalid');
                    verifiedContactTypeFeedback.className = "invalid-feedback";
                    verifiedContactTypeFeedback.innerText = value;
                    break;
                case 'verified_contact':
                    verifiedContact.classList.add('is-invalid');
                    verifiedContactFeedback.className = "invalid-feedback";
                    verifiedContactFeedback.innerText = value;
                    break;
                case 'failed':
                    for(let input of inputs) {
                        input.classList.add('is-invalid');
                    }
                    resetFeedback.hidden = false;
                    resetFeedback.innerText = value;
                    break;
                default:
                    alert('undefine feedback key');
                    break;
            }
        }
    }
    for(let input of inputs) {
        if(!input.classList.contains('is-invalid')) {
            input.classList.add('is-valid');
        }
    }
    resettingButton.hidden = true;
    disabledLogin.hidden = true;
    disabledRegister.hidden = true;
    resetButton.hidden = false;
    login.hidden = false;
    register.hidden = false;
}

form.addEventListener(
    'submit', function (event) {
        event.preventDefault();
        resetFeedback.hidden = true;
        if(resetFeedback.classList.contains('alert-success')) {
            resetFeedback.classList.remove('alert-success')
            resetFeedback.classList.add('alert-danger');
        }
        if(resettingButton.hidden) {
            if(validation()) {
                resetButton.hidden = true;
                login.hidden = true;
                register.hidden = true;
                resettingButton.hidden = false;
                disabledLogin.hidden = false;
                disabledRegister.hidden = false;
                let data = {
                    passport_type_id: passportType.value,
                    passport_number: passportNumber.value,
                    birthday: birthday.value,
                    verified_contact_type: verifiedContactType.value,
                    verified_contact: verifiedContact.value,
                };
                post(form.action, successCallback, failCallback, 'put', data);
            }
        }
    }
);
