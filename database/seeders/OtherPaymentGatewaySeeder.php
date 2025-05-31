<?php

namespace Database\Seeders;

use App\Models\OtherPaymentGateway;
use Illuminate\Database\Seeder;

class OtherPaymentGatewaySeeder extends Seeder
{
    public function run()
    {
        $gateway = OtherPaymentGateway::firstOrCreate(['name' => 'Cash']);
        $gateway->update([
            'display_order' => 0,
            'is_active' => true,
        ]);
        $gateway = OtherPaymentGateway::firstOrCreate(['name' => 'Faster Payment System']);
        $gateway->update([
            'display_order' => 1,
            'is_active' => true,
        ]);
        $gateway = OtherPaymentGateway::firstOrCreate(['name' => 'PayMe']);
        $gateway->update([
            'display_order' => 2,
            'is_active' => true,
        ]);
    }
}
