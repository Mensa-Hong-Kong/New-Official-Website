<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner, Alert, Col, Label, Input, Table } from '@sveltestrap/sveltestrap';
	import Datalist from '@/Pages/Components/Datalist.svelte';
	import Contacts from './Contacts.svelte';
    import { Link } from "@inertiajs/svelte";
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { formatToDate } from '@/timeZoneDatetime';

    let { user: initUser, genders, passportTypes, maxBirthday } = $props();
    let user = $state({
        username: initUser.username,
        familyName: initUser.family_name,
        middleName: initUser.middle_name,
        givenName: initUser.given_name,
        passportTypeID: initUser.passport_type_id,
        passportNumber: initUser.passport_number,
        genderID: initUser.gender_id,
        birthday: formatToDate(initUser.birthday),
    });
    let inputs = $state({});
    let editing = $state(false);
    let submitting = $state(false);
    let updating = $state(false);
    let feedbacks = $state({
        username: '',
        password: '',
        newPassword: '',
        gender: '',
        birthday: '',
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
        for(const key in feedbacks) {
            feedbacks[key] = 'Looks good!';
        }
        if(inputs.username.validity.valueMissing) {
            feedbacks.username = 'The username field is required.';
        } else if(inputs.username.validity.tooShort) {
            feedbacks.username = `The username field must be at least ${inputs.username.minLength} characters.`;
        } else if(inputs.username.validity.tooLong) {
            feedbacks.username = `The username field must not be greater than ${inputs.username.maxLength} characters.`;
        }
        if(
            inputs.username.value != user.username ||
            inputs.newPassword.value || inputs.confirmNewPassword.value
        ) {
            if(inputs.password.validity.valueMissing) {
                feedbacks.password = 'The password field is required when you change the username or password.';
            } else if(inputs.password.validity.tooShort) {
                feedbacks.password = `The password field must be at least ${inputs.password.minLength} characters.`;
            } else if(inputs.password.validity.tooLong) {
                feedbacks.password = `The password field must not be greater than ${inputs.password.maxLength} characters.`;
            }
            if(inputs.newPassword.validity.tooShort) {
                feedbacks.newPassword = `The password field must be at least ${inputs.newPassword.minLength} characters.`;
            } else if(inputs.newPassword.validity.tooLong) {
                feedbacks.newPassword = `The password field must not be greater than ${inputs.newPassword.maxLength} characters.`;
            } else if(inputs.newPassword.value != inputs.confirmNewPassword.value) {
                feedbacks.newPassword = 'The new password confirmation does not match.';
            }
        }
        if(inputs.gender.validity.valueMissing) {
            feedbacks.gender = 'The gender field is required.';
        } else if(inputs.gender.validity.tooLong) {
            feedbacks.gender = `The gender not be greater than ${gender.maxLength} characters.`;
        }
        if(inputs.birthday.validity.valueMissing) {
            feedbacks.birthday = 'The birthday field is required.';
        } else if(inputs.birthday.validity.rangeOverflow) {
            feedbacks.birthday = `The birthday not be greater than ${birthday.max} characters.`;
        }
        return !hasError();
    }

    function resetInputValues() {
        inputs.username.value = user.username;
        inputs.password.value = '';
        inputs.newPassword.value = '';
        inputs.confirmNewPassword.value = '';
        inputs.gender.value = user.gender;
        inputs.birthday.value = user.birthday;
    }

    function successCallback(response) {
        alert(response.data.success);
        genders[response.data.gender_id] = response.data.gender
        user.username = response.data.username;
        user.genderID = response.data.gender_id;
        user.birthday = formatToDate(response.data.birthday);
        editing = false;
        resetInputValues();
        submitting = false;
        updating = false;
    }

    function failCallback(error) {
        if(error.status == 422) {
            for(let key in error.response.data.errors) {
                let value = error.response.data.errors[key];
                let feedback;
                let input;
                switch(key) {
                    case 'username':
                        feedbacks.username = value;
                        break;
                    case 'password':
                        feedbacks.password = value;
                        break;
                    case 'new_password':
                        feedbacks.newPassword = value;
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
            submitting = 'updateProfile'+submitAt;
            if(submitting == 'updateProfile'+submitAt) {
                if(validation()) {
                    updating = true;
                    let data = {
                        username: inputs.username.value,
                        gender: inputs.gender.value,
                        birthday: inputs.birthday.value,
                    }
                    if(
                        inputs.newPassword.value ||
                        inputs.username.value != user.username
                    ) {
                        data['password'] = inputs.password.value;
                    }
                    if(inputs.newPassword.value) {
                        data['new_password'] = inputs.newPassword.value;
                        data['new_password_confirmation'] = inputs.confirmNewPassword.value;
                    }
                    post(
                        route('profile.update'),
                        successCallback,
                        failCallback,
                        'put', data
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
    }

    function edit(event) {
        event.preventDefault();
        editing = true;
    }
</script>

<svelte:head>
    <title>Profile | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <article>
            <form class="row g-3" novalidate onsubmit="{update}">
                <h2 class="mb-2 fw-bold">
                    Profile
                    <Button color="primary" disabled={updating} hidden={! editing} outline={! updating}>
                        {#if updating}
                            <Spinner type="border" size="sm" />Saving...
                        {:else}
                            Save
                        {/if}
                    </Button>
                    <Button color="primary" outline onclick={edit}
                        hidden={editing || updating}>Edit</Button>
                    <Button color="danger" outline onclick={cancel}
                        hidden={! editing || updating}>Cancel</Button>
                </h2>
                <Alert color="primary" hidden={! editing}>
                    <ol>
                        <li>Password only require when you change the username or password</li>
                        <li>New password and confirm password is not require unless you want to change a new password</li>
                    </ol>
                </Alert>
                <Col md=4>
                    <Label>Username</Label>
                    <div hidden={editing}>{user.username}</div>
                    <Input name="username" type="text" hidden={! editing} disabled={updating}
                        minlength=8 maxlength=16 required value={user.username} placeholder="username"
                        feedback={feedbacks.username} valid={feedbacks.username == 'Looks good!'}
                        invalid={feedbacks.username != '' && feedbacks.username != 'Looks good!'}
                        bind:inner={inputs.username} />
                </Col>
                <Col md=4>
                    <Label>Password</Label>
                    <div hidden="{editing}">********</div>
                    <Input name="password" type="password" disabled={updating} hidden={! editing}
                        minlength=8 maxlength=16 required placeholder="password"
                        feedback={feedbacks.password} valid={feedbacks.password == 'Looks good!'}
                        invalid={feedbacks.password != '' && feedbacks.password != 'Looks good!'}
                        bind:inner="{inputs.password}" />
                </Col>
                <Col md=4 />
                <Col md=4 hidden={! editing}>
                    <Label>New Password</Label>
                    <Input name="new_password" type="password" disabled={updating}
                        minlength=8 maxlength=16 placeholder="New password"
                        feedback={feedbacks.newPassword} valid={feedbacks.newPassword == 'Looks good!'}
                        invalid={feedbacks.newPassword != '' && feedbacks.newPassword != 'Looks good!'}
                        bind:inner={inputs.newPassword} />
                </Col>
                <Col md=4 hidden={! editing}>
                    <Label>Confirm New Password</Label>
                    <Input name="new_password_confirmation" type="password" disabled={updating}
                        minlength=8 maxlength=16 placeholder="confirm new password"
                        invalid={feedbacks.newPassword != '' && feedbacks.newPassword != 'Looks good!'}
                        valid={feedbacks.newPassword == 'Looks good!'} bind:inner={inputs.confirmNewPassword} />
                </Col>
                <Col md=4 hidden={! editing}></Col>
                <Col md=4>
                    <div class="form-label">Family Name</div>
                    <div>{user.familyName}</div>
                </Col>
                <Col md=4>
                    <div class="form-label">Middle Name</div>
                    <div>{user.middleName}</div>
                </Col>
                <Col md=4>
                    <div class="form-label">Given Name</div>
                    <div>{user.givenName}</div>
                </Col>
                <Col md=4>
                    <div class="form-label">Passport Type</div>
                    <div>{passportTypes[user.passportTypeID]}</div>
                </Col>
                <Col md=4>
                    <div class="form-label">Passport Number</div>
                    <div>{user.passportNumber}</div>
                </Col>
                <Col md=4 />
                <Col md=4>
                    <Label>Gender</Label>
                    <div hidden="{editing}">{genders[user.genderID]}</div>
                    <Input name="gender" type="text" list="genders" hidden={! editing} disabled={updating}
                        maxlength="255" required value={genders[user.genderID]} placeholder="gender"
                        feedback={feedbacks.gender} valid={feedbacks.gender == 'Looks good!'}
                        invalid={feedbacks.gender != '' && feedbacks.gender != 'Looks good!'}
                        bind:inner={inputs.gender} />
                </Col>
                <Datalist id="genders" data={genders} />
                <Col md=4>
                    <Label>Date of Birth</Label>
                    <div hidden="{editing}">{user.birthday}</div>
                    <Input name="birthday" type="date" hidden={! editing} disabled={updating}
                        max={maxBirthday} required value={user.birthday}
                        feedback={feedbacks.birthday} valid={feedbacks.birthday == 'Looks good!'}
                        invalid={feedbacks.birthday != '' && feedbacks.birthday != 'Looks good!'}
                        bind:inner={inputs.birthday} />
                </Col>
            </form>
        </article>
        <Contacts type="email" contacts={initUser.emails} bind:submitting={submitting} />
        <Contacts type="mobile" contacts={initUser.mobiles} bind:submitting={submitting} />
        {#if initUser.admission_tests.length}
            <article>
                <h3 class="mb-2 fw-bold"><i class="bi bi-clipboard"></i> Admission Test</h3>
                <Table hover>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Is Present</th>
                            <th>Is Pass</th>
                            <th>Show</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each initUser.admission_tests as test}
                            <tr>
                                <th>{formatToDate(test.testing_at)}</th>
                                <td>
                                    <i class={[
                                        'bi', {
                                            'bi-check': test.pivot.is_present,
                                            'bi-x': ! test.pivot.is_present,
                                        }
                                    ]}></i>
                                </td>
                                <td>
                                    {#if test.pivot.is_pass !== null}
                                        <i class={[
                                            'bi', {
                                                'bi-check': test.pivot.is_pass,
                                                'bi-x': ! test.pivot.is_pass,
                                            }
                                        ]}></i>
                                    {/if}
                                </td>
                                <td>
                                    <Link class="btn btn-primary" href={route('admission-tests.candidates.show', {'admission_test': test.id})}>Show</Link>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </Table>
            </article>
        {/if}
    </section>
</Layout>