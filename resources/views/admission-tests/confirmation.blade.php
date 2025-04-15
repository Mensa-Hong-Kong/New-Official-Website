@extends('layouts.app')

@section('main')
    <section class="container">
        <x-stripe-alert />
        <h3 class="fw-bold mb-2">{{auth()->user()->futureAdmissionTest ? 'Reschedule' : 'Schedule'}} Admission Tests</h3>
        <table>
            <tr>
                <th>Date</th>
                <td>{{ $test->testing_at->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <th>Time</th>
                <td>{{ $test->testing_at->format("H:i") }}</td>
            </tr>
            <tr>
                <th>Location</th>
                <td>{{ $test->location->name }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td>
                    {{ $test->address->address }},
                    {{ $test->address->district->name }},
                    {{ $test->address->district->area->name }}
                </td>
            </tr>
        </table>
        <form method="POST" action="{{ route('admission-tests.candidates.store', ['admission_test' => $test]) }}">
            @csrf
            @if(auth()->user()->futureAdmissionTest)
                <button class="btn btn-danger form-control">Reschedule</button>
            @else
                <button class="btn btn-primary form-control">Schedule</button>
            @endif
        </form>
    </section>
@endsection
