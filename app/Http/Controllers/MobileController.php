<?php

namespace App\Http\Controllers;

use App\Models\UserHasMobile;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function verification(Request $request, UserHasMobile $mobile)
    {
        // ...
    }

    public function verify(Request $request, UserHasMobile $mobile, $verifyCode)
    {
        // ...
    }
}
