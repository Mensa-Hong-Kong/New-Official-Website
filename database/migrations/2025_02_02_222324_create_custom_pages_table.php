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
            $table->string('pathname', 768)->primary(); // SEO max 1855 and primary varchar max 768
            $table->string('title', 60); // SEO max 60
            $table->string('og_image_url', 15484)->nullable(); // varchar max 16383 but max row size limit only can 15484
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
        Schema::dropIfExists('custom_pages');
    }
};
