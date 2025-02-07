@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">Navigation Items</h2>
        @if(count($items))
            @include('admin.navigation-items.navigation-items', ['items' => $items, 'isRoot' => true])
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
