<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Inward Register Excel Template & Mapping Configuration
    |--------------------------------------------------------------------------
    */
    'inward' => [
        'template_path' => storage_path('app/templates/INWARD REG. SHEET 2026-27RRR.xlsx'),

        'sheets' => [
            '2W' => [
                'name' => '2W',
                'start_row' => 8,
            ],
            '4W' => [
                'name' => '4W',
                'start_row' => 7,
            ],
        ],

        'columns' => [
            'A' => 'complaint_number',
            'B' => 'complaint_date',
            'C' => 'case_number',
            'D' => 'purchase_date',
            'E' => 'product_model',
            'F' => 'battery_type',
            'G' => 'battery_serial_number',
            'H' => 'is_digital_warranty',
            'I' => 'vehicle_number',
            'J' => 'vehicle_name',
            'K' => 'final_decision',
            'L' => 'is_service_battery_given',
            'M' => 'customer_mobile',
            'N' => 'customer_name',
            'O' => 'location',
            'P' => 'closing_date',
            'Q' => 'new_battery_number',
            'R' => 'return_date',
            'S' => 'delivery_mode',
        ],
    ],
];
