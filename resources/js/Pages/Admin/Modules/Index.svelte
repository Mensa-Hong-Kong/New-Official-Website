<script>
    import { seo } from '@/Pages/Layouts/App.svelte';
    import ModuleItems from './ModuleItems.svelte';
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { post } from "@/submitForm.svelte";
    import { Button, Spinner, Alert } from '@sveltestrap/sveltestrap';

    seo.title = 'Administration Modules';

    let { auth, modules } = $props();
    let editing = $state(false);
    let updating = $state(false);
    let submitting = $state(false);
    let moduleNodes = $state({
        root: {
            id: 'root',
            children: [],
        }
    });
    let originNodes;
    for(let module of modules) {
        moduleNodes[module.id] = {
            id: module.id,
            children: [],
            name: module.name,
            title: module.title,
            editing: false,
            updating: false,
        };
    };
    for (let module of modules) {
        moduleNodes[module.master_id ?? 'root']['children'].push({id: module.id});
    }

    function updateSuccessCallback(response) {
        editing = false;
        alert(response.data.success);
        for(let key in moduleNodes) {
            moduleNodes[key]['children'] = [];
            if(response.data.display_order[key == 'root' ? '0' : key]) {
                for(let childID of response.data.display_order[key == 'root' ? '0' : key]) {
                    moduleNodes[key]['children'].push({id: childID});
                }
            }
        }
        updating = false;
        submitting = false;
    }

    function updateFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.display_order);
        }
        updating = false;
        submitting = false;
    }

    function update() {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateDisplayOrder'+submitAt;
            if(submitting == 'updateDisplayOrder'+submitAt) {
                updating = true;
                let data = {display_order: {}};
                for(let [key, item] of Object.entries(moduleNodes)) {
                    if(item.children.length) {
                        data['display_order'][key == 'root' ? 0 : key] = item.children.map(item => item.id);
                    }
                }
                post(
                    route('admin.modules.display-order.update'),
                    updateSuccessCallback,
                    updateFailCallback,
                    'put', data
                );
            }
        }
    }

    function edit() {
        originNodes = $state.snapshot(moduleNodes);
        editing = true;
    }

    function cancel(event) {
        if(! updating && ! submitting) {
            moduleNodes = originNodes;
            editing = false;
        }
    }
</script>

<section class="container">
    <h2 class="mb-2 fw-bold text-uppercase">
        Modules
        {#if
            (
                auth.user.permissions.includes('Edit:Permission') ||
                auth.user.roles.includes('Super Administrator')
            ) && moduleNodes.root.children.length
        }
            <Button color="primary" onclick={edit} hidden={editing} disabled={submitting}>Edit Display Order</Button>
            <Button color="primary" onclick={update} hidden={! editing}>
                {#if updating}
                    <Spinner type="border" size="sm" />Saving Display Order...
                {:else}
                    Save Display Order
                {/if}
            </Button>
            <Button color="danger" onclick={cancel} hidden={! editing || updating}>Cancel</Button>
        {/if}
    </h2>
    {#if moduleNodes.root.children.length}
        <ModuleItems auth={auth} bind:moduleNodes={moduleNodes} moduleNode={moduleNodes.root}
            editing={editing} updating={updating} bind:submitting={submitting} />
    {:else}
        <Alert color="danger">
            No Result
        </Alert>
    {/if}
</section>

<style>
	section {
		border: 0px solid black;
		padding: 0.4em 0 0.4em 1em;
		overflow-y: auto ;
		height: auto;
        overflow-x: hidden;
        width: 100%;
	}
	article {
		width: auto;
		padding: 0.3em 0 0.3em 1em;
		border: 0px solid blue;
		margin: 0.15em 0;
	}
</style>
