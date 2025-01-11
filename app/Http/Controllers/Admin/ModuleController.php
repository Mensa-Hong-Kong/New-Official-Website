<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;

class ModuleController extends Controller
{
    public function index()
    {
        return view('admin.module')
            ->with('modules', Module::orderBy('display_order')->get());
    }
}
