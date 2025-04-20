import { post } from "@/submitForm";

let submitting = 'loading';

const submitButtons = document.getElementsByClassName('submitButton')

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

const tableBody = document.getElementById('tableBody');
const dataRows = document.getElementsByClassName('dataRow');
const editDisplayOrder = document.getElementById('editDisplayOrder');
const saveDisplayOrder = document.getElementById('saveDisplayOrder');
const cancelDisplayOrder = document.getElementById('cancelDisplayOrder');
const savingDisplayOrder = document.getElementById('savingDisplayOrder');

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
    for(let id of displayOrder) {
        let row = document.getElementById('dataRow'+id);
        row.removeEventListener('dragstart', dragStart);
        row.removeEventListener('dragover', dragOver);
        row.draggable = false;
        row.classList.remove('draggable');
        tableBody.appendChild(row);
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
            disableSubmitting();
            if(submitting == 'updateDisplayOrder'+submitAt) {
                saveDisplayOrder.hidden = true;
                cancelDisplayOrder.hidden = true;
                savingDisplayOrder.hidden = false;
                let data = {display_order: []};
                for(let row of dataRows) {
                    data.display_order.push(row.id.replace('dataRow', ''));
                }
                post(
                    window.location.href+'/roles/display-order',
                    updataDisplayOrderSuccessCallback, updataDisplayOrderFailCallback,
                    'put', data
                );
            }
        }
    }
);

editDisplayOrder.addEventListener(
    'click', function(event) {
        displayOrder = [];
        for(let row of dataRows) {
            row.addEventListener('dragstart', dragStart);
            row.addEventListener('dragover', dragOver);
            row.classList.add('draggable');
            row.draggable = true;
            displayOrder.push(row.id.replace('dataRow', ''));
        }
        event.target.hidden = true;
        saveDisplayOrder.hidden = false;
        cancelDisplayOrder.hidden = false;
    }
);

function closeEditDisplayName(id) {
    document.getElementById('saving'+id).hidden = true;
    document.getElementById('updateDisplayNameForm'+id).hidden = true;
    document.getElementById('save'+id).hidden = true;
    document.getElementById('cancel'+id).hidden = true;
    let input = document.getElementById('displayNameInput'+id);
    input.value = input.dataset.value;
    document.getElementById('showDisplayName'+id).hidden = false;
    document.getElementById('edit'+id).hidden = false;
}

const editTeam = document.getElementById('editTeam');
const disabledEditTeam = document.getElementById('disabledEditTeam');
const editRoles = document.getElementsByClassName('editRole');
const disabledEditRoles = document.getElementsByClassName('disabledEditRole');

function urlGetTeamID(url) {
    return (new URL(url).pathname).match(/^\/admin\/teams\/([0-9]+)\/roles\/([0-9]+).*/i)[2];
}

function deleteTeamSuccessCallback(response) {
    bootstrapAlert(response.data.success);
    let id =  urlGetTeamID(response.request.responseURL);
    document.getElementById('dataRow'+id).remove();
    disabledEditTeam.hidden = true;
    editTeam.hidden = false;
    for(let disabledEditRole of disabledEditRoles) {
        disabledEditRole.hidden = true;
    }
    for(let editRole of editRoles) {
        editRole.hidden = false;
    }
    enableSubmitting();
}

function deleteTeamFailCallback(error) {
    let id = urlGetTeamID(error.request.responseURL);
    document.getElementById('deletingTeam'+id).hidden = true;
    document.getElementById('deleteTeam'+id).hidden = false;
    disabledEditTeam.hidden = true;
    editTeam.hidden = false;
    for(let disabledEditRole of disabledEditRoles) {
        disabledEditRole.hidden = true;
    }
    for(let editRole of editRoles) {
        editRole.hidden = false;
    }
    enableSubmitting();
}

function confirmedDeleteRole(event) {
    if(submitting == '') {
        let submitAt = Date.now();
        submitting = 'deleteRole'+submitAt;
        let id = event.target.id.replace('deleteRoleForm', '');
        disableSubmitting();
        if(submitting == 'deleteRole'+submitAt) {
            editTeam.hidden = true;
            disabledEditTeam.hidden = false;
            for(let editRole of editRoles) {
                editRole.hidden = true;
            }
            for(let disabledEditRole of disabledEditRoles) {
                disabledEditRole.hidden = false;
            }
            document.getElementById('deleteRole'+id).hidden = true;
            document.getElementById('deletingRole'+id).hidden = false;
            post(
                event.target.action,
                deleteTeamSuccessCallback,
                deleteTeamFailCallback,
                'delete'
            );
        }
    }
}

function deleteRole(event) {
    event.preventDefault();
    let message = `Are you sure to delete the role of ${event.submitter.dataset.name}?`;
    bootstrapConfirm(message, confirmedDeleteRole, event);
}

document.querySelectorAll('.roleLoader').forEach(
    function(loader) {
        let id = loader.id.replace('roleLoader', '');
        document.getElementById('deleteRoleForm'+id).addEventListener(
            'submit', deleteRole
        );
        loader.remove();
        document.getElementById('deleteRole'+id).hidden = false;
    }
);

submitting = '';
