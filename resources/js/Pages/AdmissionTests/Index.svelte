<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import StripeCustomerAlert from "@/Pages/Components/StripeAlert/Customer.svelte";
    import { Table, Button } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import "ckeditor5/ckeditor5.css";
    import { formatToDate, formatToTime } from '@/timeZoneDatetime';

    let { contents, tests, user: initUser } = $props();
    let user = $state(initUser);
</script>

<svelte:head>
    <title>Admission Tests | {import.meta.env.VITE_APP_NAME}</title>
    <meta name="title" content="mission Tests | {import.meta.env.VITE_APP_NAME}">
    <meta name="description" content="{import.meta.env.VITE_APP_DESCRIPTION}">
    <meta name="og:description" content="{import.meta.env.VITE_APP_DESCRIPTION}">
    <meta name="og:image" content="og_image.png">
    <meta name="og:url" content="{import.meta.env.VITE_APP_URL}">
    <meta name="og:site_name" content="{import.meta.env.VITE_APP_NAME}">
</svelte:head>

<Layout>
    <section class="container">
        {#if user}
            <StripeCustomerAlert bind:customer={user} type="user" />
        {/if}
        <h2 class="mb-2 fw-bold text-uppercase">Admission Tests</h2>
        <article class="ck-content">
            {@html contents.Info}
        </article>
        <article>
            <h3 class="mb-2 fw-bold">Upcoming Admission Tests</h3>
            <Table hover>
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                        <th scope="col">Location</th>
                        <th scope="col">Candidates</th>
                        {#if ! user || ! user.has_qualification_of_membership}
                            <th scope="col">Control</th>
                        {/if}
                    </tr>
                </thead>
                <tbody>
                    {#each tests as test}
                        <tr>
                            <td>{formatToDate(test.testing_at)}</td>
                            <td>{formatToTime(test.testing_at).slice(0, -3)}</td>
                            <td title="{test.address.address}, {test.address.district.name}, {test.address.district.area.name}">{test.location.name}</td>
                            <td>{test.candidates_count}/{test.maximum_candidates}</td>
                            {#if ! user || ! user.has_qualification_of_membership}
                                <td>
                                    {#if user && user.future_admission_test}
                                        {#if user && user.future_admission_test.id == test.id}
                                            <Link class="btn btn-primary" href={
                                                route(
                                                    'admission-tests.candidates.show',
                                                    {admission_test: test.id}
                                                )
                                            }>Ticket</Link>
                                        {:else}
                                            {#if
                                                new Date(formatToDate(test.testing_at)) > (new Date).addDays(2).endOfDay() && (
                                                    ! user || ! user.last_attended_admission_test ||
                                                    test.testing_at >= (new Date(user.last_attended_admission_test.testing_at))
                                                        .addMonths(user.last_attended_admission_test.type.interval_month)
                                                        .endOfDay()
                                                )
                                            }
                                                <Link class="btn btn-danger" href={
                                                    route(
                                                        'admission-tests.candidates.create',
                                                        {admission_test: test.id}
                                                    )
                                                }>Reschedule</Link>
                                            {:else}
                                                <Button color="secondary">Reschedule</Button>
                                            {/if}
                                        {/if}
                                    {:else}
                                        {#if
                                            user.created_stripe_customer &&
                                            new Date(formatToDate(test.testing_at)) > (new Date).addDays(2).endOfDay() && (
                                                ! user || ! user.last_attended_admission_test ||
                                                test.testing_at >= (new Date(user.last_attended_admission_test.testing_at))
                                                    .addMonths(user.last_attended_admission_test.type.interval_month)
                                                    .endOfDay()
                                            )
                                        }
                                            <Link class="btn btn-primary" href={
                                                route(
                                                    'admission-tests.candidates.create',
                                                    {admission_test: test.id}
                                                )
                                            }>Schedule</Link>
                                        {:else}
                                            <Button color="secondary">Schedule</Button>
                                        {/if}
                                    {/if}
                                </td>
                            {/if}
                        </tr>
                    {/each}
                </tbody>
            </Table>
        </article>
        <article class="ck-content">
            {@html contents.Remind}
        </article>
    </section>
</Layout>