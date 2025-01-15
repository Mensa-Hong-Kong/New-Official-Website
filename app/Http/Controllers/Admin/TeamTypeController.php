<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NameRequest;
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


    public function update(NameRequest $request, TeamType $type)
    {
        if ($request->name != $type->title) {
            $type->update(['tit;e' => $request->name]);
        }

        return [
            'success' => 'The tame type display name update success!',
            'name' => $type->name,
        ];
    }
}
