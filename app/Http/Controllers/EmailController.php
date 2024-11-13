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
            abort(404);
        }
        $email->sendVerifyEmail();
        return ['message' => 'Verification link sent!'];
    }

    public function verify(Request $request, UserHasEmail $email, $verifyCode)
    {
        // ...
    }
}
