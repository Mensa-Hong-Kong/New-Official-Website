<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { FormGroup, Input, Row, Col, Button, Spinner, Alert } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { onMount } from "svelte";
    import ClearInputHistory from '@/clearInputHistory.js';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { router } from '@inertiajs/svelte';

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
        router.get(response.request.responseURL);
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

<svelte:head>
    <title>Login | {import.meta.env.VITE_APP_NAME}</title>
    <meta name="title" content="Login | {import.meta.env.VITE_APP_NAME}">
    <meta name="description" content="{import.meta.env.VITE_APP_DESCRIPTION}">
    <meta name="og:description" content="{import.meta.env.VITE_APP_DESCRIPTION}">
    <meta name="og:image" content="og_image.png">
    <meta name="og:url" content="{import.meta.env.VITE_APP_URL}">
    <meta name="og:site_name" content="{import.meta.env.VITE_APP_NAME}">
</svelte:head>

<Layout>
    <section class="container">
        <form class="mx-auto w-25" novalidate onsubmit="{login}">
            <h2 class="mb-2 fw-bold text-uppercase">Login</h2>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Username">
                    <Input name="username" placeholder="username"
                        minlength=7 maxlength=320 required disabled={loggingIn}
                        feedback={feedbacks.username} valid={feedbacks.username == 'Looks good!'}
                        invalid={feedbacks.username != '' && feedbacks.username != 'Looks good!'}
                        bind:inner={inputs.username} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Password">
                    <Input name="password" type="password" placeholder="password"
                        minlength=8 maxlength=16 required disabled={loggingIn}
                        feedback={feedbacks.password} valid={feedbacks.password == 'Looks good!'}
                        invalid={feedbacks.password != '' && feedbacks.password != 'Looks good!'}
                        bind:inner={inputs.password} />
                </FormGroup>
            </div>
            <Row class="mb-4">
                <Col class="d-flex justify-content-center">
                    <Input type="checkbox" name="remember_me" value={true} label="Remember Me"
                        bind:inner={inputs.rememberMe} />
                </Col>
                <Col class="d-flex justify-content-center">
                    <Link href={route('forget-password')}>Forgot password?</Link>
                </Col>
            </Row>
            <Button color="primary" disabled={submitting} class="form-control">
                {#if submitting}
                    <Spinner type="border" size="sm" />Logging in...
                {:else}
                    Login
                {/if}
            </Button>
            <Alert color="danger" hidden={feedbacks.failed == ''}>{feedbacks.failed}</Alert>
            <div class="text-center form-control">
                <p>Not a member? <Link href={route('register')}>Register</Link></p>
            </div>
        </form>
    </section>
</Layout>