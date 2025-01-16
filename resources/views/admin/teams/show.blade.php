@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">
            Team
        </h2>
        <h3 class="fw-bold mb-2">
            Info
        </h3>
        <table class="table">
            <tr>
                <th>Type</th>
                <td>{{ $team->type->name }}</td>
            </tr>
            <tr>
                <th>Name</th>
                <td>{{ $team->name }}</td>
            </tr>
        </table>
        <h3 class="fw-bold mb-2">
            Roles
        </h3>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($team->roles as $role)
                    <tr>
                        <th>{{ $role->name }}</th>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
