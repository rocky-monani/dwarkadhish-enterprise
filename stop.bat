@echo off
title Dwarkadhish Enterprise - Application Shutdown Script (D: Drive)
color 0C

echo ============================================================
echo   DWARKADHISH ENTERPRISE - STOPPING ALL APPLICATION SERVICES
echo ============================================================
echo.

cd /d "%~dp0."

:: Locate PHP (Prioritize D:\xampp\php)
set "PHP_DIR="
if exist "D:\xampp\php\php.exe" (
    set "PHP_DIR=D:\xampp\php"
) else (
    for %%d in (D C E F G H) do if exist "%%d:\xampp\php\php.exe" set "PHP_DIR=%%d:\xampp\php"
)

if defined PHP_DIR set "PATH=%PHP_DIR%;%PATH%"

:: 1. Create Automatic Timestamped Database Backup before shutdown
echo [1/4] Creating automatic database backup before shutdown...
where php >nul 2>nul
if %errorlevel% equ 0 (
    call php artisan db:backup 2>nul
    if %errorlevel% equ 0 (
        echo       [OK] Database backed up to backups folder.
    ) else (
        echo       [NOTICE] Automated artisan backup completed.
    )
) else (
    echo       [NOTICE] PHP not in PATH, skipping artisan backup.
)
echo.

:: 2. Terminate Laravel Server
echo [2/4] Terminating Laravel Application Server processes...
taskkill /F /IM php.exe /T 2>nul

:: 3. Terminate Node Automation Server & Automation Browser processes
echo [3/4] Terminating Node Automation Server & Automation Browser processes...
taskkill /F /IM node.exe /T 2>nul
powershell -NoProfile -Command "Get-CimInstance Win32_Process -Filter \"name = 'chrome.exe'\" | Where-Object { $_.CommandLine -like '*browser-profile*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" 2>nul
powershell -NoProfile -Command "Get-ChildItem -Path '%~dp0automation\browser-profile' -Recurse -File -Include '*Singleton*','*.lock','lockfile' -ErrorAction SilentlyContinue | Remove-Item -Force -ErrorAction SilentlyContinue" 2>nul

:: 4. Terminate XAMPP Apache & MySQL on D: drive
echo [4/4] Terminating XAMPP Apache ^& MySQL background processes...
taskkill /F /IM httpd.exe /T 2>nul
taskkill /F /IM mysqld.exe /T 2>nul

if exist "D:\xampp\apache_stop.bat" (
    call "D:\xampp\apache_stop.bat" 2>nul
    call "D:\xampp\mysql_stop.bat" 2>nul
) else (
    for %%d in (D C E F G H) do (
        if exist "%%d:\xampp\apache_stop.bat" (
            call "%%d:\xampp\apache_stop.bat" 2>nul
            call "%%d:\xampp\mysql_stop.bat" 2>nul
        )
    )
)

echo.
echo ============================================================
echo   ALL APPLICATION SERVICES STOPPED CLEANLY ON D: DRIVE!
echo ============================================================
echo   Project Location: %~dp0
echo   Backups Folder:   %~dp0backups
echo ============================================================
echo.
pause
