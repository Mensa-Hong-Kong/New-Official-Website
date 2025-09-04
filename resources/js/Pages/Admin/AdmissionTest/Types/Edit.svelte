<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner } from '@sveltestrap/sveltestrap';
    import Form from './Form.svelte';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { router } from '@inertiajs/svelte';

    let { displayOptions, type } = $props();
    let inputs = $state({});
    let feedbacks = $state({
        name: '',
        intervalMonth: '',
        displayOrder: '',
    });
    let submitting = $state(false);
    let updating = $state(false);
    let form;

    function successCallback(response) {
        updating = false;
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
                    case 'interval_month':
                        feedbacks.intervalMonth = value;
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
        updating = false;
        submitting = false;
    }

    function update(event) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'update'+submitAt;
            if(submitting == 'update'+submitAt) {
                if(form.validation()) {
                    updating = true;
                    post(
                        route(
                            'admin.admission-test.types.update',
                            {type: type.id}
                        ),
                        successCallback,
                        failCallback,
                        'put', {
                            name: inputs.name.value,
                            interval_month: inputs.intervalMonth.value,
                            is_active: inputs.isActive.checked,
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
    <title>Administration Edit Admission Test Type | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <form id="form" method="POST" novalidate onsubmit={update}>
            <h2 class="mb-2 fw-bold text-uppercase">Edit Admission Test Type</h2>
            <Form displayOptions={displayOptions} type={type}
                bind:inputs={inputs} bind:feedbacks={feedbacks}
                bind:submitting={updating} bind:this={form} />
            <Button color="primary" class="form-control" disabled={submitting}>
                {#if updating}
                    <Spinner type="border" size="sm" />Saving...
                {:else}
                    Save
                {/if}
            </Button>
        </form>
    </section>
</Layout>