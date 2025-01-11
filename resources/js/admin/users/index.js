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
const genderFeedback = document.getElementById('genderFeedback');
const birthday = document.getElementById('validationBirthday');
const birthdayFeedback = document.getElementById('birthdayFeedback');
const email = document.getElementById('validationEmail');
const emailFeedback = document.getElementById('emailFeedback');
const mobile = document.getElementById('validationMobile');
const mobileFeedback = document.getElementById('mobileFeedback');

var inputs = [
    familyName,  middleName, givenName,
    passportType,  passportNumber, birthday,
    email, mobile,
];

const feedbacks = [
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
    if(familyName.value && familyName.validity.tooLong) {
        familyName.classList.add('is-invalid');
        familyNameFeedback.className = 'invalid-feedback';
        familyNameFeedback.innerText = `The family name must not be greater than ${familyName.maxLength} characters.`;
    }
    if(middleName.value && middleName.validity.tooLong) {
        middleName.classList.add('is-invalid');
        middleNameFeedback.className = 'invalid-feedback';
        middleNameFeedback.innerText = `The middle name must not be greater than ${middleName.maxLength} characters.`;
    }
    if(givenName.value && givenName.validity.tooLong) {
        givenName.classList.add('is-invalid');
        givenNameFeedback.className = 'invalid-feedback';
        givenNameFeedback.innerText = `The given name must not be greater than ${givenName.maxLength} characters.`;
    }
    if(passportType.value && passportNumber.value) {
        if(passportNumber.validity.tooShort) {
            passportNumber.classList.add('is-invalid');
            passportNumberFeedback.className = 'invalid-feedback';
            passportNumberFeedback.innerText = `The passport number must be at least ${passportNumber.minLength} characters.`;
        } else if(passportNumber.validity.tooLong) {
            passportNumber.classList.add('is-invalid');
            passportNumberFeedback.className = 'invalid-feedback';
            passportNumberFeedback.innerText = `The passport number must not be greater than ${passportNumber.maxLength} characters.`;
        }
    }
    if(birthday.value && birthday.validity.rangeOverflow) {
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

form.addEventListener(
    'submit', function (event) {
        if(! validation()) {
            event.preventDefault();
        }
    }
);
