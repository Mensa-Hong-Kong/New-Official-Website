<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\Profile\UpdateRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\ContactType;
use App\Models\Gender;
use App\Models\PassportType;
use App\Models\User;
use App\Models\UserContact;
use App\Models\LoginLog;
use Exception;
use Illuminate\Http\Request;
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
                UserContact::create([
                    'user_id' => $user->id,
                    'type_id' => ContactType::firstWhere('email')->id,
                    'contact' => $request->email,
                ]);
            }
            if ($request->mobile) {
                UserContact::create([
                    'user_id' => $user->id,
                    'type_id' => ContactType::firstWhere('mobile')->id,
                    'contact' => $request->mobile,
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
        Auth::login($user);

        return redirect()->route('profile.show');
    }

    public function logout()
    {
        auth()->logout();

        return redirect()->route('index');
    }

    public function login(LoginRequest $request)
    {
        $user = User::with([
            'loginLogs' => function ($query) {
                $query->where('status', false)
                    ->where('login_at', '>=', now()->subDay());
            },
        ])->firstWhere('username', $request->username);
        if ($user) {
            if ($user->loginLogs->count() >= 10) {
                $firstInRangeLoginFailedTime = $user['loginLogs'][0]['login_at'];

                return response([
                    'errors' => ['throttle' => "Too many failed login attempts. Please try again later than $firstInRangeLoginFailedTime."],
                ], 422);
            }
            $log = [
                'user_id' => $user->id,
                'login_at' => now(),
            ];
            if ($user->checkPassword($request->password)) {
                $log['status'] = true;
                LoginLog::create($log);
                Auth::login($user, $request->remember_me);

                return redirect()->intended(route('profile.show'));
            }
            LoginLog::create($log);
        }

        return response([
            'errors' => ['failed' => 'The provided username or password is incorrect.'],
        ], 422);
    }

    public function show(Request $request)
    {
        return view('profile')
            ->with('user', $request->user())
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

    public function update(UpdateRequest $request)
    {
        $user = $request->user();
        if ($request->password != '' && ! $user->checkPassword($request->password)) {
            return response([
                'errors' => ['password' => 'The provided password is incorrect.'],
            ], 422);
        }
        $gender = Gender::firstOrCreate(['name' => $request->gender]);
        $update = [
            'username' => $request->username,
            'family_name' => $request->family_name,
            'middle_name' => $request->middle_name,
            'given_name' => $request->given_name,
            'passport_type_id' => $request->passport_type_id,
            'passport_number' => $request->passport_number,
            'gender_id' => $gender->id,
            'birthday' => $request->birthday,
        ];
        if ($request->new_password) {
            $update['password'] = $request->new_password;
        }
        $user->update($update);
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation', 'gender_id'];
        $return = array_diff_key($update, array_flip($unsetKeys));
        $return['gender'] = $request->gender;

        return $return;
    }

    public function destroy()
    {
        // ...
    }
}
