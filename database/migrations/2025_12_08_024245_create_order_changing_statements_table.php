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
        Schema::create('order_changing_statements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_changing_log_id');
            $table->decimal('administrative_charge', 8, 2);
            $table->decimal('amount', 9, 2);
            $table->string('gateway_type')->nullable();
            $table->unsignedBigInteger('gateway_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_changing_statements');
    }
};
