<script> 
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner, Alert } from '@sveltestrap/sveltestrap';
    import NavigationItems from './NavigationItems.svelte';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';

    let { navigationItems, navigationNodes } = $props();
    let editing = $state(false);
    let updating = $state(false);
    let submitting = $state(false);
    let navNodes = $state({});
    let originNodes;

    for(let [key, children] of Object.entries(navigationNodes)) {
        navNodes[key] = {
            id: key,
            children: []
        };
        for(let id of children) {
            navNodes[key]['children'].push({id: id});
        }
        if(key != 'root') {
            navNodes[key]['name'] = navigationItems[key]['name'];
            navNodes[key]['url'] = navigationItems[key]['url'];
            navNodes[key]['deleting'] = false;
            navNodes[key]['disclose'] = false;
        }
    }

    function updateSuccessCallback(response) {
        editing = false;
        alert(response.data.success);
        for(let key in navNodes) {
            navNodes[key]['children'] = [];
            if(response.data.display_order[key == 'root' ? '0' : key]) {
                for(let childID of response.data.display_order[key == 'root' ? '0' : key]) {
                    navNodes[key]['children'].push({id: childID});
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
                for(let [key, item] of Object.entries(navNodes)) {
                    if(item.children.length) {
                        data['display_order'][key == 'root' ? 0 : key] = item.children.map(item => item.id);
                    }
                }
                post(
                    route('admin.other-payment-gateways.display-order.update'),
                    updateSuccessCallback,
                    updateFailCallback,
                    'put', data
                );
            }
        }
    }

    function edit() {
        originNodes = navNodes;
        editing = true;
    }

    function cancel(event) {
        if(! updating && ! submitting) {
            navNodes = originNodes;
            editing = false;
        }
    }
</script>

<svelte:head>
    <title>Administration Navigation Items | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h2 class="mb-2 fw-bold text-uppercase">
            Navigation Items
            {#if navNodes.root.children.length}
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
        {#if navNodes.root.children.length}
            <NavigationItems bind:navNodes={navNodes} navNode={navNodes.root}
                editing={editing} updating={updating} bind:submitting={submitting} />
        {:else}
            <Alert color="danger">
                No Result
            </Alert>
        {/if}
    </section>
</Layout>