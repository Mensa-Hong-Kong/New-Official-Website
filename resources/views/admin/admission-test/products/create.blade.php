@extends('layouts.app')

@section('main')
    <form id="form" method="POST" action="{{ route('admin.admission-test.products.store') }}" novalidate>
        <h2 class="fw-bold mb-2 text-uppercase">Create Admission Test Type</h2>
        @csrf
        <div class="form-outline mb-4">
            <div class="form-floating">
                <input name="name" class="form-control" id="validationName" placeholder="name"
                    maxlength="255" value="{{ old('name') }}" required />
                <label for="validationName">Name</label>
                <div id="nameFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
        </div>
        <div class="form-outline mb-4">
            <div class="form-floating">
                <input type="number" name="minimum_age" class="form-control" id="validationMinimumAge" placeholder="minimum age"
                    step="1" min="1" max="255" value="{{ old('minimum_age') }}" />
                <label for="validationMinimumAge">Minimum Age</label>
                <div id="minimumAgeFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
        </div>
        <div class="form-outline mb-4">
            <div class="form-floating">
                <input type="number" name="maximum_age" class="form-control" id="validationMaximumAge" placeholder="maximum age"
                    step="1" min="1" max="255" value="{{ old('maximum_age') }}" />
                <label for="validationMaximumAge">Maximum Age</label>
                <div id="maximumAgeFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
        </div>
        <div class="form-outline mb-4">
            <div class="form-floating">
                <input type="datetime-local" name="start_at" class="form-control" id="validationStartAt" placeholder="start at" value="{{ old('start_at') }}" />
                <label for="validationStartAt">Start At</label>
                <div id="startAtFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
        </div>
        <div class="form-outline mb-4">
            <div class="form-floating">
                <input type="datetime-local" name="end_at" class="form-control" id="validationEndAt" placeholder="end at" value="{{ old('end_at') }}" />
                <label for="validationEndAt">End At</label>
                <div id="endAtFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
        </div>
        <div class="form-outline mb-4">
            <div class="form-floating">
                <input type="number" name="quota" class="form-control" id="validationQuota" placeholder="quota"
                    step="1" min="1" max="255" value="{{ old('quota') }}" required />
                <label for="validationQuota">Quota</label>
                <div id="quotaFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
        </div>
        <div class="form-outline mb-4">
            <div class="form-floating">
                <input name="name" class="form-control" id="validationPriceName" placeholder="price name"
                    maxlength="255" value="{{ old('price_name') }}" />
                <label for="validationPriceName">Price Name</label>
                <div id="priceNameFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
        </div>
        <div class="form-outline mb-4">
            <div class="form-floating">
                <input type="number" name="maximum_age" class="form-control" id="validationPrice" placeholder="price"
                    step="1" min="1" max="65535" value="{{ old('price') }}" />
                <label for="validationPrice">Price</label>
                <div id="priceFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
        </div>
        <input type="submit" id="createButton" class="form-control btn btn-success" value="Create">
        <button class="form-control btn btn-success" id="creatingButton" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Creating...
        </button>
    </form>
@endsection

@push('after footer')
    @vite('resources/js/admin/admissionTest/products/create.js')
@endpush
