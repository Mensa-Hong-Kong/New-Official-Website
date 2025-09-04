<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Table, Alert } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";

    let { products } = $props();
</script>

<svelte:head>
    <title>Administration Admission Test Products | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h2 class="mb-2 fw-bold text-uppercase">Admission Test Products</h2>
        {#if products.length}
            <Table hover>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Minimum Age</th>
                        <th scope="col">Maximum Age</th>
                        <th>Show</th>
                    </tr>
                </thead>
                <tbody>
                    {#each products as row}
                        <tr>
                            <td>{row.id}</td>
                            <td>{row.name}</td>
                            <td>{row.minimum_age}</td>
                            <td>{row.maximum_age}</td>
                            <td>
                                <Link class="btn btn-primary" href={
                                    route(
                                        'admin.admission-test.products.show',
                                        {product: row.id}
                                    )
                                }>Show</Link>
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
