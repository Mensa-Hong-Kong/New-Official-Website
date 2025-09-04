<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Table, Button, Spinner, Alert } from '@sveltestrap/sveltestrap';
    import SortableLink from '@/Pages/Components/SortableLink.svelte';
    import { Link } from "@inertiajs/svelte";
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';
    import { formatToDatetime } from '@/timeZoneDatetime';

    let { pages: initPages } = $props();
    let pages = $state([]);
    let submitting = $state(false);
    for(let row of initPages) {
        row['deleting'] = false;
        pages.push(row);
    }

    function getIndexById(id) {
        return pages.findIndex(
            function(row) {
                return row.id == id;
            }
        );
    }

    function deleteSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'delete').params.contact;
        let index = getIndexById(id);
        pages.splice(index, 1);
        submitting = false;
    }

    function deleteFailCallback(error) {
        let id = route().match(response.request.responseURL, 'delete').params.contact;
        let index = getIndexById(id);
        pages[index]['deleting'] = false;
        submitting = false;
    }

    function confirmedDelete(index) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'delete'+submitAt;
            if(submitting == 'delete'+submitAt) {
                pages[index]['deleting'] = true;
                post(
                    route(
                        'admin.custom-web-pages.destroy',
                        {custom_web_page: pages[index]['id']}
                    ),
                    deleteSuccessCallback,
                    deleteFailCallback,
                    'delete'
                );
            }
        }
    }

    function destroy(index) {
        let message = `Are you sure to delete the custom web page of ${pages[index]['title']}?`;
        confirm(message, confirmedDelete, index);
    }
</script>

<svelte:head>
    <title>Administration Custom Web Pages | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h2 class="mb-2 fw-bold text-uppercase">Custom Web Pages</h2>
        {#if pages.length}
            <Table hover>
                <thead>
                    <tr>
                        <th scope="col"><SortableLink column="pathname" title="Pathname At" /></th>
                        <th scope="col"><SortableLink column="title" title="Title" /></th>
                        <th scope="col"><SortableLink column="created_at" title="Created At" /></th>
                        <th scope="col"><SortableLink column="updated_at" title="Updated At" /></th>
                        <th scope="col">Control</th>
                    </tr>
                </thead>
                <tbody>
                    {#each pages as page, index}
                        <tr>
                            <th scope="row">{page.pathname}</th>
                            <td>{page.title}</td>
                            <td>{formatToDatetime(page.created_at)}</td>
                            <td>{formatToDatetime(page.updated_at)}</td>
                            <td>
                                <Link class="btn btn-primary" href={
                                    route(
                                        'custom-web-page',
                                        {pathname: page.pathname}
                                    )
                                }>Show</Link>
                                <Link class="btn btn-primary" href={
                                    route(
                                        'admin.custom-web-pages.edit',
                                        {custom_web_page: page.id}
                                    )
                                }>Edit</Link>
                                <Button color="danger"
                                    disabled={submitting} onclick={() => destroy(index)}>
                                    {#if page.deleting}
                                        <Spinner type="border" size="sm" />
                                        Deleting...
                                    {:else}
                                        Delete
                                    {/if}
                                </Button>
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </Table>
        {:else}
            <Alert color="danger">
                No Result
            </Alert>
        {/if}
    </section>
</Layout>