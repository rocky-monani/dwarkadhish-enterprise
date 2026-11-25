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
     * Generate fresh Excel export from Laravel database (Streaming ZipArchive + XML for 10k+ speed)
     */
    public function generateExport(?string $fromDate = null, ?string $toDate = null, ?string $vehicleType = 'All'): array
    {
        $templatePath = config('excel.inward.template_path', storage_path('app/templates/INWARD REG. SHEET 2026-27RRR.xlsx'));

        // Ensure we have a template file on disk (cache fallback once if needed)
        if (! File::exists($templatePath)) {
            $templatePath = storage_path('app/public/temp_exports/fallback_inward_template.xlsx');
            if (! File::exists($templatePath)) {
                $tDir = dirname($templatePath);
                if (! is_dir($tDir)) {
                    mkdir($tDir, 0755, true);
                }
                $fallback = $this->createFallbackSpreadsheet();
                IOFactory::createWriter($fallback, 'Xlsx')->save($templatePath);
            }
        }

        // 2. Fetch database records using lightweight base query (no Eloquent hydration or JSON blobs)
        $query = BatteryComplaint::active()->toBase();

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

        $records = $query->select([
                'id', 'battery_type', 'complaint_number', 'complaint_date', 'case_number',
                'purchase_date', 'product_model', 'product_segment', 'battery_serial_number',
                'is_digital_warranty', 'online_reg_type', 'vehicle_number', 'vehicle_name',
                'final_decision', 'is_prorata', 'prorata_percentage', 'is_service_battery_given',
                'service_battery_number', 'customer_mobile', 'customer_name', 'location',
                'closing_date', 'new_battery_number', 'return_date', 'delivery_mode',
            ])
            ->orderBy('complaint_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

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

        // Clean up stale temp exports older than 1 hour so disk never fills up
        $this->cleanupStaleExports($dir);

        // Fast-path: direct ZipArchive + disk-streamed inlineStr XML (flat RAM even at 100k+ rows)
        $streamed = false;
        if (class_exists(\ZipArchive::class) && copy($templatePath, $tempPath)) {
            $writtenCounts = $this->streamIntoXlsxZip($tempPath, $vehicleTypeUpper, $twRecords, $fwRecords, 8);
            if ($writtenCounts !== null) {
                $streamed = true;
            }
        }

        // Safe fallback to PhpSpreadsheet if ZipArchive streaming wasn't applicable
        if (! $streamed) {
            $spreadsheet = IOFactory::load($templatePath);
            $writtenCounts = [
                '2W' => in_array($vehicleTypeUpper, ['ALL', '2W']) ? $this->populateSheet($spreadsheet, '2W', 8, $twRecords) : 0,
                '4W' => in_array($vehicleTypeUpper, ['ALL', '4W']) ? $this->populateSheet($spreadsheet, '4W', 8, $fwRecords) : 0,
            ];
            if (! in_array($vehicleTypeUpper, ['ALL', '2W'])) {
                $this->clearSheetDataArea($spreadsheet, '2W', 8);
            }
            if (! in_array($vehicleTypeUpper, ['ALL', '4W'])) {
                $this->clearSheetDataArea($spreadsheet, '4W', 8);
            }
            IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tempPath);
        }

        Log::info('Excel export generated successfully', [
            'total_records' => $records->count(),
            'written_2w' => $writtenCounts['2W'],
            'written_4w' => $writtenCounts['4W'],
            'filename' => $filename,
            'streamed' => $streamed,
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
     * Stream rows directly into an existing .xlsx ZIP archive using inlineStr XML.
     * Avoids PhpSpreadsheet Cell object hydration for 10k+ row lightning speed.
     */
    protected function streamIntoXlsxZip(string $xlsxPath, string $vehicleTypeUpper, $twRecords, $fwRecords, int $startRow): ?array
    {
        $zip = new \ZipArchive();
        if ($zip->open($xlsxPath) !== true) {
            return null;
        }

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbookXml === false || $relsXml === false) {
            $zip->close();
            return null;
        }

        $sheetMap = $this->resolveSheetPaths($workbookXml, $relsXml);
        if (empty($sheetMap)) {
            $zip->close();
            return null;
        }

        $written = ['2W' => 0, '4W' => 0];
        $tempXmlFiles = [];

        try {
            if (isset($sheetMap['2W'])) {
                $targetRecords = in_array($vehicleTypeUpper, ['ALL', '2W']) ? $twRecords : collect();
                $written['2W'] = $this->rewriteSheetXmlInZip($zip, $sheetMap['2W'], $startRow, $targetRecords, $tempXmlFiles);
            }

            if (isset($sheetMap['4W'])) {
                $targetRecords = in_array($vehicleTypeUpper, ['ALL', '4W']) ? $fwRecords : collect();
                $written['4W'] = $this->rewriteSheetXmlInZip($zip, $sheetMap['4W'], $startRow, $targetRecords, $tempXmlFiles);
            }

            $zip->close();
        } finally {
            foreach ($tempXmlFiles as $tmpFile) {
                if (is_file($tmpFile)) {
                    @unlink($tmpFile);
                }
            }
        }

        return $written;
    }

    /**
     * Map sheet names ('2W', '4W') to their internal zip XML paths.
     */
    protected function resolveSheetPaths(string $workbookXml, string $relsXml): array
    {
        $wb = @simplexml_load_string($workbookXml);
        $rels = @simplexml_load_string($relsXml);
        if (! $wb || ! $rels) {
            return [];
        }

        $relTargets = [];
        foreach ($rels->Relationship as $rel) {
            $id = (string) $rel['Id'];
            $target = ltrim((string) $rel['Target'], '/');
            if (! str_starts_with($target, 'xl/')) {
                $target = 'xl/' . $target;
            }
            $relTargets[$id] = $target;
        }

        $map = [];
        $ns = $wb->getNamespaces(true);
        $rNs = $ns['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

        if (isset($wb->sheets->sheet)) {
            foreach ($wb->sheets->sheet as $sheet) {
                $name = (string) $sheet['name'];
                $attrs = $sheet->attributes($rNs);
                $rId = (string) ($attrs['id'] ?? '');
                if ($rId !== '' && isset($relTargets[$rId])) {
                    $map[$name] = $relTargets[$rId];
                }
            }
        }

        return $map;
    }

    /**
     * Rewrite a worksheet XML inside the zip via a temp file on disk (flat RAM even at 100k+ rows).
     */
    protected function rewriteSheetXmlInZip(\ZipArchive $zip, string $sheetPath, int $startRow, $records, array &$tempXmlFiles): int
    {
        $xml = $zip->getFromName($sheetPath);
        if ($xml === false) {
            return 0;
        }

        // Normalize self-closing <sheetData/> to <sheetData></sheetData>
        if (str_contains($xml, '<sheetData/>')) {
            $xml = str_replace('<sheetData/>', '<sheetData></sheetData>', $xml);
        }

        $startTagPos = strpos($xml, '<sheetData');
        if ($startTagPos === false) {
            return 0;
        }
        $openEndPos = strpos($xml, '>', $startTagPos);
        $closeTagPos = strpos($xml, '</sheetData>', $openEndPos);
        if ($openEndPos === false || $closeTagPos === false) {
            return 0;
        }

        $beforeSheetData = substr($xml, 0, $openEndPos + 1);
        $existingSheetData = substr($xml, $openEndPos + 1, $closeTagPos - ($openEndPos + 1));
        $afterSheetData = substr($xml, $closeTagPos);
        unset($xml);

        // Preserve header rows (row r < $startRow) and capture template cell styles from row $startRow
        $headerRowsXml = '';
        $colStyles = [];
        if (preg_match_all('/<row\b[^>]*\br="(\d+)"[^>]*>.*?<\/row>/s', $existingSheetData, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $rNum = (int) $m[1];
                if ($rNum < $startRow) {
                    $headerRowsXml .= $m[0];
                } elseif ($rNum === $startRow) {
                    if (preg_match_all('/<c\b[^>]*\br="([A-Z]+)\d+"[^>]*\bs="(\d+)"/', $m[0], $cMatches, PREG_SET_ORDER)) {
                        foreach ($cMatches as $cm) {
                            $colStyles[$cm[1]] = $cm[2];
                        }
                    }
                }
            }
        }
        unset($existingSheetData);

        $colLetters = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U'];
        $styleAttrs = [];
        for ($i = 0; $i < 21; $i++) {
            $c = $colLetters[$i];
            $styleAttrs[$i] = isset($colStyles[$c]) ? ' s="' . $colStyles[$c] . '"' : '';
        }

        $xmlTempPath = tempnam(sys_get_temp_dir(), 'xlsx_sheet_');
        $tempXmlFiles[] = $xmlTempPath;
        $fh = fopen($xmlTempPath, 'wb');

        // Pre-calculate last row so dimension tag is accurate without buffering the whole sheet
        $recordCount = is_countable($records) ? count($records) : 0;
        $lastRow = max($startRow - 1, $startRow + $recordCount - 1);
        $beforeSheetData = preg_replace(
            '/<dimension\s+ref="[A-Z]+\d+:[A-Z]+\d+"\s*\/>/',
            '<dimension ref="A1:U' . $lastRow . '"/>',
            $beforeSheetData,
            1
        );

        fwrite($fh, $beforeSheetData . $headerRowsXml);
        unset($beforeSheetData, $headerRowsXml);

        $chunkBuffer = '';
        $chunkSize = 0;
        $currentRow = $startRow;
        $count = 0;

        foreach ($records as $record) {
            $vals = $this->mapRecordToRowValues($record);
            $rowXml = '<row r="' . $currentRow . '">';

            for ($i = 0; $i < 21; $i++) {
                $val = $vals[$i];
                $col = $colLetters[$i];
                $styleAttr = $styleAttrs[$i];

                if ($val === '' || $val === null) {
                    if ($styleAttr !== '') {
                        $rowXml .= '<c r="' . $col . $currentRow . '"' . $styleAttr . '/>';
                    }
                } else {
                    $str = (string) $val;
                    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $str)) {
                        $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $str);
                    }
                    $escaped = htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                    $rowXml .= '<c r="' . $col . $currentRow . '"' . $styleAttr . ' t="inlineStr"><is><t xml:space="preserve">' . $escaped . '</t></is></c>';
                }
            }

            $rowXml .= '</row>';
            $chunkBuffer .= $rowXml;
            $chunkSize++;
            $currentRow++;
            $count++;

            if ($chunkSize >= 1000) {
                fwrite($fh, $chunkBuffer);
                $chunkBuffer = '';
                $chunkSize = 0;
            }
        }

        if ($chunkBuffer !== '') {
            fwrite($fh, $chunkBuffer);
        }

        fwrite($fh, $afterSheetData);
        fclose($fh);

        $zip->addFile($xmlTempPath, $sheetPath);
        if (method_exists($zip, 'setCompressionName')) {
            $zip->setCompressionName($sheetPath, \ZipArchive::CM_DEFLATE, 1);
        }

        return $count;
    }

    /**
     * Remove stale temp export files older than 1 hour.
     */
    protected function cleanupStaleExports(string $dir): void
    {
        $cutoff = time() - 3600;
        foreach (glob($dir . DIRECTORY_SEPARATOR . 'export_*.xlsx') ?: [] as $file) {
            if (@filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    /**
     * Map a complaint record (stdClass or Model) to the 21 Excel columns (A-U).
     */
    protected function mapRecordToRowValues($record): array
    {
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

        return [
            mb_strtoupper((string) ($record->complaint_number ?? ''), 'UTF-8'),   // Col A: COMPLAIN NO
            $this->formatDate($record->complaint_date),                           // Col B: COMPLAIN DATE (dd/mm/yyyy)
            mb_strtoupper((string) ($record->case_number ?? ''), 'UTF-8'),        // Col C: CASE NO
            $this->formatDate($record->purchase_date),                            // Col D: DATE OF SALE (dd/mm/yyyy)
            mb_strtoupper((string) ($record->product_model ?? ''), 'UTF-8'),       // Col E: PRODUCT
            ! empty($record->product_segment) ? mb_strtoupper((string) $record->product_segment, 'UTF-8') : $seg, // Col F: PROD.SEG.
            mb_strtoupper((string) ($record->battery_serial_number ?? ''), 'UTF-8'), // Col G: OLD BATTERY SERIAL NO.
            $record->is_digital_warranty ? 'YES' : '',                            // Col H: DIGITAL WARANTY
            mb_strtoupper((string) ($record->online_reg_type ?? ''), 'UTF-8'),     // Col I: DIGITAL WARANTY 2
            mb_strtoupper((string) ($record->vehicle_number ?? ''), 'UTF-8'),      // Col J: VEHICLE NO.
            mb_strtoupper((string) ($record->vehicle_name ?? ''), 'UTF-8'),        // Col K: VEHICLE TYPE
            $decision,                                                            // Col L: Decision
            '',                                                                   // Col M: DE CASH MEMO
            $serviceBattGiven,                                                    // Col N: SERVICE BATT. GIVEN
            mb_strtoupper((string) ($record->customer_mobile ?? ''), 'UTF-8'),     // Col O: MOBILE NO
            mb_strtoupper((string) ($record->customer_name ?? ''), 'UTF-8'),       // Col P: CUTOMER/DEALER NAME
            mb_strtoupper((string) ($record->location ?? ''), 'UTF-8'),            // Col Q: LOCATION
            $this->formatDate($record->closing_date),                             // Col R: CLOSING DATE (dd/mm/yyyy)
            mb_strtoupper((string) ($record->new_battery_number ?? ''), 'UTF-8'), // Col S: NEW BATTERY SERIAL NO.
            $this->formatDate($record->return_date),                              // Col T: RETURN DATE (dd/mm/yyyy)
            mb_strtoupper((string) ($record->delivery_mode ?? ''), 'UTF-8'),       // Col U: TAKE PERSON NAME & MOBILE NO.
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

        $highestRow = $sheet->getHighestDataRow();
        if ($highestRow >= $startRow) {
            $sheet->removeRow($startRow, $highestRow - $startRow + 1);
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

        // Clear previous sample/data rows in one batch
        $this->clearSheetDataArea($spreadsheet, $sheetName, $startRow);

        if ($records->isEmpty()) {
            return 0;
        }

        $rows = [];
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

            // Map 21 Columns (A to U) matching template layout
            $rows[] = [
                mb_strtoupper((string) ($record->complaint_number ?? ''), 'UTF-8'),   // Col A: COMPLAIN NO
                $this->formatDate($record->complaint_date),                           // Col B: COMPLAIN DATE (dd/mm/yyyy)
                mb_strtoupper((string) ($record->case_number ?? ''), 'UTF-8'),        // Col C: CASE NO
                $this->formatDate($record->purchase_date),                            // Col D: DATE OF SALE (dd/mm/yyyy)
                mb_strtoupper((string) ($record->product_model ?? ''), 'UTF-8'),       // Col E: PRODUCT
                ! empty($record->product_segment) ? mb_strtoupper((string) $record->product_segment, 'UTF-8') : $seg, // Col F: PROD.SEG.
                mb_strtoupper((string) ($record->battery_serial_number ?? ''), 'UTF-8'), // Col G: OLD BATTERY SERIAL NO.
                $record->is_digital_warranty ? 'YES' : '',                            // Col H: DIGITAL WARANTY
                mb_strtoupper((string) ($record->online_reg_type ?? ''), 'UTF-8'),     // Col I: DIGITAL WARANTY 2 (BLANK if empty)
                mb_strtoupper((string) ($record->vehicle_number ?? ''), 'UTF-8'),      // Col J: VEHICLE NO.
                mb_strtoupper((string) ($record->vehicle_name ?? ''), 'UTF-8'),        // Col K: VEHICLE TYPE
                $decision,                                                            // Col L: Decision
                '',                                                                   // Col M: DE CASH MEMO (BLANK if empty)
                $serviceBattGiven,                                                    // Col N: SERVICE BATT. GIVEN
                mb_strtoupper((string) ($record->customer_mobile ?? ''), 'UTF-8'),     // Col O: MOBILE NO
                mb_strtoupper((string) ($record->customer_name ?? ''), 'UTF-8'),       // Col P: CUTOMER/DEALER NAME
                mb_strtoupper((string) ($record->location ?? ''), 'UTF-8'),            // Col Q: LOCATION
                $this->formatDate($record->closing_date),                             // Col R: CLOSING DATE (dd/mm/yyyy)
                mb_strtoupper((string) ($record->new_battery_number ?? ''), 'UTF-8'), // Col S: NEW BATTERY SERIAL NO.
                $this->formatDate($record->return_date),                              // Col T: RETURN DATE (dd/mm/yyyy)
                mb_strtoupper((string) ($record->delivery_mode ?? ''), 'UTF-8'),       // Col U: TAKE PERSON NAME & MOBILE NO.
            ];
        }

        // Write all rows in a single batch call
        $sheet->fromArray($rows, null, "A{$startRow}", true);

        $count = count($rows);
        $lastRow = $startRow + $count - 1;

        // Apply date format and borders once for the entire range
        foreach (['B', 'D', 'R', 'T'] as $dateCol) {
            $sheet->getStyle("{$dateCol}{$startRow}:{$dateCol}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('dd/mm/yyyy');
        }

        $sheet->getStyle("A{$startRow}:U{$lastRow}")->applyFromArray([
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

        return $count;
    }

    /**
     * Format Date values as dd/mm/yyyy (fast-path for YYYY-MM-DD strings)
     */
    protected function formatDate($date): ?string
    {
        if (empty($date)) {
            return '';
        }

        if (is_string($date) && strlen($date) >= 10 && $date[4] === '-' && $date[7] === '-') {
            return substr($date, 8, 2) . '/' . substr($date, 5, 2) . '/' . substr($date, 0, 4);
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
