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
        Schema::create('custom_web_pages', function (Blueprint $table) {
            $table->id();
            $table->string('pathname', 768)->unique(); // SEO max 1855 - max domain 256 = 1699 but unique varchar max 768
            $table->string('title', 60); // SEO max 60
            $table->string('og_image_url', 8000)->nullable();
            $table->string('description', 65);
            $table->mediumText('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_web_pages');
    }
};
