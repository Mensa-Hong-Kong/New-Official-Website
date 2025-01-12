@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">Modules</h2>
        @if(count($modules))
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Display Name</th>
                        <th scope="col">Control</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($modules as $module)
                        <tr>
                            <th scope="row">{{ $module->name }}</th>
                            <td>
                                <span id="showDisplayName{{ $module->id }}">{{ $module->title }}</span>
                                <form id="updateDisplayNameForm{{ $module->id }}" method="POST" hidden novalidate
                                    action="{{ route('admin.modules.update', ['module' => $module]) }}">
                                    @csrf
                                    @method('put')
                                    <input class="form-control" id="displayNameInput{{ $module->id }}" pattern="(?!.*:).*"
                                        value="{{ $module->title }}" data-value="{{ $module->title }}" />
                                </form>
                            </td>
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
    @vite('resources/js/admin/module.js')
@endpush
