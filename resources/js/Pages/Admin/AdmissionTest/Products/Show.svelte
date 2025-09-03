<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Button, Spinner, Input } from '@sveltestrap/sveltestrap';
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { post } from "@/submitForm.svelte";
    import Prices from './Prices.svelte'

    let {product: initProduct} = $props();
    let submitting = $state(false);
    let editing = $state(false);
    let updating = $state(false);
    let product = $state({
        name: initProduct.name,
        optionName: initProduct.option_name,
        minimumAge: initProduct.minimum_age ?? '',
        maximumAge: initProduct.maximum_age ?? '',
        startAt: initProduct.start_at ?? '',
        endAt: initProduct.end_at ?? '',
        quota: initProduct.quota,
    });
    let inputs = $state({});
    let feedbacks = $state({
        name: '',
        optionName: '',
        minimumAge: '',
        maximumAge: '',
        startAt: '',
        endAt: '',
        quota: '',
    });

    function close() {
        editing = false;
        inputs.name.value = product.name;
        inputs.optionName.value = product.optionName;
        inputs.minimumAge.value = product.minimumAge;
        inputs.maximumAge.value = product.maximumAge;
        inputs.startAt.value = product.startAt;
        inputs.endAt.value = product.endAt;
        inputs.quota.value = product.quota;
        for(let key in feedbacks) {
            feedbacks[key] = '';
        }
    }

    function cancel(event) {
        event.preventDefault()
        close();
    }

    function hasError() {
        for(let feedback of Object.entries(feedbacks)) {
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
        if(inputs.name.validity.valueMissing) {
            feedbacks.name = 'The name field is required.';
        } else if(inputs.name.validity.tooLong) {
            feedbacks.name = `The name field must not be greater than ${inputs.name.maxLength} characters.`;
        }
        if(inputs.optionName.validity.valueMissing) {
            feedbacks.optionName = 'The option name field is required.';
        } else if(inputs.optionName.validity.tooLong) {
            feedbacks.optionName = `The option name field must not be greater than ${inputs.optionName.maxLength} characters.`;
        }
        if(inputs.minimumAge.value) {
            if(inputs.minimumAge.validity.rangeUnderflow) {
                feedbacks.minimumAge = `The minimum age field must be at least ${inputs.minimumAge.min}.`;
            } else if(inputs.minimumAge.validity.rangeOverflow) {
                feedbacks.minimumAge = `The minimum age field must not be greater than ${inputs.minimumAge.max}.`;
            } else if(inputs.maximumAge.value && inputs.minimumAge.value >= inputs.maximumAge.value) {
                feedbacks.minimumAge = `The minimum age field must be less than maximum age.`;
            }
        }
        if(inputs.maximumAge.value) {
            if(inputs.maximumAge.validity.rangeUnderflow) {
                feedbacks.maximumAge = `The maximum age field must be at least ${inputs.maximumAge.min}.`;
            } else if(inputs.maximumAge.validity.rangeOverflow) {
                feedbacks.maximumAge = `The maximum age field must not be greater than ${inputs.maximumAge.max}.`;
            } else if(inputs.minimumAge.value >= inputs.maximumAge.value) {
                feedbacks.maximumAge = `The maximum age field must be greater than minimum age.`;
            }
        }
        if(inputs.endAt.value && inputs.startAt.value >= inputs.endAt.value) {
            feedbacks.startAt = `The start at field must be a date before end at field.`;
            feedbacks.endAt = `The end at field must be a date after start at field.`;
        }
        if(inputs.quota.validity.valueMissing) {
            feedbacks.quota = 'The quota field is required.';
        } else if(inputs.quota.validity.rangeUnderflow) {
            feedbacks.quota = `The quota field must be at least ${inputs.quota.min}.`;
        } else if(inputs.quota.validity.rangeOverflow) {
            feedbacks.quota = `The quota field must not be greater than ${inputs.quota.max}.`;
        }
        return hasError();
    }

    function updateSuccessCallback(response) {
        alert(response.data.success);
        product.name = response.data.name;
        product.optionName = response.data.option_name;
        product.minimumAge = response.data.minimum_age ?? '';
        product.maximumAge = response.data.maximum_age ?? '';
        product.startAt = response.data.start_at ?? '';
        product.endAt = response.data.end_at ?? '';
        product.quota = response.data.quota;
        close();
        updating = false;
        submitting = false;
    }

    function updateFailCallback(error) {
        if(error.status == 422) {
            for(let key in error.response.data.errors) {
                let message = error.response.data.errors[key];
                switch(key) {
                    case 'name':
                        feedbacks.name = message;
                        break;
                    case 'option_name':
                        feedbacks.optionName = message;
                        break;
                    case 'minimum_age':
                        feedbacks.minimumAge = message;
                        break;
                    case 'maximum_age':
                        feedbacks.maximumAge = message;
                        break;
                    case 'start_at':
                        feedbacks.startAt = message;
                        break;
                    case 'end_at':
                        feedbacks.endAt = message;
                        break;
                    case 'quota':
                        feedbacks.quota = message;
                        break;
                    default:
                        alert(`Undefine Feedback Key: ${key}\nMessage: ${message}`);
                }
            }
        }
        updating = false;
        submitting = false;
    }

    function update(event) {
        event.preventDefault();
        if (! submitting) {
            let submitAt = Date.now();
            submitting = 'updateProduct'+submitAt;
            if (submitting == 'updateProduct'+submitAt) {
                if(validation()) {
                    updating = true;
                    let data = {
                        name: inputs.name.value,
                        option_name: inputs.optionName.value,
                        quota: inputs.quota.value,
                    };
                    if(inputs.minimumAge.value) {
                        data['minimum_age'] = inputs.minimumAge.value;
                    }
                    if(inputs.maximumAge.value) {
                        data['maximum_age'] = inputs.maximumAge.value;
                    }
                    if(inputs.startAt.value) {
                        data['start_at'] = inputs.startAt.value;
                    }
                    if(inputs.endAt.value) {
                        data['end_at'] = inputs.endAt.value;
                    }
                    post(
                        route(
                            'admin.admission-test.products.update',
                            {product: route().params.product}
                        ),
                        updateSuccessCallback,
                        updateFailCallback,
                        'put', data
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }

    function edit(event) {
        event.preventDefault();
        editing = true;
    }
</script>

<svelte:head>
    <title>Administration Show Admission Test Product | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <article>
            <form onsubmit="{update}" novalidate>
                <h3 class="mb-2 fw-bold">
                    Info
                    <Button color="primary" outline onclick={edit}
                        hidden={editing || updating}>Edit</Button>
                    <Button color="primary" outline
                        hidden={! editing && ! updating}>Save</Button>
                    <Button color="danger" outline onclick={cancel}
                        hidden={! editing && ! updating}>Cancel</Button>
                    <Button color="primary" disabled hidden={! updating}>
                        <Spinner type="border" size="sm" />Saving...
                    </Button>
                </h3>
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Name</th>
                            <td>
                                <span hidden="{editing}">{product.name}</span>
                                <Input name="name" placeholder="name"
                                    maxlength="255" required disabled={updating} hidden={! editing}
                                    feedback={feedbacks.name} valid={feedbacks.name == 'Looks good!'}
                                    invalid={feedbacks.name != '' && feedbacks.name != 'Looks good!'}
                                    bind:inner={inputs.name} value={product.name} />
                            </td>
                        </tr>
                        <tr>
                            <th>Option Name</th>
                            <td>
                                <span hidden="{editing}">{product.optionName}</span>
                                <Input name="option_name" placeholder="option name"
                                    maxlength="255" required disabled={updating} hidden={! editing}
                                    feedback={feedbacks.optionName} valid={feedbacks.optionName == 'Looks good!'}
                                    invalid={feedbacks.optionName != '' && feedbacks.optionName != 'Looks good!'}
                                    bind:inner={inputs.optionName} value={product.optionName} />
                            </td>
                        </tr>
                        <tr>
                            <th>Minimum Age</th>
                            <td>
                                <span hidden="{editing}">{product.minimumAge}</span>
                                <Input type="number" name="minimum_age" placeholder="minimum age"
                                    step="1" min="1" max="255" disabled={updating} hidden={! editing}
                                    feedback={feedbacks.minimumAge} valid={feedbacks.minimumAge == 'Looks good!'}
                                    invalid={feedbacks.minimumAge != '' && feedbacks.minimumAge != 'Looks good!'}
                                    bind:inner={inputs.minimumAge} value={product.minimumAge} />
                            </td>
                        </tr>
                        <tr>
                            <th>Maximum Age</th>
                            <td>
                                <span hidden="{editing}">{product.maximumAge}</span>
                                <Input type="number" name="maximum_age" placeholder="maximum age"
                                    step="1" min="1" max="255" disabled={updating} hidden={! editing}
                                    feedback={feedbacks.maximumAge} valid={feedbacks.maximumAge == 'Looks good!'}
                                    invalid={feedbacks.maximumAge != '' && feedbacks.maximumAge != 'Looks good!'}
                                    bind:inner={inputs.maximumAge} value={product.maximumAge} />
                            </td>
                        </tr>
                        <tr>
                            <th>Start At</th>
                            <td>
                                <span hidden="{editing}">{product.startAt}</span>
                                <Input type="datetime-local" name="start_at" placeholder="start at"
                                    step="1" min="1" max="255" disabled={updating} hidden={! editing}
                                    feedback={feedbacks.startAt} valid={feedbacks.startAt == 'Looks good!'}
                                    invalid={feedbacks.startAt != '' && feedbacks.startAt != 'Looks good!'}
                                    bind:inner={inputs.startAt} value={product.startAt} />
                            </td>
                        </tr>
                        <tr>
                            <th>End At</th>
                            <td>
                                <span hidden="{editing}">{product.endAt}</span>
                                <Input type="datetime-local" name="end_at" placeholder="end at"
                                    step="1" min="1" max="255" disabled={updating} hidden={! editing}
                                    feedback={feedbacks.endAt} valid={feedbacks.endAt == 'Looks good!'}
                                    invalid={feedbacks.endAt != '' && feedbacks.endAt != 'Looks good!'}
                                    bind:inner={inputs.endAt} value={product.endAt} />
                            </td>
                        </tr>
                        <tr>
                            <th>Quota</th>
                            <td>
                                <span  hidden="{editing}">{product.quota}</span>
                                <Input type="number-local" name="quota" placeholder="quota"
                                    step="1" min="1" max="255" required disabled={updating} hidden={! editing}
                                    feedback={feedbacks.quota} valid={feedbacks.quota == 'Looks good!'}
                                    invalid={feedbacks.quota != '' && feedbacks.quota != 'Looks good!'}
                                    bind:inner={inputs.quota} value={product.quota} />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </article>
        <Prices prices={initProduct.prices} bind:submitting={submitting}></Prices>
    </section>
</Layout>
