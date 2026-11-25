<?php

namespace Database\Seeders;

use App\Models\BatteryComplaint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TwBatteryComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $twData = [
            [
                'complaint_number' => '1001',
                'complaint_date' => '2026-05-14',
                'case_number' => 'CRM-847291',
                'purchase_date' => '2024-08-24',
                'product_model' => 'APBTZ4A',
                'battery_serial_number' => 'E JE02 31 A 6 44397',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ 10 CM 5407',
                'vehicle_name' => 'HONDA-2W Activa (ES) (3G,4G,5G,6G) BS6',
                'customer_mobile' => '9876543210',
                'customer_name' => 'Ronik Patel',
                'location' => 'JAMNAGAR',
                'complaint_status' => 'Open',
                'delivery_mode' => 'BY HIMSELF',
            ],
            [
                'complaint_number' => '1002',
                'complaint_date' => '2026-05-15',
                'case_number' => 'CRM-847292',
                'purchase_date' => '2024-09-10',
                'product_model' => 'AP-BTZ5L',
                'battery_serial_number' => 'E JE02 31 A 6 44398',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ 10 AB 1234',
                'vehicle_name' => 'HERO Splendor Plus',
                'customer_mobile' => '9825012345',
                'customer_name' => 'Ramesh Varma',
                'location' => 'LALPUR',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-17',
                'new_battery_number' => 'CD420230A427900',
                'return_date' => '2026-05-17',
                'delivery_mode' => 'SEND IN ROUTE',
            ],
            [
                'complaint_number' => '1003',
                'complaint_date' => '2026-05-16',
                'case_number' => 'CRM-847293',
                'purchase_date' => '2024-10-01',
                'product_model' => 'APBTZ4A',
                'battery_serial_number' => 'E JE02 31 A 6 44399',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ 10 XY 9999',
                'vehicle_name' => 'TVS Jupiter',
                'customer_mobile' => '9909099090',
                'customer_name' => 'Mahesh Shah',
                'location' => 'KHAMBHALIA',
                'complaint_status' => 'Open',
                'delivery_mode' => 'BY RETAILER',
            ],
        ];

        foreach ($twData as $item) {
            $complaint = BatteryComplaint::updateOrCreate(
                ['complaint_number' => $item['complaint_number']],
                $item
            );

            // Auto-enrich missing details from Battery Connect API
            try {
                $response = Http::timeout(10)->post('http://127.0.0.1:3000/api/check-status', [
                    'serial_number' => $complaint->battery_serial_number,
                    'complaint_number' => $complaint->complaint_number,
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $meta = $json['metadata'] ?? [];
                    $imagePath = $json['screenshot_path'] ?? $json['image_path'] ?? null;

                    $updates = [];
                    if (! empty($meta['date_of_sale']) && empty($complaint->purchase_date)) {
                        $time = strtotime($meta['date_of_sale']);
                        if ($time) {
                            $updates['purchase_date'] = date('Y-m-d', $time);
                        }
                    }
                    if (! empty($meta['vehicle_name']) && empty($complaint->vehicle_name)) {
                        $updates['vehicle_name'] = $meta['vehicle_name'];
                    }
                    if (! empty($meta['vehicle_number']) && empty($complaint->vehicle_number)) {
                        $updates['vehicle_number'] = $meta['vehicle_number'];
                    }
                    if (! empty($meta['product_model']) && empty($complaint->product_model)) {
                        $updates['product_model'] = $meta['product_model'];
                    }
                    if ($imagePath && empty($complaint->battery_connect_image_path)) {
                        $updates['battery_connect_image_path'] = $imagePath;
                    }
                    if ($meta) {
                        $updates['battery_connect_data'] = $meta;
                        $updates['battery_connect_lookup_at'] = now();
                    }

                    if (! empty($updates)) {
                        $complaint->update($updates);
                    }
                }
            } catch (\Throwable $e) {
            }
        }
    }
}
