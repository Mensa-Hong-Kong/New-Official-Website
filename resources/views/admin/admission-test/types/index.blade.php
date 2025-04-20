@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">Admission Test Type</h2>
        @if(count($types))
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Interval Month</th>
                        <th scope="col">Status</th>
                        <th scope="col">Edit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($types as $type)
                        <tr>
                            <td scope="row">{{ $type->id }}</td>
                            <td>{{ $type->name }}</td>
                            <td>{{ $type->interval_month }}</td>
                            <td>{{ $type->is_active ? 'Active' : 'Inactive' }}</td>
                            <td><a class="btn btn-primary" href="{{ route('admin.admission-test.types.edit', ['admission_test_type' => $type]) }}">Edit</a></td>
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
