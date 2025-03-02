@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">Admission Tests</h2>
        <article>
            <div class="alert alert-primary" role="alert">
                To qualify for membership, applicants must score in the top 2% of the population on the admission test.
                <br>
                There is no other basis for membership, whoever you are, whatever you do.
                <br><br>
                No preparation is needed for the test, but you <span class="fw-bolder">must be aged 14 or over</span> to take the test and <span class="fw-bolder">residing in Hong Kong</span>.
                <br>
                If you are <span class="fw-bolder text-decoration-underline">under 14</span> and have already taken a qualified IQ test, you may apply for membership using prior evidence.
                <br><br><br>
                For enquiries regarding <span class="fw-bolder text-decoration-underline">admission test</span>, please contact us at <a class="fw-bolder alert-link link-underline link-underline-opacity-0 link-underline-opacity-0-hover" href="mailto:test@mensa.org.hk">test@mensa.org.hk</a>.
                <br>
                For enquiries regarding <span class="fw-bolder text-decoration-underline">application for admission via prior evidence</span> (for those under 14 years old), please contact us at <a class="fw-bolder alert-link link-underline link-underline-opacity-0 link-underline-opacity-0-hover" href="mailto:admission@mensa.org.hk">admission@mensa.org.hk</a>.
            </div>
            <div class="alert alert-danger" role="alert">
                Note: please read the rules below before registering for the test
            </div>
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
        <article>
            <div class="alert alert-dark" role="alert">
                <span class="fw-bolder">Notes</span>
                <ul>
                    <li>Admission test fee: $300</li>
                    <li>Reservation is required three days (72 hours) before the test date, and admission is on a first-come-first-served basis. No walk-ins are allowed.</li>
                    <li>Each candidate is permitted to reschedule his/her test date up to TWO times. There will be an administrative charge of HK$200 for each subsequent reschedulling of test date.</li>
                    <li>No-show without a valid reason and evidence will incur an administrative charge of HK$200 for each subsequent rescheduling of the test date.</li>
                    <li>All tests take place in Central. You will receive a confirmation email with the exact venue of the test only after you have (1) paid for the test AND (2) submitted your personal information. </li>
                    <li>All admission tests are on Saturdays at 2:30 p.m. HK time (save for extra sessions during summer months, which will be at 3:30 p.m.). The time and date of the admission tests will appear differently if you are viewing this at a different time zone. </li>
                    <li>Candidates should arrive 20 minutes before the test session. Latecomers may be denied entry.</li>
                    <li>Please bring your own (1) <span class="fw-bolder text-decoration-underline">pencil</span>, (2) <span class="fw-bolder text-decoration-underline">ticket QR code</span>, and your (3) <span class="fw-bolder text-decoration-underline">Hong Kong/Macau/(Mainland) Resident ID card</span>. Other identity documentation will NOT be accepted unless special permission is obtained beforehand.</li>
                    <li>If you are qualified to join Mensa and are a Hong Kong resident, the membership dues are HK$400 per annum ($200 if you are under 21 when fees become due).</li>
                    <li>If you are qualified but are NOT a Hong Kong resident, you will be regstered as the direct member of mensa International.</li>
                    <li>If you do not qualify for membership for the first time, you are entitled to re-take the test free of charge once at any time between 6 months and 18 months after the date of your test, retaking the test within 6 months of the first attempt is strictly prohibited under any circumstances.</li>
                    <li>A person is subject to a maximum of 2 attempts in the admission test of Hong Kong Mensa in a lifetime. Any further attempts shall be disqualified immediately with no refund of the test fee.</li>
                    <li>You will be notified when the result is out. Since this test only serves as an entry test for Hong Kong Mensa, it should NOT be taken as a comprehensive IQ test. The exact score will not be disclosed. You will only be informed about whether you are qualified for membership.  </li>
                    <li>There will be no refund of any paid test fees.</li>
                </ul>
            </div>
            <div class="alert alert-danger text-center" role="alert">
                Mensa Hong Kong reserves the right to update the rules without prior notice. Any changes will take effect immediately upon posting on this website. Mensa Hong Kong reserves the right to the final interpretation of the admission test rules.
            </div>
        </article>
    </section>
@endsection
