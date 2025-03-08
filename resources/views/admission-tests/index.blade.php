@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">Admission Tests</h2>
        @vite('resources/css/ckEditor.css')
        <article class="ck-content">
            {!! $contents['Info'] !!}
        </article>
        <article>
            <h3 class="fw-bold mb-2">Upcoming Admission Tests</h3>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                        <th scope="col">Location</th>
                        <th scope="col">Candidates</th>
                    </tr>
                </thead>
                @foreach ($tests as $test)
                    <tr>
                        <td scope="row">{{ $test->testing_at->format('Y-m-d') }}</td>
                        <td>{{ $test->testing_at->format("H:i") }}</td>
                        <td title="{{ $test->address->address }}, {{ $test->address->district->name }}, {{ $test->address->district->area->name }}">{{ $test->location->name }}</td>
                        <td>{{ $test->candidates_count }}/{{ $test->maximum_candidates }}</td>
                    </tr>
                @endforeach
            </table>
        </article>
        <article class="ck-content">
            {!! $contents['Remind'] !!}
        </article>
    </section>
@endsection
