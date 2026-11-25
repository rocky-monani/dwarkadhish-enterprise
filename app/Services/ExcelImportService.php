<?php

namespace App\Services;

use App\Models\BatteryComplaint;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelImportService
{
    /**
     * Master template storage path
     */
    public static function getMasterTemplatePath(): string
    {
        return storage_path('app/templates/INWARD REG. SHEET 2026-27RRR.xlsx');
    }

    /**
     * Performs dry-run analysis and generates import preview summary
     */
    public function previewImport(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $preview = [
            'total_rows' => 0,
            'ready_count' => 0,
            'error_count' => 0,
            'sheets' => [
                '2W' => ['total' => 0, 'new' => 0, 'existing' => 0, 'unchanged' => 0, 'duplicates' => 0, 'errors' => 0, 'rows' => []],
                '4W' => ['total' => 0, 'new' => 0, 'existing' => 0, 'unchanged' => 0, 'duplicates' => 0, 'errors' => 0, 'rows' => []],
            ],
            'errors' => [],
        ];

        $seenIdentifiers = [];
        $lookupIndex = $this->loadExistingLookupIndex();

        // 1. Process 2W Sheet
        $twSheet = $spreadsheet->getSheetByName('2W') ?? $spreadsheet->getSheet(0);
        if ($twSheet) {
            $this->analyzeSheet($twSheet, '2W', 8, $preview, $seenIdentifiers, $lookupIndex);
        }

        // 2. Process 4W Sheet
        $fwSheet = $spreadsheet->getSheetByName('4W') ?? ($spreadsheet->getSheetCount() > 1 ? $spreadsheet->getSheet(1) : null);
        if ($fwSheet && $fwSheet->getTitle() !== ($twSheet ? $twSheet->getTitle() : '')) {
            $this->analyzeSheet($fwSheet, '4W', 8, $preview, $seenIdentifiers, $lookupIndex);
        }

        $preview['total_rows'] = $preview['sheets']['2W']['total'] + $preview['sheets']['4W']['total'];
        $preview['ready_count'] = ($preview['sheets']['2W']['new'] + $preview['sheets']['2W']['existing'] + $preview['sheets']['2W']['unchanged'])
                                + ($preview['sheets']['4W']['new'] + $preview['sheets']['4W']['existing'] + $preview['sheets']['4W']['unchanged']);
        $preview['error_count'] = count($preview['errors']);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $preview;
    }

    /**
     * Executes the actual import inside a safe DB transaction
     */
    public function executeImport(string $filePath, ?int $userId = null): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $summary = [
            'total_processed' => 0,
            'inserted' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'skipped' => 0,
            'errors' => 0,
            'sheets' => [
                '2W' => ['inserted' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped' => 0],
                '4W' => ['inserted' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped' => 0],
            ],
        ];

        // Save uploaded file as active master template for exports
        $masterPath = static::getMasterTemplatePath();
        $masterDir = dirname($masterPath);
        if (! File::exists($masterDir)) {
            File::makeDirectory($masterDir, 0755, true);
        }
        File::copy($filePath, $masterPath);

        DB::transaction(function () use ($spreadsheet, $userId, &$summary) {
            $seenIdentifiers = [];
            $lookupIndex = $this->loadExistingLookupIndex();

            // Import 2W Sheet
            $twSheet = $spreadsheet->getSheetByName('2W') ?? $spreadsheet->getSheet(0);
            if ($twSheet) {
                $this->importSheetRecords($twSheet, '2W', 8, $userId, $seenIdentifiers, $summary, $lookupIndex);
            }

            // Import 4W Sheet
            $fwSheet = $spreadsheet->getSheetByName('4W') ?? ($spreadsheet->getSheetCount() > 1 ? $spreadsheet->getSheet(1) : null);
            if ($fwSheet && $fwSheet->getTitle() !== ($twSheet ? $twSheet->getTitle() : '')) {
                $this->importSheetRecords($fwSheet, '4W', 8, $userId, $seenIdentifiers, $summary, $lookupIndex);
            }
        });

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $summary['total_processed'] = $summary['inserted'] + $summary['updated'] + $summary['unchanged'];

        Log::info('Excel import executed successfully', $summary);

        return $summary;
    }

    /**
     * Preload existing active records into O(1) lookup maps in a single query.
     */
    protected function loadExistingLookupIndex(): array
    {
        $rows = BatteryComplaint::active()
            ->toBase()
            ->select([
                'id', 'battery_type', 'complaint_number', 'complaint_date', 'case_number',
                'purchase_date', 'product_model', 'battery_serial_number', 'vehicle_number',
                'vehicle_name', 'final_decision', 'customer_mobile', 'customer_name',
                'location', 'closing_date', 'new_battery_number', 'return_date', 'delivery_mode',
            ])
            ->get();

        $byComplaint = [];
        $bySerialDate = [];

        foreach ($rows as $row) {
            $type = strtoupper($row->battery_type ?? 'TW') === 'TW' ? 'TW' : 'FW';
            if (! empty($row->complaint_number)) {
                $cKey = $type . '|' . trim((string) $row->complaint_number);
                $byComplaint[$cKey] = $row;
            }
            if (! empty($row->battery_serial_number) && ! empty($row->complaint_date)) {
                $dStr = substr((string) $row->complaint_date, 0, 10);
                $sKey = $type . '|' . trim((string) $row->battery_serial_number) . '|' . $dStr;
                $bySerialDate[$sKey] = $row;
            }
        }

        return [
            'byComplaint' => $byComplaint,
            'bySerialDate' => $bySerialDate,
        ];
    }

    /**
     * Analyze sheet rows for preview
     */
    protected function analyzeSheet(Worksheet $worksheet, string $category, int $startRow, array &$preview, array &$seenIdentifiers, array $lookupIndex = []): void
    {
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();

        if ($highestRow < $startRow) {
            return;
        }

        // Read entire sheet range in one call instead of per-row rangeToArray
        $allRows = $worksheet->rangeToArray("A{$startRow}:{$highestColumn}{$highestRow}", null, true, false, false);

        foreach ($allRows as $offset => $rowCells) {
            $r = $startRow + $offset;
            $nonEmpty = array_filter($rowCells, fn ($val) => $val !== null && trim((string) $val) !== '');

            if (empty($nonEmpty)) {
                continue;
            }

            $rowData = $this->extractRowData($rowCells, $worksheet, $r, $category);
            $complaintNo = $rowData['complaint_number'];
            $serialNo = $rowData['battery_serial_number'];

            if (empty($serialNo)) {
                continue;
            }

            $preview['sheets'][$category]['total']++;

            $dedupKey = $complaintNo ? "C:{$complaintNo}_T:{$category}" : "S:{$serialNo}_D:{$rowData['complaint_date']}_T:{$category}";
            if (isset($seenIdentifiers[$dedupKey])) {
                $preview['sheets'][$category]['existing']++;
                continue;
            }
            $seenIdentifiers[$dedupKey] = $r;

            $existing = $this->findExistingRecord($complaintNo, $serialNo, $rowData['complaint_date'], $category, $lookupIndex);

            if (! $existing) {
                $preview['sheets'][$category]['new']++;
            } else {
                if ($this->isRecordIdentical($existing, $rowData)) {
                    $preview['sheets'][$category]['unchanged']++;
                } else {
                    $preview['sheets'][$category]['existing']++;
                }
            }
        }
    }

    /**
     * Import records from sheet into database (Batch inserts + O(1) lookup)
     */
    protected function importSheetRecords(Worksheet $worksheet, string $category, int $startRow, ?int $userId, array &$seenIdentifiers, array &$summary, array &$lookupIndex = []): void
    {
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();

        if ($highestRow < $startRow) {
            return;
        }

        $allRows = $worksheet->rangeToArray("A{$startRow}:{$highestColumn}{$highestRow}", null, true, false, false);
        $now = now()->toDateTimeString();
        $toInsert = [];
        $pendingInsertKeys = []; // dedupKey => index in $toInsert

        foreach ($allRows as $offset => $rowCells) {
            $r = $startRow + $offset;
            $nonEmpty = array_filter($rowCells, fn ($val) => $val !== null && trim((string) $val) !== '');

            if (empty($nonEmpty)) {
                continue;
            }

            $rowData = $this->extractRowData($rowCells, $worksheet, $r, $category);
            $complaintNo = $rowData['complaint_number'];
            $serialNo = $rowData['battery_serial_number'];

            if (empty($serialNo)) {
                $summary['sheets'][$category]['skipped']++;
                $summary['skipped']++;
                continue;
            }

            $dedupKey = $complaintNo ? "C:{$complaintNo}_T:{$category}" : "S:{$serialNo}_D:{$rowData['complaint_date']}_T:{$category}";
            $existing = $this->findExistingRecord($complaintNo, $serialNo, $rowData['complaint_date'], $category, $lookupIndex);

            $payload = array_merge($rowData, [
                'battery_type' => $category === '2W' ? 'TW' : 'FW',
                'excel_sheet' => $category,
                'excel_row_number' => $r,
                'complaint_status' => (! empty($rowData['closing_date']) || ! empty($rowData['final_decision'])) ? 'Closed' : 'Open',
                'updated_at' => $now,
            ]);

            if (! $existing && ! isset($seenIdentifiers[$dedupKey])) {
                $payload['created_by'] = $userId;
                $payload['created_at'] = $now;
                $idx = count($toInsert);
                $toInsert[] = $payload;
                $pendingInsertKeys[$dedupKey] = $idx;
                $seenIdentifiers[$dedupKey] = true;
                $summary['sheets'][$category]['inserted']++;
                $summary['inserted']++;
            } else {
                if (isset($pendingInsertKeys[$dedupKey])) {
                    // Duplicate within same Excel batch: overwrite buffered insert row
                    $idx = $pendingInsertKeys[$dedupKey];
                    $payload['created_by'] = $toInsert[$idx]['created_by'];
                    $payload['created_at'] = $toInsert[$idx]['created_at'];
                    $toInsert[$idx] = $payload;
                } elseif ($existing && isset($existing->id)) {
                    if (! $this->isRecordIdentical($existing, $rowData)) {
                        DB::table('battery_complaints')->where('id', $existing->id)->update($payload);
                    }
                }
                $summary['sheets'][$category]['updated']++;
                $summary['updated']++;
            }
        }

        if (! empty($toInsert)) {
            foreach (array_chunk($toInsert, 500) as $chunk) {
                DB::table('battery_complaints')->insert($chunk);
            }
        }
    }

    /**
     * Find existing database record using preloaded O(1) lookup maps
     */
    protected function findExistingRecord(?string $complaintNo, ?string $serialNo, ?string $complaintDate, string $category = '2W', array $lookupIndex = []): ?object
    {
        $type = $category === '2W' ? 'TW' : 'FW';

        if (! empty($lookupIndex)) {
            if (! empty($complaintNo)) {
                $cKey = $type . '|' . trim($complaintNo);
                if (isset($lookupIndex['byComplaint'][$cKey])) {
                    return $lookupIndex['byComplaint'][$cKey];
                }
            }

            if (! empty($serialNo) && ! empty($complaintDate)) {
                $sKey = $type . '|' . trim($serialNo) . '|' . substr($complaintDate, 0, 10);
                if (isset($lookupIndex['bySerialDate'][$sKey])) {
                    return $lookupIndex['bySerialDate'][$sKey];
                }
            }

            return null;
        }

        if (! empty($complaintNo)) {
            $rec = BatteryComplaint::where('complaint_number', trim($complaintNo))
                ->where('battery_type', $type)
                ->first();
            if ($rec) {
                return $rec;
            }
        }

        if (! empty($serialNo) && ! empty($complaintDate)) {
            return BatteryComplaint::where('battery_serial_number', trim($serialNo))
                ->whereDate('complaint_date', $complaintDate)
                ->where('battery_type', $type)
                ->first();
        }

        return null;
    }

    /**
     * Check if database record is identical to incoming row payload
     */
    protected function isRecordIdentical(object $record, array $payload): bool
    {
        $keys = [
            'complaint_number', 'complaint_date', 'case_number', 'purchase_date',
            'product_model', 'battery_serial_number', 'vehicle_number', 'vehicle_name',
            'final_decision', 'customer_mobile', 'customer_name', 'location',
            'closing_date', 'new_battery_number', 'return_date', 'delivery_mode',
        ];
        $dateKeys = ['complaint_date', 'purchase_date', 'closing_date', 'return_date'];

        foreach ($keys as $key) {
            $dbVal = $record->{$key} ?? null;
            $payVal = $payload[$key] ?? null;

            if ($dbVal instanceof Carbon) {
                $dbVal = $dbVal->format('Y-m-d');
            } elseif ($dbVal !== null && in_array($key, $dateKeys, true)) {
                $dbVal = substr((string) $dbVal, 0, 10);
            }

            if (trim((string) $dbVal) !== trim((string) $payVal)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build dynamic column mapping by scanning worksheet headers (Rows 1 to 7)
     */
    protected function buildColumnMap(Worksheet $worksheet): array
    {
        $highestCol = $worksheet->getHighestColumn();
        $highestColIdx = Coordinate::columnIndexFromString($highestCol);

        $map = [];

        for ($r = 1; $r <= 7; $r++) {
            for ($c = 1; $c <= $highestColIdx; $c++) {
                $colLetter = Coordinate::stringFromColumnIndex($c);
                $val = mb_strtoupper(trim((string) $worksheet->getCell("{$colLetter}{$r}")->getValue()), 'UTF-8');

                if (empty($val)) {
                    continue;
                }

                $cIdx = $c - 1; // 0-based index

                if (! isset($map['complaint_no']) && (str_contains($val, 'COMPLAIN NO') || str_contains($val, 'COMPLAINT NO'))) {
                    $map['complaint_no'] = $cIdx;
                } elseif (! isset($map['complaint_date']) && str_contains($val, 'COMPLAIN DATE')) {
                    $map['complaint_date'] = $cIdx;
                } elseif (! isset($map['case_no']) && str_contains($val, 'CASE NO')) {
                    $map['case_no'] = $cIdx;
                } elseif (! isset($map['date_of_sale']) && (str_contains($val, 'DATE OF SALE') || str_contains($val, 'PURCHASE DATE'))) {
                    $map['date_of_sale'] = $cIdx;
                } elseif (! isset($map['prod_seg']) && (str_contains($val, 'PROD.SEG') || str_contains($val, 'PROD SEG') || str_contains($val, 'PROD. SEG') || str_contains($val, 'SEGMENT'))) {
                    $map['prod_seg'] = $cIdx;
                } elseif (! isset($map['product']) && str_contains($val, 'PRODUCT') && ! str_contains($val, 'PROD.SEG')) {
                    $map['product'] = $cIdx;
                } elseif (! isset($map['serial_no']) && (str_contains($val, 'BATTERY SERIAL') || str_contains($val, 'OLD BATTERY'))) {
                    $map['serial_no'] = $cIdx;
                } elseif (! isset($map['digital_warranty']) && (str_contains($val, 'DIGITAL WARANTY') || str_contains($val, 'DIGITAL WARRANTY'))) {
                    $map['digital_warranty'] = $cIdx;
                } elseif (! isset($map['vehicle_no']) && str_contains($val, 'VEHICLE NO')) {
                    $map['vehicle_no'] = $cIdx;
                } elseif (! isset($map['vehicle_type']) && str_contains($val, 'VEHICLE TYPE')) {
                    $map['vehicle_type'] = $cIdx;
                } elseif (! isset($map['decision']) && (str_contains($val, 'DECISION') || str_contains($val, 'ડીસીસન'))) {
                    $map['decision'] = $cIdx;
                } elseif (! isset($map['service_batt']) && str_contains($val, 'SERVICE BATT')) {
                    $map['service_batt'] = $cIdx;
                } elseif (! isset($map['mobile_no']) && (str_contains($val, 'MOBILE NO') || str_contains($val, 'મોબાઈલ'))) {
                    $map['mobile_no'] = $cIdx;
                } elseif (! isset($map['customer_name']) && (str_contains($val, 'CUTOMER') || str_contains($val, 'CUSTOMER') || str_contains($val, 'PARTY') || str_contains($val, 'DEALER') || str_contains($val, 'CLIENT') || str_contains($val, 'ગ્રાહક') || str_contains($val, 'NAME'))) {
                    if (! str_contains($val, 'VEHICLE') && ! str_contains($val, 'TAKE PERSON') && ! str_contains($val, 'PRODUCT')) {
                        $map['customer_name'] = $cIdx;
                    }
                } elseif (! isset($map['location']) && (str_contains($val, 'LOCATION') || str_contains($val, 'ગામ'))) {
                    $map['location'] = $cIdx;
                } elseif (! isset($map['closing_date']) && str_contains($val, 'CLOSING DATE')) {
                    $map['closing_date'] = $cIdx;
                } elseif (! isset($map['new_serial_no']) && (str_contains($val, 'NEW BATTERY SERIAL') || str_contains($val, 'નવી  બેટરી'))) {
                    $map['new_serial_no'] = $cIdx;
                } elseif (! isset($map['return_date']) && str_contains($val, 'RETURN DATE')) {
                    $map['return_date'] = $cIdx;
                } elseif (! isset($map['delivery_mode']) && (str_contains($val, 'TAKE PERSON') || str_contains($val, 'DELIVERY'))) {
                    $map['delivery_mode'] = $cIdx;
                }
            }
        }

        // Standard 19-Column default index fallback (0-indexed A..U)
        $defaults = [
            'complaint_no' => 0,     // Col A
            'complaint_date' => 1,   // Col B
            'case_no' => 2,          // Col C
            'date_of_sale' => 3,     // Col D
            'product' => 4,          // Col E
            'prod_seg' => 5,         // Col F (PROD.SEG.)
            'serial_no' => 6,        // Col G
            'digital_warranty' => 7, // Col H
            'vehicle_no' => 9,       // Col J
            'vehicle_type' => 10,    // Col K
            'decision' => 11,        // Col L
            'service_batt' => 13,    // Col N
            'mobile_no' => 14,       // Col O
            'customer_name' => 15,   // Col P (CUTOMER/DEALER NAME)
            'location' => 16,        // Col Q
            'closing_date' => 17,    // Col R
            'new_serial_no' => 18,   // Col S
            'return_date' => 19,     // Col T
            'delivery_mode' => 20,   // Col U
        ];

        foreach ($defaults as $k => $defIdx) {
            if (! isset($map[$k])) {
                $map[$k] = $defIdx;
            }
        }

        return $map;
    }

    /**
     * Extract row data mapping Columns A through U
     */
    protected function extractRowData(array $cells, Worksheet $worksheet, int $rowIdx, string $category): array
    {
        $map = $this->buildColumnMap($worksheet);

        $getVal = function ($key) use ($cells, $map) {
            $colIdx = $map[$key] ?? null;
            if ($colIdx === null) {
                return null;
            }
            $val = $cells[$colIdx] ?? null;

            return $val !== null ? trim((string) $val) : null;
        };

        $getDateVal = function ($key) use ($worksheet, $rowIdx, $map) {
            $colIdx = $map[$key] ?? null;
            if ($colIdx === null) {
                return null;
            }
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $cell = $worksheet->getCell("{$colLetter}{$rowIdx}");

            return $this->parseExcelDate($cell->getValue());
        };

        $complaintNo = $getVal('complaint_no');
        $complaintDate = $getDateVal('complaint_date');
        $caseNo = $getVal('case_no');
        $purchaseDate = $getDateVal('date_of_sale');
        $productModel = $getVal('product');
        $prodSegVal = $getVal('prod_seg');
        $serialNo = $getVal('serial_no');

        if (empty($prodSegVal)) {
            $prodSegVal = ($category === '2W' ? 'TW' : 'FW');
        } else {
            $prodSegVal = mb_strtoupper(trim($prodSegVal), 'UTF-8');
        }
        $digitalVal = $getVal('digital_warranty');
        $vehicleNo = $getVal('vehicle_no');
        $vehicleName = $getVal('vehicle_type');
        $decisionRaw = $getVal('decision');
        $serviceBattVal = $getVal('service_batt');
        $mobileNo = $getVal('mobile_no');
        $customerName = $getVal('customer_name');
        $location = $getVal('location');
        $closingDate = $getDateVal('closing_date');
        $newSerialNo = $getVal('new_serial_no');
        $returnDate = $getDateVal('return_date');
        $deliveryMode = $getVal('delivery_mode');

        // Preserve exact cell state: if cell is empty/blank in Excel, keep it null/blank
        if (! empty($customerName)) {
            $customerName = mb_strtoupper(trim($customerName), 'UTF-8');
        } else {
            $customerName = null;
        }

        $isDigital = $digitalVal && in_array(mb_strtoupper($digitalVal, 'UTF-8'), ['YES', 'Y', '1', 'TRUE']);

        $isServiceGiven = ! empty($serviceBattVal) && mb_strtoupper($serviceBattVal, 'UTF-8') !== 'NO';
        $serviceBattNo = ($isServiceGiven && mb_strtoupper($serviceBattVal, 'UTF-8') !== 'YES') ? $serviceBattVal : null;

        $isProrata = false;
        $prorataPct = null;
        if ($decisionRaw) {
            $upperDec = mb_strtoupper($decisionRaw, 'UTF-8');
            if (str_starts_with($upperDec, 'P') || str_contains($upperDec, 'PRORATA') || str_contains($upperDec, 'PRO-RATA')) {
                $isProrata = true;
                if (preg_match('/(\d+(?:\.\d+)?)\s*%?/', $upperDec, $m)) {
                    $prorataPct = (float) $m[1];
                }
            }
        }

        return [
            'battery_type' => $category === '2W' ? 'TW' : 'FW',
            'complaint_number' => $complaintNo,
            'complaint_date' => $complaintDate,
            'case_number' => $caseNo,
            'purchase_date' => $purchaseDate,
            'product_model' => $productModel,
            'product_segment' => $prodSegVal,
            'battery_serial_number' => $serialNo,
            'is_digital_warranty' => $isDigital,
            'vehicle_number' => $vehicleNo,
            'vehicle_name' => $vehicleName,
            'final_decision' => $decisionRaw,
            'is_prorata' => $isProrata,
            'prorata_percentage' => $prorataPct,
            'is_service_battery_given' => $isServiceGiven,
            'service_battery_number' => $serviceBattNo,
            'customer_mobile' => $mobileNo,
            'customer_name' => $customerName,
            'location' => $location,
            'closing_date' => $closingDate,
            'new_battery_number' => $newSerialNo,
            'return_date' => $returnDate,
            'delivery_mode' => $deliveryMode,
        ];
    }

    /**
     * Parses Excel dates safely to Y-m-d
     */
    protected function parseExcelDate($val): ?string
    {
        if (empty($val)) {
            return null;
        }

        if (is_numeric($val)) {
            try {
                $dt = Date::excelToDateTimeObject($val);

                return $dt ? $dt->format('Y-m-d') : null;
            } catch (\Exception $e) {
                return null;
            }
        }

        $str = trim((string) $val);
        $time = strtotime($str);
        if ($time) {
            return date('Y-m-d', $time);
        }

        if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})$/', $str, $m)) {
            $year = strlen($m[3]) === 2 ? '20'.$m[3] : $m[3];

            return sprintf('%04d-%02d-%02d', $year, $m[1], $m[2]);
        }

        return null;
    }
}
