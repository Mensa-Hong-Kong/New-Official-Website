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
        Schema::create('contact_has_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->string('contact', 320);
            $table->enum('type', ['email', 'mobile']);
            $table->string('code')->nullable();
            $table->unsignedTinyInteger('tried_time')->default(0);
            $table->dateTime('closed_at');
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->unsignedBigInteger('creator_id');
            $table->string('creator_ip');
            $table->boolean('middleware_should_count')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_has_verifications');
    }
};
