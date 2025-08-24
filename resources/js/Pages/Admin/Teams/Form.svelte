<script>
    import { FormGroup, Input } from '@sveltestrap/sveltestrap';
    let {
        inputs = $bindable(), feedbacks = $bindable(), submitting,
        types, displayOptions, team={}
    } = $props();
    let typeValue = $state();

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
        } else if(inputs.name.validity.tooShort) {
            feedbacks.name = `The name field must be as least ${inputs.name.minLength} characters.`;
        } else if(inputs.name.validity.tooLong) {
            feedbacks.name = `The name field must not be greater than ${inputs.name.maxLength} characters.`;
        } else if(inputs.name.validity.petterMismatch) {
            feedbacks.name = `The name field cannot has ";".`;
        }
        if(inputs.type.validity.valueMissing) {
            feedbacks.type = 'The type field is required.';
        }
        if(inputs.displayOrder.validity.valueMissing) {
            feedbacks.displayOrder = 'The displayOption field is required.';
        }
        return ! hasError();
    }
</script>

<div class="mb-4 form-outline">
    <FormGroup floating label="Name">
        <Input name="name" placeholder="name" disabled={submitting}
            maxlength="170" pattern="(?!.*:).*" required
            feedback={feedbacks.name} valid={feedbacks.name == 'Looks good!'}
            invalid={feedbacks.name != '' && feedbacks.name != 'Looks good!'}
            bind:inner={inputs.name} value={team.name} />
    </FormGroup>
</div>
<div class="mb-4 form-outline">
    <FormGroup floating label="Type">
        <Input type="select" name="type_id" disabled={submitting} required
            feedback={feedbacks.type} valid={feedbacks.type == 'Looks good!'}
            invalid={feedbacks.type != '' && feedbacks.type != 'Looks good!'}
            bind:inner={inputs.type} bind:value={typeValue}
            onchange={() => inputs.displayOrder.value = ""}>
            <option value="" selected={! team.type_id} disabled>Please select type</option>
            {#each Object.entries(types) as [key, value]}
                <option value="{key}" selected={key == team.type_id}>{value}</option>
            {/each}
        </Input>
    </FormGroup>
</div>
<div class="mb-4 form-outline">
    <FormGroup floating label="Display Order">
        <Input type="select" name="display_order" disabled={submitting || ! typeValue} required
            feedback={feedbacks.displayOrder} valid={feedbacks.displayOrder == 'Looks good!'}
            invalid={feedbacks.displayOrder != '' && feedbacks.displayOrder != 'Looks good!'}
            bind:inner={inputs.displayOrder}>
            <option value="" selected={! team.display_order} disabled>Please display order type</option>
            {#each Object.entries(displayOptions) as [typeID, object]}
                {#each Object.entries(object) as [key, value]}
                    <option value="{key}" hidden={typeID != typeValue}
                        selected={typeID == team.type_id && key == team.display_order}>{value}</option>
                {/each}
            {/each}
        </Input>
    </FormGroup>
</div>
