<script>
    import { post } from "@/submitForm";

    import {submitting} from "@/submitting.svelte.js";

    import Prices from './Prices.svelte'

    let {product: initProduct} = $props();

    const product = $state({
        name: initProduct.name,
        optionName: initProduct.option_name,
        minimumAge: initProduct.minimum_age ?? '',
        maximumAge: initProduct.maximum_age ?? '',
        startAt: initProduct.start_at ?? '',
        endAt: initProduct.end_at ?? '',
        quota: initProduct.quota,
        editing: false,
        updating: false,
    });

    let name, optionName, minimumAge, maximumAge, startAt, endAt, quota;

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
        product.editing = false;
        name.value = product.name;
        optionName.value = product.optionName;
        minimumAge.value = product.minimumAge;
        maximumAge.value = product.maximumAge;
        startAt.value = product.startAt;
        endAt.value = product.endAt;
        quota.value = product.quota;
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
        if(name.validity.valueMissing) {
            feedbacks.name = 'The name field is required.';
        } else if(name.validity.tooLong) {
            feedbacks.name = `The name field must not be greater than ${name.maxLength} characters.`;
        }
        if(optionName.validity.valueMissing) {
            feedbacks.optionName = 'The option name field is required.';
        } else if(optionName.validity.tooLong) {
            feedbacks.optionName = `The option name field must not be greater than ${optionName.maxLength} characters.`;
        }
        if(minimumAge.value) {
            if(minimumAge.validity.rangeUnderflow) {
                feedbacks.minimumAge = `The minimum age field must be at least ${minimumAge.min}.`;
            } else if(minimumAge.validity.rangeOverflow) {
                feedbacks.minimumAge = `The minimum age field must not be greater than ${minimumAge.max}.`;
            } else if(maximumAge.value && minimumAge.value >= maximumAge.value) {
                feedbacks.minimumAge = `The minimum age field must be less than maximum age.`;
            }
        }
        if(maximumAge.value) {
            if(maximumAge.validity.rangeUnderflow) {
                feedbacks.maximumAge = `The maximum age field must be at least ${maximumAge.min}.`;
            } else if(maximumAge.validity.rangeOverflow) {
                feedbacks.maximumAge = `The maximum age field must not be greater than ${maximumAge.max}.`;
            } else if(minimumAge.value >= maximumAge.value) {
                feedbacks.maximumAge = `The maximum age field must be greater than minimum age.`;
            }
        }
        if(endAt.value && startAt.value >= endAt.value) {
            feedbacks.startAt = `The start at field must be a date before end at field.`;
            feedbacks.endAt = `The end at field must be a date after start at field.`;
        }
        if(quota.validity.valueMissing) {
            feedbacks.quota = 'The quota field is required.';
        } else if(quota.validity.rangeUnderflow) {
            feedbacks.quota = `The quota field must be at least ${quota.min}.`;
        } else if(quota.validity.rangeOverflow) {
            feedbacks.quota = `The quota field must not be greater than ${quota.max}.`;
        }
        return hasError();
    }

    function updateSuccessCallback(response) {
        bootstrapAlert(response.data.success);
        product.name = response.data.name;
        product.optionName = response.data.option_name;
        product.minimumAge = response.data.minimum_age ?? '';
        product.maximumAge = response.data.maximum_age ?? '';
        product.startAt = response.data.start_at ?? '';
        product.endAt = response.data.end_at ?? '';
        product.quota = response.data.quota;
        close();
        product.updating = false;
        submitting.set = false;
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
        product.updating = false;
        submitting.set = false;
    }

    function update(event) {
        event.preventDefault();
        if (submitting.get == '') {
            let submitAt = Date.now();
            submitting.set = 'updateProduct'+submitAt;
            if (submitting.get == 'updateProduct'+submitAt) {
                if(validation()) {
                    product.updating = true;
                    let data = {
                        name: name.value,
                        option_name: optionName.value,
                        quota: quota.value,
                    };
                    if(minimumAge.value) {
                        data['minimum_age'] = minimumAge.value;
                    }
                    if(maximumAge.value) {
                        data['maximum_age'] = maximumAge.value;
                    }
                    if(startAt.value) {
                        data['start_at'] = startAt.value;
                    }
                    if(endAt.value) {
                        data['end_at'] = endAt.value;
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
                    submitting.set = false;
                }
            }
        }
    }

    function edit(event) {
        event.preventDefault();
        product.editing = true;
    }
</script>

<article>
    <form onsubmit="{update}" novalidate>
        <h3 class="fw-bold mb-2">
            Info
            <button class="btn btn-outline-primary" onclick="{edit}"
                hidden="{product.editing || product.updating}">Edit</button>
            <button class="btn btn-outline-primary" disabled="{submitting.get}"
                hidden="{! product.editing && ! product.updating}">Save</button>
            <button class="btn btn-outline-danger" onclick="{cancel}"
                hidden="{! product.editing && ! product.updating}">Cancel</button>
            <button class="btn btn-primary"disabled hidden="{! product.updating}">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Saving...
            </button>
        </h3>
        <table class="table">
            <tbody>
                <tr>
                    <th>Name</th>
                    <td>
                        <span hidden="{product.editing}">{product.name}</span>
                        <input bind:this={name} name="name" placeholder="name"
                            maxlength="255" value="{product.name}" required
                            hidden="{! product.editing}" disabled="{product.updating}" class={[
                                'form-control', {
                                    'is-valid': feedbacks.name == 'Looks good!',
                                    'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.name),
                                }
                            ]}/>
                        <div class={[{
                            'valid-feedback': ['', 'Looks good!'].includes(feedbacks.name),
                            'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.name),
                        }]}>{feedbacks.name}</div>
                    </td>
                </tr>
                <tr>
                    <th>Option Name</th>
                    <td>
                        <span hidden="{product.editing}">{product.optionName}</span>
                        <input bind:this={optionName} name="option_name" placeholder="name"
                            maxlength="255" value="{product.optionName}" required
                            hidden="{! product.editing}" disabled="{product.updating}" class={[
                                'form-control', {
                                    'is-valid': feedbacks.optionName == 'Looks good!',
                                    'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.optionName),
                                }
                            ]}/>
                        <div class={[{
                            'valid-feedback': ['', 'Looks good!'].includes(feedbacks.optionName),
                            'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.optionName),
                        }]}>{feedbacks.optionName}</div>
                    </td>
                </tr>
                <tr>
                    <th>Minimum Age</th>
                    <td>
                        <span hidden="{product.editing}">{product.minimumAge}</span>
                        <input bind:this={minimumAge} type="number" name="minimum_age" placeholder="minimum age"
                            step="1" min="1" max="255" value="{product.minimumAge}"
                            hidden="{! product.editing}" disabled="{product.updating}" class={[
                                'form-control', {
                                    'is-valid': feedbacks.minimumAge == 'Looks good!',
                                    'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.minimumAge),
                                }
                            ]}/>
                        <div class={[{
                            'valid-feedback': ['', 'Looks good!'].includes(feedbacks.minimumAge),
                            'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.minimumAge),
                        }]}>{feedbacks.minimumAge}</div>
                    </td>
                </tr>
                <tr>
                    <th>Maximum Age</th>
                    <td>
                        <span hidden="{product.editing}">{product.maximumAge}</span>
                        <input bind:this={maximumAge} type="number" name="maximum_age"
                            placeholder="maximum age" value="{product.maximumAge}"
                            hidden="{! product.editing}" disabled="{product.updating}" class={[
                                'form-control', {
                                    'is-valid': feedbacks.maximumAge == 'Looks good!',
                                    'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.maximumAge),
                                }
                            ]} step="1" min="1" max="255" />
                        <div class={[{
                            'valid-feedback': ['', 'Looks good!'].includes(feedbacks.maximumAge),
                            'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.maximumAge),
                        }]}>{feedbacks.maximumAge}</div>
                    </td>
                </tr>
                <tr>
                    <th>Start At</th>
                    <td>
                        <span hidden="{product.editing}">{product.startAt}</span>
                        <input bind:this={startAt} type="datetime-local" name="start_at"
                            placeholder="start at" value="{product.startAt}"
                            hidden="{! product.editing}" disabled="{product.updating}" class={[
                                'form-control', {
                                    'is-valid': feedbacks.startAt == 'Looks good!',
                                    'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.startAt),
                                }
                            ]}/>
                        <div class={[{
                            'valid-feedback': ['', 'Looks good!'].includes(feedbacks.startAt),
                            'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.startAt),
                        }]}>{feedbacks.startAt}</div>
                    </td>
                </tr>
                <tr>
                    <th>End At</th>
                    <td>
                        <span hidden="{product.editing}">{product.endAt}</span>
                        <input bind:this={endAt} type="datetime-local" name="start_at"
                            placeholder="start at" value="{product.endAt}"
                            hidden="{! product.editing}" disabled="{product.updating}" class={[
                                'form-control', {
                                    'is-valid': feedbacks.name == 'Looks good!',
                                    'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.name),
                                }
                            ]}/>
                        <div class={[{
                            'valid-feedback': ['', 'Looks good!'].includes(feedbacks.endAt),
                            'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.endAt),
                        }]}>{feedbacks.endAt}</div>
                    </td>
                </tr>
                <tr>
                    <th>Quota</th>
                    <td>
                        <span  hidden="{product.editing}">{product.quota}</span>
                        <input bind:this={quota} type="number" name="quota"
                            placeholder="quota" value="{product.quota}"
                            hidden="{! product.editing}" disabled="{product.updating}" class={[
                                'form-control', {
                                    'is-valid': feedbacks.quota == 'Looks good!',
                                    'is-invalid': ! ['', 'Looks good!'].includes(feedbacks.quota),
                                }
                            ]} step="1" min="1" max="255" required />
                        <div class={[{
                            'valid-feedback': ['', 'Looks good!'].includes(feedbacks.quota),
                            'invalid-feedback': ! ['', 'Looks good!'].includes(feedbacks.quota),
                        }]}>{feedbacks.quota}</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</article>
<Prices prices={initProduct.prices}></Prices>
