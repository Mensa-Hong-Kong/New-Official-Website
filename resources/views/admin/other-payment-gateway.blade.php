@extends('layouts.app')

@section('main')
    <section class="container" id="container">
    </section>
@endsection

@push('after footer')
    <script>
        let paymentGateways = {{ Js::from($paymentGateways) }};
    </script>
    @vite('resources/js/admin/otherPaymentGateway.js')
@endpush
