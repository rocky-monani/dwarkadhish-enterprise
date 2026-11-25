<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineRegTypeDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_online_reg_type_when_creating_complaint(): void
    {
        $user = User::factory()->create();

        $payload = [
            'battery_type' => 'TW',
            'complaint_number' => '10001',
            'case_number' => 'CRM-12345',
            'online_reg_type' => 'WARRANTY CARD',
            'complaint_date' => '2026-03-01',
            'customer_name' => 'John Doe',
            'customer_mobile' => '9876543210',
            'location' => 'Rajkot',
            'vehicle_name' => 'Splendor',
            'vehicle_number' => 'GJ03AB1234',
            'battery_serial_number' => 'BAT123456',
            'product_model' => 'TZ4',
            'product_segment' => 'TW',
            'purchase_date' => '2025-10-01',
        ];

        $response = $this->actingAs($user)->post(route('complaints.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('battery_complaints', [
            'complaint_number' => '10001',
            'online_reg_type' => 'WARRANTY CARD',
        ]);
    }

    public function test_can_update_online_reg_type_when_editing_complaint(): void
    {
        $user = User::factory()->create();

        $complaint = BatteryComplaint::create([
            'battery_type' => 'FW',
            'complaint_number' => '10002',
            'online_reg_type' => 'NOT DOWNLOADED',
            'complaint_date' => '2026-03-01',
            'customer_name' => 'Jane Smith',
            'customer_mobile' => '9876543211',
            'location' => 'Ahmedabad',
            'battery_serial_number' => 'BAT999999',
            'product_segment' => 'FW',
            'complaint_status' => 'Open',
        ]);

        $payload = [
            'battery_type' => 'FW',
            'complaint_number' => '10002',
            'online_reg_type' => 'RC BOOK',
            'complaint_date' => '2026-03-01',
            'customer_name' => 'Jane Smith',
            'customer_mobile' => '9876543211',
            'location' => 'Ahmedabad',
            'battery_serial_number' => 'BAT999999',
            'product_segment' => 'FW',
            'complaint_status' => 'Open',
        ];

        $response = $this->actingAs($user)->put(route('complaints.update', $complaint->id), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('battery_complaints', [
            'id' => $complaint->id,
            'online_reg_type' => 'RC BOOK',
        ]);
    }

    public function test_online_reg_type_dropdown_is_outside_auto_fetched_fields_row_on_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('complaints.create'));

        $response->assertStatus(200);
        $content = $response->getContent();

        // Extract auto_fetched_fields_row div block
        $this->assertStringContainsString('id="auto_fetched_fields_row"', $content);
        
        // Ensure online_reg_type is NOT inside auto_fetched_fields_row
        preg_match('/<div[^>]*id="auto_fetched_fields_row"[^>]*>(.*?)<\/div>/s', $content, $matches);
        $this->assertNotEmpty($matches);
        $this->assertStringNotContainsString('id="online_reg_type"', $matches[1]);
        $this->assertStringContainsString('id="online_reg_type"', $content);
    }
}
