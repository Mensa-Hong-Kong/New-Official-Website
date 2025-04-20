import { post, get } from "@/submitForm";

const editForm = document.getElementById('form');

const editButton = document.getElementById('editButton');
const saveButton = document.getElementById('saveButton');
const cancelButton = document.getElementById('cancelButton');
const savingButton = document.getElementById('savingButton');

const showUsername = document.getElementById('showUsername');
const usernameInput = document.getElementById('validationUsername');
const usernameFeedback = document.getElementById('usernameFeedback');

const showPassword = document.getElementById('showPassword');
const passwordInput = document.getElementById('validationPassword');
const passwordFeedback = document.getElementById('passwordFeedback');

const newPasswordInput = document.getElementById('validationNewPassword');
const newPasswordFeedback = document.getElementById('newPasswordFeedback');
const confirmNewPasswordInput = document.getElementById('confirmNewPassword');

const showGender = document.getElementById('showGender');
const genderInput = document.getElementById('validationGender');
const genderFeedback = document.getElementById('genderFeedback');

const showBirthday = document.getElementById('showBirthday');
const birthdayInput = document.getElementById('validationBirthday');
const birthdayFeedback = document.getElementById('birthdayFeedback');

const newPasswordColumns = document.getElementsByClassName('newPasswordColumn');

const editInfoRemind = document.getElementById('editInfoRemind');

const showInfos = [
    showUsername, showPassword,
    showGender, showBirthday,
];

const inputs = [
    usernameInput, passwordInput,
    newPasswordInput, confirmNewPasswordInput,
    genderInput, birthdayInput,
];

let inputValues = {
    username: usernameInput.value,
    gender: genderInput.value,
    birthday: birthdayInput.value,
};

const feedbacks = [
    usernameFeedback, passwordFeedback, newPasswordFeedback,
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
        editInfoRemind.hidden = false;
        for(let column of newPasswordColumns) {
            column.hidden = false;
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
    passwordInput.value = '';
    newPasswordInput.value = '';
    confirmNewPasswordInput.value = '';
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
        for(let column of newPasswordColumns) {
            column.hidden = true;
        }
        editInfoRemind.hidden = true;
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
    if(usernameInput.value != showUsername.innerText || newPasswordInput.value || confirmNewPasswordInput.value) {
        if(passwordInput.validity.valueMissing) {
            passwordInput.classList.add('is-invalid');
            passwordFeedback.className = 'invalid-feedback';
            passwordFeedback.innerText = 'The password field is required when you change the username or password.';
        } else if(passwordInput.validity.tooShort) {
            passwordInput.classList.add('is-invalid');
            passwordFeedback.className = 'invalid-feedback';
            passwordFeedback.innerText = `The password field must be at least ${password.minLength} characters.`;
        } else if(passwordInput.validity.tooLong) {
            passwordInput.classList.add('is-invalid');
            passwordFeedback.className = 'invalid-feedback';
            passwordFeedback.innerText = `The password field must not be greater than ${password.maxLength} characters.`;
        }
        if(newPasswordInput.validity.tooShort) {
            newPasswordInput.classList.add('is-invalid');
            newPasswordFeedback.className = 'invalid-feedback';
            newPasswordFeedback.innerText = `The password field must be at least ${newPasswordInput.minLength} characters.`;
        } else if(newPasswordInput.validity.tooLong) {
            newPasswordInput.classList.add('is-invalid');
            newPasswordFeedback.className = 'invalid-feedback';
            newPasswordFeedback.innerText = `The password field must not be greater than ${newPasswordInput.maxLength} characters.`;
        } else if(newPasswordInput.value != confirmNewPasswordInput.value) {
            newPasswordInput.classList.add('is-invalid');
            newPasswordFeedback.className = 'invalid-feedback';
            newPasswordFeedback.innerText = 'The new password confirmation does not match.';
        }
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
    passwordInput.disabled = false;
    newPasswordInput.disabled = false;
    confirmNewPasswordInput.disabled = false;
    genderInput.disabled = false;
    birthdayInput.disabled = false;
}

function successCallback(response) {
    for(let input of inputs) {
        input.classList.remove('is-valid');
        input.classList.remove('is-invalid');
        input.hidden = true;
    }
    inputValues.username = response.data.username;
    inputValues.gender = response.data.gender;
    inputValues.birthday = response.data.birthday;
    for(let column of newPasswordColumns) {
        column.hidden = true;
    }
    editInfoRemind.hidden = true;
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
                case 'password':
                    input = passwordInput;
                    feedback = passwordFeedback;
                    break;
                case 'new_password':
                    input = newPasswordInput;
                    feedback = passwordFeedback;
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
                    passwordInput.disabled = true;
                    newPasswordInput.disabled = true;
                    confirmNewPasswordInput.disabled = true;
                    genderInput.disabled = true;
                    birthdayInput.disabled = true;
                    saveButton.hidden = true;
                    cancelButton.hidden = true;
                    savingButton.hidden = false;
                    let data = {
                        username: usernameInput.value,
                        gender: genderInput.value,
                        birthday: birthdayInput.value,
                    }
                    if(newPasswordInput.value || usernameInput.value != inputValues.username) {
                        data[password] = passwordInput.value;
                    }
                    if(newPasswordInput.value) {
                        data[new_password] = newPasswordInput.value;
                        data[new_password_confirmation] = confirmNewPasswordInput.value;
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
    return (new URL(url).pathname).match(/^\/contacts\/([0-9]+).*/i)[1];
}

function verified(id) {
    document.getElementById('verifyContactForm'+id).hidden = true;
    document.getElementById('requestingContactButton'+id).hidden = true;
    document.getElementById('verifyCodeInput'+id).disabled = false;
    let verifyButton = document.getElementById('verifyContactButton'+id);
    verifyButton.classList.remove('submitButton');
    verifyButton.classList.remove('btn-primary');
    verifyButton.classList.add('btn-secondary');
    verifyButton.innerText = "Verified";
    verifyButton.removeEventListener('click', verifyContact);
    verifyButton.disabled = false;
    verifyButton.hidden = false;
    document.getElementById('setDefault'+id).hidden = false;
    document.getElementById('editContact'+id).hidden = false;
    document.getElementById('deleteContact'+id).hidden = false;
}

function requestVerifyCodeSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    let id = urlGetContactID(response.request.responseURL);
    document.getElementById('requestingContactButton'+id).hidden = true;
    document.getElementById('verifyCodeInput'+id).disabled = false;
    document.getElementById('verifyCodeInput'+id).value = '';
    document.getElementById('verifyContactForm'+id).hidden = false;
    document.getElementById('requestNewVerifyCode'+id).hidden = false;
    document.getElementById('submitVerifyCode'+id).hidden = false;
    document.getElementById('cancelVerify'+id).hidden = false;
    enableSubmitting();
}

function requestNewVerifyCodeFailCallback(error) {
    let id = urlGetContactID(error.request.responseURL);
    document.getElementById('requestingContactButton'+id).hidden = true;
    document.getElementById('verifyCodeInput'+id).disabled = false;
    switch(error.status) {
        case 410:
            verified(id);
            break;
        case 429:
            document.getElementById('verifyContactForm'+id).hidden = true;
            document.getElementById('verifyContactButton'+id).hidden = false;
            break;
        default:
            document.getElementById('requestNewVerifyCode'+id).hidden = false;
            document.getElementById('submitVerifyCode'+id).hidden = false;
            document.getElementById('cancelVerify'+id).hidden = false;
            break;
    }
    enableSubmitting();
}

function requestNewVerifyCode(event) {
    let id = event.target.id.replace('requestNewVerifyCode', '');
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'requestNewVerifyCode'+submitAt;
        disableSubmitting();
        if(submitting == 'requestNewVerifyCode'+submitAt) {
            document.getElementById('verifyCodeInput'+id).disabled = true;
            document.getElementById('requestNewVerifyCode'+id).hidden = true;
            document.getElementById('submitVerifyCode'+id).hidden = true;
            document.getElementById('cancelVerify'+id).hidden = true;
            document.getElementById('requestingContactButton'+id).hidden = false;
            get(
                event.target.parentElement.dataset.requestVerifyCodeUrl,
                requestVerifyCodeSuccessCallback,
                requestNewVerifyCodeFailCallback
            );
        }
    }
}

function codeValidation(id) {
    let input = document.getElementById('verifyCodeInput'+id);
    if(input.validity.valueMissing) {
        bootstrapAlert('The code field is required.');
    } else if(input.validity.tooLong || input.validity.tooShort) {
        bootstrapAlert('The code field must be 6 characters.');
    } else if(input.validity.patternMismatch) {
        bootstrapAlert('The code field must only contain letters and numbers.');
    } else {
        return true;
    }
    document.getElementById('verifyCodeInput'+id).disabled = false;
    enableSubmitting();
    return false;
}

function submitVerifyCodeSuccessCallback(response) {
    bootstrapAlert(
        response.status == 201 ?
        response.data.message : response.data.success
    );
    let id = urlGetContactID(response.request.responseURL);
    document.getElementById('submittingContactButton'+id).hidden = true;
    verified(id);
    enableSubmitting();
}

function submitVerifyCodeFailCallback(error) {
    if(error.status == 422) {
        bootstrapAlert(error.response.data.errors.code);
    }
    let id = urlGetContactID(error.request.responseURL);
    document.getElementById('submittingContactButton'+id).hidden = true;
    if(
        error.status == 429 ||
        (error.status == 422 && error.response.data.errors.isFailedTooMany)
    ) {
        document.getElementById('verifyContactForm'+id).hidden = true;
        document.getElementById('verifyCodeInput'+id).value = '';
        document.getElementById('verifyContactButton'+id).hidden = true;
    } else {
        document.getElementById('requestNewVerifyCode'+id).hidden = false;
        document.getElementById('submitVerifyCode'+id).hidden = false;
        document.getElementById('cancelVerify'+id).hidden = false;
    }
    document.getElementById('verifyCodeInput'+id).disabled = false;
    enableSubmitting();
}

function submitVerifyCode(event) {
    event.preventDefault();
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'submitVerifyCode'+submitAt;
        let id = event.target.id.replace('verifyContactForm', '');
        disableSubmitting();
        if(submitting == 'submitVerifyCode'+submitAt) {
            if(codeValidation(id)) {
                document.getElementById('verifyCodeInput'+id).disabled = true;
                document.getElementById('verifyCodeInput'+id).disabled = true;
                document.getElementById('requestNewVerifyCode'+id).hidden = true;
                document.getElementById('submitVerifyCode'+id).hidden = true;
                document.getElementById('cancelVerify'+id).hidden = true;
                document.getElementById('submittingContactButton'+id).hidden = false;
                let data = {code: document.getElementById('verifyCodeInput'+id).value}
                post(
                    event.target.action,
                    submitVerifyCodeSuccessCallback,
                    submitVerifyCodeFailCallback,
                    'post', data
                );
            } else {
                submitting = '';
                enableSubmitting();
            }
        }
    }
}

function cancelVerifyContact(event) {
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'cancelVerifyContact'+submitAt;
        let id = event.target.id.replace('cancelVerify', '');
        if(submitting == 'cancelVerifyContact'+submitAt) {
            document.getElementById('verifyContactForm'+id).hidden = true;
            document.getElementById('verifyCodeInput'+id).value = '';
            document.getElementById('requestNewVerifyCode'+id).hidden = true;
            document.getElementById('submitVerifyCode'+id).hidden = true;
            document.getElementById('cancelVerify'+id).hidden = true;
            document.getElementById('verifyContactButton'+id).hidden = false;
            document.getElementById('editContact'+id).hidden = false;
            document.getElementById('deleteContact'+id).hidden = false;
            submitting = '';
        }
    }
}

function requestVerifyCodeFailCallback(error) {
    let id = urlGetContactID(error.request.responseURL);
    if(error.status == 410) {
        verified(id);
    } else {
        document.getElementById('requestingContactButton'+id).hidden = true;
        document.getElementById('verifyContactButton'+id).hidden = false;
        enableSubmitting();
    }
}

function verifyContact(event) {
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'verifyContact'+submitAt;
        disableSubmitting()
        if(submitting == 'verifyContact'+submitAt) {
            let id = event.target.id.replace('verifyContactButton', '');
            document.getElementById('verifyContactButton'+id).hidden = true;
            document.getElementById('editContact'+id).hidden = true;
            document.getElementById('deleteContact'+id).hidden = true;
            document.getElementById('requestingContactButton'+id).hidden = false;
            get(
                event.target.parentElement.dataset.requestVerifyCodeUrl,
                requestVerifyCodeSuccessCallback,
                requestVerifyCodeFailCallback
            );
        }
    }
}

function showDefault(id) {
    document.getElementById('settingDefault'+id).hidden = true;
    let defaultContact = document.getElementById('defaultContact'+id);
    let type = defaultContact.dataset.type;
    for(let tag of document.getElementsByClassName(type+'DefaultContact')) {
        tag.hidden = true;
    }
    for(let form of document.getElementsByClassName(type+'SetDefault')) {
        let contactID = form.id.replace('setDefault', '');
        form.hidden = ! (
            contactID != id &&
            ! document.getElementById('verifyContactButton'+contactID)
                .classList.contains('submitButton')
        );
    }
    defaultContact.hidden = false;
}

function setDefaultSuccessCallback(response) {
    bootstrapAlert(
        response.status == 201 ?
        response.data.message : response.data.success
    );
    showDefault(urlGetContactID(response.request.responseURL))
    enableSubmitting();
}

function enableVerifyButton(id) {
    document.getElementById('setDefault'+id).hidden = true;
    document.getElementById('defaultContact'+id).hidden = true;
    let verifyButton = document.getElementById('verifyContactButton'+id);
    verifyButton.classList.remove('btn-secondary');
    verifyButton.classList.add('submitButton');
    verifyButton.classList.add('btn-primary');
    verifyButton.innerText = "Verify";
    verifyButton.addEventListener('click', verifyContact);
}

function setDefaultFailCallback(error) {
    let id = urlGetContactID(error.request.responseURL);
    document.getElementById('settingDefault'+id).hidden = true;
    document.getElementById('setDefault'+id).hidden = false;
    if(error.status == 428) {
        enableVerifyButton(id);
    }
    enableSubmitting();
}

function setDefault(event) {
    event.preventDefault();
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'setDefault'+submitAt;
        disableSubmitting()
        if(submitting == 'setDefault'+submitAt) {
            event.target.hidden = true;
            let id = event.target.id.replace('setDefault', '');
            document.getElementById('settingDefault'+id).hidden = false;
            post(
                event.target.action,
                setDefaultSuccessCallback,
                setDefaultFailCallback,
                'put'
            );
        }
    }
}

function closeEdit(id) {
    document.getElementById('editContactForm'+id).hidden = true;
    document.getElementById('saveContact'+id).hidden = true;
    document.getElementById('cancelEditContact'+id).hidden = true;
    let input = document.getElementById('contactInput'+id);
    input.value = input.dataset.value;
    document.getElementById('contact'+id).hidden = false;
    document.getElementById('verifyContactButton'+id).hidden = false;
    document.getElementById('editContact'+id).hidden = false;
    document.getElementById('deleteContact'+id).hidden = false;
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
    let input = document.getElementById('contactInput'+id);
    document.getElementById('contact'+id).innerText = response.data[input.name];
    input.dataset.value = response.data[input.name];
    if(
        ! response.data.is_verified &&
        ! document.getElementById('verifyContactButton'+id)
            .classList.contains('submitButton')
    ) {
        enableVerifyButton(id);
    }
    if(
        response.data[`default_${input.type}_id`] != id &&
        ! document.getElementById('defaultContact'+id).hidden
    ) {
        showDefault(response.data[`default_${input.type}_id`]);
    }
    document.getElementById('savingContact'+id).hidden = true;
    closeEdit(id);
    input.disabled = false;
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
    enableSubmitting();
}

function updateContact(event) {
    event.preventDefault();
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'updateContact'+submitAt;
        let id = event.target.id.replace('editContactForm', '');
        let input = document.getElementById('contactInput'+id);
        disableSubmitting()
        if(submitting == 'updateContact'+submitAt) {
            if(contactValidation(input)) {
                input.disabled = true;
                document.getElementById('saveContact'+id).hidden = true;
                document.getElementById('cancelEditContact'+id).hidden = true;
                document.getElementById('savingContact'+id).hidden = false;
                let data = {};
                data[input.name] = input.value;
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
    document.getElementById('contact'+id).hidden = true;
    document.getElementById('verifyContactButton'+id).hidden = true;
    event.target.hidden = true;
    document.getElementById('deleteContact'+id).hidden = true;
    document.getElementById('editContactForm'+id).hidden = false;
    document.getElementById('saveContact'+id).hidden = false;
    document.getElementById('cancelEditContact'+id).hidden = false;
}

function deleteContactSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    document.getElementById(
        'contactRow'+urlGetContactID(response.request.responseURL)
    ).remove();
    enableSubmitting();
}

function deleteContactFailCallback(error) {
    let id = urlGetContactID(error.request.responseURL);
    document.getElementById('setDefault'+id).disabled = false;
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
    let verifyContactButton = document.getElementById('verifyContactButton'+id)
    document.getElementById('setDefault'+id).addEventListener(
        'submit', setDefault
    );
    document.getElementById('editContactForm'+id).addEventListener(
        'submit', updateContact
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
    verifyContactButton.addEventListener(
        'click', verifyContact
    );
    document.getElementById('requestNewVerifyCode'+id).addEventListener(
        'click', requestNewVerifyCode
    );
    document.getElementById('verifyContactForm'+id).addEventListener(
        'submit', submitVerifyCode
    );
    document.getElementById('cancelVerify'+id).addEventListener(
        'click', cancelVerifyContact
    );
    if(
        document.getElementById('defaultContact'+id).hidden &&
        ! verifyContactButton.classList.contains('submitButton')
    ) {
        document.getElementById('setDefault'+id).hidden = false;
    }
    loader.remove();
    verifyContactButton.hidden = false;
    editContactButton.hidden = false;
    document.getElementById('deleteContact'+id).hidden = false;
}

document.querySelectorAll('.contactLoader').forEach(
    (loader) => {
        setContactEventListeners(loader);
    }
);

function createContactSuccess(response) {
    bootstrapAlert(response.data.success);
    let id = response.data.id;
    let token = document.querySelector("meta[name='csrf-token']").getAttribute("content");
    let type = response.data.type;
    let contact = response.data.contact;
    let input = document.getElementById(type+'ContactInput');
    input.value = '';
    enableSubmitting();
    input.disabled = false;
    document.getElementById(type+'CreatingContact').hidden = true;
    document.getElementById(type+'CreateButton').hidden = false;
    let rowElement = document.createElement('div');
    rowElement.className = "row g-4";
    rowElement.id = 'contactRow'+id;
    rowElement.dataset.requestVerifyCodeUrl = response.data.send_verify_code_url;
    let innerHtml = `
        <div class="col-md-3">
            <span id="contact${id}">${contact}</span>
            <form id="editContactForm${id}" method="POST" novalidate hidden
                action="${response.data.update_url}">
                <input id="contactInput${id}" class="form-control"
    `;
    switch(type) {
        case 'email':
            innerHtml += `
                    type="email" name="email" maxlength="320"
                    placeholder="dammy@example.com"
            `;
            break;
        case 'mobile':
            innerHtml += `
                    type="tel" name="mobile" minlength="5" maxlength="15"
                    placeholder="85298765432"
            `;
            break;
    }
    innerHtml += `
                    value="${contact}" data-value="${contact}" required />
            </form>
        </div>
        <div class="col-md-2">
            <span
                class="${type}DefaultContact"
                id="defaultContact${id}"
                data-type="${type}"
                hidden>
                Default
            </span>
            <form id="setDefault${id}" class="${type}SetDefault" method="POST"
                action="${response.data.set_default_url}" hidden>
                <input type="hidden" name="_token" value="${token}">
                <input type="hidden" name="_method" value="put">
                <button class="btn btn-primary submitButton">Set Default</button>
            </form>
            <button class="btn btn-primary" id="settingDefault${id}" disabled hidden>Setting</button>
        </div>
        <div class="col-md-2">
            <div class="contactLoader" id="contactLoader${id}">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </div>
            <form id="verifyContactForm${id}" hidden novalidate
                action="${response.data.verify_url}"
                method="POST">
                <input type="hidden" name="_token" value="${token}">
                <input type="text" name="code" class="form-control" id="verifyCodeInput${id}"
                    minlength="6" maxlength="6" pattern="[A-Za-z0-9]{6}" required
                    autocomplete="off" placeholder="Verify Code" />
            </form>
        </div>
        <button id="verifyContactButton${id}" hidden class="btn col-md-1 btn-primary submitButton">Verify</button>
        <button class="btn btn-primary col-md-2 submitButton requestNewVerifyCodeButton" id="requestNewVerifyCode${id}" hidden>
            Send New Verify Code
        </button>
        <button class="btn btn-primary col-md-4" id="requestingContactButton${id}" hidden disabled>Requesting</button>
        <button class="btn btn-primary col-md-1 submitButton" id="submitVerifyCode${id}" form="verifyContactForm${id}" hidden>Submit</button>
        <button class="btn btn-danger col-md-1" id="cancelVerify${id}" hidden>Cancel</button>
        <button class="btn btn-danger col-md-4" id="submittingContactButton${id}" hidden disabled>Submitting</button>
        <button class="btn btn-primary col-md-1" id="editContact${id}" hidden>Edit</button>
        <button class="btn btn-primary col-md-1 submitButton" id="saveContact${id}" form="editContactForm${id}" hidden>Save</button>
        <button class="btn btn-danger col-md-1" id="cancelEditContact${id}" hidden>Cancel</button>
        <button class="btn btn-primary col-md-2" id="savingContact${id}" hidden disabled>Saving</button>
        <form id="deleteContactForm${id}" method="POST" hidden
            action="${response.data.delete_url}">
            <input type="hidden" name="_token" value="${token}">
            <input type="hidden" name="_method" value="delete">
        </form>
        <button class="btn btn-danger col-md-1 submitButton" id="deleteContact${id}" form="deleteContactForm${id}" hidden>Delete</button>
    `;
    rowElement.innerHTML = innerHtml;
    document.getElementById(type).insertBefore(rowElement, document.getElementById(type+'CreateForm'));
    setContactEventListeners(document.getElementById('contactLoader'+id));
}

function createContactFail(error) {
    let type = submitting.match(/^([a-z]+)Create.*/i)[1];
    if(error.response.data.errors.message) {
        bootstrapAlert(error.response.data.errors.message);
    } else if(error.response.data.errors[type]){
        bootstrapAlert(error.response.data.errors[type]);
    } else {
        bootstrapAlert('The profile.js missing create fail type handle, please contact us.')
    }
    document.getElementById(type+'ContactInput').disabled = false;
    document.getElementById(type+'CreatingContact').hidden = true;
    document.getElementById(type+'CreateButton').hidden = false;
    submitting = '';
    enableSubmitting();
}

function createContact(event) {
    event.preventDefault();
    if(submitting == '') {
        let type = event.target.dataset.type;
        let input = document.getElementById(type+'ContactInput');
        let submitAt = Date.now();
        submitting = type+'Create'+submitAt;
        disableSubmitting();
        if(submitting == type+'Create'+submitAt) {
            if(contactValidation(input)) {
                input.disabled = true;
                document.getElementById(type+'CreateButton').hidden = true;
                document.getElementById(type+'CreatingContact').hidden = false;
                let data = {
                    type: type,
                    contact: input.value,
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
