<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NavigationItemRequest;
use App\Models\NavigationItem;
use Illuminate\Support\Facades\DB;

class NavigationItemController extends Controller
{
    public function store(NavigationItemRequest $request)
    {
        DB::beginTransaction();
        NavigationItem::where('master_id', $request->master_id)
            ->where('display_order', '>=', $request->display_order)
            ->increment('display_order');
        NavigationItem::create([
            'master_id' => $request->master_id,
            'name' => $request->name,
            'url' => $request->url,
            'display_order' => $request->display_order,
        ]);
        DB::commit();

        return redirect()->route('admin.index');
    }
}
