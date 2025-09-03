<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { post } from "@/submitForm.svelte";
    import { Button, Spinner, Table, Input, Alert } from '@sveltestrap/sveltestrap';

    let { paymentGateways: initPaymentGateways } = $props();
    let paymentGateways = $state([]);
    let submitting = $state(false);
    let inputNames = $state({});

    for (const data of initPaymentGateways) {
        paymentGateways.push({
            id: data.id,
            name: data.name,
            editing: false,
            updating: false,
            isActive: data.is_active,
            updatingActiveStatus: false,
        });
        inputNames[data.id] = data.name;
    }

    function nameValidation(input) {
        if(input.validity.valueMissing) {
            alert('The name field is required.');
            return false;
        } else if(input.validity.tooLong) {
            alert(`The name field must not be greater than ${input.maxLength} characters.`);
            return false;
        }
        return true;
    }

    function updateNameSuccessCallback(response) {
        alert(response.data.success);
        let location = new URL(response.request.responseURL);
        let id = route().match(location.host + location.pathname, 'PUT').params.other_payment_gateway;
        let index = getIndex(id);
        inputNames[index].value = response.data.name;
        paymentGateways[index]['name'] = response.data.name;
        paymentGateways[index]['editing'] = false;
        paymentGateways[index]['updating'] = false;
        submitting = false;
    }

    function updateNameFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.name);
        }
        let location = new URL(error.request.responseURL);
        let id = route().match(location.host + location.pathname, 'PUT').params.other_payment_gateway;
        paymentGateways[getIndex(id)]['updating'] = false;
        submitting = false;
    }

    function updateName(event, index) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateName'+submitAt;
            if(submitting == 'updateName'+submitAt) {
                if(nameValidation(inputNames[index])) {
                    paymentGateways[index]['updating'] = true;
                    post(
                        route(
                            'admin.other-payment-gateways.update',
                            {other_payment_gateway: paymentGateways[index]['id']}
                        ),
                        updateNameSuccessCallback,
                        updateNameFailCallback,
                        'put',
                        {name: inputNames[index].value}
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }

    function updateActionSuccessCallback(response) {
        alert(response.data.success);
        let location = new URL(response.request.responseURL);
        let id = route().match(location.host + location.pathname, 'PUT').params.other_payment_gateway;
        let index = getIndex(id);
        paymentGateways[index]['isActive'] = response.data.status;
        paymentGateways[index]['updatingActiveStatus'] = false;
        submitting = false;
    }

    function updateActionFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.status);
        }
        let id = route().match(location.host + location.pathname, 'PUT').params.other_payment_gateway;
        paymentGateways[getIndex(id)]['updatingActiveStatus'] = false;
        submitting = false;
    }

    function updateAction(index, status) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateAction'+submitAt;
            if(submitting == 'updateAction'+submitAt) {
                paymentGateways[index]['updatingActiveStatus'] = true;
                post(
                    route(
                        'admin.other-payment-gateways.active.update',
                        {other_payment_gateway: paymentGateways[index]['id']}
                    ),
                    updateActionSuccessCallback,
                    updateActionFailCallback,
                    'put',
                    {status: status}
                );
            }
        }
    }

    function cancelEditName(index) {
        paymentGateways[index]['editing'] = false;
        inputNames[index].value = paymentGateways[index]['name'];
    }

    function getIndex(id) {
        return paymentGateways.findIndex(
            function(element) {
                return element.id == id;
            }
        );
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
        for(let row of originDisplayOrder) {
            paymentGateways.push(row);
        }
        editingDisplayOrder = false;
    }

    function updateDisplayOrderSuccessCallback(response) {
        alert(response.data.success);
        editingDisplayOrder = false;
        updatingDisplayOrder = false;
        submitting = false;
    }

    function updateDisplayOrderFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.display_order);
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
                for(let row of paymentGateways) {
                    data.display_order.push(row.id);
                }
                post(
                    route('admin.other-payment-gateways.display-order.update'),
                    updateDisplayOrderSuccessCallback,
                    updateDisplayOrderFailCallback,
                    'put', data
                );
            }
        }
    }

    function editDisplayOrder() {
        originDisplayOrder = [];
        for(let row of paymentGateways) {
            originDisplayOrder.push(row);
        }
        editingDisplayOrder = true;
    }
</script>

<svelte:head>
    <title>Administration Other Payment Gateways | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section>
        <h2 class="mb-2 fw-bold text-uppercase">
            Other Payment Gateway
            <Button color="primary" onclick={editDisplayOrder}
                hidden={editingDisplayOrder || updatingDisplayOrder}>Edit Display Order</Button>
            <Button color="primary" onclick={updateDisplayOrder} disabled={submitting}
                hidden={! editingDisplayOrder || updatingDisplayOrder}>Save Display Order</Button>
            <Button color="danger" onclick={cancelEditDisplay}
                hidden={! editingDisplayOrder || updatingDisplayOrder}>Cancel</Button>
            <Button color="primary" hidden={! updatingDisplayOrder} disabled>
                <Spinner type="border" size="sm" />
                Saving Display Order...
            </Button>
        </h2>
        {#if paymentGateways.length}
            <Table hover>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Status</th>
                        <th scope="col">Control</th>
                    </tr>
                </thead>
                <tbody>
                    {#each paymentGateways as row, index}
                        <tr data-id="{row.id}"
                            ondragstart={dragStart} ondragover={dragOver} ondragend={dragEnd}
                            draggable="{editingDisplayOrder && ! updatingDisplayOrder}"
                            class={{draggable: editingDisplayOrder && ! updatingDisplayOrder}}>
                            <th>{row.id}</th>
                            <td>
                                <span hidden="{row.editing}">{row.name}</span>
                                <form id="updateName{row.id}" method="POST" hidden="{! row.editing}" novalidate
                                    onsubmit={(event) => updateName(event, index)}>
                                    <Input name="name" maxlength="255"
                                        value={row.name} disabled={row.updating}
                                        bind:inner={inputNames[index]} />
                                </form>
                            </td>
                            <td>
                                <Button color={row.isActive ? 'success' : 'danger'}
                                    onclick={() => updateAction(index, ! row.isActive)}
                                    disabled={row.updatingActiveStatus}>
                                    {#if row.updatingActiveStatus}
                                        <Spinner type="border" size="sm" />
                                        Updating...
                                    {:else}
                                        {row.isActive ? 'Active' : 'Inactive'}
                                    {/if}
                                </Button>
                            </td>
                            <td>
                                <Button color="primary" hidden={row.editing || row.updating}
                                    onclick={() => paymentGateways[index]['editing'] = true}>Edit</Button>
                                <Button color="primary" form="updateName{row.id}"
                                    hidden={! row.editing || row.updating} disabled={submitting}>Save</Button>
                                <Button color="danger" hidden={! row.editing || row.updating}
                                    onclick={() => cancelEditName(index)}>Cancel</Button>
                                <Button color="primary" hidden={! row.updating} disabled>
                                    <Spinner type="border" size="sm" />
                                    Saving...
                                </Button>
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </Table>
        {:else}
            <Alert color="danger">
                No Result
            </Alert>
        {/if}
    </section>
</Layout>