<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner, TabContent, TabPane, Table } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';
    
    let { auth, types: initTypes } = $props();
    let submitting = $state(false);
    let types = $state(initTypes);

    function getTypeAndTeamIndexById(id) {
        for(let typeIndex in types) {
            for(let teamIndex in types[typeIndex]['teams']) {
                if(types[typeIndex]['teams'][teamIndex]['id'] == id) {
                    return [typeIndex, teamIndex];
                }
            }
        }
        return [];
    }

    function deleteSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'delete').params.team;
        let [typeIndex, teamIndex] = getTypeAndTeamIndexById(id);
        types[typeIndex]['teams'].splice(teamIndex, 1);
        submitting = false;
    }

    function deleteFailCallback(error) {
        let id = route().match(error.request.responseURL, 'delete').params.team;
        let [typeIndex, teamIndex] = getTypeAndTeamIndexById(id);
        types[typeIndex]['teams'][teamIndex]['deleting'] = false;
        submitting = false;
    }

    function confirmedDelete(indexes) {
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'deleteTeam'+submitAt;
            let [typeIndex, teamIndex] = indexes;
            if(submitting == 'deleteTeam'+submitAt) {
                types[typeIndex]['teams'][teamIndex]['deleting'] = true;
                post(
                    route(
                        'admin.teams.destroy',
                        {team: types[typeIndex]['teams'][teamIndex]['id']}
                    ),
                    deleteSuccessCallback,
                    deleteFailCallback,
                    'delete'
                );
            }
        }
    }

    function destroy(typeIndex, teamIndex) {
        let message = `Are you sure to delete the team of ${types[typeIndex]['teams'][teamIndex]['name']}?`;
        confirm(message, confirmedDelete, [typeIndex, teamIndex]);
    }

    let editingDisplayOrder = $state(false);
    let savingDisplayOrder = $state(false);
    let originDisplayOrder = [];
    let currentTypeIndex;

    function cancelEditDisplay(event) {
        types[currentTypeIndex]["teams"].splice(0);
        for(let row of originDisplayOrder) {
            types[currentTypeIndex]["teams"].push(row);
        }
        editingDisplayOrder = false;
    }

    let row;
    let updatingDisplayOrder = $state(false);

    function getTeamIndex(id) {
        return types[currentTypeIndex]["teams"].findIndex(
            function(element) {
                return element.id == id;
            }
        );
    }

    function dragEnd(event) {
        types[currentTypeIndex]["teams"].splice(
            Array.from(event.target.parentNode.children).indexOf(row),
            0,
            types[currentTypeIndex]["teams"].splice(getTeamIndex(row.dataset.id), 1)[0]
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
                let data = {
                    type_id: types[currentTypeIndex]['id'],
                    display_order: [],
                };
                for(let row of types[currentTypeIndex]["teams"]) {
                    data.display_order.push(row.id);
                }
                post(
                    route('admin.teams.display-order.update'),
                    updateDisplayOrderSuccessCallback,
                    updateDisplayOrderFailCallback,
                    'put', data
                );
            }
        }
    }

    function editDisplayOrder(event) {
        originDisplayOrder = [];
        for(let row of types[currentTypeIndex]["teams"]) {
            originDisplayOrder.push(row);
        }
        editingDisplayOrder = true;
    }
</script>


<svelte:head>
    <title>Administration Teams | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h2 class="mb-2 fw-bold text-uppercase">
            Teams
            {#if
                auth.user.permissions.includes('Edit:Permission') ||
                auth.user.roles.includes('Super Administrator')
            }
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
        </h2>
        <TabContent pills on:tab={(e) => (currentTypeIndex = e.detail)}>
            {#each types as type, typeIndex}
                <TabPane tabId={typeIndex} tab={type.title ?? type.name}
                    disabled={editingDisplayOrder} active={typeIndex == 0}>
                    <Table hover>
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Control</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each type.teams as team, teamIndex}
                                <tr data-id="{team.id}" ondragstart={dragStart}
                                    ondragover={dragOver} ondragend={dragEnd}
                                    draggable="{
                                        editingDisplayOrder &&
                                        ! updatingDisplayOrder &&
                                        currentTypeIndex == typeIndex
                                    }" class={{
                                        draggable: editingDisplayOrder &&
                                            ! updatingDisplayOrder &&
                                            currentTypeIndex == typeIndex
                                    }}>
                                    <th>{team.name}</th>
                                    <td>
                                        <Link class="btn btn-primary"
                                            href={
                                                route(
                                                    'admin.teams.show',
                                                    {team: team.id}
                                                )
                                            }>Show</Link>
                                        {#if
                                            auth.user.permissions.includes('Edit:Permission') ||
                                            auth.user.roles.includes('Super Administrator')
                                        }
                                            <Button color="danger" disabled={submitting} onclick={() => destroy(typeIndex, teamIndex)}>
                                                {#if team.deleting}
                                                    <Spinner type="border" size="sm" />Deleting...
                                                {:else}
                                                    Delete
                                                {/if}
                                            </Button>
                                        {/if}
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </Table>
                </TabPane>
            {/each}
        </TabContent>
    </section>
</Layout>