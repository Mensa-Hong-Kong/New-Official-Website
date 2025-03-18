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
                        @if(!auth()->user() || !auth()->user()->hasQualificationOfMembership())
                            <th scope="col">Control</th>
                        @endif
                    </tr>
                </thead>
                @foreach ($tests as $test)
                    <tr>
                        <td scope="row">{{ $test->testing_at->format('Y-m-d') }}</td>
                        <td>{{ $test->testing_at->format("H:i") }}</td>
                        <td title="{{ $test->address->address }}, {{ $test->address->district->name }}, {{ $test->address->district->area->name }}">{{ $test->location->name }}</td>
                        <td>{{ $test->candidates_count }}/{{ $test->maximum_candidates }}</td>
                        @if(!auth()->user() || !auth()->user()->hasQualificationOfMembership())
                            <td>
                                @if(auth()->user() && auth()->user()->futureAdmissionTest)
                                    @if(auth()->user()->futureAdmissionTest->id == $test->id)
                                        <button class="btn btn-secondary">Cancel</button>
                                    @else
                                        @if(
                                            (auth()->user()->defaultEmail || auth()->user()->defaultMobile) &&
                                            $test->testing_at > now()->addDays(2)->endOfDay() &&
                                            auth()->user()->hasTestedWithinDateRange($test->testing_at->subMonths(6), now())
                                        )
                                            <a class="btn btn-danger" href="{{ route('admission-tests.candidates.create', ['admission_test' => $test]) }}">Reschedule</a>
                                        @else
                                            <button class="btn btn-secondary">Reschedule</button>
                                        @endif
                                    @endif
                                @else
                                    @if(
                                        !auth()->user() ||
                                        (
                                            (auth()->user()->defaultEmail || auth()->user()->defaultMobile) &&
                                            $test->testing_at > now()->addDays(2)->endOfDay() &&
                                            !auth()->user()->hasTestedWithinDateRange($test->testing_at->subMonths(6), now())
                                        )
                                    )
                                        <a class="btn btn-primary" href="{{ route('admission-tests.candidates.create', ['admission_test' => $test]) }}">Schedule</a>
                                    @else
                                        <button class="btn btn-secondary">Schedule</button>
                                    @endif
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </article>
        <article class="ck-content">
            {!! $contents['Remind'] !!}
        </article>
    </section>
@endsection
