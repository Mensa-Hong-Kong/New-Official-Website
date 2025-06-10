<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\ProductRequest;
use App\Models\AdmissionTestPrice;
use App\Models\AdmissionTestProduct;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        $product = AdmissionTestProduct::create([
            'name' => $request->name,
            'option_name' => $request->option_name,
            'minimum_age' => $request->minimum_age,
            'maximum_age' => $request->maximum_age,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'quota' => $request->quota,
        ]);
        AdmissionTestPrice::create([
            'product_id' => $product->id,
            'name' => $request->price_name,
            'price' => $request->price,
        ]);
        DB::commit();

        return redirect()->route(
            'admin.admission-test.products.show',
            ['product' => $product]
        );
    }

    public function show(AdmissionTestProduct $product)
    {
        $product->load([
            'prices' => function ($query) {
                $query->select([
                    'id',
                    'product_id',
                    'name',
                    'price',
                    'start_at',
                ])->orderByDesc('start_at')
                    ->orderByDesc('updated_at');
            },
        ])->makeHidden([
            'created_at',
            'stripe_id',
            'synced_to_stripe',
        ]);
        $product->prices->makeHidden('product_id');

        return view('admin.admission-test.products.show')
            ->with('product', $product);
    }

    public function update(ProductRequest $request, AdmissionTestProduct $product)
    {
        $product->update([
            'name' => $request->name,
            'option_name' => $request->option_name,
            'minimum_age' => $request->minimum_age,
            'maximum_age' => $request->maximum_age,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'quota' => $request->quota,
        ]);

        return [
            'success' => 'The admission test product update success.',
            'name' => $product->name,
            'option_name' => $product->option_name,
            'minimum_age' => $product->minimum_age,
            'maximum_age' => $product->maximum_age,
            'start_at' => $product->start_at,
            'end_at' => $product->end_at,
            'quota' => $product->quota,
        ];
    }
}
