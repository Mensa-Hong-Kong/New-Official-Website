<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner } from '@sveltestrap/sveltestrap';
	import Form from './Form.svelte';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { router } from '@inertiajs/svelte';
    
    let inputs = $state({});
    let feedbacks = $state({
        pathname: '',
        title: '',
        openGraphImageUrl: '',
        description: '',
        content: '',
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
                    case 'pathname':
                        feedbacks.pathname = value;
                        break;
                    case 'title':
                        feedbacks.title = value;
                        break;
                    case 'og_image_url':
                        feedbacks.openGraphImageUrl = value;
                        break;
                    case 'description':
                        feedbacks.description = value;
                        break;
                    case 'content':
                        feedbacks.content = value;
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
                        route('admin.custom-web-pages.store'),
                        successCallback,
                        failCallback,
                        'post', {
                            pathname: inputs.pathname.value,
                            title: inputs.title.value,
                            og_image_url: inputs.openGraphImageUrl.value,
                            description: inputs.description.value,
                            content: inputs.content.value,
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
    <title>Administration Create Custom Web Page | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <form method="POST" novalidate onsubmit="{create}">
            <h2 class="mb-2 fw-bold text-uppercase">Create Custom Web Page</h2>
            <Form bind:inputs={inputs} bind:feedbacks={feedbacks}
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