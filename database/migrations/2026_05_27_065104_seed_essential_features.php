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
        $features = [
            ['name' => 'Manajemen MikroTik', 'parameter' => 'mikrotik'],
            ['name' => 'WhatsApp Gateway', 'parameter' => 'whatsapp'],
            ['name' => 'Langganan Prabayar', 'parameter' => 'pra'],
            ['name' => 'Langganan Pascabayar', 'parameter' => 'pasca'],
        ];

        foreach ($features as $feature) {
            \App\Models\Feature::updateOrCreate(
                ['parameter' => $feature['parameter']],
                ['name' => $feature['name']]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Feature::whereIn('parameter', ['mikrotik', 'whatsapp', 'pra', 'pasca'])->delete();
    }
};
