@extends('layouts.app')

@section('main')
    <form id="form" method="POST" action="{{ route('admin.admission-test-types.store') }}" novalidate>
        <h2 class="fw-bold mb-2 text-uppercase">Create Admission Test Type</h2>
        @include('admin.admission-test-types.form')
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
