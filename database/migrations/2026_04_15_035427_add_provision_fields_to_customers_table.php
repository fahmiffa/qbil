<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete()->after('user_id');
            $table->string('username')->nullable()->after('package_id');
            $table->string('password')->nullable()->after('username');
            $table->enum('service_type', ['hotspot', 'pppoe'])->default('hotspot')->after('password');
            $table->timestamp('activated_at')->nullable()->after('service_type');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn(['package_id', 'username', 'password', 'service_type', 'activated_at']);
        });
    }
};
