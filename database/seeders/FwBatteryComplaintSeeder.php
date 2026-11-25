<?php

namespace Database\Seeders;

use App\Models\FwBatteryComplaint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class FwBatteryComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $fwData = [
            [
                'complaint_number' => '1521',
                'complaint_date' => '2026-05-14',
                'case_number' => '13936953',
                'purchase_date' => '2024-02-03',
                'product_model' => 'BL400R',
                'battery_serial_number' => 'CAPE0405202613',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ10BN4060',
                'vehicle_name' => 'ACE',
                'customer_mobile' => '9726055566',
                'customer_name' => 'JAY JALARAM',
                'location' => 'KHAMBHALIA',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-15',
                'new_battery_number' => 'CD420230A427218',
                'return_date' => '2026-05-15',
                'delivery_mode' => 'SEND IN ROUTE',
            ],
            [
                'complaint_number' => '1522',
                'complaint_date' => '2026-05-14',
                'case_number' => '13936980',
                'purchase_date' => '2024-02-05',
                'product_model' => 'FL550L',
                'battery_serial_number' => 'CADR0409214488',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ04BC3341',
                'customer_mobile' => '8267200589',
                'customer_name' => 'SHIV AUTO ELEC.',
                'location' => 'JAMNAGAR',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-14',
                'new_battery_number' => 'FDEC0231J103165',
                'return_date' => '2026-05-14',
                'delivery_mode' => 'BY HIMSELF',
            ],
            [
                'complaint_number' => '1523',
                'complaint_date' => '2026-05-14',
                'case_number' => '13936952',
                'purchase_date' => '2024-04-02',
                'product_model' => 'BL800R',
                'battery_serial_number' => 'FDEG0406210027',
                'is_digital_warranty' => true,
                'customer_mobile' => '9377488887',
                'customer_name' => 'AFZALBHAI',
                'location' => 'JAMNAGAR',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-19',
                'new_battery_number' => 'FDEC0231J103165',
                'return_date' => '2026-05-19',
                'delivery_mode' => 'BY HIMSELF',
            ],
            [
                'complaint_number' => '1524',
                'complaint_date' => '2026-05-14',
                'case_number' => null,
                'purchase_date' => '2024-01-08',
                'product_model' => 'BL700R',
                'battery_serial_number' => 'CAEB0405234857',
                'is_digital_warranty' => true,
                'vehicle_name' => 'YUVRAJ (TRACTOR)',
                'customer_name' => 'SWAMI AUTO ELEC.',
                'location' => 'LALPUR',
                'complaint_status' => 'Open',
                'delivery_mode' => 'SEND IN TRANSPORT',
            ],
            [
                'complaint_number' => '1525',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939103',
                'product_model' => 'GO 40B20L',
                'battery_serial_number' => 'EAE102046208577',
                'is_digital_warranty' => true,
                'customer_name' => 'SWAMI AUTO ELEC.',
                'location' => 'LALPUR',
                'complaint_status' => 'Open',
                'delivery_mode' => 'SEND IN TRANSPORT',
            ],
            [
                'complaint_number' => '1526',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939105',
                'product_model' => 'BL400R',
                'battery_serial_number' => 'CAER0409212891',
                'is_digital_warranty' => true,
                'customer_name' => 'KANAIYA BATTERY',
                'location' => 'LALPUR',
                'complaint_status' => 'Open',
                'delivery_mode' => 'BY HIMSELF',
            ],
            [
                'complaint_number' => '1527',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939130',
                'product_model' => 'GO 40B20L',
                'battery_serial_number' => 'AAE040624204291',
                'is_digital_warranty' => true,
                'customer_name' => 'SHIV BATTERY (BHAAN)',
                'location' => 'KHANVAD',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-17',
                'new_battery_number' => 'EDE12206B143924',
                'return_date' => '2026-05-17',
                'delivery_mode' => 'SEND IN TRANSPORT',
            ],
            [
                'complaint_number' => '1528',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939145',
                'purchase_date' => '2024-11-03',
                'product_model' => 'GO 40B20L',
                'battery_serial_number' => 'AA040624204201',
                'is_digital_warranty' => true,
                'customer_name' => 'BLUE STAR ELEC.',
                'location' => 'JAMNAGAR',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-17',
                'new_battery_number' => 'AA040224A205214',
                'return_date' => '2026-05-17',
                'delivery_mode' => 'BY RETAILER',
            ],
            [
                'complaint_number' => '1529',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939146',
                'product_model' => 'GO 40B20L',
                'battery_serial_number' => 'AAE040624204292',
                'is_digital_warranty' => true,
                'customer_name' => 'KHODIYAR AUTO ELEC.',
                'location' => 'JAMNAGAR',
                'complaint_status' => 'Open',
            ],
            [
                'complaint_number' => '1530',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939147',
                'purchase_date' => '2025-05-03',
                'product_model' => 'BL800R',
                'battery_serial_number' => 'CD4202241075',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ10TY5006',
                'vehicle_name' => 'MAHINDRA PICKUP',
                'customer_name' => 'JAGDAMBA BATTERY',
                'location' => 'JAMNAGAR',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-17',
                'new_battery_number' => 'CD420224A420815',
                'return_date' => '2026-05-17',
                'delivery_mode' => 'BY HIMSELF',
            ],
            [
                'complaint_number' => '1531',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939120',
                'purchase_date' => '2024-02-12',
                'product_model' => 'BL800R',
                'battery_serial_number' => 'CADR0409214472',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ10TY5006',
                'vehicle_name' => 'ACE',
                'customer_name' => 'EAGLE ENTERPRISE',
                'location' => 'JAMNAGAR',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-17',
                'new_battery_number' => 'CD420224A420815',
                'return_date' => '2026-05-17',
                'delivery_mode' => 'SULTANBHAI',
            ],
            [
                'complaint_number' => '1532',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939121',
                'purchase_date' => '2025-02-03',
                'product_model' => 'BL800R',
                'battery_serial_number' => 'CADR0409214473',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ03BN4060',
                'vehicle_name' => 'BOLERO',
                'customer_name' => 'KANAIYA AUTO ELEC.',
                'location' => 'JAMNAGAR',
                'complaint_status' => 'Open',
            ],
            [
                'complaint_number' => '1533',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939122',
                'purchase_date' => '2025-03-03',
                'product_model' => 'BL800R',
                'battery_serial_number' => 'CD4202241076',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ10TY5006',
                'customer_name' => 'KHODIYAR BATTERY',
                'location' => 'MORKANDA',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-17',
                'new_battery_number' => 'FDEC0231J103165',
                'return_date' => '2026-05-17',
                'delivery_mode' => 'BY HIMSELF',
            ],
            [
                'complaint_number' => '1534',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939123',
                'purchase_date' => '2025-03-03',
                'product_model' => 'BL800R',
                'battery_serial_number' => 'CA470222121675',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ10TY5006',
                'vehicle_name' => 'SHAKTIMAN TRACTOR',
                'customer_name' => 'KHODIYAR BATTERY',
                'location' => 'MORKANDA',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-17',
                'new_battery_number' => 'FDEC0231J103165',
                'return_date' => '2026-05-17',
                'delivery_mode' => 'BY HIMSELF',
            ],
            [
                'complaint_number' => '1535',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939124',
                'purchase_date' => '2025-03-03',
                'product_model' => 'BL800R',
                'battery_serial_number' => 'CA470222121676',
                'is_digital_warranty' => true,
                'vehicle_name' => 'ACE',
                'customer_name' => 'JAY AUTO ELEC.',
                'location' => 'JAMNAGAR',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-17',
                'new_battery_number' => 'CD420224A420815',
                'return_date' => '2026-05-17',
                'delivery_mode' => 'SEND IN TRANSPORT',
            ],
            [
                'complaint_number' => '1536',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939125',
                'purchase_date' => '2025-03-03',
                'product_model' => 'BL800R',
                'battery_serial_number' => 'CA470222121677',
                'is_digital_warranty' => true,
                'customer_name' => 'SAMRAT BATTERY',
                'location' => 'JAMJODHPUR',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-17',
                'new_battery_number' => 'ADE020524B105822',
                'return_date' => '2026-05-17',
                'delivery_mode' => 'SEND IN TRANSPORT',
            ],
            [
                'complaint_number' => '1537',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939175',
                'purchase_date' => '2024-07-15',
                'product_model' => 'ET500L',
                'battery_serial_number' => 'CD030225Y235472',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ04F4040',
                'vehicle_name' => 'SWIFT',
                'customer_name' => 'SHREE BATTERY',
                'location' => 'DHROL',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-17',
                'new_battery_number' => 'CAB031A4211552',
                'return_date' => '2026-05-17',
                'delivery_mode' => 'TRANSPORT',
            ],
            [
                'complaint_number' => '1538',
                'complaint_date' => '2026-05-15',
                'case_number' => '13939176',
                'purchase_date' => '2024-07-15',
                'product_model' => 'ET500L',
                'battery_serial_number' => 'CD030225Y235473',
                'is_digital_warranty' => true,
                'vehicle_name' => 'SWIFT',
                'customer_name' => 'MODI AUTO ELEC.',
                'location' => 'DHROL',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-17',
                'new_battery_number' => 'CAB031A4211552',
                'return_date' => '2026-05-17',
                'delivery_mode' => 'TRANSPORT',
            ],
            [
                'complaint_number' => '1539',
                'complaint_date' => '2026-05-17',
                'case_number' => '13939789',
                'purchase_date' => '2024-06-07',
                'product_model' => 'BL400R',
                'battery_serial_number' => 'CA440224Y225490',
                'is_digital_warranty' => true,
                'customer_name' => 'PATEL BATTERY AGENCY',
                'location' => 'LALPUR',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-20',
                'new_battery_number' => 'FDEC0231J103165',
                'return_date' => '2026-05-20',
                'delivery_mode' => 'BY HIMSELF',
            ],
            [
                'complaint_number' => '1540',
                'complaint_date' => '2026-05-17',
                'case_number' => '13939790',
                'purchase_date' => '2024-06-07',
                'product_model' => 'BL400R',
                'battery_serial_number' => 'CA440224Y225491',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ10DF1234',
                'vehicle_name' => 'CRETA',
                'customer_name' => 'INTEL BATTERY AGENCY',
                'location' => 'JAMNAGAR',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-25',
                'new_battery_number' => 'RECHARGE',
                'return_date' => '2026-05-25',
                'delivery_mode' => 'NAVANDAN',
            ],
            [
                'complaint_number' => '1541',
                'complaint_date' => '2026-05-17',
                'case_number' => '13939791',
                'purchase_date' => '2024-06-07',
                'product_model' => 'BL400R',
                'battery_serial_number' => 'CA440224Y225492',
                'is_digital_warranty' => true,
                'vehicle_number' => 'GJ10DF1234',
                'vehicle_name' => 'DUSTER',
                'customer_name' => 'KHODIYAR AUTO ELEC.',
                'location' => 'JAMNAGAR',
                'complaint_status' => 'Closed',
                'closing_date' => '2026-05-25',
                'new_battery_number' => 'ADE020524B105822',
                'return_date' => '2026-05-25',
                'delivery_mode' => 'NAVANDAN',
            ],
        ];

        foreach ($fwData as $item) {
            $complaint = FwBatteryComplaint::updateOrCreate(
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
