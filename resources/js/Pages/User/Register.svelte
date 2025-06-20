<script>
	let { genders, passportTypes, maxBirthday } = $props();
    import { onMount } from "svelte";
    import ClearInputHistory from '@/clearInputHistory.js';
	import Datalist from '@/Pages/Components/Datalist.svelte';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';

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
            feedbacks.birthday = `The birthday not be greater than ${inputs.birthday.max} characters.`;
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
        window.location.href = response.request.responseURL;
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

<section class="container">
    <div class="alert alert-primary" role="alert">
        <ol>
            <li>
                Passport number include inside brackets number but without all symbol<br>
                Example 1: A123456(7) should type A1234567
                Example 1: 1234567(8) should type 12345678
            </li>
            <li>The family name, middle name, given name and gender must match passport</li>
            <li>Mobile number include country code without "+" and "-"</li>
        </ol>
    </div>
    <form class="row g-3" onsubmit="{register}" novalidate>
        <h2 class="mb-2 fw-bold text-uppercase">Register</h2>
        <div class="col-md-4">
            <label for="username" class="form-label">Username</label>
            <input name="username" type="text" placeholder="username" disabled="{creating}"
                minlength="8" maxlength="16" required bind:this="{inputs.username}"
                class={[
                    'form-control', {
                        'is-valid': feedbacks.username == 'Looks good!',
                        'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.username),
                    }
                ]} />
            <div class={[{
                'valid-feedback': ['', 'Looks good!'].includes(feedbacks.username),
                'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.username),
            }]}>{feedbacks.username}</div>
        </div>
        <div class="col-md-4">
            <label for="password" class="form-label">Password</label>
            <input name="password" type="password" placeholder="password" disabled="{creating}"
                minlength="8" maxlength="16" required bind:this="{inputs.password}"
                class={[
                    'form-control', {
                        'is-valid': feedbacks.password == 'Looks good!',
                        'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.password),
                    }
                ]} />
            <div class={[{
                'valid-feedback': ['', 'Looks good!'].includes(feedbacks.password),
                'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.password),
            }]}>{feedbacks.password}</div>
        </div>
        <div class="col-md-4">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input name="password_confirmation" type="password" disabled="{creating}"
                minlength="8" maxlength="16" required placeholder="confirm password"
                bind:this="{inputs.confirmPassword}"
                class={[
                    'form-control', {
                        'is-valid': feedbacks.password == 'Looks good!',
                        'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.password),
                    }
                ]} />
        </div>
        <div class="col-md-4">
            <label for="family_name" class="form-label">Family Name</label>
            <input name="family_name" type="text" disabled="{creating}"
                maxlength="255" required placeholder="family name"
                bind:this="{inputs.familyName}" class={[
                    'form-control', {
                        'is-valid': feedbacks.familyName == 'Looks good!',
                        'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.familyName),
                    }
                ]} />
            <div class={[{
                'valid-feedback': ['', 'Looks good!'].includes(feedbacks.familyName),
                'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.familyName),
            }]}>{feedbacks.familyName}</div>
        </div>
        <div class="col-md-4">
            <label for="middle_name" class="form-label">Middle Name</label>
            <input name="middle_name" type="text" disabled="{creating}"
                maxlength="255" placeholder="middle name"
                bind:this="{inputs.middleName}" class={[
                    'form-control', {
                        'is-valid': feedbacks.middleName == 'Looks good!',
                        'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.middleName),
                    }
                ]} />
            <div class={[{
                'valid-feedback': ['', 'Looks good!'].includes(feedbacks.middleName),
                'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.middleName),
            }]}>{feedbacks.middleName}</div>
        </div>
        <div class="col-md-4">
            <label for="given_name" class="form-label">Given Name</label>
            <input name="given_name" type="text" disabled="{creating}"
                maxlength="255" required placeholder="given name"
                bind:this="{inputs.givenName}" class={[
                    'form-control', {
                        'is-valid': feedbacks.givenName == 'Looks good!',
                        'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.givenName),
                    }
                ]} />
            <div class={[{
                'valid-feedback': ['', 'Looks good!'].includes(feedbacks.givenName),
                'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.givenName),
            }]}>{feedbacks.givenName}</div>
        </div>
        <div class="col-md-4">
            <label for="passport_type_id" class="form-label">Passport Type</label>
            <select name="passport_type_id" required disabled="{creating}"
                bind:this="{inputs.passportType}" class={[
                    'form-select', {
                        'is-valid': feedbacks.passportType == 'Looks good!',
                        'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.passportType),
                    }
                ]}>
                <option value="" selected disabled>Please select passport type</option>
                {#each Object.entries(passportTypes) as [key, value]}
                    <option value="{key}">{value}</option>
                {/each}
            </select>
            <div class={[{
                'valid-feedback': ['', 'Looks good!'].includes(feedbacks.passportType),
                'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.passportType),
            }]}>{feedbacks.passportType}</div>
        </div>
        <div class="col-md-4">
            <label for="passport_number">Passport Number</label>
            <input name="passport_number" type="text" disabled="{creating}"
                minlength="8" maxlength="18" required placeholder="passport number"
                bind:this="{inputs.passportNumber}" class={[
                    'form-control', {
                        'is-valid': feedbacks.passportNumber == 'Looks good!',
                        'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.passportNumber),
                    }
                ]} />
            <div class={[{
                'valid-feedback': ['', 'Looks good!'].includes(feedbacks.passportNumber),
                'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.passportNumber),
            }]}>{feedbacks.passportNumber}</div>
        </div>
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <label for="gender" class="form-label">Gender</label>
            <input name="gender" type="text" disabled="{creating}"
                maxlength="255" list="genders" required placeholder="gender"
                bind:this="{inputs.gender}" class={[
                    'form-control', {
                        'is-valid': feedbacks.gender == 'Looks good!',
                        'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.gender),
                    }
                ]} />
            <div class={[{
                'valid-feedback': ['', 'Looks good!'].includes(feedbacks.gender),
                'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.gender),
            }]}>{feedbacks.gender}</div>
        </div>
        <Datalist id="genders" data={genders} />
        <div class="col-md-4">
            <label for="birthday">Date of Birth</label>
            <input name="birthday" type="date" disabled="{creating}"
                max="{maxBirthday}" required placeholder="birthday"
                bind:this="{inputs.birthday}" class={[
                    'form-control', {
                        'is-valid': feedbacks.birthday == 'Looks good!',
                        'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.birthday),
                    }
                ]} />
            <div class={[{
                'valid-feedback': ['', 'Looks good!'].includes(feedbacks.birthday),
                'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.birthday),
            }]}>{feedbacks.birthday}</div>
        </div>
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <label for="email">Email</label>
            <input name="email" type="email" disabled="{creating}"
                maxlength="320" required placeholder="dammy@example.com"
                bind:this="{inputs.email}" class={[
                    'form-control', {
                        'is-valid': feedbacks.email == 'Looks good!',
                        'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.email),
                    }
                ]} />
            <div class={[{
                'valid-feedback': ['', 'Looks good!'].includes(feedbacks.email),
                'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.email),
            }]}>{feedbacks.email}</div>
        </div>
        <div class="col-md-4">
            <label for="mobile">Mobile</label>
            <input name="mobile" type="tel" disabled="{creating}"
                minlength="5" maxlength="15" required placeholder="85298765432"
                bind:this="{inputs.mobile}" class={[
                    'form-control', {
                        'is-valid': feedbacks.mobile == 'Looks good!',
                        'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.mobile),
                    }
                ]} />
            <div class={[{
                'valid-feedback': ['', 'Looks good!'].includes(feedbacks.mobile),
                'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.mobile),
            }]}>{feedbacks.mobile}</div>
        </div>
        <input type="submit" class="form-control btn btn-primary" value="Submit" hidden="{submitting}">
        <button class="form-control btn btn-primary" type="button" disabled hidden="{!submitting}">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Submitting...
        </button>
    </form>
</section>
