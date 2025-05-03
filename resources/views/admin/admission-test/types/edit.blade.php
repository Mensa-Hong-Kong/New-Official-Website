@extends('layouts.app')

@section('main')
    <form id="form" method="POST" action="{{ route('admin.admission-test.types.update', ['type' => $type]) }}" novalidate>
        <h2 class="fw-bold mb-2 text-uppercase">Create Admission Test Type</h2>
        @method('PUT')
        @include('admin.admission-test.types.form')
        <input type="submit" id="saveButton" class="form-control btn btn-primary" value="Save">
        <button class="form-control btn btn-primary" id="savingButton" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Saving...
        </button>
    </form>
@endsection

@push('after footer')
    @vite('resources/js/admin/admissionTest/types/edit.js')
@endpush
