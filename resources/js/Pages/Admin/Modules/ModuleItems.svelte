<script>
    import { InputGroup, InputGroupText, Input, Button, Spinner } from '@sveltestrap/sveltestrap';
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { post } from "@/submitForm";
	import { dndzone } from 'svelte-dnd-action';
    import ModuleItems from './ModuleItems.svelte';
	import { flip } from 'svelte/animate';

    let {
        auth, moduleNodes = $bindable(), moduleNode,
        editing, updating, submitting = $bindable()
    } = $props();
	const flipDurationMs = 300;
    let inputNames = $state({});

	function handleDndConsider(e) {
		moduleNode.children = e.detail.items;
	}
	function handleDndFinalize(e) {
		moduleNode.children = e.detail.items;
	}

    function nameValidation(input) {
        if(input.validity.valueMissing) {
            alert('The name field is required.');
            return false;
        } else if(input.validity.tooLong) {
            alert(`The name field must not be greater than ${input.maxLength} characters.`);
            return false;
        }
        return true;
    }

    function updateNameSuccessCallback(response) {
        alert(response.data.success);
        let location = new URL(response.request.responseURL);
        let id = route().match(location.host + location.pathname, 'PUT').params.module;
        inputNames[id].value = response.data.name;
        moduleNodes[id]['title'] = response.data.name;
        moduleNodes[id]['editing'] = false;
        moduleNodes[id]['updating'] = false;
        submitting = false;
    }

    function updateNameFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.name);
        }
        let location = new URL(error.request.responseURL);
        let id = route().match(location.host + location.pathname, 'PUT').params.module;
        moduleNodes[id]['updating'] = false;
        submitting = false;
    }

    function updateName(event, id) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateName'+submitAt;
            if(submitting == 'updateName'+submitAt) {
                if(nameValidation(inputNames[id])) {
                    moduleNodes[id]['updating'] = true;
                    post(
                        route(
                            'admin.modules.update',
                            {module: id}
                        ),
                        updateNameSuccessCallback,
                        updateNameFailCallback,
                        'put',
                        {name: inputNames[id].value}
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }

    function cancelEditName(id) {
        moduleNodes[id]['editing'] = false;
        inputNames[id].value = moduleNodes[id]['title'];
    }
</script>

{#if moduleNode && moduleNode.id != 'root'}
    <div class="row">
        <div class="col">
            <span hidden="{moduleNode.editing}">
                {#if moduleNode.title}
                    {moduleNode.title} ({moduleNode.name})
                {:else}
                    {moduleNode.name}
                {/if}
            </span>
            <form id="updateName{moduleNode.id}" method="POST" hidden="{! moduleNode.editing}" novalidate
                onsubmit={(event) => updateName(event, moduleNode.id)}>
                <InputGroup>
                    <Input name="name" maxlength=255
                        value={moduleNode.title} disabled={moduleNode.updating}
                        bind:inner={inputNames[moduleNode.id]} />
                    <InputGroupText>({moduleNode.name})</InputGroupText>
                </InputGroup>
            </form>
        </div>
        {#if
            auth.user.permissions.includes('Edit:Permission') ||
            auth.user.roles.includes('Super Administrator')
        }
            <div class="col text-end">
                <Button color="primary" hidden={moduleNode.editing || moduleNode.updating}
                    onclick={() => moduleNodes[moduleNode.id]['editing'] = true}>Edit</Button>
                <Button color="primary" form="updateName{moduleNode.id}"
                    hidden={! moduleNode.editing || moduleNode.updating} disabled={submitting}>Save</Button>
                <Button color="danger" hidden={! moduleNode.editing || moduleNode.updating}
                    onclick={() => cancelEditName(moduleNode.id)}>Cancel</Button>
                <Button color="primary" hidden={! moduleNode.updating} disabled>
                    <Spinner type="border" size="sm" />
                    Saving...
                </Button>
            </div>
        {/if}
    </div>
{/if}
{#if moduleNode && moduleNode.children}
    <section use:dndzone={{
            items:moduleNode.children, flipDurationMs,
            centreDraggedOnCursor: true,
            dragDisabled: ! editing || updating || submitting,
        }} onconsider={handleDndConsider} onfinalize={handleDndFinalize}>
        {#each moduleNode.children as item(item.id)}
            <article animate:flip="{{duration: flipDurationMs}}">
                <ModuleItems auth={auth} bind:moduleNodes={moduleNodes} moduleNode={moduleNodes[item.id]}
                    editing={editing} updating={updating} bind:submitting={submitting} />
            </article>
        {/each}
    </section>
{/if}

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
