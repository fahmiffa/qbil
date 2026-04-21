<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('router_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('address'); // Stores ranges
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_pools');
    }
};
