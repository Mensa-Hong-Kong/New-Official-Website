<?php

namespace App\Http\Controllers\Admin\AdmissionTest;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\Admin\AdmissionTest\Order\StoreRequest;
use App\Jobs\Orders\RemoveExpiredOrderReservedAdmissionTest;
use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasCandidate;
use App\Models\AdmissionTestOrder;
use App\Models\OtherPaymentGateway;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends BaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            (new Middleware('permission:Edit:Admission Test Order')),
        ];
    }

    public function create()
    {
        return Inertia::render(
            'Admin/AdmissionTest/Orders/Create',
            [
                'paymentGateways' => function () {
                    return OtherPaymentGateway::where('is_active', true)
                        ->get(['id', 'name'])
                        ->pluck('name', 'id')
                        ->toArray();
                },
                'tests' => function () {
                    $tests = AdmissionTest::with(['address.district.area', 'location'])
                        ->where('testing_at', '>=', now()->addDays(2)->endOfDay())
                        ->whereAvailable()
                        ->withCount('candidates')
                        ->get();
                    foreach ($tests as $test) {
                        $test->address->district->area
                            ->makeHidden(['id', 'display_order', 'created_at', 'updated_at']);
                        $test->address->district
                            ->makeHidden(['id', 'area_id', 'display_order', 'created_at', 'updated_at']);
                        $test->address->makeHidden(['id', 'district_id', 'created_at', 'updated_at']);
                        $test->location->makeHidden(['id', 'created_at', 'updated_at']);
                        $test->makeHidden(['type_id', 'address_id', 'location_id', 'expect_end_at', 'created_at', 'updated_at']);
                    }

                    return $tests;
                },
            ]
        );
    }

    public function store(StoreRequest $request)
    {
        $booking = null;
        if ($request->test) {
            DB::beginTransaction();
            $booking = AdmissionTestHasCandidate::create([
                'test_id' => $request->test_id,
                'user_id' => $request->user_id,
            ]);
            // check again for may be concurrent than over sell
            if ($request->test->candidates()->count() > $request->test->maximum_candidates) {
                DB::rollback();

                return response()->json([
                    'errors' => ['test_id' => 'The admission test is fulled, please other test, if you need update to date tests info, please reload the page or open a new window tab to read date tests info.'],
                ], 422);
            }
        } else {
            DB::beginTransaction();
        }
        $order = AdmissionTestOrder::create([
            'user_id' => $request->user_id,
            'product_name' => $request->product_name,
            'price_name' => $request->price_name,
            'price' => $request->price,
            'quota' => $request->quota,
            'status' => $request->status,
            'expired_at' => $request->status == 'pending' && $request->expired_at ? $request->expired_at : now(),
            'gatewayable_type' => OtherPaymentGateway::class,
            'gatewayable_id' => $request->payment_gateway_id,
            'reference_number' => $request->reference_number,
        ]);
        if ($booking) {
            AdmissionTestHasCandidate::where('test_id', $request->test_id)
                ->where('user_id', $request->user_id)
                ->update(['order_id' => $order->id]);
            if ($order->status != 'succeeded') {
                RemoveExpiredOrderReservedAdmissionTest::dispatch($order->id)->delay($order->expired_at);
            }
        }
        DB::commit();

        return redirect()->route('admin.index');
    }
}
