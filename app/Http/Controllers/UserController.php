<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Models\Gender;
use App\Models\PassportType;
use App\Models\ResetPasswordLog;
use App\Models\User;
use App\Models\UserHasContact;
use App\Models\UserLoginLog;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware(
            function (Request $request, Closure $next) {
                $failForgetPasswordLogsWithin24Hours = ResetPasswordLog::where('passport_type_id', $request->passport_type_id)
                    ->where('passport_number', $request->passport_number)
                    ->where('created_at', '>=', now()->subDay())
                    ->where('middleware_should_count', true)
                    ->get();
                if ($failForgetPasswordLogsWithin24Hours->count() >= 10) {
                    $firstInRangeResetPasswordFailedTime = $failForgetPasswordLogsWithin24Hours[0]['created_at'];
                    abort(429, "Too many failed reset password attempts. Please try again later than $firstInRangeResetPasswordFailedTime.");
                }

                return $next($request);
            }
        ))->only('resetPassword')];
    }

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
        Auth::login($user);

        return redirect()->route('profile.show');
    }

    public function show(Request $request)
    {
        return view('user.profile')
            ->with('user', $request->user())
            ->with(
                'genders', Gender::all()
                    ->pluck('name', 'id')
                    ->toArray()
            )->with(
                'passportTypes', PassportType::all()
                    ->pluck('name', 'id')
                    ->toArray()
            )->with(
                'maxBirthday', now()
                    ->subYears(2)
                    ->format('Y-m-d')
            );
    }

    public function update(UpdateRequest $request)
    {
        $user = $request->user();
        if ($request->password != '' && ! $user->checkPassword($request->password)) {
            return response([
                'errors' => ['password' => 'The provided password is incorrect.'],
            ], 422);
        }
        DB::beginTransaction();
        $gender = $user->gender->updateName($request->gender);
        $update = [
            'username' => $request->username,
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
        DB::commit();

        return $return;
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
                $firstInRangeLoginFailedTime = $user->loginLogs[0]['created_at'];

                abort(429, "Too many failed login attempts. Please try again later than $firstInRangeLoginFailedTime.");
            }
            $log = ['user_id' => $user->id];
            if ($user->checkPassword($request->password)) {
                $log['status'] = true;
                UserLoginLog::create($log);
                $user->loginLogs()
                    ->where('status', false)
                    ->delete();
                Auth::login($user, $request->remember_me);

                return redirect()->intended(route('profile.show'));
            }
            UserLoginLog::create($log);
        }

        return response([
            'errors' => ['failed' => 'The provided username or password is incorrect.'],
        ], 422);
    }

    public function forgetPassword()
    {
        return view('user.forget-password')
            ->with(
                'passportTypes', PassportType::all()
                    ->pluck('name', 'id')
                    ->toArray()
            )->with('maxBirthday', now()->subYears(2)->format('Y-m-d'));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        DB::beginTransaction();
        $contact = UserHasContact::where('type', $request->verified_contact_type)
            ->where('contact', $request->verified_contact)
            ->whereHas(
                'user', function ($query) use ($request) {
                    $query->where('passport_type_id', $request->passport_type_id)
                        ->where('passport_number', $request->passport_number)
                        ->where('birthday', $request->birthday);
                }
            )->whereHas(
                'verifications', function ($query) {
                    $query->whereNotNull('verified_at')
                        ->whereNull('expired_at');
                }
            )->first();
        $log = [
            'passport_type_id' => $request->passport_type_id,
            'passport_number' => $request->passport_number,
            'contact_type' => $request->verified_contact_type,
            'creator_ip' => $request->ip(),
        ];
        ResetPasswordLog::create($log);
        if ($contact) {
            $log['user_id'] = $contact->user->id;
            $log['creator_id'] = $contact->user->id;
            $password = App::environment('testing') ? '12345678' : Str::password(16);
            $contact->user->update(['password' => $password]);
            $contact->notify(new ResetPasswordNotification($contact->type, $password));
            DB::commit();

            return ['success' => "The new password has been send to {$contact->type} of {$contact->contact}"];
        }
        DB::commit();

        return response([
            'errors' => ['failed' => 'The provided passport, birthday or verified contact is incorrect.'],
        ], 422);
    }

    public function syncedToStripe(Request $request)
    {
        return ['status' => $request->user()->synced_to_stripe];
    }
}
