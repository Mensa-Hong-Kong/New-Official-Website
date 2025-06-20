<script>
	let { passportTypes, maxBirthday } = $props();
    import { onMount } from "svelte";
    import ClearInputHistory from '@/clearInputHistory.js';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';

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
        bootstrapAlert(response.data.success);
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
<section class="container">
    <form class="mx-auto w-25" novalidate onsubmit="{forgetPassword}">
        <h2 class="mb-2 fw-bold text-uppercase">Forget Password</h2>
        <div class="mb-4">
            <div class="form-floating">
                <select name="passport_type_id" required disabled="{forgetting}"
                    bind:this="{inputs.passportType}" class={[
                        'form-select', {
                            'is-valid': feedbacks.passportType == 'Looks good!',
                            'is-invalid': feedbacks.failed != '' ||
                                ! ['', 'Looks good!'].includes(feedbacks.passportType),
                        }
                    ]}>
                    <option value="" selected disabled>Please select passport type</option>
                    {#each Object.entries(passportTypes) as [key, value]}
                        <option value="{key}">{value}</option>
                    {/each}
                </select>
                <label for="passport_type_id" class="form-label">Passport Type</label>
                <div class={[{
                    'valid-feedback': ['', 'Looks good!'].includes(feedbacks.passportType),
                    'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.passportType),
                }]}>{feedbacks.passportType}</div>
            </div>
        </div>
        <div class="mb-4">
            <div class="form-floating">
                <input name="passport_number" type="text" disabled="{forgetting}"
                    minlength="8" maxlength="18" required placeholder="passport number"
                    bind:this="{inputs.passportNumber}" class={[
                        'form-control', {
                            'is-valid': feedbacks.passportNumber == 'Looks good!',
                            'is-invalid': feedbacks.failed != '' ||
                                ! ['', 'Looks good!'].includes(feedbacks.passportNumber),
                        }
                    ]} />
                <label for="passport_number">Passport Number</label>
                <div class={[{
                    'valid-feedback': ['', 'Looks good!'].includes(feedbacks.passportNumber),
                    'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.passportNumber),
                }]}>{feedbacks.passportNumber}</div>
            </div>
        </div>
        <div class="mb-4">
            <div class="form-floating">
                <input name="birthday" type="date" disabled="{forgetting}"
                    max="{maxBirthday}" required placeholder="birthday"
                    bind:this="{inputs.birthday}" class={[
                        'form-control', {
                            'is-valid': feedbacks.birthday == 'Looks good!',
                            'is-invalid': feedbacks.failed != '' ||
                                ! ['', 'Looks good!'].includes(feedbacks.birthday),
                        }
                    ]} />
                <label for="birthday">Birthday</label>
                <div class={[{
                    'valid-feedback': ['', 'Looks good!'].includes(feedbacks.birthday),
                    'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.birthday),
                }]}>{feedbacks.birthday}</div>
            </div>
        </div>
        <div class="mb-4">
            <div class="form-floating">
                <select name="verified_contact_type" required disabled="{forgetting}"
                    bind:this="{inputs.verifiedContactType}" class={[
                        'form-select', {
                            'is-valid': feedbacks.verifiedContactType == 'Looks good!',
                            'is-invalid': feedbacks.failed != '' ||
                                ! ['', 'Looks good!'].includes(feedbacks.verifiedContactType),
                        }
                    ]} bind:value="{verifiedContactTypeValue}">
                    <option value="" selected disabled>Please select verified contact type</option>
                    <option value="email">Email</option>
                    <option value="mobile">Mobile</option>
                </select>
                <label for="verified_contact_type" class="form-label">Verified Contact Type</label>
                <div class={[{
                    'valid-feedback': ['', 'Looks good!'].includes(feedbacks.verifiedContactType),
                    'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.verifiedContactType),
                }]}>{feedbacks.verifiedContactType}</div>
            </div>
        </div>
        <div class="mb-4">
            <div class="form-floating">
                {#if verifiedContactTypeValue == 'email'}
                    <input name="email" type="email" disabled="{forgetting}"
                        maxlength="320" required placeholder="dammy@example.com"
                        bind:this="{inputs.verifiedContact}" class={[
                            'form-control', {
                                'is-valid': feedbacks.verifiedContact == 'Looks good!',
                                'is-invalid': feedbacks.failed != '' ||
                                    ! ['', 'Looks good!'].includes(feedbacks.verifiedContact),
                            }
                        ]}>
                {:else if verifiedContactTypeValue == 'mobile'}
                    <input type="tel"  name="verified_contact" disabled="{forgetting}"
                        minlength="5" maxlength="15" required placeholder="85298765432"
                        bind:this="{inputs.verifiedContact}" class={[
                            'form-control', {
                                'is-valid': feedbacks.verifiedContact == 'Looks good!',
                                'is-invalid': feedbacks.failed != '' ||
                                    ! ['', 'Looks good!'].includes(feedbacks.verifiedContact),
                            }
                        ]}>
                {:else}
                    <input type="text" name="verified_contact" placeholder="Verified Contact"
                        bind:this="{inputs.verifiedContact}" disabled class={[
                            'form-control', {
                                'is-valid': feedbacks.verifiedContact == 'Looks good!',
                                'is-invalid': feedbacks.failed != '' ||
                                    ! ['', 'Looks good!'].includes(feedbacks.verifiedContact),
                            }
                        ]}>
                {/if}
                <label for="verified_contact">Verified Contact</label>
                <div class={[{
                    'valid-feedback': ['', 'Looks good!'].includes(feedbacks.verifiedContact),
                    'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.verifiedContact),
                }]}>{feedbacks.verifiedContact}</div>
            </div>
        </div>
        <div class="mb-4">
            <input type="submit" class="form-control btn btn-primary" value="Reset Password" hidden="{submitting}">
            <div class="alert alert-danger" role="alert" hidden="{feedbacks.failed == ''}">{feedbacks.failed}</div>
            <div class="alert alert-danger" role="alert" hidden="{feedbacks.succeeded == ''}">{feedbacks.succeeded}</div>
            <button class="form-control btn btn-primary" type="button" disabled hidden="{! submitting}">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Resetting...
            </button>
        </div>
        <div class="mb-4 row">
            <div class="col d-flex justify-content-center">
                <a id="login" href="{route('login')}" class="form-control btn btn-outline-primary">Login</a>
                <button id="disabledLogin" class="form-control btn btn-outline-primary" disabled hidden>Login</button>
            </div>
            <div class="col d-flex justify-content-center">
                <a id="register" href="{route('register')}" class="form-control btn btn-outline-success">Register</a>
                <button id="disabledRegister" class="form-control btn btn-outline-success" disabled hidden>Register</button>
            </div>
        </div>
    </form>
</section>