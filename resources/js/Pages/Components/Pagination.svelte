<script>
    import { Pagination, PaginationItem } from '@sveltestrap/sveltestrap';
    import PaginationLink from './PaginationLink.svelte';

    let { total, current } = $props();
    let min = Math.max(current - 3, 1)
    let max = Math.min(current + 3, total)
    if(min == 1 && total >= 7) {
        max = 7;
    }
    if(max == total && total >= 7) {
        min = total - 6;
    }
    let url = new URL(window.location);
    function getUrl(page) {
        url.searchParams.set('page', page);
        return url.toString();
    }
</script>

{#if total > 1}
    <Pagination>
        {#if current > 2}
            <PaginationItem>
                <PaginationLink first href={getUrl(1)} />
            </PaginationItem>
        {/if}
        {#if current > 1}
            <PaginationItem>
                <PaginationLink previous href={getUrl(current - 1)} />
            </PaginationItem>
        {/if}
        {#if min > 1}
            <PaginationItem>
                <PaginationLink href={getUrl(1)}>1</PaginationLink>
            </PaginationItem>
        {/if}
        {#if min > 2}
            <PaginationItem>
                <PaginationLink href={getUrl(1)}>2</PaginationLink>
            </PaginationItem>
        {/if}
        {#if min > 3}
            <PaginationItem>
                <PaginationLink href={getUrl(1)}>3</PaginationLink>
            </PaginationItem>
        {/if}
        {#if min > 4}
            <PaginationItem disabled>
                <span class="page-link">......</span>
            </PaginationItem>
        {/if}
        {#each range(min, max) as index}
            <PaginationItem active={current == index}>
                <PaginationLink href={getUrl(index)}>{index}</PaginationLink>
            </PaginationItem>
        {/each}
        {#if max < total - 3}
            <PaginationItem disabled>
                <span class="page-link">......</span>
            </PaginationItem>
        {/if}
        {#if max < total - 2}
            <PaginationItem>
                <PaginationLink href={getUrl(total - 2)}>{total - 2}</PaginationLink>
            </PaginationItem>
        {/if}
        {#if max < total - 1}
            <PaginationItem>
                <PaginationLink href={getUrl(total - 1)}>{total - 1}</PaginationLink>
            </PaginationItem>
        {/if}
        {#if max < total}
            <PaginationItem>
                <PaginationLink href={getUrl(total)}>{total}</PaginationLink>
            </PaginationItem>
        {/if}
        {#if current < total}
            <PaginationItem>
                <PaginationLink next href={getUrl(current + 1)} />
            </PaginationItem>
        {/if}
        {#if current < total - 1}
            <PaginationItem>
                <PaginationLink last href={getUrl(total)} />
            </PaginationItem>
        {/if}
    </Pagination>
{/if}