<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\Gender;
use App\Models\PassportType;
use App\Models\User;
use App\Models\UserHasEmail;
use App\Models\UserHasMobile;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function create()
    {
        return view('authentication.register')
            ->with(
                'genders', Gender::all()
                    ->pluck('name', 'id')
                    ->toArray()
            )->with(
                'passportTypes', PassportType::all()
                    ->pluck('name', 'id')
                    ->toArray()
            )->with('maxBirthday', now()->subYears(2)->format('Y-m-d'));
    }

    public function store(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();
            $gender = Gender::firstOrCreate(['name' => $request->gender]);
            $user = User::create([
                'username' => $request->username,
                'password' => $request->password,
                'family_name' => $request->family_name,
                'middle_name' => $request->middle_name,
                'given_name' => $request->given_name,
                'passport_type_id' => $request->passport_type_id,
                'passport_number' => $request->passport_number,
                'gender_id' => $gender->id,
                'birthday' => $request->birthday,
            ]);
            if ($request->email) {
                UserHasEmail::create([
                    'user_id' => $user->id,
                    'email' => $request->email,
                ]);
            }
            if ($request->mobile) {
                UserHasMobile::create([
                    'user_id' => $user->id,
                    'mobile' => $request->mobile,
                ]);
            }
            DB::commit();
        } catch (Exception $e) {
            try {
                DB::rollBack();
            } catch (Exception $e) {
            }
            throw $e;
        }
        Auth::loginUsingId($user->id);

        return ['success'];
    }

    public function logout()
    {
        auth()->logout();

        return redirect()->route('index');
    }
}
