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
        Schema::table('packages', function (Blueprint $table) {
            $table->string('burst_upload')->nullable()->after('speed_download');
            $table->string('burst_download')->nullable()->after('burst_upload');
            $table->integer('burst_threshold')->nullable()->after('burst_download');
            $table->integer('limit_at')->nullable()->after('burst_threshold');
            $table->integer('burst_duration')->nullable()->after('limit_at');
            $table->integer('priority')->default(8)->after('burst_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'burst_upload',
                'burst_download',
                'burst_threshold',
                'limit_at',
                'burst_duration',
                'priority',
            ]);
        });
    }
};
