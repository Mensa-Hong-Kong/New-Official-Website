@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">
            Navigation Items
            <button class="btn btn-primary" id="editDisplayOrder">Edit Display Order</button>
            <button class="btn btn-primary" id="saveDisplayOrder" hidden>Save Display Order</button>
            <button class="btn btn-danger" id="cancelDisplayOrder" hidden>Cancel</button>
            <button class="btn btn-success" id="savingDisplayOrder" hidden disabled>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Saving Display Order...
            </button>
        </h2>
        @if(count($items))
            @include('admin.navigation-items.navigation-items', ['items' => $items, 'id' => 0])
        @else
            <div class="alert alert-danger" role="alert">
                No Result
            </div>
        @endif
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/navigationItems/index.js')
@endpush
