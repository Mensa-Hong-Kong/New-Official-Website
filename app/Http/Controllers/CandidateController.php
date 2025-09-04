<?php

namespace App\Http\Controllers;

use App\Models\AdmissionTest;
use App\Notifications\AdmissionTest\RescheduleAdmissionTest;
use App\Notifications\AdmissionTest\ScheduleAdmissionTest;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRMarkupHTML;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\EncryptHistoryMiddleware;
use Inertia\Inertia;

class CandidateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            (new Middleware(
                function (Request $request, Closure $next) {
                    $user = $request->user();
                    $now = now();
                    $admissionTest = $request->route('admission_test');
                    $errorReturn = redirect()->route('admission-tests.index');
                    if (! $request->route('admission_test')->is_public) {
                        return $errorReturn->withErrors(['message' => 'The admission test is private.']);
                    }
                    if (! $user->stripe) {
                        return $errorReturn->withErrors(['message' => 'We are creating you customer account on stripe, please try again in a few minutes.']);
                    }
                    if ($user->futureAdmissionTest && $user->futureAdmissionTest->id == $admissionTest->id) {
                        return redirect()->route('admission-tests.candidates.show', ['admission_test' => $admissionTest])
                            ->withErrors(['message' => 'You has already schedule this admission test.']);
                    }
                    if ($user->isActiveMember()) {
                        return $errorReturn->withErrors(['message' => 'You has already been member.']);
                    }
                    if ($user->hasQualificationOfMembership) {
                        return $errorReturn->withErrors(['message' => 'You has already been qualification for membership.']);
                    }
                    if ($user->hasSamePassportAlreadyQualificationOfMembership) {
                        return $errorReturn->withErrors(['message' => 'Your passport has already been qualification for membership.']);
                    }
                    if ($user->lastAttendedAdmissionTestOfOtherSamePassportUser) {
                        return $errorReturn->withErrors(['message' => 'You other same passport user account tested.']);
                    }
                    if (
                        $user->lastAttendedAdmissionTest &&
                        $user->lastAttendedAdmissionTest->testing_at
                            ->addMonths(
                                $user->lastAdmissionTest->type->interval_month
                            )->endOfDay() >= $now
                    ) {
                        return $errorReturn->withErrors(['message' => "You has admission test record within {$user->lastAttendedAdmissionTest->type->interval_month} months(count from testing at of this test sub {$user->lastAttendedAdmissionTest->type->interval_month} months to now)."]);
                    }
                    if ($admissionTest->testing_at <= now()->addDays(2)->endOfDay()) {
                        return $errorReturn->withErrors(['message' => 'Cannot register after than before testing date two days.']);
                    }
                    if ($admissionTest->candidates()->count() >= $admissionTest->maximum_candidates) {
                        return $errorReturn->withErrors(['message' => 'The admission test is fulled.']);
                    }

                    return $next($request);
                }
            ))->except('show'),
            (new Middleware(
                [
                    EncryptHistoryMiddleware::class,
                    function (Request $request, Closure $next) {
                        $user = $request->user();
                        $admissionTest = $request->route('admission_test');
                        if (! in_array($user->id, $admissionTest->candidates->pluck('id')->toArray())) {
                            $redirect = redirect()->route('admission-tests.index');
                            if (! $admissionTest->is_public) {
                                return $redirect->withErrors(['message' => 'You have no register this admission test and this test is private, please register other admission test.']);
                            }
                            if ($user->isActiveMember()) {
                                return $redirect->withErrors(['message' => 'You have no register this admission test and you has already been member.']);
                            }
                            if ($user->hasQualificationOfMembership) {
                                return $redirect->withErrors(['message' => 'You have no register this admission test and you has already been qualification for membership.']);
                            }
                            if ($user->hasSamePassportAlreadyQualificationOfMembership) {
                                return $redirect->withErrors(['message' => 'You have no register this admission test and your passport has already been qualification for membership.']);
                            }
                            if ($user->lastAttendedAdmissionTestOfOtherSamePassportUser) {
                                return $redirect->withErrors(['message' => 'You have no register this admission test and your passport has other same passport user account tested.']);
                            }
                            if (
                                $user->lastAttendedAdmissionTest &&
                                $user->lastAttendedAdmissionTest->testing_at
                                    ->addMonths(
                                        $user->lastAdmissionTest->type->interval_month
                                    )->endOfDay() >= now()
                            ) {
                                return $redirect->withErrors(['message' => "You have no register this admission test and You has admission test record within {$user->lastAdmissionTest->type->interval_month} months(count from testing at of this test sub {$user->lastAdmissionTest->type->interval_month} months to now)."]);
                            }
                            if ($admissionTest->testing_at <= now()->addDays(2)->endOfDay()) {
                                return $redirect->withErrors(['message' => 'You have no register this admission test and cannot register after than before testing date two days, please register other admission test.']);
                            }
                            if ($admissionTest->candidates()->count() < $admissionTest->maximum_candidates) {
                                return redirect()->route(
                                    'admission-tests.candidates.create',
                                    ['admission_test' => $admissionTest]
                                )->withErrors(['message' => 'You have no register this admission test, please register first.']);
                            }

                            return $redirect->withErrors(['message' => 'You have no register this admission test and this test is fulled, please register other admission test.']);
                        }

                        return $next($request);
                    },
                ]
            ))->only('show'),
        ];
    }

    public function create(Request $request, AdmissionTest $admissionTest)
    {
        $user = [
            'future_admission_test' => $request->user()->futureAdmissionTest ? [
                'id' => $request->user()->futureAdmissionTest->id,
            ] : null,
            'created_stripe_customer' => (bool) $request->user()->stripe,
            'default_email' => $request->user()->defaultEmail ? [
                'contact' => $request->user()->defaultEmail->contact,
            ] : null,
        ];
        $admissionTest->load(['address.district.area', 'location']);
        $admissionTest->address->district->area
            ->makeHidden(['id', 'display_order', 'created_at', 'updated_at']);
        $admissionTest->address->district
            ->makeHidden(['id', 'area_id', 'display_order', 'created_at', 'updated_at']);
        $admissionTest->address->makeHidden(['id', 'district_id', 'created_at', 'updated_at']);
        $admissionTest->location->makeHidden(['id', 'created_at', 'updated_at']);
        $admissionTest->makeHidden(['type_id', 'address_id', 'location_id', 'expect_end_at', 'is_public', 'created_at', 'updated_at']);

        return Inertia::render('AdmissionTests/Confirmation')
            ->with('test', $admissionTest)
            ->with('user', $user);
    }

    public function store(Request $request, AdmissionTest $admissionTest)
    {
        $user = $request->user();
        $redirect = redirect()->route('admission-tests.candidates.show', ['admission_test' => $admissionTest]);
        DB::beginTransaction();
        $admissionTest->candidates()->attach($user->id);
        if ($user->futureAdmissionTest) {
            $oldTest = clone $user->futureAdmissionTest;
            $oldTest->delete();
            $user->notify(new RescheduleAdmissionTest($user->futureAdmissionTest, $admissionTest));
            $success = 'Your reschedule request successfully, ';
        } else {
            $user->notify(new ScheduleAdmissionTest($admissionTest));
            $success = 'Your schedule request successfully, ';
        }
        if ($user->defaultEmail || $user->defaultMobile) {
            $success .= 'the new ticket will be to your default contact(s), you also can cap screen to save your ticket.';
        } else {
            $success .= 'because you have no default contact, please cap screen the ticket for worst case no network on test location of your phone. We suggest you add default contact(s) as soon as possible because if the test has any update that you will missing the notification.';
        }
        DB::commit();

        return $redirect->with('success', $success);
    }

    private function qrCode($test, $user)
    {
        $options = new QROptions;

        $options->version = 5;
        $options->outputInterface = QRMarkupHTML::class;
        $options->cssClass = 'qrcode';
        $options->moduleValues = [
            // finder
            QRMatrix::M_FINDER_DARK => '#A71111', // dark (true)
            QRMatrix::M_FINDER_DOT => '#A71111', // finder dot, dark (true)
            QRMatrix::M_FINDER => '#FFBFBF', // light (false)
            // alignment
            QRMatrix::M_ALIGNMENT_DARK => '#A70364',
            QRMatrix::M_ALIGNMENT => '#FFC9C9',
        ];

        $out = (new QRCode($options))->render(
            route(
                'admin.admission-tests.candidates.show', [
                    'admission_test' => $test,
                    'candidate' => $user,
                ]
            )
        );

        return $out;
    }

    public function show(Request $request, AdmissionTest $admissionTest)
    {
        $admissionTest->load(['address.district.area', 'location']);
        $admissionTest->address->district->area
            ->makeHidden(['id', 'display_order', 'created_at', 'updated_at']);
        $admissionTest->address->district
            ->makeHidden(['id', 'area_id', 'display_order', 'created_at', 'updated_at']);
        $admissionTest->address->makeHidden(['id', 'district_id', 'created_at', 'updated_at']);
        $admissionTest->location->makeHidden(['id', 'created_at', 'updated_at']);
        $admissionTest->makeHidden(['id', 'type_id', 'address_id', 'location_id', 'is_public', 'created_at', 'updated_at', 'candidates']);
        $candidate = $admissionTest->candidates()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $return = Inertia::render('AdmissionTests/Ticket')
            ->with('test', $admissionTest);
        if ($admissionTest->expect_end_at >= now()->subHour()) {
            $return = $return->with('qrCode', $this->qrCode($admissionTest, $request->user()));
        } else {
            $return = $return->with(
                'candidate', [
                    'pivot' => [
                        'is_present' => $candidate->pivot->is_present,
                        'is_pass' => $candidate->pivot->is_pass,
                    ],
                ]
            );
        }

        return $return;
    }
}
