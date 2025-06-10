<script>
    import { post } from "@/submitForm";

    import {submitting} from "@/submitting.svelte.js";

    let {prices: initPrices} = $props();

    const prices = $state([]);

    let inputs = [];

    for (const data of initPrices) {
        inputs.push({});
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

    function cancel(event, index) {
        event.preventDefault();
        prices[index]['editing'] = false;
        inputs[index]['startAt']['value'] = prices[index]['startAt'];
        inputs[index]['name']['value'] = prices[index]['name'];
    }

    function updateSuccessCallback(response) {
        bootstrapAlert(response.data.success);
        let id = route().match(response.request.responseURL, 'put').params.price;
        let index = getIndexById(id);
        if(prices[index]['updatedAt'] != response.data.updated_at) {
            prices[index]['editing'] = false;
            prices[index]['updating'] = false;
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
            prices[index]['editing'] = false;
            prices[index]['updating'] = false;
        }
        submitting.set = false;
    }

    function updateFailCallback(error) {
        if(error.status == 422) {
            bootstrapAlert(error.response.data.errors.join("\r\n"));
        }
        let id = route().match(error.request.responseURL, 'put').params.price;
        prices[getIndexById(id)]['updating'] = false;
        submitting.set = false;
    }

    function update(event, index) {
        event.preventDefault();
        if(submitting.get == '') {
            let submitAt = Date.now();
            submitting.set = 'updatePrice'+submitAt;
            if(submitting.get == 'updatePrice'+submitAt) {
                if(validation(inputs[index]['startAt'], inputs[index]['name'])) {
                    prices[index]['updating'] = true;
                    let data = {};
                    if(inputs[index]['startAt']['value']) {
                        data['start_at'] = inputs[index]['startAt']['value'];
                    }
                    if(inputs[index]['name']['value']) {
                        data['name'] = inputs[index]['name']['value'];
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
                    submitting.set = false;
                }
            }
        }
    }

    function edit(event, index) {
        event.preventDefault();
        prices[index]['editing'] = true;
    }

    let startAt, name, price;

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
            bootstrapAlert(errors.join("\r\n"));
            return false;
        }
        return true;
    }

    function createSuccessCallback(response) {
        bootstrapAlert(response.data.success);
        let data = {
            id: response.data.id,
            startAt: response.data.start_at ?? '',
            name: response.data.name ?? '',
            price: response.data.price,
            editing: false,
            updating: false,
            updatedAt: response.data.updated_at,
        };
        inputs.push({});
        if(prices.length) {
            prices.splice(getIndexByStartAt(data.startAt), 0, data);
        } else {
            prices.push(data);
        }
        creating = false;
        submitting.set = false;
    }

    function createFailCallback(error) {
        if(error.status == 422) {
            bootstrapAlert(error.response.data.errors.join("\r\n"));
        }
        creating = false;
        submitting.set = false;
    }

    function create(event) {
        event.preventDefault();
        if (submitting.get == '') {
            let submitAt = Date.now();
            submitting.set = 'updateProduct'+submitAt;
            if (submitting.get == 'updateProduct'+submitAt) {
                if(validation(startAt, name, price)) {
                    creating = true;
                    let data = {price: price.value};
                    if(startAt.value) {
                        data['start_at'] = startAt.value;
                    }
                    if(name.value) {
                        data['name'] = name.value;
                    }
                    console.log(data);
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
                    submitting.set = false;
                }
            }
        }
    }
</script>

<article>
    <h3 class="fw-bold mb-2">Prices</h3>
    <form class="row g-3" onsubmit="{create}" novalidate>
        <input type="datetime-local" name="start_at" class="col-md-3" placeholder="start at"
            bind:this={startAt} disabled="{creating}" />
        <input name="name" class="col-md-2" placeholder="name" max="255"
            bind:this={name} disabled="{creating}" />
        <input name="price" class="col-md-1" placeholder="price" step="1" min="1" max="65535"
            bind:this={price} disabled="{creating}" />
        <button class="btn btn-success col-md-2 submitButton" hidden="{creating}" disabled="{submitting.get}">Create</button>
        <button class="btn btn-success col-md-2 submitButton" disabled hidden="{! creating}">
            Creating...
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        </button>
    </form>
    <div class="row g-3">
        <div class="col-md-3 fw-bold">Start At</div>
        <div class="col-md-2 fw-bold">Name</div>
        <div class="col-md-1 fw-bold">Price</div>
        <div class="col-md-2 fw-bold">Edit</div>
    </div>
    {#each prices as price, index}
        <form class="row g-3" onsubmit="{(event) => update(event, index)}" novalidate>
            <div class="col-md-3" hidden="{price.editing}">{price.startAt}</div>
            <input type="datetime-local" name="start_at" class="col-md-3"
                placeholder="start at"  hidden="{! price.editing}"
                bind:this="{inputs[index]['startAt']}" value="{price.startAt}" />
            <div class="col-md-2" hidden="{price.editing}">{price.name}</div>
            <input name="name" class="col-md-2" max="255"
                placeholder="name" hidden="{! price.editing}"
                bind:this="{inputs[index]['name']}" value="{price.name}" />
            <div class="col-md-1">{price.price}</div>
            <button class="btn btn-primary col-md-2"
                hidden="{price.editing || price.updating}"
                onclick="{(event) => edit(event, index)}">Edit</button>
            <button class="btn btn-primary col-md-1" hidden="{! price.editing || price.updating}" disabled="{submitting.get}">Save</button>
            <button class="btn btn-danger col-md-1"
                hidden="{! price.editing || price.updating}"
                onclick="{(event) => cancel(event, index)}">Cancel</button>
            <button class="btn btn-primary col-md-2" disabled hidden="{! price.updating}">Saving</button>
        </form>
    {/each}
</article>
