<script>
    import { seo } from '@/Pages/Layouts/App.svelte';
    import { formatToDate, formatToDatetime, formatToTime } from '@/timeZoneDatetime';
    import { Table } from '@sveltestrap/sveltestrap';
    import QR from '@svelte-put/qr/img/QR.svelte';

    seo.title = 'Ticket';

    let { auth, test, candidate } = $props();
</script>

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
                {test.address.value},
                {test.address.district.name},
                {test.address.district.area.name}
            </td>
        </tr>
        {#if new Date(formatToDatetime(test.expect_end_at)) >= (new Date).subHour() }
            <tr>
                <th colspan=2>Ticket</th>
            </tr>
            <tr>
                <td colspan=2>
                    <QR anchorOuterFill="red" anchorInnerFill="red"
                        moduleFill='#000000' backgroundFill='#FFFFFF'
                        version=6 height=315 width=315 margin={4} data={
                            route(
                                'admin.admission-tests.candidates.show',
                                {
                                    admission_test: route().params.admission_test,
                                    candidate: auth.user.id,
                                }
                            )
                        } />
                </td>
            </tr>
        {:else}
            <tr>
                <th>Status</th>
                <td>
                    {#if candidate.pivot.is_present !== null}
                        {candidate.pivot.is_present ? 'Yes' : 'No'}
                    {/if}
                </td>
            </tr>
            {#if candidate.pivot.is_passed !== null}
                <tr>
                    <th>Result</th>
                    <td>{candidate.pivot.is_passed ? 'Yes' : 'No'}</td>
                </tr>
            {/if}
        {/if}
    </Table>
    {#if new Date(formatToDatetime(test.expect_end_at)) < (new Date).subHour()}
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
