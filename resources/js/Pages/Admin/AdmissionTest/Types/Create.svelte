<script>
    import { seo } from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner } from '@sveltestrap/sveltestrap';
    import Form from './Form.svelte';
    import { post } from "@/submitForm";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { router } from '@inertiajs/svelte';

    seo.title = 'Administration Create Admission Test Type';

    let { displayOptions } = $props();
    let inputs = $state({});
    let feedbacks = $state({
        name: '',
        intervalMonth: '',
        minimumAge: '',
        maximumAge: '',
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
                    case 'interval_month':
                        feedbacks.intervalMonth = value;
                        break;
                    case 'minimum_age':
                        feedbacks.minimumAge = value;
                        break;
                    case 'maximum_age':
                        feedbacks.maximumAge = value;
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
                    let data = {
                        name: inputs.name.value,
                        interval_month: inputs.intervalMonth.value,
                        is_active: inputs.isActive.checked,
                        display_order: inputs.displayOrder.value,
                    }
                    if(inputs.minimumAge.value) {
                        data['minimum_age'] = inputs.minimumAge.value;
                    }
                    if(inputs.maximumAge.value) {
                        data['maximum_age'] = inputs.maximumAge.value;
                    }
                    post(
                        route('admin.admission-test.types.store'),
                        successCallback,
                        failCallback,
                        'post', data
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }
</script>

<section class="container">
    <form id="form" method="POST" novalidate onsubmit={create}>
        <h2 class="mb-2 fw-bold text-uppercase">Create Admission Test Type</h2>
        <Form displayOptions={displayOptions}
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
