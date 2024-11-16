<?php

namespace App\Http\Controllers;

use App\Models\UserHasMobile;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class MobileController extends Controller
{
    public function verification(Request $request, UserHasMobile $mobile)
    {
        $user = $request->user();
        if($mobile->user_id != $user->id) {
            abort(403);
        }
        $mobile->sendVerifyWhatsapp();
        return ['message' => 'The verify code sent!'];
    }

    public function verify(Request $request, UserHasMobile $mobile)
    {
        if($mobile->user_id != $request->user()->id) {
            abort(403);
        }
        if($mobile->lastVerification->created_at >= now()->subMinutes(5)) {
            $mobile->sendVerifyWhatsapp();
            return response([
                'errors' => ['failed' => 'The verify code expired, the new verify code sent.'],
            ], 422);
        }
        if($mobile->lastVerification->verified_at) {
            return response([
                'success' => ['message' => 'The email verified.'],
            ], 200);
        }
        $mobile->lastVerification->update(['verified_at' => now()]);
        return response([
            'success' => ['message' => 'The email verifiy success.'],
        ], 200);
    }
}
