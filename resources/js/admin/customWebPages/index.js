import { post } from "@/submitForm";

let submitting = 'loading';

const submitButtons = document.getElementsByClassName('submitButton');

function disableSubmitting(){
    for(let submitButton of submitButtons) {
        submitButton.disabled = true;
    }
}

function enableSubmitting(){
    submitting = '';
    for(let submitButton of submitButtons) {
        submitButton.disabled = false;
    }
}

function urlGetCustomWebPageID(url) {
    return (new URL(url).pathname).match(/^\/admin\/custom-web-pages\/([0-9]+).*/i)[1];
}

function deleteCustomWebPageSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    let id =  urlGetCustomWebPageID(response.request.responseURL);
    document.getElementById('row'+id).remove();
    enableSubmitting();
}

function deleteCustomWebPageFailCallback(error) {
    let id = urlGetCustomWebPageID(error.request.responseURL);
    document.getElementById('deleting'+id).hidden = true;
    document.getElementById('delete'+id).hidden = false;
    enableSubmitting();
}

function confirmedDeleteTeam(event) {
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'delete'+submitAt;
        let id = event.target.id.replace('deleteForm', '');
        disableSubmitting();
        if(submitting == 'delete'+submitAt) {
            document.getElementById('delete'+id).hidden = true;
            document.getElementById('deleting'+id).hidden = false;
            post(
                event.target.action,
                deleteCustomWebPageSuccessCallback,
                deleteCustomWebPageFailCallback,
                'delete'
            );
        }
    }
}

function deletePage(event) {
    event.preventDefault();
    let message = `Are you sure to delete the custom web page of ${event.submitter.dataset.title}?`;
    bootstrapConfirm(message, confirmedDeleteTeam, event);
}

document.querySelectorAll('.pageLoader').forEach(
    function(loader) {
        let id = loader.id.replace('pageLoader', '');
        document.getElementById('deleteForm'+id).addEventListener(
            'submit', deletePage
        );
        loader.remove();
        document.getElementById('delete'+id).hidden = false;
    }
);

submitting = '';
