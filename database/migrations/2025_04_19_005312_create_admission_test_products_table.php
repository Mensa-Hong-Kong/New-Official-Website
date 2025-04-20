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
        Schema::create('admission_test_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('minimum_age')->nullable();
            $table->unsignedTinyInteger('maximum_age')->nullable();
            $table->string('stripe_id')->nullable();
            $table->boolean('synced_to_stripe')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_test_products');
    }
};
