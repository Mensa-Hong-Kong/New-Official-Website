@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">
            Team Types
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
                        <th scope="col">Name</th>
                        <th scope="col">Display Name</th>
                        <th scope="col">Control</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @foreach ($types as $type)
                        <tr class="dataRow" id="dataRow{{ $type->id }}">
                            <th scope="row">{{ $type->name }}</th>
                            <td scope="row">
                                <span id="showDisplayName{{ $type->id }}">{{ $type->title }}</span>
                                <form id="updateDisplayNameForm{{ $type->id }}" method="POST" hidden novalidate
                                    action="{{ route('admin.team-types.update', ['team_type' => $type]) }}">
                                    @csrf
                                    @method('put')
                                    <input class="form-control" id="displayNameInput{{ $type->id }}" pattern="(?!.*:).*"
                                        value="{{ $type->title }}" data-value="{{ $type->title }}" />
                                </form>
                            </td>
                            <td>
                                <div class="contactLoader" id="contactLoader{{ $type->id }}">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                </div>
                                <button class="btn btn-primary" id="edit{{ $type->id }}" hidden>Edit</button>
                                <button class="btn btn-primary submitButton" id="save{{ $type->id }}" form="updateDisplayNameForm{{ $type->id }}" hidden>Save</button>
                                <button class="btn btn-danger" id="cancel{{ $type->id }}" hidden>Cancel</button>
                                <button class="btn btn-success" id="saving{{ $type->id }}" hidden disabled>
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Saving...
                                </button>
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

@push('after footer')
    @vite('resources/js/admin/teamType.js')
@endpush
