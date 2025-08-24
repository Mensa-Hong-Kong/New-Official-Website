<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NavigationItem\DisplayOrderRequest;
use App\Http\Requests\Admin\NavigationItem\FormRequest;
use App\Models\NavigationItem;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NavigationItemController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Navigation Item'))];
    }

    public function index()
    {
        return Inertia::render('Admin/NavigationItems/Index');
    }

    public function create()
    {
        $items = NavigationItem::orderBy('display_order')
            ->get(['id', 'master_id', 'name', 'display_order']);
        $displayOptions = array_fill_keys($items->pluck('id')->toArray(), []);
        $displayOptions[0] = [];
        foreach ($items as $item) {
            $displayOptions[$item->master_id ?? 0][$item->display_order] = "before \"$item->name\"";
        }
        foreach ($displayOptions as $masterID => $array) {
            if (count($array)) {
                $displayOptions[$masterID][max(array_keys($array)) + 1] = 'latest';
            }
            $displayOptions[$masterID][0] = 'top';
        }
        $items->makeHidden('display_order');

        return Inertia::render('Admin/NavigationItems/Create')
            ->with('items', $items)
            ->with('displayOptions', $displayOptions);
    }

    public function store(FormRequest $request)
    {
        DB::beginTransaction();
        if ($request->master_id == 0) {
            $model = NavigationItem::whereNull('master_id');
        } else {
            $model = NavigationItem::where('master_id', $request->master_id);
        }
        $model->where('display_order', '>=', $request->display_order)
            ->increment('display_order');
        NavigationItem::create([
            'master_id' => $request->master_id == 0 ? null : $request->master_id,
            'name' => $request->name,
            'url' => $request->url,
            'display_order' => $request->display_order,
        ]);
        DB::commit();

        return redirect()->route('admin.navigation-items.index');
    }

    public function edit(NavigationItem $navigationItem)
    {
        $items = NavigationItem::orderBy('display_order')
            ->get(['id', 'master_id', 'name', 'display_order']);
        $displayOptions = array_fill_keys($items->pluck('id')->toArray(), []);
        $displayOptions[0] = [];
        foreach ($items as $item) {
            $displayOrder = $item->display_order;
            if ($navigationItem->id == $item->id && $displayOrder > $navigationItem->display_order) {
                $displayOrder--;
            }
            $displayOptions[$item->master_id ?? 0][$displayOrder] = "before \"$item->name\"";
        }
        foreach ($displayOptions as $masterID => $array) {
            if (count($array) || $masterID != $navigationItem->master_id) {
                $index = 0;
                if (count($array)) {
                    $index = max(array_keys($array));
                    if (
                        $masterID != $navigationItem->master_id ||
                        $navigationItem->display_order != $index
                    ) {
                        $index++;
                    }
                }
                $displayOptions[$masterID][$index] = 'latest';
            }
            $displayOptions[$masterID][0] = 'top';
        }
        $items->makeHidden('display_order');
        $navigationItem->makeHidden(['created_at', 'updated_at']);

        return Inertia::render('Admin/NavigationItems/Edit')
            ->with('item', $navigationItem)
            ->with('items', $items)
            ->with('displayOptions', $displayOptions);
    }

    public function update(FormRequest $request, NavigationItem $navigationItem)
    {
        DB::beginTransaction();
        if ($request->master_id == 0) {
            $increment = NavigationItem::whereNull('master_id');
        } else {
            $increment = NavigationItem::where('master_id', $request->master_id);
        }
        if (! $navigationItem->master_id) {
            $decrement = NavigationItem::whereNull('master_id');
        } else {
            $decrement = NavigationItem::where('master_id', $navigationItem->master_id);
        }
        if ($request->display_order > $request->maxDisplayOrder) {
            $decrement->where('display_order', '>', $navigationItem->display_order)
                ->decrement('display_order');
            $request->display_order -= 1;
        } elseif (
            $navigationItem->master_id != $request->master_id ||
            $navigationItem->display_order > $request->display_order
        ) {
            $increment->where('display_order', '>=', $request->display_order)
                ->increment('display_order');
            $decrement->where('display_order', '>', $navigationItem->display_order)
                ->decrement('display_order');
        } elseif ($navigationItem->display_order < $request->display_order) {
            $decrement->where('display_order', '>', $navigationItem->display_order)
                ->decrement('display_order');
            $increment->where('display_order', '>=', $request->display_order)
                ->increment('display_order');
        }
        $navigationItem->update([
            'master_id' => $request->master_id == 0 ? null : $request->master_id,
            'name' => $request->name,
            'url' => $request->url,
            'display_order' => $request->display_order,
        ]);
        DB::commit();

        return redirect()->route('admin.navigation-items.index');
    }

    public function destroy(NavigationItem $navigationItem)
    {
        $navigationItem->delete();

        return ['success' => 'The display order update success!'];
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
        NavigationItem::whereIn('id', $IDs)
            ->update([
                'master_id' => DB::raw("(CASE $masterIdCase ELSE master_id END)"),
                'display_order' => DB::raw("(CASE $displayOrderCase ELSE display_order END)"),
            ]);
        $return = [
            'success' => 'The display order update success!',
            'display_order' => [],
        ];
        $items = NavigationItem::orderBy('display_order')
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
