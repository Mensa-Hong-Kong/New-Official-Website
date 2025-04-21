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
        AdmissionTestProduct::create([
            'name' => $request->name,
            'minimum_age' => $request->minimum_age,
            'maximum_age' => $request->maximum_age,
        ]);

        return redirect()->route('admin.index');
    }

    public function show(AdmissionTestProduct $product)
    {
        return view('admin.admission-test.products.show')
            ->with('product', $product);
    }
}
