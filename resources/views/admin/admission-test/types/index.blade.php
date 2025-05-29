@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2">
            Admission Test Types
            <button class="btn btn-primary" id="editDisplayOrder">Edit Display Order</button>
            <button class="btn btn-primary" id="saveDisplayOrder" hidden>Save Display Order</button>
            <button class="btn btn-danger" id="cancelDisplayOrder" hidden>Cancel</button>
            <button class="btn btn-success" id="savingDisplayOrder" hidden disabled>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Saving Display Order...
            </button>
        </h2>
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
                <tbody id="tableBody">
                    @foreach ($types as $type)
                        <tr class="dataRow" id="dataRow{{ $type->id }}">
                            <td scope="row">{{ $type->id }}</td>
                            <td>{{ $type->name }}</td>
                            <td>{{ $type->interval_month }}</td>
                            <td>{{ $type->is_active ? 'Active' : 'Inactive' }}</td>
                            <td><a class="btn btn-primary" href="{{ route('admin.admission-test.types.edit', ['type' => $type]) }}">Edit</a></td>
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

@push('after footer')
    @vite('resources/js/admin/admissionTest/types/index.js')
@endpush
