<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { FormGroup, Input, Button, Spinner } from '@sveltestrap/sveltestrap';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { router } from '@inertiajs/svelte';

    let inputs = $state({});
    let feedbacks = $state({
        name: '',
        optionName: '',
        minimumAge: '',
        maximumAge: '',
        startAt: '',
        endAt: '',
        quota: '',
        priceName: '',
        price: '',
    });
    let submitting = $state(false);
    let creating = $state(false);
    
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
        if(inputs.name.validity.valueMissing) {
            feedbacks.name = 'The name field is required.';
        } else if(inputs.name.validity.tooLong) {
            feedbacks.name = `The name field must not be greater than ${inputs.name.maxLength} characters.`;
        }
        if(inputs.optionName.validity.valueMissing) {
            feedbacks.optionName = 'The option name field is required.';
        } else if(inputs.optionName.validity.tooLong) {
            feedbacks.optionName = 'The option name field must not be greater than 255 characters.';
        }
        if(inputs.minimumAge.value) {
            if(inputs.minimumAge.validity.rangeUnderflow) {
                feedbacks.minimumAge = `The minimum age field must be at least ${inputs.minimumAge.min}.`;
            } else if(inputs.minimumAge.validity.rangeOverflow) {
                feedbacks.minimumAge = `The minimum age field must not be greater than ${inputs.minimumAge.max}.`;
            }
        }
        if(inputs.maximumAge.value) {
            if(inputs.maximumAge.validity.rangeUnderflow) {
                feedbacks.maximumAge = `The maximum age field must be at least ${inputs.maximumAge.min}.`;
            } else if(inputs.maximumAge.validity.rangeOverflow) {
                feedbacks.maximumAge = `The maximum age field must not be greater than ${inputs.maximumAge.max}.`;
            } else if(inputs.minimumAge.value >= inputs.maximumAge.value) {
                feedbacks.minimumAge = `The minimum age field must be less than maximum age.`;
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
        if(inputs.priceName.validity.valueMissing) {
            feedbacks.priceName = 'The price name field is required.';
        } else if(inputs.priceName.validity.tooLong) {
            feedbacks.priceName = 'The price name field must not be greater than 255 characters.';
        }
        if(inputs.price.validity.valueMissing) {
            feedbacks.price = 'The price field is required.';
        } else if(inputs.price.validity.rangeUnderflow) {
            feedbacks.price = `The price field must be at least ${inputs.price.min}.`;
        } else if(inputs.price.validity.rangeOverflow) {
            feedbacks.price = `The price field must not be greater than ${inputs.price.max}.`;
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
                let value = error.response.data.errors[key];
                switch(key) {
                    case 'name':
                        feedbacks.name = value;
                        break;
                    case 'option_name':
                        feedbacks.optionName = value;
                        break;
                    case 'minimum_age':
                        feedbacks.minimumAge = value;
                        break;
                    case 'maximum_age':
                        feedbacks.maximumAge = value;
                        break;
                    case 'start_at':
                        feedbacks.startAt = value;
                        break;
                    case 'end_at':
                        feedbacks.endAt = value;
                        break;
                    case 'quota':
                        feedbacks.quota = value;
                        break;
                    case 'price_name':
                        feedbacks.priceName = value;
                        break;
                    case 'price':
                        feedbacks.price = value;
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
                        name: inputs.name.value,
                        option_name: inputs.optionName.value,
                        quota: inputs.quota.value,
                        price: inputs.price.value
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
                    if(inputs.priceName.value) {
                        data['price_name'] = inputs.priceName.value;
                    }
                    post(
                        route('admin.admission-test.products.store'),
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
    <title>Create Admission Test Product | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <form id="form" method="POST" novalidate onsubmit={create}>
            <h2 class="mb-2 fw-bold text-uppercase">Create Admission Test Product</h2>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Name">
                    <Input name="name" placeholder="name"
                        maxlength="255" required  disabled={creating}
                        feedback={feedbacks.name} valid={feedbacks.name == 'Looks good!'}
                        invalid={feedbacks.name != '' && feedbacks.name != 'Looks good!'}
                        bind:inner={inputs.name} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Option Name">
                    <Input name="option_name" placeholder="option name"
                        maxlength="255" required disabled={creating}
                        feedback={feedbacks.optionName} valid={feedbacks.optionName == 'Looks good!'}
                        invalid={feedbacks.optionName != '' && feedbacks.optionName != 'Looks good!'}
                        bind:inner={inputs.optionName} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Minimum Age">
                    <Input type="number" name="minimum_age" placeholder="minimum age"
                        step="1" min="1" max="255" disabled={creating}
                        feedback={feedbacks.minimumAge} valid={feedbacks.minimumAge == 'Looks good!'}
                        invalid={feedbacks.minimumAge != '' && feedbacks.minimumAge != 'Looks good!'}
                        bind:inner={inputs.minimumAge} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Maximum Age">
                    <Input type="number" name="maximum_age" placeholder="maximum age"
                        step="1" min="1" max="255" disabled={creating}
                        feedback={feedbacks.maximumAge} valid={feedbacks.maximumAge == 'Looks good!'}
                        invalid={feedbacks.maximumAge != '' && feedbacks.maximumAge != 'Looks good!'}
                        bind:inner={inputs.maximumAge} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Start At">
                    <Input type="datetime-local" name="start_at" placeholder="start at" disabled={creating}
                        feedback={feedbacks.startAt} valid={feedbacks.startAt == 'Looks good!'}
                        invalid={feedbacks.startAt != '' && feedbacks.startAt != 'Looks good!'}
                        bind:inner={inputs.startAt} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="End At">
                    <Input type="datetime-local" name="end_at" placeholder="end at" disabled={creating}
                        feedback={feedbacks.endAt} valid={feedbacks.endAt == 'Looks good!'}
                        invalid={feedbacks.endAt != '' && feedbacks.endAt != 'Looks good!'}
                        bind:inner={inputs.endAt} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Quota">
                    <Input type="number-local" name="quota" placeholder="quota"
                        step="1" min="1" max="255" required disabled={creating}
                        feedback={feedbacks.quota} valid={feedbacks.quota == 'Looks good!'}
                        invalid={feedbacks.quota != '' && feedbacks.quota != 'Looks good!'}
                        bind:inner={inputs.quota} />
                </FormGroup>
            </div>
            <div class="mb-4 form-outline">
                <div class="form-floating">
                    <FormGroup floating label="Price Name">
                        <Input name="price_name" placeholder="price name"
                            maxlength="255" disabled={creating}
                            feedback={feedbacks.priceName} valid={feedbacks.priceName == 'Looks good!'}
                            invalid={feedbacks.priceName != '' && feedbacks.priceName != 'Looks good!'}
                            bind:inner={inputs.priceName} />
                    </FormGroup>
                </div>
            </div>
            <div class="mb-4 form-outline">
                <FormGroup floating label="Price">
                    <Input type="number" name="price" placeholder="price"
                        step="1" min="1" max="65535" required disabled={creating}
                        feedback={feedbacks.price} valid={feedbacks.price == 'Looks good!'}
                        invalid={feedbacks.price != '' && feedbacks.price != 'Looks good!'}
                        bind:inner={inputs.price} />
                </FormGroup>
            </div>
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