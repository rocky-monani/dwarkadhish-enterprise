<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserCredentialsSeeder extends Seeder
{
    /**
     * ============================================================
     * CHANGE YOUR LOGIN CREDENTIALS HERE:
     * ============================================================
     */
    public static string $email = 'admin@gmail.com';
    public static string $password = 'admin@123';
    public static string $name = 'Dwarkadhish Admin';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Read credentials from env if set, otherwise use variables above
        $adminEmail = env('ADMIN_EMAIL', self::$email);
        $adminPassword = env('ADMIN_PASSWORD', self::$password);
        $adminName = env('ADMIN_NAME', self::$name);

        // Update or create the main Admin user account
        $admin = User::where('role', 'admin')->first() ?: new User();
        $admin->name = $adminName;
        $admin->email = $adminEmail;
        $admin->password = Hash::make($adminPassword);
        $admin->role = 'admin';
        $admin->save();

        echo "  [SUCCESS] Admin credentials updated:\n";
        echo "  - Email:    {$adminEmail}\n";
        echo "  - Password: {$adminPassword}\n";
    }
}
