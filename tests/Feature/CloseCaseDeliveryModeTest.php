<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloseCaseDeliveryModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_inward_page_does_not_contain_delivery_mode_input(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/complaints/create');
        $response->assertStatus(200);
        $response->assertDontSee('id="delivery_mode"', false);
    }

    public function test_case_close_page_allows_setting_delivery_mode(): void
    {
        $user = User::factory()->create();

        $complaint = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'DEL123456',
            'complaint_number' => '7001',
            'complaint_date' => date('Y-m-d'),
            'customer_name' => 'DELIVERY MODE TESTER',
            'complaint_status' => 'Open',
        ]);

        $response = $this->actingAs($user)->postJson("/complaints/{$complaint->id}/close-direct", [
            'final_decision' => 'F',
            'decision_date' => date('Y-m-d'),
            'closing_date' => date('Y-m-d'),
            'new_battery_number' => 'NEW123456',
            'delivery_mode' => 'BY HIMSELF',
            'complaint_status' => 'Closed',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('battery_complaints', [
            'id' => $complaint->id,
            'delivery_mode' => 'BY HIMSELF',
            'complaint_status' => 'Closed',
        ]);
    }
}
