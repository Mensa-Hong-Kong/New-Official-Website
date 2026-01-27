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
        Schema::create('admission_test_prices', function (Blueprint $table) use ($priceDigits, $priceDecimal) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('name')->nullable();
            $table->decimal('price', $priceDigits, $priceDecimal)->unsigned();
            $table->dateTime('start_at')->nullable();
            $table->string('stripe_one_time_type_id')->nullable();
            $table->boolean('synced_one_time_type_to_stripe')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_test_prices');
    }
};
