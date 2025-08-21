<script>
	let { nodes, items, id } = $props();
</script>

<ul class="dropdown-menu" aria-labelledby="dropdown{{ id }}">
    {#each nodes[id] as itemID}
        {#if nodes[itemID].length}
            <li class="dropdown dropend">
                <a class="dropdown-item dropdown-toggle" href="{items[itemID]['url'] ?? '#'}" role="button"
                    id="dropdown{itemID}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    {items[itemID]['name']}
                </a>
                <NavDropdown nodes={nodes} items={items} id={itemID} />
            </li>
        {:else}
            <li>
                <a class="dropdown-item" href="{items[itemID]['url'] ?? '#'}">{items[itemID]['name']}</a>
            </li>
        {/if}
    {/each}
</ul>
