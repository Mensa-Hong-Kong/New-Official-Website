@extends('layouts.app')

@section('main')
    <section class="container">
        <form id="form" method="POST" action="{{ route('admin.teams.roles.update', ['team' => $team, 'role' => $role]) }}" novalidate>
            <h2 class="fw-bold mb-2 text-uppercase">Edit Role For {{ $team->name }}</h2>
            @include('admin.teams.roles.form')
            <input type="submit" id="saveButton" class="form-control btn btn-primary" value="Save">
            <button class="form-control btn btn-primary" id="savingButton" type="button" disabled hidden>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Saving...
            </button>
        </form>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/teams/roles/edit.js')
@endpush
