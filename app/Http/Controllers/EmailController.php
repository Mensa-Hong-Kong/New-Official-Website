<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserHasEmail;

class EmailController extends Controller
{
    public function verification(Request $request, UserHasEmail $email)
    {
        $user = $request->user();
        if($email->user_id != $user->id) {
            abort(403);
        }
        $email->sendVerifyEmail();
        return ['message' => 'The verify code sent!'];
    }

    public function verify(Request $request, UserHasEmail $email)
    {
        if($email->user_id != $request->user()->id) {
            abort(403);
        }
        if($email->lastVerification->created_at >= now()->subMinutes(5)) {
            $email->sendVerifyEmail();
            return response([
                'errors' => ['failed' => 'The verify code expired, the new verify code sent.'],
            ], 422);
        }
        if($email->lastVerification->verified_at) {
            return response([
                'success' => ['message' => 'The mobile number verified.'],
            ], 200);
        }
        $email->lastVerification->update(['verified_at' => now()]);
        return response([
            'success' => ['message' => 'The mobile number verifiy success.'],
        ], 200);
    }
}
