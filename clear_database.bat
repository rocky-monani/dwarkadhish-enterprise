@echo off
title Dwarkadhish Enterprise - Clear Database Data Script
color 0C

echo ============================================================
echo   DWARKADHISH ENTERPRISE - REMOVE ALL DATABASE DATA
echo ============================================================
echo.
echo WARNING: This script will WIPE and REMOVE ALL battery complaint
echo records, inward history, and complaint database entries!
echo.
echo An automatic database backup will be created in backups\ FIRST.
echo ============================================================
echo.

cd /d "%~dp0."

:: Dynamic PHP Search across all drive partitions
set "PHP_DIR="
for %%d in (C D E F G H I J K L M N O P Q R S T U V W X Y Z) do if exist "%%d:\xampp\php\php.exe" set "PHP_DIR=%%d:\xampp\php"
if defined PHP_DIR set "PATH=%PHP_DIR%;%PATH%"

where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP was not found on this PC! Cannot proceed with database clear.
    echo Please ensure XAMPP is installed.
    pause
    exit /b 1
)

set /p CONFIRM="Type YES to confirm database data deletion (or press Enter to cancel): "

if /i "%CONFIRM%"=="YES" (
    echo.
    echo Running automated database backup and wipe procedure...
    echo.
    call php artisan db:clear --force
    echo.
    echo ============================================================
    echo   ALL DATABASE DATA CLEARED SUCCESSFULLY!
    echo   Backup saved in: %~dp0backups
    echo ============================================================
) else (
    echo.
    echo Operation cancelled. No database data was deleted.
)

echo.
echo Press any key to exit...
pause
