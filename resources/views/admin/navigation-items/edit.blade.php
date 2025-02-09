@extends('layouts.app')

@section('main')
    <section class="container">
        <form id="form" method="POST" action="{{ route('admin.navigation-items.update', ['navigation_item' => $item]) }}" novalidate>
            <h2 class="fw-bold mb-2 text-uppercase">Edit Navigation Item</h2>
            @method('put')
            @include('admin.navigation-items.form')
            <input type="submit" id="saveButton" class="form-control btn btn-primary" value="Save">
            <button class="form-control btn btn-primary" id="savingButton" type="button" disabled hidden>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Saving...
            </button>
        </form>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/navigationItems/edit.js')
@endpush
