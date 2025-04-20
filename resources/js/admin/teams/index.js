import { post } from "@/submitForm";

let submitting = 'loading';

const teamTypeTabs = document.getElementsByClassName('teamTypeTab');
const submitButtons = document.getElementsByClassName('submitButton');

const editDisplayOrder = document.getElementById('editDisplayOrder');
const saveDisplayOrder = document.getElementById('saveDisplayOrder');
const cancelDisplayOrder = document.getElementById('cancelDisplayOrder');
const savingDisplayOrder = document.getElementById('savingDisplayOrder');

const showTeams = document.getElementsByClassName('showTeam');
const disabledShowTeams = document.getElementsByClassName('disabledShowTeam');

function disableSubmitting(){
    if(saveDisplayOrder.hidden) {
        for(let tab of teamTypeTabs) {
            tab.disabled = true;
        }
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

let row;

let displayOrder;

function dragOver(event) {
    event.preventDefault();
    let children= Array.from(event.target.parentNode.parentNode.children);
    if(children.indexOf(event.target.parentNode)>children.indexOf(row)) {
        event.target.parentNode.after(row);
    } else {
        event.target.parentNode.before(row);
    }
}

function dragStart(event) {
    row = event.target;
}

function closeEditDisplayOrder(){
    saveDisplayOrder.hidden = true;
    cancelDisplayOrder.hidden = true;
    savingDisplayOrder.hidden = true;
    let typeID;
    for(let tab of teamTypeTabs) {
        tab.disabled = false;
        if(tab.classList.contains('active')) {
            typeID = tab.id.match(/^pills-team-type-([0-9]+)-tab/i)[1];
            console.log(tab);
        }
    }
    for(let id of displayOrder) {
        let row = document.getElementById('row'+id);
        row.removeEventListener('dragstart', dragStart);
        row.removeEventListener('dragover', dragOver);
        row.draggable = false;
        row.classList.remove('draggable');
        document.getElementById('tableBody'+typeID).appendChild(row);
    }
    editDisplayOrder.hidden = false;
}

cancelDisplayOrder.addEventListener(
    'click', closeEditDisplayOrder
);

function updataDisplayOrderSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    displayOrder = response.data.display_order;
    closeEditDisplayOrder();
    enableSubmitting();
}

function updataDisplayOrderFailCallback(error) {
    if(error.status == 422) {
        bootstrapAlert(error.data.errors.display_order);
    }
    savingDisplayOrder.hidden = true;
    saveDisplayOrder.hidden = false;
    cancelDisplayOrder.hidden = false;
    enableSubmitting();
}

saveDisplayOrder.addEventListener(
    'click', function(event) {
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'updateDisplayOrder'+submitAt;
            let typeID;
            for(let tab of teamTypeTabs) {
                if(tab.classList.contains('active')) {
                    typeID = tab.id.match(/^pills-team-type-([0-9]+)-tab/i)[1];
                }
            }
            disableSubmitting();
            if(submitting == 'updateDisplayOrder'+submitAt) {
                saveDisplayOrder.hidden = true;
                cancelDisplayOrder.hidden = true;
                savingDisplayOrder.hidden = false;
                let data = {
                    type_id: typeID,
                    display_order: [],
                };
                for(let row of document.getElementsByClassName('dataRow'+typeID)) {
                    data.display_order.push(row.id.replace('row', ''));
                }
                post(
                    window.location.href+'/display-order',
                    updataDisplayOrderSuccessCallback, updataDisplayOrderFailCallback,
                    'put', data
                );
            }
        }
    }
);

editDisplayOrder.addEventListener(
    'click', function(event) {
        let id;
        if(saveDisplayOrder.hidden) {
            for(let tab of teamTypeTabs) {
                tab.disabled = true;
                if(tab.classList.contains('active')) {
                    id = tab.id.match(/^pills-team-type-([0-9]+)-tab/i)[1];
                }
            }
        }
        displayOrder = [];
        for(let row of document.getElementsByClassName('dataRow'+id)) {
            row.addEventListener('dragstart', dragStart);
            row.addEventListener('dragover', dragOver);
            row.classList.add('draggable');
            row.draggable = true;
            displayOrder.push(row.id.replace('row', ''));
        }
        event.target.hidden = true;
        saveDisplayOrder.hidden = false;
        cancelDisplayOrder.hidden = false;
    }
);

function urlGetTeamID(url) {
    return (new URL(url).pathname).match(/^\/admin\/teams\/([0-9]+).*/i)[1];
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
