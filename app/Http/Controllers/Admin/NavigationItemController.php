<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NavigationItemRequest;
use App\Models\NavigationItem;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class NavigationItemController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Navigation Item'))];
    }

    public function store(NavigationItemRequest $request)
    {
        DB::beginTransaction();
        if($request->master_id == 0) {
            $model = NavigationItem::whereNull('master_id');
        } else {
            $model = NavigationItem::where('master_id', $request->master_id);
        }
        $model->where('display_order', '>=', $request->display_order)
            ->increment('display_order');
        NavigationItem::create([
            'master_id' => $request->master_id == 0 ? null : $request->master_id ,
            'name' => $request->name,
            'url' => $request->url,
            'display_order' => $request->display_order,
        ]);
        DB::commit();

        return redirect()->route('admin.index');
    }
}
