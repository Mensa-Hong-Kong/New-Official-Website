@extends('layouts.app')

@section('main')
    <section class="container">
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
                    'text-warning' => $user->hasOtherUserSamePassportJoinedFutureTest(),
                    'text-danger' => $user->hasSamePassportTestedTwoTimes() ||
                        $user->hasSamePassportAlreadyQualificationOfMembership() ||
                        $user->hasSamePassportTestedWithinDateRange(
                            $test->testing_at->subMonths(6), now()
                        ),
                ])>{{ $user->passport_number }}</td>
            </tr>
        </table>
    </section>
@endsection
