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
        Schema::create('custom_pages', function (Blueprint $table) {
            $table->string('pathname',  768)->primary(); // Google SERP max 1855 and primary varchar max 768
            $table->string('title', 43); // max 60 - length of ' | Mensa Hong Kong'
            $table->string('og_image_url', 65535)->nullable();
            $table->string('description', 65);
            $table->string('content', 65535)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_pages');
    }
};
