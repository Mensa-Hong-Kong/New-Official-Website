<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { FormGroup, Input, Button, Spinner, Alert, Row, Col } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { onMount } from "svelte";
    import ClearInputHistory from '@/clearInputHistory.js';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';

	let { passportTypes, maxBirthday } = $props();
    let inputs = $state({});
    let submitting = $state(false);
    let forgetting = $state(false);

    onMount(
        () => {
            let clearInputHistory = new ClearInputHistory(inputs);

            return () => {clearInputHistory.destroy()}
        }
    );

    let feedbacks = $state({
        passportType: '',
        passportNumber: '',
        birthday: '',
        verifiedContactType: '',
        verifiedContact: '',
        failed: '',
        succeeded: '',
    });

    const inputFeedbackKeys = [
        'passportType', 'passportNumber', 'birthday',
        'verifiedContactType', 'verifiedContact'
    ];

    let verifiedContactTypeValue = $state('');

    function hasError() {
        for(let key of inputFeedbackKeys) {
            if(feedbacks[key] != 'Looks good!') {
                return true;
            }
        }
        return false;
    }

    function validation() {
        for(let key of inputFeedbackKeys) {
            feedbacks[key] = 'Looks good!';
        }
        if(inputs.passportType.validity.valueMissing) {
            feedbacks.passportType = 'The passport type field is required.';
        }
        if(inputs.passportNumber.validity.valueMissing) {
            feedbacks.passportNumber = 'The passport number field is required.';
        } else if(inputs.passportNumber.validity.tooShort) {
            feedbacks.passportNumber = `The passport number must be at least ${passportNumber.minLength} characters.`;
        } else if(inputs.passportNumber.validity.tooLong) {
            feedbacks.passportNumber = `The passport number must not be greater than ${passportNumber.maxLength} characters.`;
        }
        if(inputs.birthday.validity.valueMissing) {
            feedbacks.birthday = 'The birthday field is required.';
        } else if(inputs.birthday.validity.rangeOverflow) {
            feedbacks.birthday = `The birthday not be greater than ${birthday.max} characters.`;
        }
        if(inputs.verifiedContactType.validity.valueMissing) {
            feedbacks.verifiedContactType = 'The verified contact type field is required.';
        } else if(inputs.verifiedContact.validity.valueMissing) {
            feedbacks.verifiedContact = 'The verified contact field is required.';
        } else {
            switch(inputs.verifiedContactType.value) {
                case 'email':
                    if(inputs.verifiedContact.validity.tooLong) {
                        feedbacks.verifiedContact = `The email must not be greater than ${email.maxLength} characters.`;
                    } else if(inputs.verifiedContact.validity.typeMismatch) {
                        feedbacks.verifiedContact = `The email must be a valid email address.`;
                    }
                    break;
                case 'mobile':
                    if(inputs.verifiedContact.validity.tooShort) {
                        feedbacks.verifiedContact = `The mobile must be at least ${mobile.minLength} characters.`;
                    } else if(inputs.verifiedContact.validity.tooLong) {
                        feedbacks.verifiedContact = `The mobile must not be greater than ${mobile.maxLength} characters.`;
                    } else if(inputs.verifiedContact.validity.typeMismatch) {
                        feedbacks.verifiedContact = `The email must be a valid email address.`;
                    }
                    break;
            }
        }

        return ! hasError();
    }

    function successCallback(response) {
        alert(response.data.success);
        feedbacks.succeeded = response.data.success;
        submitting = false;
        forgetting = false;
    }

    function failCallback(error) {
        if(error.status == 422) {
            for(let key in error.response.data.errors) {
                let value = error.response.data.errors[key];
                switch(key) {
                    case 'passport_type_id':
                        feedbacks.passportType = value;
                        break;
                    case 'passport_number':
                        feedbacks.passportNumber = value;
                        break;
                    case 'birthday':
                        feedbacks.birthday = value;
                        break;
                    case 'verified_contact_type':
                        feedbacks.verifiedContactType = value;
                        break;
                    case 'verified_contact':
                        feedbacks.verifiedContact = value;
                        break;
                    case 'failed':
                        for(let key of inputFeedbackKeys) {
                            feedbacks[key] = '';
                        }
                        feedbacks.failed = value;
                        break;
                    default:
                        alert(`Undefine Feedback Key: ${key}\nMessage: ${message}`);
                        break;
                }
            }
        }
        submitting = false;
        forgetting = false;
    }

    function forgetPassword(event) {
        event.preventDefault();
        let submitAt = Date.now();
        submitting = 'forgetPassword'+submitAt;
        feedbacks.failed = '';
        feedbacks.succeeded = '';
        if (submitting == 'forgetPassword'+submitAt) {
            if(validation()) {
                forgetting = true;
                let data = {
                    passport_type_id: inputs.passportType.value,
                    passport_number: inputs.passportNumber.value,
                    birthday: inputs.birthday.value,
                    verified_contact_type: inputs.verifiedContactType.value,
                    verified_contact: inputs.verifiedContact.value,
                };
                post(
                    route('reset-password'),
                    successCallback,
                    failCallback,
                    'put', data
                );
            } else {
                submitting = false;
            }
        }
    }
</script>

<svelte:head>
    <title>Forget Password | {import.meta.env.VITE_APP_NAME}</title>
    <meta name="title" content="Forget Password | {import.meta.env.VITE_APP_NAME}">
    <meta name="description" content="{import.meta.env.VITE_APP_DESCRIPTION}">
    <meta name="og:description" content="{import.meta.env.VITE_APP_DESCRIPTION}">
    <meta name="og:image" content="og_image.png">
    <meta name="og:url" content="{import.meta.env.VITE_APP_URL}">
    <meta name="og:site_name" content="{import.meta.env.VITE_APP_NAME}">
</svelte:head>

<Layout>
    <section class="container">
        <form class="mx-auto w-25" novalidate onsubmit="{forgetPassword}">
            <h2 class="mb-2 fw-bold text-uppercase">Forget Password</h2>
            <div class="mb-4">
                <FormGroup floating label="Passport Type">
                    <Input type="select" name="passport_type_id" disabled={forgetting} required
                        feedback={feedbacks.passportType} valid={feedbacks.passportType == 'Looks good!'}
                        invalid={feedbacks.passportType != '' && feedbacks.passportType != 'Looks good!'}
                        bind:inner={inputs.passportType}>
                        <option value="" selected disabled>Please select passport type</option>
                        {#each Object.entries(passportTypes) as [key, value]}
                            <option value="{key}">{value}</option>
                        {/each}
                    </Input>
                </FormGroup>
            </div>
            <div class="mb-4">
                <FormGroup floating label="Passport Number">
                    <Input name="passport_number" disabled={forgetting}
                        minlength=8 maxlength=18 required placeholder="passport number"
                        feedback={feedbacks.passportNumber} valid={feedbacks.passportNumber == 'Looks good!'}
                        invalid={feedbacks.passportNumber != '' && feedbacks.passportNumber != 'Looks good!'}
                        bind:inner="{inputs.passportNumber}" />
                </FormGroup>
            </div>
            <div class="mb-4">
                <FormGroup floating label="Date of Birth">
                    <Input name="birthday" type="date" disabled={forgetting}
                        max={maxBirthday} required placeholder="birthday"
                        feedback={feedbacks.birthday} valid={feedbacks.birthday == 'Looks good!'}
                        invalid={feedbacks.birthday != '' && feedbacks.birthday != 'Looks good!'}
                        bind:inner={inputs.birthday} />
                </FormGroup>
            </div>
            <div class="mb-4">
                <FormGroup floating label="Verified Contact Type">
                    <Input type="select" name="verified_contact_type" required disabled={forgetting}
                        feedback={feedbacks.verifiedContactType} valid={feedbacks.verifiedContactType == 'Looks good!'}
                        invalid={feedbacks.verifiedContactType != '' && feedbacks.verifiedContactType != 'Looks good!'}
                        bind:inner={inputs.verifiedContactType} bind:value={verifiedContactTypeValue}>
                        <option value="" selected disabled>Please select verified contact type</option>
                        <option value="email">Email</option>
                        <option value="mobile">Mobile</option>
                    </Input>
                </FormGroup>
            </div>
            <div class="mb-4">
                <FormGroup floating label="Verified Contact">
                    {#if verifiedContactTypeValue == 'email'}
                        <Input name="email" type="email" disabled={forgetting}
                            maxlength=320 required placeholder="dammy@example.com"
                            feedback={feedbacks.verifiedContact} valid={feedbacks.verifiedContact == 'Looks good!'}
                            invalid={feedbacks.verifiedContact != '' && feedbacks.verifiedContact != 'Looks good!'}
                            bind:inner={inputs.verifiedContact} />
                    {:else if verifiedContactTypeValue == 'mobile'}
                        <Input type="tel"  name="verified_contact" disabled={forgetting}
                            minlength=5 maxlength=15 required placeholder=85298765432
                            feedback={feedbacks.verifiedContact} valid={feedbacks.verifiedContact == 'Looks good!'}
                            invalid={feedbacks.verifiedContact != '' && feedbacks.verifiedContact != 'Looks good!'}
                            bind:inner={inputs.verifiedContact} />
                    {:else}
                        <Input name="verified_contact" placeholder="Verified Contact" disabled
                            feedback={feedbacks.verifiedContact} valid={feedbacks.verifiedContact == 'Looks good!'}
                            invalid={feedbacks.verifiedContact != '' && feedbacks.verifiedContact != 'Looks good!'}
                            bind:inner={inputs.verifiedContact} />
                    {/if}
                </FormGroup>
            </div>
            <div class="mb-4">
                <Button color="primary" class="form-control" disabled={submitting}>
                    {#if submitting}
                        <Spinner type="border" size="sm" />Resetting...
                    {:else}
                        Reset Password
                    {/if}
                </Button>
                <Alert color="danger" hidden={! feedbacks.failed}>{feedbacks.failed}</Alert>
                <Alert color="danger" hidden={! feedbacks.succeeded}>{feedbacks.succeeded}</Alert>
            </div>
            <Row class="mb-4">
                <Col class="d-flex justify-content-center">
                    <Link href={route('login')} class="form-control btn btn-outline-primary">Login</Link>
                </Col>
                <Col class="d-flex justify-content-center">
                    <Link href={route('register')} class="form-control btn btn-outline-success">Register</Link>
                </Col>
            </Row>
        </form>
    </section>
</Layout>