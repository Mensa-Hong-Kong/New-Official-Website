<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\ProductRequest;
use App\Models\AdmissionTestProduct;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:Edit:Admission Test')];
    }

    public function index()
    {
        return view('admin.admission-test.products.index')
            ->with('products', AdmissionTestProduct::orderBy('name')->get());
    }

    public function create()
    {
        return view('admin.admission-test.products.create');
    }

    public function store(ProductRequest $request)
    {
        $product = AdmissionTestProduct::create([
            'name' => $request->name,
            'minimum_age' => $request->minimum_age,
            'maximum_age' => $request->maximum_age,
        ]);

        return redirect()->route(
            'admin.admission-test.products.show',
            ['product' => $product]
        );
    }

    public function show(AdmissionTestProduct $product)
    {
        return view('admin.admission-test.products.show')
            ->with('product', $product);
    }

    public function update(ProductRequest $request, AdmissionTestProduct $product)
    {
        $product->update([
            'name' => $request->name,
            'minimum_age' => $request->minimum_age,
            'maximum_age' => $request->maximum_age,
        ]);

        return [
            'success' => 'The admission test product update success.',
            'name' => $product->name,
            'minimum_age' => $product->minimum_age,
            'maximum_age' => $product->maximum_age,
        ];
    }
}
