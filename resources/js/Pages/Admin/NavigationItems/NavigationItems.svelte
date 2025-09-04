<script>
    import { Button, Spinner } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';
	import { dndzone } from 'svelte-dnd-action';
    import NavigationItems from './NavigationItems.svelte';
	import { flip } from 'svelte/animate';

    let {navNodes = $bindable(), navNode, editing, updating, submitting = $bindable()} = $props();
	const flipDurationMs = 300;

    function removeNavNodeAndRelationNavNode(id) {
        let nodeKey = Object.keys(navNodes).find(
            function(key) {
                return navNodes[key]['children'].some(
                    function(child) {
                        return child.id == id;
                    }
                );
            }
        );
        let childIndex = navNodes[nodeKey]['children'].findIndex(
            function(element) {
                return element.id == id;
            }
        );
        navNodes[nodeKey]['children'].splice(childIndex, 1);
        for(let item of navNodes[id]['children']) {
            delete navNodes[item.id];
        }
        delete navNodes[id];
    }

    function deleteSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'delete').params.navigation_item;
        removeNavNodeAndRelationNavNode(id);
        submitting = false;
    }

    function deleteFailCallback(error) {
        let id = route().match(error.request.responseURL, 'delete').params.navigation_item;
        navNodes[id]['deleting'] = false;
        submitting = false;
    }

    function confirmedDelete(id) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'delete'+submitAt;
            if(submitting == 'delete'+submitAt) {
                navNodes[id]['deleting'] = true;
                post(
                    route(
                        'admin.navigation-items.destroy',
                        {navigation_item: id}
                    ),
                    deleteSuccessCallback,
                    deleteFailCallback,
                    'delete'
                );
            }
        }
    }

    function destroy(id) {
        let message = `Are you sure to delete the item of ${navNodes[id]['name']}?`;
        confirm(message, confirmedDelete, id);
    }

	function handleDndConsider(e) {
		navNode.children = e.detail.items;
	}
	function handleDndFinalize(e) {
		navNode.children = e.detail.items;
	}
</script>

{#if navNode && navNode.id != 'root'}
    <div class="row">
        <div class="col">
            <span class="itemTitle">{navNode.name}</span>
        </div>
        <div class="col text-end">
            {#if navNode.url}
                {#if route().match('navNode.url').name == undefined}
                    <a href={navNode.url} class="btn button btn-primary">Url</a>
                {:else}
                    <Link href={navNode.url} class="btn button btn-primary">Url</Link>
                {/if}
            {:else}
                <Button color="secondary">Url</Button>
            {/if}
            <Link href={
                route(
                    'admin.navigation-items.edit',
                    {navigation_item: navNode.id}
                )
            } class="btn button btn-primary">Edit</Link>
            <Button color="danger" onclick={() => destroy(navNode.id)} disabled={submitting || editing || updating}>
                {#if navNode.deleting}
                    <Spinner type="border" size="sm" />Deleting...
                {:else}
                    Delete
                {/if}
            </Button>
        </div>
    </div>
{/if}
{#if navNode && navNode.children}
    <section use:dndzone={{
            items:navNode.children, flipDurationMs,
            centreDraggedOnCursor: true, 
            dragDisabled: ! editing || updating || submitting,
            dropDisabled: ! editing || updating || submitting,
        }} onconsider={handleDndConsider} onfinalize={handleDndFinalize}>
        {#each navNode.children as item(item.id)}
            <article animate:flip="{{duration: flipDurationMs}}">
                <NavigationItems bind:navNodes={navNodes} navNode={navNodes[item.id]}
                    editing={editing} updating={updating} bind:submitting={submitting} />
            </article>
        {/each}
    </section>
{/if}

<style>
	section {
		width: auto;
		border: 0px solid black;
		padding: 0.4em 0 0.4em 1em;
		overflow-y: auto ;
		height: auto;
	}
	article {
		width: auto;
		padding: 0.3em 0 0.3em 1em;
		border: 0px solid blue;
		margin: 0.15em 0;
	}
</style>