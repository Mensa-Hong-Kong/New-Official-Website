<script>
    import Layout from '@/Pages/Layouts/App.svelte';
	import Datalist from '@/Pages/Components/Datalist.svelte';
    import Contacts from './Contacts.svelte';
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { post } from "@/submitForm.svelte";
    import { Button, Spinner, Col, Row, Label, Input } from '@sveltestrap/sveltestrap';
    import { formatToDate } from '@/timeZoneDatetime';

    let {auth, user: initUser, passportTypes, genders, maxBirthday} = $props();
    let user = $state({
        id: initUser.id,
        username: initUser.username,
        familyName: initUser.family_name,
        middleName: initUser.middle_name,
        givenName: initUser.given_name,
        passportTypeID: initUser.passport_type_id,
        passportNumber: initUser.passport_number,
        genderID: initUser.gender_id,
        birthday: formatToDate(initUser.birthday),
        defaultEmail: null,
        defaultMobile: null
    });

    for(let row of initUser.emails) {
        if(row.is_default) {
            user.defaultEmail = row.id;
        }
    }
    
    for(let row of initUser.mobiles) {
        if(row.is_default) {
            user.defaultMobile = row.id;
        }
    }

    let inputs = $state({});
    let editing = $state(false);
    let submitting = $state(false);
    let updating = $state(false);
    let resettingPassword = $state(false);
    let feedbacks = $state({
        username: '',
        familyName: '',
        middleName: '',
        givenName: '',
        passportType: '',
        passportNumber: '',
        gender: '',
        birthday: '',
    });

    function resetInputValues() {
        inputs.username.value = user.username;
        inputs.familyName.value = user.familyName;
        inputs.middleName.value = user.middleName;
        inputs.givenName.value = user.givenName;
        inputs.passportType.value = user.passportTypeID;
        inputs.passportNumber.value = user.passportNumber;
        inputs.gender.value = user.genderID;
        inputs.birthday.value = user.birthday;
        for(let key in feedbacks) {
            feedbacks[key] = '';
        }
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
        if(inputs.username.validity.valueMissing) {
            feedbacks.username = 'The username field is required.';
        } else if(inputs.username.validity.tooShort) {
            feedbacks.username = `The username field must be at least ${inputs.username.minLength} characters.`;
        } else if(inputs.username.validity.tooLong) {
            feedbacks.username = `The username field must not be greater than ${inputs.username.maxLength} characters.`;
        }
        if(inputs.familyName.validity.valueMissing) {
            feedbacks.familyName = 'The family name field is required.';
        } else if(inputs.familyName.validity.tooLong) {
            feedbacks.familyName = `The family name not be greater than ${inputs.familyName.maxLength} characters.`;
        }
        if(inputs.middleName.value && inputs.middleName.validity.tooLong) {
            feedbacks.middleName = `The middle name not be greater than ${inputs.middleName.maxLength} characters.`;
        }
        if(inputs.givenName.validity.valueMissing) {
            feedbacks.givenName = 'The given name field is required.';
        } else if(inputs.givenName.validity.tooLong) {
            feedbacks.givenName = `The given name not be greater than ${inputs.givenName.maxLength} characters.`;
        }
        if(inputs.passportType.validity.valueMissing) {
            feedbacks.passportType = 'The passport type field is required.';
        }
        if(inputs.passportNumber.validity.valueMissing) {
            feedbacks.passportNumber = 'The passport number field is required.';
        } else if(inputs.passportNumber.validity.tooShort) {
            feedbacks.passportNumber = `The passport number must be at least ${inputs.passportNumber.minLength} characters.`;
        } else if(inputs.passportNumber.validity.tooLong) {
            feedbacks.passportNumber = `The passport number not be greater than ${inputs.passportNumber.maxLength} characters.`;
        }
        if(inputs.gender.validity.valueMissing) {
            feedbacks.gender = 'The gender field is required.';
        } else if(inputs.gender.validity.tooLong) {
            feedbacks.gender = `The gender not be greater than ${inputs.gender.maxLength} characters.`;
        }
        if(inputs.birthday.validity.valueMissing) {
            feedbacks.birthday = 'The birthday field is required.';
        } else if(inputs.birthday.validity.rangeOverflow) {
            feedbacks.birthday = `The birthday field must be a date before or equal to ${inputs.birthday.max}.`;
        }
        return ! hasError();
    }

    function updateSuccessCallback(response) {
        alert(response.data.success);
        genders[response.data.gender_id] = response.data.gender;
        user.username = response.data.username;
        user.familyName = response.data.family_name;
        user.middleName = response.data.middle_name;
        user.givenName = response.data.given_name;
        user.passportTypeID = response.data.passport_type_id;
        user.passportNumber = response.data.passport_number;
        user.genderID = response.data.gender_id;
        user.birthday = response.data.birthday;
        editing = false;
        resetInputValues();
        submitting = false;
        updating = false;
    }

    function updateFailCallback(error) {
        if(error.status == 422) {
            for(let key in error.response.data.errors) {
                let value = error.response.data.errors[key];
                let feedback;
                let input;
                switch(key) {
                    case 'username':
                        feedbacks.username = value;;
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
                    default:
                        alert(`Undefine Feedback Key: ${key}\nMessage: ${message}`);
                        break;
                }
            }
        }
        submitting = false;
        updating = false;
    }
    
    function update(event) {
        event.preventDefault();
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'update'+submitAt;
            if(submitting == 'update'+submitAt) {
                if(validation()) {
                    updating = true;
                    post(
                        route(
                            'admin.users.update',
                            {user: user.id}
                        ),
                        updateSuccessCallback,
                        updateFailCallback,
                        'put', {
                            username: inputs.username.value,
                            family_name: inputs.familyName.value,
                            middle_name: inputs.middleName.value,
                            given_name: inputs.givenName.value,
                            passport_type_id: inputs.passportType.value,
                            passport_number: inputs.passportNumber.value,
                            gender: inputs.gender.value,
                            birthday: inputs.birthday.value,
                        }
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }

    function cancel(event) {
        event.preventDefault();
        editing = false;
        resetInputValues();
    }

    function edit(event) {
        event.preventDefault();
        editing = true;
    }

    function resetPasswordSuccessCallback(response) {
        alert(response.data.success);
        resettingPassword = false;
    }

    function resetPasswordFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.contact_type);
        }
        resettingPassword = false;
    }

    function resetPassword(event) {
        event.preventDefault();
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'resetPassword'+submitAt;
            if(submitting == 'resetPassword'+submitAt) {
                resettingPassword = true;
                post(
                    route(
                        'admin.users.reset-password',
                        {user: user.id}
                    ),
                    resetPasswordSuccessCallback,
                    resetPasswordFailCallback,
                    'put', {contact_type: event.target.value}
                );
            }
        }
    }
</script>

<svelte:head>
    <title>Administration Show User | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <article>
            <form method="POST" class="row g-3" novalidate onsubmit="{update}">
                <h3 class="mb-2 fw-bold">
                    Info
                    {#if 
                        auth.user.permissions.includes('Edit:User') ||
                        auth.user.roles.includes('Super Administrator')
                    }
                        <Button color="primary" hidden={! updating} disabled>
                            <Spinner type="border" size="sm" />Saving...
                        </Button>
                        <Button color="primary" onclick={edit} hidden={editing || updating}>Edit</Button>
                        <Button color="primary"hidden={! editing || updating}
                            disabled={submitting}>Save</Button>
                        <Button color="danger" onclick={cancel} hidden={!editing || updating}>Cancel</Button>
                    {/if}
                </h3>
                <Col md="4">
                    <Label for="username">Username</Label>
                    <div hidden="{editing}">{user.username}</div>
                    <Input name="username" minlength="8" maxlength="16" required
                        hidden={! editing} disabled={updating}
                        value={user.username} placeholder="username"
                        valid={feedbacks.username == 'Looks good!'}
                        invalid={feedbacks.username != '' && feedbacks.username != 'Looks good!' }
                        feedback={feedbacks.username} bind:inner={inputs.username} />
                </Col>
                <Col md="5">
                    <Label>Password</Label>
                    <Row>
                        <Col md="2">********</Col>
                        {#if 
                            auth.user.permissions.includes('Edit:User') ||
                            auth.user.roles.includes('Super Administrator')
                        }
                            <Button color={user.defaultEmail ? 'danger' : 'secondary'}
                                hidden={resettingPassword}
                                disabled={! user.defaultEmail || submitting}
                                name="contact_type" value="email" class="col-4"
                                onclick={resetPassword}>Reset by Email</Button>
                            <Button color="danger" hidden={! resettingPassword} disabled class="col-4">
                                <Spinner type="border" size="sm" />Resetting...
                            </Button>
                            <Button color={user.defaultMobile ? 'danger' : 'secondary'}
                                hidden={resettingPassword}
                                disabled={! user.defaultMobile || submitting}
                                name="contact_type" value="mobile" class="col-4"
                                onclick={resetPassword}>Reset by Mobile</Button>
                        {/if}
                    </Row>
                </Col>
                <Col md="3"></Col>
                <Col md="4">
                    <Label for="family_name">Family Name</Label>
                    <div hidden="{editing}">{user.familyName}</div>
                    <Input name="family_name" maxlength="255" required
                        hidden={! editing} disabled={updating}
                        value={user.familyName} placeholder="family name"
                        valid={feedbacks.familyName == 'Looks good!'}
                        invalid={feedbacks.familyName != '' && feedbacks.familyName != 'Looks good!' }
                        feedback={feedbacks.familyName} bind:inner={inputs.familyName} />
                </Col>
                <Col md="4">
                    <Label for="middle_name">Middle Name</Label>
                    <div hidden="{editing}">{user.middleName}</div>
                    <Input name="middle_name" maxlength="255"
                        hidden={! editing} disabled={updating}
                        value={user.middleName} placeholder="middle name"
                        valid={feedbacks.middleName == 'Looks good!'}
                        invalid={feedbacks.middleName != '' && feedbacks.middleName != 'Looks good!' }
                        feedback={feedbacks.middleName} bind:inner={inputs.middleName} />
                </Col>
                <Col md="4">
                    <Label for="given_name">Given Name</Label>
                    <div hidden="{editing}">{user.givenName}</div>
                    <Input name="given_name" maxlength="255" required
                        hidden={! editing} disabled={updating}
                        value={user.givenName} placeholder="given name"
                        valid={feedbacks.givenName == 'Looks good!'}
                        invalid={feedbacks.givenName != '' && feedbacks.givenName != 'Looks good!' }
                        feedback={feedbacks.givenName} bind:inner={inputs.givenName} />
                </Col>
                <Col md="4">
                    <Label for="passport_type_id">Passport Type</Label>
                    <div hidden="{editing}">{passportTypes[user.passportTypeID]}</div>
                    <Input name="passport_type_id" type="select" required
                        hidden={! editing} disabled={updating}
                        valid={feedbacks.passportType == 'Looks good!'}
                        invalid={feedbacks.passportType != '' && feedbacks.passportType != 'Looks good!' }
                        feedback={feedbacks.passportType} bind:inner={inputs.passportType}>
                        {#each Object.entries(passportTypes) as [key, value]}
                            <option value="{key}" selected={key == user.passportTypeID}>{value}</option>
                        {/each}
                    </Input>
                </Col>
                <Col md="4">
                    <Label for="passport_number">Passport Number</Label>
                    <div hidden="{editing}">{user.passportNumber}</div>
                    <Input name="passport_number" minlength="8" maxlength="18" required
                        hidden={! editing} disabled={updating}
                        value={user.passportNumber} placeholder="passport number"
                        valid={feedbacks.passportNumber == 'Looks good!'}
                        invalid={feedbacks.passportNumber != '' && feedbacks.passportNumber != 'Looks good!' }
                        feedback={feedbacks.passportNumber} bind:inner={inputs.passportNumber} />
                </Col>
                <Col md="4"></Col>
                <Col md="4">
                    <Label for="gender">Gender</Label>
                    <div hidden="{editing}">{genders[user.genderID]}</div>
                    <Input name="gender" maxlength=255 required
                        hidden={! editing} disabled={updating} list="genders"
                        value={genders[user.genderID]} placeholder="gender"
                        valid={feedbacks.gender == 'Looks good!'}
                        invalid={feedbacks.gender != '' && feedbacks.gender != 'Looks good!' }
                        feedback={feedbacks.gender} bind:inner={inputs.gender} />
                </Col>
                <Datalist id="genders" data={genders} />
                <Col md="4">
                    <Label for="birthday">Date of Birth</Label>
                    <div hidden="{editing}">{user.birthday}</div>
                    <Input type="date" name="birthday" max={maxBirthday} required
                        hidden={! editing} disabled={updating}
                        value={user.birthday} placeholder="birthday"
                        valid={feedbacks.birthday == 'Looks good!'}
                        invalid={feedbacks.birthday != '' && feedbacks.birthday != 'Looks good!' }
                        feedback={feedbacks.birthday} bind:inner={inputs.birthday} />
                </Col>
            </form>
        </article>
        <Contacts auth={auth} type='email' contacts={initUser.emails}
            bind:submitting={submitting} bind:defaultContact={user.defaultEmail} />
        <Contacts auth={auth} type='mobile' contacts={initUser.mobiles}
            bind:submitting={submitting} bind:defaultContact={user.defaultMobile} />
    </section>
</Layout>