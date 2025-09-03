<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { Table, Button, Spinner, Input } from '@sveltestrap/sveltestrap';
	import Datalist from '@/Pages/Components/Datalist.svelte';
    import Proctors from './Proctors.svelte';
    import Candidates from './Candidates.svelte';
    import { formatToDatetime } from '@/timeZoneDatetime';

    let { auth, test: initTest, types, locations, districts: areaDistricts, addresses } = $props();
    let submitting = $state(false);
    let editing = $state(false);
    let updating = $state(false);
    let test = $state({
        id: initTest.id,
        typeID: initTest.type_id,
        testingAt: formatToDatetime(initTest.testing_at),
        expectEndAt: formatToDatetime(initTest.expect_end_at),
        locationID: initTest.location_id,
        districtID: initTest.address.district_id,
        addressID: initTest.address.id,
        maximumCandidates: initTest.maximum_candidates,
        isPublic: initTest.is_public,
    });
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
    let districts = {};
    for(let [area, object] of Object.entries(areaDistricts)) {
        for(let [key, value] of Object.entries(object)) {
            districts[key] = value;
        }
    }
    function close() {
        for(let key in feedbacks) {
            feedbacks[key] = '';
        }
        editing = false;
        inputs.type.value = test.type;
        inputs.testingAt.value = test.testingAt;
        inputs.expectEndAt.value = test.expectEndAt;
        inputs.location.value = locations[test.locationID];
        inputs.district.value = test.districtID;
        inputs.address.value = addresses[test.addressID];
        inputs.maximumCandidates.value = test.maximumCandidates;
        inputs.isPublic.checked = test.isPublic;
    }

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
            feedbacks.expectEndAt = 'The expect end at field must be a date after testing at.';
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
            feedbacks.maximumCandidates = `The maximum candidates field must be at least ${inputs.maximumCandidates.min}.`;
        }
        return !hasError();
    }
    
    function successCallback(response) {
        test.type = response.data.type_id;
        test.testingAt = formatToDatetime(response.data.testing_at);
        test.expectEndAt = formatToDatetime(response.data.expect_end_at);
        locations[response.data.location_id] = response.data.location;
        test.locationID = response.data.location_id;
        test.districtID = response.data.district_id;
        delete addresses[test.addressID];
        test.addressID = response.data.address_id;
        addresses[response.data.address_id] = response.data.address;
        test.maximumCandidates = response.data.maximum_candidates;
        test.isPublic = response.data.is_public;
        close();
        updating = false;
        submitting = false;
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
                    case 'district':
                        feedbacks.district = value;
                        break;
                    case 'address':
                        feedbacks.address = value;
                        break;
                    case 'maximum_candidates':
                        feedbacks.maximumCandidates = value;
                        break;
                    default:
                        alert(`Undefine Feedback Key: ${key}\nMessage: ${value}`);
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
                if(validation()) {
                    updating = true;
                    post(
                        route(
                            'admin.admission-tests.update',
                            {admission_test: test.id}
                        ),
                        successCallback,
                        failCallback,
                        'put', {
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

    function edit(event) {
        event.preventDefault();
        editing = true;
    }

    function cancel(event) {
        event.preventDefault();
        close();
    }
</script>

<svelte:head>
    <title>Administration Show Admission Test | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <article>
            <form id="form" method="POST" novalidate onsubmit={update}>
                <h3 class="mb-2 fw-bold">
                    Info
                    <Button color="primary" disabled hidden={! updating}>
                        <Spinner type="border" size="sm" />Saving...
                    </Button>
                    <Button color="primary" outline hidden={editing || updating}
                        onclick={edit}>Edit</Button>
                    <Button color="primary" outline hidden={! editing && ! updating}
                        disabled={submitting}>Save</Button>
                    <Button color="danger" outline hidden={! editing && ! updating}
                        onclick={cancel}>Cancel</Button>
                </h3>
                <Table hover>
                    <tbody>
                        <tr>
                            <th>Type</th>
                            <td>
                                <span hidden={editing}>{types[test.typeID]}</span>
                                <Input type="select" name="type_id" required
                                    hidden={! editing} disable={updating}
                                    feedback={feedbacks.type} valid={feedbacks.type == 'Looks good!'}
                                    invalid={feedbacks.type != '' && feedbacks.type != 'Looks good!' }
                                    bind:inner={inputs.type}>
                                    {#each Object.entries(types) as [key, value]}
                                        <option selected={key == test.typeID}
                                            value={key}>{value}</option>
                                    {/each}
                                </Input>
                            </td>
                        </tr>
                        <tr>
                            <th>Testing At</th>
                            <td>
                                <span hidden={editing}>{test.testingAt}</span>
                                <Input type="datetime-local" name="testing_at" required
                                    hidden={! editing} disable={updating} placeholder="testing at"
                                    feedback={feedbacks.testingAt} valid={feedbacks.testingAt == 'Looks good!'}
                                    invalid={feedbacks.testingAt != '' && feedbacks.testingAt != 'Looks good!' }
                                    bind:inner={inputs.testingAt} value={test.testingAt} />
                            </td>
                        </tr>
                        <tr>
                            <th>Expect End At</th>
                            <td>
                                <span hidden={editing}>{test.expectEndAt}</span>
                                <Input type="datetime-local" name="expect_end_at" required
                                    hidden={! editing} disable={updating} placeholder="expect end at"
                                    feedback={feedbacks.expectEndAt} valid={feedbacks.expectEndAt == 'Looks good!'}
                                    invalid={feedbacks.expectEndAt != '' && feedbacks.expectEndAt != 'Looks good!' }
                                    bind:inner={inputs.expectEndAt} value={test.expectEndAt} />
                            </td>
                        </tr>
                        <tr>
                            <th>Location</th>
                            <td>
                                <span hidden={editing}>{locations[test.locationID]}</span>
                                <Input name="location" maxlength="255" required placeholder="location"
                                    hidden={! editing} disable={updating} list="locations"
                                    feedback={feedbacks.location} valid={feedbacks.location == 'Looks good!'}
                                    invalid={feedbacks.location != '' && feedbacks.location != 'Looks good!' }
                                    bind:inner={inputs.location} value={locations[test.locationID]} />
                                <Datalist id="locations" data={Object.values(locations)} />
                            </td>
                        </tr>
                        <tr>
                            <th>District</th>
                            <td>
                                <span hidden={editing}>{districts[test.districtID]}</span>
                                <Input type="select" name="type_id" required
                                    hidden={! editing} disable={updating}
                                    feedback={feedbacks.district} valid={feedbacks.district == 'Looks good!'}
                                    invalid={feedbacks.district != '' && feedbacks.district != 'Looks good!' }
                                    bind:inner={inputs.district}>
                                    {#each Object.entries(areaDistricts) as [area, object]}
                                        <optgroup label={area}>
                                            {#each Object.entries(object) as [key, value]}
                                                <option selected={key == test.districtID}
                                                    value="{key}">{value}</option>
                                                {/each}
                                        </optgroup>
                                    {/each}
                                </Input>
                            </td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>
                                <span hidden={editing}>{addresses[test.addressID]}</span>
                                <Input name="location" maxlength="255" required placeholder="address"
                                    hidden={! editing} disable={updating} list="addresses"
                                    feedback={feedbacks.address} valid={feedbacks.address == 'Looks good!'}
                                    invalid={feedbacks.address != '' && feedbacks.address != 'Looks good!' }
                                    bind:inner={inputs.address} value={addresses[test.addressID]} />
                                <Datalist id="addresses" data={Object.values(addresses)} />
                            </td>
                        </tr>
                        <tr>
                            <th>Maximum Candidates</th>
                            <td>
                                <span hidden={editing}>{test.maximumCandidates}</span>
                                <Input type="number" name="maximum_candidates"
                                    min="1" step="1" required placeholder="maximum candidates"
                                    hidden={! editing} disable={updating}
                                    feedback={feedbacks.maximumCandidates} valid={feedbacks.maximumCandidates == 'Looks good!'}
                                    invalid={feedbacks.maximumCandidates != '' && feedbacks.maximumCandidates != 'Looks good!' }
                                    bind:inner={inputs.maximumCandidates} value={test.maximumCandidates} />
                            </td>
                        </tr>
                        <tr>
                            <th>Current Candidates</th>
                            <td>{initTest.candidates.length}</td>
                        </tr>
                        <tr>
                            <th>Is Public</th>
                            <td>
                                <span hidden={editing}>{test.isPublic ? 'Public' : 'Private'}</span>
                                <Input type="switch" name="is_public" hidden={! editing} disable={updating}
                                    bind:inner={inputs.isPublic} checked={test.isPublic} />
                            </td>
                        </tr>
                    </tbody>
                </Table>
            </form>
        </article>
        <Proctors proctors={initTest.proctors} bind:submitting={submitting} />
        <Candidates auth={auth} test={test} candidates={initTest.candidates} bind:submitting={submitting} />
    </section>
</Layout>