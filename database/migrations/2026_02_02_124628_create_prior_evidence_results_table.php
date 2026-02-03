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
        Schema::create('prior_evidence_results', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->primary();
            $table->unsignedBigInteger('test_id');
            $table->date('taken_on');
            $table->string('score');
            $table->decimal('percent_of_group', 2, 2)->nullable();
            $table->boolean('is_pass')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prior_evidence_results');
    }
};
