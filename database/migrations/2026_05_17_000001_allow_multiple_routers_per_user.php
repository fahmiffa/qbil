<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * SAFE for production:
     * - Menghapus UNIQUE constraint pada user_id di routers (agar 1 user bisa punya banyak router)
     * - Kolom router_id di customers & packages bersifat NULLABLE (backward compatible)
     * - Data lama tetap valid, tidak ada breaking change
     */
    public function up(): void
    {
        // 1. Drop unique constraint pada routers.user_id
        //    Di MySQL, kita harus drop foreign key dulu sebelum drop index yang dipakai oleh FK tersebut.
        Schema::table('routers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // 2. Tambah router_id ke customers (nullable = backward compatible)
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('router_id')
                ->nullable()
                ->after('user_id')
                ->constrained('routers')
                ->nullOnDelete();
        });

        // 3. Tambah router_id ke packages (nullable = backward compatible)
        Schema::table('packages', function (Blueprint $table) {
            $table->foreignId('router_id')
                ->nullable()
                ->after('user_id')
                ->constrained('routers')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['router_id']);
            $table->dropColumn('router_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['router_id']);
            $table->dropColumn('router_id');
        });

        Schema::table('routers', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }
};
