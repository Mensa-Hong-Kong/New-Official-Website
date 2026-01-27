<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { formatToDate, formatToTime } from '@/timeZoneDatetime';
    import { Table, Button, Row, Col } from '@sveltestrap/sveltestrap';

    let { test, user, csrf_token, products, price } = $props();
</script>

<svelte:head>
    <title>{products ? 'Detail' : 'Confirmation'} | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h3 class="mb-2 fw-bold">
            {user.future_admission_test ? 'Reschedule' : 'Schedule'}
            Admission Test {products ? 'Detail' : 'Confirmation'}
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
                    {test.address.value},
                    {test.address.district.name},
                    {test.address.district.area.name}
                </td>
            </tr>
            {#if price}
                {#if price.product.quota > 1}
                    <tr>
                        <th>Retake Quota</th>
                        <td>{price.product.quota - 1}</td>
                    </tr>
                {/if}
                <tr>
                    <th>Price</th>
                    <td>{price.value}</td>
                </tr>
            {/if}
        </Table>
        {#if products}
            <form action={
                route(
                    'admission-tests.candidates.create',
                    {admission_test: test.id}
                )
            }>
                <Row class="g-4">
                    {#each products as product, index}
                        <Col md=3>
                            <input type="radio" name="price_id" id="price{index}" checked={index == 0}
                                value={product.price.id} class="radio-input d-none" />
                            <label class="p-4 radio-card card h-100" for="price{index}">
                                <div class="p-0 card-body">
                                    <h5 class="card-title">{product.option_name}</h5>
                                    <h2 class="mb-3 card-text">${product.price.value}</h2>
                                    {#if product.quota > 1}
                                        <ul class="list-unstyled">
                                            <li class="mb-2">✓ Retake {product.quota - 1} time when fail</li>
                                        </ul>
                                    {/if}
                                </div>
                            </label>
                        </Col>
                    {/each}
                </Row>
                <Button color="primary" class="form-control">Next</Button>
            </form>
        {:else}
            <form method="POST" action={
                route(
                    'admission-tests.candidates.store',
                    {admission_test: test.id}
                )
            }>
                <input type="hidden" name="_token" value={csrf_token} />
                {#if price}
                    <input type="hidden" name="price_id" value={price.id} />
                {/if}
                <Button color="success" class="form-control">Confirm</Button>
            </form>
        {/if}
    </section>
</Layout>

<style>
    .radio-card {
        cursor: pointer;
        border: 2px solid #dee2e6;
        transition: all 0.3s ease;
    }
    
    .radio-card:hover {
        border-color: #0d6efd;
    }
    
    .radio-input:checked + .radio-card {
        border-color: #0d6efd;
        background-color: #f8f9ff;
    }
</style>