<script>
    import { seo } from '@/Pages/Layouts/App.svelte';
    import { Alert, Col, Label, Input, Button, Spinner } from '@sveltestrap/sveltestrap';
    import { onMount } from "svelte";
    import ClearInputHistory from '@/clearInputHistory.js';
	import Datalist from '@/Pages/Components/Datalist.svelte';
    import { post } from "@/submitForm";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { router } from '@inertiajs/svelte';

    seo.title = 'Register';

	let { genders, passportTypes, maxBirthday, districts } = $props();
    let inputs = $state({});
    let districtValue = $state('');
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
        district: '',
        address: '',
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
        } else if(inputs.passportNumber.validity.patternMismatch) {
            feedbacks.passportNumber = 'The passport number format is invalid. It should only contain uppercase letters and numbers.';
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
        if(inputs.district.value) {
            if(inputs.address.validity.valueMissing) {
                feedbacks.address = 'The address field is required when district is present.';
            } else if(inputs.address.validity.tooLong) {
                feedbacks.mobile = `The address must not be greater than ${inputs.address.maxLength} characters.`;
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
                    case 'district_id':
                        feedbacks.district = value;
                        break;
                    case 'address':
                        feedbacks.address = value;
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
                if (inputs.district.value) {
                    data['district_id'] = inputs.district.value;
                    data['address'] = inputs.address.value;
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
            <Label for="username">Username</Label>
            <Input name="username" placeholder="username" disabled={creating}
                minlength=8 maxlength=16 required bind:inner={inputs.username}
                feedback={feedbacks.username} valid={feedbacks.username == 'Looks good!'}
                invalid={feedbacks.username != '' && feedbacks.username != 'Looks good!'} />
        </Col>
        <Col md=4>
            <Label for="password">Password</Label>
            <Input name="password" type="password" placeholder="password" disabled={creating}
                minlength=8 maxlength=16 required bind:inner={inputs.password}
                feedback={feedbacks.password} valid={feedbacks.password == 'Looks good!'}
                invalid={feedbacks.password != '' && feedbacks.password != 'Looks good!'} />
        </Col>
        <Col md=4>
            <Label for="password_confirmation">Confirm Password</Label>
            <Input name="password_confirmation" type="password" disabled={creating}
                minlength=8 maxlength=16 required placeholder="confirm password"
                invalid={feedbacks.password != '' && feedbacks.password != 'Looks good!'}
                valid={feedbacks.password == 'Looks good!'} bind:inner={inputs.confirmPassword} />
        </Col>
        <Col md=4>
            <Label for="family_name">Family Name</Label>
            <Input name="family_name" disabled={creating}
                maxlength=255 required placeholder="family name"
                feedback={feedbacks.familyName} valid={feedbacks.familyName == 'Looks good!'}
                invalid={feedbacks.familyName != '' && feedbacks.familyName != 'Looks good!'}
                bind:inner="{inputs.familyName}" />
        </Col>
        <Col md=4>
            <Label for="middle_name">Middle Name</Label>
            <Input name="middle_name" disabled={creating}
                maxlength="255" placeholder="middle name"
                feedback={feedbacks.middleName} valid={feedbacks.middleName == 'Looks good!'}
                invalid={feedbacks.middleName != '' && feedbacks.middleName != 'Looks good!'}
                bind:inner="{inputs.middleName}" />
        </Col>
        <Col md=4>
            <Label for="given_name">Given Name</Label>
            <Input name="given_name" type="text" disabled={creating}
                maxlength=255 required placeholder="given name"
                feedback={feedbacks.givenName} valid={feedbacks.givenName == 'Looks good!'}
                invalid={feedbacks.givenName != '' && feedbacks.givenName != 'Looks good!'}
                bind:inner="{inputs.givenName}" />
        </Col>
        <Col md=4>
            <Label for="passport_type_id">Passport Type</Label>
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
            <Label for="passport_number">Passport Number</Label>
            <Input name="passport_number" disabled={creating}
                required minlength=8 maxlength=18 pattern="^[A-Z0-9]+$" placeholder="passport number"
                feedback={feedbacks.passportNumber} valid={feedbacks.passportNumber == 'Looks good!'}
                invalid={feedbacks.passportNumber != '' && feedbacks.passportNumber != 'Looks good!'}
                bind:inner="{inputs.passportNumber}" />
        </Col>
        <Col md=4 />
        <Col md=4>
            <Label for="gender">Gender</Label>
            <Input name="gender" disabled={creating}
                maxlength="255" list="genders" required placeholder="gender"
                feedback={feedbacks.gender} valid={feedbacks.gender == 'Looks good!'}
                invalid={feedbacks.gender != '' && feedbacks.gender != 'Looks good!'}
                bind:inner={inputs.gender} />
        </Col>
        <Datalist id="genders" data={genders} />
        <Col md=4>
            <Label for="birthday">Date of Birth</Label>
            <Input name="birthday" type="date" disabled={creating}
                max={maxBirthday} required placeholder="birthday"
                feedback={feedbacks.birthday} valid={feedbacks.birthday == 'Looks good!'}
                invalid={feedbacks.birthday != '' && feedbacks.birthday != 'Looks good!'}
                bind:inner="{inputs.birthday}" />
        </Col>
        <Col md=4 />
        <Col md=4>
            <Label for="email">Email</Label>
            <Input name="email" type="email" disabled={creating}
                maxlength=320 placeholder="dammy@example.com"
                feedback={feedbacks.email} valid={feedbacks.email == 'Looks good!'}
                invalid={feedbacks.email != '' && feedbacks.email != 'Looks good!'}
                bind:inner={inputs.email} />
        </Col>
        <Col md=4>
            <Label for="mobile">Mobile</Label>
            <Input name="mobile" type="tel" disabled={creating}
                minlength=5 maxlength=15 placeholder=85298765432
                feedback={feedbacks.mobile} valid={feedbacks.mobile == 'Looks good!'}
                invalid={feedbacks.mobile != '' && feedbacks.mobile != 'Looks good!'}
                bind:inner={inputs.mobile} />
        </Col>
        <Col md=4 />
        <Col md=4>
            <Label for="district_id">District</Label>
            <Input type="select" name="district_id" disabled={creating}
                feedback={feedbacks.district} valid={feedbacks.district == 'Looks good!'}
                invalid={feedbacks.district != '' && feedbacks.district != 'Looks good!'}
                bind:inner={inputs.district} bind:value={districtValue}>
                <option value="" selected>Please select district</option>
                {#each Object.entries(districts) as [area, object]}
                    <optgroup label={area}>
                        {#each Object.entries(object) as [key, value]}
                            <option value={key}>{value}</option>
                        {/each}
                    </optgroup>
                {/each}
            </Input>
        </Col>
        <Col md=8>
            <Label for="address">Address</Label>
            <Input name="address" disabled={! districtValue || creating}
                maxlength=255 required placeholder="Room 123, 12/F, ABC building, XYZ road"
                feedback={feedbacks.address} valid={feedbacks.address == 'Looks good!'}
                invalid={feedbacks.address != '' && feedbacks.address != 'Looks good!'}
                bind:inner={inputs.address} />
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
