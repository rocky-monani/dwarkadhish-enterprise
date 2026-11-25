<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProrataAndLeadTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_out_of_warranty_from_battery_connect_is_treated_as_prorata(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/complaints', [
            'battery_type' => 'TW',
            'battery_serial_number' => 'EXPIRED123456',
            'complaint_number' => '9001',
            'complaint_date' => date('Y-m-d'),
            'customer_name' => 'EXPIRED WARRANTY CUSTOMER',
            'product_model' => 'BTZ4L',
            'battery_connect_data' => json_encode([
                'warranty_status' => 'Out of Warranty',
            ]),
        ]);

        $response->assertRedirect(route('complaints.create'));

        $this->assertDatabaseHas('battery_complaints', [
            'complaint_number' => '9001',
            'is_prorata' => true,
        ]);
    }

    public function test_lead_time_exceeding_90_days_shows_warning_on_pending_cases(): void
    {
        $user = User::factory()->create();

        $complaintDate = Carbon::today()->toDateString();
        $purchaseDate = Carbon::today()->subDays(120)->toDateString(); // 120 days ago (> 90 days)

        BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'LEADTIME90',
            'complaint_number' => '9002',
            'complaint_date' => $complaintDate,
            'purchase_date' => $purchaseDate,
            'customer_name' => 'LEAD TIME TESTER',
            'complaint_status' => 'Open',
            'battery_connect_data' => [
                'lead_time_1' => 120,
            ],
        ]);

        $response = $this->actingAs($user)->get('/complaints/pending-cases');

        $response->assertStatus(200);
        $response->assertSee('Lead time &gt; 90d', false);
        $response->assertSee('do not enter', false);
    }

    public function test_lead_time_1_calculates_from_primary_billing_to_sale_date(): void
    {
        $complaint = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'BL1000RMF',
            'complaint_number' => '1842',
            'complaint_date' => '2026-09-17',
            'purchase_date' => '2024-12-19',
            'customer_name' => 'TEST CUSTOMER',
            'complaint_status' => 'Open',
            'battery_connect_data' => [
                'date_of_primary_billing' => '26-Nov-2024',
                'date_of_sale' => '19-Dec-2024',
            ],
        ]);

        $this->assertEquals(23, $complaint->lead_time_days);
        $this->assertFalse($complaint->isLeadTimeExceeded());
    }
}
