<?php

namespace App\Http\Controllers;

use App\Models\BatteryComplaint;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportCsv(Request $request)
    {
        $query = BatteryComplaint::active()->with(['creator', 'closedBy'])->orderBy('complaint_date', 'asc')->orderBy('id', 'asc');

        if ($search = trim($request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('battery_serial_number', 'like', "%{$search}%")
                    ->orWhere('complaint_number', 'like', "%{$search}%")
                    ->orWhere('case_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_name', 'like', "%{$search}%")
                    ->orWhere('product_model', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('complaint_status', $status);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('complaint_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('complaint_date', '<=', $dateTo);
        }

        $records = $query->get();

        $fileName = 'tw_battery_complaints_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Exact 19 Header Columns
            fputcsv($file, [
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
            ]);

            foreach ($records as $row) {
                $decisionRaw = mb_strtoupper(trim((string) ($row->final_decision ?? '')), 'UTF-8');

                $pct = null;
                if ($row->prorata_percentage !== null && (float) $row->prorata_percentage > 0) {
                    $pct = (float) $row->prorata_percentage;
                }

                if ($row->is_prorata || in_array($decisionRaw, ['P', 'PRO-RATA', 'PRORATA', 'PRO RATA']) || str_contains($decisionRaw, 'PRORATA') || str_starts_with($decisionRaw, 'P')) {
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

                $closingDateStr = $row->closing_date ? $row->closing_date->format('d-m-Y') : '';
                $returnDateStr = $row->return_date ? $row->return_date->format('d-m-Y') : $closingDateStr;

                fputcsv($file, [
                    mb_strtoupper((string) ($row->complaint_number ?? ''), 'UTF-8'),
                    $row->complaint_date ? $row->complaint_date->format('d-m-Y') : '',
                    mb_strtoupper((string) ($row->case_number ?? ''), 'UTF-8'),
                    $row->purchase_date ? $row->purchase_date->format('d-m-Y') : '',
                    mb_strtoupper((string) ($row->product_model ?? ''), 'UTF-8'),
                    'TW',
                    mb_strtoupper((string) ($row->battery_serial_number ?? ''), 'UTF-8'),
                    $row->is_digital_warranty ? 'YES' : 'NO',
                    mb_strtoupper((string) ($row->vehicle_number ?? ''), 'UTF-8'),
                    mb_strtoupper((string) ($row->vehicle_name ?? ''), 'UTF-8'),
                    $decision,
                    $serviceBattGiven,
                    mb_strtoupper((string) ($row->customer_mobile ?? ''), 'UTF-8'),
                    mb_strtoupper((string) ($row->customer_name ?? ''), 'UTF-8'),
                    mb_strtoupper((string) ($row->location ?? ''), 'UTF-8'),
                    $closingDateStr,
                    mb_strtoupper((string) ($row->new_battery_number ?? ''), 'UTF-8'),
                    $returnDateStr,
                    mb_strtoupper((string) ($row->delivery_mode ?? ''), 'UTF-8'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
