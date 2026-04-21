<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppp_profiles', function (Blueprint $table) {
            $table->string('only_one')->nullable()->change();
            $table->string('dns_server')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ppp_profiles', function (Blueprint $table) {
            $table->string('only_one')->nullable(false)->change();
            $table->string('dns_server')->nullable(false)->change();
        });
    }
};
