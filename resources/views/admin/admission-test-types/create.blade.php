@extends('layouts.app')

@section('main')
    <form id="form" method="POST" action="{{ route('admin.admission-test-types.store') }}" novalidate>
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
                <input type="number" name="interval_month" class="form-control" id="validationIntervalMonth" placeholder="interval month"
                    step="1" min="0" max="60" value="{{ old('interval_month', 0) }}" required />
                <label for="validationIntervalMonth">Name</label>
                <div id="intervalMonthFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
        </div>
        <div class="form-outline mb-4">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="isActive" name="is_active"
                    @checked(old('is_active', true)) />
                <label class="form-check-label" for="isActive">Is Active</label>
            </div>
        </div>
        <div class="form-outline mb-4">
            <div class="form-floating">
                <select class="form-select" id="validationDisplayOrder" name="display_order" required>
                    <option value="" @selected(old('display_order', null) === null) disabled>Please display order</option>
                    @foreach ($types as $key => $value)
                        <option value="{{ $key }}" @selected($key === old('display_order', ''))>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
                <label for="validationDisplayOrder" class="form-label">Display Order</label>
                <div id="displayOrderFeedback" class="valid-feedback">
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
    @vite('resources/js/admin/admissionTestTypes/create.js')
@endpush
