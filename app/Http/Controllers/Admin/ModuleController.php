<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Module\DisplayOrderRequest;
use App\Http\Requests\NameRequest;
use App\Models\Module;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ModuleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:Edit:Permission')];
    }

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

    public function displayOrder(DisplayOrderRequest $request)
    {
        $case = [];
        foreach(array_values($request->display_order) as $order => $id) {
            $case[] = "WHEN id = $id THEN $order";
        }
        $case = implode(' ', $case);
        Module::whereIn('id', $request->display_order)
            ->update(['display_order' => "(CASE $case ELSE display_order END)"]);

        return [
            'success' => 'The display order update success!',
            'display_order' => $request->display_order,
        ];
    }
}
