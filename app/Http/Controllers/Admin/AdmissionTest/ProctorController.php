<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\ProctorRequest;
use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasProctor;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProctorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:Edit:Admission Test'),
            new Middleware('permission:View:User'),
            (new Middleware(
                function (Request $request, Closure $next) {
                    if (
                        AdmissionTestHasProctor::where('test_id', $request->route('admission_test')->id)
                            ->where('user_id', $request->route('proctor')->id)
                            ->exists()
                    ) {
                        return $next($request);
                    }
                    abort(404);
                }
            ))->except('store'),
        ];
    }

    public function store(ProctorRequest $request, AdmissionTest $admissionTest)
    {
        $user = User::find($request->user_id);
        if (! $user) {
            return response([
                'errors' => ['user_id' => 'The selected user id is invalid.'],
            ], 422);
        }
        $admissionTest->proctors()->attach($user->id);

        return [
            'success' => 'Add proctor success',
            'user_id' => $user->id,
            'name' => $user->name,
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $user]
            ),
            'update_proctor_url' => route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $admissionTest,
                    'proctor' => $user,
                ]
            ),
        ];
    }

    public function update(ProctorRequest $request, AdmissionTest $admissionTest, User $proctor)
    {
        $user = User::find($request->user_id);
        if (! $user) {
            return response([
                'errors' => ['user_id' => 'The selected user id is invalid.'],
            ], 422);
        }
        AdmissionTestHasProctor::where('test_id', $admissionTest->id)
            ->where('user_id', $proctor->id)
            ->update(['user_id' => $user->id]);

        return [
            'success' => 'Update proctor success',
            'user_id' => $user->id,
            'name' => $user->name,
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $user]
            ),
            'update_proctor_url' => route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $admissionTest,
                    'proctor' => $user,
                ]
            ),
        ];
    }
}
