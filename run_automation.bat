@echo off
title Battery Connect Automation Server (Port 3000)
color 0B

echo ============================================================
echo   DWARKADHISH ENTERPRISE - BATTERY CONNECT AUTOMATION
echo ============================================================
echo.

cd /d "%~dp0automation"

:: Check Node.js
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Node.js was not found in PATH!
    echo Please make sure Node.js LTS is installed.
    echo.
    pause
    exit /b 1
)

:: Check dependencies
if not exist "node_modules" (
    echo [*] Installing missing Node.js packages in automation folder...
    call npm install
    call npx playwright install chromium
)

if exist "%~dp0automation\browser-bin" (
    set "PLAYWRIGHT_BROWSERS_PATH=%~dp0automation\browser-bin"
)

echo [*] Starting Automation Server on http://127.0.0.1:3000 ...
echo [*] Keep this window open while using the software.
echo.
node server.js
pause
