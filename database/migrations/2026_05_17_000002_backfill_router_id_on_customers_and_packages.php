<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill router_id pada customers & packages yang sudah ada di DB.
     * 
     * SAFE: Hanya mengisi data yang NULL. Tidak mengubah/menghapus data yang sudah ada.
     * Setiap customer/package lama akan diisi dengan router pertama milik user-nya.
     */
    public function up(): void
    {
        // Backfill customers.router_id
        DB::statement("
            UPDATE customers c
            INNER JOIN (
                SELECT user_id, MIN(id) as router_id
                FROM routers
                GROUP BY user_id
            ) r ON c.user_id = r.user_id
            SET c.router_id = r.router_id
            WHERE c.router_id IS NULL
        ");

        // Backfill packages.router_id
        DB::statement("
            UPDATE packages p
            INNER JOIN (
                SELECT user_id, MIN(id) as router_id
                FROM routers
                GROUP BY user_id
            ) r ON p.user_id = r.user_id
            SET p.router_id = r.router_id
            WHERE p.router_id IS NULL
        ");
    }

    /**
     * Tidak ada rollback untuk data backfill (aman, hanya SET ke NULL kembali).
     */
    public function down(): void
    {
        DB::table('customers')->update(['router_id' => null]);
        DB::table('packages')->update(['router_id' => null]);
    }
};
