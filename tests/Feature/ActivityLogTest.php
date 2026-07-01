<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_activities_page_requires_authentication()
    {
        $this->get('/activities')
            ->assertRedirect('/login');
    }

    public function test_activities_page_loads_for_authenticated_users()
    {
        $user = User::factory()->create([
            'role' => 1, // Regular user, bypasses router check because hasFeature('mikrotik') is false
        ]);

        $this->actingAs($user)
            ->get('/activities')
            ->assertOk()
            ->assertSee('Log Aktivitas Sistem');
    }

    public function test_activities_can_be_filtered_by_date()
    {
        $user = User::factory()->create(['role' => 0]);

        // Create log on 2026-07-01 using DB facade to prevent Eloquent timestamp auto-updating
        \Illuminate\Support\Facades\DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'title' => 'Aktivitas Alpha',
            'message' => 'Detail A',
            'type' => 'system',
            'created_at' => '2026-07-01 10:00:00',
            'updated_at' => '2026-07-01 10:00:00'
        ]);

        // Create log on 2026-07-05 using DB facade
        \Illuminate\Support\Facades\DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'title' => 'Aktivitas Beta',
            'message' => 'Detail B',
            'type' => 'system',
            'created_at' => '2026-07-05 10:00:00',
            'updated_at' => '2026-07-05 10:00:00'
        ]);

        // Livewire test
        Livewire::actingAs($user)
            ->test(\App\Livewire\Activities\ActivityLog::class)
            ->assertSee('Aktivitas Alpha')
            ->assertSee('Aktivitas Beta')
            // Apply start_date
            ->set('start_date', '2026-07-02')
            ->assertDontSee('Aktivitas Alpha')
            ->assertSee('Aktivitas Beta')
            // Apply end_date
            ->set('end_date', '2026-07-06')
            ->assertDontSee('Aktivitas Alpha')
            ->assertSee('Aktivitas Beta')
            // Change end_date so Aktivitas Beta is excluded
            ->set('end_date', '2026-07-04')
            ->assertDontSee('Aktivitas Alpha')
            ->assertDontSee('Aktivitas Beta')
            // Reset filters
            ->call('resetFilters')
            ->assertSee('Aktivitas Alpha')
            ->assertSee('Aktivitas Beta');
    }

    public function test_activities_can_be_filtered_by_category()
    {
        $user = User::factory()->create(['role' => 0]);

        // Create system log
        \Illuminate\Support\Facades\DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'title' => 'Log System 1',
            'message' => 'Detail Sys',
            'type' => 'system',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create customer log
        \Illuminate\Support\Facades\DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'title' => 'Log Customer 1',
            'message' => 'Detail Cust',
            'type' => 'customer.created',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Activities\ActivityLog::class)
            ->assertSee('Log System 1')
            ->assertSee('Log Customer 1')
            // Filter by system category
            ->set('category', 'system')
            ->assertSee('Log System 1')
            ->assertDontSee('Log Customer 1')
            // Filter by customer category
            ->set('category', 'customer')
            ->assertDontSee('Log System 1')
            ->assertSee('Log Customer 1')
            // Reset filters
            ->call('resetFilters')
            ->assertSee('Log System 1')
            ->assertSee('Log Customer 1');
    }
}
