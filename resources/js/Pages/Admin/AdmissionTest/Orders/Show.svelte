<script>
    import { seo } from '@/Pages/Layouts/App.svelte';
    import { Table, Button, Spinner, Input } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { formatToDatetime } from '@/timeZoneDatetime';
    import { post } from "@/submitForm";
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { canAny, can } from "@/gate.ts";

    seo.title = 'Administration Show Admission Test Order';

    let { order: initOrder } = $props();
    let order = $state(initOrder);
    let submitting = $state(false);
    let creating = $state(false);
    let inputs = $state({});
    let isReschedule = $derived(order.user?.last_admission_test && ! order.user.last_admission_test.pivot_is_present);

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

    function validation()
    {
        if(inputs.test.validity.valueMissing) {
            alert('The user id field is required.');
            return false;
        }
        if(inputs.test.validity.patternMismatch) {
            alert('The user id field must be an integer.');
            return false;
        }
        return true;
    }

    function createTestSuccessCallback(response) {
        alert(response.data.success);
        let data = {
            id: response.data.id,
            type: {name: response.data.type},
            testing_at: response.data.testing_at,
            location: {name: response.data.location},
            pivot: {is_present: null}
        }
        if(
            canAny([
                'View:Admission Test Result',
                'Edit:Admission Test Result',
            ])
        ) {
            data['isPassed'] = null;
        }
        order.tests.unshift(data);
        creating = false;
        submitting = false;
    }

    function createTestFailCallback(error) {
        alert(error.response.data.message);
        creating = false;
        submitting  = false;
    }

    function createTest(event) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'createTest'+submitAt;
            if(submitting == 'createTest'+submitAt) {
                if(validation()) {
                    creating = true;
                    let data = {
                        test_id: inputs.test.value,
                        function: event.submitter.value,
                    };
                    post(
                        route(
                            'admin.admission-test.orders.admission-tests.store',
                            {order: order.id}
                        ),
                        createTestSuccessCallback,
                        createTestFailCallback,
                        'post', data
                    );
                } else {
                    submitting = false;
                }
            }
        }
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
                    <td>{order.quota}</td>
                </tr>
                <tr>
                    <th>Returned Quota</th>
                    <td>{order.returned_quota}</td>
                </tr>
                <tr>
                    <th>Used Quota</th>
                    <td>{order.attended_tests_count}</td>
                </tr>
                <tr>
                    <th>Quota Validity Months</th>
                    <td>{order.quota_validity_months ? order.quota_validity_months : 'Infinite'}</td>
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
    </article>
    {#if
        canAny([
            'View:Admission Test Candidate',
            'Edit:Admission Test Candidate',
            'View:Admission Test Result',
            'Edit:Admission Test Result',
        ])
    }
        <article>
            <h3 class="mb-2 fw-bold">Tests</h3>
            <Table hover>
                <thead>
                    {#if can('Edit:Admission Test Candidate')}
                        <tr>
                            <td style="width: 150px">
                                <form method="POST" novalidate onsubmit={createTest} id="createTestForm">
                                    <Input name="test_id" patten="^\+?[1-9][0-9]*" required
                                        bind:inner={inputs.test} />
                                </form>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            {#if
                                canAny([
                                    'View:Admission Test Result',
                                    'Edit:Admission Test Result',
                                ])
                            }
                                <td></td>
                            {/if}
                            <td>
                                <Button block form="createTestForm" disabled={submitting}
                                    color={isReschedule ? 'danger': 'success'}
                                    name="function"
                                    value={isReschedule ? 'reschedule': 'schedule'}>
                                    {#if creating}
                                        <Spinner type="border" size="sm" />
                                        {isReschedule ? 'Rescheduling' : 'Scheduling'}...
                                    {:else}
                                        {isReschedule ? 'Reschedule' : 'Schedule'}
                                    {/if}
                                </Button>
                            </td>
                        </tr>
                    {/if}
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Testing At</th>
                        <th>Location</th>
                        <th>Status</th>
                        {#if
                            canAny([
                                'View:Admission Test Result',
                                'Edit:Admission Test Result',
                            ])
                        }
                            <th>Result</th>
                        {/if}
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
                            <td>
                                {#if test.pivot.is_present !== null}
                                    {test.pivot.is_present ? 'Yes' : 'No'}
                                {/if}
                            </td>
                            {#if
                                canAny([
                                    'View:Admission Test Result',
                                    'Edit:Admission Test Result',
                                ])
                            }
                                <td>
                                    {#if test.pivot.is_passed !== null}
                                        {test.pivot.is_passed ? 'Yes' : 'No'}
                                    {/if}
                                </td>
                            {/if}
                        </tr>
                    {/each}
                </tbody>
            </Table>
        </article>
    {/if}
</section>
