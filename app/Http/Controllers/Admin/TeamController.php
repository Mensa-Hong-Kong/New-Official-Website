<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamType;

class TeamController extends Controller
{
    public function index()
    {
        return view('admin.teams.index')
            ->with(
                'types', TeamType::with([
                    'teams' => function ($query) {
                        $query->orderBy('display_order')
                            ->orderBy('id');
                    },
                ])->orderBy('display_order')
                    ->orderBy('id')
                    ->get()
            );
    }
}
