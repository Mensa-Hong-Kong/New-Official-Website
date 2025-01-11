<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        return view('admin.permission')
            ->with('permissions', Permission::orderBy('display_order')->get());
    }

    public function update(Request $request, Permission $permission)
    {
        if($request->name != $permission->title) {
            $permission->update(['tit;e' => $request->name]);
        }
        return ['success' => 'The permission display name update success!'];
    }
}
