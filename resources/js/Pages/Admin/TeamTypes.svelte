<script>
    import Layout from '@/Pages/Layouts/App.svelte';
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { post } from "@/submitForm.svelte";
    import { Button, Spinner, Table, Input, Alert } from '@sveltestrap/sveltestrap';

    let {types: initTypes} = $props();
    let types = $state([]);
    let submitting = $state(false);
    let inputNames = $state({});
    for (let data of initTypes) {
        data['editing'] = false;
        data['updating'] = false;
        types.push(data);
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
        let id = route().match(location.host + location.pathname, 'PUT').params.team_type;
        let index = getIndex(id);
        inputNames[index].value = response.data.name;
        types[index]['title'] = response.data.name;
        types[index]['editing'] = false;
        types[index]['updating'] = false;
        submitting = false;
    }

    function updateNameFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.name);
        }
        let location = new URL(error.request.responseURL);
        let id = route().match(location.host + location.pathname, 'PUT').params.team_type;
        types[getIndex(id)]['updating'] = false;
        submitting = false;
    }

    function updateName(event, index) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateName'+submitAt;
            if(submitting == 'updateName'+submitAt) {
                if(nameValidation(inputNames[index])) {
                    types[index]['updating'] = true;
                    post(
                        route(
                            'admin.team-types.update',
                            {team_type: types[index]['id']}
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

    function cancelEditName(index) {
        types[index]['editing'] = false;
        inputNames[index].value = types[index]['title'];
    }

    function getIndex(id) {
        return types.findIndex(
            function(element) {
                return element.id == id;
            }
        );
    }

    let row;
    let updatingDisplayOrder = $state(false);

    function dragEnd(event) {
        types.splice(
            Array.from(event.target.parentNode.children).indexOf(row),
            0,
            types.splice(getIndex(row.dataset.id), 1)[0]
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
        types.splice(0);
        for(let row of originDisplayOrder) {
            types.push(row);
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
                for(let row of types) {
                    data.display_order.push(row.id);
                }
                post(
                    route('admin.team-types.display-order.update'),
                    updateDisplayOrderSuccessCallback,
                    updateDisplayOrderFailCallback,
                    'put', data
                );
            }
        }
    }

    function editDisplayOrder() {
        originDisplayOrder = [];
        for(let row of types) {
            originDisplayOrder.push(row);
        }
        editingDisplayOrder = true;
    }
</script>

<svelte:head>
    <title>Administration Team Types | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h2 class="mb-2 fw-bold text-uppercase">
            Team Types
            <Button color="primary" onclick={editDisplayOrder}
                hidden={editingDisplayOrder || updatingDisplayOrder}>Edit Display Order</Button>
            <Button color="primary" onclick={updateDisplayOrder} disabled={submitting}
                hidden={! editingDisplayOrder || updatingDisplayOrder}>Save Display Order</Button>
            <Button color="danger" onclick={cancelEditDisplay}
                hidden={! editingDisplayOrder || updatingDisplayOrder}>Cancel</Button>
            <Button color="success" hidden={! updatingDisplayOrder} disabled>
                <Spinner type="border" size="sm" />
                Saving Display Order...
            </Button>
        </h2>
        {#if types.length}
            <Table hover>
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Display Name</th>
                        <th scope="col">Control</th>
                    </tr>
                </thead>
                <tbody>
                    {#each types as row, index}
                        <tr data-id="{row.id}"
                            ondragstart={dragStart} ondragover={dragOver} ondragend={dragEnd}
                            draggable="{editingDisplayOrder && ! updatingDisplayOrder}"
                            class={{draggable: editingDisplayOrder && ! updatingDisplayOrder}}>
                            <th scope="row">{row.name}</th>
                            <td>
                                <span hidden="{row.editing}">{row.title}</span>
                                <form method="POST" id="updateName{row.id}" hidden="{! row.editing}" novalidate
                                    onsubmit={(event) => updateName(event, index)}>
                                    <Input name="name" maxlength="255"
                                        value={row.title} disabled={row.updating}
                                        bind:inner={inputNames[index]} />
                                </form>
                            </td>
                            <td>
                                <Button color="primary" hidden={row.editing || row.updating}
                                    onclick={() => types[index]['editing'] = true}>Edit</Button>
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