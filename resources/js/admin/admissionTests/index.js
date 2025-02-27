import { post } from "../../submitForm";

let submitting = 'loading';

const showTests = document.getElementsByClassName('showTest');
const disabledShowTests = document.getElementsByClassName('disabledShowTest');
const submitButtons = document.getElementsByClassName('submitButton');

function disableSubmitting() {
    for(let showTest of showTests) {
        showTest.hidden = true;
    }
    for(let disabledShowTest of disabledShowTests) {
        disabledShowTest.hidden = false;
    }
    for(let submitButton of submitButtons) {
        submitButton.disabled = true;
    }
}

function enableSubmitting() {
    submitting = '';
    for(let disabledShowTest of disabledShowTests) {
        disabledShowTest.hidden = true;
    }
    for(let showTest of showTests) {
        showTest.hidden = false;
    }
    for(let submitButton of submitButtons) {
        submitButton.disabled = false;
    }
}

function alertCallback() {
    window.location.reload();
}

function deleteTestSuccessCallback(response) {
    bootstrapAlert(response.data.success, alertCallback);
}

function deleteTestFailCallback(error) {
    enableSubmitting();
}

function confirmedDeleteTest(event) {
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'deleteTest'+submitAt;
        disableSubmitting();
        if(submitting == 'deleteTest'+submitAt) {
            post(
                event.target.action,
                deleteTestSuccessCallback,
                deleteTestFailCallback,
                'delete'
            );
        }
    }
}

function deleteTest(event) {
    event.preventDefault();
    let message = `Are you sure to delete the ${event.submitter.dataset.location}(${event.submitter.dataset.testingat})?`;
    bootstrapConfirm(message, confirmedDeleteTest, event);
}

document.querySelectorAll('.testLoader').forEach(
    (loader) => {
        let id = loader.id.replace('testLoader', '');
        document.getElementById('deleteTestForm'+id).addEventListener(
            'submit', deleteTest
        );
        loader.remove();
        document.getElementById('deleteTest'+id).hidden = false;
    }
);

submitting = '';
