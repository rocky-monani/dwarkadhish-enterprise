<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Database\Seeders\UserCredentialsSeeder;

echo "============================================================\n";
echo "   DWARKADHISH ENTERPRISE - CURRENT LOGIN CREDENTIALS\n";
echo "============================================================\n\n";

$admin = User::where('role', 'admin')->first();

if (!$admin) {
    echo "⚠️  No admin found in database. Running credentials seeder...\n";
    $seeder = new UserCredentialsSeeder();
    $seeder->run();
    $admin = User::where('role', 'admin')->first();
}

$email = $admin ? $admin->email : UserCredentialsSeeder::$email;
$password = UserCredentialsSeeder::$password;

echo "Registered User Accounts in Database:\n";
echo "------------------------------------------------------------\n";
$users = User::all();
foreach ($users as $index => $user) {
    $num = $index + 1;
    echo "  [{$num}] Name:  {$user->name}\n";
    echo "      Email: {$user->email}\n";
    echo "      Role:  " . ($user->role ?? 'admin') . "\n\n";
}

echo "============================================================\n";
echo "   🔑 CURRENT ADMIN LOGIN CREDENTIALS:\n";
echo "============================================================\n";
echo "   App Login URL:  http://127.0.0.1:8000/login\n";
echo "   Email:          {$email}\n";
echo "   Password:       {$password}\n";
echo "============================================================\n";
echo "   To edit email or password, edit database/seeders/UserCredentialsSeeder.php\n";
echo "   and double-click update_credentials.bat\n";
echo "============================================================\n\n";
