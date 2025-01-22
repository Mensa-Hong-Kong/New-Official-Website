<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\DisplayOrderRequest;
use App\Http\Requests\Admin\Role\FormRequest;
use App\Models\Module;
use App\Models\ModulePermission;
use App\Models\Permission;
use App\Models\Role;
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

    public function create(Team $team)
    {
        $rows = ModulePermission::get(['id', 'module_id', 'permission_id']);
        $modulePermissions = [];
        foreach ($rows as $row) {
            $modulePermissions[$row->module_id][$row->permission_id] = $row->id;
        }
        $displayOptions = [];
        foreach ($team->roles as $role) {
            $displayOptions[$role->pivot->display_order] = "before \"$role->name\"";
        }
        $displayOptions[0] = 'top';
        $displayOptions[max(array_keys($displayOptions)) + 1] = 'latest';
        ksort($displayOptions);

        return view('admin.teams.roles.create')
            ->with('team', $team)
            ->with(
                'roles', Role::whereDoesntHave(
                    'teams', function ($query) use ($team) {
                        $query->where($query->getModel()->getTable().'.id', $team->id);
                    }
                )->get('name')
                    ->pluck('name')
                    ->toArray()
            )->with('displayOptions', $displayOptions)
            ->with(
                'modules', Module::orderBy('display_order')
                    ->get(['id', 'name'])
            )->with(
                'permissions', Permission::orderBy('display_order')
                    ->get(['id', 'name'])
            )->with('modulePermissions', $modulePermissions);
    }

    public function store(FormRequest $request, Team $team)
    {
        DB::beginTransaction();
        $role = Role::firstOrCreate(['name' => $request->name]);
        $teamRole = TeamRole::create([
            'name' => "{$team->type->name}:{$team->name}:{$role->name}",
            'team_id' => $team->id,
            'role_id' => $role->id,
            'display_order' => $request->display_order,
        ]);
        if ($request->module_permissions) {
            $teamRole->syncPermissions(array_map('intval', $request->module_permissions));
        }
        DB::commit();

        return redirect()->route('admin.teams.show', ['team' => $team]);
    }

    public function edit(Team $team, Role $role)
    {
        $rows = ModulePermission::get(['id', 'module_id', 'permission_id']);
        $modulePermissions = [];
        foreach ($rows as $row) {
            $modulePermissions[$row->module_id][$row->permission_id] = $row->id;
        }
        $displayOptions = [];
        foreach ($team->roles as $role) {
            $displayOptions[$role->pivot->display_order] = "before \"$role->name\"";
        }
        $displayOptions[0] = 'top';
        $displayOptions[max(array_keys($displayOptions)) + 1] = 'latest';
        ksort($displayOptions);
        $roleHasModulePermissions = ModulePermission::whereHas(
            'roles', function ($query) use ($team, $role) {
                $query->where('team_id', $team->id)
                    ->where('role_id', $role->id);
            }
        )->get('id')
            ->pluck('id', 'id')
            ->toArray();

        return view('admin.teams.roles.edit')
            ->with('team', $team)
            ->with('role', $role)
            ->with(
                'roles', Role::whereDoesntHave(
                    'teams', function ($query) use ($team) {
                        $query->where($query->getModel()->getTable().'.id', $team->id);
                    }
                )->get('name')
                    ->pluck('name')
                    ->toArray()
            )->with('displayOptions', $displayOptions)
            ->with(
                'modules', Module::orderBy('display_order')
                    ->get(['id', 'name'])
            )->with(
                'permissions', Permission::orderBy('display_order')
                    ->get(['id', 'name'])
            )->with('modulePermissions', $modulePermissions)
            ->with('roleHasModulePermissions', $roleHasModulePermissions);
    }

    public function update(FormRequest $request, Team $team, Role $role)
    {
        DB::beginTransaction();
        $role->update(['name' => $request->name]);
        $teamRole = TeamRole::where('team_id', $team->id)
            ->where('role_id', $role->id)
            ->first();
        $teamRole->update([
            'name' => "{$team->type->name}:{$team->name}:{$role->name}",
            'display_order' => $request->display_order,
        ]);
        $modulePermissions = $request->module_permissions ?? [];
        $teamRole->syncPermissions(array_map('intval', $modulePermissions));
        DB::commit();

        return redirect()->route('admin.teams.show', ['team' => $team]);
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
