<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return view('admin.permission')
            ->with('permissions', Permission::orderBy('display_order')->get());
    }
}
