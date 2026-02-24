<script>
    import { seo } from '@/Pages/Layouts/App.svelte';
    import { Table, Button, Spinner } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { formatToDatetime } from '@/timeZoneDatetime';
    import { post } from "@/submitForm";
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { can } from "@/gate";

    seo.title = 'Administration Show Admission Test Order';

    let { order: initOrder } = $props();
    let order = $state(initOrder);
    let submitting = $state(false);

    if (order.status == 'pending') {
        let expiredTime = new Date(formatToDatetime(order.expired_at)) - (new Date);
        if (expiredTime > 0) {
            setTimeout(
                () => {
                    if (
                        order.status == 'pending' && ! (
                            typeof submitting == 'string' &&
                            submitting.startsWith('updateStatus')
                        )
                    ) {
                        order.status = 'expired';
                    }
                }, expiredTime
            );
        } else {
            order.status = 'expired';
        }
    }

    function updateStatusSuccessCallback(response) {
        alert(response.data.success);
        order.status = response.data.status;
        submitting = false;
    }

    function updateStatusFailCallback(error) {
        if (error.status == 422) {
            alert(error.response.data.errors.status);
        }
        if (new Date(formatToDatetime(order.expired_at)) - (new Date) <= 0) {
            order.status = 'expired';
        }
        submitting = false;
    }

    function confirmedUpdateStatus(status) {
        if (! submitting) {
            let submitAt = Date.now();
            submitting = 'updateStatus'+submitAt;
            if(submitting == 'updateStatus'+submitAt) {
                post(
                    route(
                        'admin.admission-test.orders.status.update',
                        {order: order.id}
                    ),
                    updateStatusSuccessCallback,
                    updateStatusFailCallback,
                    'put', {status: status}
                );
            }
        }
    }

    function updateStatus(status) {
        let message = `Are you sure to update order status to ${status}?`;
        confirm(message, confirmedUpdateStatus, status);
    }
</script>

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
                        {#if can('View:User')}
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
                    <th>Minimum Age</th>
                    <td>{order.minimum_age}</td>
                </tr>
                <tr>
                    <th>Maximum Age</th>
                    <td>{order.maximum_age}</td>
                </tr>
                <tr>
                    <th>Quota</th>
                    <td>
                        {#if can('Edit:Admission Test')}
                            {order.tests.length}
                        {:else}
                            {order.tests_count}
                        {/if}/{order.quota}
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        {order.status.ucfirst()}
                        {#if order.status == 'pending' && can('permission:Edit:Admission Test Order')}
                            <Button color="success" disabled={submitting}
                                onclick={() => updateStatus("succeeded")}>Succeeded</Button>
                            <Button color="danger" disabled={submitting}
                                    onclick={() => updateStatus("canceled")}>Canceled</Button>
                        {/if}
                    </td>
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
    {#if can('Edit:Admission Test')}
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
