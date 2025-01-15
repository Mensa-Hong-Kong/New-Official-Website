@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">
            Permissions
            @can('Edit:Permission')
                <button class="btn btn-primary" id="editDisplayOrder">Edit Display Order</button>
                <button class="btn btn-primary" id="saveDisplayOrder" hidden>Save Display Order</button>
                <button class="btn btn-danger" id="cancelDisplayOrder" hidden>Cancel</button>
                <button class="btn btn-success" id="savingDisplayOrder" hidden disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Saving Display Order...
                </button>
            @endcan
        </h2>
        @if(count($permissions))
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Display Name</th>
                        @can('Edit:Permission')
                            <th scope="col">Control</th>
                        @endcan
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @foreach ($permissions as $permission)
                        <tr class="dataRow" id="dataRow{{ $permission->id }}">
                            <th scope="row">{{ $permission->name }}</th>
                            <td>
                                <span id="showDisplayName{{ $permission->id }}">{{ $permission->title }}</span>
                                <form id="updateDisplayNameForm{{ $permission->id }}" method="POST" hidden novalidate
                                    action="{{ route('admin.permissions.update', ['permission' => $permission]) }}">
                                    @csrf
                                    @method('put')
                                    <input class="form-control" id="displayNameInput{{ $permission->id }}" pattern="(?!.*:).*"
                                        value="{{ $permission->title }}" data-value="{{ $permission->title }}" />
                                </form>
                            </td>
                            @can('Edit:Permission')
                                <td>
                                    <div class="contactLoader" id="contactLoader{{ $permission->id }}">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    </div>
                                    <button class="btn btn-primary" id="edit{{ $permission->id }}" hidden>Edit</button>
                                    <button class="btn btn-primary submitButton" id="save{{ $permission->id }}" form="updateDisplayNameForm{{ $permission->id }}" hidden>Save</button>
                                    <button class="btn btn-danger" id="cancel{{ $permission->id }}" hidden>Cancel</button>
                                    <button class="btn btn-success" id="saving{{ $permission->id }}" hidden disabled>
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </button>
                                </td>
                            @endcan
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

@can('Edit:Permission')
    @push('after footer')
        @vite('resources/js/admin/permission.js')
    @endpush
@endcan
