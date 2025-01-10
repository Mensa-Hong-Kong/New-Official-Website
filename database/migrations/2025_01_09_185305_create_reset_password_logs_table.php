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
        Schema::create('reset_password_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('passport_type_id');
            $table->string('passport_number')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('contact_type', ['email', 'mobile']);
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->string('creator_ip');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reset_password_logs');
    }
};
