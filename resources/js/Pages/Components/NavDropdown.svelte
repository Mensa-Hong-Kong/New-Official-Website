<script>
    import { DropdownMenu, Dropdown, DropdownToggle, DropdownItem } from '@sveltestrap/sveltestrap';
    import NavDropdown from './NavDropdown.svelte';

	let { nodes, items, id } = $props();
</script>

<DropdownMenu>
    {#each nodes[id] as itemID}
        {#if nodes[itemID].length}
            <Dropdown direction="right">
                <DropdownToggle nav caret class="dropdown-item">{items[itemID]['name']}</DropdownToggle>
                <NavDropdown nodes={nodes} items={items} id={itemID} />
            </Dropdown>
        {:else}
            <DropdownItem href={items[itemID]['url'] ?? '#'}>{items[itemID]['name']}</DropdownItem>
        {/if}
    {/each}
</DropdownMenu>
