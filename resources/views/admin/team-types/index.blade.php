@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">
            Team Types
        </h2>
        @if(count($types))
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Display Name</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @foreach ($types as $type)
                        <tr class="dataRow" id="dataRow{{ $type->id }}">
                            <th scope="row">{{ $type->name }}</th>
                            <th scope="row">{{ $type->title }}</th>
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
