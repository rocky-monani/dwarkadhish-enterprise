<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintTodayInwardListTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_today_2w_and_4w_inward_list_print_report(): void
    {
        $user = User::factory()->create();

        $today = date('Y-m-d');

        BatteryComplaint::create([
            'battery_type' => 'TW',
            'complaint_number' => '4383',
            'case_number' => '14438943',
            'complaint_date' => $today,
            'purchase_date' => '2024-09-09',
            'product_model' => 'BTX5L',
            'battery_serial_number' => 'BAA02132341487',
            'customer_name' => 'VIPULBHAI RANA',
            'customer_mobile' => '9054601608',
            'location' => 'KHAMPALIA',
            'is_digital_warranty' => true,
            'online_reg_type' => 'RC BOOK',
            'complaint_status' => 'Open',
        ]);

        BatteryComplaint::create([
            'battery_type' => 'FW',
            'complaint_number' => '4400',
            'case_number' => '14438940',
            'complaint_date' => $today,
            'purchase_date' => '2025-09-01',
            'product_model' => 'AP-BTZ5L',
            'battery_serial_number' => 'BAF04332404100',
            'customer_name' => 'TAJ AUTO AGENCY',
            'customer_mobile' => '8140937421',
            'location' => 'JAMNAGAR',
            'is_digital_warranty' => true,
            'online_reg_type' => 'WARRANTY CARD',
            'complaint_status' => 'Open',
        ]);

        // 2W Report
        $response2W = $this->actingAs($user)->get('/complaints/print-today-list?category=TW');
        $response2W->assertStatus(200);
        $response2W->assertSee('2W DAILY INWARD COMPLAINT REGISTER');
        $response2W->assertSee('4383');
        $response2W->assertSee('VIPULBHAI RANA');
        $response2W->assertDontSee('DIGITAL WARRANTY 2');
        $response2W->assertDontSee('DE CASH MEMO');

        // 4W Report
        $response4W = $this->actingAs($user)->get('/complaints/print-today-list?category=FW');
        $response4W->assertStatus(200);
        $response4W->assertSee('4W DAILY INWARD REGISTER (SHEET 1: WARRANTY CARD / RC BOOK)');
        $response4W->assertSee('4W DAILY INWARD REGISTER (SHEET 2: NOT DOWNLOADED / PENDING)');
        $response4W->assertSee('4400');
        $response4W->assertSee('TAJ AUTO AGENCY');
        $response4W->assertSee('DIGITAL WARRANTY 2');
        $response4W->assertSee('DE CASH MEMO');
    }

    public function test_individual_battery_print_shows_service_battery_value(): void
    {
        $user = User::factory()->create();

        $complaint = BatteryComplaint::create([
            'battery_type' => 'TW',
            'complaint_number' => '5501',
            'case_number' => '14438999',
            'complaint_date' => date('Y-m-d'),
            'product_model' => 'BTX5L',
            'battery_serial_number' => 'TESTSN12345',
            'customer_name' => 'SERVICE TEST USER',
            'customer_mobile' => '9876543210',
            'location' => 'RAJKOT',
            'is_service_battery_given' => true,
            'service_battery_number' => 'SB-998877',
            'complaint_status' => 'Open',
        ]);

        $response = $this->actingAs($user)->get("/complaints/{$complaint->id}/print");
        $response->assertStatus(200);
        $response->assertSee('Serv. Batt: SB-998877');
    }
}

