<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Models\AdmissionTestProduct;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        AdmissionTestProduct::create([
            'name' => $request->name,
            'minimum_age' => $request->minimum_age,
            'maximum_age' => $request->maximum_age,
        ]);

        return redirect()->route('admin.index');
    }
}
