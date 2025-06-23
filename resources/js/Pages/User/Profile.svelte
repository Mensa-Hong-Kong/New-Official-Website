<script>
    let { user: initUser, genders, passportTypes, maxBirthday } = $props();
	import Datalist from '@/Pages/Components/Datalist.svelte';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import Contacts from './Contacts.svelte';

    console.log(initUser);

    let user = $state({
        username: initUser.username,
        familyName: initUser.family_name,
        middleName: initUser.middle_name,
        givenName: initUser.given_name,
        passportTypeID: initUser.passport_type_id,
        passportNumber: initUser.passport_number,
        genderID: initUser.gender_id,
        birthday: (new Date(initUser.birthday)).toISOString().split('T')[0],
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
        user.birthday = response.data.birthday;
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

<section class="container">
    <article>
        <form class="row g-3" novalidate onsubmit="{update}">
            <h2 class="mb-2 fw-bold">
                Profile
                <button class="btn btn-primary" type="button" disabled hidden="{! updating}">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Saving...
                </button>
                <button class="btn btn-outline-primary"
                    onclick="{edit}" hidden="{editing || updating}">Edit</button>
                <button type="submit" class="btn btn-outline-primary"
                    hidden="{! editing || updating}" disabled="{submitting}">Save</button>
                <button class="btn btn-outline-danger" onclick="{cancel}"
                    hidden="{! editing || updating}">Cancel</button>
            </h2>
            <div class="alert alert-primary" role="alert" hidden="{! editing}">
                <ol>
                    <li>Password only require when you change the username or password</li>
                    <li>New password and confirm password is not require unless you want to change a new password</li>
                </ol>
            </div>
            <div class="col-md-4">
                <label for="username" class="form-label">Username</label>
                <div hidden="{editing}">{user.username}</div>
                <input name="username" type="text" hidden="{! editing}" disabled="{updating}"
                    minlength="8" maxlength="16" required
                    value="{user.username}" placeholder="username"
                    bind:this="{inputs.username}" class={[
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
                <div hidden="{editing}">********</div>
                <input name="password" type="password" placeholder="password"
                    minlength="8" maxlength="16" required
                    disabled="{updating}" hidden="{! editing}"
                    bind:this="{inputs.password}" class={[
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
            <div class="col-md-4"></div>
            <div class="col-md-4" hidden="{! editing}">
                <label for="new_password" class="form-label">New Password</label>
                <input name="new_password" type="password" placeholder="New password" disabled="{updating}"
                    minlength="8" maxlength="16" bind:this="{inputs.newPassword}" class={[
                        'form-control', {
                            'is-valid': feedbacks.newPassword == 'Looks good!',
                            'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.newPassword),
                        }
                    ]} />
                <div class={[{
                    'valid-feedback': ['', 'Looks good!'].includes(feedbacks.newPassword),
                    'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.newPassword),
                }]}>{feedbacks.newPassword}</div>
            </div>
            <div class="col-md-4 newPasswordColumn" hidden="{! editing}">
                <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                <input name="new_password_confirmation" type="password" placeholder="confirm new password"
                    minlength="8" maxlength="16" bind:this="{inputs.confirmNewPassword}"
                    disabled="{updating}" class={[
                        'form-control', {
                            'is-valid': feedbacks.newPassword == 'Looks good!',
                            'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.newPassword),
                        }
                    ]} />
            </div>
            <div class="col-md-4 newPasswordColumn" hidden="{! editing}"></div>
            <div class="col-md-4">
                <div class="form-label">Family Name</div>
                <div>{user.familyName}</div>
            </div>
            <div class="col-md-4">
                <div class="form-label">Middle Name</div>
                <div>{user.middleName}</div>
            </div>
            <div class="col-md-4">
                <div class="form-label">Given Name</div>
                <div>{user.givenName}</div>
            </div>
            <div class="col-md-4">
                <div class="form-label">Passport Type</div>
                <div>{passportTypes[user.passportTypeID]}</div>
            </div>
            <div class="col-md-4">
                <div class="form-label">Passport Number</div>
                <div>{user.passportNumber}</div>
            </div>
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <label for="gender" class="form-label">Gender</label>
                <div hidden="{editing}">{genders[user.genderID]}</div>
                <input name="gender" type="text" list="genders" hidden="{! editing}" disabled="{updating}"
                    maxlength="255" required bind:this="{inputs.gender}"
                    value="{genders[user.genderID]}" placeholder="gender" class={[
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
                <div hidden="{editing}">{user.birthday}</div>
                <input name="birthday" type="date" hidden="{! editing}" disabled="{updating}"
                    max="{maxBirthday}" required bind:this="{inputs.birthday}"
                    value="{user.birthday}" placeholder="birthday" class={[
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
        </form>
    </article>
    <Contacts type="email" contacts={initUser.emails} bind:submitting={submitting} />
    <Contacts type="mobile" contacts={initUser.mobiles} bind:submitting={submitting} />
    {#if initUser.admission_tests.length}
        <article>
            <h3 class="mb-2 fw-bold"><i class="bi bi-clipboard"></i> Admission Test</h3>
            <table class="table table-hover">
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
                            <th>{(new Date(test.testing_at)).toISOString().split('T')[0]}</th>
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
                                <a class="btn btn-primary" href="{route('admission-tests.candidates.show', {'admission_test': test.id})}">Show</a>
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </article>
    {/if}
</section>