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
        $admissionTest->proctors()->attach($request->user->id);

        return [
            'success' => 'The proctor create success',
            'user_id' => $request->user->id,
            'name' => $request->user->adornedName,
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $request->user]
            ),
            'update_proctor_url' => route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $admissionTest,
                    'proctor' => $request->user,
                ]
            ),
            'delete_proctor_url' => route(
                'admin.admission-tests.proctors.destroy',
                [
                    'admission_test' => $admissionTest,
                    'proctor' => $request->user,
                ]
            ),
        ];
    }

    public function update(ProctorRequest $request, AdmissionTest $admissionTest, User $proctor)
    {
        AdmissionTestHasProctor::where('test_id', $admissionTest->id)
            ->where('user_id', $proctor->id)
            ->update(['user_id' => $request->user->id]);

        return [
            'success' => 'The proctor update success',
            'user_id' => $request->user->id,
            'name' => $request->user->adornedName,
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $request->user]
            ),
            'update_proctor_url' => route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $admissionTest,
                    'proctor' => $request->user,
                ]
            ),
            'delete_proctor_url' => route(
                'admin.admission-tests.proctors.destroy',
                [
                    'admission_test' => $admissionTest,
                    'proctor' => $request->user,
                ]
            ),
        ];
    }

    public function destroy(AdmissionTest $admissionTest, User $proctor)
    {
        $admissionTest->proctors()->detach($proctor->id);

        return ['success' => 'The proctor delete success!'];
    }
}
