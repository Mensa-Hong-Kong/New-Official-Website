<script>
    import { Button, Spinner } from '@sveltestrap/sveltestrap';
    import Form from './Form.svelte';
    import { post } from "@/submitForm.svelte";

    let { types, displayOptions, team } = $props();
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
        window.location.href = response.request.responseURL;
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
    function update(event) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'update'+submitAt;
            if(submitting == 'update'+submitAt) {
                if(form.validation()) {
                    creating = true;
                    post(
                        route(
                            'admin.teams.update',
                            {team: team.id}
                        ),
                        successCallback,
                        failCallback,
                        'put', {
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

<section class="container">
    <form id="form" method="POST" novalidate onsubmit={update}>
        <h2 class="mb-2 fw-bold text-uppercase">Edit Team</h2>
        <Form types={types} displayOptions={displayOptions} team={team}
            bind:inputs={inputs} bind:feedbacks={feedbacks}
            bind:submitting={creating} bind:this={form} />
        <Button color="primary" class="form-control" disabled={submitting}>
            {#if creating}
                <Spinner type="border" size="sm" />Saving...
            {:else}
                Save
            {/if}
        </Button>
    </form>
</section>