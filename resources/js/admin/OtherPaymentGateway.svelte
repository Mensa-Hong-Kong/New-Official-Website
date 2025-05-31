<script>
    let {paymentGateways: initPaymentGateways} = $props();
    const paymentGateways = $state([]);
    for (const data of initPaymentGateways) {
        paymentGateways.push(data);
    }
</script>

<h2 class="fw-bold mb-2 text-uppercase">
    Other Payment Gateway
</h2>
{#if paymentGateways.length}
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
            {#each paymentGateways as paymentGateway, index}
                <tr>
                    <th>{paymentGateway.id}</th>
                    <td>{paymentGateway.name}</td>
                    <td>{paymentGateway.is_active ? 'Active' : 'Inactive'}</td>
                </tr>
            {/each}
        </tbody>
    </table>
{:else}
    <div class="alert alert-danger" role="alert">
        No Result
    </div>
{/if}
