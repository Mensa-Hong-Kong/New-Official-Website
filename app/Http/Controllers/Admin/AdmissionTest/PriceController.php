<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Models\AdmissionTestPrice;
use App\Models\AdmissionTestProduct;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function update(Request $request, AdmissionTestProduct $product, AdmissionTestPrice $price)
    {
        $product->update([
            'name' => $request->name,
            'start_at' => $request->start_at,
        ]);

        return [
            'name' => $product->name,
            'start_at' => $product->start_at,
        ];
    }
}
