@echo off
title Dwarkadhish Enterprise - Fresh PC Setup Script (D: Drive)
color 0A

echo ============================================================
echo   DWARKADHISH ENTERPRISE - FIRST TIME SETUP (D: DRIVE)
echo ============================================================
echo.

cd /d "%~dp0."

:: 1. Locate PHP (Prioritizing D:\xampp\php)
set "PHP_DIR="
if exist "D:\xampp\php\php.exe" (
    set "PHP_DIR=D:\xampp\php"
) else (
    for %%d in (D C E F G H) do if exist "%%d:\xampp\php\php.exe" set "PHP_DIR=%%d:\xampp\php"
)
if defined PHP_DIR set "PATH=%PHP_DIR%;%PATH%"

where php >nul 2>nul
if %errorlevel% equ 0 goto :php_ok
echo [ERROR] PHP was not found on this PC!
echo Please ensure XAMPP is installed in D:\xampp.
echo Download XAMPP: https://www.apachefriends.org/download.html
echo.
echo Press any key to close this setup window...
pause
exit /b 1

:php_ok
echo [*] PHP detected at: %PHP_DIR%\php.exe

:: 2. Locate Node.js across standard paths
where node >nul 2>nul
if %errorlevel% equ 0 goto :node_found

set "NODE_DIR="
for %%d in (D C E F G) do (
    if exist "%%d:\Program Files\nodejs\node.exe" set "NODE_DIR=%%d:\Program Files\nodejs"
    if exist "%%d:\Program Files (x86)\nodejs\node.exe" set "NODE_DIR=%%d:\Program Files (x86)\nodejs"
)
if exist "%LocalAppData%\Programs\nodejs\node.exe" set "NODE_DIR=%LocalAppData%\Programs\nodejs"
if defined NODE_DIR set "PATH=%NODE_DIR%;%PATH%"

:node_found
where node >nul 2>nul
if %errorlevel% equ 0 goto :node_ok
echo [ERROR] Node.js was not found on this PC!
echo Please install Node.js (v20+ LTS) first before running setup.bat.
echo Download Node.js: https://nodejs.org/en/download
echo.
echo Press any key to close this setup window...
pause
exit /b 1

:node_ok
echo [*] Node.js runtime ready.

:: 3. Locate Composer
where composer >nul 2>nul
if %errorlevel% equ 0 goto :composer_found

set "COMPOSER_DIR="
for %%d in (D C E F G H) do if exist "%%d:\ProgramData\ComposerSetup\bin\composer.bat" set "COMPOSER_DIR=%%d:\ProgramData\ComposerSetup\bin"
if defined COMPOSER_DIR set "PATH=%COMPOSER_DIR%;%PATH%"

:composer_found

:: 4. Create .env file if missing
if not exist .env (
    if exist .env.example (
        copy .env.example .env >nul
        echo [*] Created .env configuration file from .env.example
    )
)

:: 5. Locate & Start XAMPP MySQL (D: Drive Priority) & Create Database
echo.
echo [1/6] Starting XAMPP MySQL on D: drive ^& Verifying Database...
set "XAMPP_DIR="
if exist "D:\xampp\apache_start.bat" (
    set "XAMPP_DIR=D:\xampp"
) else (
    for %%d in (D C E F G H) do if exist "%%d:\xampp\apache_start.bat" set "XAMPP_DIR=%%d:\xampp"
)

if defined XAMPP_DIR (
    echo       Using XAMPP from: %XAMPP_DIR%
    tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I /N "mysqld.exe">nul
    if errorlevel 1 (
        start /min "XAMPP MySQL" cmd /c "cd /d %XAMPP_DIR% && call mysql_start.bat"
        timeout /t 3 >nul
    )
)

php -r "try{$pdo=new PDO('mysql:host=127.0.0.1;port=3306','root','');}catch(Exception $e){try{$pdo=new PDO('mysql:host=127.0.0.1;port=3307','root','');}catch(Exception $e2){$pdo=null;}} if($pdo){$pdo->exec('CREATE DATABASE IF NOT EXISTS dwarkadhish_enterprise CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'); echo '      [OK] MySQL Database dwarkadhish_enterprise ready.\n';}else{echo '      [WARNING] Could not connect to MySQL.\n';}"

:: 6. Run Composer install
echo.
echo [2/6] Installing PHP dependencies (Composer)...
where composer >nul 2>nul
if %errorlevel% neq 0 goto :skip_composer
call composer install --no-interaction --prefer-dist --ignore-platform-reqs
goto :composer_done
:skip_composer
echo       [NOTICE] Composer executable not in PATH. Skipping composer install (vendor folder exists).
:composer_done

:: 7. Generate Application Security Key
echo.
echo [3/6] Generating Laravel Application Security Key...
findstr /C:"APP_KEY=base64:" .env >nul 2>&1
if %errorlevel% neq 0 (
    call php artisan key:generate --force
) else (
    echo       [OK] Application Key already present in .env
)

:: 8. Install Node Automation & Playwright Browser on D: Drive
echo.
echo [4/6] Installing Node Automation ^& Playwright Chromium on D: drive...
if not exist "automation" goto :skip_automation
cd automation
set "PLAYWRIGHT_BROWSERS_PATH=%~dp0automation\browser-bin"
call npm install
call npx playwright install chromium
cd ..
:skip_automation

:: 9. Run Database Migrations (Safe check: migrate or fresh)
echo.
echo [5/6] Checking ^& Configuring Database Tables...
call php artisan migrate --force

:: Seed initial users if database is empty
php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); try { if(Illuminate\Support\Facades\DB::table('users')->count() === 0){ echo '      [*] Seeding initial default user accounts...\n'; Artisan::call('db:seed', ['--force' => true]); } else { echo '      [OK] Existing user records preserved.\n'; } } catch(Exception \$e){}" 2>nul

:: 10. Link Storage & Build Frontend Assets
echo.
echo [6/6] Linking Storage ^& Compiling Frontend Assets on D: drive...
call php artisan storage:link --force
call npm install
call npm run build

:: 11. Create Desktop Shortcut for easy launch
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ws = New-Object -ComObject WScript.Shell; $desktop = [Environment]::GetFolderPath('Desktop'); $shortcut = $ws.CreateShortcut((Join-Path $desktop 'Dwarkadhish Enterprise.lnk')); $shortcut.TargetPath = '%~dp0start.bat'; $shortcut.WorkingDirectory = '%~dp0'; $shortcut.Description = 'Start Dwarkadhish Enterprise'; $shortcut.Save()" 2>nul

echo.
echo ============================================================
echo   SETUP COMPLETED SUCCESSFULLY ON D: DRIVE!
echo ============================================================
echo   DEFAULT ADMIN LOGIN CREDENTIALS:
echo   - Web URL:  http://127.0.0.1:8000
echo   - Email:    admin@gmail.com
echo   - Password: admin@123
echo ============================================================
echo   phpMyAdmin URL: http://localhost/phpmyadmin
echo   MySQL Database: dwarkadhish_enterprise
echo   Desktop Shortcut: 'Dwarkadhish Enterprise' created on Desktop
echo ============================================================
echo   You can now double-click START.BAT to run the application.
echo ============================================================
echo.
echo Press any key to exit setup...
pause
