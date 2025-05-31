<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtherPaymentGateway;

class OtherPaymentGatewayController extends Controller
{
    public function index()
    {
        return view('admin.other-payment-gateway')
            ->with(
                'paymentGateways', OtherPaymentGateway::orderBy('display_order')
                    ->get(['id', 'name'])
            );
    }
}
