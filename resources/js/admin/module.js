import { post } from "../submitForm";

const submitButtons = document.getElementsByClassName('submitButton')

let submitting = 'loading';

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

function closeEditDisplayName(id) {
    document.getElementById('saving'+id).hidden = true;
    document.getElementById('updateDisplayNameForm'+id).hidden = true;
    document.getElementById('save'+id).hidden = true;
    document.getElementById('cancel'+id).hidden = true;
    let input = document.getElementById('displayNameInput'+id);
    input.value = input.dataset.value;
    document.getElementById('edit'+id).hidden = false;
}

function cancelEditDisplayName(event) {
    closeEditDisplayName(event.target.id.replace('cancel', ''));
}

function urlGetContactID(url) {
    return (new URL(url).pathname).match(/^\/admin\/modules\/([0-9]+).*/i)[1];
}

function updateDisplayNameSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    let id = urlGetContactID(response.request.responseURL);
    let input = document.getElementById('displayNameInput'+id);
    input.dataset.value = response.data.name;
    document.getElementById('showDisplayName'+id).innerText = response.data.name;
    closeEditDisplayName(id);
    input.disabled = false;
    enableSubmitting();
}

function updateDisplayNameFailCallback(error) {
    if(error.status == 422) {
        bootstrapAlert(error.data.errors.contact_type);
    }
    let id = urlGetContactID(error.request.responseURL);
    document.getElementById('saving'+id).hidden = true;
    document.getElementById('displayNameInput'+id).disabled = false;
    document.getElementById('save'+id).hidden = false;
    document.getElementById('cancel'+id).hidden = false;
    enableSubmitting();
}

function updateDisplayName(event) {
    event.preventDefault();
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'updateDisplayName'+submitAt;
        let id = event.target.id.replace('updateDisplayNameForm', '');
        let input = document.getElementById('displayNameInput'+id);
        disableSubmitting();
        if(submitting == 'updateDisplayName'+submitAt) {
            if(input.validity.patternMismatch) {
                bootstrapAlert('The name field cannot has ";".');
                enableSubmitting();
            } else {
                input.disabled = true;
                document.getElementById('save'+id).hidden = true;
                document.getElementById('cancel'+id).hidden = true;
                document.getElementById('saving'+id).hidden = false;
                let data = {
                    name: input.value,
                }
                post(event.target.action, updateDisplayNameSuccessCallback, updateDisplayNameFailCallback, 'put', data);
            }
        }
    }
}

function editDisplayName(event) {
    let id = event.target.id.replace('edit', '');
    event.target.hidden = true;
    document.getElementById('updateDisplayNameForm'+id).hidden = false;
    document.getElementById('save'+id).hidden = false;
    document.getElementById('cancel'+id).hidden = false;
}

function setContactEventListeners(loader) {
    let id = loader.id.replace('contactLoader', '');
    document.getElementById('updateDisplayNameForm'+id).addEventListener(
        'submit', updateDisplayName
    );
    let editButton = document.getElementById('edit'+id);
    editButton.addEventListener(
        'click', editDisplayName
    );
    document.getElementById('cancel'+id).addEventListener(
        'click', cancelEditDisplayName
    );
    loader.remove();
    editButton.hidden = false;
}

document.querySelectorAll('.contactLoader').forEach(
    (loader) => {
        setContactEventListeners(loader);
    }
);

submitting = '';
