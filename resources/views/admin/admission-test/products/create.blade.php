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
                <label for="validationMaximumAge">Minimum Age</label>
                <div id="maximumAgeFeedback" class="valid-feedback">
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
