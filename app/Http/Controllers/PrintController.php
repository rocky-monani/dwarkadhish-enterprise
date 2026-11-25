<?php

namespace App\Http\Controllers;

use App\Models\BatteryComplaint;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PrintController extends Controller
{
    /**
     * Print single complaint record and increment print count
     */
    public function show($id)
    {
        $complaint = BatteryComplaint::active()->findOrFail($id);

        // Increment print counter
        $complaint->increment('print_count');

        return view('complaints.print', compact('complaint'));
    }

    /**
     * Print all today's unprinted entries (print_count == 0) in one batch
     * Optional category filter: 'TW' for 2W, 'FW' for 4W
     */
    public function printBatchToday(Request $request)
    {
        $today = date('Y-m-d');
        $category = strtoupper(trim($request->input('category', '')));
        $force = $request->boolean('force');

        // Query today's entries
        $query = BatteryComplaint::active()
            ->whereDate('complaint_date', $today);

        if (!$force) {
            $query->where('print_count', 0);
        }

        if ($category === 'TW') {
            $query->where('battery_type', 'TW');
        } elseif ($category === 'FW') {
            $query->where('battery_type', '!=', 'TW');
        }

        $complaints = $query->orderBy('id', 'asc')->get();

        if ($complaints->isEmpty()) {
            $catLabel = $category === 'TW' ? ' 2W' : ($category === 'FW' ? ' 4W' : '');
            return redirect()->route('complaints.pending_cases')
                ->with('error', "No{$catLabel} entries found to print for today ({$today}).");
        }

        // Increment print count for all printed batch records
        BatteryComplaint::whereIn('id', $complaints->pluck('id'))->increment('print_count');

        return view('complaints.print_batch', compact('complaints', 'category'));
    }

    /**
     * Reset print count to 0 for today's entries (or specific category)
     */
    public function resetPrintToday(Request $request)
    {
        $today = date('Y-m-d');
        $category = strtoupper(trim($request->input('category', 'TW')));

        $query = BatteryComplaint::active()->whereDate('complaint_date', $today);

        if ($category === 'TW') {
            $query->where('battery_type', 'TW');
        } elseif ($category === 'FW') {
            $query->where('battery_type', '!=', 'TW');
        }

        $count = $query->update(['print_count' => 0]);

        $catLabel = $category === 'TW' ? '2W (Two-Wheeler)' : ($category === 'FW' ? '4W (Four-Wheeler)' : 'all');

        return redirect()->route('complaints.pending_cases')
            ->with('success', "Print status reset to 0 for {$count} today's {$catLabel} entries. You can print them again now!");
    }

    /**
     * Build the base query for Back-Date Open Pending Cases (date < today).
     */
    private function getBackdatePendingQuery(string $today, string $category)
    {
        $query = BatteryComplaint::active()
            ->where('complaint_status', 'Open')
            ->whereDate('complaint_date', '<', $today);

        if ($category === 'TW') {
            $query->where('battery_type', 'TW');
        } elseif ($category === 'FW') {
            $query->where('battery_type', '!=', 'TW');
        }

        return $query->orderBy('complaint_date', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Print a complete report list of Back-Date Open Pending Cases (date < today)
     * Optional category filter: 'TW' for 2W, 'FW' for 4W
     */
    public function printBackdatePending(Request $request)
    {
        $today = date('Y-m-d');
        $category = strtoupper(trim($request->input('category', '')));

        $complaints = $this->getBackdatePendingQuery($today, $category)->get();

        if ($complaints->isEmpty()) {
            $catLabel = $category === 'TW' ? ' TW (2W)' : ($category === 'FW' ? ' FW (4W)' : '');
            return redirect()->route('complaints.pending_cases')
                ->with('error', "No{$catLabel} back-date pending cases found! All open cases belong to today.");
        }

        return view('complaints.print_backdate_pending', compact('complaints', 'today', 'category'));
    }

    /**
     * Export Back-Date Open Pending Cases (TW / FW / All) to Excel (.xlsx)
     */
    public function exportBackdatePending(Request $request)
    {
        $today = date('Y-m-d');
        $category = strtoupper(trim($request->input('category', '')));

        $complaints = $this->getBackdatePendingQuery($today, $category)->get();

        if ($complaints->isEmpty()) {
            $catLabel = $category === 'TW' ? ' TW (2W)' : ($category === 'FW' ? ' FW (4W)' : '');
            return redirect()->route('complaints.pending_cases')
                ->with('error', "No{$catLabel} back-date pending cases found to export!");
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheetTitle = $category === 'TW' ? 'TW Back-Date Pending' : ($category === 'FW' ? 'FW Back-Date Pending' : 'Back-Date Pending');
        $sheet->setTitle($sheetTitle);

        $headers = [
            '#',
            'Inward Date',
            'Age (Days)',
            'Co. No.',
            'Case No.',
            'Category',
            'Customer Name',
            'Mobile',
            'Location',
            'Vehicle Name',
            'Vehicle No.',
            'Product Model',
            'Battery Serial No.',
            'Standby Batt.',
            'Inspection / Action Notes',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $rowNum = 2;
        foreach ($complaints as $index => $complaint) {
            $inwardDate = $complaint->complaint_date;
            $daysOld = $inwardDate ? (int) $inwardDate->diffInDays(now()) : 0;
            $standby = $complaint->is_service_battery_given
                ? 'YES (' . ($complaint->service_battery_number ?: 'N/A') . ')'
                : 'No';

            $sheet->fromArray([
                $index + 1,
                $inwardDate ? $inwardDate->format('d/m/Y') : '-',
                $daysOld,
                $complaint->complaint_number,
                $complaint->case_number ?: '-',
                $complaint->battery_type === 'TW' ? '2W (TW)' : '4W (FW)',
                $complaint->customer_name ?: 'N/A',
                $complaint->customer_mobile ?: '-',
                $complaint->location ?: '-',
                $complaint->vehicle_name ?: '-',
                $complaint->vehicle_number ?: '-',
                $complaint->product_model ?: '-',
                $complaint->battery_serial_number,
                $standby,
                '',
            ], null, "A{$rowNum}");

            $rowNum++;
        }

        if ($rowNum > 2) {
            $lastRow = $rowNum - 1;
            $sheet->getStyle("A2:O{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'backdate_pending_') . '.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        $catSuffix = in_array($category, ['TW', 'FW'], true) ? $category : 'ALL';
        $downloadName = "backdate_pending_{$catSuffix}_{$today}.xlsx";

        return response()->download($tempPath, $downloadName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Print today's inward list report (TW or FW) formatted matching the physical paper ledger
     */
    public function printTodayList(Request $request)
    {
        $today = $request->input('date', date('Y-m-d'));
        $category = strtoupper(trim($request->input('category', 'TW')));

        $query = BatteryComplaint::active()
            ->whereDate('complaint_date', $today);

        if ($category === 'TW') {
            $query->where('battery_type', 'TW');
        } elseif ($category === 'FW') {
            $query->where('battery_type', '!=', 'TW');
        }

        $complaints = $query->orderBy('complaint_number', 'asc')->get();

        return view('complaints.print_today_list', compact('complaints', 'category', 'today'));
    }
}
