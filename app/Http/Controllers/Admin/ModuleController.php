<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Module\DisplayOrderRequest;
use App\Http\Requests\NameRequest;
use App\Models\Module;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ModuleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Permission'))->except('index')];
    }

    public function index()
    {
        return Inertia::render('Admin/Modules/Index')
            ->with(
                'modules', Module::orderBy('display_order')
                    ->get(['id', 'name', 'title', 'master_id'])
            );
    }

    public function update(NameRequest $request, Module $module)
    {
        if ($request->name != $module->title) {
            $module->update(['title' => $request->name]);
        }

        return [
            'success' => 'The module display name update success!',
            'name' => $module->title,
        ];
    }

    public function displayOrder(DisplayOrderRequest $request)
    {
        $IDs = [];
        $masterIdCase = [];
        $displayOrderCase = [];
        foreach ($request->display_order as $masterID => $array) {
            foreach (array_values($array) as $order => $id) {
                $IDs[] = $id;
                $masterIdCase[] = "WHEN id = $id THEN ".($masterID == '0' ? 'NULL' : $masterID);
                $displayOrderCase[] = "WHEN id = $id THEN $order";
            }
        }
        $masterIdCase = implode(' ', $masterIdCase);
        $displayOrderCase = implode(' ', $displayOrderCase);
        Module::whereIn('id', $IDs)
            ->update([
                'master_id' => DB::raw("(CASE $masterIdCase ELSE master_id END)"),
                'display_order' => DB::raw("(CASE $displayOrderCase ELSE display_order END)"),
            ]);
        $return = [
            'success' => 'The display order update success!',
            'display_order' => [],
        ];
        $items = Module::orderBy('display_order')
            ->get(['id', 'master_id'])
            ->pluck('master_id', 'id')
            ->toArray();
        foreach (array_unique($items) as $masterID) {
            $return['display_order'][$masterID ?? 0] = [];
        }
        foreach ($items as $id => $masterID) {
            $return['display_order'][$masterID ?? 0][] = $id;
        }

        return $return;
    }
}
