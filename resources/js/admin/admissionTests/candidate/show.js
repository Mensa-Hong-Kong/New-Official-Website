import { post } from "../../../submitForm";
import stringToBoolean from "../../../stringToBoolean";

const editLink = document.getElementById('editLink');
const disabledEditLink = document.getElementById('disabledEditLink');
const presentForm = document.getElementById('presentForm');
const presentButton = document.getElementById('presentButton');

function successCallback(response) {
    bootstrapAlert(response.data.success);
    presentButton.value = !response.data.status;
    if(response.data.status) {
        presentButton.innerText = 'Present';
        if(presentButton.classList.contains('btn-danger')) {
            presentButton.classList.remove('btn-danger');
        }
        if(!presentButton.classList.contains('btn-success')) {
            presentButton.classList.add('btn-success');
        }
    } else {
        presentButton.innerText = 'Absent';
        if(presentButton.classList.contains('btn-success')) {
            presentButton.classList.remove('btn-success');
        }
        if(!presentButton.classList.contains('btn-danger')) {
            presentButton.classList.add('btn-danger');
        }
    }
    disabledEditLink.hidden = true;
    editLink.hidden = false;
    presentButton.disabled = false;
}

function failCallback(error) {
    if(error.status == 422) {
        if(error.response.data.errors.status) {
            bootstrapAlert(error.response.data.errors.status);
        } else {
            alert('undefine feedback key');
        }
    }
    disabledEditLink.hidden = true;
    editLink.hidden = false;
    presentButton.disabled = false;
}

presentForm.addEventListener(
    'submit', function(event) {
        event.preventDefault();
        if(!presentButton.disabled) {
            presentButton.disabled = true;
            editLink.hidden = true;
            disabledEditLink.hidden = false;
            let data = {status: stringToBoolean(presentButton.value)};
            post(presentForm.action, successCallback, failCallback, 'put', data);
        }
    }
);
