<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_export_headers_and_uppercase_data(): void
    {
        $user = User::factory()->create();

        BatteryComplaint::create([
            'complaint_number' => '3634',
            'complaint_date' => '2026-08-14',
            'case_number' => '13965794',
            'purchase_date' => '2025-08-28',
            'product_model' => 'btx5l',
            'battery_serial_number' => 'ea002062103054',
            'is_digital_warranty' => true,
            'vehicle_number' => 'gj10bn3539',
            'vehicle_name' => '',
            'final_decision' => 'free replacement',
            'customer_mobile' => '9428724829',
            'customer_name' => 'momai auto garage',
            'location' => 'jam',
            'closing_date' => '2026-08-17',
            'new_battery_number' => 'cab0231a204086',
            'return_date' => '2026-08-17',
            'delivery_mode' => 'himself',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/export/csv?type=tw');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        // Remove UTF-8 BOM
        $cleanContent = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = explode("\n", trim($cleanContent));

        $this->assertCount(2, $lines);

        $headers = str_getcsv($lines[0]);
        $expectedHeaders = [
            'ZW COMPLAIN NO',
            'ZW COMPLAIN DATE',
            'CASE NO',
            'DATE OF SALE',
            'PRODUCT',
            'PROD.SEG.',
            'OLD BATTERY SERIAL NO.',
            'DIGITAL WARANTY',
            'VEHICLE NO. (IF NOT ONLINE REG.)',
            'VEHICLE TYPE (IF NOT ONLINE REG.)',
            'Decision',
            'SERVICE BATT. GIVEN',
            'MOBILE NO',
            'CUTOMER/DEALER NAME',
            'LOCATION',
            'CLOSING DATE',
            'NEW BATTERY SERIAL NO.',
            'RETURN DATE',
            'TAKE PERSON NAME & MOBILE NO.',
        ];

        $this->assertEquals($expectedHeaders, $headers);

        $row = str_getcsv($lines[1]);
        $this->assertEquals('3634', $row[0]);
        $this->assertEquals('14-08-2026', $row[1]);
        $this->assertEquals('13965794', $row[2]);
        $this->assertEquals('28-08-2025', $row[3]);
        $this->assertEquals('BTX5L', $row[4]); // Uppercased
        $this->assertEquals('TW', $row[5]);
        $this->assertEquals('EA002062103054', $row[6]); // Uppercased
        $this->assertEquals('YES', $row[7]);
        $this->assertEquals('GJ10BN3539', $row[8]); // Uppercased
        $this->assertEquals('F', $row[10]); // Decision code
        $this->assertEquals('MOMAI AUTO GARAGE', $row[13]); // Uppercased
        $this->assertEquals('JAM', $row[14]); // Uppercased
        $this->assertEquals('17-08-2026', $row[15]);
        $this->assertEquals('CAB0231A204086', $row[16]); // Uppercased
        $this->assertEquals('17-08-2026', $row[17]);
        $this->assertEquals('HIMSELF', $row[18]); // Uppercased
    }

    public function test_csv_export_ordering_oldest_first(): void
    {
        $user = User::factory()->create();

        BatteryComplaint::create([
            'complaint_number' => '1001',
            'complaint_date' => '2026-08-01',
            'battery_serial_number' => 'OLD12345',
            'customer_name' => 'OLD CUSTOMER',
            'created_by' => $user->id,
        ]);

        BatteryComplaint::create([
            'complaint_number' => '1002',
            'complaint_date' => '2026-08-15',
            'battery_serial_number' => 'NEW67890',
            'customer_name' => 'NEW CUSTOMER',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/export/csv?type=tw');
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $response->streamedContent());
        $lines = explode("\n", trim($content));

        $this->assertCount(3, $lines); // 1 header line + 2 data lines

        $row1 = str_getcsv($lines[1]);
        $row2 = str_getcsv($lines[2]);

        // Old entry at top
        $this->assertEquals('1001', $row1[0]);
        $this->assertEquals('01-08-2026', $row1[1]);

        // Latest entry at bottom
        $this->assertEquals('1002', $row2[0]);
        $this->assertEquals('15-08-2026', $row2[1]);
    }

    public function test_csv_export_prorata_percentage_decision(): void
    {
        $user = User::factory()->create();

        BatteryComplaint::create([
            'complaint_number' => '1003',
            'complaint_date' => '2026-08-20',
            'battery_serial_number' => 'BAE0214Y433245',
            'customer_name' => 'PRORATA CUSTOMER',
            'is_prorata' => true,
            'prorata_percentage' => 45.00,
            'final_decision' => 'P',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/export/csv?type=tw');
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $response->streamedContent());
        $lines = explode("\n", trim($content));

        $row = str_getcsv($lines[1]);
        $this->assertEquals('P (45%)', $row[10]); // Decision column outputs P (45%)
    }
}
