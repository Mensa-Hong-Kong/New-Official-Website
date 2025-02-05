@extends('layouts.app')

@section('main')
    <section class="container">
        <form id="form" method="POST" action="{{ route('admin.custom-pages.update', ['custom_page' => $page]) }}" novalidate>
            <h2 class="fw-bold mb-2 text-uppercase">Edit Custom Page</h2>
            @method('put')
            @include('admin.custom-pages.form')
            <input type="submit" id="saveButton" class="form-control btn btn-primary" value="Save">
            <button class="form-control btn btn-primary" id="savingButton" type="button" disabled hidden>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Saving...
            </button>
        </form>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/custom-pages/edit.js')
@endpush
