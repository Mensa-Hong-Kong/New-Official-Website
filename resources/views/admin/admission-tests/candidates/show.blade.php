@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="mb-2 fw-bold">
            Candidate
            <a class="btn btn-primary" id="editLink"
                href="{{ route('admin.admission-tests.candidates.edit', ['admission_test' => $test, 'candidate' => $user]) }}">Edit</a>
            <button class="btn btn-primary" id="disabledEditLink" hidden disabled>Edit</button>
        </h2>
        <table class="table">
            <tr>
                <th>Gender</th>
                <td>{{ $user->gender->name }}</td>
            </tr>
            <tr>
                <th>Family Name</th>
                <td>{{ $user->family_name }}</td>
            </tr>
            <tr>
                <th>Middle Name</th>
                <td>{{ $user->middle_name }}</td>
            </tr>
            <tr>
                <th>Given Name</th>
                <td>{{ $user->given_name }}</td>
            </tr>
            <tr>
                <th>Passport Type</th>
                <td>{{ $user->passportType->name }}</td>
            </tr>
            <tr>
                <th>Passport Number</th>
                <td @class([
                    'text-warning' => $user->hasOtherUserSamePassportJoinedFutureTest,
                    'text-danger' => $user->hasOtherSamePassportUserTested() ||
                        $user->hasSamePassportAlreadyQualificationOfMembership || (
                            $user->lastAdmissionTest &&
                            $user->hasTestedWithinDateRange(
                                $test->testing_at->subMonths($user->lastAdmissionTest->type->interval_month), now(), $test
                            )
                        ),
                ])>{{ $user->passport_number }}</td>
            </tr>
            <tr>
                <th>Is Present</th>
                <td>
                    <form id="presentForm" hidden method="POST"
                        action="{{ route('admin.admission-tests.candidates.present.update', ['admission_test' => $test, 'candidate'=> $user]) }}">
                        @csrf
                        @method("put")
                    </form>
                    <button name="status" id="presentButton" form="presentForm" value="{{ !$isPresent }}" @class([
                        'btn',
                        'btn-success' => $isPresent,
                        'btn-danger' => !$isPresent,
                    ])>{{ $isPresent ? 'Present' : 'Absent' }}</button>
                </td>
            </tr>
        </table>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/admissionTests/candidate/show.js')
@endpush
