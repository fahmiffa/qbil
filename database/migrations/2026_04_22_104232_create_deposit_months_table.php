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
        Schema::create('deposit_months', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('deposit_id')->constrained()->cascadeOnDelete();
            $table->date('month'); // Store as e.g. 2026-01-01
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_months');
    }
};
