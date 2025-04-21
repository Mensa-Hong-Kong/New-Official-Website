@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">Admission Test Products</h2>
        @if(count($products))
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Minimum Age</th>
                        <th scope="col">Maximum Age</th>
                        <th>Show</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td scope="row">{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->minimum_age }}</td>
                            <td>{{ $product->maximum_age }}</td>
                            <td>
                                <a href="{{ route('admin.admission-test.products.show', ['product' => $product]) }}"
                                    class="btn btn-primary">Show</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-danger" role="alert">
                No Result
            </div>
        @endif
    </section>
@endsection
