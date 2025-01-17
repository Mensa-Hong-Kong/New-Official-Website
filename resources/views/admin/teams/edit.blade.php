@extends('layouts.app')

@section('main')
    <section class="container">
        <form id="form" method="POST" action="{{ route('admin.teams.update', ['team' => $team]) }}" novalidate>
            <h2 class="fw-bold mb-2 text-uppercase">Edit Team</h2>
            @method('put')
            @include('admin.teams.form')
            <input type="submit" id="createButton" class="form-control btn btn-primary" value="Save">
            <button class="form-control btn btn-primary" id="creatingButton" type="button" disabled hidden>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Saving...
            </button>
        </form>
    </section>
@endsection

