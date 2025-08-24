<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\Type\DisplayOrderRequest;
use App\Http\Requests\Admin\AdmissionTest\Type\FormRequest;
use App\Models\AdmissionTestType;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Admission Test'))];
    }

    public function index()
    {
        return Inertia::render('Admin/AdmissionTest/Types/Index')
            ->with('types', AdmissionTestType::orderBy('display_order')->get());
    }

    public function create()
    {
        $displayOptions = AdmissionTestType::orderBy('display_order')
            ->get(['name', 'display_order'])
            ->pluck('name', 'display_order')
            ->toArray();
        foreach ($displayOptions as $displayOrder => $name) {
            $types[$displayOrder] = "before \"$name\"";
        }
        if (count($displayOptions)) {
            $displayOptions[max(array_keys($types)) + 1] = 'latest';
        }
        $displayOptions[0] = 'top';

        return Inertia::render('Admin/AdmissionTest/Types/Create')
            ->with('displayOptions', $displayOptions);
    }

    public function store(FormRequest $request)
    {
        DB::beginTransaction();
        AdmissionTestType::where('display_order', '>=', $request->display_order)
            ->increment('display_order');
        AdmissionTestType::create([
            'name' => $request->name,
            'interval_month' => $request->interval_month,
            'is_active' => $request->is_active,
            'display_order' => $request->display_order,
        ]);
        DB::commit();

        return redirect()->route('admin.admission-test.types.index');
    }

    public function edit(AdmissionTestType $type)
    {
        $types = AdmissionTestType::orderBy('display_order')
            ->get(['name', 'display_order']);
        $displayOptions = [];
        foreach ($types as $thisType) {
            $displayOrder = $thisType->display_order;
            if ($displayOrder > $type->display_order) {
                $displayOrder--;
            }
            $displayOptions[$displayOrder] = "before \"{$thisType->name}\"";
        }
        if (count($types) > 1) {
            $index = max(array_keys($displayOptions));
            if ($index != $types->max('display_order')) {
                $index++;
            }
            $displayOptions[$index] = 'latest';
        }
        $displayOptions[0] = 'top';
        $type->makeHidden(['created_at', 'updated_at']);

        return Inertia::render('Admin/AdmissionTest/Types/Edit')
            ->with('type', $type)
            ->with('displayOptions', $displayOptions);
    }

    public function update(FormRequest $request, AdmissionTestType $type)
    {
        DB::beginTransaction();
        if ($request->display_order > $request->maxDisplayOrder) {
            AdmissionTestType::where('display_order', '>', $type->display_order)
                ->decrement('display_order');
            $request->display_order -= 1;
        } elseif ($type->display_order > $request->display_order) {
            AdmissionTestType::where('display_order', '>=', $request->display_order)
                ->increment('display_order');
            AdmissionTestType::where('display_order', '>', $type->display_order)
                ->decrement('display_order');
        } elseif ($type->display_order < $request->display_order) {
            AdmissionTestType::where('display_order', '>', $type->display_order)
                ->decrement('display_order');
            AdmissionTestType::where('display_order', '>=', $request->display_order)
                ->increment('display_order');
        }
        $type->update([
            'name' => $request->name,
            'interval_month' => $request->interval_month,
            'is_active' => $request->is_active,
            'display_order' => $request->display_order,
        ]);
        DB::commit();

        return redirect()->route('admin.admission-test.types.index');
    }

    public function displayOrder(DisplayOrderRequest $request)
    {
        $case = [];
        foreach (array_values($request->display_order) as $order => $id) {
            $case[] = "WHEN id = $id THEN $order";
        }
        $case = implode(' ', $case);
        AdmissionTestType::whereIn('id', $request->display_order)
            ->update(['display_order' => DB::raw("(CASE $case ELSE display_order END)")]);

        return [
            'success' => 'The display order update success!',
            'display_order' => AdmissionTestType::orderBy('display_order')
                ->get('id')
                ->pluck('id')
                ->toArray(),
        ];
    }
}
