<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import "ckeditor5/ckeditor5.css";
    import { formatToDate, formatToTime, formatToDatetime } from '@/timeZoneDatetime';
    import { Table } from '@sveltestrap/sveltestrap';

    let { test, qrCode, candidate } = $props();
</script>

<svelte:head>
    <title>Ticket | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h3 class="mb-2 fw-bold">Admission Test Scheduled</h3>
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
            {#if qrCode}
                <tr>
                    <th>Ticket</th>
                </tr>
                <tr>
                    <td>
                        <img src={qrCode} alt="Ticket QR Code">
                    </td>
                </tr>
            {:else}
                <tr>
                    <th>Is Present</th>
                    <td>
                        {#if candidate.pivot.is_present}
                            Yes
                        {:else if Date(formatToDatetime(test.expect_end_at)) < (new Date).subHour()}
                            No
                        {/if}
                    </td>
                </tr>
                {#if candidate.pivot.is_pass !== null}
                    <tr>
                        <th>Is Pass</th>
                        <td>{candidate.pivot.is_pass ? 'Yes' : 'No'}</td>
                    </tr>
                {/if}
            {/if}
        </Table>
        {#if qrCode}
            <div class="alert alert-danger" role="alert">
                <b>Remember:</b>
                <ol>
                    <li>Please bring your own pencil.</li>
                    <li>Please bring your own ticket QR code.</li>
                    <li>Please bring your own Hong Kong/Macau/(Mainland) Resident ID.</li>
                    <li>Candidates should arrive 20 minutes before the test session. Latecomers may be denied entry.'</li>
                </ol>
            </div>
        {/if}
    </section>
</Layout>