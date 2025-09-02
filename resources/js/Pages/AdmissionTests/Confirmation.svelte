<script>
    import StripeCustomerAlert from "@/Pages/Components/StripeAlert/Customer.svelte";
    import { formatToDate, formatToTime } from '@/timeZoneDatetime';
    import { Table, Row, Button, Col } from '@sveltestrap/sveltestrap';

    let { test, user, products, csrf_token } = $props();
</script>

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
        {#if products}
            <Row class="g-4">
                {#each products as product, index}
                    <Col md=3>
                        <input type="radio" name="price_id" id="price{index}" checked={index == 0}
                            value={product.price.id} class="radio-input d-none" />
                        <label class="p-4 radio-card card h-100" for="price{index}">
                            <div class="p-0 card-body">
                                <h5 class="card-title">{product.option_name}</h5>
                                <h2 class="mb-3 card-text">${product.price.price}</h2>
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
        {/if}
        {#if user.future_admission_test}
            <Button color="danger" class="form-control">Reschedule</Button>
        {:else}
            <Button color="primary" class="form-control">Schedule</Button>
        {/if}
    </form>
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
        background-color: #f8f9ff;
    }
</style>