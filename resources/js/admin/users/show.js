import { post } from "../../submitForm";
import stringToBoolean from "../../stringToBoolean";

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

const resettingPasswordButton = document.getElementById('resettingPassword');

function closeResettingPassword() {
    resettingPasswordButton.hidden = true;
    for(let button of document.getElementsByClassName('resetPassword')) {
        button.hidden = false;
    }
    editButton.disabled = false;
    enableSubmitting();
    submitting = '';
}

function resetPasswordSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    closeResettingPassword();
}

function resetPasswordFailCallback(error) {
    if(error.status == 422) {
        bootstrapAlert(error.data.errors.contact_type);
    }
    closeResettingPassword();
}

document.getElementById('resetPassword').addEventListener(
    'submit', function(event) {
        event.preventDefault();
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'resetPassword'+submitAt;
            disableSubmitting();
            if(submitting == 'resetPassword'+submitAt) {
                editButton.disabled = true;
                event.submitter.hidden = true;
                resettingPasswordButton.hidden = false;
                post(
                    event.target.action,
                    resetPasswordSuccessCallback, resetPasswordFailCallback,
                    'put', {contact_type: event.submitter.value}
                );
            }
        }
    }
);

function urlGetContactID(url) {
    return (new URL(url).pathname).match(/^\/admin\/contacts\/([0-9]+).*/i)[1];
}

function updateVerifyContactStatusButton(button, status) {
    if(stringToBoolean(button.value) == status) {
        button.value = status ? 0 : 1;
        if(status) {
            button.innerText = 'Verified';
            button.classList.remove('btn-danger');
            button.classList.add('btn-success');
        } else {
            button.innerText = 'Not Verified';
            button.classList.remove('btn-success');
            button.classList.add('btn-danger');
        }
    }
}

function updateContactDefaultStatusButton(id, status) {
    let defaultButton = document.getElementById('contactDefaultStatus'+id);
    let resetPasswordButton = document.getElementById(
        document.getElementById('contactInput'+id).name+'ResetPassword'
    );
    if(stringToBoolean(defaultButton.value) == status) {
        if(status) {
            defaultButton.innerText = 'Default';
            defaultButton.classList.remove('btn-danger');
            defaultButton.classList.add('btn-success');
            if(resetPasswordButton.classList.contains('btn-secondary')) {
                resetPasswordButton.classList.remove('btn-secondary');
                resetPasswordButton.classList.add('btn-danger');
                resetPasswordButton.disabled = false;
            }
        } else {
            defaultButton.innerText = 'Non Default';
            defaultButton.classList.remove('btn-success');
            defaultButton.classList.add('btn-danger');
            if(resetPasswordButton.classList.contains('btn-danger')) {
                resetPasswordButton.classList.remove('btn-danger');
                resetPasswordButton.classList.add('btn-secondary');
                resetPasswordButton.disabled = true;
            }
        }
        defaultButton.value = status ? 0 : 1;
    }
}

function changeVerifyContactStatusSuccessCallback(response) {
    let id = urlGetContactID(response.request.responseURL);
    document.getElementById('changingVerifyContactStatus'+id).hidden = true;
    let input = document.getElementById('verifyContactStatus'+id);
    document.getElementById('isVerifiedContactCheckbox'+id).checked = response.data.status;
    if(! response.data.status) {
        if(
            stringToBoolean(
                document.getElementById('contactDefaultStatus'+id).value
            ) == false
        ) {
            updateContactDefaultStatusButton(id, false);
        }
        document.getElementById('isDefaultContactCheckbox'+id).checked = false;
    }
    updateVerifyContactStatusButton(input, response.data.status);
    enableSubmitting();
    input.hidden = false;
}

function changeVerifyContactStatusFailCallback(error) {
    if(error.response.data.status) {
        bootstrapAlert(error.response.data.status);
    }
    document.getElementById('changingVerifyContactStatus'+id).hidden = true;
    enableSubmitting();
    document.getElementById('verifyContactStatus'+id).hidden = false;
}

function changeVerifyContactStatus(event) {
    event.preventDefault();
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'changeVerifyContactStatus'+submitAt;
        let id = event.target.id.replace('changeVerifyContactStatusForm', '');
        disableSubmitting();
        if(submitting == 'changeVerifyContactStatus'+submitAt) {
            let input = document.getElementById('verifyContactStatus'+id);
            input.hidden = true;
            document.getElementById('changingVerifyContactStatus'+id).hidden = false;
            let data = {status: stringToBoolean(input.value)};
            post(
                event.target.action,
                changeVerifyContactStatusSuccessCallback,
                changeVerifyContactStatusFailCallback,
                'put', data
            );
        }
    }
}

function updateAllDefaultCheckboxToFalse(type) {
    for(let checkbox of document.getElementsByClassName(type+'DefaultContactCheckbox')) {
        checkbox.checked = false;
    }
}

function updateAllDefaultButtonToNonDefault(type) {
    for(let element of document.getElementsByClassName(type+'DefaultContact')) {
        updateContactDefaultStatusButton(
            element.id.replace('contactDefaultStatus', ''), false
        );
    }
}

function changeContactDefaultStatusSuccessCallback(response) {
    let id = urlGetContactID(response.request.responseURL);
    document.getElementById('changingContactDefaultStatus'+id).hidden = true;
    let name = document.getElementById('contactInput'+id).name;
    if(response.data.status) {
        let verifyButton = document.getElementById('verifyContactStatus'+id);
        if(stringToBoolean(verifyButton.value) == true) {
            updateVerifyContactStatusButton(verifyButton, true);
        }
        updateAllDefaultCheckboxToFalse(name);
        updateAllDefaultButtonToNonDefault(name);
        document.getElementById('isVerifiedContactCheckbox'+id).checked = true;
    }
    document.getElementById('isDefaultContactCheckbox'+id).checked = response.data.status;
    updateContactDefaultStatusButton(id, response.data.status);
    enableSubmitting();
    document.getElementById('contactDefaultStatus'+id).hidden = false;
}

function changeContactDefaultStatusFailCallback(error) {
    if(error.response.data.status) {
        bootstrapAlert(error.response.data.status);
    }
    document.getElementById('changingContactDefaultStatus'+id).hidden = true;
    enableSubmitting();
    document.getElementById('contactDefaultStatus'+id).hidden = false;
}

function changeContactDefaultStatus(event) {
    event.preventDefault();
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'changeContactDefaultStatus'+submitAt;
        let id = event.target.id.replace('changeContactDefaultStatusForm', '');
        disableSubmitting();
        if(submitting == 'changeContactDefaultStatus'+submitAt) {
            let input = document.getElementById('contactDefaultStatus'+id);
            input.hidden = true;
            document.getElementById('changingContactDefaultStatus'+id).hidden = false;
            let data = {status: stringToBoolean(input.value)};
            post(
                event.target.action,
                changeContactDefaultStatusSuccessCallback,
                changeContactDefaultStatusFailCallback,
                'put', data
            );
        }
    }
}

function closeEdit(id) {
    document.getElementById('editContactForm'+id).hidden = true;
    let contact = document.getElementById('contactInput'+id);
    contact.value = contact.dataset.value;
    document.getElementById('isVerifiedContactCheckbox'+id).checked = ! stringToBoolean(
        document.getElementById('verifyContactStatus'+id).value
    );
    document.getElementById('isDefaultContactCheckbox'+id).checked = ! stringToBoolean(
        document.getElementById('contactDefaultStatus'+id).value
    );
    document.getElementById('showContactRow'+id).hidden = false;
}

function cancelEditContact(event) {
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'cancelEditContact'+submitAt;
        let id = event.target.id.replace('cancelEditContact', '');
        if(submitting == 'cancelEditContact'+submitAt) {
            closeEdit(id);
            submitting = '';
        }
    }
}

function contactValidation(input) {
    if(input.validity.valueMissing) {
        bootstrapAlert(`The ${input.name} field is required.`);
        return false;
    }
    if(input.name == 'mobile' && input.validity.tooShort) {
        bootstrapAlert(`The ${input.name} be at least ${input.minLength} characters.`);
        return false;
    }
    if(input.validity.tooLong) {
        bootstrapAlert(`The ${input.name} must not be greater than ${input.maxLength} characters.`);
        return false;
    }
    if(input.name == 'email' && input.validity.typeMismatch) {
        bootstrapAlert(`The email must be a valid email address.`);
        return false;
    }
    return true;
}

function updateContactSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    let id = urlGetContactID(response.request.responseURL);
    let contact = document.getElementById('contactInput'+id);
    document.getElementById('contact'+id).innerText = response.data[contact.name];
    contact.dataset.value = response.data[contact.name];
    let isVerified = document.getElementById('isVerifiedContactCheckbox'+id);
    updateVerifyContactStatusButton(
        document.getElementById('verifyContactStatus'+id),
        response.data.is_verified
    );
    let isDefault = document.getElementById('isDefaultContactCheckbox'+id);
    if(response.data.is_default) {
        updateAllDefaultCheckboxToFalse(contact.name);
        updateAllDefaultButtonToNonDefault(contact.name);
    }
    isDefault.checked = response.data.is_default;
    updateContactDefaultStatusButton(id, response.data.is_default);
    document.getElementById('savingContact'+id).hidden = true;
    closeEdit(id);
    document.getElementById('saveContact'+id).hidden = false;
    document.getElementById('cancelEditContact'+id).hidden = false;
    contact.disabled = false;
    isVerified.disabled = false;
    isDefault.disabled = false;
    enableSubmitting();
}

function updateContactFailCallback(error) {
    let id = urlGetContactID(error.request.responseURL);
    if(error.status == 422) {
        let input = document.getElementById('verifyCodeInput'+id);
        bootstrapAlert(error.response.data.errors[input.name]);
    }
    document.getElementById('savingContact'+id).hidden = true;
    document.getElementById('saveContact'+id).hidden = false;
    document.getElementById('cancelEditContact'+id).hidden = false;
    document.getElementById('contactInput'+id).disabled = false;
    document.getElementById('isVerifiedContactCheckbox'+id).disabled = false;
    document.getElementById('isDefaultContactCheckbox'+id).disabled = false;
    enableSubmitting();
}

function updateContact(event) {
    event.preventDefault();
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'updateContact'+submitAt;
        let id = event.target.id.replace('editContactForm', '');
        let contact = document.getElementById('contactInput'+id);
        let isVerified = document.getElementById('isVerifiedContactCheckbox'+id);
        let isDefault = document.getElementById('isDefaultContactCheckbox'+id);
        disableSubmitting();
        if(submitting == 'updateContact'+submitAt) {
            if(contactValidation(contact)) {
                contact.disabled = true;
                isVerified.disabled = true;
                isDefault.disabled = true;
                document.getElementById('saveContact'+id).hidden = true;
                document.getElementById('cancelEditContact'+id).hidden = true;
                document.getElementById('savingContact'+id).hidden = false;
                let data = {
                    is_verified: isVerified.checked,
                    is_default: isDefault.checked,
                };
                data[contact.name] = contact.value;
                post(
                    event.target.action,
                    updateContactSuccessCallback,
                    updateContactFailCallback,
                    'put', data
                );
            } else {
                submitting = '';
                enableSubmitting();
            }
        }
    }
}

function editContact(event) {
    let id = event.target.id.replace('editContact', '');
    document.getElementById('showContactRow'+id).hidden = true;
    document.getElementById('editContactForm'+id).hidden = false;
}

function deleteContactSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    let id =  urlGetContactID(response.request.responseURL);
    if(! stringToBoolean(document.getElementById('contactDefaultStatus'+id).value)) {
        let resetPasswordButton = document.getElementById(
            document.getElementById('contactInput'+id).name+'ResetPassword'
        );
        if(resetPasswordButton.classList.contains('btn-danger')) {
            resetPasswordButton.classList.remove('btn-danger');
            resetPasswordButton.classList.add('btn-secondary');
            resetPasswordButton.disabled = true;
        }
    }
    document.getElementById('showContactRow'+id).remove();
    document.getElementById('editContactForm'+id).remove();
    enableSubmitting();
}

function deleteContactFailCallback(error) {
    let id = urlGetContactID(error.request.responseURL);
    let editContactButton = document.getElementById('editContact'+id)
    editContactButton.addEventListener('click', editContact);
    editContactButton.disabled = false;
    enableSubmitting();
}

function confirmedDeleteContact(event) {
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'deleteContactForm'+submitAt;
        disableSubmitting();
        let id = event.target.id.replace('deleteContactForm', '');
        let editContactButton = document.getElementById('editContact'+id)
        editContactButton.removeEventListener('click', editContact);
        editContactButton.disabled = true;
        if(submitting == 'deleteContactForm'+submitAt) {
            post(
                event.target.action,
                deleteContactSuccessCallback,
                deleteContactFailCallback,
                'delete'
            );
        } else {
            editContactButton.addEventListener('click', editContact);
            editContactButton.disabled = false;
        }
    }
}

function deleteContact(event) {
    event.preventDefault();
    let id = event.target.id.replace('deleteContactForm', '');
    let contactInput = document.getElementById('contactInput'+id);
    let message = `Are you sure to delete the ${contactInput.name} of ${contactInput.dataset.value}?`;
    bootstrapConfirm(message, confirmedDeleteContact, event);
}

function setContactEventListeners(loader) {
    let id = loader.id.replace('contactLoader', '');
    document.getElementById('changeVerifyContactStatusForm'+id).addEventListener(
        'submit', changeVerifyContactStatus
    )
    document.getElementById('changeContactDefaultStatusForm'+id).addEventListener(
        'submit', changeContactDefaultStatus
    )
    document.getElementById('editContactForm'+id).addEventListener(
        'submit', updateContact
    );
    document.getElementById('isDefaultContactCheckbox'+id).addEventListener(
        'change', function(event) {
            if(event.target.checked) {
                document.getElementById('isVerifiedContactCheckbox'+id).checked = true;
            }
        }
    );
    document.getElementById('isVerifiedContactCheckbox'+id).addEventListener(
        'change', function(event) {
            if(! event.target.checked) {
                document.getElementById('isDefaultContactCheckbox'+id).checked = false;
            }
        }
    );
    document.getElementById('cancelEditContact'+id).addEventListener(
        'click', cancelEditContact
    );
    let editContactButton = document.getElementById('editContact'+id);
    editContactButton.addEventListener(
        'click', editContact
    );
    document.getElementById('deleteContactForm'+id).addEventListener(
        'submit', deleteContact
    );
    loader.remove();
    document.getElementById('verifyContactStatus'+id).hidden = false;
    document.getElementById('contactDefaultStatus'+id).hidden = false;
    editContactButton.hidden = false;
    document.getElementById('deleteContact'+id).hidden = false;
}

document.querySelectorAll('.contactLoader').forEach(
    (loader) => {
        setContactEventListeners(loader);
    }
);

function createContactSuccess(response) {
    let id = response.data.id;
    let type = response.data.type;
    let contactInput = document.getElementById(type+'ContactInput')
    let isVerified = document.getElementById(type+'IsVerifiedCheckbox');
    let isDefault = document.getElementById(type+'IsDefaultCheckbox');
    contactInput.value = '';
    isVerified.checked = false;
    isDefault.checked = false;
    enableSubmitting();
    contactInput.disabled = false;
    isVerified.disabled = false;
    isDefault.disabled = false;
    document.getElementById(type+'CreatingContact').hidden = true;
    document.getElementById(type+'CreateButton').hidden = false;
    let contact = response.data.contact;
    let token = document.querySelector("meta[name='csrf-token']").getAttribute("content");
    let formElement = document.createElement('form');
    formElement.className = "row g-3";
    formElement.id = 'editContactForm'+id;
    formElement.method = 'POST';
    formElement.hidden = true;
    formElement.action = response.data.update_url;
    let formInnerHtml = `
        <input type="hidden" name="_token" value="${token}">
        <input type="hidden" name="_method" value="put">
        <input id="contactInput${id}" class="col-md-3"
    `;
    switch(type) {
        case 'email':
            formInnerHtml += `
                    type="email" name="email" maxlength="320"
                    placeholder="dammy@example.com"
            `;
            break;
        case 'mobile':
            formInnerHtml += `
                    type="tel" name="mobile" minlength="5" maxlength="15"
                    placeholder="85298765432"
            `;
            break;
    }
    formInnerHtml += `
            value="${contact}"
            data-value="${contact}" required />
        <div class=" col-md-2">
            <input type="checkbox" class="btn-check" id="isVerifiedContactCheckbox${id}" ${!response.data.is_verified ? 'checked' : ''})>
            <label class="form-control btn btn-outline-success" for="isVerifiedContactCheckbox${id}">Verified</label>
        </div>
        <div class=" col-md-2">
            <input type="checkbox" class="btn-check ${type}DefaultContactCheckbox" id="isDefaultContactCheckbox${id}" ${!response.data.is_default ? 'checked' : ''})>
            <label class="form-control btn btn-outline-success" for="isDefaultContactCheckbox${id}">Default</label>
        </div>
        <button class="btn btn-primary col-md-1 submitButton" id="saveContact${id}">Save</button>
        <button class="btn btn-danger col-md-1" id="cancelEditContact${id}" onclick="return false;">Cancel</button>
        <button class="btn btn-primary col-md-2" id="savingContact${id}" hidden disabled>
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Saving
        </button>
    `;
    formElement.innerHTML = formInnerHtml;
    document.getElementById(type).insertBefore(formElement, document.getElementById(type+'CreateForm'));
    let rowElement = document.createElement('div');
    rowElement.className = "row g-3";
    rowElement.id = 'showContactRow'+id;
    let rowInnerHtml = `
        <span class="col-md-3" id="contact${id}">${contact}</span>
        <form class="col-md-2" id="changeVerifyContactStatusForm${id}" method="POST"
            action="${response.data.verify_url}">
            <input type="hidden" name="_token" value="${token}">
            <input type="hidden" name="_method" value="put">
            <button id="verifyContactStatus${id}" hidden
                name="status" value="${response.data.is_verified ? '0' : '1'}"
                class="btn form-control ${response.data.is_verified ? 'btn-success' : 'btn-danger'} submitButton">
                ${response.data.is_verified ? 'Verified' : 'Not Verified'}
            </button>
            <button class="btn btn-primary form-control" id="changingVerifyContactStatus${id}" hidden disabled>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Changing...
            </button>
        </form>
        <form class="col-md-2" id="changeContactDefaultStatusForm${id}" method="POST"
            action="${response.data.default_url}">
            <input type="hidden" name="_token" value="${token}">
            <input type="hidden" name="_method" value="put">
            <button id="contactDefaultStatus${id}" hidden
                name="status" value="${response.data.is_default ? '0' : '1'}"
                class="btn form-control ${response.data.is_default ? 'btn-success' : 'btn-danger'} submitButton ${type}DefaultContact">
                ${response.data.is_default ? 'Default' : 'Non Default'}
            </button>
            <button class="btn btn-primary form-control" id="changingContactDefaultStatus${id}" hidden disabled>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Changing...
            </button>
        </form>
        <div class="contactLoader col-md-1" id="contactLoader${id}">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        </div>
        <button class="btn btn-primary col-md-1" id="editContact${id}" hidden>Edit</button>
        <form id="deleteContactForm${id}" method="POST" hidden
            action="${response.data.delete_url}">
            <input type="hidden" name="_token" value="${token}">
            <input type="hidden" name="_method" value="delete">
        </form>
        <button class="btn btn-danger col-md-1 submitButton" id="deleteContact${id}" form="deleteContactForm${id}" hidden>Delete</button>
    `;
    rowElement.innerHTML = rowInnerHtml;
    document.getElementById(type).insertBefore(rowElement, formElement);
    setContactEventListeners(document.getElementById('contactLoader'+id));
}

function createContactFail(error) {
    let type = submitting.match(/^([a-z]+)Create.*/i)[1];
    if(error.response.data.errors.message) {
        bootstrapAlert(error.response.data.errors.message);
    } else if(error.response.data.errors[type]){
        bootstrapAlert(error.response.data.errors[type]);
    } else {
        bootstrapAlert('The show.js missing create fail type handle, please contact us.')
    }
    document.getElementById(type+'ContactInput').disabled = false;
    document.getElementById(type+'IsVerifiedCheckbox').disabled = false;
    document.getElementById(type+'IsDefaultCheckbox').disabled = false;
    document.getElementById(type+'CreatingContact').hidden = true;
    document.getElementById(type+'CreateButton').hidden = false;
    submitting = '';
    enableSubmitting();
}

function createContact(event) {
    event.preventDefault();
    if(submitting == '') {
        let type = event.target.dataset.type;
        let contact = document.getElementById(type+'ContactInput');
        let isVerified = document.getElementById(type+'IsVerifiedCheckbox');
        let isDefault = document.getElementById(type+'IsDefaultCheckbox');
        let submitAt = Date.now();
        submitting = type+'Create'+submitAt;
        disableSubmitting();
        if(submitting == type+'Create'+submitAt) {
            if(contactValidation(contact)) {
                contact.disabled = true;
                isVerified.disabled = true;
                isDefault.disabled = true;
                document.getElementById(type+'CreateButton').hidden = true;
                document.getElementById(type+'CreatingContact').hidden = false;
                let data = {
                    user_id: window.location.pathname.match(/^\/admin\/users\/([0-9]+).*/i)[1],
                    is_verified: isVerified.checked,
                    is_default: isDefault.checked,
                    type: type,
                    contact: contact.value,
                };
                post(
                    event.target.action,
                    createContactSuccess,
                    createContactFail,
                    'post', data
                );
            } else {
                submitting = '';
                enableSubmitting();
            }
        }
    }
}

for(let form of document.getElementsByClassName('createContact')) {
    form.addEventListener('submit', createContact)
}

submitting = '';
