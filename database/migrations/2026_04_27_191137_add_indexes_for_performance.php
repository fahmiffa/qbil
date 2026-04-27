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
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('status');
            $table->index('due_date');
            $table->index('billing_period');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('status');
            if (Schema::hasColumn('customers', 'id_pelanggan')) {
                $table->index('id_pelanggan');
            }
            if (Schema::hasColumn('customers', 'username')) {
                $table->index('username');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['billing_period']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['status']);
            if (Schema::hasColumn('customers', 'id_pelanggan')) {
                $table->dropIndex(['id_pelanggan']);
            }
            if (Schema::hasColumn('customers', 'username')) {
                $table->dropIndex(['username']);
            }
        });
    }
};
