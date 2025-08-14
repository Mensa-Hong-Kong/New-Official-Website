<script>
    import { FormGroup, Input } from '@sveltestrap/sveltestrap';
    import MasterOptions from './MasterOptions.svelte';

    let {
        inputs = $bindable(), feedbacks = $bindable(), submitting,
        items, displayOptions, item = {},
    } = $props();
    let masterValue = $state();

    function hasError() {
        for(let [key, feedback] of Object.entries(feedbacks)) {
            if(feedback != 'Looks good!') {
                return true;
            }
        }
        return false;
    }

    export function validation() {
        for(let key in feedbacks) {
            feedbacks[key] = 'Looks good!';
        }
        if(inputs.master.validity.valueMissing) {
            feedbacks.master = 'The master field is required.';
        }
        if(inputs.name.validity.valueMissing) {
            feedbacks.name = 'The name field is required.';
        } else if(inputs.name.validity.tooLong) {
            feedbacks.name = `The name field must not be greater than ${inputs.name.maxLength} characters.`;
        }
        if(inputs.url.value) {
            if(inputs.url.validity.tooLong) {
                feedbacks.url = `The url field must not be greater than ${inputs.url.maxLength} characters.`;
            } else if(inputs.url.validity.typeMismatch) {
                feedbacks.url = 'The url field is not a valid URL.';
            }
        }
        if(inputs.displayOrder.validity.valueMissing) {
            feedbacks.displayOrder = 'The display order field is required.';
        }
        return ! hasError();
    }
</script>

<div class="mb-3 row g-3 form-outline">
    <FormGroup floating label="Master">
        <Input type="select" name="master_id" required disabled={submitting}
            feedback={feedbacks.master} valid={feedbacks.master == 'Looks good!'}
            invalid={feedbacks.master != '' && feedbacks.master != 'Looks good!'}
            bind:inner={inputs.master} bind:value={masterValue}>
            <option value="" selected={! item.master_id} disabled>Please display order master</option>
            <option value="0" selected={item.master_id === null}>root</option>
            <MasterOptions items={items} selected={item.master_id} ignore={item.id} />
        </Input>
    </FormGroup>
</div>
<div class="mb-3 row g-3 form-outline">
    <FormGroup floating label="Name">
        <Input name="name" placeholder="name..." disabled={submitting}
            maxlength="255" required value={item.name}
            feedback={feedbacks.name} valid={feedbacks.name == 'Looks good!'}
            invalid={feedbacks.name != '' && feedbacks.name != 'Looks good!'}
            bind:inner={inputs.name} />
    </FormGroup>
</div>
<div class="mb-3 row g-3 form-outline">
    <FormGroup floating label="URL">
        <Input type="url" name="url" disabled={submitting}
            placeholder="https://google.com" maxlength=8000 value={item.url}
            feedback={feedbacks.url} valid={feedbacks.url == 'Looks good!'}
            invalid={feedbacks.url != '' && feedbacks.url != 'Looks good!'}
            bind:inner={inputs.url} />
    </FormGroup>
</div>
<div class="mb-3 row g-3 form-outline">
    <FormGroup floating label="Display Order">
        <Input type="select" name="display_order" required disabled={submitting || ! masterValue}
            feedback={feedbacks.displayOrder} valid={feedbacks.displayOrder == 'Looks good!'}
            invalid={feedbacks.displayOrder != '' && feedbacks.displayOrder != 'Looks good!'}
            bind:inner={inputs.displayOrder}>
            <option value="" selected={item.master_id === undefined} disabled>Please display order</option>
            {#each Object.entries(displayOptions) as [masterID, object]}
                {#each Object.entries(object) as [key, value]}
                    <option hidden={masterID != masterValue} value={key}>{value}</option>
                {/each}
            {/each}
        </Input>
    </FormGroup>
</div>
