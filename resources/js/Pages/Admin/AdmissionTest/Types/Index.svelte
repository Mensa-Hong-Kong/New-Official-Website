<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner, Table, Alert } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';

    let { types: initTypes } = $props();
    let submitting = $state(false);
    let types = $state(initTypes);

    function getIndexById(id) {
        return types.findIndex(
            function(element) {
                return element.id == id;
            }
        );
    }

    let editingDisplayOrder = $state(false);
    let savingDisplayOrder = $state(false);
    let originDisplayOrder = [];

    function cancelEditDisplay(event) {
        types.splice(0);
        for(let row of originDisplayOrder) {
            types.push(row);
        }
        editingDisplayOrder = false;
    }

    let row;
    let updatingDisplayOrder = $state(false);

    function dragEnd(event) {
        types.splice(
            Array.from(event.target.parentNode.children).indexOf(row),
            0,
            types.splice(getIndexById(row.dataset.id), 1)[0]
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
                    route('admin.admission-test.types.display-order.update'),
                    updateDisplayOrderSuccessCallback,
                    updateDisplayOrderFailCallback,
                    'put', data
                );
            }
        }
    }

    function editDisplayOrder(event) {
        originDisplayOrder = [];
        for(let row of types) {
            originDisplayOrder.push(row);
        }
        editingDisplayOrder = true;
    }
</script>

<svelte:head>
    <title>Administration Admission Test Types | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h2 class="mb-2 fw-bold">
            Admission Test Types
            <Button color="primary" hidden={editingDisplayOrder}
                onclick={editDisplayOrder}>Edit Display Order</Button>
            <Button color="primary" disabled={submitting} hidden={! editingDisplayOrder}
                onclick={updateDisplayOrder}>
                {#if savingDisplayOrder}
                    <Spinner type="border" size="sm" />Saving Display Order...
                {:else}
                    Save Display Order
                {/if}
            </Button>
            <Button color="danger" hidden={! editingDisplayOrder || savingDisplayOrder}
                onclick={cancelEditDisplay}>Cancel</Button>
        </h2>
        {#if types.length}
            <Table hover>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Interval Month</th>
                        <th scope="col">Status</th>
                        <th scope="col">Edit</th>
                    </tr>
                </thead>
                <tbody>
                    {#each types as row}
                        <tr data-id="{row.id}"
                            ondragstart={dragStart} ondragover={dragOver} ondragend={dragEnd}
                            draggable="{editingDisplayOrder && ! updatingDisplayOrder}" class={{
                                draggable: editingDisplayOrder && ! updatingDisplayOrder
                            }}>
                            <td>{row.id}</td>
                            <td>{row.name}</td>
                            <td>{row.interval_month}</td>
                            <td>{row.is_active ? 'Active' : 'Inactive'}</td>
                            <td>
                                <Link class="btn btn-primary"
                                    href={
                                        route(
                                            'admin.admission-test.types.edit',
                                            {type: row.id}
                                        )
                                    }>Edit</Link>
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