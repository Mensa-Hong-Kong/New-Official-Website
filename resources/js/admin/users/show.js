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

function urlGetContactID(url) {
    return (new URL(url).pathname).match(/^\/admin\/contacts\/([0-9]+).*/i)[1];
}

function updateVerifyContactStatusButton(button, status) {
    if(stringToBoolean(button.value) == status) {
        button.value = status ? 0 : 1;
        if(status) {
            button.innerHTML = 'Verified';
            button.classList.remove('btn-danger');
            button.classList.add('btn-success');
        } else {
            button.innerHTML = 'Not Verified';
            button.classList.remove('btn-success');
            button.classList.add('btn-danger');
        }
    }
}

function updateContactDefaultStatusButton(button, status) {
    if(stringToBoolean(button.value) == status) {
        if(status) {
            button.innerHTML = 'Default';
            button.classList.remove('btn-danger');
            button.classList.add('btn-success');
        } else {
            button.innerHTML = 'Non Default';
            button.classList.remove('btn-success');
            button.classList.add('btn-danger');
        }
        button.value = status ? 0 : 1;
    }
}

function changeVerifyContactStatusSuccessCallbacke(response) {
    let id = urlGetContactID(response.request.responseURL);
    document.getElementById('changingVerifyContactStatus'+id).hidden = true;
    let input = document.getElementById('verifyContactStatus'+id);
    document.getElementById('isVerifiedContactCheckbox'+id).checked = response.data.status;
    if(! response.data.status) {
        let defaultButton = document.getElementById('contactDefaultStatus'+id);
        if(stringToBoolean(defaultButton.value) == false) {
            updateContactDefaultStatusButton(defaultButton, false);
        }
        document.getElementById('isDefaultContactCheckbox'+id).checked = false;
    }
    updateVerifyContactStatusButton(input, response.data.status);
    enableSubmitting();
    input.hidden = false;
}

function changeVerifyContactStatusFailCallbacke(error) {
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
                changeVerifyContactStatusSuccessCallbacke,
                changeVerifyContactStatusFailCallbacke,
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
        updateContactDefaultStatusButton(element, false);
    }
}

function changeContacDefaulttStatusSuccessCallbacke(response) {
    let id = urlGetContactID(response.request.responseURL);
    document.getElementById('changingContactDefaultStatus'+id).hidden = true;
    let input = document.getElementById('contactDefaultStatus'+id);
    if(response.data.status) {
        let verifiyButton = document.getElementById('verifyContactStatus'+id);
        if(stringToBoolean(verifiyButton.value) == true) {
            updateVerifyContactStatusButton(verifiyButton, true);
        }
        let name = document.getElementById('contactInput'+id).name;
        updateAllDefaultCheckboxToFalse(name);
        updateAllDefaultButtonToNonDefault(name);
        document.getElementById('isVerifiedContactCheckbox'+id).checked = true;
    }
    document.getElementById('isDefaultContactCheckbox'+id).checked = response.data.status;
    updateContactDefaultStatusButton(input, response.data.status);
    enableSubmitting();
    input.hidden = false;
}

function changeContactDefaultStatusFailCallbacke(error) {
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
                changeContacDefaulttStatusSuccessCallbacke,
                changeContactDefaultStatusFailCallbacke,
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
    document.getElementById('contact'+id).innerText = response.data.contact;
    let contact = document.getElementById('contactInput'+id);
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
    updateContactDefaultStatusButton(
        document.getElementById('contactDefaultStatus'+id),
        response.data.is_default
    );
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
    document.getElementById('showContactRow'+id).remove();
    document.getElementById('editContactForm'+id).remove();
    enableSubmitting();
}

function deleteContactFailCallback(error) {
    let id = urlGetContactID(error.request.responseURL);
    let editContactButton = document.getElementById('editContact'+id)
    editContactButton.addEventListener('click', editContact);
    setDefaultButton.disabled = false;
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

submitting = '';
