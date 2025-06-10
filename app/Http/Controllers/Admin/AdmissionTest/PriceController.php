<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\PriceRequest;
use App\Models\AdmissionTestPrice;
use App\Models\AdmissionTestProduct;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PriceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:Edit:Admission Test'),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $product = $request->route('product');
                    $price = $request->route('price');
                    if ($price->product_id == $product->id) {
                        return $next($request);
                    }
                    abort(404);
                }
            ))->only('update'),
        ];
    }

    public function store(PriceRequest $request, AdmissionTestProduct $product)
    {
        $price = AdmissionTestPrice::create([
            'product_id' => $product->id,
            'name' => $request->name,
            'price' => $request->price,
            'start_at' => $request->start_at,
        ]);

        return [
            'success' => 'The admission test product price create success.',
            'id' => $price->id,
            'name' => $price->name,
            'price' => $price->price,
            'start_at' => $price->start_at,
            'updated_at' => $price->updated_at,
        ];
    }

    public function update(PriceRequest $request, AdmissionTestProduct $product, AdmissionTestPrice $price)
    {
        $price->update([
            'name' => $request->name,
            'start_at' => $request->start_at,
        ]);

        return [
            'success' => 'The admission test product price update success.',
            'name' => $price->name,
            'start_at' => $price->start_at,
            'updated_at' => $price->updated_at,
        ];
    }
}
