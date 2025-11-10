<?php

namespace Database\Seeders;

use App\Models\OtherPaymentGateway;
use Illuminate\Database\Seeder;

/**
 * This seeder populates the other_payment_gateways table with predefined data as follows:
 * 
 * The 'other_payment_gateways' table will contain:
 * | id  | name                  | is_active | display_order | created_at | updated_at |
 * | --- | --------------------- | --------- | ------------- | ---------- | ---------- |
 * | 1   | Cash                  | 1         | 0             | ...        | ...        |
 * | 2   | Faster Payment System | 1         | 1             | ...        | ...        |
 * | 3   | PayMe                 | 1         | 2             | ...        | ...        |
 */
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
