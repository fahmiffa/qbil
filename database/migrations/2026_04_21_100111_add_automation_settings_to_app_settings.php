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
        Schema::table('app_settings', function (Blueprint $table) {
            $table->integer('invoice_gen_days')->default(-1);
            $table->time('invoice_gen_time')->default('00:00');
            $table->integer('isolate_days')->default(0);
            $table->time('isolate_time')->default('00:05');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn(['invoice_gen_days', 'invoice_gen_time', 'isolate_days', 'isolate_time']);
        });
    }
};
