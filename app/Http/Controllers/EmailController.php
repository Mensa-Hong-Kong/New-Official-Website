<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserHasEmail;

class EmailController extends Controller
{
    public function verification(Request $request, UserHasEmail $email)
    {
        // ...
    }

    public function verify(Request $request, UserHasEmail $email, $verifyCode)
    {
        // ...
    }
}
