<script>
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';
    import { Row, Col, Input, Button, Spinner } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    
    let { proctors: initProctors, submitting = $bindable() } = $props();
    let proctors = $state([]);
    let inputs = $state({
        proctors: []
    });

    for(let row of initProctors) {
        inputs['proctors'].push({});
        proctors.push({
            id: row.id,
            name: row.adorned_name,
            editing: false,
            updating: false,
            deleting: false,
        });
    }

    function getIndexById(id) {
        return proctors.findIndex(
            function(element) {
                return element.id == id;
            }
        );
    }

    function validation(input)
    {
        if(input.validity.valueMissing) {
            alert('The user id field is required.');
            return false;
        }
        if(input.validity.patternMismatch) {
            alert('The user id field must be an integer.');
            return false;
        }
        return true;
    }

    function close(index) {
        inputs['proctors'][index]['user'].value = proctors[index]['id'];
        proctors[index]['editing'] = false;
    }

    function updateSuccessCallback(response) {
        let id = route().match(response.request.responseURL, 'put').params.proctor;
        let index = getIndexById(id);
        alert(response.data.success);
        proctors[index]['id'] = response.data.user_id;
        proctors[index]['name'] = response.data.name;
        close(index);
        proctors[index]['updating'] = false;
        submitting = false;
    }

    function updateFailCallback(error) {
        let id = route().match(error.request.responseURL, 'put').params.proctor;
        let index = getIndexById(id);
        if(error.status == 422) {
            alert(error.response.data.errors.user_id);
        }
        proctors[index]['updating'] = false;
        submitting = false;
    }

    function update(event, index) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateProctor'+submitAt;
            if(submitting == 'updateProctor'+submitAt) {
                if(validation(inputs['proctors'][index]['user'])) {
                    proctors[index]['updating'] = true;
                    post(
                        route(
                            'admin.admission-tests.proctors.update',
                            {
                                admission_test: route().params.admission_test,
                                proctor: proctors[index]['id'],
                            }
                        ),
                        updateSuccessCallback,
                        updateFailCallback,
                        'put', {user_id: inputs['proctors'][index]['user']['value']}
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }

    function edit(event, index) {
        event.preventDefault();
        proctors[index]['editing'] = true;
    }

    function cancel(event, index) {
        event.preventDefault();
        close(index)
    }

    function deleteSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'delete').params.proctor;
        let index = getIndexById(id);
        proctors.splice(index, 1);
        submitting = false;
    }

    function deleteFailCallback(error) {
        let id = route().match(error.request.responseURL, 'delete').params.proctor;
        let index = getIndexById(id);
        proctors[index]['deleting'] = false;
        submitting = false;
    }

    function confirmedDelete(index) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'deleteProctor'+submitAt;
            if(submitting == 'deleteProctor'+submitAt) {
                proctors[index]['deleting'] = true;
                post(
                    route(
                        'admin.admission-tests.proctors.destroy',
                        {
                            admission_test: route().params.admission_test,
                            proctor: proctors[index]['id'],
                        }
                    ),
                    deleteSuccessCallback,
                    deleteFailCallback,
                    'delete'
                );
            }
        }
    }

    function destroy(event, index) {
        event.preventDefault();
        let message = `Are you sure to delete the proctor of ${proctors[index]['name']}?`;
        confirm(message, confirmedDelete, index);
    }

    let creating = $state(false);

    function createSuccessCallback(response) {
        alert(response.data.success);
        inputs.proctors.push({});
        proctors.push({
            id: response.data.user_id,
            name: response.data.name,
            editing: false,
            updating: false,
            deleting: false,
        });
        inputs.user.value = '';
        creating = false;
        submitting = false;
    }

    function createFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.user_id);
        }
        creating = false;
        submitting  = false;
    }

    function create(event) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'addProctor'+submitAt;
            if(submitting == 'addProctor'+submitAt) {
                if(validation(inputs.user)) {
                    creating = true;
                    post(
                        route(
                            'admin.admission-tests.proctors.store',
                            {admission_test: route().params.admission_test}
                        ),
                        createSuccessCallback,
                        createFailCallback,
                        'post', {user_id: inputs.user.value}
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }
</script>

<article>
    <h3 class="mb-2 fw-bold">Proctors</h3>
    <Row class="g-3">
        <Col md=1>User ID</Col>
        <Col md=2>Name</Col>
        <Col md=3>Control</Col>
    </Row>
    {#each proctors as row, index}
        <form class="row g-3" novalidate onsubmit={(event) => update(event, index)}>
            <Col md=1>
                <span hidden={row.editing}>{row.id}</span>
                <Input name="user_id" hidden={! row.editing}
                    value={row.id} patten="^\+?[1-9][0-9]*" required 
                    bind:inner={inputs['proctors'][index]['user']} />
            </Col>
            <Col md=2>{row.name}</Col>
            <Link class="btn btn-primary col-md-1"
                href={
                    route(
                        'admin.users.show',
                        {user: row.id}
                    )
                }>Show</Link>
            <Button color="primary" class="col-md-1"
                hidden={row.editing || row.updating || row.deleting}
                onclick={(event) => edit(event, index)}>Edit</Button>
            <Button color="primary" disabled={submitting} 
                hidden={! row.editing} class={[{
                    "col-md-1": ! row.updating,
                    "col-md-2": row.updating,
                }]}>
                {#if row.updating}
                    <Spinner type="border" size="sm" />Saving...
                {:else}
                    Save
                {/if}
            </Button>
            <Button color="danger" class="col-md-1"
                hidden={! row.editing || row.updating}
                onclick={(event) => cancel(event, index)}>Cancel</Button>
            <Button color="danger" disabled={submitting} 
                hidden={row.editing || row.updating}
                onclick={() => destroy(event, index)} class={[{
                    "col-md-1": ! row.deleting,
                    "col-md-2": row.deleting,
                }]}>
                {#if row.deleting}
                    <Spinner type="border" size="sm" />Deleting...
                {:else}
                    Delete
                {/if}
            </Button>
        </form>
    {/each}
    <form class="row g-3" method="POST" novalidate onsubmit={create}>
        <Col md=1>
            <Input name="user_id" patten="^\+?[1-9][0-9]*" required 
                bind:inner={inputs.user} />
        </Col>
        <Col md=2 />
        <Button color="success" class="col-md-3" disabled={submitting}>
            {#if creating}
                <Spinner type="border" size="sm" />Adding...
            {:else}
                Add
            {/if}
        </Button>
    </form>
</article>