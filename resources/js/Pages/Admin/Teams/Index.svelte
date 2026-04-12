<script>
    import { seo } from '@/Pages/Layouts/App.svelte';
    import { post } from "@/submitForm";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';
    import { move } from '@dnd-kit/helpers';
    import { can } from "@/gate.ts";
    import { Button, Spinner, TabContent, TabPane, Table } from '@sveltestrap/sveltestrap';
    import { DragDropProvider } from '@dnd-kit/svelte';
    import { createSortable } from '@dnd-kit/svelte/sortable';
    import { Link } from "@inertiajs/svelte";

    seo.title = 'Administration Teams';

    let { types: initTypes } = $props();
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

    let snapshot = [];
    let updatingDisplayOrder = $state(false);

    function getTeamIndex(id) {
        return types[currentTypeIndex]["teams"].findIndex(
            function(element) {
                return element.id == id;
            }
        );
    }

    function onDragStart() {
        snapshot = types[currentTypeIndex]["teams"].slice();
    }

    function onDragOver(event) {
        types[currentTypeIndex]["teams"] = move(types[currentTypeIndex]["teams"], event);
    }

    function onDragEnd(event) {
        if (event.canceled) types[currentTypeIndex]["teams"] = snapshot;
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

<section class="container">
    <h2 class="mb-2 fw-bold text-uppercase">
        Teams
        {#if can('Edit:Permission')}
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
                <DragDropProvider {onDragStart} {onDragOver} {onDragEnd}>
                    <Table hover>
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Control</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each type.teams as team, teamIndex}
                                {@const sortable = createSortable({
                                    id: team.id,
                                    index: () => teamIndex,
                                    disabled: ! editingDisplayOrder || updatingDisplayOrder || currentTypeIndex != typeIndex
                                })}
                                <tr {@attach ! editingDisplayOrder || updatingDisplayOrder || currentTypeIndex != typeIndex ? null : sortable.attach}>
                                    <th>{team.name}</th>
                                    <td>
                                        <Link class="btn btn-primary"
                                            href={
                                                route(
                                                    'admin.teams.show',
                                                    {team: team.id}
                                                )
                                            }>Show</Link>
                                        {#if can('Edit:Permission')}
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
                </DragDropProvider>
            </TabPane>
        {/each}
    </TabContent>
</section>
