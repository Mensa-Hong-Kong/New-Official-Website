<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { FormGroup, Input, Label, Row, Col, Button, Spinner } from '@sveltestrap/sveltestrap';
    import { onMount } from 'svelte';
    import { post } from "@/submitForm.svelte";
    import { router } from '@inertiajs/svelte';
    import { formatToDate, formatToTime, formatToDatetime } from '@/timeZoneDatetime';

    let { paymentGateways, tests } = $props();
    let inputs = $state({});
    let feedbacks = $state({
        userID: '',
        productName: '',
        priceName: '',
        price: '',
        quota: '',
        status: '',
        expiredAt: '',
        paymentGateway: '',
        referenceNumber: '',
        test: '',
    });
    let submitting = $state(false);
    let creating = $state(false);
    let statusValue = $state('');
    let testValue = $state('');
    let minExpiredAt = $state();
    let maxExpiredAt = $state();
    let expiredAtValue = $state('');

    function updateExpiredTimeRange() {
        minExpiredAt = formatToDatetime((new Date).addMinutes(5)).slice(0, -3);
        maxExpiredAt = formatToDatetime((new Date).addDay()).slice(0, -3);
        if(expiredAtValue < minExpiredAt) {
            expiredAtValue = minExpiredAt;
        }
    }
    updateExpiredTimeRange();
    setTimeout(
        () => {
            setInterval(updateExpiredTimeRange, 60000);
            updateExpiredTimeRange();
        },
        (new Date).addMinute().startOfMinute() - (new Date)
    );
    
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
        if(inputs.userID.validity.valueMissing) {
            feedbacks.userID = 'The user id field is required.';
        } else if(inputs.userID.validity.patternMismatch) {
            feedbacks.userID = 'The user id field must be an integer.';
        }
        if(inputs.productName.value && inputs.productName.validity.tooLong) {
            feedbacks.productName = `The product name field must not be greater than ${inputs.name.maxLength} characters.`;
        }
        if(inputs.priceName.value && inputs.priceName.validity.tooLong) {
            feedbacks.priceName = 'The price name field must not be greater than 255 characters.';
        }
        if(inputs.price.validity.valueMissing) {
            feedbacks.price = 'The price field is required.';
        } else if(inputs.price.validity.rangeUnderflow) {
            feedbacks.price = `The price field must be at least ${inputs.price.min}.`;
        } else if(inputs.price.validity.rangeOverflow) {
            feedbacks.price = `The price field must not be greater than ${inputs.price.max}.`;
        }
        if(inputs.quota.validity.valueMissing) {
            feedbacks.quota = 'The quota field is required.';
        } else if(inputs.quota.validity.rangeUnderflow) {
            feedbacks.quota = `The quota field must be at least ${inputs.quota.min}.`;
        } else if(inputs.quota.validity.rangeOverflow) {
            feedbacks.quota = `The quota field must not be greater than ${inputs.quota.max}.`;
        }
        if(inputs.status.validity.valueMissing) {
            feedbacks.status = 'The quota field is required.';
        }
        if(statusValue == 'pending') {
            if(inputs.expiredAt.valueMissing) {
                feedbacks.status = 'The expired at field is required.';
            } else if(inputs.expiredAt.validity.rangeUnderflow) {
                feedbacks.quota = `The expired at field must be a date after or equal to 5 minutes.`;
            } else if(inputs.expiredAt.validity.rangeOverflow) {
                feedbacks.quota = `The expired at field must be a date before or equal to 24 hours.`;
            }
        }
        if(inputs.referenceNumber.value && inputs.referenceNumber.tooLong) {
            feedbacks.priceName = 'The reference number field must not be greater than 255 characters.';
        }
        return ! hasError();
    }

    function successCallback(response) {
        creating = false;
        submitting = false;
        router.get(response.request.responseURL);
    }

    function failCallback(error) {
        if(error.status == 422) {
            for(let key in error.response.data.errors) {
                let message = error.response.data.errors[key];
                switch(key) {
                    case 'user_id':
                        feedbacks.userID = message;
                        break;
                    case 'product_name':
                        feedbacks.productName = message;
                        break;
                    case 'price_name':
                        feedbacks.priceName = message;
                        break;
                    case 'price':
                        feedbacks.price = message;
                        break;
                    case 'quota':
                        feedbacks.quota = message;
                        break;
                    case 'payment_gateway_id':
                        feedbacks.paymentGateway = message;
                        break;
                    case 'reference_number':
                        feedbacks.referenceNumber = message;
                        break;
                    case 'test_id':
                        alert(value);
                        break;
                    default:
                        alert(`Undefine Feedback Key: ${key}\nMessage: ${message}`);
                        break;
                }
            }
        }
        creating = false;
        submitting = false;
    }
    
    function create(event) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'create'+submitAt;
            if(submitting == 'create'+submitAt) {
                if(validation()) {
                    creating = true;
                    let data = {
                        user_id: inputs.userID.value,
                        price: inputs.price.value,
                        quota: inputs.quota.value,
                        status: statusValue,
                        payment_gateway_id: inputs.paymentGateway.value,
                    };
                    if(inputs.productName.value) {
                        data['product_name'] = inputs.productName.value;
                    }
                    if(inputs.priceName.value) {
                        data['price_name'] = inputs.priceName.value;
                    }
                    if(statusValue == 'pending') {
                        data['expired_at'] = inputs.expiredAt.value;
                    }
                    if(inputs.referenceNumber.value) {
                        data['reference_number'] = inputs.referenceNumber.value;
                    }
                    if(tests.length && testValue) {
                        date['test_id'] = testValue;
                    }
                    post(
                        route('admin.admission-test.orders.store'),
                        successCallback,
                        failCallback,
                        'post', data
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }
</script>

<svelte:head>
    <title>Create Admission Test Order | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <form id="form" method="POST" novalidate onsubmit={create}>
            <h2 class="mb-2 fw-bold text-uppercase">Create Admission Test Order</h2>
            <div class="mb-4 form-outline">
                <FormGroup floating label="User ID">
                    <Input name="user_id" placeholder="product name"
                        patten="^\+?[1-9][0-9]*" required  disabled={creating}
                        feedback={feedbacks.userID} valid={feedbacks.userID == 'Looks good!'}
                        invalid={feedbacks.userID != '' && feedbacks.userID != 'Looks good!'}
                        bind:inner={inputs.userID} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Product Name">
                    <Input name="product_name" placeholder="product name"
                        maxlength=255 disabled={creating}
                        feedback={feedbacks.productName} valid={feedbacks.productName == 'Looks good!'}
                        invalid={feedbacks.productName != '' && feedbacks.productName != 'Looks good!'}
                        bind:inner={inputs.productName} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <div class="form-floating">
                    <FormGroup floating label="Price Name">
                        <Input name="price_name" placeholder="price name"
                            maxlength=255 disabled={creating}
                            feedback={feedbacks.priceName} valid={feedbacks.priceName == 'Looks good!'}
                            invalid={feedbacks.priceName != '' && feedbacks.priceName != 'Looks good!'}
                            bind:inner={inputs.priceName} />
                    </FormGroup>
                </div>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Price">
                    <Input type="number" name="price" placeholder="price"
                        step=1 min=1 max=65535 required disabled={creating}
                        feedback={feedbacks.price} valid={feedbacks.price == 'Looks good!'}
                        invalid={feedbacks.price != '' && feedbacks.price != 'Looks good!'}
                        bind:inner={inputs.price} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Quota">
                    <Input type="number" name="quota" placeholder="quota"
                        step=1 min=1 max=255 required disabled={creating}
                        feedback={feedbacks.quota} valid={feedbacks.quota == 'Looks good!'}
                        invalid={feedbacks.quota != '' && feedbacks.quota != 'Looks good!'}
                        bind:inner={inputs.quota} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Status">
                    <Input type="select" name="status" required disabled={creating}
                        feedback={feedbacks.status} valid={feedbacks.status == 'Looks good!'}
                        invalid={feedbacks.status != '' && feedbacks.status != 'Looks good!'}
                        bind:inner={inputs.status} bind:value={statusValue}>
                        <option value="" disabled>Please select status</option>
                        <option value="pending">Pending</option>
                        <option value="succeeded">Succeeded</option>
                    </Input>
                </FormGroup>
            </div>
            <div class="mb-4 form-outline" hidden={statusValue != 'pending'}>
                <FormGroup floating label="Expired At">
                    <Input type="datetime-local" name="expired_at" placeholder="expired at"
                        required min={minExpiredAt} max={maxExpiredAt} disabled={statusValue != 'pending' || creating}
                        feedback={feedbacks.expiredAt} valid={feedbacks.expiredAt == 'Looks good!'}
                        invalid={feedbacks.expiredAt != '' && feedbacks.expiredAt != 'Looks good!'}
                        bind:inner={inputs.expiredAt} bind:value={expiredAtValue} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Payment Gateway">
                    <Input type="select" name="payment_gateway_id" required disabled={creating}
                        feedback={feedbacks.paymentGateway} valid={feedbacks.paymentGateway == 'Looks good!'}
                        invalid={feedbacks.paymentGateway != '' && feedbacks.paymentGateway != 'Looks good!'}
                        bind:inner={inputs.paymentGateway}>
                        <option value="" selected disabled>Please select payment gateway</option>
                        {#each Object.entries(paymentGateways) as [id, name]}
                            <option value={id}>{name}</option>
                        {/each}
                    </Input>
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Reference Number">
                    <Input name="reference_number" placeholder="product name"
                        maxlength=255 disabled={creating}
                        feedback={feedbacks.referenceNumber} valid={feedbacks.referenceNumber == 'Looks good!'}
                        invalid={feedbacks.referenceNumber != '' && feedbacks.referenceNumber != 'Looks good!'}
                        bind:inner={inputs.referenceNumber} />
                </FormGroup>
            </div>
            {#if tests.length}
                <div class='mb-3 g-3 form-outline'>
                    <Label>Test</Label>
                    <Row class="g-4">
                        <Col md=3>
                            <input type="radio" name="test_id" id="test"
                                value="" class="radio-input d-none" bind:group={testValue} />
                            <label class="p-4 d-flex justify-content-center align-items-center radio-card card h-100" for="test">
                                <div class="text-center">
                                    <h5 class="card-title">Skip</h5>
                                </div>
                            </label>
                        </Col>
                        {#each tests as test}
                            <Col md=3>
                                <input type="radio" name="test_id" id="test{test.id}"
                                    value={test.id} class="radio-input d-none" bind:group={testValue} />
                                <label class="p-4 radio-card card h-100" for="test{test.id}">
                                    <div class="p-0 card-body">
                                        <ul class="list-unstyled">
                                            <li class="mb-2">Date: {formatToDate(test.testing_at)}</li>
                                            <li class="mb-2">Time: {formatToTime(test.testing_at).slice(0, -3)}</li>
                                            <li class="mb-2">Location: {test.location.name}</li>
                                            <li class="mb-2">Candidate: {test.candidates_count}/{test.maximum_candidates}</li>
                                            <li class="mb-2">Visibility: {test.is_public ? 'Public' : 'Private'}</li>
                                        </ul>
                                    </div>
                                </label>
                            </Col>
                        {/each}
                    </Row>
                </div>
            {/if}
            <Button color="success" class="form-control" disabled={submitting}>
                {#if creating}
                    <Spinner type="border" size="sm" />Creating...
                {:else}
                    Create
                {/if}
            </Button>
        </form>
    </section>
</Layout>

<style>
    .radio-card {
        cursor: pointer;
        border: 2px solid #dee2e6;
        transition: all 0.3s ease;
    }
    
    .radio-card:hover {
        border-color: #0d6efd;
    }
    
    .radio-input:checked + .radio-card {
        border-color: #0d6efd;
        background-color: #f8f9ff;
    }
</style>