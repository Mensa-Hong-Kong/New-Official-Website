<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Table } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { formatToDatetime } from '@/timeZoneDatetime';

    let { auth, order } = $props();
</script>

<svelte:head>
    <title>Administration Show Admission Test Order | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <article>
            <h3 class="mb-2 fw-bold">Info</h3>
            <Table hover>
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>{order.id}</td>
                    </tr>
                    <tr>
                        <th>Payer</th>
                        <td>
                            {#if
                                auth.user.permissions.includes('View:User') ||
                                auth.user.roles.includes('Super Administrator')
                            }
                                <Link href={route('admin.users.show', {user: order.user.id})}>
                                    {order.user.adorned_name}
                                </Link>
                            {:else}
                                {order.user.adorned_name}
                            {/if}
                        </td>
                    </tr>
                    <tr>
                        <th>Product Name</th>
                        <td>{order.product_name}</td>
                    </tr>
                    <tr>
                        <th>Price Name</th>
                        <td>{order.price_name}</td>
                    </tr>
                    <tr>
                        <th>Price</th>
                        <td>{order.price}</td>
                    </tr>
                    <tr>
                        <th>Quota</th>
                        <td>
                            {#if
                                auth.user.permissions.includes('Edit:Admission Test') ||
                                auth.user.roles.includes('Super Administrator')
                            }
                                {order.tests.length}
                            {:else}
                                {order.tests_count}
                            {/if}/{order.quota}
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{order.status.ucfirst()}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{formatToDatetime(order.created_at)}</td>
                    </tr>
                    <tr>
                        <th>Expired At</th>
                        <td>{formatToDatetime(order.expired_at)}</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>{order.gateway.type}</td>
                    </tr>
                    <tr>
                        <th>Gateway</th>
                        <td>{order.gateway.name}</td>
                    </tr>
                    <tr>
                        <th>Reference Number</th>
                        <td>{order.reference_number}</td>
                    </tr>
                </tbody>
            </Table>
        </article>>
        {#if
            auth.user.permissions.includes('Edit:Admission Test') ||
            auth.user.roles.includes('Super Administrator')
        }
            <article>
                <h3 class="mb-2 fw-bold">Tests</h3>
                <Table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Testing At</th>
                            <th>Location</th>
                            <th>Is Present</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each order.tests as test}
                            <tr>
                                <td>
                                    <Link href={
                                        route(
                                            'admin.admission-tests.show',
                                            {admission_test: test.id}
                                        )
                                    }>{test.id}</Link>
                                </td>
                                <td>{test.type.name}</td>
                                <td>{formatToDatetime(test.testing_at)}</td>
                                <td>{test.location.name}</td>
                                <td>{test.is_present ? 'Yes' : 'No'}</td>
                                <td>
                                    {#if test.is_present}
                                        {test.is_pass ? 'Yes' : 'No'}
                                    {/if}
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </Table>
            </article>
        {/if}
    </section>
</Layout>