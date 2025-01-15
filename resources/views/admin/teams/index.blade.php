@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">
            Teams
        </h2>
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            @foreach ($types as $type)
                <li class="nav-item" role="presentation">
                    <button id="pills-team-type-{{ $type->id }}-tab" type="button"
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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($type->teams as $team)
                                <tr>
                                    <th>{{ $team->name }}</th>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
          </div>
    </section>
@endsection
