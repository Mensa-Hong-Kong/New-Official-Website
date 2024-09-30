<?php

namespace App\Http\Controllers;

use App\Models\UserHasMobile;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function verification(Request $request, UserHasMobile $mobile)
    {
        $user = $request->user();
        if($mobile->user_id != $user->id) {
            abort(404);
        }
        $mobile->sendVerifyWhatsapp();
        return ['message' => 'Verification link sent!'];
    }

    public function verify(Request $request, UserHasMobile $mobile, $verifyCode)
    {
        // ...
    }
}
