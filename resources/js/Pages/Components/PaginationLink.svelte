<script>
    import { Link } from "@inertiajs/svelte";

    let {
        next = false,
        previous = false,
        first = false,
        last = false, 
        ariaLabel = '',
        href = '',
        children,
        ...restProps
    } = $props();
    let defaultAriaLabel;
  
    if (previous) {
        defaultAriaLabel = 'Previous';
    } else if (next) {
        defaultAriaLabel = 'Next';
    } else if (first) {
        defaultAriaLabel = 'First';
    } else if (last) {
        defaultAriaLabel = 'Last';
    }
  
    let realLabel = ariaLabel || defaultAriaLabel;
</script>
  
<Link {...restProps} class="page-link" {href}>
    {#if previous || next || first || last}
        <span aria-hidden="true">
            {#if children}
                {@render children()}
            {:else}
                {#if previous}
                    {'\u2039'}
                {:else if next}
                    {'\u203A'}
                {:else if first}
                    {'\u00ab'}
                {:else}
                    {'\u00bb'}
                {/if}
            {/if}
        </span>
        <span class="visually-hidden">{realLabel}</span>
    {:else}
        {@render children?.()}
    {/if}
</Link>
  