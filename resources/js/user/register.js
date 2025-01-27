import ClearInputHistory from "../clearInputHistory";
import { post } from "../submitForm";

const form = document.getElementById('form');
const username = document.getElementById('validationUsername');
const usernameFeedback = document.getElementById('usernameFeedback');
const password = document.getElementById('validationPassword');
const passwordFeedback = document.getElementById('passwordFeedback');
const confirmPassword = document.getElementById('confirmPassword');
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
const birthday = document.getElementById('validationBirthday');
const birthdayFeedback = document.getElementById('birthdayFeedback');
const email = document.getElementById('validationEmail');
const emailFeedback = document.getElementById('emailFeedback');
const mobile = document.getElementById('validationMobile');
const mobileFeedback = document.getElementById('mobileFeedback');
const submitButton = document.getElementById('submitButton');
const submittingButton = document.getElementById('submittingButton');

const inputs = [
    username,  password,  confirmPassword,
    familyName,  middleName, givenName,
    passportType,  passportNumber,
    gender, birthday,
    email, mobile,
];

new ClearInputHistory(inputs);

const feedbacks = [
    usernameFeedback, passwordFeedback,
    familyNameFeedback, middleNameFeedback, givenNameFeedback,
    passportTypeFeedback, passportNumberFeedback,
    genderFeedback, birthdayFeedback,
    emailFeedback, mobileFeedback,
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
    for(let feedback of feedbacks) {
        feedback.className = 'valid-feedback';
        feedback.innerText = 'Looks good!'
    }
    if(username.validity.valueMissing) {
        username.classList.add('is-invalid');
        usernameFeedback.className = 'invalid-feedback';
        usernameFeedback.innerText = 'The username field is required.';
    } else if(username.validity.tooShort) {
        username.classList.add('is-invalid');
        usernameFeedback.className = 'invalid-feedback';
        usernameFeedback.innerText = `The username field must be at least ${username.minLength} characters.`;
    } else if(username.validity.tooLong) {
        username.classList.add('is-invalid');
        usernameFeedback.className = 'invalid-feedback';
        usernameFeedback.innerText = `The username field must not be greater than ${username.maxLength} characters.`;
    }
    if(password.validity.valueMissing) {
        password.classList.add('is-invalid');
        passwordFeedback.className = 'invalid-feedback';
        passwordFeedback.innerText = 'The password field is required.';
    } else if(password.validity.tooShort) {
        password.classList.add('is-invalid');
        passwordFeedback.className = 'invalid-feedback';
        passwordFeedback.innerText = `The password field must be at least ${password.minLength} characters.`;
    } else if(password.validity.tooLong) {
        password.classList.add('is-invalid');
        passwordFeedback.className = 'invalid-feedback';
        passwordFeedback.innerText = `The password field must not be greater than ${password.maxLength} characters.`;
    } else if(password.value != confirmPassword.value) {
        password.classList.add('is-invalid');
        confirmPassword.classList.add('is-invalid');
        passwordFeedback.innerText = 'The password confirmation does not match.';
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
    if(birthday.validity.valueMissing) {
        birthday.classList.add('is-invalid');
        birthdayFeedback.className = 'invalid-feedback';
        birthdayFeedback.innerText = 'The birthday field is required.';
    } else if(birthday.validity.rangeOverflow) {
        birthday.classList.add('is-invalid');
        birthdayFeedback.className = 'invalid-feedback';
        birthdayFeedback.innerText = `The birthday not be greater than ${birthday.max} characters.`;
    }
    if(email.value) {
        if(email.validity.tooLong) {
            email.classList.add('is-invalid');
            emailFeedback.className = 'invalid-feedback';
            emailFeedback.innerText = `The email must not be greater than ${email.maxLength} characters.`;
        } else if(email.validity.typeMismatch) {
            email.classList.add('is-invalid');
            emailFeedback.className = 'invalid-feedback';
            emailFeedback.innerText = `The email must be a valid email address.`;
        }
    }
    if(mobile.value) {
        if(mobile.validity.tooShort) {
            mobile.classList.add('is-invalid');
            mobileFeedback.className = 'invalid-feedback';
            mobileFeedback.innerText = `The mobile must be at least ${mobile.minLength} characters.`;
        } else if(mobile.validity.tooLong) {
            mobile.classList.add('is-invalid');
            mobileFeedback.className = 'invalid-feedback';
            mobileFeedback.innerText = `The mobile must not be greater than ${mobile.maxLength} characters.`;
        } else if(mobile.validity.typeMismatch) {
            mobile.classList.add('is-invalid');
            mobileFeedback.className = 'invalid-feedback';
            mobileFeedback.innerText = `The email must be a valid email address.`;
        }
    }
    for(let input of inputs) {
        if(!input.classList.contains('is-invalid')) {
            input.classList.add('is-valid');
        }
    }
    return !hasError();
}

function successCallback(response) {
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
                case 'username':
                    input = username;
                    feedback = usernameFeedback;
                    break;
                case 'password':
                    input = password;
                    feedback = passwordFeedback;
                    break;
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
                case 'birthday':
                    input = birthday;
                    feedback = birthdayFeedback;
                    break;
                case 'email':
                    input = email;
                    feedback = emailFeedback;
                    break;
                case 'mobile':
                    input = mobile;
                    feedback = mobileFeedback;
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
    submittingButton.hidden = true;
    submitButton.hidden = false;
}

form.addEventListener(
    'submit', function (event) {
        event.preventDefault();
        if(submittingButton.hidden) {
            if(validation()) {
                submitButton.hidden = true;
                submittingButton.hidden = false;
                let data = {
                    username: username.value,
                    password: password.value,
                    password_confirmation: confirmPassword.value,
                    family_name: familyName.value,
                    middle_name: middleName.value,
                    given_name: givenName.value,
                    gender: gender.value,
                    passport_type_id: passportType.value,
                    passport_number: passportNumber.value,
                    birthday: birthday.value,
                    email: email.value,
                    mobile: mobile.value,
                }
                post(form.action, successCallback, failCallback, 'post', data);
            }
        }
    }
);
