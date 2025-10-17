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
            (new Middleware(
                function (Request $request, Closure $next) {
                    if (
                        ! $request->user()->can('View:User') ||
                        ! $request->user()->can('Edit:Admission Test')
                    ) {
                        abort(403);
                    }

                    return $next($request);
                }
            ))->only(['store', 'result', 'destroy']),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $test = $request->route('admission_test');
                    if ($test->testing_at <= now()) {
                        abort(410, 'Can not add candidate after than testing time.');
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
                        ->first();
                    if (! $pivot) {
                        abort(404);
                    }
                    $request->merge(['pivot' => $pivot]);

                    return $next($request);
                }
            ))->except('store'),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $test = $request->route('admission_test');
                    if (
                        ! (
                            $request->user()->can('View:User') &&
                            $request->user()->can('Edit:Admission Test')
                        ) && ! (
                            $test->inTestingTimeRange &&
                            in_array($request->user()->id, $test->proctors->pluck('id')->toArray())
                        )
                    ) {
                        abort(403);
                    }
                    if ($test->testing_at > now()->addHours(2)) {
                        abort(409, 'Could not access before than testing time 2 hours.');
                    }
                    if ($test->expect_end_at < now()->subHour()) {
                        abort(410, 'Could not access after than expect end time 1 hour.');
                    }

                    return $next($request);
                }
            ))->only(['show', 'edit', 'update', 'present']),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $user = $request->route('candidate');
                    $test = $request->route('admission_test');
                    if (in_array($request->pivot->is_pass, ['0', '1'])) {
                        abort(410, 'Cannot change exists result candidate present status.');
                    } elseif ($user->hasSamePassportAlreadyQualificationOfMembership) {
                        abort(409, 'The candidate has already been qualification for membership.');
                    } elseif (
                        $user->lastAttendedAdmissionTestOfOtherSamePassportUser &&
                        $user->lastAttendedAdmissionTestOfOtherSamePassportUser->id != $test->id
                    ) {
                        abort(409, 'The candidate has other same passport user account tested.');
                    } elseif (
                        $user->lastAttendedAdmissionTest &&
                        $user->lastAttendedAdmissionTest->id != $test->id &&
                        $user->lastAttendedAdmissionTest->testing_at
                            ->addMonths(
                                $user->lastAttendedAdmissionTest->type->interval_month
                            )->endOfDay() >= $test->testing_at
                    ) {
                        abort(409, "The candidate has admission test record within {$user->lastAttendedAdmissionTest->type->interval_month} months(count from testing at of this test sub {$user->lastAttendedAdmissionTest->type->interval_month} months to now).");
                    }

                    return $next($request);
                }
            ))->only('present'),
            (new Middleware(
                function (Request $request, Closure $next) {
                    if ($request->route('admission_test')->expect_end_at > now()) {
                        abort(409, 'Cannot add result before expect end time.');
                    }
                    if ($request->pivot->is_present) {
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
        if ($request->is_free) {
            $admissionTest->candidates()->attach($request->user->id);
        } else {
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

        return [
            'success' => 'The candidate create success',
            'user_id' => $request->user->id,
            'name' => $request->user->adornedName,
            'passport_type' => $request->user->passportType->name,
            'passport_number' => $request->user->passport_number,
            'has_other_same_passport_user_joined_future_test' => $request->user->hasOtherSamePassportUserJoinedFutureTest,
        ];
    }

    public function show(Request $request, AdmissionTest $admissionTest, User $candidate)
    {
        $admissionTest->makeHidden([
            'type_id', 'testing_at', 'expect_end_at',
            'location_id', 'address_id', 'maximum_candidates',
            'is_public', 'created_at', 'updated_at',
        ]);
        $candidate->load([
            'lastAttendedAdmissionTest' => function ($query) use ($admissionTest) {
                $query->with([
                    'type' => function ($query) {
                        $query->select(['id', 'interval_month']);
                    },
                ])->whereNot('test_id', $admissionTest->id);
            }, 'passportType' => function ($query) {
                $query->select(['id', 'name']);
            }, 'gender' => function ($query) {
                $query->select(['id', 'name']);
            },
        ]);
        $candidate->append([
            'adorned_name', 'has_other_same_passport_user_joined_future_test',
            'last_attended_admission_test_of_other_same_passport_user',
            'has_same_passport_already_qualification_of_membership',
        ]);
        $candidate->makeHidden([
            'username', 'member', 'family_name', 'middle_name', 'given_name',
            'gender_id', 'synced_to_stripe', 'created_at', 'updated_at',
        ]);
        $candidate->passportType->makeHidden('id');
        $candidate->gender->makeHidden('id');
        if ($candidate->lastAttendedAdmissionTest) {
            $candidate->lastAttendedAdmissionTest->makeHidden([
                'id', 'type_id', 'expect_end_at', 'address_id', 'location_id',
                'maximum_candidates', 'is_public', 'created_at', 'updated_at',
                'laravel_through_key',
            ]);
            $candidate->lastAttendedAdmissionTest->type->makeHidden('id');
        }

        return Inertia::render('Admin/AdmissionTests/Candidates/Show')
            ->with('test', $admissionTest)
            ->with('user', $candidate)
            ->with('isPresent', $request->pivot->is_present);
    }

    public function edit(AdmissionTest $admissionTest, User $candidate)
    {
        $candidate->makeHidden(['username', 'synced_to_stripe', 'created_at', 'updated_at', 'member']);

        return Inertia::render('Admin/AdmissionTests/Candidates/Edit')
            ->with('user', $candidate)
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
        if (in_array($request->pivot->is_pass, ['0', '1'])) {
            $candidate->notify(new RemovedAdmissionTestRecord($admissionTest, $request->pivot));
        } else {
            $candidate->notify(new CanceledAdmissionTestAppointment($admissionTest));
        }
        DB::commit();

        return ['success' => 'The candidate delete success!'];
    }

    public function present(StatusRequest $request, AdmissionTest $admissionTest, User $candidate)
    {
        $request->pivot->update(['is_present' => $request->status]);

        return [
            'success' => "The candidate of $candidate->adornedName changed to be ".($request->pivot->is_present ? 'present.' : 'absent.'),
            'status' => $request->pivot->is_present,
        ];
    }

    public function result(StatusRequest $request, AdmissionTest $admissionTest, User $candidate)
    {
        DB::beginTransaction();
        $request->pivot->update(['is_pass' => $request->status]);
        if ($request->pivot->is_pass) {
            $candidate->notify(new PassAdmissionTest($admissionTest));
        } else {
            $candidate->notify(new FailAdmissionTest($admissionTest));
        }
        DB::commit();

        return [
            'success' => "The candidate of $candidate->adornedName changed to be ".($request->pivot->is_pass ? 'pass.' : 'fail.'),
            'status' => $request->pivot->is_pass,
        ];
    }
}
