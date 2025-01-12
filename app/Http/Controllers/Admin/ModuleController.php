<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NameRequest;
use App\Models\Module;

class ModuleController extends Controller
{
    public function index()
    {
        return view('admin.module')
            ->with('modules', Module::orderBy('display_order')->get());
    }

    public function update(NameRequest $request, Module $module)
    {
        if ($request->name != $module->title) {
            $module->update(['tit;e' => $request->name]);
        }

        return [
            'success' => 'The module display name update success!',
            'name' => $request->name,
        ];
    }
}
