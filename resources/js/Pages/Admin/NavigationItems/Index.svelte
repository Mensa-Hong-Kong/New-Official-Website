<script>
    import { page } from "@inertiajs/svelte";
    import { seo } from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner, Alert } from '@sveltestrap/sveltestrap';
    import NavigationItems from './NavigationItems.svelte';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';

    seo.title = 'Administration Navigation Items';

    let editing = $state(false);
    let updating = $state(false);
    let submitting = $state(false);
    let navNodes = $state({
        root: {
            id: 'root',
            children: [],
        }
    });
    let originNodes;

    for(let data of $page.props.navigationItems) {
        navNodes[data.id] = {
            id: data.id,
            children: [],
            name: data.name,
            url: data.url,
            deleting: false,
            disclose: false,
        };
    };
    for (let data of $page.props.navigationItems ) {
        navNodes[data.master_id ?? 'root']['children'].push({id: data.id});
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
        originNodes = $state.snapshot(navNodes);
        editing = true;
    }

    function cancel(event) {
        if(! updating && ! submitting) {
            navNodes = originNodes;
            editing = false;
        }
    }
</script>

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
