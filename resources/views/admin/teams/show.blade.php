@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">
            Team
        </h2>
        <h3 class="fw-bold mb-2">
            Info
            @can('Edit:Permission')
                <a class="btn btn-primary"
                    href="{{ route('admin.teams.edit', ['team' => $team]) }}">
                    Edit
                </a>
            @endcan
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
            @can('Edit:Permission')
                <a href="{{ route('admin.teams.roles.create', ['team' => $team]) }}"
                    class="btn btn-success">Create</a>
                <button class="btn btn-primary" id="editDisplayOrder">Edit Display Order</button>
                <button class="btn btn-primary" id="saveDisplayOrder" hidden>Save Display Order</button>
                <button class="btn btn-danger" id="cancelDisplayOrder" hidden>Cancel</button>
                <button class="btn btn-success" id="savingDisplayOrder" hidden disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Saving Display Order...
                </button>
            @endcan
        </h3>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    @can('Edit:Permission')
                        <th scope="col">Control</th>
                    @endcan
                </tr>
            </thead>
            <tbody id="tableBody">
                @foreach ($team->roles as $role)
                    <tr class="dataRow" id="dataRow{{ $role->id }}">
                        <th>{{ $role->name }}</th>
                        @can('Edit:Permission')
                            <td>
                                <a class="btn btn-primary"
                                    href="{{ route('admin.teams.roles.edit', ['team' => $team, 'role' => $role]) }}">
                                    Edit
                                </a>
                            </td>
                        @endcan
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection

@can('Edit:Permission')
    @push('after footer')
        @vite('resources/js/admin/teams/show.js')
    @endpush
@endcan
