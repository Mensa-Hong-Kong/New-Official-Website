<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamType;

class TeamTypeController extends Controller
{
    public function index()
    {
        return view('admin.team-types.index')
            ->with(
                'types', TeamType::orderBy('display_order')
                    ->orderBy('id')
                    ->get()
            );
    }
}
