<script>
	let { items, id } = $props();
</script>

<ul class="dropdown-menu" aria-labelledby="dropdown{{ id }}">
    {#each Object.entries(items) as [id, item]}
        {#if item.children}
            <li class="dropdown dropend">
                <a class="dropdown-item dropdown-toggle" href="{item.url ?? '#'}" role="button"
                    id="dropdown{id}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    {item.name}
                </a>
                <NavDropdown items={item.children} id={id} />
            </li>
        {:else}
            <li>
                <a class="dropdown-item" href="{item.url ?? '#'}">{item.name}</a>
            </li>
        {/if}
    {/each}
</ul>
