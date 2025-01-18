<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\DisplayOrderRequest;
use App\Models\Team;
use App\Models\TeamRole;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Permission'))];
    }

    public function displayOrder(DisplayOrderRequest $request, Team $team)
    {
        $case = [];
        foreach (array_values($request->display_order) as $order => $id) {
            $case[] = "WHEN role_id = $id THEN $order";
        }
        $case = implode(' ', $case);
        TeamRole::whereIn('role_id', $request->display_order)
            ->where('team_id', $team->id)
            ->update(['display_order' => DB::raw("(CASE $case ELSE display_order END)")]);

        return [
            'success' => 'The display order update success!',
            'display_order' => TeamRole::where('team_id', $team->id)
                ->orderBy('display_order')
                ->get('role_id')
                ->pluck('role_id')
                ->toArray(),
        ];
    }
}
