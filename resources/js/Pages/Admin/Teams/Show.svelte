<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner, Table } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';

    let { auth, team } = $props();
    let roles = $state(team.roles);
    let submitting = $state(false);

    function getIndexById(id) {
        return roles.findIndex(
            function(element) {
                return element.id == id;
            }
        );
    }

    function deleteSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'delete').params.role;
        let index = getIndexById(id);
        roles.splice(index, 1);
        submitting = false;
    }

    function deleteFailCallback(error) {
        let id = route().match(error.request.responseURL, 'delete').params.team;
        let index = getIndexById(id);
        roles[index]['deleting'] = true;
        submitting = false;
    }

    function confirmedDelete(index) {
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'deleteTeam'+submitAt;
            if(submitting == 'deleteTeam'+submitAt) {
                roles[index]['deleting'] = true;
                post(
                    route(
                        'admin.teams.roles.destroy',
                        {
                            team: team.id,
                            role: roles[index]['id']
                        }
                    ),
                    deleteSuccessCallback,
                    deleteFailCallback,
                    'delete'
                );
            }
        }
    }

    function destroy(index) {
        let message = `Are you sure to delete the role of ${roles[index]['name']}?`;
        confirm(message, confirmedDelete, index);
    }

    let editingDisplayOrder = $state(false);
    let savingDisplayOrder = $state(false);
    let originDisplayOrder = [];

    function cancelEditDisplay(event) {
        roles.splice(0);
        for(let row of originDisplayOrder) {
            roles.push(row);
        }
        editingDisplayOrder = false;
    }

    let row;
    let updatingDisplayOrder = $state(false);

    function dragEnd(event) {
        roles.splice(
            Array.from(event.target.parentNode.children).indexOf(row),
            0,
            roles.splice(getIndexById(row.dataset.id), 1)[0]
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
                for(let row of roles) {
                    data.display_order.push(row.id);
                }
                post(
                    route(
                        'admin.teams.roles.display-order.update',
                        {team: team.id}
                    ),
                    updateDisplayOrderSuccessCallback,
                    updateDisplayOrderFailCallback,
                    'put', data
                );
            }
        }
    }

    function editDisplayOrder(event) {
        originDisplayOrder = [];
        for(let row of roles) {
            originDisplayOrder.push(row);
        }
        editingDisplayOrder = true;
    }
</script>

<svelte:head>
    <title>Administration Show Team | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h2 class="mb-2 fw-bold text-uppercase">
            Team
        </h2>
        <article>
            <h3 class="mb-2 fw-bold">
                Info
                {#if
                    auth.user.permissions.includes('Edit:Permission') ||
                    auth.user.roles.includes('Super Administrator')
                }
                    <Link class="btn btn-primary" href={
                        route('admin.teams.edit', {team: team.id})
                    }>Edit</Link>
                {/if}
            </h3>
            <table class="table">
                <tbody>
                    <tr>
                        <th>Type</th>
                        <td>{team.type.title ?? team.type.name}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{team.name}</td>
                    </tr>
                </tbody>
            </table>
        </article>
        <h3 class="mb-2 fw-bold">
            Roles
            {#if
                auth.user.permissions.includes('Edit:Permission') ||
                auth.user.roles.includes('Super Administrator')
            }
                <Link href={route('admin.teams.roles.create', {team: team.id})}
                    class="btn btn-success">Create</Link>
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
            {/if}
        </h3>
        <Table hover>
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    {#if
                        auth.user.permissions.includes('Edit:Permission') ||
                        auth.user.roles.includes('Super Administrator')
                    }
                        <th scope="col">Control</th>
                    {/if}
                </tr>
            </thead>
            <tbody id="tableBody">
                {#each roles as row, index}
                    <tr data-id="{row.id}"
                        ondragstart={dragStart} ondragover={dragOver} ondragend={dragEnd}
                        draggable="{editingDisplayOrder && ! updatingDisplayOrder}" class={{
                            draggable: editingDisplayOrder && ! updatingDisplayOrder
                        }}>
                        <th>{row.name}</th>
                        {#if
                            auth.user.permissions.includes('Edit:Permission') ||
                            auth.user.roles.includes('Super Administrator')
                        }
                            <td>
                                <Link class="btn btn-primary editRole"
                                    href={
                                        route(
                                            'admin.teams.roles.edit', 
                                            {
                                                team: team.id,
                                                role: row.id,
                                            }
                                        )
                                    }>Edit</Link>
                                <Button color="danger" disabled={submitting} onclick={() => destroy(index)}>
                                    {#if row.deleting}
                                        <Spinner type="border" size="sm" />Deleting...
                                    {:else}
                                        Delete
                                    {/if}
                                </Button>
                            </td>
                        {/if}
                    </tr>
                {/each}
            </tbody>
        </Table>
    </section>
</Layout>