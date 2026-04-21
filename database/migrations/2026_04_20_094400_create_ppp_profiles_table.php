<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppp_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('router_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('local_address')->nullable();
            $table->string('remote_address')->nullable();
            $table->string('rate_limit')->nullable();
            $table->string('only_one')->nullable();
            $table->string('dns_server')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppp_profiles');
    }
};
