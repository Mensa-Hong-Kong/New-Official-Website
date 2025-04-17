<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\TypeRequest;
use App\Models\AdmissionTestType;

class TypeController extends Controller
{
    public function store(TypeRequest $request)
    {
        AdmissionTestType::create([
            'name' => $request->name,
            'interval_month' => $request->interval_month,
            'is_active' => $request->is_active,
            'display_order' => $request->display_order,
        ]);

        return redirect()->route('admin.index');
    }
}
