<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\SystemPaymentGateway;
use App\Models\Team;
use App\Models\TeamType;
use Illuminate\Database\Seeder;

/**
 * This seeder populates the other_payment_gateways table with predefined data as follows:
 *
 * The 'other_payment_gateways' table will contain:
 * | id  | name                  | created_at | updated_at |
 * | --- | --------------------- | ---------- | ---------- |
 * | 1   | Stripe                | ...        | ...        |
 */
class SystemPaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        SystemPaymentGateway::firstOrCreate(['name' => 'Stripe']);
    }
}
