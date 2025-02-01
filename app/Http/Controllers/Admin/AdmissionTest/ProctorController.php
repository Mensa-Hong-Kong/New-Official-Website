<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\ProctorRequest;
use App\Models\AdmissionTest;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProctorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:Edit:Admission Test'),
            new Middleware('permission:View:User'),
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
            'user_show_url' => route(
                'admin.users.show',
                ['user' => $user]
            ),
        ];
    }
}
