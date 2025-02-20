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
use App\Notifications\AssignAdmissionTest;
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
                    if (
                        ! AdmissionTestHasCandidate::where('test_id', $request->route('admission_test')->id)
                            ->where('user_id', $request->route('candidate')->id)
                            ->exists()
                    ) {
                        abort(404);
                    }
                    $test = $request->route('admission_test');
                    if ($test->testing_at > now()->addHours(2)) {
                        abort(409, 'Could not access before than testing time 2 hours.');
                    }
                    if ($test->expect_end_at < now()->subHour()) {
                        abort(410, 'Could not access after than expect end time 1 hour.');
                    }
                    if (
                        (
                            $request->user()->can('View:User') &&
                            $request->user()->can('Edit:Admission Test')
                        ) || (
                            $test->inTestingTimeRange() &&
                            in_array($request->user()->id, $test->proctors->pluck('id')->toArray())
                        )
                    ) {
                        return $next($request);
                    }
                    abort(403);
                }
            ))->except('store'),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $user = $request->route('candidate');
                    if ($user->hasSamePassportAlreadyQualificationOfMembership()) {
                        abort(409, 'The passport of user has already been qualification for membership.');
                    } elseif (
                        $user->hasSamePassportTestedWithinDateRange(
                            $request->route('admission_test')->testing_at->subMonths(6), now()
                        )
                    ) {
                        abort(409, 'The passport of user has admission test record within 6 months(count from testing at of this test sub 6 months to now).');
                    } elseif ($user->hasSamePassportTestedTwoTimes()) {
                        abort(409, 'The passport of user tested two times admission test.');
                    }

                    return $next($request);
                }
            ))->only('present'),
        ];
    }

    public function store(StoreRequest $request, AdmissionTest $admissionTest)
    {
        DB::beginTransaction();
        AdmissionTestHasCandidate::where('user_id', $request->user->id)
            ->whereHas(
                'test', function ($query) use ($request) {
                    $query->where('testing_at', '>', $request->now);
                }
            )->delete();
        $admissionTest->candidates()->attach($request->user->id);
        $request->user->notify(new AssignAdmissionTest($admissionTest));
        DB::commit();

        return [
            'success' => 'The candidate create success',
            'user_id' => $request->user->id,
            'name' => $request->user->name,
            'passport_type' => $request->user->passportType->name,
            'passport_number' => $request->user->passport_number,
            'has_same_passport' => $request->user->hasOtherUserSamePassportJoinedFutureTest(),
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $request->user]
            ),
            'in_testing_time_range' => $admissionTest->inTestingTimeRange(),
            'present_url' => route(
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => $admissionTest,
                    'candidate' => $request->user,
                ]
            ),
        ];
    }

    public function show(AdmissionTest $admissionTest, User $candidate)
    {
        return view('admin.admission-tests.candidates.show')
            ->with('test', $admissionTest)
            ->with('user', $candidate)
            ->with(
                'isPresent', AdmissionTestHasCandidate::where('test_id', $admissionTest->id)
                    ->where('user_id', $candidate->id)
                    ->first('is_present')
                    ->is_present
            );
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

    public function present(StatusRequest $request, AdmissionTest $admissionTest, User $candidate)
    {
        AdmissionTestHasCandidate::where('test_id', $admissionTest->id)
            ->where('user_id', $candidate->id)
            ->update(['is_present' => $request->status]);

        return [
            'success' => "The candidate of $candidate->name changed to be ".($request->status ? 'present.' : 'absent.'),
            'status' => $request->status,
        ];
    }
}
