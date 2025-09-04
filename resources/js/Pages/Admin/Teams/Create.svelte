<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner } from '@sveltestrap/sveltestrap';
    import Form from './Form.svelte';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { router } from '@inertiajs/svelte';

    let { types, displayOptions } = $props();
    let inputs = $state({});
    let feedbacks = $state({
        name: '',
        type: '',
        displayOrder: '',
    });
    let submitting = $state(false);
    let creating = $state(false);
    let form;

    function successCallback(response) {
        creating = false;
        submitting = false;
        router.get(response.request.responseURL);
    }

    function failCallback(error) {
        if(error.status == 422) {
            for(let key in error.response.data.errors) {
                let value = error.response.data.errors[key];
                switch(key) {
                    case 'name':
                        feedbacks.name = value;
                        break;
                    case 'type_id':
                        feedbacks.type = value;
                        break;
                    case 'display_order':
                        feedbacks.displayOrder = value;
                        break;
                    default:
                        alert(`Undefine Feedback Key: ${key}\nMessage: ${message}`);
                        break;
                }
            }
        }
        creating = false;
        submitting = false;
    }
    
    function create(event) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'create'+submitAt;
            if(submitting == 'create'+submitAt) {
                if(form.validation()) {
                    creating = true;
                    post(
                        route('admin.teams.store'),
                        successCallback,
                        failCallback,
                        'post', {
                            name: inputs.name.value,
                            type_id: inputs.type.value,
                            display_order: inputs.displayOrder.value,
                        }
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }
</script>

<svelte:head>
    <title>Administration Create Team | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <form id="form" method="POST" novalidate onsubmit={create}>
            <h2 class="mb-2 fw-bold text-uppercase">Create Team</h2>
            <Form types={types} displayOptions={displayOptions}
                bind:inputs={inputs} bind:feedbacks={feedbacks}
                bind:submitting={creating} bind:this={form} />
            <Button color="success" class="form-control" disabled={submitting}>
                {#if creating}
                    <Spinner type="border" size="sm" />Creating...
                {:else}
                    Create
                {/if}
            </Button>
        </form>
    </section>
</Layout>