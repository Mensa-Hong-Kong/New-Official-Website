<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Models\Gender;
use App\Models\PassportType;
use App\Models\User;
use App\Models\UserHasContact;
use App\Models\UserLoginLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function create()
    {
        return view('user.register')
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
                UserHasContact::create([
                    'user_id' => $user->id,
                    'type' => 'email',
                    'contact' => $request->email,
                ]);
            }
            if ($request->mobile) {
                UserHasContact::create([
                    'user_id' => $user->id,
                    'type' => 'mobile',
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
        Auth::logout();

        return redirect()->route('index');
    }

    public function login(LoginRequest $request)
    {
        $user = User::with([
            'loginLogs' => function ($query) {
                $query->where('status', false)
                    ->where('created_at', '>=', now()->subDay());
            },
        ])->firstWhere('username', $request->username);
        if ($user) {
            if ($user->loginLogs->count() >= 10) {
                $firstInRangeLoginFailedTime = $user['loginLogs'][0]['created_at'];

                return response([
                    'errors' => ['throttle' => "Too many failed login attempts. Please try again later than $firstInRangeLoginFailedTime."],
                ], 422);
            }
            $log = ['user_id' => $user->id];
            if ($user->checkPassword($request->password)) {
                $log['status'] = true;
                UserLoginLog::create($log);
                Auth::login($user, $request->remember_me);

                return redirect()->intended(route('profile.show'));
            }
            UserLoginLog::create($log);
        }

        return response([
            'errors' => ['failed' => 'The provided username or password is incorrect.'],
        ], 422);
    }

    public function show(Request $request)
    {
        $user = $request->user();
        $contacts = [
            'emails' => [],
            'mobiles' => [],
        ];
        foreach ($user->contacts as $contact) {
            $contacts[$contact->type.'s'][] = $contact;
        }

        return view('user.profile')
            ->with('user', $user)
            ->with(
                'genders', Gender::all()
                    ->pluck('name', 'id')
                    ->toArray()
            )->with(
                'passportTypes', PassportType::all()
                    ->pluck('name', 'id')
                    ->toArray()
            )->with('maxBirthday', now()->subYears(2)->format('Y-m-d'))
            ->with('contacts', $contacts);
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
}
