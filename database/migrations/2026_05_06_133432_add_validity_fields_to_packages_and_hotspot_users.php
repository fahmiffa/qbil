<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah ke tabel packages
        Schema::table('packages', function (Blueprint $table) {
            // Masa aktif setelah login pertama (format MikroTik: "1d", "12h", "1w")
            if (!Schema::hasColumn('packages', 'masa_aktif')) {
                $table->string('masa_aktif')->nullable()->after('limit_time');
            }
            // Masa berlaku sebelum diaktivasi (format: "30d", "7d")
            if (!Schema::hasColumn('packages', 'valid_duration')) {
                $table->string('valid_duration')->nullable()->after('masa_aktif');
            }
        });

        // Tambah ke tabel hotspot_users
        Schema::table('hotspot_users', function (Blueprint $table) {
            // Kapan user pertama kali login (diisi oleh CleanupJob)
            if (!Schema::hasColumn('hotspot_users', 'activated_at')) {
                $table->timestamp('activated_at')->nullable()->after('expired_at');
            }
            // Batas terakhir voucher boleh diaktivasi (sebelum login pertama)
            if (!Schema::hasColumn('hotspot_users', 'valid_until')) {
                $table->timestamp('valid_until')->nullable()->after('activated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['masa_aktif', 'valid_duration']);
        });
        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->dropColumn(['activated_at', 'valid_until']);
        });
    }
};
