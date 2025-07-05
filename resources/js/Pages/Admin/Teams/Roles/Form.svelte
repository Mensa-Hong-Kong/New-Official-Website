<script>
    import { FormGroup, Input, Label, Table } from '@sveltestrap/sveltestrap';
	import Datalist from '@/Pages/Components/Datalist.svelte';
    let {
        inputs = $bindable(), feedbacks = $bindable(), submitting,
        roles, displayOptions, modules, permissions, modulePermissions,
        role = {}, displayOrder = null, roleHasModulePermissions = []
    } = $props();
    inputs.permissions = {};

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
            feedbacks.name = `The name field must be at least ${inputs.name.minLength} characters.`;
        } else if(inputs.name.validity.tooLong) {
            feedbacks.name = `The name field must not be greater than ${inputs.name.maxLength} characters.`;
        } else if(inputs.name.validity.patternMismatch) {
            feedbacks.name = `The name field cannot has ";".`;
        }
        if(inputs.displayOrder.validity.valueMissing) {
            feedbacks.displayOrder = 'The display order field is required.';
        }
        return ! hasError();
    }
</script>

<div class="mb-4 form-outline">
    <FormGroup floating label="Name">
        <Input name="name" placeholder="name" list="roles" disabled={submitting}
            maxlength="170" pattern="(?!.*:).*" required
            feedback={feedbacks.name} valid={feedbacks.name == 'Looks good!'}
            invalid={feedbacks.name != '' && feedbacks.name != 'Looks good!'}
            bind:inner={inputs.name} value={role.name} />
        <Datalist id="roles" data={roles} />
    </FormGroup>
</div>
<div class="mb-4 form-outline">
    <FormGroup floating label="Display Order">
        <Input type="select" name="display_order" disabled={submitting} required
            feedback={feedbacks.type} valid={feedbacks.type == 'Looks good!'}
            invalid={feedbacks.type != '' && feedbacks.type != 'Looks good!'}
            bind:inner={inputs.displayOrder}>
            <option value="" selected={! displayOrder} disabled>Please display order</option>
            {#each Object.entries(displayOptions) as [key, value]}
                <option value="{key}" selected={key == displayOrder}>{value}</option>
            {/each}
        </Input>
    </FormGroup>
</div>
<div class="mb-4 form-outline">
    <h3 class="mb-2 fw-bold">Permissions</h3>
    <Table hover>
        <thead>
            <tr>
                <th></th>
                {#each permissions as permission}
                    <th>{permission.name}</th>
                {/each}
            </tr>
        </thead>
        <tbody>
            {#each modules as module}
                <tr>
                    <th>{module.name}</th>
                    {#each permissions as permission}
                        <td>
                            {#if modulePermissions[module.id] && modulePermissions[module.id][permission.id]}
                                <div class="form-check">
                                    <input type="checkbox" name="module_permissions[]"
                                        value="{modulePermissions[module.id][permission.id]}"
                                        class="form-check-input permission"
                                        checked={roleHasModulePermissions.includes(modulePermissions[module.id][permission.id])}
                                        bind:this={inputs['permissions'][modulePermissions[module.id][permission.id]]} />
                                </div>
                            {/if}
                        </td>
                    {/each}
                </tr>
            {/each}
        </tbody>
    </Table>
</div>
