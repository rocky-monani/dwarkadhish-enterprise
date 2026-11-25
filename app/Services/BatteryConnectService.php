<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BatteryConnectService
{
    protected string $baseUrl;

    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(Setting::get('automation_service_url', 'http://127.0.0.1:3000'), '/');
        $this->secretKey = env('AUTOMATION_SECRET', 'amaron_local_secret_key_2026');
    }

    /**
     * Check Playwright local service health
     */
    public function checkHealth(): array
    {
        try {
            $response = Http::connectTimeout(1)->timeout(1)
                ->withHeaders(['X-Automation-Secret' => $this->secretKey])
                ->get("{$this->baseUrl}/health");

            if ($response->successful()) {
                return [
                    'online' => true,
                    'data' => $response->json(),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Playwright service health check failed: '.$e->getMessage());
        }

        return [
            'online' => false,
            'message' => 'Playwright Automation microservice is offline or unreachable.',
        ];
    }

    /**
     * Perform automated lookup for battery serial number
     */
    public function lookup(string $serialNumber, ?string $complaintNumber = null): array
    {
        try {
            $response = Http::timeout(150)
                ->withHeaders(['X-Automation-Secret' => $this->secretKey])
                ->post("{$this->baseUrl}/battery-connect/check-status", [
                    'serial_number' => $serialNumber,
                    'complaint_number' => $complaintNumber,
                ]);

            $data = $response->json() ?? [];

            $isSuccess = ($data['success'] ?? false) || ($data['code'] ?? '') === 'CAPTURE_SUCCESS';
            $code = $data['code'] ?? ($isSuccess ? 'CAPTURE_SUCCESS' : 'AUTOMATION_ERROR');
            $imagePath = $data['screenshot_path'] ?? $data['image_path'] ?? null;

            $message = $data['message'] ?? ($isSuccess ? 'Battery details fetched successfully' : 'Lookup failed');
            if ($code === 'BATTERY_NOT_FOUND') {
                $message = "No registered battery warranty record found on Battery Connect for serial {$serialNumber}. Please verify the serial number or attach a screenshot manually.";
            }

            return [
                'status' => $isSuccess ? 'SUCCESS' : $code,
                'code' => $code,
                'success' => $isSuccess,
                'image_path' => $imagePath,
                'screenshot_path' => $imagePath,
                'message' => $message,
                'battery_connect_data' => $data['metadata'] ?? $data['battery_connect_data'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('BatteryConnect lookup failed: '.$e->getMessage());

            return [
                'status' => 'SERVICE_ERROR',
                'code' => 'BATTERY_CONNECT_UNAVAILABLE',
                'success' => false,
                'message' => 'Automation service unreachable. Please ensure node server.js is running.',
            ];
        }
    }

    /**
     * Trigger headed browser opening for manual session login
     */
    public function openLoginBrowser(): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-Automation-Secret' => $this->secretKey])
                ->post("{$this->baseUrl}/battery/open-login");

            if ($response->successful()) {
                $data = $response->json() ?? [];
                $data['status'] = $data['status'] ?? ($data['success'] ?? false ? 'SUCCESS' : 'ERROR');

                return $data;
            }

            return [
                'status' => 'ERROR',
                'message' => 'Could not launch login browser.',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'ERROR',
                'message' => 'Failed to reach automation service: '.$e->getMessage(),
            ];
        }
    }
}
