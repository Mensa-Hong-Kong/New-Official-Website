<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\CandidateRequest;
use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasCandidate;
use App\Models\User;
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
                    if($request->route('admission_test')->expect_end_at->addHour() < now()) {
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
        $user = User::find($request->user_id);
        $now = now();
        if (! $user) {
            return response([
                'errors' => ['user_id' => 'The selected user id is invalid.'],
            ], 422);
        }
        if(
            AdmissionTestHasCandidate::where('passport_type_id', $user->passport_type_id)
                ->where('passport_number', $user->passport_number)
                ->whereHas(
                    'test', function($query) use($admissionTest, $now) {
                        $query->whereBetween('testing_at', [$admissionTest->testing_at->subMonths(6), $now]);
                    }
                )->exists()
        ) {
            return response([
                'errors' => ['user_id' => 'The passport of selected user id has admission test record within 6 months(count from testing at of this test sub 6 months to now).'],
            ], 422);
        }
        AdmissionTestHasCandidate::where('user_id', $user->id)
            ->whereHas(
                'test', function($query) use($now) {
                    $query->where('testing_at', '>', $now);
                }
            )->delete();
        $admissionTest->candidates()->attach(
            $user->id, [
                'passport_type_id' => $user->passport_type_id,
                'passport_number' => $user->passport_number,
            ]
        );
        DB::commit();

        return [
            'success' => 'The candidate create success',
            'user_id' => $user->id,
            'name' => $user->name,
            'passport_type' => $user->passportType->name,
            'passport_number' => $user->passport_number,
            'has_same_passport' => AdmissionTestHasCandidate::whereNot('user_id', $user->id)
                ->where('passport_type_id', $user->passport_type_id)
                ->where('passport_number', $user->passport_number)
                ->exists(),
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $user]
            ),
        ];
    }
}
