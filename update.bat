@echo off
title Dwarkadhish Enterprise - Automatic GitHub Updater
color 0A

echo ============================================================
echo   DWARKADHISH ENTERPRISE - AUTOMATIC GITHUB UPDATE UTILITY
echo ============================================================
echo.

cd /d "%~dp0."

:: 1. Dynamic PHP Search across all drive partitions
set "PHP_DIR="
for %%d in (C D E F G H I J K L M N O P Q R S T U V W X Y Z) do (
    if exist "%%d:\xampp\php\php.exe" set "PHP_DIR=%%d:\xampp\php"
)
if defined PHP_DIR set "PATH=%PHP_DIR%;%PATH%"

:: 2. Check Git Installation
where git >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Git is not installed or not added to System PATH!
    echo Please install Git for Windows from: https://git-scm.com/downloads
    echo.
    pause
    exit /b 1
)

:: 3. Check Git Repository & Remote Configuration
if not exist ".git" (
    echo [ERROR] This folder is not a valid Git repository.
    echo Please run 'git init' first.
    echo.
    pause
    exit /b 1
)

git remote get-url origin >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] GitHub Remote URL (origin) is not set up yet!
    echo.
    echo Please link your GitHub repository first by running:
    echo   git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
    echo   git push -u origin main
    echo.
    pause
    exit /b 1
)

:: 4. Fetch and Pull latest updates from GitHub
echo [1/3] Fetching and pulling latest update files from GitHub...
echo.
call git pull origin main
if %errorlevel% neq 0 (
    echo.
    echo [WARNING] 'git pull origin main' failed. Trying generic 'git pull'...
    call git pull
)

if %errorlevel% neq 0 (
    echo.
    echo ============================================================
    echo [ERROR] Automatic Update Failed!
    echo ============================================================
    echo Possible reasons:
    echo 1. You have modified files locally that conflict with the update.
    echo 2. Internet connection issue or GitHub server unreachable.
    echo 3. The branch name is different.
    echo.
    echo To fix local modified file conflict, you can run:
    echo   git stash
    echo   update.bat
    echo ============================================================
    echo.
    pause
    exit /b 1
)

echo.
echo [2/3] Checking and running database migrations (if any)...
where php >nul 2>nul
if %errorlevel% equ 0 (
    call php artisan migrate --force
) else (
    echo       [SKIP] PHP not detected, skipping artisan migrate.
)

echo.
echo [3/3] Clearing application view ^& configuration caches...
if %errorlevel% equ 0 (
    call php artisan cache:clear >nul 2>&1
    call php artisan config:clear >nul 2>&1
    call php artisan view:clear >nul 2>&1
    call php artisan route:clear >nul 2>&1
    echo       ✅ Caches cleared successfully!
)

echo.
echo ============================================================
echo   UPDATE COMPLETED SUCCESSFULLY!
echo ============================================================
echo   Your software is now up to date with GitHub!
echo   You can start the software using START.BAT
echo ============================================================
echo.
pause
