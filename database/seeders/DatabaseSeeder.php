<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Main Custom Admin Credentials
        $this->call(UserCredentialsSeeder::class);

        // 2. Service Center Secondary Admin User
        User::updateOrCreate(
            ['email' => 'admin@amaron.local'],
            [
                'name' => 'Service Center Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 3. Operator 1 User (Inward Entry Operator)
        User::updateOrCreate(
            ['email' => 'inward@gmail.com'],
            [
                'name' => 'Operator 1 (Inward Entry)',
                'password' => Hash::make('inward@123'),
                'role' => 'operator1',
            ]
        );

        // 4. Operator 2 User (Pending & Case Number Operator)
        User::updateOrCreate(
            ['email' => 'case@gmail.com'],
            [
                'name' => 'Operator 2 (Pending & Case No)',
                'password' => Hash::make('case@123'),
                'role' => 'operator2',
            ]
        );

        // 5. Viewer User (Read-Only Complaints Viewer)
        User::updateOrCreate(
            ['email' => 'viewer@gmail.com'],
            [
                'name' => 'Viewer (Read Only)',
                'password' => Hash::make('viewer@123'),
                'role' => 'viewer',
            ]
        );

        // Settings
        $defaultSettings = [
            'service_center_name' => 'Authorized Amaron Service Center',
            'complaint_number_label' => 'Complaint Number',
            'case_number_label' => 'Case Number',
            'battery_connect_url' => 'https://batteryconnect.com/amaron/app/warranty/register',
            'automation_service_url' => 'http://127.0.0.1:3000',
            'enable_prorata' => '1',
            'print_margin_mm' => '8',
        ];

        foreach ($defaultSettings as $key => $val) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $val, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
