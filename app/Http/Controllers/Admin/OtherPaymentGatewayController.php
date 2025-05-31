<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtherPaymentGateway;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OtherPaymentGatewayController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Other Payment Gateway'))];
    }

    public function index()
    {
        return view('admin.other-payment-gateway')
            ->with(
                'paymentGateways', OtherPaymentGateway::orderBy('display_order')
                    ->get(['id', 'name', 'is_active'])
            );
    }
}
