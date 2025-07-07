<script>
    import { FormGroup, Input } from '@sveltestrap/sveltestrap';
    let {
        inputs = $bindable(), feedbacks = $bindable(), submitting,
        displayOptions, type = {}
    } = $props();

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
        if(inputs.name.validity.valueMissing) {
            feedbacks.name = 'The name field is required.';
        } else if(inputs.name.validity.tooLong) {
            feedbacks.name = `The name field must not be greater than ${inputs.name.maxlength} characters.`;
        }
        if(inputs.intervalMonth.validity.valueMissing) {
            feedbacks.intervalMonth = 'The interval month field is required.';
        } else if(inputs.intervalMonth.validity.rangeOverflow) {
            feedbacks.intervalMonth = `The interval month field must be at least ${inputs.intervalMonth.min}.`;
        } else if(inputs.intervalMonth.validity.range) {
            feedbacks.intervalMonth = `The interval month field must be at least ${inputs.intervalMonth.max}.`;
        }
        if(inputs.displayOrder.validity.valueMissing) {
            feedbacks.displayOrder = 'The display order field is required.';
        }
        return ! hasError();
    }
</script>

<div class="mb-4 form-outline">
    <FormGroup floating label="Name">
        <Input name="name" placeholder="name" disabled={submitting}
            maxlength="255" required
            feedback={feedbacks.name} valid={feedbacks.name == 'Looks good!'}
            invalid={feedbacks.name != '' && feedbacks.name != 'Looks good!'}
            bind:inner={inputs.name} value={type.name} />
    </FormGroup>
</div>
<div class="mb-4 form-outline">
    <FormGroup floating label="Interval Month">
        <Input type="number" name="interval_month" placeholder="interval month"
            min="0" step="1" max="60" required disabled={submitting}
            feedback={feedbacks.intervalMonth} valid={feedbacks.intervalMonth == 'Looks good!'}
            invalid={feedbacks.intervalMonth != '' && feedbacks.intervalMonth != 'Looks good!'}
            bind:inner={inputs.intervalMonth} value={type.interval_month} />
    </FormGroup>
</div>
<div class="mb-4 form-outline">
    <Input type="switch" name="is_active" label="Is Active"
        value={true} disabled={submitting} bind:inner={inputs.isActive} checked={type.is_active} />
</div>
<div class="mb-4 form-outline">
    <FormGroup floating label="Display Order">
        <Input type="select" name="display_order" required disabled={submitting}
            feedback={feedbacks.displayOrder} valid={feedbacks.displayOrder == 'Looks good!'}
            invalid={feedbacks.displayOrder != '' && feedbacks.displayOrder != 'Looks good!'}
            bind:inner={inputs.displayOrder}>
            <option value="" selected={! type.display_order} disabled>Please select display order</option>
            {#each Object.entries(displayOptions) as [key, value]}
                <option value="{key}" selected={key == type.display_order}>{value}</option>
            {/each}
        </Input>
    </FormGroup>
</div>
