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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('id_pelanggan')->nullable()->after('id');
            $table->string('keterangan')->nullable()->after('address');
            $table->string('username')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('service_type')->default('static')->change(); // Use string for easier transition
            $table->unsignedBigInteger('package_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['id_pelanggan', 'keterangan']);
        });
    }
};
