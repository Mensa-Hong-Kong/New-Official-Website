@extends('layouts.app')

@section('main')
    <section class="container">
        <article>
            <h3 class="fw-bold mb-2">Info</h3>
            <table class="table">
                <tr>
                    <th>Testing At</th>
                    <td>{{ $test->testing_at }}</td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td>{{ $test->location->name }}</td>
                </tr>
                <tr>
                    <th>District</th>
                    <td>
                        {{ $test->location->address->district->name }},
                        {{ $test->location->address->district->area->name }}
                    </td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>{{ $test->location->address->address }}</td>
                </tr>
                <tr>
                    <th>Maximum Candidates</th>
                    <td>{{ $test->testing_at }}</td>
                </tr>
                <tr>
                    <th>Current Candidates</th>
                    <td>0</td>
                </tr>
                <tr>
                    <th>Is Public</th>
                    <td>{{ $test->is_public ? 'Public' : 'Private' }}</td>
                </tr>
            </table>
        </article>
    </section>
@endsection
