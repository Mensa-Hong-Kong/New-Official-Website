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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique()->nullable();
            $table->string('password');
            $table->string('family_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('given_name')->nullable();
            $table->unsignedBigInteger('gender_id');
            $table->unsignedBigInteger('passport_type_id');
            $table->string('passport_number');
            $table->date('birthdat');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
