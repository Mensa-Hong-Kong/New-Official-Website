<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NavigationItemController extends Controller
{
    public function store(Request $request)
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
