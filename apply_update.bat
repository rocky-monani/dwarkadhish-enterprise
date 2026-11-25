@echo off
title Dwarkadhish Enterprise - Automatic Software Update Utility
color 0B

echo ============================================================
echo   DWARKADHISH AUTOMOBILES - AUTOMATIC UPDATE EXTRACTOR
echo ============================================================
echo.

cd /d "%~dp0"

if not exist "dwarkadhish_update_files.zip" (
    echo [ERROR] dwarkadhish_update_files.zip was not found in this folder!
    echo Please make sure dwarkadhish_update_files.zip is placed in this directory:
    echo %~dp0
    echo.
    pause
    exit /b 1
)

echo [1/3] Detecting dwarkadhish_update_files.zip...
echo       Found: %~dp0dwarkadhish_update_files.zip
echo.

echo [2/3] Extracting updated files and replacing existing versions...
powershell -Command "Expand-Archive -Path '%~dp0dwarkadhish_update_files.zip' -DestinationPath '%~dp0' -Force"

if %errorlevel% equ 0 (
    echo       ✅ Files extracted ^& updated successfully!
) else (
    echo       [ERROR] Extraction failed. Please ensure no files are currently locked by open editors.
    pause
    exit /b 1
)

echo.
echo [3/3] Clearing application view ^& configuration caches...
where php >nul 2>nul
if %errorlevel% neq 0 (
    for %%d in (C D E F G H I J K L M N O P Q R S T U V W X Y Z) do (
        if exist "%%d:\xampp\php\php.exe" (
            set "PATH=%%d:\xampp\php;%PATH%"
            goto :php_found
        )
    )
    if exist "%~dp0..\..\php\php.exe" (
        set "PATH=%~dp0..\..\php;%PATH%"
    )
)
:php_found

where php >nul 2>nul
if %errorlevel% equ 0 (
    call php artisan cache:clear >nul 2>&1
    call php artisan config:clear >nul 2>&1
    call php artisan view:clear >nul 2>&1
    echo       ✅ Caches cleared successfully!
)

echo.
echo ============================================================
echo   UPDATE COMPLETED SUCCESSFULLY!
echo ============================================================
echo   All updated files have been placed in their correct paths.
echo   You can now run START.BAT or SETUP.BAT as needed.
echo ============================================================
echo.
pause
