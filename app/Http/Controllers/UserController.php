<?php

namespace App\Http\Controllers;
use App\Models\Gender;
use App\Models\PassportType;


class UserController extends Controller
{
    public function create()
    {
        return view('authentication.register')
        ->with(
            'genders', Gender::all()
                ->pluck('name','id')
                ->toArray()
        )->with(
            'passportTypes', PassportType::all()
                ->pluck('name','id')
                ->toArray()
        );
    }

    public function store()
    {
        // ...
    }
}
