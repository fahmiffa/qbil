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
        Schema::table('routers', function (Blueprint $table) {
            $table->string('connection_status')->default('unknown')->after('password'); // online, offline, unknown
            $table->integer('ping_ms')->nullable()->after('connection_status');
            $table->text('connection_error')->nullable()->after('ping_ms');
            $table->timestamp('last_checked_at')->nullable()->after('connection_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn(['connection_status', 'ping_ms', 'connection_error', 'last_checked_at']);
        });
    }
};
