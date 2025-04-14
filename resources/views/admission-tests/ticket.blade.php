@extends('layouts.app')

@section('main')
    <section class="container">
        <h3 class="fw-bold mb-2">Admission Test Scheduled</h3>
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
                <td>
                    @if($test->location)
                        {{ $test->location->name }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Address</th>
                <td>
                    @if($test->address)
                        {{ $test->address->address }},
                        {{ $test->address->district->name }},
                        {{ $test->address->district->area->name }}
                    @else
                        Unknown
                    @endif
                </td>
            </tr>
            @isset($qrCode)
                <tr>
                    <th>Ticket</th>
                </tr>
                <tr>
                    <td>
                        <img src="{{ $qrCode }}">
                    </td>
                </tr>
            @else
                <tr>
                    <th>Is Present</th>
                    <td>
                        @if($admissionTest->is_present)
                            Yes
                        @elseif($admissionTest->expect_end_at < now()->subHour())
                            No
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Is Present</th>
                    <td>
                        @if($admissionTest->is_present)
                            Yes
                        @elseif($admissionTest->expect_end_at < now()->subHour())
                            No
                        @endif
                    </td>
                </tr>
                @if(!is_null($admissionTest->is_pass)))
                    <tr>
                        <th>Is Present</th>
                        <td>{{ $admissionTest->is_pass ? 'Yes' : 'Mo' }}</td>
                    </tr>
                @endif
            @endisset
        </table>
        @isset($qrCode)
            <div class="alert alert-danger" role="alert">
                <b>Remember:</b>
                <ol>
                    <li>Please bring your own pencil.</li>
                    <li>Please bring your own ticket QR code.</li>
                    <li>Please bring your own Hong Kong/Macau/(Mainland) Resident ID.</li>
                    <li>Candidates should arrive 20 minutes before the test session. Latecomers may be denied entry.'</li>
                </ol>
            </div>
        @endisset
    </section>
@endsection
