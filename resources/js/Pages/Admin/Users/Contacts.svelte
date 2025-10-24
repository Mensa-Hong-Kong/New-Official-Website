<script>
    import { Row, Button, Spinner, Col, Input } from '@sveltestrap/sveltestrap';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';
    let { auth, type, contacts: initContacts, submitting = $bindable(), defaultContact = $bindable() } = $props();
    let contacts = $state([]);
    let createInputs = $state({});
    let creating = $state(false);
    let updateInputs = $state([]);

    for(let row of initContacts) {
        updateInputs.push({});
        contacts.push({
            id: row.id,
            contact: row.contact,
            isDefault: row.is_default,
            isVerified: row.is_verified,
            updatingDefaultStatus: false,
            updatingVerifyStatus: false,
            editing: false,
            updating: false,
            deleting: false,
        });
    }

    function isVerifyCheckboxOnChange(event, isDefaultCheckbox) {
        if(! event.target.checked) {
            isDefaultCheckbox.checked = false;
        }
    }

    function isDefaultCheckboxOnChange(event, isVerifyCheckbox) {
        if(event.target.checked) {
            isVerifyCheckbox.checked = true;
        }
    }

    function getIndexById(id) {
        return contacts.findIndex(
            function(row) {
                return row.id == id;
            }
        );
    }

    function updateVerifyStatusSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'put').params.contact;
        let index = getIndexById(id);
        contacts[index]['isVerified'] = response.data.status;
        if(! response.data.status) {
            contacts[index]['isDefault'] = false;
            if(defaultContact == id) {
                defaultContact = null;
            }
        }
        contacts[index]['updatingVerifyStatus'] = false;
        submitting = false;
    }

    function updateVerifyStatusFailsCallback(error) {
        if(error.response.data.status) {
            alert(error.response.data.status);
        }
        let id = route().match(error.request.responseURL, 'put').params.contact;
        let index = getIndexById(id);
        contacts[index]['updatingVerifyStatus'] = false;
        submitting = false;
    }

    function updateVerifyStatus(index) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateVerifyStatus'+submitAt;
            if(submitting == 'updateVerifyStatus'+submitAt) {
                contacts[index]['updatingVerifyStatus'] = true;
                post(
                    route(
                        'admin.contacts.verify',
                        {contact: contacts[index]['id']}
                    ),
                    updateVerifyStatusSuccessCallback,
                    updateVerifyStatusFailsCallback,
                    'put', {status: ! contacts[index]['isVerified']}
                );
            }
        }
    }

    function updateDefaultStatusSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'put').params.contact;
        let index = getIndexById(id);
        if(response.data.status) {
            contacts[index]['isVerified'] = true;
            for(let index in contacts) {
                contacts[index]['isDefault'] = contacts[index]['id'] == id;
            }
            defaultContact = contacts[index]['id'];
        } else {
            if(defaultContact == id) {
                defaultContact = null;
            }
            contacts[index]['isDefault'] = false;
        }
        contacts[index]['updatingDefaultStatus'] = false;
        submitting = false;
    }

    function updateDefaultStatusFailsCallback(error) {
        if(error.response.data.status) {
            alert(error.response.data.status);
        }
        let id = route().match(error.request.responseURL, 'put').params.contact;
        let index = getIndexById(id);
        contacts[index]['updatingDefaultStatus'] = false;
        submitting = false;
    }

    function updateDefaultStatus(index) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateDefaultStatus'+submitAt;
            if(submitting == 'updateDefaultStatus'+submitAt) {
                contacts[index]['updatingDefaultStatus'] = true;
                post(
                    route(
                        'admin.contacts.default',
                        {contact: contacts[index]['id']}
                    ),
                    updateDefaultStatusSuccessCallback,
                    updateDefaultStatusFailsCallback,
                    'put', {status: ! contacts[index]['isDefault']}
                );
            }
        }
    }

    function resetInputValues(index) {
        updateInputs[index]['isVerify'].checked = contacts[index]['isVerified'];
        updateInputs[index]['isDefault'].checked = contacts[index]['isDefault'];
        updateInputs[index]['contact'].value = contacts[index]['contact'];
    }

    function validation(contactInput) {
        if(contactInput.validity.valueMissing) {
            alert(`The ${contactInput.name} field is required.`);
            return false;
        }
        if(type == 'mobile' && contactInput.validity.tooShort) {
            alert(`The ${contactInput.name} be at least ${contactInput.minLength} characters.`);
            return false;
        }
        if(contactInput.validity.tooLong) {
            alert(`The ${contactInput.name} must not be greater than ${contactInput.maxLength} characters.`);
            return false;
        }
        if(type == 'email' && contactInput.validity.typeMismatch) {
            alert(`The email must be a valid email address.`);
            return false;
        }
        return true;
    }

    function updateSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'put').params.contact;
        let index = getIndexById(id);
        contacts[index]['isVerified'] = response.data.is_verified;
        contacts[index]['contact'] = response['data'][type];
        if(response.data.is_default) {
            for(let key in contacts) {
                contacts[key]['isDefault'] = contacts[key]['id'] == id;
            }
        } else {
            if(defaultContact == id) {
                defaultContact = null;
            }
            contacts[index]['isDefault'] = false;
        }
        contacts[index]['editing'] = false;
        resetInputValues(index);
        contacts[index]['updating'] = false;
        submitting = false;
    }

    function updateFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors[input.name]);
        }
        let id = route().match(error.request.responseURL, 'put').params.contact;
        let index = getIndexById(id);
        contacts[index]['updating'] = false;
        submitting = false;
    }

    function update(event, index) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateContact'+submitAt;
            if(submitting == 'updateContact'+submitAt) {
                if(validation(updateInputs[index]['contact'])) {
                    contacts[index]['updating'] = true;
                    let data = {
                        is_verified: updateInputs[index]['isVerify'].checked,
                        is_default: updateInputs[index]['isDefault'].checked,
                    };
                    data[type] = updateInputs[index]['contact'].value;
                    post(
                        route(
                            'admin.contacts.update',
                            {contact: contacts[index]['id']}
                        ),
                        updateSuccessCallback,
                        updateFailCallback,
                        'put', data
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }

    function cancel(event, index) {
        event.preventDefault();
        contacts[index]['editing'] = false;
        resetInputValues(index);
    }

    function deleteSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'delete').params.contact;
        let index = getIndexById(id);
        updateInputs.splice(index, 1);
        contacts.splice(index, 1);
        submitting = false;
    }

    function deleteFailCallback(error) {
        let id = route().match(error.request.responseURL, 'delete').params.contact;
        let index = getIndexById(id);
        contacts[index]['deleting'] = false;
        submitting = false;
    }

    function confirmedDelete(index) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'deleteContact'+submitAt;
            if(submitting == 'deleteContact'+submitAt) {
                contacts[index]['deleting'] = true;
                post(
                    route(
                        'admin.contacts.destroy',
                        {contact: contacts[index]['id']}
                    ),
                    deleteSuccessCallback,
                    deleteFailCallback,
                    'delete'
                );
            }
        }
    }

    function destroy(index) {
        let message = `Are you sure to delete the ${type} of ${contacts[index]['contact']}?`;
        confirm(message, confirmedDelete, index);
    }

    function createSuccessCallback(response) {
        alert(response.data.success);
        updateInputs.push({});
        if(response.data.is_default) {
            if(defaultContact) {
                contacts[getIndexById(defaultContact)]['isDefault'] = false;
            }
            defaultContact = response.data.id;
        }
        contacts.push({
            id: response.data.id,
            contact: response.data.contact,
            isDefault: response.data.is_default,
            isVerified: response.data.is_verified,
            updatingDefaultStatus: false,
            updatingVerifyStatus: false,
            editing: false,
            updating: false,
            deleting: false,
        });
        createInputs.contact.value = '';
        createInputs.isVerify.checked = false;
        createInputs.isDefault.checked = false;
        creating = false;
        submitting = false;
    }

    function createFailCallback(error) {
        if(error.status == 422) {
            if(error.response.data.errors.message) {
                alert(error.response.data.errors.message);
            } else if(error.response.data.errors.contact){
                alert(error.response.data.errors.contact);
            }
        }
        creating = false;
        submitting = false;
    }

    function create(event) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'createContact'+submitAt;
            if(submitting == 'createContact'+submitAt) {
                if(validation(createInputs.contact)) {
                    creating = true;
                    post(
                        route('admin.contacts.store'),
                        createSuccessCallback,
                        createFailCallback,
                        'post', {
                            user_id: route().params.user,
                            type: type,
                            is_verified: createInputs.isVerify.checked,
                            is_default: createInputs.isDefault.checked,
                            contact: createInputs.contact.value,
                        }
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }
</script>

<article>
    <h3 class="mb-2 fw-bold">
        {#if type == 'email'}
            <i class="bi bi-envelope"></i>Email
        {:else if type == 'mobile'}
            <i class="bi bi-phone"></i> Mobile
        {/if}
    </h3>
    {#if 
        auth.user.permissions.includes('Edit:User') ||
        auth.user.roles.includes('Super Administrator')
    }
        {#each contacts as row, index}
            <Row class="g-3" hidden={row.editing}>
                <Col md=3>{row.contact}</Col>
                <Col md=2>
                    <Button name="status" color={row.isVerified ? 'success' : 'danger'}
                        class="form-control" value={! row.isVerified} disabled={submitting}
                        onclick={() => updateVerifyStatus(index)}>
                        {#if row.updatingVerifyStatus}
                            <Spinner type="border" size="sm" />Changing...
                        {:else}
                            {row.isVerified ? 'Verified' : 'Not Verified'}
                        {/if}
                    </Button>
                </Col>
                <Col md=2>
                    <Button name="status" color={row.isDefault ? 'success' : 'danger'}
                        class="form-control" value={! row.isDefault} disabled={submitting}
                        onclick={() => updateDefaultStatus(index)}>
                        {#if row.updatingDefaultStatus}
                            <Spinner type="border" size="sm" />Changing...
                        {:else}
                            {row.isVerified ? 'Default' : 'Non Default'}
                        {/if}
                    </Button>
                </Col>
                <Button color="primary" class="col-md-1" hidden={row.deleting}
                    onclick={() => contacts[index]['editing'] = true}>Edit</Button>
                <Button color="danger" class="col-md-1" hidden={row.deleting}
                    onclick={() => destroy(index)}>Delete</Button>
                <Button color="danger" class="col-md-2" hidden={! row.deleting} disabled>
                    <Spinner type="border" size="sm" />Deleting...
                </Button>
            </Row>
            <form class="row g-3" method="POST" hidden={! row.editing}
                onsubmit="{(event) => update(event, index)}">
                <Col md=3>
                    {#if type == 'email'}
                        <Input name="contact" type="email" placeholder="dammy@example.com"
                            maxlength="320" required disabled={row.updating} value={row.contact}
                            bind:inner="{updateInputs[index]['contact']}" />
                    {:else if type == 'mobile'}
                        <Input name="contact" type="tel" placeholder="85298765432" value={row.contact}
                            minlength="5" maxlength="15" required  disabled={row.updating}
                            bind:inner="{updateInputs[index]['contact']}" />
                    {/if}
                </Col>
                <Col md=2>
                    <input type="checkbox" class="btn-check" id="isVerify{row.id}"
                        disabled={row.updating} checked={row.isVerified}
                        bind:this="{updateInputs[index]['isVerify']}"
                        onchange={(event) => isVerifyCheckboxOnChange(event, updateInputs[index]['isDefault'])} />
                    <label class="form-control btn btn-outline-success" for="isVerify{row.id}">Verified</label>
                </Col>
                <Col md=2>
                    <input type="checkbox" class="btn-check" id="isDefault{row.id}"
                        disabled={row.updating} checked={row.isDefault}
                        bind:this="{updateInputs[index]['isDefault']}"
                        onchange="{(event) => isDefaultCheckboxOnChange(event, updateInputs[index]['isVerify'])}" />
                    <label class="form-control btn btn-outline-success" for="isDefault{row.id}">Default</label>
                </Col>
                <Button color="primary" class="col-md-1" disabled={submitting}
                    hidden={row.updating}>Save</Button>
                <Button color="danger" class="col-md-1" hidden={row.updating}
                    onclick={(event) => cancel(event, index)}>Cancel</Button>
                <Button color="primary" class="col-md-2" disabled hidden={! row.updating}>
                    <Spinner type="border" size="sm" />Saving...
                </Button>
            </form>
        {/each}
        <form class="row g-3" method="POST" novalidate onsubmit="{create}">
            <Col md=3>
                {#if type == 'email'}
                    <Input name="email" type="email" placeholder="dammy@example.com"
                        maxlength="320" required disabled={creating}
                        bind:inner="{createInputs['contact']}" />
                {:else if type == 'mobile'}
                    <Input name="mobile" type="tel" placeholder="85298765432"
                        minlength="5" maxlength="15" required disabled={creating}
                        bind:inner="{createInputs['contact']}" />
                {/if}
            </Col>
            <Col md=2>
                <input type="checkbox" class="btn-check" id="{type}IsVerify"
                    disabled={creating} bind:this={createInputs.isVerify}
                    onchange="{(event) => isVerifyCheckboxOnChange(event, createInputs.isDefault)}" />
                <label class="form-control btn btn-outline-success" for="{type}IsVerify">Verified</label>
            </Col>
            <Col md=2>
                <input type="checkbox" class="btn-check" id="{type}IsDefault"
                disabled={creating} bind:this={createInputs['isDefault']}
                onchange={(event) => isDefaultCheckboxOnChange(event, createInputs.isVerify)} />
                <label class="form-control btn btn-outline-success" for="{type}IsDefault">Default</label>
            </Col>
            <Button color="success" class="col-md-2" disabled={submitting}
                hidden={creating}>Create</Button>
            <Button color="success" class="col-md-2" disabled hidden={! creating}>
                <Spinner type="border" size="sm" />Creating...
            </Button>
        </form>
    {:else}
        {#each contacts as row, index}
            <Row class="g-3">
                <Col md="3">{row.contact}</Col>
            </Row>
        {/each}
    {/if}
</article>