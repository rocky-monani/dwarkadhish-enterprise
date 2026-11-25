<?php

namespace App\Http\Controllers;

use App\Models\BatteryComplaint;
use App\Services\BatteryConnectService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected BatteryConnectService $batteryConnectService;

    public function __construct(BatteryConnectService $batteryConnectService)
    {
        $this->batteryConnectService = $batteryConnectService;
    }

    public function index()
    {
        $todayStr = Carbon::today()->toDateString();

        // Single aggregate query for all dashboard KPIs (replaces 13 separate COUNT queries)
        $stats = BatteryComplaint::active()
            ->toBase()
            ->selectRaw("
                COUNT(*) as total_complaints,
                SUM(CASE WHEN DATE(complaint_date) = ? THEN 1 ELSE 0 END) as todays_complaints,
                SUM(CASE WHEN DATE(complaint_date) = ? AND battery_connect_image_path IS NOT NULL THEN 1 ELSE 0 END) as todays_battery_checks,
                SUM(CASE WHEN DATE(complaint_date) = ? AND is_prorata = 1 THEN 1 ELSE 0 END) as todays_prorata_cases,
                SUM(CASE WHEN battery_connect_image_path IS NOT NULL THEN 1 ELSE 0 END) as total_successful_captures,
                SUM(CASE WHEN complaint_status = 'Open' THEN 1 ELSE 0 END) as open_complaints,
                SUM(CASE WHEN complaint_status IN ('Closed', 'Rejected') THEN 1 ELSE 0 END) as closed_complaints,
                SUM(CASE WHEN complaint_status = 'Open' AND print_count = 0 THEN 1 ELSE 0 END) as pending_print_count,
                SUM(CASE WHEN battery_type = 'TW' OR excel_sheet = '2W' THEN 1 ELSE 0 END) as tw_count,
                SUM(CASE WHEN battery_type = 'FW' OR excel_sheet = '4W' THEN 1 ELSE 0 END) as fw_count,
                SUM(CASE WHEN DATE(complaint_date) = ? AND (battery_type = 'TW' OR excel_sheet = '2W') THEN 1 ELSE 0 END) as todays_tw_count,
                SUM(CASE WHEN DATE(complaint_date) = ? AND (battery_type = 'FW' OR excel_sheet = '4W') THEN 1 ELSE 0 END) as todays_fw_count,
                SUM(CASE WHEN complaint_status = 'Open' AND DATE(complaint_date) < ? AND battery_type = 'TW' THEN 1 ELSE 0 END) as backdate_tw_count,
                SUM(CASE WHEN complaint_status = 'Open' AND DATE(complaint_date) < ? AND battery_type != 'TW' THEN 1 ELSE 0 END) as backdate_fw_count
            ", [$todayStr, $todayStr, $todayStr, $todayStr, $todayStr, $todayStr, $todayStr])
            ->first();

        $totalComplaints         = (int) ($stats->total_complaints ?? 0);
        $todaysComplaints        = (int) ($stats->todays_complaints ?? 0);
        $todaysBatteryChecks     = (int) ($stats->todays_battery_checks ?? 0);
        $todaysProrataCases      = (int) ($stats->todays_prorata_cases ?? 0);
        $totalSuccessfulCaptures = (int) ($stats->total_successful_captures ?? 0);
        $openComplaints          = (int) ($stats->open_complaints ?? 0);
        $closedComplaints        = (int) ($stats->closed_complaints ?? 0);
        $pendingPrintCount       = (int) ($stats->pending_print_count ?? 0);
        $twCount                 = (int) ($stats->tw_count ?? 0);
        $fwCount                 = (int) ($stats->fw_count ?? 0);
        $todaysTwCount           = (int) ($stats->todays_tw_count ?? 0);
        $todaysFwCount           = (int) ($stats->todays_fw_count ?? 0);
        $backdatePendingTWCount  = (int) ($stats->backdate_tw_count ?? 0);
        $backdatePendingFWCount  = (int) ($stats->backdate_fw_count ?? 0);

        // Low-RAM query (no Eloquent model hydration, only 3 lightweight columns)
        $openComplaintsForChart = BatteryComplaint::active()
            ->toBase()
            ->where('complaint_status', 'Open')
            ->whereNotNull('complaint_date')
            ->orderBy('complaint_date', 'asc')
            ->get(['complaint_date', 'battery_type', 'excel_sheet']);

        $monthlyMap = [];
        foreach ($openComplaintsForChart as $item) {
            $parsedDate = Carbon::parse($item->complaint_date);
            $ym = $parsedDate->format('Y-m');
            if (!isset($monthlyMap[$ym])) {
                $monthlyMap[$ym] = [
                    'label' => $parsedDate->format('M Y'),
                    'tw' => 0,
                    'fw' => 0,
                    'total' => 0,
                ];
            }
            $isTw = ($item->battery_type === 'TW' || $item->excel_sheet === '2W');
            if ($isTw) {
                $monthlyMap[$ym]['tw']++;
            } else {
                $monthlyMap[$ym]['fw']++;
            }
            $monthlyMap[$ym]['total']++;
        }

        $monthlyPendingChart = array_values($monthlyMap);

        // Check Automation Microservice Status
        $automationHealth = $this->batteryConnectService->checkHealth();

        return view('dashboard.index', compact(
            'todaysComplaints',
            'todaysTwCount',
            'todaysFwCount',
            'backdatePendingTWCount',
            'backdatePendingFWCount',
            'monthlyPendingChart',
            'todaysBatteryChecks',
            'todaysProrataCases',
            'totalSuccessfulCaptures',
            'openComplaints',
            'totalComplaints',
            'closedComplaints',
            'pendingPrintCount',
            'twCount',
            'fwCount',
            'automationHealth'
        ));
    }
}
