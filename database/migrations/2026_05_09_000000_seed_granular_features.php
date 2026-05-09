<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $features = [
            [
                'name' => 'Layanan PPPoE',
                'parameter' => 'pppoe',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Layanan Static',
                'parameter' => 'static',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Layanan Hotspot',
                'parameter' => 'hotspot',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($features as $feature) {
            DB::table('features')->updateOrInsert(
                ['parameter' => $feature['parameter']],
                ['name' => $feature['name'], 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('features')->whereIn('parameter', ['pppoe', 'static', 'hotspot'])->delete();
    }
};
