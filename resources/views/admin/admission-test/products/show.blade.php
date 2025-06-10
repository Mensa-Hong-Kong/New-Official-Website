@extends('layouts.app')

@section('main')
    <section id="container">
    </section>
@endsection

@push('after footer')
    <script>
        let product = {{Js::from($product)}};
    </script>
    @vite('resources/js/admin/admissionTest/products/show.js')
@endpush
