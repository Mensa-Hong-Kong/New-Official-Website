<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Permission\DisplayOrderRequest;
use App\Http\Requests\NameRequest;
use App\Models\Permission;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Permission'))->except('index')];
    }

    public function index()
    {
        return view('admin.permissions')
            ->with('permissions', Permission::orderBy('display_order')->get());
    }

    public function update(NameRequest $request, Permission $permission)
    {
        if ($request->name != $permission->title) {
            $permission->update(['title' => $request->name]);
        }

        return [
            'success' => 'The permission display name update success!',
            'name' => $permission->title,
        ];
    }

    public function displayOrder(DisplayOrderRequest $request)
    {
        $case = [];
        foreach (array_values($request->display_order) as $order => $id) {
            $case[] = "WHEN id = $id THEN $order";
        }
        $case = implode(' ', $case);
        Permission::whereIn('id', $request->display_order)
            ->update(['display_order' => DB::raw("(CASE $case ELSE display_order END)")]);

        return [
            'success' => 'The display order update success!',
            'display_order' => Permission::orderBy('display_order')
                ->get('id')
                ->pluck('id')
                ->toArray(),
        ];
    }
}
