@extends('layouts.app')

@section('main')
    <section class="container">
        <article>
            <form id="form" method="POST" action="{{ route('admin.admission-test.products.update', ['product' => $product]) }}" novalidate>
                @csrf
                @method('put')
                <h3 class="fw-bold mb-2">
                    Info
                    <button class="btn btn-primary" id="savingButton" type="button" disabled hidden>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Saving...
                    </button>
                    <button onclick="return false" class="btn btn-outline-primary" id="editButton">Edit</button>
                    <button type="submit" class="btn btn-outline-primary submitButton" id="saveButton" hidden>Save</button>
                    <button onclick="return false" class="btn btn-outline-danger" id="cancelButton" hidden>Cancel</button>
                </h3>
                <table class="table">
                    <tr>
                        <th>Name</th>
                        <td>
                            <span id="showName">{{ $product->name }}</span>
                            <input name="name" class="form-control" id="validationName" placeholder="name"
                                maxlength="255" value="{{ $product->name }}" data-value="{{ $product->name }}" hidden required />
                            <div id="nameFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Minimum Age</th>
                        <td>
                            <span id="showMinimumAge">{{ $product->minimum_age }}</span>
                            <input type="number" name="minimum_age" class="form-control" id="validationMinimumAge" placeholder="minimum age"
                                step="1" min="1" max="255" value="{{ $product->minimum_age }}" data-value="{{ $product->minimum_age }}" hidden />
                            <div id="minimumAgeFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Maximum Age</th>
                        <td>
                            <span id="showMaximumAge">{{ $product->maximum_age }}</span>
                            <input type="number" name="maximum_age" class="form-control" id="validationMaximumAge" placeholder="maximum age"
                                step="1" min="1" max="255" value="{{ $product->maximum_age }}" data-value="{{ $product->maximum_age }}" hidden />
                            <div id="maximumAgeFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Start At</th>
                        <td>
                            <span id="showStartAt">{{ $product->start_at }}</span>
                            <input type="datetime-local" name="start_at" class="form-control" id="validationStartAt" placeholder="start at"
                                value="{{ $product->start_at }}" data-value="{{ $product->start_at }}" hidden />
                            <div id="startAtFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>End At</th>
                        <td>
                            <span id="showEndAt">{{ $product->end_at }}</span>
                            <input type="datetime-local" name="end_at" class="form-control" id="validationEndAt" placeholder="end at"
                                value="{{ $product->end_at }}" data-value="{{ $product->end_at }}" hidden />
                            <div id="endAtFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Quota</th>
                        <td>
                            <span id="showQuota">{{ $product->quota }}</span>
                            <input type="number" name="quota" class="form-control" id="validationQuota" placeholder="quota"
                                step="1" min="1" max="255" value="{{ $product->quota }}" data-value="{{ $product->quota }}" hidden required />
                            <div id="quotaFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </article>
        <article id="prices">
            <h3 class="fw-bold mb-2">Prices</h3>
            <div class="row g-3">
                <div class="col-md-2">Start At</div>
                <div class="col-md-2">Name</div>
                <div class="col-md-1">Price</div>
                <div class="col-md-2">Edit</div>
            </div>
            @foreach ($product->prices as $price)
                <form class="row g-3 priceForm" id="priceForm{{ $price->id }}" novalidate
                    action="{{ route('admin.admission-test.products.prices.update', ['product' => $product, 'price' => $price]) }}">
                    @csrf
                    @method('put')
                    <div class="col-md-2" id="showPriceStartAt{{ $price->id }}">{{ $price->start_at }}</div>
                    <input type="datetime-local" name="start_at" class="col-md-2" placeholder="start at" id="priceStartAtInput{{ $price->id }}"
                        value="{{ $price->start_at }}" data-value="{{ $price->start_at }}" hidden />
                    <div class="col-md-2" id="showPriceName{{ $price->id }}">{{ $price->name }}</div>
                    <input name="start_at" class="col-md-2" placeholder="name" max="255" id="priceNameInput{{ $price->id }}"
                        value="{{ $price->name }}" data-value="{{ $price->name }}" hidden />
                    <div class="col-md-1">{{ $price->price }}</div>
                    <span class="spinner-border spinner-border-sm priceLoader" id="priceLoader{{ $price->id }}" role="status" aria-hidden="true"></span>
                    <button class="btn btn-primary col-md-2" id="editPrice{{ $price->id }}" onclick="return false;" hidden>Edit</button>
                    <button class="btn btn-primary col-md-1 submitButton" id="savePrice{{ $price->id }}" hidden>Save</button>
                    <button class="btn btn-danger col-md-1" id="cancelEditPrice{{ $price->id }}" onclick="return false;" hidden>Cancel</button>
                    <button class="btn btn-danger col-md-2" id="savingPrice{{ $price->id }}" disabled hidden>Saving</button>
                </form>
            @endforeach
        </article>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/admissionTest/products/show.js')
@endpush
