import { post } from "../../submitForm";
import stringToBoolean from "../../stringToBoolean";

let submitting = 'loading';
const submitButtons = document.getElementsByClassName('submitButton');

const editForm = document.getElementById('form');

if(editForm) {
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

    function urlGetProctorID(url) {
        return (new URL(url).pathname).match(/^\/admin\/admission-tests\/([0-9]+)\/proctors\/([0-9]+).*/i)[2];
    }

    function saveProctorSuccessCallback(response) {
        bootstrapAlert(response.data.success);
        let id = urlGetProctorID(response.request.responseURL);
        users[response.data.user_id] = response.data.name;
        let form = document.getElementById('editProctorForm'+id);
        form.hidden = true;
        form.id = 'editProctorForm'+response.data.user_id;
        form.action = response.data.update_proctor_url;
        let savingButton = document.getElementById('savingProctor'+id);
        savingButton.hidden = true;
        savingButton.id = 'savingProctor'+response.data.user_id;
        let saveButton = document.getElementById('saveProctor'+id);
        saveButton.hidden = false;
        saveButton.id = 'saveProctor'+response.data.user_id;
        let cancelButton = document.getElementById('cancelEditProctor'+id)
        cancelButton.disabled = false;
        cancelButton.id = 'cancelEditProctor'+response.data.user_id;
        let input = document.getElementById('proctorUserIdInput'+id)
        input.value = response.data.user_id;
        input.dataset.value = input.value;
        input.id = 'proctorUserIdInput'+response.data.user_id;
        let proctorName = document.getElementById('proctorName'+id);
        proctorName.id = 'proctorName'+response.data.user_id;
        proctorName.innerText = response.data.name;
        let showProctorId = document.getElementById('showProctorId'+id);
        showProctorId.id = 'showProctorId'+response.data.user_id;
        showProctorId.innerText = response.data.user_id;
        let showProctorName = document.getElementById('showProctorName'+id);
        showProctorName.id = 'showProctorName'+response.data.user_id;
        showProctorName.innerText = response.data.name;
        let showProctorLink = document.getElementById('showProctorLink'+id);
        showProctorLink.id = 'showProctorLink' + response.data.user_id;
        showProctorLink.href = response.data.show_user_url;
        let editButton = document.getElementById('editProctor'+id);
        editButton.id = 'editProctor'+response.data.user_id;
        let deleteForm = document.getElementById('deleteProctorForm'+id);
        deleteForm.id = 'deleteProctorForm'+response.data.user_id;
        deleteForm.action = response.data.delete_proctor_url;
        let deleteButton = document.getElementById('deleteProctor'+id);
        deleteButton.id = 'deleteProctor'+response.data.user_id;
        deleteButton.setAttribute('form', deleteForm.id);
        let showProctor = document.getElementById('showProctor'+id);
        showProctor.id = 'showProctor'+response.data.user_id;
        showProctor.hidden = false;
        enableSubmitting();
    }

    function saveProctorFailCallback(error) {
        let id = urlGetProctorID(response.request.responseURL);
        if(error.status == 422) {
            bootstrapAlert(error.response.data.errors.user_id);
        }
        document.getElementById('savingProctor'+id).hidden = true;
        document.getElementById('saveProctor'+id).hidden = false;
        document.getElementById('cancelEditProctor'+id).disabled = false;
        enableSubmitting();
    }

    function saveProctor(event) {
        event.preventDefault();
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'updateProctor'+submitAt;
            let id = event.target.id.replace('editProctorForm', '');
            let input = document.getElementById('proctorUserIdInput'+id);
            disableSubmitting();
            if(submitting == 'updateProctor'+submitAt) {
                if(userIdValidation(input)) {
                    input.disabled = true;
                    document.getElementById('saveProctor'+id).hidden = true;
                    document.getElementById('savingProctor'+id).hidden = false;
                    document.getElementById('cancelEditProctor'+id).disabled = true;
                    let data = {user_id: input.value};
                    post(event.target.action, saveProctorSuccessCallback, saveProctorFailCallback, 'put', data);
                } else {
                    enableSubmitting();
                }
            }
        }
    }

    function cancelEditProctor(event) {
        let id = event.target.id.replace('cancelEditProctor', '');
        document.getElementById('editProctorForm'+id).hidden = true;
        let input = document.getElementById('proctorUserIdInput'+id);
        input.value = input.dataset.value;
        document.getElementById('proctorName'+id).innerText = users[input.value] ?? '';
        document.getElementById('showProctor'+id).hidden = false;
    }

    function editProctor(event) {
        let id = event.target.id.replace('editProctor', '');
        document.getElementById('showProctor'+id).hidden = true;
        document.getElementById('editProctorForm'+id).hidden = false;
    }

    function deleteProctorSuccessCallback(response) {
        bootstrapAlert(response.data.success);
        let id =  urlGetProctorID(response.request.responseURL);
        document.getElementById('showProctor'+id).remove();
        document.getElementById('editProctorForm'+id).remove();
        enableSubmitting();
    }

    function deleteProctorFailCallback(error) {
        let id = urlGetProctorID(error.request.responseURL);
        let editButton = document.getElementById('editProctor'+id);
        editButton.addEventListener('click', editProctor);
        editButton.disabled = false;
        enableSubmitting();
    }

    function confirmedDeleteProctor(event) {
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'deleteProctor'+submitAt;
            disableSubmitting();
            let id = event.target.id.replace('deleteProctorForm', '');
            let editButton = document.getElementById('editProctor'+id)
            editButton.removeEventListener('click', editProctor);
            editButton.disabled = true;
            if(submitting == 'deleteProctor'+submitAt) {
                post(
                    event.target.action,
                    deleteProctorSuccessCallback,
                    deleteProctorFailCallback,
                    'delete'
                );
            } else {
                editButton.addEventListener('click', editContact);
                editButton.disabled = false;
            }
        }
    }

    function deleteProctor(event) {
        event.preventDefault();
        let id = event.target.id.replace('deleteProctorForm', '');
        let showProctorName = document.getElementById('showProctorName'+id);
        let message = `Are you sure to delete the proctor of ${showProctorName.innerText}?`;
        bootstrapConfirm(message, confirmedDeleteProctor, event);
    }

    function setProctorEventListeners(loader) {
        let id = loader.id.replace('proctorLoader', '');
        document.getElementById('proctorUserIdInput'+id).addEventListener(
            'keyup', function(event) {
                document.getElementById('proctorName'+id).innerText = users[event.target.value] ?? '';
            }
        )
        document.getElementById('editProctorForm'+id).addEventListener(
            'submit', saveProctor
        )
        document.getElementById('cancelEditProctor'+id).addEventListener(
            'click', cancelEditProctor
        )
        let editButton = document.getElementById('editProctor'+id);
        editButton.addEventListener('click', editProctor);
        document.getElementById('deleteProctorForm'+id).addEventListener(
            'submit', deleteProctor
        );
        loader.remove();
        editButton.hidden = false;
        document.getElementById('deleteProctor'+id).hidden = false;
    }

    document.querySelectorAll('.proctorLoader').forEach(
        (loader) => {
            setProctorEventListeners(loader);
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

    function createProctorSuccessCallback(response) {
        bootstrapAlert(response.data.success);
        let formElement = document.createElement('form');
        formElement.className = 'row g-3';
        formElement.id = 'editProctorForm'+response.data.user_id;
        formElement.method = 'POST';
        formElement.noValidate = true;
        formElement.hidden = true;
        formElement.action = response.data.update_proctor_url;
        let token = document.querySelector("meta[name='csrf-token']").getAttribute("content");
        formElement.innerHTML = `
            <input type="hidden" name="_token" value="${token}">
            <input type="hidden" name="_method" value="put">
            <input type="text" id="proctorUserIdInput${response.data.user_id}" class="col-md-2" name="user_id" list="users" value="${response.data.user_id}" data-value="${response.data.user_id}" required />
            <div class="col-md-4" id="proctorName${response.data.user_id}">${response.data.name}</div>
            <div class="col-md-6">
                <button class="btn btn-primary col-md-4 submitButton" id="saveProctor${response.data.user_id}">Save</button>
                <button class="btn btn-primary col-md-4 submitButton" id="savingProctor${response.data.user_id}" disabled hidden>Save</button>
                <button class="btn btn-danger col-md-4" id="cancelEditProctor${response.data.user_id}" onclick="return false">Cancel</button>
            </div>
        `;
        proctor.insertBefore(formElement, createProctorForm);
        let rowElement = document.createElement('div');
        rowElement.id = 'showProctor'+response.data.user_id;
        rowElement.className = 'row g-3';
        rowElement.innerHTML = `
            <div class="col-md-2" id="showProctorId${response.data.user_id}">${response.data.user_id}</div>
            <div class="col-md-4" id="showProctorName${response.data.user_id}">${response.data.name}</div>
            <div class="col-md-6">
                <form method="POST" id="deleteProctorForm${response.data.user_id}" action="${response.data.delete_proctor_url}" hidden>
                    @csrf
                    @method('DELETE')
                </form>
                <a id="showProctorLink${response.data.user_id}" href="${response.data.show_user_url}" class="btn btn-primary col-md-4">Show</a>
                <span class="spinner-border spinner-border-sm proctorLoader" id="proctorLoader${response.data.user_id}" role="status" aria-hidden="true"></span>
                <button class="btn btn-primary col-md-4" id="editProctor${response.data.user_id}" hidden>Edit</button>
                <button class="btn btn-danger col-md-4" id="deleteProctor${response.data.user_id}" form="deleteProctorForm${response.data.user_id}" hidden>Delete</button>
            </div>
        `;
        proctor.insertBefore(rowElement, formElement);
        setProctorEventListeners(document.getElementById('proctorLoader'+response.data.user_id));
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
}

submitting = '';