import { post } from "../..//submitForm";

const editForm = document.getElementById('form');

const editButton = document.getElementById('editButton');
const saveButton = document.getElementById('saveButton');
const cancelButton = document.getElementById('cancelButton');
const savingButton = document.getElementById('savingButton');

const showUsername = document.getElementById('showUsername');
const usernameInput = document.getElementById('validationUsername');
const usernameFeedback = document.getElementById('usernameFeedback');

const showFamilyName = document.getElementById('showFamilyName');
const familyNameInput = document.getElementById('validationFamilyName');
const familyNameFeedback = document.getElementById('familyNameFeedback');

const showMiddleName = document.getElementById('showMiddleName');
const middleNameInput = document.getElementById('validationMiddleName');
const middleNameFeedback = document.getElementById('middleNameFeedback');

const showGivenName = document.getElementById('showGivenName');
const givenNameInput = document.getElementById('validationGivenName');
const givenNameFeedback = document.getElementById('givenNameFeedback');

const showPassportType = document.getElementById('showPassportType');
const passportTypeInput = document.getElementById('validationPassportType');
const passportTypeFeedback = document.getElementById('passportTypeFeedback');

const showPassportNumber = document.getElementById('showPassportNumber');
const passportNumberInput = document.getElementById('validationPassportNumber');
const passportNumberFeedback = document.getElementById('passportNumberFeedback');

const showGender = document.getElementById('showGender');
const genderInput = document.getElementById('validationGender');
const genderFeedback = document.getElementById('genderFeedback');

const showBirthday = document.getElementById('showBirthday');
const birthdayInput = document.getElementById('validationBirthday');
const birthdayFeedback = document.getElementById('birthdayFeedback');

const showInfos = [
    showUsername,
    showFamilyName, showMiddleName, showGivenName,
    showPassportType, showPassportNumber,
    showGender, showBirthday,
];

const inputs = [
    usernameInput,
    familyNameInput, middleNameInput, givenNameInput,
    passportTypeInput, passportNumberInput,
    genderInput, birthdayInput,
];

let inputValues = {
    username: usernameInput.value,
    familyName: familyNameInput.value,
    middleName: middleNameInput.value,
    givenName: givenNameInput.value,
    passportType: passportTypeInput.value,
    passportNumber: passportNumberInput.value,
    gender: genderInput.value,
    birthday: birthdayInput.value,
};

const feedbacks = [
    usernameFeedback,
    familyNameFeedback, middleNameFeedback, givenNameFeedback,
    passportTypeFeedback, passportNumberFeedback,
    genderFeedback, birthdayFeedback,
];

const gendersDatalist = document.getElementById('genders');

let genders = [];

for(let option of gendersDatalist.options) {
    genders.push(option.value);
}

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

editButton.addEventListener(
    'click', function() {
        editButton.hidden = true;
        for(let showDiv of showInfos) {
            showDiv.hidden = true;
        }
        for(let input of inputs) {
            input.hidden = false;
        }
        saveButton.hidden = false;
        cancelButton.hidden = false;
        return false;
    }
);

function fillInputValues() {
    usernameInput.value = inputValues.username;
    familyNameInput.value = inputValues.familyName;
    middleNameInput.value = inputValues.middleName;
    givenNameInput.value = inputValues.givenName;
    passportTypeInput.value = inputValues.passportType;
    passportNumberInput.value = inputValues.passportNumber;
    genderInput.value = inputValues.gender;
    birthdayInput.value = inputValues.birthday;
}

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
    if(usernameInput.validity.valueMissing) {
        usernameInput.classList.add('is-invalid');
        usernameFeedback.className = 'invalid-feedback';
        usernameFeedback.innerText = 'The username field is required.';
    } else if(usernameInput.validity.tooShort) {
        usernameInput.classList.add('is-invalid');
        usernameFeedback.className = 'invalid-feedback';
        usernameFeedback.innerText = `The username field must be at least ${username.minLength} characters.`;
    } else if(usernameInput.validity.tooLong) {
        usernameInput.classList.add('is-invalid');
        usernameFeedback.className = 'invalid-feedback';
        usernameFeedback.innerText = `The username field must not be greater than ${username.maxLength} characters.`;
    }
    if(familyNameInput.validity.valueMissing) {
        familyNameInput.classList.add('is-invalid');
        familyNameFeedback.className = 'invalid-feedback';
        familyNameFeedback.innerText = 'The family name field is required.';
    } else if(familyNameInput.validity.tooLong) {
        familyNameInput.classList.add('is-invalid');
        familyNameFeedback.className = 'invalid-feedback';
        familyNameFeedback.innerText = `The family name not be greater than ${familyName.maxLength} characters.`;
    }
    if(middleNameInput.value && middleNameInput.validity.tooLong) {
        middleNameInput.classList.add('is-invalid');
        middleNameFeedback.className = 'invalid-feedback';
        middleNameFeedback.innerText = `The middle name not be greater than ${middleName.maxLength} characters.`;
    }
    if(givenNameInput.validity.valueMissing) {
        givenNameInput.classList.add('is-invalid');
        givenNameFeedback.className = 'invalid-feedback';
        givenNameFeedback.innerText = 'The given name field is required.';
    } else if(givenNameInput.validity.tooLong) {
        givenNameInput.classList.add('is-invalid');
        givenNameFeedback.className = 'invalid-feedback';
        givenNameFeedback.innerText = `The given name not be greater than ${givenName.maxLength} characters.`;
    }
    if(passportTypeInput.validity.valueMissing) {
        passportTypeInput.classList.add('is-invalid');
        passportTypeFeedback.className = 'invalid-feedback';
        passportTypeFeedback.innerText = 'The passport type field is required.';
    }
    if(passportNumberInput.validity.valueMissing) {
        passportNumberInput.classList.add('is-invalid');
        passportNumberFeedback.className = 'invalid-feedback';
        passportNumberFeedback.innerText = 'The passport number field is required.';
    } else if(passportNumberInput.validity.tooShort) {
        passportNumberInput.classList.add('is-invalid');
        passportNumberFeedback.className = 'invalid-feedback';
        passportNumberFeedback.innerText = `The passport number must be at least ${passportNumber.minLength} characters.`;
    } else if(passportNumberInput.validity.tooLong) {
        passportNumberInput.classList.add('is-invalid');
        passportNumberFeedback.className = 'invalid-feedback';
        passportNumberFeedback.innerText = `The passport number not be greater than ${passportNumber.maxLength} characters.`;
    }
    if(genderInput.validity.valueMissing) {
        genderInput.classList.add('is-invalid');
        genderFeedback.className = 'invalid-feedback';
        genderFeedback.innerText = 'The gender field is required.';
    } else if(genderInput.validity.tooLong) {
        genderInput.classList.add('is-invalid');
        genderFeedback.className = 'invalid-feedback';
        genderFeedback.innerText = `The gender not be greater than ${gender.maxLength} characters.`;
    }
    if(birthdayInput.validity.valueMissing) {
        birthdayInput.classList.add('is-invalid');
        birthdayFeedback.className = 'invalid-feedback';
        birthdayFeedback.innerText = 'The birthday field is required.';
    } else if(birthdayInput.validity.rangeOverflow) {
        birthdayInput.classList.add('is-invalid');
        birthdayFeedback.className = 'invalid-feedback';
        birthdayFeedback.innerText = `The birthday not be greater than ${birthday.max} characters.`;
    }
    for(let input of inputs) {
        if(!input.classList.contains('is-invalid')) {
            input.classList.add('is-valid');
        }
    }
    return !hasError();
}

function enableEditForm() {
    usernameInput.disabled = false;
    familyNameInput.disabled = false;
    middleNameInput.disabled = false;
    givenNameInput.disabled = false;
    genderInput.disabled = false;
    passportTypeInput.disabled = false;
    passportNumberInput.disabled = false;
    birthdayInput.disabled = false;
}

function successCallback(response) {
    for(let input of inputs) {
        input.classList.remove('is-valid');
        input.classList.remove('is-invalid');
        input.hidden = true;
    }
    inputValues.username = response.data.username;
    inputValues.familyName = response.data.family_name;
    inputValues.middleName = response.data.middle_name;
    inputValues.givenName = response.data.given_name;
    inputValues.passportType = response.data.passport_type_id;
    inputValues.passportNumber = response.data.passport_number;
    inputValues.gender = response.data.gender;
    inputValues.birthday = response.data.birthday;
    for(let feedback of feedbacks) {
        feedback.className = 'valid-feedback';
        feedback.innerText = 'Looks good!'
    }
    fillInputValues();
    if(!genders.includes(response.data.gender)) {
        genders.push(response.data.gender);
        newOption = document.createElement('option');
        newOption.value = response.data.gender;
        gendersDatalist.appendChild(newOption);
    }
    showUsername.innerText = response.data.username;
    showFamilyName.innerText = response.data.family_name;
    showMiddleName.innerText = response.data.middle_name;
    showGivenName.innerText = response.data.given_name;
    showPassportType.innerText = passportTypeInput.options[passportTypeInput.selectedIndex].text;
    showPassportNumber.innerText = response.data.passport_number;
    showGender.innerText = response.data.gender;
    showBirthday.innerText = response.data.birthday;
    submitting = '';
    enableEditForm();
    enableSubmitting();
    for(let showDiv of showInfos) {
        showDiv.hidden = false;
    }
    savingButton.hidden = true;
    editButton.hidden = false;
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
                    input = usernameInput;
                    feedback = usernameFeedback;
                    break;
                case 'family_name':
                    input = familyNameInput;
                    feedback = familyNameFeedback;
                    break;
                case 'middle_name':
                    input = middleNameInput;
                    feedback = middleNameFeedback;
                    break;
                case 'given_name':
                    input = givenNameInput;
                    feedback = givenNameFeedback;
                    break;
                case 'passport_type_id':
                    input = passportTypeInput;
                    feedback = passportTypeFeedback;
                    break;
                case 'passport_number':
                    input = passportNumberInput;
                    feedback = passportNumberFeedback;
                    break;
                case 'gender':
                    input = genderInput;
                    feedback = genderFeedback;
                    break;
                case 'birthday':
                    input = birthdayInput;
                    feedback = birthdayFeedback;
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
    submitting = '';
    enableEditForm();
    enableSubmitting();
    savingButton.hidden = true;
    saveButton.hidden = false;
    cancelButton.hidden = false;
}

editForm.addEventListener(
    'submit', function (event) {
        event.preventDefault();
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'updateProfile'+submitAt;
            disableSubmitting();
            if(submitting == 'updateProfile'+submitAt) {
                if(validation()) {
                    usernameInput.disabled = true;
                    familyNameInput.disabled = true;
                    middleNameInput.disabled = true;
                    givenNameInput.disabled = true;
                    genderInput.disabled = true;
                    passportTypeInput.disabled = true;
                    passportNumberInput.disabled = true;
                    birthdayInput.disabled = true;
                    saveButton.hidden = true;
                    cancelButton.hidden = true;
                    savingButton.hidden = false;
                    let data = {
                        username: usernameInput.value,
                        family_name: familyNameInput.value,
                        middle_name: middleNameInput.value,
                        given_name: givenNameInput.value,
                        gender: genderInput.value,
                        passport_type_id: passportTypeInput.value,
                        passport_number: passportNumberInput.value,
                        birthday: birthdayInput.value,
                    }
                    post(editForm.action, successCallback, failCallback, 'put', data);
                } else {
                    enableEditForm();
                    enableSubmitting();
                }
            }
        }
    }
);

submitting = '';