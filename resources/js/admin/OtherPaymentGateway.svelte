<script>
    import { post } from "@/submitForm";

    let {paymentGateways: initPaymentGateways} = $props();
    const paymentGateways = $state([]);
    let submitting = $state(false);
    const inputNames = {};
    for (const data of initPaymentGateways) {
        data['editing'] = false;
        data['updating'] = false;
        paymentGateways.push(data);
        inputNames[data.id] = data.name;
    }

    function nameValidation(id) {
        let name = document.getElementById('name'+id);
        if(name.validity.valueMissing) {
            bootstrapAlert('The name field is required.');
            return false;
        } else if(name.validity.tooLong) {
            bootstrapAlert(`The name field must not be greater than ${name.maxLength} characters.`);
            return false;
        }
        return true;
    }

    function getIndex(id) {
        return paymentGateways.findIndex(
            function(element) {
                return element.id == id;
            }
        );
    }

    function updateNameSuccessCallback(response) {
        bootstrapAlert(response.data.success);
        let location = new URL(response.request.responseURL);
        let id = route().match(location.host + location.pathname, 'PUT').params.other_payment_gateway;
        inputNames[id] = response.data.name;
        paymentGateways[getIndex(id)]['name'] = response.data.name;
        paymentGateways[getIndex(id)]['editing'] = false;
        paymentGateways[getIndex(id)]['updating'] = false;
        submitting = false;
    }

    function updateNameFailCallback(error) {
        if(error.status == 422) {
            bootstrapAlert(error.data.errors.name);
        }
        let location = new URL(error.request.responseURL);
        let id = route().match(location.host + location.pathname, 'PUT').params.other_payment_gateway;
        paymentGateways[getIndex(id)]['updating'] = false;
        submitting = false;
    }

    function updateName(event, id) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateName'+submitAt;
            if(submitting == 'updateName'+submitAt) {
                if(nameValidation(id)) {
                    paymentGateways[getIndex(id)]['updating'] = true;
                    post(
                        route(
                            'admin.other-payment-gateways.update',
                            {other_payment_gateway: id}
                        ),
                        updateNameSuccessCallback,
                        updateNameFailCallback,
                        'put',
                        {name: inputNames[id]}
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }

    function cancelEditName(index) {
        paymentGateways[index]['editing'] = false;
        inputNames[paymentGateways[index]['id']] = paymentGateways[index]['name'];
    }

    function updateActionSuccessCallback(response) {
        bootstrapAlert(response.data.success);
        let location = new URL(response.request.responseURL);
        let id = route().match(location.host + location.pathname, 'PUT').params.other_payment_gateway;
        paymentGateways[getIndex(id)]['is_active'] = response.data.status;
        submitting = false;
    }

    function updateActionFailCallback(error) {
        if(error.status == 422) {
            bootstrapAlert(error.data.errors.status);
        }
        submitting = false;
    }

    function updateAction(id, status) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateAction'+submitAt;
            if(submitting == 'updateAction'+submitAt) {
                paymentGateways[getIndex(id)]['updating'] = true;
                post(
                    route(
                        'admin.other-payment-gateways.active.update',
                        {other_payment_gateway: id}
                    ),
                    updateActionSuccessCallback,
                    updateActionFailCallback,
                    'put',
                    {status: status}
                );
            }
        }
    }

    let row;
    let updatingDisplayOrder = $state(false);

    function dragEnd(event) {
        paymentGateways.splice(
            Array.from(event.target.parentNode.children).indexOf(row),
            0,
            paymentGateways.splice(getIndex(row.dataset.id), 1)[0]
        );
    }

    function dragOver(event) {
        event.preventDefault();
        if(! updatingDisplayOrder) {
            let children= Array.from(event.target.parentNode.parentNode.children);
            if(children.indexOf(event.target.parentNode)>children.indexOf(row)) {
                event.target.parentNode.after(row);
            } else {
                event.target.parentNode.before(row);
            }
        }
    }

    function dragStart(event) {
        row = event.target;
    }

    let editingDisplayOrder = $state(false);
    let originDisplayOrder = [];

    function cancelEditDisplay() {
        paymentGateways.splice(0);
        for(let paymentGateway of originDisplayOrder) {
            paymentGateways.push(paymentGateway);
        }
        editingDisplayOrder = false;
    }

    function updateDisplayOrderSuccessCallback(response) {
        bootstrapAlert(response.data.success);
        editingDisplayOrder = false;
        updatingDisplayOrder = false;
        submitting = false;
    }

    function updateDisplayOrderFailCallback(error) {
        if(error.status == 422) {
            bootstrapAlert(error.data.errors.display_order);
        }
        updatingDisplayOrder = false;
        submitting = false;
    }

    function updateDisplayOrder() {
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'updateDisplayOrder'+submitAt;
            if(submitting == 'updateDisplayOrder'+submitAt) {
                updatingDisplayOrder = true;
                let data = {display_order: []};
                for(let paymentGateway of paymentGateways) {
                    data.display_order.push(paymentGateway.id);
                }
                post(
                    route('admin.other-payment-gateways.display-order.update'),
                    updateDisplayOrderSuccessCallback,
                    updateDisplayOrderFailCallback,
                    'put',data
                );
            }
        }
    }

    function editDisplayOrder() {
        originDisplayOrder = [];
        for(let paymentGateway of paymentGateways) {
            originDisplayOrder.push(paymentGateway);
        }
        editingDisplayOrder = true;
    }
</script>

<h2 class="fw-bold mb-2 text-uppercase">
    Other Payment Gateway
    <button onclick="{editDisplayOrder}" class="btn btn-primary" hidden="{editingDisplayOrder || updatingDisplayOrder}">Edit Display Order</button>
    <button onclick="{updateDisplayOrder}" class="btn btn-primary" hidden="{! editingDisplayOrder || updatingDisplayOrder}">Save Display Order</button>
    <button onclick="{cancelEditDisplay}" class="btn btn-danger" hidden="{! editingDisplayOrder || updatingDisplayOrder}">Cancel</button>
    <button class="btn btn-success" hidden="{! updatingDisplayOrder}" disabled>
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        Saving Display Order...
    </button>
</h2>
{#if paymentGateways.length}
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Status</th>
                <th scope="col">Control</th>
            </tr>
        </thead>
        <tbody>
            {#each paymentGateways as paymentGateway, index}
                <tr data-id="{paymentGateway.id}"
                    ondragstart={dragStart} ondragover={dragOver} ondragend={dragEnd}
                    draggable="{editingDisplayOrder && ! updatingDisplayOrder}"
                    class={{draggable: editingDisplayOrder && ! updatingDisplayOrder}}>
                    <th>{paymentGateway.id}</th>
                    <td>
                        {#if paymentGateway.editing}
                            <form id="updateName{paymentGateway.id}" onsubmit={(event) => updateName(event, paymentGateway.id)}>
                                <input type="text" maxlength="255" id="name{paymentGateway.id}"
                                    bind:value={inputNames[paymentGateway.id]} required disabled="{paymentGateway.updating}" />
                            </form>
                        {:else}
                            {paymentGateway.name}
                        {/if}
                    </td>
                    <td>
                        <button onclick="{() => updateAction(paymentGateway.id, ! paymentGateway.is_active)}" class={[
                            'btn', {
                                'btn-success': paymentGateway.is_active,
                                'btn-danger': ! paymentGateway.is_active,
                            }
                        ]}>{paymentGateway.is_active ? 'Active' : 'Inactive'}</button>
                    </td>
                    <td>
                        {#if paymentGateway.updating}
                            <button class="btn btn-primary" disabled>Saving</button>
                        {:else if paymentGateway.editing}
                            <button class="btn btn-primary" disabled="{submitting}" form="updateName{paymentGateway.id}">Save</button>
                            <button class="btn btn-danger"onclick={() => cancelEditName(index)}>Cancel</button>
                        {:else}
                            <button class="btn btn-primary" onclick={() => paymentGateways[index]['editing'] = true}>Edit</button>
                        {/if}
                    </td>
                </tr>
            {/each}
        </tbody>
    </table>
{:else}
    <div class="alert alert-danger" role="alert">
        No Result
    </div>
{/if}
