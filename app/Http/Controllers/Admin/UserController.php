<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\ResetPasswordRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Models\Address;
use App\Models\Area;
use App\Models\Gender;
use App\Models\PassportType;
use App\Models\ResetPasswordLog;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\App;
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
                ['permission:View:User', EncryptHistoryMiddleware::class])
            )->only(['index', 'show']),
            (new Middleware('permission:Edit:User'))
                ->only(['update', 'resetPassword']),
        ];
    }

    public function index(Request $request)
    {
        $isSearch = false;
        $append = [];
        $users = User::with([
            'lastLoginLog' => function ($query) {
                $query->select(['id', 'user_id', 'created_at']);
            },
        ]);
        if ($request->family_name) {
            $append['family_name'] = $request->family_name;
            $isSearch = true;
            $users = $users->where('family_name', $request->family_name);
        }
        if ($request->middle_name) {
            $append['middle_name'] = $request->middle_name;
            $isSearch = true;
            $users = $users->where('middle_name', $request->middle_name);
        }
        if ($request->given_name) {
            $append['given_name'] = $request->given_name;
            $isSearch = true;
            $users = $users->where('given_name', $request->given_name);
        }
        if ($request->passport_type_id && $request->passport_number) {
            $append['passport_type_id'] = $request->passport_type_id;
            $append['passport_number'] = $request->passport_number;
            $isSearch = true;
            $users = $users->where('passport_type_id', $request->passport_type_id)
                ->where('passport_number', $request->passport_number);
        }
        if ($request->gender_id) {
            $append['gender_id'] = $request->gender_id;
            $isSearch = true;
            $users = $users->where('gender_id', $request->gender_id);
        }
        if ($request->birthday) {
            $append['birthday'] = $request->birthday;
            $isSearch = true;
            $users = $users->where('birthday', $request->birthday);
        }
        if ($request->email) {
            $append['email'] = $request->email;
            $isSearch = true;
            $users = $users->whereHas(
                'emails', function ($query) use ($request) {
                    $query->where('contact', $request->email);
                }
            );
        }
        if ($request->mobile) {
            $append['mobile'] = $request->mobile;
            $isSearch = true;
            $users = $users->whereHas(
                'mobiles', function ($query) use ($request) {
                    $query->where('contact', $request->mobile);
                }
            );
        }
        $users = $users->sortable('id')->paginate();
        $users->append('adorned_name');
        $users->makeHidden([
            'username', 'synced_to_stripe',
            'family_name', 'middle_name', 'given_name',
            'passport_type_id', 'passport_number',
            'birthday', 'member',
        ]);
        foreach ($users as $user) {
            if ($user->lastLoginLog) {
                $user->lastLoginLog->makeHidden(['id', 'user_id']);
            }
        }
        $passportTypes = PassportType::get(['id', 'name'])
            ->pluck('name', 'id')
            ->toArray();
        $genders = Gender::get(['id', 'name'])
            ->pluck('name', 'id')
            ->toArray();

        return Inertia::render('Admin/Users/Index')
            ->with('isSearch', $isSearch)
            ->with('append', $append)
            ->with('passportTypes', $passportTypes)
            ->with('genders', $genders)
            ->with('maxBirthday', now()->subYears(2)->format('Y-m-d'))
            ->with('users', $users);
    }

    public function show(User $user)
    {
        $user->load([
            'member', 'emails.lastVerification' => function ($query) {
                $query->select(['contact_id', 'verified_at', 'expired_at']);
            }, 'mobiles.lastVerification' => function ($query) {
                $query->select(['contact_id', 'verified_at', 'expired_at']);
            }, 'address'
        ]);
        $user->member?->makeHidden(['user_id', 'created_at', 'updated_at']);
        $user->member?->append('is_active');
        $user->emails->append('is_verified');
        $user->emails->makeHidden(['user_id', 'type', 'created_at', 'lastVerification']);
        $user->mobiles->append('is_verified');
        $user->mobiles->makeHidden(['user_id', 'type', 'created_at', 'lastVerification']);
        $user->address?->makeHidden(['id', 'created_at', 'updated_at']);

        return Inertia::render('Admin/Users/Show')
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
                'districts', function() {
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

    public function update(UpdateRequest $request, User $user)
    {
        DB::beginTransaction();
        $gender = $user->gender->updateName($request->gender);
        $return = [
            'username' => $request->username,
            'family_name' => $request->family_name,
            'middle_name' => $request->middle_name,
            'given_name' => $request->given_name,
            'passport_type_id' => $request->passport_type_id,
            'passport_number' => $request->passport_number,
            'gender_id' => $gender->id,
            'birthday' => $request->birthday,
        ];
        if ($user->address) {
            if ($request->district_id) {
                $return['address_id'] = $user->address->updateAddress(
                    $request->district_id,
                    $request->address
                )->id;
            } else {
                $user->address->delete();
                $return['address_id'] = null;
            }
        } elseif ($request->district_id) {
            $return['address_id'] = Address::firstOrCreate([
                'district_id' => $request->district_id,
                'value' => $request->address,
            ])->id;
        }
        $user->update($return);
        unset($return['address_id']);
        $return['district_id'] = $request->district_id;
        $return['address'] = $request->address;
        $return['gender'] = $gender->name;
        $return['success'] = 'The user data update success!';
        if($user->member) {
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

    public function resetPassword(ResetPasswordRequest $request, User $user)
    {
        $contact = UserHasContact::where('user_id', $user->id)
            ->where('type', $request->contact_type)
            ->where('is_default', true)
            ->first();
        if (! $contact) {
            return response([
                'errors' => ['contact_type' => "This user have no default {$request->contact_type}, cannot reset password by {$request->contact_type}."],
            ], 422);
        }
        DB::beginTransaction();
        ResetPasswordLog::create([
            'passport_type_id' => $user->passport_type_id,
            'passport_number' => $user->passport_number,
            'contact_type' => $request->contact_type,
            'user_id' => $user->id,
            'creator_id' => $request->user()->id,
            'creator_ip' => $request->ip(),
            'middleware_should_count' => false,
        ]);
        $password = App::environment('testing') ? '12345678' : Str::password(16);
        $user->update(['password' => $password]);
        $contact->notify(new ResetPasswordNotification($contact->type, $password));
        DB::commit();

        return ['success' => "The new password has been send to user default {$contact->type}."];
    }
}
