<?php

namespace App\Services;

use App\Models\BatteryComplaint;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InwardExcelExportService
{
    /**
     * Generate fresh Excel export from Laravel database
     */
    public function generateExport(?string $fromDate = null, ?string $toDate = null, ?string $vehicleType = 'All'): array
    {
        $templatePath = config('excel.inward.template_path', storage_path('app/templates/INWARD REG. SHEET 2026-27RRR.xlsx'));

        if (File::exists($templatePath)) {
            $spreadsheet = IOFactory::load($templatePath);
        } else {
            $spreadsheet = $this->createFallbackSpreadsheet();
        }

        // 2. Fetch database records based on filters
        $query = BatteryComplaint::active();

        if ($fromDate) {
            $query->whereDate('complaint_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('complaint_date', '<=', $toDate);
        }

        $vehicleTypeUpper = strtoupper(trim($vehicleType ?? 'ALL'));

        if ($vehicleTypeUpper === '2W') {
            $query->where('battery_type', 'TW');
        } elseif ($vehicleTypeUpper === '4W') {
            $query->where('battery_type', '!=', 'TW');
        }

        $records = $query->orderBy('complaint_date', 'asc')->orderBy('id', 'asc')->get();

        if ($records->isEmpty()) {
            return [
                'success' => false,
                'count' => 0,
                'message' => 'No records found for the selected criteria.',
            ];
        }

        // Separate 2W and 4W records
        $twRecords = $records->filter(fn ($r) => strtoupper($r->battery_type ?? 'TW') === 'TW');
        $fwRecords = $records->filter(fn ($r) => strtoupper($r->battery_type ?? 'TW') !== 'TW');

        $writtenCounts = [
            '2W' => 0,
            '4W' => 0,
        ];

        // 3. Process 2W Sheet if applicable
        if (in_array($vehicleTypeUpper, ['ALL', '2W'])) {
            $writtenCounts['2W'] = $this->populateSheet($spreadsheet, '2W', 8, $twRecords);
        } else {
            $this->clearSheetDataArea($spreadsheet, '2W', 8);
        }

        // 4. Process 4W Sheet if applicable
        if (in_array($vehicleTypeUpper, ['ALL', '4W'])) {
            $writtenCounts['4W'] = $this->populateSheet($spreadsheet, '4W', 8, $fwRecords);
        } else {
            $this->clearSheetDataArea($spreadsheet, '4W', 8);
        }

        // 5. Generate output filename
        $dateSuffix = date('y-m-d');
        if ($fromDate && $toDate) {
            $fromFormatted = Carbon::parse($fromDate)->format('d-m-Y');
            $toFormatted = Carbon::parse($toDate)->format('d-m-Y');
            $dateSuffix = "{$fromFormatted} to {$toFormatted}";
        } elseif ($fromDate) {
            $dateSuffix = Carbon::parse($fromDate)->format('d-m-Y');
        }

        $filename = "{$dateSuffix} - INWARD REG. SHEET 2026-27.xlsx";
        $tempPath = storage_path('app/public/temp_exports/'.uniqid('export_').'.xlsx');

        $dir = dirname($tempPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        Log::info('Excel export generated successfully', [
            'total_records' => $records->count(),
            'written_2w' => $writtenCounts['2W'],
            'written_4w' => $writtenCounts['4W'],
            'filename' => $filename,
        ]);

        return [
            'success' => true,
            'count' => $records->count(),
            'written_2w' => $writtenCounts['2W'],
            'written_4w' => $writtenCounts['4W'],
            'file_path' => $tempPath,
            'filename' => $filename,
        ];
    }

    /**
     * Clear existing data area in a sheet (Columns A through U)
     */
    protected function clearSheetDataArea($spreadsheet, string $sheetName, int $startRow): void
    {
        $sheet = $spreadsheet->getSheetByName($sheetName);
        if (! $sheet) {
            return;
        }

        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= $startRow) {
            for ($r = $startRow; $r <= $highestRow; $r++) {
                for ($c = 1; $c <= 21; $c++) {
                    $colLetter = Coordinate::stringFromColumnIndex($c);
                    $sheet->setCellValue("{$colLetter}{$r}", null);
                }
            }
        }
    }

    /**
     * Populate records into worksheet starting at specified row
     */
    protected function populateSheet($spreadsheet, string $sheetName, int $startRow, $records): int
    {
        $sheet = $spreadsheet->getSheetByName($sheetName);
        if (! $sheet) {
            return 0;
        }

        // Clear previous sample/data rows completely first
        $this->clearSheetDataArea($spreadsheet, $sheetName, $startRow);

        $currentRow = $startRow;
        $count = 0;

        foreach ($records as $record) {
            $is2W = (strtoupper($record->battery_type ?? 'TW') === 'TW');
            $seg = $is2W ? 'TW' : 'FW';

            $decisionRaw = mb_strtoupper(trim((string) ($record->final_decision ?? '')), 'UTF-8');
            $pct = null;
            if ($record->prorata_percentage !== null && (float) $record->prorata_percentage > 0) {
                $pct = (float) $record->prorata_percentage;
            }

            if ($record->is_prorata || str_starts_with($decisionRaw, 'P') || str_contains($decisionRaw, 'PRORATA') || str_contains($decisionRaw, 'PRO-RATA')) {
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
            if (! empty($record->service_battery_number)) {
                $serviceBattGiven = mb_strtoupper((string) $record->service_battery_number, 'UTF-8');
            } elseif ($record->is_service_battery_given) {
                $serviceBattGiven = 'YES';
            }

            // Map 21 Columns (A to U / Index 0 to 20) matching template layout
            $values = [
                0  => mb_strtoupper((string) ($record->complaint_number ?? ''), 'UTF-8'),   // Col A: COMPLAIN NO
                1  => $this->formatDate($record->complaint_date),                           // Col B: COMPLAIN DATE (dd/mm/yyyy)
                2  => mb_strtoupper((string) ($record->case_number ?? ''), 'UTF-8'),        // Col C: CASE NO
                3  => $this->formatDate($record->purchase_date),                            // Col D: DATE OF SALE (dd/mm/yyyy)
                4  => mb_strtoupper((string) ($record->product_model ?? ''), 'UTF-8'),       // Col E: PRODUCT
                5  => ! empty($record->product_segment) ? mb_strtoupper((string) $record->product_segment, 'UTF-8') : $seg, // Col F: PROD.SEG.
                6  => mb_strtoupper((string) ($record->battery_serial_number ?? ''), 'UTF-8'),// Col G: OLD BATTERY SERIAL NO.
                7  => $record->is_digital_warranty ? 'YES' : '',                            // Col H: DIGITAL WARANTY
                8  => mb_strtoupper((string) ($record->online_reg_type ?? ''), 'UTF-8'),     // Col I: DIGITAL WARANTY 2 (BLANK if empty)
                9  => mb_strtoupper((string) ($record->vehicle_number ?? ''), 'UTF-8'),      // Col J: VEHICLE NO.
                10 => mb_strtoupper((string) ($record->vehicle_name ?? ''), 'UTF-8'),        // Col K: VEHICLE TYPE
                11 => $decision,                                                            // Col L: Decision
                12 => '',                                                                    // Col M: DE CASH MEMO (BLANK if empty)
                13 => $serviceBattGiven,                                                     // Col N: SERVICE BATT. GIVEN
                14 => mb_strtoupper((string) ($record->customer_mobile ?? ''), 'UTF-8'),     // Col O: MOBILE NO
                15 => mb_strtoupper((string) ($record->customer_name ?? ''), 'UTF-8'),       // Col P: CUTOMER/DEALER NAME
                16 => mb_strtoupper((string) ($record->location ?? ''), 'UTF-8'),            // Col Q: LOCATION
                17 => $this->formatDate($record->closing_date),                              // Col R: CLOSING DATE (dd/mm/yyyy)
                18 => mb_strtoupper((string) ($record->new_battery_number ?? ''), 'UTF-8'),     // Col S: NEW BATTERY SERIAL NO.
                19 => $this->formatDate($record->return_date),                               // Col T: RETURN DATE (dd/mm/yyyy)
                20 => mb_strtoupper((string) ($record->delivery_mode ?? ''), 'UTF-8'),       // Col U: TAKE PERSON NAME & MOBILE NO.
            ];

            foreach ($values as $colIdx => $val) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->setCellValue("{$colLetter}{$currentRow}", $val);

                // Apply explicit dd/mm/yyyy date format in Excel
                if (in_array($colLetter, ['B', 'D', 'R', 'T']) && ! empty($val)) {
                    $sheet->getStyle("{$colLetter}{$currentRow}")
                        ->getNumberFormat()
                        ->setFormatCode('dd/mm/yyyy');
                }
            }

            $this->applyRowStyle($sheet, $currentRow);
            $currentRow++;
            $count++;
        }

        return $count;
    }

    /**
     * Format Date values as dd/mm/yyyy (d/m/Y)
     */
    protected function formatDate($date): ?string
    {
        if (empty($date)) {
            return '';
        }

        try {
            if ($date instanceof Carbon || $date instanceof \DateTime) {
                return $date->format('d/m/Y');
            }
            $time = strtotime((string) $date);

            return $time ? date('d/m/Y', $time) : (string) $date;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Format Decision column value
     */
    protected function formatDecision($record): ?string
    {
        if (! empty($record->final_decision)) {
            return trim($record->final_decision);
        }

        if ($record->is_prorata) {
            return 'P';
        }

        return '';
    }

    /**
     * Format Service Battery column value
     */
    protected function formatServiceBattery($record): ?string
    {
        if (! empty($record->service_battery_number)) {
            return trim($record->service_battery_number);
        }

        return $record->is_service_battery_given ? 'YES' : '';
    }

    /**
     * Apply standard border and font styling to data row
     */
    protected function applyRowStyle($sheet, int $row): void
    {
        $range = "A{$row}:U{$row}";

        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D0D0D0'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    /**
     * Create fallback spreadsheet if no master template exists
     */
    protected function createFallbackSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $twSheet = $spreadsheet->getActiveSheet();
        $twSheet->setTitle('2W');
        $this->populateHeaderRow($twSheet);

        $fwSheet = $spreadsheet->createSheet();
        $fwSheet->setTitle('4W');
        $this->populateHeaderRow($fwSheet);

        return $spreadsheet;
    }

    /**
     * Populates Row 6 headers on fallback sheet
     */
    protected function populateHeaderRow(Worksheet $sheet): void
    {
        $headers = [
            0  => 'COMPLAIN NO',
            1  => 'COMPLAIN DATE',
            2  => 'CASE NO',
            3  => 'DATE OF SALE',
            4  => 'PRODUCT',
            5  => 'PROD.SEG.',
            6  => 'OLD BATTERY SERIAL NO.',
            7  => 'DIGITAL WARANTY',
            8  => 'DIGITAL WARANTY',
            9  => 'VEHICLE NO. (IF NOT ONLINE REG.)',
            10 => 'VEHICLE TYPE (IF NOT ONLINE REG.)',
            11 => 'Decision',
            12 => 'DE CASH MEMO',
            13 => 'SERVICE BATT. GIVEN',
            14 => 'MOBILE NO',
            15 => 'CUTOMER/DEALER NAME',
            16 => 'LOCATION',
            17 => 'CLOSING DATE',
            18 => 'NEW BATTERY SERIAL NO.',
            19 => 'RETURN DATE',
            20 => 'TAKE PERSON NAME & MOBILE NO.',
        ];

        foreach ($headers as $colIdx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue("{$colLetter}6", $header);
            $sheet->getStyle("{$colLetter}6")->getFont()->setBold(true);
        }
    }
}
