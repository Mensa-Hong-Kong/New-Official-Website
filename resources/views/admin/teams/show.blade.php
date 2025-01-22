@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">
            Team
        </h2>
        <h3 class="fw-bold mb-2">
            Info
            @can('Edit:Permission')
                <a class="btn btn-primary" id="editTeam"
                    href="{{ route('admin.teams.edit', ['team' => $team]) }}">
                    Edit
                </a>
                <button class="btn btn-primary" id="disabledEditTeam" disabled hidden>Edit</button>
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
                                <a class="btn btn-primary editRole"
                                    href="{{ route('admin.teams.roles.edit', ['team' => $team, 'role' => $role]) }}">
                                    Edit
                                </a>
                                <button class="btn btn-primary disabledEditRole" disabled hidden>Edit</button>
                                <span class="spinner-border spinner-border-sm roleLoader" id="roleLoader{{ $role->id }}" role="status" aria-hidden="true"></span>
                                <form method="POST" id="deleteRoleForm{{ $role->id }}" hidden
                                    action="{{ route('admin.teams.roles.destroy', ['team' => $team, 'role' => $role]) }}">
                                    @csrf
                                    @method('delete')
                                </form>
                                <button class="btn btn-danger submitButton" form="deleteRoleForm{{ $role->id }}" id="deleteRole{{ $role->id }}" data-name="{{ $role->name }}"  hidden>Delete</button>
                                <button class="btn btn-danger" id="deletingRole{{ $role->id }}" hidden disabled>
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Deleting...
                                </button>
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
