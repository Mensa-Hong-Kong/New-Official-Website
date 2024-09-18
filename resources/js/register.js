import axios from 'axios';
import ClearInputHistory from "./clearInputHistory";

const form = document.getElementById('form');
const username = document.getElementById('validationUsername');
const usernameFeedback = document.getElementById('usernameFeedback');
const password = document.getElementById('validationPassword');
const passwordFeedback = document.getElementById('passwordFeedback');
const confirmPassword = document.getElementById('validationConfirmPassword');
const confirmPasswordFeedback = document.getElementById('confirmPasswordFeedback');
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

new ClearInputHistory([
    username,  password,  confirmPassword,
    familyName,  middleName, givenName,
    passportType,  passportNumber,
    gender, birthday,
    email, mobile,
]);

const feedbacks = [
    usernameFeedback, passwordFeedback, confirmPasswordFeedback,
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

form.addEventListener(
    'submit', function (event) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.remove('was-validated');
        for(let feedback of feedbacks) {
            feedback.className = 'valid-feedback';
            feedback.innerText = 'Looks good!'
        }
        if(username.validity.valueMissing) {
            usernameFeedback.className = 'invalid-feedback';
            usernameFeedback.innerText = 'The username field is required';
        } else if(username.validity.tooShort) {
            console.log(123);
            usernameFeedback.className = 'invalid-feedback';
            usernameFeedback.innerText = `The username must be at least ${username.minLength} characters`;
        } else if(username.validity.tooLong) {
            usernameFeedback.className = 'invalid-feedback';
            usernameFeedback.innerText = `The username not be greater than ${username.maxLength} characters`;
        }
        if(password.validity.valueMissing) {
            passwordFeedback.className = 'invalid-feedback';
            passwordFeedback.innerText = 'The password field is required';
        } else if(password.validity.tooShort) {
            passwordFeedback.className = 'invalid-feedback';
            passwordFeedback.innerText = `The password must be at least ${password.minLength} characters`;
        } else if(password.validity.tooLong) {
            passwordFeedback.className = 'invalid-feedback';
            passwordFeedback.innerText = `The password not be greater than ${password.maxLength} characters`;
        }
        if(password.validity.valueMissing) {
            confirmPasswordFeedback.className = 'invalid-feedback';
            confirmPasswordFeedback.innerText = 'The password field is required';
        } else if(password.validity.tooShort) {
            confirmPasswordFeedback.className = 'invalid-feedback';
            confirmPasswordFeedback.innerText = `The password must be at least ${confirmPassword.minLength} characters`;
        } else if(password.validity.tooLong) {
            confirmPasswordFeedback.className = 'invalid-feedback';
            confirmPasswordFeedback.innerText = `The password not be greater than ${confirmPassword.maxLength} characters`;
        }
        if(password.value != confirmPasswordFeedback.value) {
            passwordFeedback.className = 'invalid-feedback';
            passwordFeedback.innerText = 'The password confirmation does not match.';
            confirmPasswordFeedback.className = 'invalid-feedback';
            confirmPasswordFeedback.innerText = 'The password confirmation does not match.';
        }
        if(familyName.validity.valueMissing) {
            familyNameFeedback.className = 'invalid-feedback';
            familyNameFeedback.innerText = 'The family name field is required';
        } else if(familyName.validity.tooLong) {
            familyNameFeedback.className = 'invalid-feedback';
            familyNameFeedback.innerText = `The family name not be greater than ${familyName.maxLength} characters`;
        }
        if(middleName.value && middleName.validity.tooLong) {
            middleNameFeedback.className = 'invalid-feedback';
            middleNameFeedback.innerText = `The middle name not be greater than ${middleName.maxLength} characters`;
        }
        if(givenName.validity.valueMissing) {
            givenNameFeedback.className = 'invalid-feedback';
            givenNameFeedback.innerText = 'The given name field is required';
        } else if(givenName.validity.tooLong) {
            givenNameFeedback.className = 'invalid-feedback';
            givenNameFeedback.innerText = `The given name not be greater than ${givenName.maxLength} characters`;
        }
        if(passportType.validity.valueMissing) {
            passportTypeFeedback.className = 'invalid-feedback';
            passportTypeFeedback.innerText = 'The passport type field is required';
        }
        if(passportNumber.validity.valueMissing) {
            passportNumberFeedback.className = 'invalid-feedback';
            passportNumberFeedback.innerText = 'The passport number field is required';
        } else if(passportNumber.validity.tooShort) {
            passportNumberFeedback.className = 'invalid-feedback';
            passportNumberFeedback.innerText = `The passport number must be at least ${passportNumber.minLength} characters`;
        } else if(passportNumber.validity.tooLong) {
            passportNumberFeedback.className = 'invalid-feedback';
            passportNumberFeedback.innerText = `The passport number not be greater than ${passportNumber.maxLength} characters`;
        }
        if(gender.validity.valueMissing) {
            genderFeedback.className = 'invalid-feedback';
            genderFeedback.innerText = 'The gender field is required';
        } else if(gender.validity.tooLong) {
            genderFeedback.className = 'invalid-feedback';
            genderFeedback.innerText = `The gender not be greater than ${gender.maxLength} characters`;
        }
        if(birthday.validity.valueMissing) {
            birthdayFeedback.className = 'invalid-feedback';
            birthdayFeedback.innerText = 'The birthday field is required';
        } else if(birthday.validity.rangeOverflow) {
            birthdayFeedback.className = 'invalid-feedback';
            birthdayFeedback.innerText = `The birthday not be greater than ${birthday.max} characters`;
        }
        if(email.value) {
            if(email.validity.tooLong) {
                emailFeedback.className = 'invalid-feedback';
                emailFeedback.innerText = `The email not be greater than ${email.maxLength} characters`;
            } else if(email.validity.typeMismatch) {
                emailFeedback.className = 'invalid-feedback';
                emailFeedback.innerText = `The email must be a valid email address`;
            }
        }
        form.classList.add('was-validated');
    }
);
