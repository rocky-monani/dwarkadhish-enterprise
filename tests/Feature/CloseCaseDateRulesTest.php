<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloseCaseDateRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_close_case_saves_return_date_and_returns_raw_complaint_date(): void
    {
        $user = User::factory()->create();

        $complaint = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'DATE123456',
            'complaint_number' => '8001',
            'complaint_date' => '2026-09-20',
            'customer_name' => 'DATE RULES TESTER',
            'complaint_status' => 'Open',
        ]);

        // Test live search endpoint returns raw_complaint_date
        $searchResponse = $this->actingAs($user)->getJson('/complaints/live-search-open?complaint_number=8001');
        $searchResponse->assertStatus(200);
        $searchResponse->assertJsonFragment([
            'complaint_number' => '8001',
            'raw_complaint_date' => '2026-09-20',
        ]);

        // Test closing case with return_date
        $closeResponse = $this->actingAs($user)->postJson("/complaints/{$complaint->id}/close-direct", [
            'final_decision' => 'C',
            'decision_date' => '2026-09-21', // 1 day after inward
            'closing_date' => '2026-09-24',
            'return_date' => '2026-09-24',
            'new_battery_number' => 'RECHARGED',
            'delivery_mode' => 'BY RETAILER',
            'complaint_status' => 'Closed',
        ]);

        $closeResponse->assertStatus(200);
        $closeResponse->assertJson(['success' => true]);

        $complaint = $complaint->fresh();
        $this->assertEquals('C', $complaint->final_decision);
        $this->assertEquals('2026-09-21', $complaint->decision_date->format('Y-m-d'));
        $this->assertEquals('2026-09-24', $complaint->closing_date->format('Y-m-d'));
        $this->assertEquals('2026-09-24', $complaint->return_date->format('Y-m-d'));
        $this->assertEquals('RECHARGED', $complaint->new_battery_number);
        $this->assertEquals('Closed', $complaint->complaint_status);
    }

    public function test_close_case_modal_contains_return_date_and_auto_date_scripts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/complaints/close-case');
        $response->assertStatus(200);
        $response->assertSee('id="modal_return_date"', false);
        $response->assertSee('-- Select Final Decision --', false);
        $response->assertSee('window.location.reload()', false);
    }
}
