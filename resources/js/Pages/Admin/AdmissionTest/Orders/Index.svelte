<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Row, Col, Table, Input, Button, Alert, Label } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { formatToDatetime } from '@/timeZoneDatetime';
    import Pagination from '@/Pages/Components/Pagination.svelte';

    let { auth, orders, append } = $props();
</script>

<svelte:head>
    <title>Administration Admission Test Orders | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h2 class="mb-2 fw-bold">
            Admission Test Orders
        </h2>
        {#if orders.data.length}
            <form novalidate onsubmit="{search}">
                <Row class="g-3">
                    <Col md=1><Label>Status:</Label></Col>
                    <Col md=2>
                        <input type="checkbox" class="btn-check" name="statuses[]" value="pending" id="pending"
                            checked={append.statuses && append.statuses.includes('pending')} />
                        <label class="form-control btn btn-outline-primary" for='pending'>Pending</label>
                    </Col>
                    <Col md=2>
                        <input type="checkbox" class="btn-check" name="statuses[]" value="cancelled" id="cancelled"
                            checked={append.statuses && append.statuses.includes('cancelled')} />
                        <label class="form-control btn btn-outline-primary" for='cancelled'>Cancelled</label>
                    </Col>
                    <Col md=2>
                        <input type="checkbox" class="btn-check" name="statuses[]" value="failed" id="failed"
                            checked={append.statuses && append.statuses.includes('failed')} />
                        <label class="form-control btn btn-outline-primary" for='failed'>Failed</label>
                    </Col>
                    <Col md=2>
                        <input type="checkbox" class="btn-check" name="statuses[]" value="expired" id="expired"
                            checked={append.statuses && append.statuses.includes('expired')} />
                        <label class="form-control btn btn-outline-primary" for='expired'>Expired</label>
                    </Col>
                    <Col md=2>
                        <input type="checkbox" class="btn-check" name="statuses[]" value="succeeded" id="succeeded"
                            checked={append.statuses && append.statuses.includes('succeeded')} />
                        <label class="form-control btn btn-outline-primary" for='succeeded'>Succeeded</label>
                    </Col>
                </Row>
                <Row class="g-3">
                    <Col md=1><Label>From:</Label></Col>
                    <Col md=3>
                        <Input type="datetime-local" name="from" value={append.from} step=1 />
                    </Col>
                    <Col md=1><Label>To:</Label></Col>
                    <Col md=3>
                        <Input type="datetime-local" name="to" value={append.to} step=1 />
                    </Col>
                    <Col md=2><Button block color="primary">Search</Button></Col>
                    <Col md=2>
                        <Link class="form-control btn btn-danger"
                            href={route('admin.admission-test.orders.index')}>Clear</Link>
                    ></Col>
                </Row>
            </form>
            <Table hover>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Payer</th>
                        <th scope="col">Price</th>
                        <th scope="col">Used/Quota</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    {#each orders.data as row}
                        <tr>
                            <td>{row.id}</td>
                            <td>
                                {#if
                                    auth.user.permissions.includes('View:User') ||
                                    auth.user.roles.includes('Super Administrator')
                                }
                                    <Link href={
                                        route('admin.users.show', {user: row.user.id})
                                    }>
                                        {#if row.user.adorned_name.length > 32}
                                            {row.user.adorned_name.substr(0, 29)}...
                                        {:else}
                                            {row.user.adorned_name}
                                        {/if}
                                    </Link>
                                {:else}
                                    {#if row.user.adorned_name.length > 32}
                                        {row.user.adorned_name.substr(0, 29)}...
                                    {:else}
                                        {row.user.adorned_name}
                                    {/if}
                                {/if}
                            </td>
                            <td>{row.price}</td>
                            <td>{row.tests_count}/{row.quota}</td>
                            <td>{row.status.ucfirst()}</td>
                            <td>{formatToDatetime(row.created_at)}</td>
                            <td>
                                <Link class="btn btn-primary" href={
                                    route(
                                        'admin.admission-test.orders.show',
                                        {order: row.id}
                                    )
                                }>Show</Link>
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </Table>
            <Pagination total={orders.last_page} current={orders.current_page} />
        {:else}
            <Alert color="danger">
                No Result
            </Alert>
        {/if}
    </section>
</Layout>