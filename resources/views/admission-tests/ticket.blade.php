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
            @isset($qrCode)
                <tr>
                    <th>Ticket</th>
                </tr>
                <tr>
                    <td>
                        <img src="{{ $qrCode }}">
                    </td>
                </tr>
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
