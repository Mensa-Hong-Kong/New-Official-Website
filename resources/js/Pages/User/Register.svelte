<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Alert, Col, Label, Input, Button, Spinner } from '@sveltestrap/sveltestrap';
    import { onMount } from "svelte";
    import ClearInputHistory from '@/clearInputHistory.js';
	import Datalist from '@/Pages/Components/Datalist.svelte';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { router } from '@inertiajs/svelte';

	let { genders, passportTypes, maxBirthday } = $props();
    let inputs = $state({});
    let submitting = $state(false);
    let creating = $state(false);

    onMount(
        () => {
            let clearInputHistory = new ClearInputHistory(inputs);

            return () => {clearInputHistory.destroy()}
        }
    );

    let feedbacks = $state({
        username: '',
        password: '',
        familyName: '',
        middleName: '',
        givenName: '',
        passportType: '',
        passportNumber: '',
        gender: '',
        birthday: '',
        email: '',
        mobile: '',
    });

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
        if(inputs.username.validity.valueMissing) {
            feedbacks.username = 'The username field is required.';
        } else if(inputs.username.validity.tooShort) {
            feedbacks.username = `The username field must be at least ${inputs.username.minLength} characters.`;
        } else if(inputs.username.validity.tooLong) {
            feedbacks.username = `The username field must not be greater than ${inputs.username.maxLength} characters.`;
        }
        if(inputs.password.validity.valueMissing) {
            feedbacks.password = 'The password field is required.';
        } else if(inputs.password.validity.tooShort) {
            feedbacks.password = `The password field must be at least ${inputs.password.minLength} characters.`;
        } else if(inputs.password.validity.tooLong) {
            feedbacks.password = `The password field must not be greater than ${inputs.password.maxLength} characters.`;
        } else if(inputs.password.value != inputs.confirmPassword.value) {
            feedbacks.password = 'The password confirmation does not match.';
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
        if(inputs.email.value) {
            if(inputs.email.validity.tooLong) {
                feedbacks.email = `The email must not be greater than ${inputs.email.maxLength} characters.`;
            } else if(inputs.email.validity.typeMismatch) {
                feedbacks.email = `The email must be a valid email address.`;
            }
        }
        if(inputs.mobile.value) {
            if(inputs.mobile.validity.tooShort) {
                feedbacks.mobile = `The mobile must be at least ${inputs.mobile.minLength} characters.`;
            } else if(inputs.mobile.validity.tooLong) {
                feedbacks.mobile = `The mobile must not be greater than ${inputs.mobile.maxLength} characters.`;
            } else if(inputs.mobile.validity.typeMismatch) {
                feedbacks.mobile = `The email must be a valid email address.`;
            }
        }

        return ! hasError();
    }

    function successCallback(response) {
        submitting = false;
        creating = false;
        router.get(response.request.responseURL);
    }

    function failCallback(error) {
        if(error.status == 422) {
            for(let key in error.response.data.errors) {
                let value = error.response.data.errors[key];
                switch(key) {
                    case 'username':
                        feedbacks.username = value;;
                        break;
                    case 'password':
                        feedbacks.password = value;
                        break;
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
                    case 'email':
                        feedbacks.email = value;
                        break;
                    case 'mobile':
                        feedbacks.mobile = value;
                        break;
                    default:
                        alert(`Undefine Feedback Key: ${key}\nMessage: ${message}`);
                        break;
                }
            }
        }
        submitting = false;
        creating = false;
    }

    function register(event) {
        event.preventDefault();
        let submitAt = Date.now();
        submitting = 'register'+submitAt;
        if (submitting == 'register'+submitAt) {
            if(validation()) {
                creating = true;
                let data = {
                    username: inputs.username.value,
                    password: inputs.password.value,
                    password_confirmation: inputs.confirmPassword.value,
                    family_name: inputs.familyName.value,
                    middle_name: inputs.middleName.value,
                    given_name: inputs.givenName.value,
                    gender: inputs.gender.value,
                    passport_type_id: inputs.passportType.value,
                    passport_number: inputs.passportNumber.value,
                    birthday: inputs.birthday.value,
                    email: inputs.email.value,
                    mobile: inputs.mobile.value,
                }
                post(
                    route('register'),
                    successCallback,
                    failCallback,
                    'post', data
                );
            } else {
                submitting = false;
            }
        }
    }
</script>

<svelte:head>
    <title>Register | {import.meta.env.VITE_APP_NAME}</title>
    <meta name="title" content="Register | {import.meta.env.VITE_APP_NAME}">
    <meta name="description" content="{import.meta.env.VITE_APP_DESCRIPTION}">
    <meta name="og:description" content="{import.meta.env.VITE_APP_DESCRIPTION}">
    <meta name="og:image" content="og_image.png">
    <meta name="og:url" content="{import.meta.env.VITE_APP_URL}">
    <meta name="og:site_name" content="{import.meta.env.VITE_APP_NAME}">
</svelte:head>

<Layout>
    <section class="container">
        <Alert color="primary">
            <ol>
                <li>
                    Passport number include inside brackets number but without all symbol<br>
                    Example 1: A123456(7) should type A1234567
                    Example 1: 1234567(8) should type 12345678
                </li>
                <li>The family name, middle name, given name and gender must match passport</li>
                <li>Mobile number include country code without "+" and "-"</li>
            </ol>
        </Alert>
        <form class="row g-3" onsubmit="{register}" novalidate>
            <h2 class="mb-2 fw-bold text-uppercase">Register</h2>
            <Col md=4>
                <Label>Username</Label>
                <Input name="username" placeholder="username" disabled={creating}
                    minlength=8 maxlength=16 required bind:inner={inputs.username}
                    feedback={feedbacks.username} valid={feedbacks.username == 'Looks good!'}
                    invalid={feedbacks.username != '' && feedbacks.username != 'Looks good!'} />
            </Col>
            <Col md=4>
                <Label>Password</Label>
                <Input name="password" type="password" placeholder="password" disabled={creating}
                    minlength=8 maxlength=16 required bind:inner={inputs.password}
                    feedback={feedbacks.password} valid={feedbacks.password == 'Looks good!'}
                    invalid={feedbacks.password != '' && feedbacks.password != 'Looks good!'} />
            </Col>
            <Col md=4>
                <Label>Confirm Password</Label>
                <Input name="password_confirmation" type="password" disabled={creating}
                    minlength=8 maxlength=16 required placeholder="confirm password"
                    invalid={feedbacks.password != '' && feedbacks.password != 'Looks good!'}
                    valid={feedbacks.password == 'Looks good!'} bind:inner={inputs.confirmPassword} />
            </Col>
            <Col md=4>
                <Label>Family Name</Label>
                <Input name="family_name" disabled={creating}
                    maxlength=255 required placeholder="family name"
                    feedback={feedbacks.familyName} valid={feedbacks.familyName == 'Looks good!'}
                    invalid={feedbacks.familyName != '' && feedbacks.familyName != 'Looks good!'}
                    bind:inner="{inputs.familyName}" />
            </Col>
            <Col md=4>
                <Label>Middle Name</Label>
                <Input name="middle_name" disabled={creating}
                    maxlength="255" placeholder="middle name"
                    feedback={feedbacks.middleName} valid={feedbacks.middleName == 'Looks good!'}
                    invalid={feedbacks.middleName != '' && feedbacks.middleName != 'Looks good!'}
                    bind:inner="{inputs.middleName}" />
            </Col>
            <Col md=4>
                <Label>Given Name</Label>
                <Input name="given_name" type="text" disabled={creating}
                    maxlength=255 required placeholder="given name"
                    feedback={feedbacks.givenName} valid={feedbacks.givenName == 'Looks good!'}
                    invalid={feedbacks.givenName != '' && feedbacks.givenName != 'Looks good!'}
                    bind:inner="{inputs.givenName}" />
            </Col>
            <Col md=4>
                <Label>Passport Type</Label>
                <Input type="select" name="passport_type_id" required disabled={creating}
                    feedback={feedbacks.passportType} valid={feedbacks.passportType == 'Looks good!'}
                    invalid={feedbacks.passportType != '' && feedbacks.passportType != 'Looks good!'}
                    bind:inner="{inputs.passportType}">
                    <option value="" selected disabled>Please select passport type</option>
                    {#each Object.entries(passportTypes) as [key, value]}
                        <option value="{key}">{value}</option>
                    {/each}
                </Input>
            </Col>
            <Col md=4>
                <Label>Passport Type</Label>
                <Input name="passport_number" disabled={creating}
                    minlength=8 maxlength=18 required placeholder="passport number"
                    feedback={feedbacks.passportNumber} valid={feedbacks.passportNumber == 'Looks good!'}
                    invalid={feedbacks.passportNumber != '' && feedbacks.passportNumber != 'Looks good!'}
                    bind:inner="{inputs.passportNumber}" />
            </Col>
            <Col md=4 />
            <Col md=4>
                <Label>Passport Type</Label>
                <Input name="gender" disabled={creating}
                    maxlength="255" list="genders" required placeholder="gender"
                    feedback={feedbacks.gender} valid={feedbacks.gender == 'Looks good!'}
                    invalid={feedbacks.gender != '' && feedbacks.gender != 'Looks good!'}
                    bind:inner={inputs.gender} />
            </Col>
            <Datalist id="genders" data={genders} />
            <Col md=4>
                <Label>Date of Birth</Label>
                <Input name="birthday" type="date" disabled={creating}
                    max={maxBirthday} required placeholder="birthday"
                    feedback={feedbacks.birthday} valid={feedbacks.birthday == 'Looks good!'}
                    invalid={feedbacks.birthday != '' && feedbacks.birthday != 'Looks good!'}
                    bind:inner="{inputs.birthday}" />
            </Col>
            <Col md=4 />
            <Col md=4>
                <Label>Email</Label>
                <Input name="email" type="email" disabled={creating}
                    maxlength=320 required placeholder="dammy@example.com"
                    feedback={feedbacks.birthday} valid={feedbacks.birthday == 'Looks good!'}
                    invalid={feedbacks.birthday != '' && feedbacks.birthday != 'Looks good!'}
                    bind:inner={inputs.email} />
            </Col>
            <Col md=4>
                <Label>Email</Label>
                <Input name="mobile" type="tel" disabled={creating}
                    minlength=5 maxlength=15 required placeholder=85298765432
                    feedback={feedbacks.birthday} valid={feedbacks.birthday == 'Looks good!'}
                    invalid={feedbacks.birthday != '' && feedbacks.birthday != 'Looks good!'}
                    bind:inner={inputs.mobile} />
            </Col>
            <Button color="primary" disabled={submitting} class="form-control">
                {#if submitting}
                    <Spinner type="border" size="sm" />Submitting...
                {:else}
                    Submit
                {/if}
            </Button>
        </form>
    </section>
</Layout>