<script>
    import { Col, Input, Button, Spinner, TabContent, TabPane, Table } from '@sveltestrap/sveltestrap';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';

    let { prices: initPrices, submitting = $bindable() } = $props();

    const prices = $state([]);

    let inputs =  $state({
        prices: [],
    });

    for (const data of initPrices) {
        inputs.prices.push({});
        prices.push({
            id: data.id,
            startAt: data.start_at,
            name: data.name,
            price: data.price,
            updatedAt: data.updated_at,
            editing: false,
            updating: false,
        });
    }

    function getIndexByStartAt(startAt) {
        return prices.findIndex(
            function(price) {
                return price.startAt <= startAt;
            }
        );
    }

    function getIndexById(id) {
        return prices.findIndex(
            function(price) {
                return price.id == id;
            }
        );
    }

    function close(index) {
        prices[index]['editing'] = false;
        prices[index]['updating'] = false;
        inputs['prices'][index]['startAt']['value'] = prices[index]['startAt'];
        inputs['prices'][index]['name']['value'] = prices[index]['name'];
    }

    function cancel(event, index) {
        event.preventDefault();
        close(index);
    }

    function updateSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'put').params.price;
        let index = getIndexById(id);
        if(prices[index]['updatedAt'] == response.data.updated_at) {
            close(index);
        } else if(prices.length) {
            let data = prices.splice(index, 1)[0];
            data['startAt'] = response.data.start_at ?? '';
            data['name'] = response.data.name ?? '';
            data['editing'] = false;
            data['updating'] = false;
            prices.splice(getIndexByStartAt(data['startAt']), 0, data);
        } else {
            prices[index]['startAt'] = response.data.start_at ?? '';
            prices[index]['name'] = response.data.name;
            close(index);
        }
        submitting = false;
    }

    function updateFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.join("\r\n"));
        }
        let id = route().match(error.request.responseURL, 'put').params.price;
        prices[getIndexById(id)]['updating'] = false;
        submitting = false;
    }

    function update(event, index) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updatePrice'+submitAt;
            if(submitting == 'updatePrice'+submitAt) {
                if(validation(inputs['prices'][index]['startAt'], inputs['prices'][index]['name'])) {
                    prices[index]['updating'] = true;
                    let data = {};
                    if(inputs['prices'][index]['startAt']['value']) {
                        data['start_at'] = inputs['prices'][index]['startAt']['value'];
                    }
                    if(inputs['prices'][index]['name']['value']) {
                        data['name'] = inputs['prices'][index]['name']['value'];
                    }
                    post(
                        route(
                            'admin.admission-test.products.prices.update',
                            {
                                product: route().params.product,
                                price: prices[index]['id'],
                            }
                        ),
                        updateSuccessCallback,
                        updateFailCallback,
                        'put', data
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }

    function edit(event, index) {
        event.preventDefault();
        prices[index]['editing'] = true;
    }

    let creating = $state(false);

    function validation(startAt, name, price) {
        let errors = [];
        if(name.validity.tooLong) {
            errors.push(`The price name field must not be greater than ${name.maxLength} characters.`);
        }
        if(typeof price != 'undefined') {
            if(price.validity.valueMissing) {
                errors.push('The price field is required.');
            } else if(price.validity.rangeUnderflow) {
                errors.push(`The price field must be at least ${price.min}.`);
            } else if(price.validity.rangeOverflow) {
                errors.push(`The price field must not be greater than ${price.max}.`);
            }
        }
        if(errors.length) {
            alert(errors.join("\r\n"));
            return false;
        }
        return true;
    }

    function createSuccessCallback(response) {
        alert(response.data.success);
        let data = {
            id: response.data.id,
            startAt: response.data.start_at ?? '',
            name: response.data.name ?? '',
            price: response.data.price,
            editing: false,
            updating: false,
            updatedAt: response.data.updated_at,
        };
        inputs.prices.push({});
        if(prices.length) {
            prices.splice(getIndexByStartAt(data.startAt), 0, data);
        } else {
            prices.push(data);
        }
        creating = false;
        submitting = false;
    }

    function createFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.join("\r\n"));
        }
        creating = false;
        submitting = false;
    }

    function create(event) {
        event.preventDefault();
        if (! submitting) {
            let submitAt = Date.now();
            submitting = 'updateProduct'+submitAt;
            if (submitting == 'updateProduct'+submitAt) {
                if(validation(inputs.startAt, inputs.name, inputs.price)) {
                    creating = true;
                    let data = {price: inputs.price.value};
                    if(inputs.startAt.value) {
                        data['start_at'] = inputs.startAt.value;
                    }
                    if(inputs.name.value) {
                        data['name'] = inputs.name.value;
                    }
                    post(
                        route(
                            'admin.admission-test.products.prices.store',
                            {product: route().params.product}
                        ),
                        createSuccessCallback,
                        createFailCallback,
                        'post', data
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }
</script>

<article>
    <h3 class="mb-2 fw-bold">Prices</h3>
    <form class="row g-3" onsubmit="{create}" novalidate>
        <Col md=3>
            <Input type="datetime-local" name="start_at" placeholder="start at"
                bind:inner={inputs.startAt} disabled={creating} />
        </Col>
        <Col md=2>
            <Input name="name" placeholder="name" max="255"
                bind:inner={inputs.name} disabled={creating} />
        </Col>
        <Col md=2>
            <Input type="number" name="price" placeholder="price"
                min="1" step="1" max="65535" required
                bind:inner={inputs.price} disabled={creating} />
        </Col>
        <Button color="success" disabled={submitting} class="col-md-2">
            {#if creating}
                <Spinner type="border" size="sm" />Creating...
            {:else}
                Create
            {/if}
        </Button>
    </form>
    <div class="row g-3">
        <div class="col-md-3 fw-bold">Start At</div>
        <div class="col-md-2 fw-bold">Name</div>
        <div class="col-md-1 fw-bold">Price</div>
        <div class="col-md-2 fw-bold">Edit</div>
    </div>
    {#each prices as price, index}
        <form class="row g-3" onsubmit="{(event) => update(event, index)}" novalidate>
            <Col md=3>
                <span hidden={price.editing}>{price.startAt}</span>
                <Input type="datetime-local" name="start_at" placeholder="start at"
                    hidden={! price.editing} disabled={price.updating} value={price.startAt}
                    bind:inner={inputs['prices'][index]['startAt']} />
            </Col>
            <Col md=2>
                <span hidden="{price.editing}">{price.name}</span>
                <Input name="name" placeholder="name" max="255"
                    hidden={! price.editing} disabled={price.updating} value={price.name}
                    bind:inner={inputs['prices'][index]['name']} />
            </Col>
            <Col md=2>{price.price}</Col>
            <Button color="primary" class="col-md-2"
                hidden={price.editing || price.updating}
                onclick={(event) => edit(event, index)}>Edit</Button>
            <Button color="primary" class="col-md-1" disabled={submitting}
                hidden={! price.editing || price.updating}>Save</Button>
            <Button color="danger" class="col-md-1"
                hidden={! price.editing || price.updating}
                onclick={(event) => cancel(event, index)}>Cancel</Button>
            <Button color="primary" class="col-md-2" disabled hidden={! price.updating}>
                <Spinner type="border" size="sm" />Saving...
            </Button>
        </form>
    {/each}
</article>
