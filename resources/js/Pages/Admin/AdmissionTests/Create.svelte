<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { FormGroup, Input, Button, Spinner } from '@sveltestrap/sveltestrap';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import Datalist from '@/Pages/Components/Datalist.svelte';
    import { router } from '@inertiajs/svelte';

    let { types, locations, districts, addresses } = $props();
    let inputs = $state({});
    let feedbacks = $state({
        type: '',
        testingAt: '',
        expectEndAt: '',
        location: '',
        district: '',
        address: '',
        maximumCandidates: '',
    });
    let submitting = $state(false);
    let creating = $state(false);

    function hasError() {
        for(let [key, feedback] of Object.entries(feedbacks)) {
            if(feedback != 'Looks good!') {
                return true;
            }
        }
        return false;
    }

    function validation() {
        for(let key in feedbacks) {
            feedbacks[key] = 'Looks good!';
        }
        if(inputs.type.validity.valueMissing) {
            feedbacks.type = 'The type field is required.';
        }
        if(inputs.testingAt.validity.valueMissing) {
            feedbacks.testingAt = 'The testing at field is required.';
        }
        if(inputs.expectEndAt.validity.valueMissing) {
            feedbacks.expectEndAt = 'The expect end at field is required.';
        } else if(inputs.testingAt.value > inputs.expectEndAt.value) {
            feedbacks.expectEndAt = 'The expect end at field must be a date after than testing at.';
        }
        if(inputs.location.validity.valueMissing) {
            feedbacks.location = 'The location field is required.';
        } else if(inputs.location.validity.tooLong) {
            feedbacks.location = `The location field must not be greater than ${inputs.location.maxLength} characters.`;
        }
        if(inputs.district.validity.valueMissing) {
            feedbacks.district = 'The district field is required.';
        }
        if(inputs.address.validity.valueMissing) {
            feedbacks.address = 'The address field is required.';
        } else if(inputs.address.validity.tooLong) {
            feedbacks.address = `The address field must not be greater than ${inputs.address.maxLength} characters.`;
        }
        if(inputs.maximumCandidates.validity.valueMissing) {
            feedbacks.maximumCandidates = 'The maximum candidates field is required.';
        } else if(inputs.maximumCandidates.validity.rangeUnderflow) {
            feedbacks.maximumCandidates = `The maximum candidates field must be at least ${inputs.address.min}.`;
        }
        return !hasError();
    }
    
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
                    case 'type_id':
                        feedbacks.type = value;
                        break;
                    case 'testing_at':
                        feedbacks.testingAt = value;
                        break;
                    case 'expect_end_at':
                        feedbacks.expectEndAt = value;
                        break;
                    case 'location':
                        feedbacks.location = value;
                        break;
                    case 'district_id':
                        feedbacks.district = value;
                        break;
                    case 'address':
                        feedbacks.address = value;
                        break;
                    case 'maximum_candidates':
                        feedbacks.maximumCandidates = value;
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
                if(validation()) {
                    creating = true;
                    post(
                        route('admin.admission-tests.store'),
                        successCallback,
                        failCallback,
                        'post', {
                            type_id: inputs.type.value,
                            testing_at: inputs.testingAt.value,
                            expect_end_at: inputs.expectEndAt.value,
                            location: inputs.location.value,
                            district_id: inputs.district.value,
                            address: inputs.address.value,
                            maximum_candidates: inputs.maximumCandidates.value,
                            is_public: inputs.isPublic.checked,
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
    <title>Administration Create Admission Test | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <form id="form" method="POST" novalidate onsubmit={create}>
            <h2 class="mb-2 fw-bold text-uppercase">Create Admission Test</h2>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Type">
                    <Input type="select" name="type_id" required disabled={creating}
                        feedback={feedbacks.type} valid={feedbacks.type == 'Looks good!'}
                        invalid={feedbacks.type != '' && feedbacks.type != 'Looks good!'}
                        bind:inner={inputs.type}>
                        <option value="" selected disabled>Please select test type</option>
                        {#each Object.entries(types) as [key, value]}
                            <option value={key}>{value}</option>
                        {/each}
                    </Input>
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Testing At">
                    <Input type="datetime-local" name="testing_at" disabled={creating}
                        feedback={feedbacks.testingAt} valid={feedbacks.testingAt == 'Looks good!'}
                        invalid={feedbacks.testingAt != '' && feedbacks.testingAt != 'Looks good!'}
                        bind:inner={inputs.testingAt} value={Date.now()} required />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Expect End At">
                    <Input type="datetime-local" name="expect_end_at" disabled={creating}
                        feedback={feedbacks.expectEndAt} valid={feedbacks.expectEndAt == 'Looks good!'}
                        invalid={feedbacks.expectEndAt != '' && feedbacks.expectEndAt != 'Looks good!'}
                        bind:inner={inputs.expectEndAt} value={Date.now()} required />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Location">
                    <Input name="location" disabled={creating} list="locations"
                        feedback={feedbacks.location} valid={feedbacks.location == 'Looks good!'}
                        invalid={feedbacks.location != '' && feedbacks.location != 'Looks good!'}
                        bind:inner={inputs.location} maxlength="255" required />
                    <Datalist id="locations" data={locations} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="District">
                    <Input type="select" name="district_id" required disabled={creating}
                        feedback={feedbacks.district} valid={feedbacks.district == 'Looks good!'}
                        invalid={feedbacks.district != '' && feedbacks.district != 'Looks good!'}
                        bind:inner={inputs.district}>
                        <option value="" selected disabled>Please select district</option>
                        {#each Object.entries(districts) as [area, object]}
                            <optgroup label={area}>
                                {#each Object.entries(object) as [key, value]}
                                    <option value={key}>{value}</option>
                                {/each}
                            </optgroup>
                        {/each}
                    </Input>
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Address">
                    <Input name="address" disabled={creating} list="addresses"
                        feedback={feedbacks.address} valid={feedbacks.address == 'Looks good!'}
                        invalid={feedbacks.address != '' && feedbacks.address != 'Looks good!'}
                        bind:inner={inputs.address} maxlength="255" required />
                    <Datalist id="addresses" data={addresses} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Maximum Candidates">
                    <Input type="number" name="maximum_candidates" disabled={creating}
                        feedback={feedbacks.maximumCandidates} valid={feedbacks.maximumCandidates == 'Looks good!'}
                        invalid={feedbacks.maximumCandidates != '' && feedbacks.maximumCandidates != 'Looks good!'}
                        bind:inner={inputs.maximumCandidates} min="1" step="1" required />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <Input type="switch" name="is_public" label="Is Public" id="isPublic"
                    bind:inner={inputs.isPublic} disabled={creating} />
            </div>
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