<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner } from '@sveltestrap/sveltestrap';
	import Form from './Form.svelte';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { router } from '@inertiajs/svelte';
    
    let { page } = $props();
    let inputs = $state({});
    let feedbacks = $state({
        pathname: '',
        title: '',
        openGraphImageUrl: '',
        description: '',
        content: '',
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
                            'admin.custom-web-pages.update',
                            {custom_web_page: page.id}
                        ),
                        successCallback,
                        failCallback,
                        'put', {
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
    <title>Administration Edit Custom Web Page | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <form method="POST" novalidate onsubmit="{update}">
            <h2 class="mb-2 fw-bold text-uppercase">Edit Custom Web Page</h2>
            <Form bind:inputs={inputs} bind:feedbacks={feedbacks}
                page={page} bind:submitting={updating} bind:this={form} />
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