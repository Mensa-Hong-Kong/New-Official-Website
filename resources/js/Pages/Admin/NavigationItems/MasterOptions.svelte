<script>
    import MasterOptions from './MasterOptions.svelte';

    let { items, masterID = null, layer = 1 } = $props();
</script>

{#each items as item}
    {#if item.master_id == masterID}
        <option value={item.id}>
            {'&nbsp;'.repeat(4 * layer) + item.name}
        </option>
        <MasterOptions items={items} masterID={item.id} layer={layer + 1} />
    {/if}
{/each}