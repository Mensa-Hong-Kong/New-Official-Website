<script>
    import { DropdownMenu, Dropdown, DropdownToggle, DropdownItem } from '@sveltestrap/sveltestrap';
    import NavDropdown from './NavDropdown.svelte';
    import { Link } from "@inertiajs/svelte";

	let { nodes, id } = $props();
</script>

<DropdownMenu>
    {#each nodes[id] as item}
        {#if nodes[item.id]?.length}
            <Dropdown direction="right">
                <DropdownToggle nav caret class="dropdown-item">{item.name}</DropdownToggle>
                <NavDropdown nodes={nodes} id={item.id} />
            </Dropdown>
        {:else}
            {#if item.url && route().match(item.url).name == undefined}
                <DropdownItem href={item.url ?? '#'}>
                    {item.name}
                </DropdownItem>
            {:else}
                <li>
                    <Link href={item.url} class="dropdown-item">
                        {item.name}
                    </Link>
                </li>
            {/if}
        {/if}
    {/each}
</DropdownMenu>
