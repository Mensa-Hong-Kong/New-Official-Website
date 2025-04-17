<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\TypeRequest;
use App\Models\AdmissionTestType;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Admission Test'))];
    }

    public function index()
    {
        return view('admin.admission-test-types.index')
            ->with('types', AdmissionTestType::orderBy('display_order')->get());
    }

    public function create()
    {
        $types = AdmissionTestType::orderBy('display_order')
            ->get(['name', 'display_order'])
            ->pluck('name', 'display_order')
            ->toArray();
        foreach ($types as $displayOrder => $name) {
            $types[$displayOrder] = "before \"$name\"";
        }
        if (count($types)) {
            $types[max(array_keys($types)) + 1] = 'latest';
        }
        $types[0] = 'top';

        return view('admin.admission-test-types.create')
            ->with('types', $types);
    }

    public function store(TypeRequest $request)
    {
        AdmissionTestType::create([
            'name' => $request->name,
            'interval_month' => $request->interval_month,
            'is_active' => $request->is_active,
            'display_order' => $request->display_order,
        ]);

        return redirect()->route('admin.admission-test-types.index');
    }
}
