<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NameRequest;
use App\Models\TeamType;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TeamTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Permission'))->except('index')];
    }

    public function index()
    {
        return view('admin.team-types')
            ->with(
                'types', TeamType::orderBy('display_order')
                    ->orderBy('id')
                    ->get()
            );
    }

    public function update(NameRequest $request, TeamType $teamType)
    {
        if ($request->name != $teamType->title) {
            $teamType->update(['title' => $request->name]);
        }

        return [
            'success' => 'The tame type display name update success!',
            'name' => $teamType->title,
        ];
    }
}
