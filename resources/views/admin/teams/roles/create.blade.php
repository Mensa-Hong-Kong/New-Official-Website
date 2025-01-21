@extends('layouts.app')

@section('main')
    <section class="container">
        <form id="form" method="POST" action="{{ route('admin.teams.roles.store', ['team' => $team]) }}" novalidate>
            <h2 class="fw-bold mb-2 text-uppercase">Create Role For {{ $team->name }}</h2>
            @include('admin.teams.roles.form')
            <input type="submit" id="createButton" class="form-control btn btn-success" value="Create">
            <button class="form-control btn btn-success" id="creatingButton" type="button" disabled hidden>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Creating...
            </button>
        </form>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/teams/roles/create.js')
@endpush
