<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\Candidate\StoreRequest;
use App\Http\Requests\Admin\AdmissionTest\Candidate\UpdateRequest;
use App\Http\Requests\StatusRequest;
use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasCandidate;
use App\Models\Gender;
use App\Models\PassportType;
use App\Models\User;
use App\Notifications\AdmissionTest\Admin\AssignAdmissionTest;
use App\Notifications\AdmissionTest\Admin\CanceledAdmissionTestAppointment;
use App\Notifications\AdmissionTest\Admin\FailAdmissionTest;
use App\Notifications\AdmissionTest\Admin\PassAdmissionTest;
use App\Notifications\AdmissionTest\Admin\RemovedAdmissionTestRecord;
use App\Notifications\AdmissionTest\Admin\RescheduleAdmissionTest;
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
            (new Middleware(EncryptHistoryMiddleware::class))->only(['show', 'edit']),
            (new Middleware('permission:Edit:Admission Test Candidate'))->only(['store', 'destroy']),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $permissions = ['Edit:Admission Test Candidate'];
                    $test = $request->route('admission_test');
                    $test->append('current_user_is_proctor');
                    if ($request->route()->getActionMethod() == 'show') {
                        $permissions[] = 'View:Admission Test Candidate';
                    }
                    if ($request->user()->canAny($permissions)) {
                        return $next($request);
                    }
                    if ($test->current_user_is_proctor) {
                        if ($test->testing_at > now()->addHours(2)) {
                            abort(409, 'Could not access before than testing time 2 hours.');
                        }
                        if ($test->expect_end_at < now()->subHour()) {
                            abort(410, 'Could not access after than expect end time 1 hour.');
                        }

                        return $next($request);
                    } else {
                        abort(403);
                    }
                }
            ))->except(['store', 'destroy', 'result']),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $test = $request->route('admission_test');
                    if ($test->testing_at <= now()->addDays(2)->endOfDay()) {
                        abort(410, 'Cannot add candidate after than before testing date two days.');
                    }
                    if ($test->candidates()->count() >= $test->maximum_candidates) {
                        return response(['errors' => ['user_id' => 'The admission test is fulled.']], 422);
                    }

                    return $next($request);
                }
            ))->only('store'),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $pivot = AdmissionTestHasCandidate::where('test_id', $request->route('admission_test')->id)
                        ->where('user_id', $request->route('candidate')->id)
                        ->firstOrFail();
                    $request->merge(['pivot' => $pivot]);

                    return $next($request);
                }
            ))->except(['store', 'result']),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $user = $request->route('candidate');
                    $test = $request->route('admission_test');
                    if ($test->type->minimum_age && $test->type->minimum_age > floor($user->ageForPsychology)) {
                        abort(410, 'The candidate age less than test minimum age limit.');
                    } elseif ($test->type->maximum_age && $test->type->maximum_age < floor($user->ageForPsychology)) {
                        abort(410, 'The candidate age greater than test maximum age limit.');
                    } elseif ($request->pivot->is_pass !== null) {
                        abort(410, 'Cannot change exists result candidate present status.');
                    } elseif ($user->hasSamePassportAlreadyQualificationOfMembership) {
                        abort(409, 'The candidate has already been qualification for membership.');
                    } elseif ($user->lastAttendedAdmissionTestOfOtherSamePassportUser) {
                        if ($user->lastAttendedAdmissionTestOfOtherSamePassportUser->id != $test->id) {
                            abort(409, 'The candidate has other same passport user account attended admission test.');
                        } else {
                            abort(409, 'The candidate has other same passport user account attended this test.');
                        }
                    } elseif (
                        $user->lastAttendedAdmissionTest &&
                        $user->lastAttendedAdmissionTest->id != $test->id &&
                        $user->lastAttendedAdmissionTest->testing_at
                            ->addMonths(
                                $user->lastAttendedAdmissionTest->type->interval_month
                            )->endOfDay() >= $test->testing_at
                    ) {
                        abort(409, "The candidate has admission test record within {$user->lastAttendedAdmissionTest->type->interval_month} months(count from testing at of this test sub {$user->lastAttendedAdmissionTest->type->interval_month} months to now).");
                    } elseif (
                        $user->hasUnusedQuotaAdmissionTestOrder &&
                        $user->hasUnusedQuotaAdmissionTestOrder->lastTest->id == $test->id
                    ) {
                        if (
                            $user->hasUnusedQuotaAdmissionTestOrder->minimum_age &&
                            $user->hasUnusedQuotaAdmissionTestOrder->minimum_age > floor($user->countAge($user->hasUnusedQuotaAdmissionTestOrder->created_at))
                        ) {
                            abort(409, 'The candidate age less than the last order age limit.');
                        } elseif (
                            $user->hasUnusedQuotaAdmissionTestOrder->maximum_age &&
                            $user->hasUnusedQuotaAdmissionTestOrder->maximum_age < floor($user->countAge($user->hasUnusedQuotaAdmissionTestOrder->created_at))
                        ) {
                            abort(409, 'The candidate age greater than the last order age limit.');
                        }
                    }

                    return $next($request);
                }
            ))->only('present'),
            (new Middleware(
                function (Request $request, Closure $next) {
                    if (! $request->user()->can('Edit:Admission Test Result')) {
                        abort(403);
                    }
                    $test = $request->route('admission_test');
                    $pivot = AdmissionTestHasCandidate::with('candidate')
                        ->where('test_id', $test->id)
                        ->where('seat_number', $request->route('seat_number'))
                        ->firstOrFail();
                    if ($test->expect_end_at > now()) {
                        abort(409, 'Cannot add result before expect end time.');
                    }
                    if ($pivot->is_present) {
                        $request->merge(['pivot' => $pivot]);

                        return $next($request);
                    }
                    abort(409, 'Cannot add result to absent candidate.');
                }
            ))->only('result'),
        ];
    }

    public function store(StoreRequest $request, AdmissionTest $admissionTest)
    {
        DB::beginTransaction();
        $return = [
            'success' => 'The candidate create success',
            'user_id' => $request->user->id,
            'name' => $request->user->adornedName,
            'birthday' => $request->user->birthday,
            'passport_type' => $request->user->passportType->name,
            'passport_number' => $request->user->passport_number,
            'has_other_same_passport_user_joined_future_test' => $request->user->hasOtherSamePassportUserJoinedFutureTest,
        ];
        if ($admissionTest->is_free || $request->is_free) {
            if (! $admissionTest->is_free) {
                $return['is_free'] = true;
            }
            $admissionTest->candidates()->attach($request->user->id);
        } else {
            $return['is_free'] = false;
            $admissionTest->candidates()->attach(
                $request->user->id,
                [
                    'order_id' => $request->user
                        ->hasUnusedQuotaAdmissionTestOrder
                        ->id,
                ]
            );
        }
        switch ($request->function) {
            case 'schedule':
                $request->user->notify(new AssignAdmissionTest($admissionTest));
                break;
            case 'reschedule':
                $oldTest = clone $request->user->futureAdmissionTest;
                $request->user->futureAdmissionTest->delete();
                $request->user->notify(new RescheduleAdmissionTest($oldTest, $admissionTest));
                break;
        }
        DB::commit();

        return $return;
    }

    public function show(Request $request, AdmissionTest $admissionTest, User $candidate)
    {
        $admissionTest->makeHidden([
            'id', 'type_id', 'location_id', 'address_id', 'maximum_candidates',
            'is_free', 'is_public', 'created_at', 'updated_at',
        ]);
        $candidate->load([
            'passportType:id,name',
            'gender:id,name',
            'lastAttendedAdmissionTest' => function ($query) use ($admissionTest) {
                $query->select(
                    array_map(
                        function ($column) use ($query) {
                            return $query->getRelated()->qualifyColumn($column);
                        },
                        ['id', 'testing_at']
                    )
                )->with('type:id,interval_month')->whereNot('test_id', $admissionTest->id);
            },
        ]);
        $candidate->append([
            'has_other_same_passport_user_joined_future_test',
            'has_other_same_passport_user_attended_admission_test',
            'has_same_passport_already_qualification_of_membership',
        ]);
        $candidate->makeHidden([
            'username', 'member', 'gender_id', 'passport_type_id',
            'address_id',  'synced_to_stripe', 'created_at', 'updated_at',
        ]);
        $candidate->passportType->makeHidden('id');
        $candidate->gender->makeHidden('id');
        if ($candidate->lastAttendedAdmissionTest) {
            $candidate->lastAttendedAdmissionTest
                ->makeHidden(['id', 'laravel_through_key']);
            $candidate->lastAttendedAdmissionTest->type
                ->makeHidden('id');
        }

        return Inertia::render('Admin/AdmissionTests/Candidates/Show')
            ->with('test', $admissionTest)
            ->with('candidate', $candidate)
            ->with('seatNumber', $request->pivot->seat_number)
            ->with('isPresent', $request->pivot->is_present)
            ->with('hasResult', $request->pivot->is_pass !== null);
    }

    public function edit(AdmissionTest $admissionTest, User $candidate)
    {
        $candidate->makeHidden(['username', 'synced_to_stripe', 'created_at', 'updated_at', 'member']);

        return Inertia::render('Admin/AdmissionTests/Candidates/Edit')
            ->with('candidate', $candidate)
            ->with(
                'passportTypes', PassportType::all()
                    ->pluck('name', 'id')
                    ->toArray()
            )->with(
                'genders', Gender::all()
                    ->pluck('name', 'id')
                    ->toArray()
            )->with(
                'maxBirthday', now()
                    ->subYears(2)
                    ->format('Y-m-d')
            );
    }

    public function update(UpdateRequest $request, AdmissionTest $admissionTest, User $candidate)
    {
        DB::beginTransaction();
        $gender = $candidate->gender->updateName($request->gender);
        $candidate->update([
            'family_name' => $request->family_name,
            'middle_name' => $request->middle_name,
            'given_name' => $request->given_name,
            'gender_id' => $gender->id,
            'passport_type_id' => $request->passport_type_id,
            'passport_number' => $request->passport_number,
            'birthday' => $request->birthday,
        ]);
        DB::commit();

        return redirect()->route(
            'admin.admission-tests.candidates.show',
            [
                'admission_test' => $admissionTest,
                'candidate' => $candidate,
            ]
        );
    }

    public function destroy(Request $request, AdmissionTest $admissionTest, User $candidate)
    {
        DB::beginTransaction();
        $admissionTest->candidates()->detach($candidate->id);
        if ($request->pivot->is_pass === null) {
            $candidate->notify(new CanceledAdmissionTestAppointment($admissionTest));
        } else {
            $candidate->notify(new RemovedAdmissionTestRecord($admissionTest, $request->pivot));
        }
        DB::commit();

        return ['success' => 'The candidate delete success!'];
    }

    public function present(StatusRequest $request, AdmissionTest $admissionTest, User $candidate)
    {
        $request->pivot->update(['is_present' => (bool) $request->status]);

        return [
            'success' => "The candidate of $candidate->adornedName changed to be ".($request->pivot->is_present ? 'present.' : 'absent.'),
            'status' => $request->pivot->is_present,
        ];
    }

    public function result(StatusRequest $request, AdmissionTest $admissionTest, int $seatNumber)
    {
        DB::beginTransaction();
        $request->pivot->update(['is_pass' => (bool) $request->status]);
        if ($request->pivot->is_pass) {
            $request->pivot->candidate->notify(new PassAdmissionTest($admissionTest));
        } else {
            $request->pivot->candidate->notify(new FailAdmissionTest($admissionTest));
        }
        DB::commit();

        return [
            'success' => "The candidate of {$request->pivot->candidate->adornedName} changed to be ".($request->pivot->is_pass ? 'pass.' : 'fail.'),
            'status' => $request->pivot->is_pass,
        ];
    }
}
