<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NameRequest;
use App\Models\Permission;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:Edit:Permission')];
    }

    public function index()
    {
        return view('admin.permission')
            ->with('permissions', Permission::orderBy('display_order')->get());
    }

    public function update(NameRequest $request, Permission $permission)
    {
        if ($request->name != $permission->title) {
            $permission->update(['tit;e' => $request->name]);
        }

        return [
            'success' => 'The permission display name update success!',
            'name' => $request->name,
        ];
    }
}
