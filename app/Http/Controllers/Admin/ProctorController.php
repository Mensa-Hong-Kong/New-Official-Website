<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionTest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProctorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:Edit:Admission Test')];
    }

    public function store(Request $request, AdmissionTest $admissionTest)
    {
        $user = User::find($request->user_id);
        if(! $user) {
            return response([
                'errors' => ['user_id' => 'The selected user id is invalid.'],
            ], 422);
        }
        $admissionTest->proctors()->attach($user->id);

        return [
            'success' => 'Add proctor success',
            'user_id' => $user->id,
            'name' => $user->name,
        ];
    }
}
