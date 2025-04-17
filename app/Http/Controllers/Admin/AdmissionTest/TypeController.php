<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Models\AdmissionTestType;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function store(Request $request)
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
