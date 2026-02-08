<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner, Alert, Col, Label, Input, Table } from '@sveltestrap/sveltestrap';
	import Datalist from '@/Pages/Components/Datalist.svelte';
	import Contacts from './Contacts.svelte';
    import { Link } from "@inertiajs/svelte";
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { formatToDate } from '@/timeZoneDatetime';

    let { user: initUser, genders, passportTypes, maxBirthday, districts: areaDistricts } = $props();
    let user = $state({
        id: initUser.id,
        memberNumber: initUser.member?.number,
        isActiveMember: initUser.member?.is_active,
        username: initUser.username,
        prefixName: initUser.member?.prefix_name,
        nickname: initUser.member?.nickname,
        suffixName: initUser.member?.suffix_name,
        familyName: initUser.family_name,
        middleName: initUser.middle_name,
        givenName: initUser.given_name,
        passportTypeID: initUser.passport_type_id,
        passportNumber: initUser.passport_number,
        genderID: initUser.gender_id,
        birthday: formatToDate(initUser.birthday),
        districtID: initUser.address?.district_id ,
        address: initUser.address?.value,
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
        district: '',
        address: '',
    });

    let usernameValue = $state(user.username);
    let newPasswordValue = $state('');
    let confirmNewPasswordValue = $state('');
    let showPassportNumber = $state(false);
    let districtValue = $state(user.districtID ?? '');
    let districts = {};
    for(let [area, object] of Object.entries(areaDistricts)) {
        for(let [key, value] of Object.entries(object)) {
            districts[key] = value;
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
        if(user.isActiveMember || inputs.district.value) {
            if (inputs.district.validity.valueMissing) {
                feedbacks.district = 'The district field is required when you are an active member.';
            }
            if(inputs.address.validity.valueMissing) {
                feedbacks.address = user.isActiveMember ?
                    'The address field is required when you are an active member.' :
                    'The address field is required when district is present.';
            } else if(inputs.address.validity.tooLong) {
                feedbacks.address = `The address must not be greater than ${inputs.address.maxLength} characters.`;
            }
        }

        return !hasError();
    }

    function resetInputValues() {
        inputs.username.value = user.username;
        inputs.password.value = '';
        inputs.newPassword.value = '';
        inputs.confirmNewPassword.value = '';
        inputs.gender.value = genders[user.genderID];
        inputs.birthday.value = user.birthday;
        inputs.district.value = user.districtID;
        inputs.address.value = user.address;
        for(let key in feedbacks) {
            feedbacks[key] = '';
        }
    }

    function successCallback(response) {
        alert(response.data.success);
        genders[response.data.gender_id] = response.data.gender;
        user.username = response.data.username;
        user.genderID = response.data.gender_id;
        user.birthday = formatToDate(response.data.birthday);
        user.districtID = response.data.district_id ?? '';
        user.address = response.data.address;
        editing = false;
        resetInputValues();
        submitting = false;
        updating = false;
    }

    function failCallback(error) {
        if(error.status == 422) {
            for(let key in error.response.data.errors) {
                let value = error.response.data.errors[key];
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
                    if (inputs.district.value) {
                        data['district_id'] = inputs.district.value;
                        data['address'] = inputs.address.value;
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
        resetInputValues();
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
            <form class="row g-3" novalidate onsubmit={update}>
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
                    <div class="form-label">User ID:</div>
                    <div>{user.id}</div>
                </Col>
                <Col md=4>
                    <div class="form-label">Member Number:</div>
                    <div>{user.memberNumber}</div>
                </Col>
                <Col md=4 />
                <Col md=4>
                    <Label for="username">Username:</Label>
                    <Input name="username" type="text" hidden={! editing} disabled={updating}
                        minlength=8 maxlength=16 required placeholder="username"
                        feedback={feedbacks.username} valid={feedbacks.username == 'Looks good!'}
                        invalid={feedbacks.username != '' && feedbacks.username != 'Looks good!'}
                        bind:inner={inputs.username} bind:value={usernameValue} />
                    <div hidden={editing}>{user.username}</div>
                </Col>
                <Col md=4>
                    <Label for="password">Password:</Label>
                    <Input name="password" type="password" disabled={updating} hidden={! editing}
                        required={usernameValue != user.username || newPasswordValue != ''}
                        minlength=8 maxlength=16 placeholder="password"
                        feedback={feedbacks.password} valid={feedbacks.password == 'Looks good!'}
                        invalid={feedbacks.password != '' && feedbacks.password != 'Looks good!'}
                        bind:inner={inputs.password} />
                    <div hidden={editing}>********</div>
                </Col>
                <Col md=4 />
                <Col md=4 hidden={! editing}>
                    <Label for="new_password">New Password:</Label>
                    <Input name="new_password" type="password" disabled={updating}
                        minlength=8 maxlength=16 placeholder="New password"
                        feedback={feedbacks.newPassword} valid={feedbacks.newPassword == 'Looks good!'}
                        invalid={feedbacks.newPassword != '' && feedbacks.newPassword != 'Looks good!'}
                        bind:inner={inputs.newPassword} bind:value={newPasswordValue} />
                </Col>
                <Col md=4 hidden={! editing}>
                    <Label for="new_password_confirmation">Confirm New Password:</Label>
                    <Input name="new_password_confirmation" type="password" disabled={updating || newPasswordValue == ''}
                        required minlength=8 maxlength=16 placeholder="confirm new password"
                        feedback={feedbacks.newPassword} valid={feedbacks.newPassword == 'Looks good!'}
                        invalid={feedbacks.newPassword != '' && feedbacks.newPassword != 'Looks good!'}
                        bind:inner={inputs.confirmNewPassword} bind:value={confirmNewPasswordValue} />
                </Col>
                <Col md=4 hidden={! editing} />
                {#if user.memberNumber}
                    <Col md=4>
                        <div class="form-label">Prefix Name:</div>
                        <div>{user.prefixName ?? "\u00A0"}</div>
                    </Col>
                    <Col md=4>
                        <div class="form-label">Nickname:</div>
                        <div>{user.nickname ?? "\u00A0"}</div>
                    </Col>
                    <Col md=4>
                        <div class="form-label">Suffix Name:</div>
                        <div>{user.suffixName ?? "\u00A0"}</div>
                    </Col>
                {/if}
                <Col md=4>
                    <div class="form-label">Family Name:</div>
                    <div>{user.familyName}</div>
                </Col>
                <Col md=4>
                    <div class="form-label">Middle Name:</div>
                    <div>{user.middleName ?? "\u00A0"}</div>
                </Col>
                <Col md=4>
                    <div class="form-label">Given Name:</div>
                    <div>{user.givenName}</div>
                </Col>
                <Col md=4>
                    <div class="form-label">Passport Type:</div>
                    <div>{passportTypes[user.passportTypeID]}</div>
                </Col>
                <Col md=4>
                    <div class="form-label">Passport Number:</div>
                    <div>
                        {showPassportNumber ? user.passportNumber : '********'}
                        <button type="button" style="border: none; background-color: transparent;"
                            aria-label="{showPassportNumber ? 'Show' : 'Hide'} passport number"
                            onclick={() => showPassportNumber = !showPassportNumber}>
                            <i class={['bi', showPassportNumber ? 'bi-eye' : 'bi-eye-slash']}></i>
                        </button>
                    </div>
                </Col>
                <Col md=4 />
                <Col md=4>
                    <Label for="gender">Gender:</Label>
                    <Input name="gender" type="text" list="genders" hidden={! editing} disabled={updating}
                        maxlength="255" required value={genders[user.genderID]} placeholder="gender"
                        feedback={feedbacks.gender} valid={feedbacks.gender == 'Looks good!'}
                        invalid={feedbacks.gender != '' && feedbacks.gender != 'Looks good!'}
                        bind:inner={inputs.gender} />
                    <div hidden={editing}>{genders[user.genderID]}</div>
                </Col>
                <Datalist id="genders" data={Object.values(genders)} />
                <Col md=4>
                    <Label for="birthday">Date of Birth:</Label>
                    <Input name="birthday" type="date" hidden={! editing} disabled={updating}
                        max={maxBirthday} required value={user.birthday}
                        feedback={feedbacks.birthday} valid={feedbacks.birthday == 'Looks good!'}
                        invalid={feedbacks.birthday != '' && feedbacks.birthday != 'Looks good!'}
                        bind:inner={inputs.birthday} />
                    <div hidden={editing}>{user.birthday}</div>
                </Col>
                <Col md=4 />
                <Col md=4>
                    <Label for="district_id">District:</Label>
                    <Input type="select" name="district_id" hidden={! editing}
                        disabled={updating} required={user.isActiveMember}
                        feedback={feedbacks.district} valid={feedbacks.district == 'Looks good!'}
                        invalid={feedbacks.district != '' && feedbacks.district != 'Looks good!'}
                        bind:inner={inputs.district} bind:value={districtValue}>
                        <option value="">Please select district</option>
                        {#each Object.entries(areaDistricts) as [area, object]}
                            <optgroup label={area}>
                                {#each Object.entries(object) as [key, value]}
                                    <option value={key}>{value}</option>
                                {/each}
                            </optgroup>
                        {/each}
                    </Input>
                    <div hidden={editing}>{districts[user.districtID] ?? "\u00A0"}</div>
                </Col>
                <Col md=8>
                    <Label for="address">Address:</Label>
                    <Input name="address" hidden={! editing}
                        disabled={(! districtValue && ! user.isActiveMember) || updating}
                        required maxlength=255 placeholder="Room 123, 12/F, ABC building, XYZ road"
                        feedback={feedbacks.address} valid={feedbacks.address == 'Looks good!'}
                        invalid={feedbacks.address != '' && feedbacks.address != 'Looks good!'}
                        bind:inner={inputs.address} value={user.address} />
                    <div hidden={editing}>{user.address ?? "\u00A0"}</div>
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
