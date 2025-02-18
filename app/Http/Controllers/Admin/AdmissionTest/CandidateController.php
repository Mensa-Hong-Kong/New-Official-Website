<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\CandidateRequest;
use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasCandidate;
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
                    if ($test->expect_end_at->addHour() < now()) {
                        abort(410, 'Can not change candidate after than expect end time one hour.');
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
            ))->only('show'),
        ];
    }

    public function store(CandidateRequest $request, AdmissionTest $admissionTest)
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
            'gender' => $request->user->gender->name,
            'name' => $request->user->name,
            'passport_type' => $request->user->passportType->name,
            'passport_number' => $request->user->passport_number,
            'has_same_passport' => $request->user->hasOtherUserSamePassportJoinedFutureTest(),
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $request->user]
            ),
        ];
    }

    public function show(AdmissionTest $admissionTest, User $candidate)
    {
        return view('admin.admission-tests.candidates.show')
            ->with('test', $admissionTest)
            ->with('user', $candidate);
    }
}
