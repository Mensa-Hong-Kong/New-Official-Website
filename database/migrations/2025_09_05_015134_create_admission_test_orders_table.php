<?php

use App\Library\Stripe\Amount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $priceDigits = Amount::getMaximumDigits();
        $priceDecimal = Amount::getActualDecimal();
        Schema::create('admission_test_orders', function (Blueprint $table) use ($priceDigits, $priceDecimal) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('product_name')->nullable();
            $table->string('price_name')->nullable();
            $table->decimal('price', $priceDigits, $priceDecimal)->unsigned();
            $table->unsignedTinyInteger('minimum_age')->nullable();
            $table->unsignedTinyInteger('maximum_age')->nullable();
            $table->unsignedTinyInteger('quota')->default(2);
            $table->enum('status', ['pending', 'canceled', 'failed', 'expired', 'succeeded', 'partial refunded', 'full refunded']);
            $table->dateTime('expired_at')->useCurrent();
            $table->string('gateway_type');
            $table->unsignedBigInteger('gateway_id');
            $table->string('reference_number')->nullable();
            $table->decimal('gateway_payment_fee', $priceDigits, $priceDecimal)->unsigned()->nullable();
            $table->unsignedTinyInteger('returned_quota')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_test_orders');
    }
};
