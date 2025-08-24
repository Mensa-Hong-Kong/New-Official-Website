<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamType\DisplayOrderRequest;
use App\Http\Requests\NameRequest;
use App\Models\TeamType;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TeamTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Permission'))->except('index')];
    }

    public function index()
    {
        return Inertia::render('Admin/TeamTypes')
            ->with(
                'types', TeamType::orderBy('display_order')
                    ->orderBy('id')
                    ->get()
                    ->makeHidden(['display_order', 'created_at'])
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

    public function displayOrder(DisplayOrderRequest $request)
    {
        $case = [];
        foreach (array_values($request->display_order) as $order => $id) {
            $case[] = "WHEN id = $id THEN $order";
        }
        $case = implode(' ', $case);
        TeamType::whereIn('id', $request->display_order)
            ->update(['display_order' => DB::raw("(CASE $case ELSE display_order END)")]);

        return [
            'success' => 'The display order update success!',
            'display_order' => TeamType::orderBy('display_order')
                ->get('id')
                ->pluck('id')
                ->toArray(),
        ];
    }
}
