<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\BatteryConnectService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected BatteryConnectService $batteryConnectService;

    public function __construct(BatteryConnectService $batteryConnectService)
    {
        $this->batteryConnectService = $batteryConnectService;
    }

    public function index()
    {
        $settings = Setting::pluck('value', 'key')->all();
        $automationHealth = $this->batteryConnectService->checkHealth();

        return view('settings.index', compact('settings', 'automationHealth'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'service_center_name' => 'required|string|max:150',
            'complaint_number_label' => 'required|string|max:50',
            'case_number_label' => 'required|string|max:50',
            'battery_connect_url' => 'required|url',
            'automation_service_url' => 'required|url',
            'enable_prorata' => 'nullable|boolean',
            'print_margin_mm' => 'required|numeric|min:0|max:30',
        ]);

        Setting::set('service_center_name', $data['service_center_name']);
        Setting::set('complaint_number_label', $data['complaint_number_label']);
        Setting::set('case_number_label', $data['case_number_label']);
        Setting::set('battery_connect_url', $data['battery_connect_url']);
        Setting::set('automation_service_url', $data['automation_service_url']);
        Setting::set('enable_prorata', $request->boolean('enable_prorata') ? '1' : '0');
        Setting::set('print_margin_mm', $data['print_margin_mm']);

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }

    public function testConnection()
    {
        $health = $this->batteryConnectService->checkHealth();

        return response()->json($health);
    }
}
