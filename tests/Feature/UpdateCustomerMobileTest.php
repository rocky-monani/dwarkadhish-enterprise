<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCustomerMobileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_customer_mobile_for_complaint(): void
    {
        $user = User::factory()->create();

        $complaint = BatteryComplaint::create([
            'battery_type' => 'TW',
            'complaint_number' => '10050',
            'complaint_date' => date('Y-m-d'),
            'customer_name' => 'Mobile Test Customer',
            'customer_mobile' => null,
            'battery_serial_number' => 'BATTEST999',
            'complaint_status' => 'Open',
        ]);

        $response = $this->actingAs($user)->postJson("/complaints/{$complaint->id}/customer-mobile", [
            'customer_mobile' => '9876500000',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('battery_complaints', [
            'id' => $complaint->id,
            'customer_mobile' => '9876500000',
        ]);
    }
}
