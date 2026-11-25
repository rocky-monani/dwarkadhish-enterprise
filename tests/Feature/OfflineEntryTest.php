<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_offline_tw_complaint_without_online_app_details(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/complaints', [
            'battery_serial_number' => 'OFFLINETW123',
            'product_model' => 'BTX5L',
            'complaint_number' => '1005',
            'complaint_date' => '2026-08-30',
            'customer_name' => 'OFFLINE CUSTOMER',
            'customer_mobile' => '9876543210',
            'location' => 'JAMNAGAR',
            'is_digital_warranty' => false,
            'battery_connect_image_path' => null,
            'battery_connect_data' => null,
        ]);

        $response->assertRedirect(route('complaints.create'));

        $this->assertDatabaseHas('battery_complaints', [
            'battery_serial_number' => 'OFFLINETW123',
            'complaint_number' => '1005',
            'customer_name' => 'OFFLINE CUSTOMER',
            'is_digital_warranty' => false,
            'battery_connect_image_path' => null,
        ]);
    }

    public function test_stores_prorata_percentage_in_database_and_renders_on_print(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/complaints', [
            'battery_serial_number' => 'BAE0214Y433245',
            'product_model' => 'AP-BTZ4L',
            'complaint_number' => '1006',
            'complaint_date' => '2026-08-30',
            'customer_name' => 'PRORATA TESTER',
            'customer_mobile' => '9876543212',
            'is_prorata' => '1',
            'prorata_percentage' => '42',
        ]);

        $response->assertRedirect(route('complaints.create'));

        $this->assertDatabaseHas('battery_complaints', [
            'battery_serial_number' => 'BAE0214Y433245',
            'complaint_number' => '1006',
            'is_prorata' => true,
            'prorata_percentage' => 42.00,
        ]);

        $complaint = BatteryComplaint::where('complaint_number', '1006')->first();

        $printResponse = $this->actingAs($user)->get("/complaints/{$complaint->id}/print");
        $printResponse->assertStatus(200);
        $printResponse->assertSee('PRO-RATA:', false);
        $printResponse->assertSee('42%');
    }
}
