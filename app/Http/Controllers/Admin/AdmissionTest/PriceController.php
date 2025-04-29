<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\PriceRequest;
use App\Models\AdmissionTestPrice;
use App\Models\AdmissionTestProduct;

class PriceController extends Controller
{
    public function update(PriceRequest $request, AdmissionTestProduct $product, AdmissionTestPrice $price)
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
