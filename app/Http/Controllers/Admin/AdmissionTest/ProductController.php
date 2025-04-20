<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\ProductRequest;
use App\Models\AdmissionTestProduct;

class ProductController extends Controller
{
    public function store(ProductRequest $request)
    {
        AdmissionTestProduct::create([
            'name' => $request->name,
            'minimum_age' => $request->minimum_age,
            'maximum_age' => $request->maximum_age,
        ]);

        return redirect()->route('admin.index');
    }
}
