<script>
    import { Link } from "@inertiajs/svelte";

    let directions = ['asc', 'desc'];
	let {
        column: sortColumn,
        class: anchorClass = [],
        title = null,
        queryParameters = {},
        defaultDirection: direction = "asc",
        ...restProps
    } = $props();

    if (title === null) {
        title = sortColumn.split('.').pop();
    }

    let icon = "fa fa-sort";

    for (const [key, value] of Object.entries(queryParameters)) {
        url.searchParams.set(key, value);
    }

    let url = new URL(window.location);

    if(route().params.sort == sortColumn && directions.includes(route().params.direction)) {
        if(route().params.direction == 'desc') {
            direction = 'asc';
            icon += '-desc'
        } else {
            direction = 'desc';
            icon += '-asc'
        }
    }
    url.searchParams.set('sort', sortColumn);
    url.searchParams.set('direction', direction);
</script>

<Link class={anchorClass} href={url.toString()} {...restProps}>{title.ucfirst()}</Link>
<i class="{icon}"></i>