<script>
    import Layout from '@/Pages/Layouts/App.svelte';
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { post } from "@/submitForm.svelte";
    import { Button, Spinner, Table, Input, Alert } from '@sveltestrap/sveltestrap';

    let {auth, modules: initModules} = $props();
    let modules = $state([]);
    let submitting = $state(false);
    let inputNames = $state({});
    
    for (const data of initModules) {
        data['editing'] = false;
        data['updating'] = false;
        modules.push(data);
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
        let id = route().match(location.host + location.pathname, 'PUT').params.module;
        let index = getIndex(id);
        inputNames[index].value = response.data.name;
        modules[index]['title'] = response.data.name;
        modules[index]['editing'] = false;
        modules[index]['updating'] = false;
        submitting = false;
    }

    function updateNameFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.name);
        }
        let location = new URL(error.request.responseURL);
        let id = route().match(location.host + location.pathname, 'PUT').params.module;
        modules[getIndex(id)]['updating'] = false;
        submitting = false;
    }

    function updateName(event, index) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateName'+submitAt;
            if(submitting == 'updateName'+submitAt) {
                if(nameValidation(inputNames[index])) {
                    modules[index]['updating'] = true;
                    post(
                        route(
                            'admin.modules.update',
                            {module: modules[index]['id']}
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
        modules[index]['editing'] = false;
        inputNames[index].value = modules[index]['title'];
    }

    function getIndex(id) {
        return modules.findIndex(
            function(element) {
                return element.id == id;
            }
        );
    }

    let row;
    let updatingDisplayOrder = $state(false);

    function dragEnd(event) {
        modules.splice(
            Array.from(event.target.parentNode.children).indexOf(row),
            0,
            modules.splice(getIndex(row.dataset.id), 1)[0]
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
        modules.splice(0);
        for(let row of originDisplayOrder) {
            modules.push(row);
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
                for(let row of modules) {
                    data.display_order.push(row.id);
                }
                post(
                    route('admin.modules.display-order.update'),
                    updateDisplayOrderSuccessCallback,
                    updateDisplayOrderFailCallback,
                    'put', data
                );
            }
        }
    }

    function editDisplayOrder() {
        originDisplayOrder = [];
        for(let row of modules) {
            originDisplayOrder.push(row);
        }
        editingDisplayOrder = true;
    }
</script>

<svelte:head>
    <title>Administration Modules | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h2 class="mb-2 fw-bold text-uppercase">
            Modules
            {#if 
                auth.user.permissions.includes('Edit:Permission') ||
                auth.user.roles.includes('Super Administrator')
            }
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
            {/if}
        </h2>
        {#if modules.length}
            <Table hover>
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Display Name</th>
                        {#if
                            auth.user.permissions.includes('Edit:Permission') ||
                            auth.user.roles.includes('Super Administrator')
                        }
                            <th scope="col">Control</th>
                        {/if}
                    </tr>
                </thead>
                <tbody>
                    {#each modules as row, index}
                        <tr data-id="{row.id}"
                            ondragstart={dragStart} ondragover={dragOver} ondragend={dragEnd}
                            draggable="{editingDisplayOrder && ! updatingDisplayOrder}"
                            class={{draggable: editingDisplayOrder && ! updatingDisplayOrder}}>
                            <th scope="row">{row.name}</th>
                            <td>
                                <span hidden="{row.editing}">{row.title}</span>
                                <form id="updateName{row.id}" method="POST" hidden="{! row.editing}" novalidate
                                    onsubmit={(event) => updateName(event, index)}>
                                    <Input name="name" maxlength="255"
                                        value={row.title} disabled={row.updating}
                                        bind:inner={inputNames[index]} />
                                </form>
                            </td>
                            {#if
                                auth.user.permissions.includes('Edit:Permission') ||
                                auth.user.roles.includes('Super Administrator')
                            }
                                <td>
                                    <Button color="primary" hidden={row.editing || row.updating}
                                        onclick={() => modules[index]['editing'] = true}>Edit</Button>
                                    <Button color="primary" form="updateName{row.id}"
                                        hidden={! row.editing || row.updating} disabled={submitting}>Save</Button>
                                    <Button color="danger" hidden={! row.editing || row.updating}
                                        onclick={() => cancelEditName(index)}>Cancel</Button>
                                    <Button color="primary" hidden={! row.updating} disabled>
                                        <Spinner type="border" size="sm" />
                                        Saving...
                                    </Button>
                                </td>
                            {/if}
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