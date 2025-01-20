@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">
            Teams
        </h2>
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            @foreach ($types as $type)
                <li class="nav-item" role="presentation">
                    <button id="pills-team-type-{{ $type->id }}-tab teamTypeTab" type="button"
                        data-bs-toggle="pill" data-bs-target="#pills-team-type-{{ $type->id }}"
                        role="tab"aria-controls="pills-home" aria-selected="true"
                        @class([
                            'nav-link',
                            'active' => $loop->first,
                        ])>{{ $type->title ?? $type->name }}</button>
                </li>
            @endforeach
        </ul>
        <div class="tab-content" id="pills-tabContent">
            @foreach ($types as $type)
                <div id="pills-team-type-{{ $type->id }}" role="tabpanel"
                    aria-labelledby="pills-team-type-{{ $type->id }}-tab" tabindex="0"
                    @class([
                        'tab-pane',
                        'fade',
                        'show' => $loop->first,
                        'active' => $loop->first,
                    ])>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Control</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($type->teams as $team)
                                <tr id="row{{ $team->id }}">
                                    <th>{{ $team->name }}</th>
                                    <td>
                                        <a class="btn btn-primary showTeam" href="{{ route('admin.teams.show', ['team' => $team]) }}">
                                            Show
                                        </a>
                                        @can('Edit:Permission')
                                            <button class="btn btn-primary disabledShowTeam" disabled hidden>Show</button>
                                            <span class="spinner-border spinner-border-sm teamLoader" id="teamLoader{{ $team->id }}" role="status" aria-hidden="true"></span>
                                            <form method="POST" id="deleteTeamForm{{ $team->id }}" hidden
                                                action="{{ route('admin.teams.destroy', ['team' => $team]) }}">
                                                @csrf
                                                @method('delete')
                                            </form>
                                            <button class="btn btn-danger submitButton"form="deleteTeamForm{{ $team->id }}" id="deleteTeam{{ $team->id }}" hidden>Delete</button>
                                            <button class="btn btn-danger" id="deletingTeam{{ $team->id }}" data-name="{{ $team->name }}" hidden disabled>
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                Deleting...
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
          </div>
    </section>
@endsection

@can('Edit:Permission')
    @push('after footer')
        @vite('resources/js/admin/teams/index.js')
    @endpush
@endcan
