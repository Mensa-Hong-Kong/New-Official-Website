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
        <article>
            <h3 class="fw-bold mb-2">Prices</h3>
            <div class="row g-3">
                <div class="col-md-2">Start At</div>
                <div class="col-md-2">Name</div>
                <div class="col-md-1">Price</div>
            </div>
            @foreach ($product->prices as $price)
                <div class="row g-3">
                    <div class="col-md-2">{{ $price->start_at }}</div>
                    <div class="col-md-2">{{ $price->name }}</div>
                    <div class="col-md-1">{{ $price->price }}</div>
                </div>
            @endforeach
        </article>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/admissionTest/products/show.js')
@endpush
