<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Models\Gender;
use App\Models\PassportType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            (new Middleware('permission:View:User'))->only(['index', 'show']),
            (new Middleware('permission:Edit:User'))->only(['update']),
        ];
    }

    public function index(Request $request)
    {
        $isSearch = false;
        $append = [];
        $users = new User;
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
        $users = $users->sortable()->paginate();
        $passportTypes = PassportType::get(['id', 'name'])
            ->pluck('name', 'id')
            ->toArray();
        $genders = Gender::get(['id', 'name'])
            ->pluck('name', 'id')
            ->toArray();

        return view('admin.users.index')
            ->with('isSearch', $isSearch)
            ->with('append', $append)
            ->with('passportTypes', $passportTypes)
            ->with('genders', $genders)
            ->with('maxBirthday', now()->subYears(2)->format('Y-m-d'))
            ->with('users', $users);
    }

    public function show(User $user)
    {
        return view('admin.users.show')
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
            );
    }

    public function update(UpdateRequest $request, User $user)
    {
        DB::beginTransaction();
        $gender = Gender::firstOrCreate(['name' => $request->gender]);
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
        $user->update($return);
        unset($return['gender_id']);
        $return['gender'] = $gender->name;
        $return['success'] = 'The user data update success!';
        DB::commit();

        return $return;
    }
}
