<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Models\Address;
use App\Models\Area;
use App\Models\Gender;
use App\Models\PassportType;
use App\Models\ResetPasswordLog;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\EncryptHistoryMiddleware;
use Inertia\Inertia;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            (new Middleware(
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
            ))->only('resetPassword'),
            (new Middleware(EncryptHistoryMiddleware::class))->only('show'),
        ];
    }

    public function create()
    {
        return Inertia::render('User/Register')
            ->with('title', 'Register')
            ->with(
                'genders', Gender::all()
                    ->pluck('name')
            )->with(
                'passportTypes', PassportType::all()
                    ->pluck('name', 'id')
            )->with('maxBirthday', now()->subYears(2)->format('Y-m-d'))
            ->with(
                'districts', function () {
                    $areas = Area::with([
                        'districts' => function ($query) {
                            $query->orderBy('display_order');
                        },
                    ])->orderBy('display_order')
                        ->get();
                    $districts = [];
                    foreach ($areas as $area) {
                        $districts[$area->name] = [];
                        foreach ($area->districts as $district) {
                            $districts[$area->name][$district->id] = $district->name;
                        }
                    }

                    return $districts;
                }
            );
    }

    public function store(RegisterRequest $request)
    {
        DB::beginTransaction();
        $gender = Gender::firstOrCreate(['name' => $request->gender]);
        $data = [
            'username' => $request->username,
            'password' => $request->password,
            'family_name' => $request->family_name,
            'middle_name' => $request->middle_name,
            'given_name' => $request->given_name,
            'passport_type_id' => $request->passport_type_id,
            'passport_number' => $request->passport_number,
            'gender_id' => $gender->id,
            'birthday' => $request->birthday,
        ];
        if ($request->district_id) {
            $address = Address::firstOrCreate([
                'district_id' => $request->district_id,
                'value' => $request->address,
            ]);
            $data['address_id'] = $address->id;
        }
        $user = User::create($data);
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
        $user = $request->user();
        $user->load([
            'member', 'admissionTests',
            'emails.lastVerification' => function ($query) {
                $query->select(['contact_id', 'verified_at', 'expired_at']);
            }, 'mobiles.lastVerification' => function ($query) {
                $query->select(['contact_id', 'verified_at', 'expired_at']);
            }, 'address',
        ]);
        $user->makeHidden([
            'roles', 'permissions', 'synced_to_stripe',
            'created_at', 'updated_at', 'address_id',
        ]);
        $user->append('can_edit_passport_information');
        $user->member?->makeHidden(['user_id', 'created_at', 'updated_at']);
        $user->member?->append('is_active');
        $user->emails->append('is_verified');
        $user->mobiles->append('is_verified');
        $user->emails->makeHidden(['user_id', 'type', 'created_at', 'lastVerification']);
        $user->mobiles->makeHidden(['user_id', 'type', 'created_at', 'lastVerification']);
        $user->admissionTests->makeHidden([
            'type_id', 'expect_end_at', 'location_id', 'address_id',
            'is_public', 'maximum_candidates', 'pivot.test_id', 'pivot.user_id',
        ]);
        foreach ($user->admissionTests as $test) {
            $test->pivot->makeHidden('user_id', 'test_id');
        }
        $user->address?->makeHidden(['id', 'created_at', 'updated_at']);

        return Inertia::render('User/Profile')
            ->with('user', $user)
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
            )->with(
                'districts', function () {
                    $areas = Area::with([
                        'districts' => function ($query) {
                            $query->orderBy('display_order');
                        },
                    ])->orderBy('display_order')
                        ->get();
                    $districts = [];
                    foreach ($areas as $area) {
                        $districts[$area->name] = [];
                        foreach ($area->districts as $district) {
                            $districts[$area->name][$district->id] = $district->name;
                        }
                    }

                    return $districts;
                }
            );
    }

    public function update(UpdateRequest $request)
    {
        $user = $request->user();
        DB::beginTransaction();
        $update = ['username' => $request->username];
        if ($user->canEditPassportInformation) {
            $gender = $user->gender->updateName($request->gender);
            $update['family_name'] = $request->family_name;
            $update['middle_name'] = $request->middle_name;
            $update['given_name'] = $request->given_name;
            $update['passport_type_id'] = $request->passport_type_id;
            $update['passport_number'] = $request->passport_number;
            $update['gender_id'] = $gender->id;
            $update['birthday'] = $request->birthday;
        }
        if ($request->new_password) {
            $update['password'] = $request->new_password;
        }
        if ($user->address) {
            if ($request->district_id) {
                $update['address_id'] = $user->address->updateAddress($request->district_id, $request->address)->id;
            } else {
                $user->address->delete();
                $update['address_id'] = null;
            }
        } elseif ($request->district_id) {
            $address = Address::firstOrCreate([
                'district_id' => $request->district_id,
                'value' => $request->address,
            ]);
            $update['address_id'] = $address->id;
        }
        $user->update($update);
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation', 'address_id'];
        $return = array_diff_key($update, array_flip($unsetKeys));
        if ($user->canEditPassportInformation) {
            $return['gender'] = $request->gender;
        }
        $return['district_id'] = $request->district_id;
        $return['address'] = $request->address;
        $return['success'] = 'The profile update success!';
        if ($user->member) {
            $user->member->update([
                'prefix_name' => $request->prefix_name,
                'nickname' => $request->nickname,
                'suffix_name' => $request->suffix_name,
            ]);
            $return['prefix_name'] = $user->member->prefix_name;
            $return['nickname'] = $user->member->nickname;
            $return['suffix_name'] = $user->member->suffix_name;
        }
        DB::commit();

        return $return;
    }

    public function logout()
    {
        Auth::logout();
        inertia()->clearHistory();

        return redirect()->route('index');
    }

    public function login(LoginRequest $request)
    {
        $request->user->loginLogs()
            ->where('status', false)
            ->delete();
        $request->user->loginLogs()->create(['status' => true]);
        Auth::login($request->user, $request->remember_me);

        return redirect()->intended(route('profile.show'));
    }

    public function forgetPassword()
    {
        return Inertia::render('User/ForgetPassword')
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

    public function createdStripeCustomer(Request $request)
    {
        return ['status' => (bool) $request->user()->stripe];
    }
}
