<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DatabaseClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear {--force : Force the operation without interactive confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create automated backup and clear/wipe all database records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('============================================================');
        $this->warn('  DWARKADHISH ENTERPRISE - CLEAR ALL DATABASE DATA');
        $this->warn('============================================================');

        if (!$this->option('force')) {
            if (!$this->confirm('WARNING: This will erase all complaint records. An automatic backup will be created first. Proceed?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('[1/3] Creating automatic timestamped database backup...');
        Artisan::call('db:backup');
        $this->info(Artisan::output());

        $this->info('[2/3] Wiping database and re-running migrations...');
        Artisan::call('migrate:fresh', ['--force' => true]);
        $this->info(Artisan::output());

        $this->info('[3/3] Seeding default system settings and user credentials...');
        Artisan::call('db:seed', ['--force' => true]);
        $this->info(Artisan::output());

        $this->info('============================================================');
        $this->info('  ALL DATABASE DATA CLEARED & RESET SUCCESSFULLY!');
        $this->info('  - Default users and system settings restored.');
        $this->info('  - Backup saved in backups/ directory.');
        $this->info('============================================================');

        return 0;
    }
}
