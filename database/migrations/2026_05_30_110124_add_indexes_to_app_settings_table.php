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
            $table->index('invoice_gen_time');
            $table->index('reminder_1_time');
            $table->index('reminder_2_time');
            $table->index('isolate_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropIndex(['invoice_gen_time']);
            $table->dropIndex(['reminder_1_time']);
            $table->dropIndex(['reminder_2_time']);
            $table->dropIndex(['isolate_time']);
        });
    }
};
