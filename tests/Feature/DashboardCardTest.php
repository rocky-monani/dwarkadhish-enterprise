<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_today_tw_and_fw_counts_separately(): void
    {
        $user = User::factory()->create();
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        // Create Today TW entry
        BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'TW1111',
            'complaint_number' => '1001',
            'complaint_date' => $today,
            'customer_name' => 'Today TW Customer',
        ]);

        // Create Today FW entry
        BatteryComplaint::create([
            'battery_type' => 'FW',
            'battery_serial_number' => 'FW2222',
            'complaint_number' => '1002',
            'complaint_date' => $today,
            'customer_name' => 'Today FW Customer',
        ]);

        // Create Yesterday TW entry (should not be counted in today's TW card)
        BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'TW3333',
            'complaint_number' => '1003',
            'complaint_date' => $yesterday,
            'customer_name' => 'Yesterday TW Customer',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('todaysTwCount', 1);
        $response->assertViewHas('todaysFwCount', 1);
        $response->assertViewHas('twCount', 2);
        $response->assertViewHas('fwCount', 1);

        $response->assertSee('Today inward');
        $response->assertSee('1 TW · 1 FW');
        $response->assertDontSee('Total TW (2W)');
        $response->assertDontSee('Total 4W / Commercial');
        $response->assertDontSee('Open Cases');
        $response->assertDontSee('Closed / Settled');
        $response->assertDontSee('Pro-Rata Cases');
    }
}
