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

    function updateName(event) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateName'+submitAt;
            let id = event.target.id.replace('updateName', '');
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

    function cancelEditName(index) {
        paymentGateways[index]['editing'] = false;
        inputNames[paymentGateways[index]['id']] = paymentGateways[index]['name'];
    }
</script>

<h2 class="fw-bold mb-2 text-uppercase">
    Other Payment Gateway
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
                <tr>
                    <th>{paymentGateway.id}</th>
                    <td>
                        {#if paymentGateway.editing}
                            <form id="updateName{paymentGateway.id}" onsubmit={updateName}>
                                <input type="text" maxlength="255" id="name{paymentGateway.id}"
                                    bind:value={inputNames[paymentGateway.id]} required disabled="{paymentGateway.updating}" />
                            </form>
                        {:else}
                            {paymentGateway.name}
                        {/if}
                    </td>
                    <td>{paymentGateway.is_active ? 'Active' : 'Inactive'}</td>
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
