<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an automated timestamped SQL backup of the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $backupDir = base_path('backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "db_backup_{$timestamp}.sql";
        $filePath = "{$backupDir}/{$filename}";

        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $tableKey = "Tables_in_{$dbName}";

        $sqlContent = "-- Dwarkadhish Automobiles Database Backup\n";
        $sqlContent .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sqlContent .= "-- Database: {$dbName}\n\n";
        $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$tableKey ?? current((array)$tableObj);

            // Structure
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createSql = $createTable[0]->{'Create Table'} ?? '';
            $sqlContent .= "-- Table structure for `{$tableName}`\n";
            $sqlContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sqlContent .= $createSql . ";\n\n";

            // Data
            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                $sqlContent .= "-- Data dumping for `{$tableName}`\n";
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $values = array_map(function ($val) {
                        if ($val === null) {
                            return 'NULL';
                        }
                        return DB::getPdo()->quote($val);
                    }, $rowArray);

                    $sqlContent .= "INSERT INTO `{$tableName}` (`" . implode('`, `', array_keys($rowArray)) . "`) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sqlContent .= "\n";
            }
        }

        $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

        File::put($filePath, $sqlContent);

        $this->info("✅ Database backup created successfully: backups/{$filename}");

        // Auto-cleanup backups older than 30 days
        $files = File::files($backupDir);
        foreach ($files as $file) {
            if ($file->getExtension() === 'sql' && (time() - $file->getMTime()) > (30 * 24 * 60 * 60)) {
                File::delete($file->getPathname());
            }
        }

        return 0;
    }
}
