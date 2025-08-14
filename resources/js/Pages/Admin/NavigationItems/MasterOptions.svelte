<script>
    import MasterOptions from './MasterOptions.svelte';

    let { items, masterID = null, layer = 1, selected = null, ignore = null } = $props();
</script>

{#each items as item}
    {#if item.master_id == masterID && item.id != ignore}
        <option value={item.id} selected={item.id == selected}>
            {'\xA0'.repeat(4 * layer) + item.name}
        </option>
        <MasterOptions items={items} masterID={item.id} layer={layer + 1} selected={selected} />
    {/if}
{/each}