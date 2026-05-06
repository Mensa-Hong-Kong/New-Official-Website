<script>
    import { seo } from '@/Pages/Layouts/App.svelte';
    import { formatToDate, formatToDatetime, formatToTime } from '@/timeZoneDatetime';
    import { Alert, Table, Button, Row, Col } from '@sveltestrap/sveltestrap';

    let { isReschedule, test, user, products, price } = $props();

    seo.title = products ? 'Detail Admission Test' : 'Confirmation Admission Test';
</script>

<section class="container">
    <h3 class="mb-2 fw-bold">
        {isReschedule ? 'Reschedule' : 'Schedule'}
        Admission Test {products ? 'Detail' : 'Confirmation'}
    </h3>
    {#if
        user.has_unused_quota_admission_test_order?.quota_expired_on &&
        formatToDate(user.has_unused_quota_admission_test_order.quota_expired_on).endOfDay < formatToDatetime(test.testing_at)
    }
        <Alert color="danger">
            You have an unused quota that expires on {formatToDate(user.has_unused_quota_admission_test_order?.quota_expired_on)}.
            If you reschedule to the current admission test, you will need to pay a new admission test fee and forfeit your old admission test quota.
            If you have a special reason to use the unused spot for reschedule to the current admission test, please contact us to reschedule by manual.
        </Alert>
    {/if}
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
            {#if price}
                <input type="hidden" name="price_id" value={price.id} />
            {/if}
            <Button color="success" class="form-control">Confirm</Button>
        </form>
    {/if}
</section>

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
    }
</style>
