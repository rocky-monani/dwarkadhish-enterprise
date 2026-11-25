<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoOnlineCaseNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_inward_entry_without_screenshot_defaults_case_number_to_no_online(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/complaints', [
            'battery_type' => 'TW',
            'battery_serial_number' => 'OFFLINE123456',
            'complaint_number' => '8001',
            'complaint_date' => date('Y-m-d'),
            'customer_name' => 'OFFLINE CUSTOMER',
            'product_model' => 'BTZ4L',
            'battery_connect_image_path' => null,
        ]);

        $response->assertRedirect(route('complaints.create'));

        $this->assertDatabaseHas('battery_complaints', [
            'complaint_number' => '8001',
            'case_number' => 'NO ONLINE',
        ]);
    }

    public function test_inward_entry_with_screenshot_keeps_case_number_null_if_not_provided(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/complaints', [
            'battery_type' => 'TW',
            'battery_serial_number' => 'ONLINE123456',
            'complaint_number' => '8002',
            'complaint_date' => date('Y-m-d'),
            'customer_name' => 'ONLINE CUSTOMER',
            'product_model' => 'BTZ4L',
            'battery_connect_image_path' => 'storage/battery_captures/test.jpg',
        ]);

        $response->assertRedirect(route('complaints.create'));

        $this->assertDatabaseHas('battery_complaints', [
            'complaint_number' => '8002',
            'case_number' => null,
        ]);
    }
}
