<?php

namespace App\Services;

use App\Models\BatteryComplaint;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelExportService
{
    /**
     * Standard 19 column headers for fallback template creation
     */
    public static function getStandardHeaders(): array
    {
        return [
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
    }

    /**
     * Exports complaints preserving exact original template formatting.
     */
    /**
     * Exports complaints preserving exact original template formatting.
     */
    public function export(): BinaryFileResponse
    {
        $templatePath = ExcelImportService::getMasterTemplatePath();

        if (File::exists($templatePath)) {
            $spreadsheet = IOFactory::load($templatePath);
        } else {
            $spreadsheet = $this->createFallbackSpreadsheet();
        }

        // 1. Export 2W Sheet
        $twSheet = $spreadsheet->getSheetByName('2W') ?? $spreadsheet->getSheetByName('TW') ?? $spreadsheet->getSheet(0);
        if ($twSheet) {
            $this->exportCategoryRecords($twSheet, '2W');
        }

        // 2. Export 4W Sheet
        $fwSheet = $spreadsheet->getSheetByName('4W') ?? $spreadsheet->getSheetByName('FW') ?? ($spreadsheet->getSheetCount() > 1 ? $spreadsheet->getSheet(1) : null);
        if ($fwSheet && $fwSheet->getTitle() !== ($twSheet ? $twSheet->getTitle() : '')) {
            $this->exportCategoryRecords($fwSheet, '4W');
        }

        // Write output file
        $tempPath = tempnam(sys_get_temp_dir(), 'excel_export_').'.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        $downloadName = 'INWARD_REG_SHEET_2026-27_UPDATED.xlsx';

        return response()->download($tempPath, $downloadName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Create fallback spreadsheet if no master template uploaded yet
     */
    protected function createFallbackSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;

        $twSheet = $spreadsheet->getActiveSheet();
        $twSheet->setTitle('2W');
        $this->populateHeaderRow($twSheet);

        $fwSheet = $spreadsheet->createSheet();
        $fwSheet->setTitle('4W');
        $this->populateHeaderRow($fwSheet);

        return $spreadsheet;
    }

    /**
     * Populates Row 1 headers on a sheet
     */
    protected function populateHeaderRow(Worksheet $sheet): void
    {
        $headers = static::getStandardHeaders();
        foreach ($headers as $colIdx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue("{$colLetter}1", $header);
            $sheet->getStyle("{$colLetter}1")->getFont()->setBold(true);
        }
    }

    /**
     * Writes records into target category sheet maintaining row identity
     */
    protected function exportCategoryRecords(Worksheet $sheet, string $category): void
    {
        $is2W = ($category === '2W' || $category === 'TW');

        $query = BatteryComplaint::active();
        if ($is2W) {
            $query->where(function ($q) {
                $q->where('battery_type', 'TW')
                  ->orWhere('excel_sheet', '2W');
            });
        } else {
            $query->where(function ($q) {
                $q->where('battery_type', '!=', 'TW')
                  ->orWhere('excel_sheet', '4W');
            });
        }

        $records = $query->orderBy('complaint_date', 'asc')->orderBy('id', 'asc')->get();
        $occupiedRows = [];

        $startRow = 8; // Data rows start at row 8 in master template

        foreach ($records as $row) {
            $rowValues = $this->buildRecordRowValues($row, $is2W ? 'TW' : 'FW');

            if (! empty($row->excel_row_number) && $row->excel_row_number >= $startRow && ! isset($occupiedRows[$row->excel_row_number]) && ($row->excel_sheet === $category || empty($row->excel_sheet))) {
                $targetRow = $row->excel_row_number;
                $occupiedRows[$targetRow] = true;
            } else {
                $targetRow = $this->getNextAvailableRow($sheet, $occupiedRows, $startRow);
                $occupiedRows[$targetRow] = true;

                $row->excel_sheet = $category;
                $row->excel_row_number = $targetRow;
                $row->saveQuietly();
            }

            $this->writeRowCells($sheet, $targetRow, $rowValues);
        }
    }

    /**
     * Finds next available row index on a sheet starting from $startRow
     */
    protected function getNextAvailableRow(Worksheet $sheet, array $occupiedRows, int $startRow = 8): int
    {
        $highest = max($startRow - 1, $sheet->getHighestRow());
        $next = max($startRow, $highest + 1);

        while (isset($occupiedRows[$next])) {
            $next++;
        }

        return $next;
    }

    /**
     * Formats 21 cells for a complaint row aligned with original template columns (A to U)
     */
    protected function buildRecordRowValues($row, string $seg): array
    {
        $decisionRaw = mb_strtoupper(trim((string) ($row->final_decision ?? '')), 'UTF-8');

        $pct = null;
        if ($row->prorata_percentage !== null && (float) $row->prorata_percentage > 0) {
            $pct = (float) $row->prorata_percentage;
        }

        if ($row->is_prorata || str_starts_with($decisionRaw, 'P') || str_contains($decisionRaw, 'PRORATA') || str_contains($decisionRaw, 'PRO-RATA')) {
            if (! $pct && preg_match('/(\d+(?:\.\d+)?)\s*%?/', $decisionRaw, $m)) {
                $pct = (float) $m[1];
            }
            $decision = $pct ? "P ({$pct}%)" : 'P';
        } elseif (in_array($decisionRaw, ['FREE REPLACEMENT', 'FREE REP.', 'FREE REP', 'FREE', 'F'])) {
            $decision = 'F';
        } elseif (in_array($decisionRaw, ['C', 'CHARGING', 'CHARGE', 'RECHARGED', 'RECHARGED OK'])) {
            $decision = 'C';
        } elseif (in_array($decisionRaw, ['R', 'DEAD RETURN', 'DEAD', 'REJECTED', 'REJECT', 'RETURN'])) {
            $decision = 'R';
        } elseif (! empty($decisionRaw)) {
            $decision = $decisionRaw;
        } else {
            $decision = '';
        }

        $serviceBattGiven = '';
        if (! empty($row->service_battery_number)) {
            $serviceBattGiven = mb_strtoupper((string) $row->service_battery_number, 'UTF-8');
        } elseif ($row->is_service_battery_given) {
            $serviceBattGiven = 'YES';
        }

        // Format dates strictly as Day/Month/Year (d/m/Y)
        $formatDate = function ($dateObj) {
            if (empty($dateObj)) {
                return '';
            }
            if ($dateObj instanceof \Carbon\Carbon || $dateObj instanceof \DateTime) {
                return $dateObj->format('d/m/Y');
            }
            $time = strtotime((string) $dateObj);

            return $time ? date('d/m/Y', $time) : (string) $dateObj;
        };

        return [
            0  => mb_strtoupper((string) ($row->complaint_number ?? ''), 'UTF-8'),   // Col A: COMPLAIN NO
            1  => $formatDate($row->complaint_date),                                 // Col B: COMPLAIN DATE
            2  => mb_strtoupper((string) ($row->case_number ?? ''), 'UTF-8'),        // Col C: CASE NO
            3  => $formatDate($row->purchase_date),                                  // Col D: DATE OF SALE
            4  => mb_strtoupper((string) ($row->product_model ?? ''), 'UTF-8'),       // Col E: PRODUCT
            5  => ! empty($row->product_segment) ? mb_strtoupper((string) $row->product_segment, 'UTF-8') : $seg, // Col F: PROD.SEG.
            6  => mb_strtoupper((string) ($row->battery_serial_number ?? ''), 'UTF-8'),// Col G: OLD BATTERY SERIAL NO.
            7  => $row->is_digital_warranty ? 'YES' : '',                            // Col H: DIGITAL WARANTY
            8  => mb_strtoupper((string) ($row->online_reg_type ?? ''), 'UTF-8'),     // Col I: DIGITAL WARANTY 2
            9  => mb_strtoupper((string) ($row->vehicle_number ?? ''), 'UTF-8'),      // Col J: VEHICLE NO.
            10 => mb_strtoupper((string) ($row->vehicle_name ?? ''), 'UTF-8'),        // Col K: VEHICLE TYPE
            11 => $decision,                                                         // Col L: Decision
            12 => '',                                                                 // Col M: DE CASH MEMO
            13 => $serviceBattGiven,                                                  // Col N: SERVICE BATT. GIVEN
            14 => mb_strtoupper((string) ($row->customer_mobile ?? ''), 'UTF-8'),     // Col O: MOBILE NO
            15 => mb_strtoupper((string) ($row->customer_name ?? ''), 'UTF-8'),       // Col P: CUTOMER/DEALER NAME
            16 => mb_strtoupper((string) ($row->location ?? ''), 'UTF-8'),            // Col Q: LOCATION
            17 => $formatDate($row->closing_date),                                    // Col R: CLOSING DATE
            18 => mb_strtoupper((string) ($row->new_battery_number ?? ''), 'UTF-8'),     // Col S: NEW BATTERY SERIAL NO.
            19 => $formatDate($row->return_date),                                     // Col T: RETURN DATE
            20 => mb_strtoupper((string) ($row->delivery_mode ?? ''), 'UTF-8'),       // Col U: TAKE PERSON NAME & MOBILE NO.
        ];
    }

    /**
     * Writes cell values into target row and formats Date columns explicitly as dd/mm/yyyy
     */
    protected function writeRowCells(Worksheet $sheet, int $rowIdx, array $values): void
    {
        $dateCols = ['B', 'D', 'R', 'T'];

        foreach ($values as $colIdx => $val) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue("{$colLetter}{$rowIdx}", $val);

            // Format date columns as dd/mm/yyyy in Excel
            if (in_array($colLetter, $dateCols) && ! empty($val)) {
                $sheet->getStyle("{$colLetter}{$rowIdx}")
                    ->getNumberFormat()
                    ->setFormatCode('dd/mm/yyyy');
            }
        }
    }
}
