<script>
    import { Row, Col, Input, Button, Spinner } from '@sveltestrap/sveltestrap';
    import { post, get } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';

    let { type, contacts: initContacts, submitting = $bindable() } = $props();
    let contacts = $state([]);
    let inputs = $state([]);

    for(let row of initContacts) {
        row['editing'] = false;
        row['updating'] = false;
        inputs.push({});
        contacts.push({
            id: row.id,
            contact: row.contact,
            isDefault: row.is_default,
            isVerified: row.is_verified,
            settingDefault: false,
            requestingVerifyCode: false,
            verifying: false,
            validating: false,
            editing: false,
            updating: false,
            deleting: false,
        });
    }

    function getIndexById(id) {
        return contacts.findIndex(
            function(row) {
                return row.id == id;
            }
        );
    }

    function setDefaultSuccessCallback(response) {
        alert(
            response.status == 201 ?
            response.data.message : response.data.success
        );
        let id = route().match(response.request.responseURL, 'put').params.contact;
        let index = getIndexById(id);
        contacts[index]['isDefault'] = true;
        contacts[index]['settingDefault'] = false;
        submitting = false;
    }

    function setDefaultFailCallback(error) {
        let id = route().match(response.request.responseURL, 'put').params.contact;
        let index = getIndexById(id);
        contacts[index]['settingDefault'] = false;
        if(error.status == 428) {
            contacts[index]['isVerified'] = false;
        }
        submitting = false;
    }

    function setDefault(index) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'setDefault'+submitAt;
            if(submitting == 'setDefault'+submitAt) {
                contacts[index]['settingDefault'] = true;
                post(
                    route(
                        'contacts.set-default',
                        {contact: contacts[index]['id']}
                    ),
                    setDefaultSuccessCallback,
                    setDefaultFailCallback,
                    'put'
                );
            }
        }
    }

    function requestVerifyCodeSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL).params.contact;
        let index = getIndexById(id);
        contacts[index]['requestingVerifyCode'] = false;
        contacts[index]['verifying'] = true;
        submitting = false
    }

    function requestNewVerifyCodeFailCallback(error) {
        let id = route().match(response.request.responseURL).params.contact;
        let index = getIndexById(id);
        switch(error.status) {
            case 410:
                contacts[index]['isVerified'] = true;
                contacts[index]['verifying'] = false;
                inputs[index]['verifyCode'].value = '';
                contacts[index]['requestingVerifyCode'] = false;
                break;
            case 429:
                contacts[index]['verifying'] = false;
                inputs[index]['verifyCode'].value = '';
                contacts[index]['requestingVerifyCode'] = false;
                break;
            default:
                contacts[index]['requestingVerifyCode'] = false;
                contacts[index]['verifying'] = true;
                break;
        }
        submitting = false;
    }

    function requestNewVerifyCode(index) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'requestNewVerifyCode'+submitAt;
            if(submitting == 'requestNewVerifyCode'+submitAt) {
                contacts[index]['requestingVerifyCode'] = true;
                get(
                    route(
                        'contacts.send-verify-code',
                        {contact: contacts[index]['id']}
                    ),
                    requestVerifyCodeSuccessCallback,
                    requestNewVerifyCodeFailCallback
                );
            }
        }
    }

    function validateValidation(index) {
        if(inputs[index]['verifyCode'].validity.valueMissing) {
            alert('The code field is required.');
        } else if(
            inputs[index]['verifyCode'].validity.tooLong ||
            inputs[index]['verifyCode'].validity.tooShort
        ) {
            alert('The code field must be 6 characters.');
        } else if(inputs[index]['verifyCode'].validity.patternMismatch) {
            alert('The code field must only contain letters and numbers.');
        } else {
            return true;
        }
        return false;
    }

    function validateSuccessCallback(response) {
        alert(
            response.status == 201 ?
            response.data.message : response.data.success
        );
        let id = route().match(response.request.responseURL, 'post').params.contact;
        let index = getIndexById(id);
        contacts[index]['isVerified'] = true;
        contacts[index]['verifying'] = false;
        inputs[index]['verifyCode'].value = '';
        contacts[index]['validating'] = false;
        submitting = false;
    }

    function validateFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.code);
        }
        let id = route().match(response.request.responseURL, 'post').params.contact;
        let index = getIndexById(id);
        contacts[index]['validating'] = false;
        if(
            error.status == 429 ||
            (error.status == 422 && error.response.data.errors.isFailedTooMany)
        ) {
            contacts[index]['verifying'] = false;
            inputs[index]['verifyCode'].value = '';
        }
        submitting = false;
    }

    function cancelVerify(index) {
        contacts[index]['verifying'] = false;
        inputs[index]['verifyCode'].value = '';
    }

    function validate(event, index) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'validate'+submitAt;
            if(submitting == 'validate'+submitAt) {
                if(validateValidation(index)) {
                    contacts[index]['validating'] = true;
                    post(
                        route(
                            'contacts.verify',
                            {contact: contacts[index]['id']}
                        ),
                        validateSuccessCallback,
                        validateFailCallback,
                        'post', {code: inputs[index]['verifyCode'].value}
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }

    function updateSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'put').params.contact;
        let index = getIndexById(id);
        contacts[index]['isVerified'] = response.data.is_verified;
        contacts[index]['contact'] = response.data[type];
        if(
            response.data[`default_${type}_id`] &&
            response.data[`default_${type}_id`] != id
        ) {
            contacts[index]['isDefault'] = false;
            let defaultContactIndex = getIndexById(response.data[`default_${type}_id`]);
            if(defaultContactIndex) {
                contacts[defaultContactIndex]['isDefault'] = true;
            }
        }
        contacts[index]['editing'] = false;
        inputs[index]['contact'].value = contacts[index]['contact'];
        contacts[index]['updating'] = false;
        submitting = false;
    }

    function updateFailCallback(error) {
        alert(error.response.data.errors[type]);
        let id = route().match(response.request.responseURL, 'put').params.contact;
        let index = getIndexById(id);
        contacts[index]['updating'] = false;
        submitting = false;
    }

    function validation(input) {
        if(input.validity.valueMissing) {
            alert(`The ${inputs[index]['contact'].name} field is required.`);
            return false;
        }
        if(type == 'mobile' && input.validity.tooShort) {
            alert(`The ${type} be at least ${input.minLength} characters.`);
            return false;
        }
        if(input.validity.tooLong) {
            alert(`The ${type} must not be greater than ${input.maxLength} characters.`);
            return false;
        }
        if(type == 'email' && input.validity.typeMismatch) {
            alert(`The email must be a valid email address.`);
            return false;
        }
        return true;
    }

    function update(event, index) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'update'+submitAt
            if(submitting == 'update'+submitAt) {
                if(validation(inputs[index]['contact'])) {
                    contacts[index]['updating'] = true;
                    let data = {};
                    data[type] = inputs[index]['contact'].value;
                    post(
                        route(
                            'contacts.update',
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

    function cancel(index) {
        contacts[index]['editing'] = false;
        inputs[index]['contact'].value = contacts[index]['contact'];
    }

    function edit(index) {
        contacts[index]['editing'] = true;
    }

    function deleteSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'delete').params.contact;
        let index = getIndexById(id);
        inputs.splice(index, 1)
        contacts.splice(index, 1)
        submitting = false;
    }
    
    function deleteFailCallback(error) {
        let id = route().match(response.request.responseURL, 'delete').params.contact;
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
                        'contacts.destroy',
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


    let creating = $state(false);
    let createContact;

    function createSuccessCallback(response) {
        alert(response.data.success);
        inputs.push({});
        contacts.push({
            id: response.data.id,
            contact: response.data.contact,
            isDefault: false,
            isVerified: false,
            settingDefault: false,
            requestingVerifyCode: false,
            verifying: false,
            validating: false,
            editing: false,
            updating: false,
            deleting: false,
        });
        createContact.value = '';
        creating = false;
        submitting = false;
    }

    function createFailCallback(error) {
        if(error.response.data.errors.message) {
            alert(error.response.data.errors.message);
        } else if(error.response.data.errors[type]){
            alert(error.response.data.errors[type]);
        } else {
            alert('The Contacts.svelte missing create fail type handle, please contact us.')
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
                if(validation(createContact)) {
                    creating = true;
                    post(
                        route('contacts.store'),
                        createSuccessCallback,
                        createFailCallback,
                        'post', {
                            type: type,
                            contact: createContact.value,
                        }
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }
</script>

<article id="email">
    <h3 class="mb-2 fw-bold">
        {#if type == 'email'}
            <i class="bi bi-envelope"></i>Email
        {:else if type == 'mobile'}
            <i class="bi bi-phone"></i> Mobile
        {/if}
    </h3>
    {#each contacts as row, index}
        <Row class="g-3">
            <Col md=3 hidden={row.editing}>{row.contact}</Col>
            <form class="col-md-3" id="editContactForm{row.id}" novalidate
                hidden="{! row.editing}" onsubmit={(event) => update(event, index)}>
                {#if type == 'email'}
                    <Input name="email" type="email" disabled={row.updating}
                        maxlength=320 required 
                        value={row.contact} placeholder="dammy@example.com"
                        bind:inner="{inputs[index]['contact']}" />
                {:else if type == 'mobile'}
                    <Input name="mobile" type="tel" disabled={row.updating}
                        minlength=5 maxlength=15 required 
                        value={row.contact} placeholder=85298765432
                        bind:inner="{inputs[index]['contact']}" class="form-control" />
                {/if}
            </form>
            <Col md=2 hidden={! row.isDefault}>Default</Col>
            <Button hidden={row.isDefault} disabled={submitting || row.settingDefault}
                color={row.isVerified ? 'primary' : 'secondary'}
                onclick={() => setDefault(index)} class={['btn', 'col-md-2']}>
                {#if row.settingDefault}
                    <Spinner type="border" size="sm" />Setting...
                {:else}
                    Set to Default
                {/if}
            </Button>
            <form class="col-md-2" id="verifyContactForm{row.id}" novalidate
                onsubmit="{(event) => validate(event, index)}">
                <Input name="code" class="form-control"
                    minlength=6 maxlength=6 pattern="[A-Za-z0-9]&lcub;6&rcub;" required
                    autocomplete="off" placeholder="Verify Code" hidden={! row.verifying}
                    bind:inner="{inputs[index]['verifyCode']}"/>
            </form>
            <Button color={row.isVerified ? 'secondary' : 'primary'}
                disabled={submitting || row.isVerified}
                hidden={row.verifying || row.editing || row.deleting}
                onclick={() => {if(!row.isVerified) (row.verifying = true)}}
                class="col-md-1">{row.isVerified ? 'Verified' : 'Verify'}</Button>
            <Button color="primary" onclick={() => requestNewVerifyCode(index)}
                hidden={row.requestingVerifyCode || ! row.verifying || row.validating}
                class="col-md-2">Send New Verify Code</Button>
            <Button color="primary" hidden={! row.requestingVerifyCode} disabled class="col-md-4">
                <Spinner type="border" size="sm" />Requesting...
            </Button>
            <Button color="primary" form="verifyContactForm{row.id}"
                hidden={! row.verifying} class="col-md-1">Submit</Button>
            <Button color="danger" onclick={() => cancelVerify(index)}
                hidden={! row.verifying || row.validating} class="col-md-1">Cancel</Button>
            <Button color="danger" hidden={! row.validating} disabled class="col-md-4">
                <Spinner type="border" size="sm" />Submitting...
            </Button>
            <Button color="primary" onclick={() => edit(index)} class="col-md-1"
                hidden={row.editing || row.updating || row.verifying || row.deleting}>Edit</Button>
            <Button color="primary" form="editContactForm{row.id}"
                hidden={! row.editing || row.updating} class="col-md-1">Save</Button>
            <Button color="danger" onclick={(event) => cancel(index)}
                hidden={! row.editing || row.updating} class="col-md-1">Cancel</Button>
            <Button color="primary" hidden={! row.updating} disabled class="col-md-2">
                <Spinner type="border" size="sm" />Saving...
            </Button>
            <Button color="danger" hidden={row.verifying || row.editing || row.deleting}
                onclick={() => destroy(index)} class="col-md-1">Delete</Button>
            <Button color="danger" hidden={! row.deleting} disabled class="col-md-4">
                <Spinner type="border" size="sm" />Deleting...
            </Button>
        </Row>
    {/each}
    <form class="row g-3 createContact" novalidate onsubmit="{create}">
        <Col md=3>
            {#if type == 'email'}
                <Input name="contact" type="email" placeholder="dammy@example.com"
                    maxlength=320 required bind:inner={createContact} />
            {:else if type == 'mobile'}
                <Input name="contact" type="tel" placeholder=85298765432
                    minlength=5 maxlength=15 required bind:inner={createContact} />
            {/if}
        </Col>
        <Col md=4 />
        <Button color="success" hidden={creating} class="col-md-3" disabled={creating}>
            {#if creating}
                <Spinner type="border" size="sm" />Creating...
            {:else}
                Create
            {/if}
        </Button>
    </form>
</article>
