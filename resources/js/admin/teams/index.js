import { post } from "../../submitForm";

let submitting = 'loading';

const teamTypeTabs = document.getElementsByClassName('teamTypeTab');
const showTeams = document.getElementsByClassName('showTeam');
const disabledShowTeams = document.getElementsByClassName('disabledShowTeam');
const submitButtons = document.getElementsByClassName('submitButton');

function urlGetTeamID(url) {
    return (new URL(url).pathname).match(/^\/admin\/teams\/([0-9]+).*/i)[1];
}

function disableSubmitting(){
    for(let tab of teamTypeTabs) {
        tab.disabled = true;
    }
    for(let showTeam of showTeams) {
        showTeam.hidden = true;
    }
    for(let disabledShowTeam of disabledShowTeams) {
        disabledShowTeam.hidden = false;
    }
    for(let submitButton of submitButtons) {
        submitButton.disabled = true;
    }
}

function enableSubmitting(){
    submitting = '';
    for(let disabledShowTeam of disabledShowTeams) {
        disabledShowTeam.hidden = true;
    }
    for(let showTeam of showTeams) {
        showTeam.hidden = false;
    }
    for(let submitButton of submitButtons) {
        submitButton.disabled = false;
    }
    for(let tab of teamTypeTabs) {
        tab.disabled = false;
    }
}

function deleteTeamSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    let id =  urlGetTeamID(response.request.responseURL);
    document.getElementById('row'+id).remove();
    enableSubmitting();
}

function deleteTeamFailCallback(error) {
    let id = urlGetTeamID(error.request.responseURL);
    document.getElementById('deletingTeam'+id).hidden = true;
    document.getElementById('deleteTeam'+id).hidden = false;
    enableSubmitting();
}

function confirmedDeleteTeam(event) {
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'deleteTeam'+submitAt;
        let id = event.target.id.replace('deleteTeamForm', '');
        disableSubmitting();
        if(submitting == 'deleteTeam'+submitAt) {
            document.getElementById('deleteTeam'+id).hidden = true;
            document.getElementById('deletingTeam'+id).hidden = false;
            post(
                event.target.action,
                deleteTeamSuccessCallback,
                deleteTeamFailCallback,
                'delete'
            );
        }
    }
}

function deleteTeam(event) {
    event.preventDefault();
    let message = `Are you sure to delete the team of ${event.submitter.dataset.name}?`;
    bootstrapConfirm(message, confirmedDeleteTeam, event);
}

document.querySelectorAll('.teamLoader').forEach(
    function(loader) {
        let id = loader.id.replace('teamLoader', '');
        document.getElementById('deleteTeamForm'+id).addEventListener(
            'submit', deleteTeam
        );
        loader.remove();
        document.getElementById('deleteTeam'+id).hidden = false;
    }
);

submitting = '';
