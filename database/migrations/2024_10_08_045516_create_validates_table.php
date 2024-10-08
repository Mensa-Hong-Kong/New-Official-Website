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
        Schema::create('validates', function (Blueprint $table) {
            $table->id();
            $table->string('validatable_type');
            $table->unsignedBigInteger('validatable_id');
            $table->string('verify_code');
            $table->unsignedTinyInteger('tried_time')->default(0);
            $table->boolean('status')->default(false);
            $table->dateTime('expiry_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validates');
    }
};
