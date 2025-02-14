<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\CandidateRequest;
use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasCandidate;
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
            new Middleware('permission:Edit:Admission Test'),
            new Middleware('permission:View:User'),
            (new Middleware(
                function (Request $request, Closure $next) {
                    if ($request->route('admission_test')->expect_end_at->addHour() < now()) {
                        abort(410, 'Can not change candidate after than expect end time one hour.');
                    }

                    return $next($request);
                }
            )),
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
}
