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

class CandidateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
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
                            $test->inTestingTimeRange() &&
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
                    } elseif ($user->hasSamePassportAlreadyQualificationOfMembership()) {
                        abort(409, 'The candidate has already been qualification for membership.');
                    } elseif ($user->hasOtherSamePassportUserTested($test)) {
                        abort(409, 'The candidate has other same passport user account tested.');
                    } elseif (
                        $user->lastAdmissionTest &&
                        $user->hasTestedWithinDateRange(
                            $test->testing_at->subMonths(
                                $user->lastAdmissionTest->type->interval_month
                            ), now(), $test
                        )
                    ) {
                        abort(409, "The candidate has admission test record within {$user->lastAdmissionTest->type->interval_month} months(count from testing at of this test sub {$user->lastAdmissionTest->type->interval_month} months to now).");
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
        $admissionTest->candidates()->attach($request->user->id);
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
            'has_same_passport' => $request->user->hasOtherUserSamePassportJoinedFutureTest(),
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $request->user]
            ),
            'in_testing_time_range' => $admissionTest->inTestingTimeRange(),
            'present_url' => route(
                'admin.admission-tests.candidates.present.update',
                [
                    'admission_test' => $admissionTest,
                    'candidate' => $request->user,
                ]
            ),
            'result_url' => route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $admissionTest,
                    'candidate' => $request->user,
                ]
            ),
            'delete_url' => route(
                'admin.admission-tests.candidates.destroy',
                [
                    'admission_test' => $admissionTest,
                    'candidate' => $request->user,
                ]
            ),
        ];
    }

    public function show(Request $request, AdmissionTest $admissionTest, User $candidate)
    {
        return view('admin.admission-tests.candidates.show')
            ->with('test', $admissionTest)
            ->with('user', $candidate)
            ->with('isPresent', $request->pivot->is_present);
    }

    public function edit(AdmissionTest $admissionTest, User $candidate)
    {
        return view('admin.admission-tests.candidates.edit')
            ->with('test', $admissionTest)
            ->with('user', $candidate)
            ->with(
                'genders', Gender::all()
                    ->pluck('name', 'id')
                    ->toArray()
            )->with(
                'passportTypes', PassportType::all()
                    ->pluck('name', 'id')
                    ->toArray()
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
            $candidate->notify(new PassAdmissionTest);
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
