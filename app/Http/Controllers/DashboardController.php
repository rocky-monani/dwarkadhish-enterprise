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
        $today = Carbon::today();

        // Operational Summary Metrics
        $todaysComplaints = BatteryComplaint::active()
            ->whereDate('complaint_date', $today)
            ->count();

        $todaysBatteryChecks = BatteryComplaint::active()
            ->whereDate('complaint_date', $today)
            ->whereNotNull('battery_connect_image_path')
            ->count();

        $todaysProrataCases = BatteryComplaint::active()
            ->whereDate('complaint_date', $today)
            ->where('is_prorata', true)
            ->count();

        $totalSuccessfulCaptures = BatteryComplaint::active()
            ->whereNotNull('battery_connect_image_path')
            ->count();

        $openComplaints = BatteryComplaint::active()
            ->where('complaint_status', 'Open')
            ->count();

        $totalComplaints = BatteryComplaint::active()->count();

        $closedComplaints = BatteryComplaint::active()
            ->whereIn('complaint_status', ['Closed', 'Rejected'])
            ->count();

        $pendingPrintCount = BatteryComplaint::active()
            ->where('complaint_status', 'Open')
            ->where('print_count', 0)
            ->count();

        $twCount = BatteryComplaint::active()
            ->where(function ($q) {
                $q->where('battery_type', 'TW')
                  ->orWhere('excel_sheet', '2W');
            })
            ->count();

        $fwCount = BatteryComplaint::active()
            ->where(function ($q) {
                $q->where('battery_type', 'FW')
                  ->orWhere('excel_sheet', '4W');
            })
            ->count();

        $todaysTwCount = BatteryComplaint::active()
            ->whereDate('complaint_date', $today)
            ->where(function ($q) {
                $q->where('battery_type', 'TW')
                  ->orWhere('excel_sheet', '2W');
            })
            ->count();

        $todaysFwCount = BatteryComplaint::active()
            ->whereDate('complaint_date', $today)
            ->where(function ($q) {
                $q->where('battery_type', 'FW')
                  ->orWhere('excel_sheet', '4W');
            })
            ->count();

        $backdatePendingTWCount = BatteryComplaint::active()
            ->where('complaint_status', 'Open')
            ->whereDate('complaint_date', '<', $today)
            ->where('battery_type', 'TW')
            ->count();

        $backdatePendingFWCount = BatteryComplaint::active()
            ->where('complaint_status', 'Open')
            ->whereDate('complaint_date', '<', $today)
            ->where('battery_type', '!=', 'TW')
            ->count();

        // All months that have open (return entry pending) TW or FW complaints
        $openComplaintsForChart = BatteryComplaint::active()
            ->where('complaint_status', 'Open')
            ->whereNotNull('complaint_date')
            ->orderBy('complaint_date', 'asc')
            ->get(['complaint_date', 'battery_type', 'excel_sheet']);

        $monthlyMap = [];
        foreach ($openComplaintsForChart as $item) {
            $ym = $item->complaint_date->format('Y-m');
            if (!isset($monthlyMap[$ym])) {
                $monthlyMap[$ym] = [
                    'label' => $item->complaint_date->format('M Y'),
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
