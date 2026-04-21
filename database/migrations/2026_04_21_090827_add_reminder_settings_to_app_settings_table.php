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
            $table->integer('reminder_1_days')->default(-1);
            $table->time('reminder_1_time')->default('08:00');
            $table->integer('reminder_2_days')->default(0);
            $table->time('reminder_2_time')->default('08:00');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn(['reminder_1_days', 'reminder_1_time', 'reminder_2_days', 'reminder_2_time']);
        });
    }
};
