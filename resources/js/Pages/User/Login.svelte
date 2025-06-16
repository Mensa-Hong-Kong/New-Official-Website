<script>
    import { onMount } from "svelte";
    import ClearInputHistory from '@/clearInputHistory.js';
    import { post } from "@/submitForm.svelte";

    let inputs = $state({});
    let submitting = $state(false);
    let loggingIn = $state(false);

    onMount(
        () => {
            let clearInputHistory = new ClearInputHistory(inputs);

            return () => {clearInputHistory.destroy()}
        }
    );

    let feedbacks = $state({
        username: '',
        password: '',
        failed: '',
    });

    const inputFeedbackKeys = ['username', 'password'];

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
        if(inputs.username.validity.valueMissing) {
            feedbacks.username = 'The username field is required.';
        } else if(inputs.username.validity.tooShort) {
            feedbacks. username = `The username field must be at least ${inputs.username.minLength} characters.`;
        } else if(inputs.username.validity.tooLong) {
            feedbacks.username = `The username field must not be greater than ${inputs.username.maxLength} characters.`;
        }
        if(inputs.password.validity.valueMissing) {
            feedbacks.password = 'The password field is required.';
        } else if(inputs.password.validity.tooShort) {
            feedbacks.password = `The password field must be at least ${inputs.password.minLength} characters.`;
        } else if(inputs.password.validity.tooLong) {
            feedbacks.password = `The password field must not be greater than ${inputs.password.maxLength} characters.`;
        }

        return !hasError();
    }

    function successCallback(response) {
        submitting = false;
        loggingIn = false;
        window.location.href = response.request.responseURL;
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
        loggingIn = false;
    }

    function login(event) {
        event.preventDefault();
        let submitAt = Date.now();
        submitting = 'login'+submitAt;
        feedbacks.failed = '';
        if (submitting == 'login'+submitAt) {
            if(validation()) {
                loggingIn = true;
                let data = {
                    username: inputs.username.value,
                    password: inputs.password.value,
                }
                if(inputs.rememberMe.checked) {
                    data['remember_me'] = true;
                }
                post(
                    route('login'),
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
    <form class="mx-auto w-25" novalidate onsubmit="{login}">
        <h2 class="mb-2 fw-bold text-uppercase">Login</h2>
        <div class="mb-4 form-outline">
            <div class="form-floating">
                <input name="username" type="text" placeholder="username"
                    minlength="7" maxlength="320" required disabled="{loggingIn}"
                    bind:this={inputs.username} class={[
                        'form-control', {
                            'is-valid': feedbacks.username == 'Looks good!' && feedbacks.failed == '',
                            'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.username) ||  feedbacks.failed != '',
                        }
                    ]} />
                <label for="username">Username</label>
                <div class={[{
                    'valid-feedback': ['', 'Looks good!'].includes(feedbacks.username) && feedbacks.failed == '',
                    'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.username) || feedbacks.failed != '',
                }]}>{feedbacks.username}</div>
            </div>
        </div>
        <div class="mb-4 form-outline">
            <div class="form-floating">
                <input name="password" type="password" placeholder="password"
                    minlength="8" maxlength="16" required disabled="{loggingIn}"
                    bind:this={inputs.password} class={[
                        'form-control', {
                            'is-valid': feedbacks.password == 'Looks good!',
                            'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.password),
                        }
                    ]} />
                <label for="validationPassword">Password</label>
                <div class={[{
                    'valid-feedback': ['', 'Looks good!'].includes(feedbacks.password),
                    'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.password),
                }]}>{feedbacks.password}</div>
            </div>
        </div>
        <div class="mb-4 row">
            <div class="col d-flex justify-content-center">
                <div class="form-check">
                    <input name="remember_me" type="checkbox" value="true" disabled="{loggingIn}"
                       id="rememberMe" bind:this={inputs.rememberMe} class="form-check-input" />
                    <label class="form-check-label" for="rememberMe">Remember Me</label>
                </div>
            </div>
            <div class="col d-flex justify-content-center">
                <a href="{route('forget-password')}">Forgot password?</a>
            </div>
        </div>
        <input type="submit" class="form-control btn btn-primary"
            value="Login" hidden="{submitting}" />
        <div class="alert alert-danger" role="alert" hidden="{feedbacks.failed == ''}">
            {feedbacks.failed}
        </div>
        <button class="form-control btn btn-primary" type="button" disabled hidden="{! submitting}">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Logging In...
        </button>
        <div class="text-center form-control">
            <p>Not a member? <a href="{route('register')}">Register</a></p>
        </div>
    </form>
</section>