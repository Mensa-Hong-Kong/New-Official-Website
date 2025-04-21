@extends('layouts.app')

@section('main')
    <section class="container">
        <article>
            <div>
                <h3 class="fw-bold mb-2">Info</h3>
                <table class="table">
                    <tr>
                        <th>Name</th>
                        <td>{{ $product->name }}</td>
                    </tr>
                    <tr>
                        <th>Minimum Age</th>
                        <td>{{ $product->minimum_age }}</td>
                    </tr>
                    <tr>
                        <th>Maximum Age</th>
                        <td>{{ $product->maximum_age }}</td>
                    </tr>
                </table>
            </div>
        </article>
    </section>
@endsection
