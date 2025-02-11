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
        Schema::create('admission_tests', function (Blueprint $table) {
            $table->id();
            $table->dateTime('testing_at');
            $table->dateTime('expect_end_at');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('address_id');
            $table->unsignedInteger('maximum_candidates');
            $table->boolean('is_public');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_tests');
    }
};
