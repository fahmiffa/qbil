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
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedSmallInteger('unique_code')->nullable()->after('due_date');
        });

        // Backfill: assign unique_code per user_id, ordered by customer name (abjad A→Z)
        // Starting from 050, incrementing by 1 per customer within each user group
        $users = DB::table('customers')
            ->select('user_id')
            ->distinct()
            ->get();

        foreach ($users as $user) {
            $customers = DB::table('customers')
                ->where('user_id', $user->user_id)
                ->orderBy('name', 'asc')
                ->get();

            $code = 50; // Start from 050
            foreach ($customers as $customer) {
                DB::table('customers')
                    ->where('id', $customer->id)
                    ->update(['unique_code' => $code]);
                $code++;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('unique_code');
        });
    }
};
