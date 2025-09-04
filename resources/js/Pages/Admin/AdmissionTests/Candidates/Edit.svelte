<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { FormGroup, Input, Button, Spinner } from '@sveltestrap/sveltestrap';
	import Datalist from '@/Pages/Components/Datalist.svelte';
    import { formatToDate } from '@/timeZoneDatetime';
    import { router } from '@inertiajs/svelte';

    let { user: initUser, passportTypes, genders, maxBirthday } = $props();
    let user = {
        id: initUser.id,
        familyName: initUser.family_name,
        middleName: initUser.middle_name,
        givenName: initUser.given_name,
        genderID: initUser.gender_id,
        passportTypeID: initUser.passport_type_id,
        passportNumber: initUser.passport_number,
        birthday: formatToDate(initUser.birthday),
    }
    let inputs = $state({});
    let feedbacks = $state({
        familyName: '',
        middleName: '',
        givenName: '',
        gender: '',
        passportType: '',
        passportNumber: '',
        birthday: '',
    });
    let submitting = $state(false);
    let updating = $state(false);
    
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
        if(inputs.familyName.validity.valueMissing) {
            feedbacks.familyName = 'The family name field is required.';
        } else if(inputs.familyName.validity.tooLong) {
            feedbacks.familyName = `The family name must not be greater than ${inputs.familyName.maxLength} characters.`;
        }
        if(inputs.middleName.value && inputs.middleName.validity.tooLong) {
            feedbacks.middleName = `The middle name must not be greater than ${inputs.middleName.maxLength} characters.`;
        }
        if(inputs.givenName.validity.valueMissing) {
            feedbacks.givenName = 'The given name field is required.';
        } else if(inputs.givenName.validity.tooLong) {
            feedbacks.givenName = `The given name must not be greater than ${inputs.givenName.maxLength} characters.`;
        }
        if(inputs.passportType.validity.valueMissing) {
            feedbacks.passportType = 'The passport type field is required.';
        }
        if(inputs.passportNumber.validity.valueMissing) {
            feedbacks.passportNumber = 'The passport number field is required.';
        } else if(inputs.passportNumber.validity.tooShort) {
            feedbacks.passportNumber = `The passport number must be at least ${inputs.passportNumber.minLength} characters.`;
        } else if(inputs.passportNumber.validity.tooLong) {
            feedbacks.passportNumber = `The passport number must not be greater than ${inputs.passportNumber.maxLength} characters.`;
        }
        if(inputs.gender.validity.valueMissing) {
            feedbacks.gender = 'The gender field is required.';
        } else if(inputs.gender.validity.tooLong) {
            feedbacks.gender = `The gender must not be greater than ${inputs.gender.maxLength} characters.`;
        }
        if(inputs.birthday.validity.valueMissing) {
            feedbacks.birthday = 'The birthday field is required.';
        } else if(inputs.birthday.validity.rangeOverflow) {
            feedbacks.birthday = `The birthday field must be a date before or equal to ${inputs.birthday.max}.`;
        }
        return ! hasError();
    }

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
                    case 'family_name':
                        feedbacks.familyName = value;
                        break;
                    case 'middle_name':
                        feedbacks.middleName = value;
                        break;
                    case 'given_name':
                        feedbacks.givenName = value;
                        break;
                    case 'passport_type_id':
                        feedbacks.passportType = value;
                        break;
                    case 'passport_number':
                        feedbacks.passportNumber = value;
                        break;
                    case 'gender':
                        feedbacks.gender = value;
                        break;
                    case 'birthday':
                        feedbacks.birthday = value;
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
            submitting = 'create'+submitAt;
            if(submitting == 'create'+submitAt) {
                if(validation()) {
                    updating = true
                    post(
                        route(
                            'admin.admission-tests.candidates.update',
                            {
                                admission_test: route().params.admission_test,
                                candidate: user.id,
                            }
                        ),
                        successCallback,
                        failCallback,
                        'put', {
                            family_name: inputs.familyName.value,
                            middle_name: inputs.middleName.value,
                            given_name: inputs.givenName.value,
                            gender: inputs.gender.value,
                            passport_type_id: inputs.passportType.value,
                            passport_number: inputs.passportNumber.value,
                            birthday: inputs.birthday.value,
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
    <title>Administration Edit Candidate | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <form method="POST" novalidate onsubmit={update}>
            <h2 class="mb-2 fw-bold">Edit Candidate</h2>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Family Name">
                    <Input name="family_name" placeholder="family name" disabled={updating}
                        maxlength=255 required value={user.familyName}
                        feedback={feedbacks.familyName} valid={feedbacks.familyName == 'Looks good!'}
                        invalid={feedbacks.familyName != '' && feedbacks.familyName != 'Looks good!'}
                        bind:inner={inputs.familyName} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Middle Name">
                    <Input name="middle_name" placeholder="middle name" disabled={updating}
                        maxlength=255 value={user.middleName}
                        feedback={feedbacks.middleName} valid={feedbacks.middleName == 'Looks good!'}
                        invalid={feedbacks.middleName != '' && feedbacks.middleName != 'Looks good!'}
                        bind:inner={inputs.middleName} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Given Name">
                    <Input name="given_name" placeholder="given name" disabled={updating}
                        maxlength=255 required value={user.givenName}
                        feedback={feedbacks.givenName} valid={feedbacks.givenName == 'Looks good!'}
                        invalid={feedbacks.givenName != '' && feedbacks.givenName != 'Looks good!'}
                        bind:inner={inputs.givenName} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Gender">
                    <Input name="gender" placeholder="gender" disabled={updating}
                        maxlength=255 required value={genders[user.genderID]}
                        feedback={feedbacks.gender} valid={feedbacks.gender == 'Looks good!'}
                        invalid={feedbacks.gender != '' && feedbacks.gender != 'Looks good!'}
                        bind:inner={inputs.gender} />
                </FormGroup>
                <Datalist id="genders" data={genders} />
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Passport Type">
                    <Input type="select" name="passport_type_id" disabled={updating} required
                        feedback={feedbacks.passportType} valid={feedbacks.passportType == 'Looks good!'}
                        invalid={feedbacks.passportType != '' && feedbacks.passportType != 'Looks good!'}
                        bind:inner={inputs.passportType}>
                        {#each Object.entries(passportTypes) as [key, value]}
                            <option value="{key}" selected={key == user.passportTypeID}>{value}</option>
                        {/each}
                    </Input>
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Passport Number">
                    <Input name="passport_number" placeholder="passport number" disabled={updating}
                        minlength="8" maxlength="18" required value={user.passportNumber}
                        feedback={feedbacks.passportNumber} valid={feedbacks.passportNumber == 'Looks good!'}
                        invalid={feedbacks.passportNumber != '' && feedbacks.passportNumber != 'Looks good!'}
                        bind:inner={inputs.passportNumber} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Birthday">
                    <Input type="date" name="birthday" placeholder="birthday" disabled={updating}
                        max={maxBirthday} required value={user.birthday}
                        feedback={feedbacks.birthday} valid={feedbacks.birthday == 'Looks good!'}
                        invalid={feedbacks.birthday != '' && feedbacks.birthday != 'Looks good!'}
                        bind:inner={inputs.birthday} />
                </FormGroup>
            </div>
            <div class="mb-4">
                <Button color="primary" class="form-control" disabled={submitting}>
                    {#if updating}
                        <Spinner type="border" size="sm" />Saving...
                    {:else}
                        Save
                    {/if}
                </Button>
            </div>
        </form>
    </section>
</Layout>