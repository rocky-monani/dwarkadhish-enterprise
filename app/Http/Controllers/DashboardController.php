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

        $recentComplaints = BatteryComplaint::active()
            ->latest('id')
            ->take(10)
            ->get();

        // Check Automation Microservice Status
        $automationHealth = $this->batteryConnectService->checkHealth();

        return view('dashboard.index', compact(
            'todaysComplaints',
            'todaysTwCount',
            'todaysFwCount',
            'todaysBatteryChecks',
            'todaysProrataCases',
            'totalSuccessfulCaptures',
            'openComplaints',
            'totalComplaints',
            'closedComplaints',
            'pendingPrintCount',
            'twCount',
            'fwCount',
            'recentComplaints',
            'automationHealth'
        ));
    }
}
