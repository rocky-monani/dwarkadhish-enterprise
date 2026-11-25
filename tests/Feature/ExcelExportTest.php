<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use App\Services\InwardExcelExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExcelExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected InwardExcelExportService $exportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->exportService = new InwardExcelExportService();
    }

    public function test_export_builds_fresh_snapshot_from_current_database(): void
    {
        $rec1 = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'EAB0206Z103054',
            'complaint_number' => '3634',
            'complaint_date' => '2026-09-01',
            'customer_name' => 'TW CUSTOMER ONE',
            'product_model' => 'BTX5L',
        ]);

        $rec2 = BatteryComplaint::create([
            'battery_type' => 'FW',
            'battery_serial_number' => 'CBA0939X320617',
            'complaint_number' => '1520',
            'complaint_date' => '2026-09-02',
            'customer_name' => 'FW CUSTOMER TWO',
            'product_model' => 'DIN50R',
        ]);

        $result = $this->exportService->generateExport();

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['count']);
        $this->assertEquals(1, $result['written_2w']);
        $this->assertEquals(1, $result['written_4w']);

        // Inspect generated output file
        $spreadsheet = IOFactory::load($result['file_path']);

        $sheet2W = $spreadsheet->getSheetByName('2W');
        $sheet4W = $spreadsheet->getSheetByName('4W');

        // Check 2W data at Row 8
        $this->assertEquals('3634', $sheet2W->getCell('A8')->getValue());
        $this->assertEquals('EAB0206Z103054', $sheet2W->getCell('G8')->getValue());
        $this->assertEquals('TW CUSTOMER ONE', $sheet2W->getCell('P8')->getValue());

        // Check 4W data at Row 8
        $this->assertEquals('1520', $sheet4W->getCell('A8')->getValue());
        $this->assertEquals('CBA0939X320617', $sheet4W->getCell('G8')->getValue());
        $this->assertEquals('FW CUSTOMER TWO', $sheet4W->getCell('P8')->getValue());
    }

    public function test_edited_database_record_reflects_in_new_export(): void
    {
        $rec = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'EAB0206Z103054',
            'complaint_number' => '3634',
            'complaint_date' => '2026-09-01',
            'customer_name' => 'OLD NAME',
        ]);

        // Edit database record
        $rec->update(['customer_name' => 'NEW EDITED NAME']);

        $result = $this->exportService->generateExport();
        $spreadsheet = IOFactory::load($result['file_path']);
        $sheet2W = $spreadsheet->getSheetByName('2W');

        $this->assertEquals('NEW EDITED NAME', $sheet2W->getCell('P8')->getValue());
    }

    public function test_deleted_record_disappears_from_new_export(): void
    {
        $rec1 = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'EAB0206Z103054',
            'complaint_number' => '3634',
            'complaint_date' => '2026-09-01',
            'customer_name' => 'FIRST RECORD',
        ]);

        $rec2 = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'BAA0238Z422074',
            'complaint_number' => '3635',
            'complaint_date' => '2026-09-02',
            'customer_name' => 'SECOND RECORD TO DELETE',
        ]);

        // Soft delete record 2
        $rec2->delete();

        $result = $this->exportService->generateExport();
        $this->assertEquals(1, $result['count']);
        $this->assertEquals(1, $result['written_2w']);

        $spreadsheet = IOFactory::load($result['file_path']);
        $sheet2W = $spreadsheet->getSheetByName('2W');

        $this->assertEquals('3634', $sheet2W->getCell('A8')->getValue());
        $this->assertNull($sheet2W->getCell('A9')->getValue());
    }

    public function test_record_moving_from_2w_to_4w(): void
    {
        $rec = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'EAB0206Z103054',
            'complaint_number' => '3634',
            'complaint_date' => '2026-09-01',
            'customer_name' => 'MOVED RECORD',
        ]);

        // Move to FW in database
        $rec->update(['battery_type' => 'FW']);

        $result = $this->exportService->generateExport();
        $this->assertEquals(0, $result['written_2w']);
        $this->assertEquals(1, $result['written_4w']);

        $spreadsheet = IOFactory::load($result['file_path']);
        $sheet2W = $spreadsheet->getSheetByName('2W');
        $sheet4W = $spreadsheet->getSheetByName('4W');

        // 2W sheet must NOT contain moved record
        $this->assertNull($sheet2W->getCell('A8')->getValue());

        // 4W sheet MUST contain moved record
        $this->assertEquals('3634', $sheet4W->getCell('A8')->getValue());
        $this->assertEquals('MOVED RECORD', $sheet4W->getCell('P8')->getValue());
    }

    public function test_date_range_and_vehicle_type_filters(): void
    {
        BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'SER1',
            'complaint_number' => '1001',
            'complaint_date' => '2026-09-01',
            'customer_name' => 'REC ONE',
        ]);

        BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'SER2',
            'complaint_number' => '1002',
            'complaint_date' => '2026-09-05',
            'customer_name' => 'REC TWO',
        ]);

        BatteryComplaint::create([
            'battery_type' => 'FW',
            'battery_serial_number' => 'SER3',
            'complaint_number' => '2001',
            'complaint_date' => '2026-09-05',
            'customer_name' => 'REC THREE',
        ]);

        // Filter 2W only from 2026-09-04 to 2026-09-06
        $result = $this->exportService->generateExport('2026-09-04', '2026-09-06', '2W');

        $this->assertEquals(1, $result['count']);
        $this->assertEquals(1, $result['written_2w']);
        $this->assertEquals(0, $result['written_4w']);
    }

    public function test_null_values_are_blank_cells_and_long_serials_are_strings(): void
    {
        BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => '0123456789012345',
            'complaint_number' => '0098',
            'customer_mobile' => '0987654321',
            'complaint_date' => '2026-09-01',
            'customer_name' => 'NULL TESTER',
            'case_number' => null,
            'location' => null,
        ]);

        $result = $this->exportService->generateExport();
        $spreadsheet = IOFactory::load($result['file_path']);
        $sheet2W = $spreadsheet->getSheetByName('2W');

        // Verify serial and mobile numbers are exact string types and not scientific notation
        $this->assertEquals('0098', $sheet2W->getCell('A8')->getValue());
        $this->assertEquals('0123456789012345', $sheet2W->getCell('G8')->getValue());
        $this->assertEquals('0987654321', $sheet2W->getCell('O8')->getValue());

        // Verify null fields are blank cells
        $this->assertNull($sheet2W->getCell('C8')->getValue());
        $this->assertNull($sheet2W->getCell('Q8')->getValue());
    }

    public function test_export_controller_endpoint(): void
    {
        BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'SER100',
            'complaint_number' => '100',
            'complaint_date' => '2026-09-01',
            'customer_name' => 'ROUTE TEST',
        ]);

        $response = $this->actingAs($this->user)->get('/excel/export/download?vehicle_type=All');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
