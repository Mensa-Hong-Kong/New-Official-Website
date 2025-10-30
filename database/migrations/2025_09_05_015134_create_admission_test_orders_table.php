<?php

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
        Schema::create('admission_test_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('product_name')->nullable();
            $table->string('price_name')->nullable();
            $table->unsignedSmallInteger('price');
            $table->unsignedTinyInteger('quota')->default(2);
            $table->enum('status', ['pending', 'cancelled', 'failed', 'expired', 'succeeded']);
            $table->dateTime('expired_at')->useCurrent();
            $table->string('gateway_type');
            $table->unsignedBigInteger('gateway_id');
            $table->string('reference_number')->nullable();
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
