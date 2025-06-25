<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OtherPaymentGateway\DisplayOrderRequest;
use App\Http\Requests\NameRequest;
use App\Http\Requests\StatusRequest;
use App\Models\OtherPaymentGateway;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OtherPaymentGatewayController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Other Payment Gateway'))];
    }

    public function index()
    {
        return Inertia::render('Admin/OtherPaymentGateways')
            ->with(
                'paymentGateways', OtherPaymentGateway::orderBy('display_order')
                    ->get(['id', 'name', 'is_active'])
            );
    }

    public function update(NameRequest $request, OtherPaymentGateway $otherPaymentGateway)
    {
        $otherPaymentGateway->update(['name' => $request->name]);

        return [
            'success' => 'The payment gateway name update success!',
            'name' => $otherPaymentGateway->name,
        ];
    }

    public function active(StatusRequest $request, OtherPaymentGateway $otherPaymentGateway)
    {
        $otherPaymentGateway->update(['is_active' => $request->status]);

        return [
            'success' => "The payment gateway of $otherPaymentGateway->name changed to be ".($otherPaymentGateway->is_active ? 'active.' : 'inactive.'),
            'status' => $otherPaymentGateway->is_active,
        ];
    }

    public function displayOrder(DisplayOrderRequest $request)
    {
        $case = [];
        foreach (array_values($request->display_order) as $order => $id) {
            $case[] = "WHEN id = $id THEN $order";
        }
        $case = implode(' ', $case);
        OtherPaymentGateway::whereIn('id', $request->display_order)
            ->update(['display_order' => DB::raw("(CASE $case ELSE display_order END)")]);

        return [
            'success' => 'The display order update success!',
            'display_order' => OtherPaymentGateway::orderBy('display_order')
                ->get('id')
                ->pluck('id')
                ->toArray(),
        ];
    }
}
