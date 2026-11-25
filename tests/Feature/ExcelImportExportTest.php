<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_operator_cannot_access_import(): void
    {
        $operator = User::factory()->create(['role' => 'operator1']);

        $response = $this->actingAs($operator)->get('/excel/import');
        $response->assertStatus(403);
    }

    public function test_can_render_excel_import_and_export_pages(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/excel/import');
        $response->assertStatus(200);
        $response->assertSee('Import Official Excel Workbook');

        $response = $this->actingAs($user)->get('/excel/export');
        $response->assertStatus(200);
        $response->assertSee('EXPORT INWARD REGISTER');
    }

    public function test_can_import_excel_workbook_sheet(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $spreadsheet = new Spreadsheet;

        $twSheet = $spreadsheet->getActiveSheet();
        $twSheet->setTitle('2W');
        $twSheet->setCellValue('A6', 'ZW COMPLAIN NO');
        $twSheet->setCellValue('B6', 'ZW COMPLAIN DATE');
        $twSheet->setCellValue('G6', 'OLD BATTERY SERIAL NO.');
        $twSheet->setCellValue('N6', 'CUTOMER/DEALER NAME');

        $twSheet->setCellValue('A8', '1001');
        $twSheet->setCellValue('B8', '14-08-2026');
        $twSheet->setCellValue('G8', 'EA002062103054');
        $twSheet->setCellValue('N8', 'MOMAI AUTO');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile(
            $tempPath,
            'INWARD REG. SHEET 2026-27RRR.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($user)->post('/excel/import', [
            'excel_file' => $uploadedFile,
        ]);

        $response->assertRedirect(route('excel.import_view'));
        $response->assertSessionHas('import_success', true);

        $this->assertDatabaseHas('battery_complaints', [
            'complaint_number' => '1001',
            'battery_serial_number' => 'EA002062103054',
            'customer_name' => 'MOMAI AUTO',
        ]);
    }

    public function test_can_export_excel_workbook(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        BatteryComplaint::create([
            'complaint_number' => '1002',
            'complaint_date' => '2026-08-20',
            'battery_serial_number' => 'EA002062103055',
            'customer_name' => 'EXPORT TESTER TW',
            'excel_sheet' => '2W',
            'excel_row_number' => 8,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/excel/export/download');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_can_import_excel_with_title_row_offset_and_fuzzy_sheet_names(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $spreadsheet = new Spreadsheet;
        $twSheet = $spreadsheet->getActiveSheet();
        $twSheet->setTitle('Sheet 2W');
        $twSheet->setCellValue('A7', 'ZW COMPLAIN NO');
        $twSheet->setCellValue('B7', 'ZW COMPLAIN DATE');
        $twSheet->setCellValue('G7', 'OLD BATTERY SERIAL NO.');
        $twSheet->setCellValue('N7', 'CUTOMER/DEALER NAME');

        $twSheet->setCellValue('A8', '1005');
        $twSheet->setCellValue('B8', '22-08-2026');
        $twSheet->setCellValue('G8', 'EA002062109999');
        $twSheet->setCellValue('N8', 'OFFSET CUSTOMER');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_offset_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile(
            $tempPath,
            'INWARD REG. SHEET 2026-27RRR.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($user)->post('/excel/import', [
            'excel_file' => $uploadedFile,
        ]);

        $response->assertRedirect(route('excel.import_view'));

        $this->assertDatabaseHas('battery_complaints', [
            'complaint_number' => '1005',
            'battery_serial_number' => 'EA002062109999',
            'customer_name' => 'OFFSET CUSTOMER',
        ]);
    }

    public function test_can_import_excel_row_with_missing_serial_number(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $spreadsheet = new Spreadsheet;
        $twSheet = $spreadsheet->getActiveSheet();
        $twSheet->setTitle('2W');
        $twSheet->setCellValue('A7', 'ZW COMPLAIN NO');
        $twSheet->setCellValue('B7', 'ZW COMPLAIN DATE');
        $twSheet->setCellValue('C7', 'CASE NO');
        $twSheet->setCellValue('E7', 'PRODUCT');
        $twSheet->setCellValue('G7', 'OLD BATTERY SERIAL NO.');
        $twSheet->setCellValue('N7', 'CUTOMER/DEALER NAME');

        $twSheet->setCellValue('A8', '3700');
        $twSheet->setCellValue('B8', '17-08-2026');
        $twSheet->setCellValue('C8', 'NO ONLINE');
        $twSheet->setCellValue('E8', 'BTZ4L');
        $twSheet->setCellValue('G8', ''); // Blank serial number
        $twSheet->setCellValue('N8', 'USMANBHAI RAZA');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_noserial_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile(
            $tempPath,
            'INWARD REG. SHEET 2026-27RRR.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($user)->post('/excel/import', [
            'excel_file' => $uploadedFile,
        ]);

        $response->assertRedirect(route('excel.import_view'));

        $this->assertDatabaseMissing('battery_complaints', [
            'complaint_number' => '3700',
        ]);
    }

    public function test_preview_import_and_confirm_transaction_import(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $spreadsheet = new Spreadsheet;
        $twSheet = $spreadsheet->getActiveSheet();
        $twSheet->setTitle('2W');
        $twSheet->setCellValue('A8', '8888');
        $twSheet->setCellValue('B8', '09-09-2026');
        $twSheet->setCellValue('G8', 'EAB0206Z108888');
        $twSheet->setCellValue('P8', 'PREVIEW CUSTOMER');

        $fwSheet = $spreadsheet->createSheet();
        $fwSheet->setTitle('4W');
        $fwSheet->setCellValue('A8', '9999');
        $fwSheet->setCellValue('B8', '09-09-2026');
        $fwSheet->setCellValue('G8', 'CBA0939X329999');
        $fwSheet->setCellValue('P8', 'CAR PREVIEW CUSTOMER');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_preview_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile(
            $tempPath,
            'INWARD REG. SHEET 2026-27RRR.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // 1. Submit preview endpoint
        $response = $this->actingAs($user)->post('/excel/import/preview', [
            'excel_file' => $uploadedFile,
        ]);

        $response->assertStatus(200);
        $response->assertSee('IMPORT PREVIEW');
        $response->assertSee('2W Sheet Records');
        $response->assertSee('4W Sheet Records');

        // Database should NOT be modified during preview
        $this->assertDatabaseMissing('battery_complaints', ['complaint_number' => '8888']);

        // 2. Submit confirm endpoint with temp token
        $token = $response->viewData('tempFileToken');
        $confirmResponse = $this->actingAs($user)->post('/excel/import/confirm', [
            'temp_file_token' => $token,
        ]);

        $confirmResponse->assertRedirect(route('excel.import_view'));

        // Database MUST contain imported records with correct battery_type
        $this->assertDatabaseHas('battery_complaints', [
            'complaint_number' => '8888',
            'battery_type' => 'TW',
            'customer_name' => 'PREVIEW CUSTOMER',
        ]);

        $this->assertDatabaseHas('battery_complaints', [
            'complaint_number' => '9999',
            'battery_type' => 'FW',
            'customer_name' => 'CAR PREVIEW CUSTOMER',
        ]);
    }
}
