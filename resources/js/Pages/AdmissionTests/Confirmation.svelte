<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import StripeCustomerAlert from "@/Pages/Components/StripeAlert/Customer.svelte";
    import { formatToDate, formatToTime } from '@/timeZoneDatetime';
    import { Table, Button } from '@sveltestrap/sveltestrap';

    let { test, user, csrf_token } = $props();
</script>

<svelte:head>
    <title>Confirmation | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <StripeCustomerAlert customer={user} type="user" />
        <h3 class="mb-2 fw-bold">
            {user.future_admission_test ? 'Reschedule' : 'Schedule'}
            Admission Tests
        </h3>
        <Table>
            <tr>
                <th>Date</th>
                <td>{formatToDate(test.testing_at)}</td>
            </tr>
            <tr>
                <th>Time</th>
                <td>{formatToTime(test.testing_at).slice(0, -3)}</td>
            </tr>
            <tr>
                <th>Location</th>
                <td>{test.location.name}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td>
                    {test.address.address},
                    {test.address.district.name},
                    {test.address.district.area.name}
                </td>
            </tr>
        </Table>
        <form method="POST" action={
            route(
                'admission-tests.candidates.store',
                {admission_test: test.id}
            )
        }>
            <input type="hidden" name="_token" value={csrf_token} />
            {#if user.future_admission_test}
                <Button color="danger" class="form-control">Reschedule</Button>
            {:else}
                <Button color="primary" class="form-control">Schedule</Button>
            {/if}
        </form>
    </section>
</Layout>