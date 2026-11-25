@echo off
title Dwarkadhish Enterprise - Update Admin Login Credentials
color 0B

echo ============================================================
echo   DWARKADHISH ENTERPRISE - UPDATING LOGIN CREDENTIALS
echo ============================================================
echo.

cd /d "%~dp0"

where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" set "PATH=C:\xampp\php;%PATH%"
    if exist "D:\xampp\php\php.exe" set "PATH=D:\xampp\php;%PATH%"
    if exist "E:\xampp\php\php.exe" set "PATH=E:\xampp\php;%PATH%"
)

echo Updating Admin credentials in database...
call php artisan db:seed --class=UserCredentialsSeeder --force

echo.
echo Displaying updated login credentials:
echo.
call php get_login_details.php

pause
