@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">
            Modules
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
        @if(count($modules))
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
                    @foreach ($modules as $module)
                        <tr class="dataRow" id="dataRow{{ $module->id }}">
                            <th scope="row">{{ $module->name }}</th>
                            <td>
                                <span id="showDisplayName{{ $module->id }}">{{ $module->title }}</span>
                                <form id="updateDisplayNameForm{{ $module->id }}" method="POST" hidden novalidate
                                    action="{{ route('admin.modules.update', ['module' => $module]) }}">
                                    @csrf
                                    @method('put')
                                    <input class="form-control" id="displayNameInput{{ $module->id }}" maxlength="255"
                                        value="{{ $module->title }}" data-value="{{ $module->title }}" />
                                </form>
                            </td>
                            @can('Edit:Permission')
                                <td>
                                    <div class="contactLoader" id="contactLoader{{ $module->id }}">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    </div>
                                    <button class="btn btn-primary" id="edit{{ $module->id }}" hidden>Edit</button>
                                    <button class="btn btn-primary submitButton" id="save{{ $module->id }}" form="updateDisplayNameForm{{ $module->id }}" hidden>Save</button>
                                    <button class="btn btn-danger" id="cancel{{ $module->id }}" hidden>Cancel</button>
                                    <button class="btn btn-success" id="saving{{ $module->id }}" hidden disabled>
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
        @vite('resources/js/admin/module.js')
    @endpush
@endcan
