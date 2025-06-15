<script>
	let { id, data, isStringKey = false } = $props();
</script>

<datalist id="{id}">
    {#if Array.isArray(data)}
        {#each data as value}
            <option value="{value}">{value}</option>
        {/each}
    {:else}
        {#each Object.entries(data) as [key, value]}
            <option value="{key}">{value}</option>
        {/each}
    {/if}
</datalist>
