@echo off
title Battery Connect - Google Chrome Session Login
color 0B

echo ============================================================
echo   AMARON BATTERY CONNECT - GOOGLE CHROME SESSION LOGIN
echo ============================================================
echo.
echo   This will open Google Chrome to log into Battery Connect.
echo.
echo   [STEPS]
echo   1. Enter your Franchise User ID and Password.
echo   2. Verify the Mobile OTP.
echo   3. Once you see the Warranty Register dashboard:
echo      CLOSE the Chrome window to save your session.
echo.
echo ============================================================
echo.

cd /d "%~dp0automation"

if not exist "node_modules" (
    echo [*] Installing automation dependencies...
    call npm install
    call npx playwright install chromium
)

:: Terminate any lingering background browser processes and remove lock files
powershell -NoProfile -Command "Get-CimInstance Win32_Process -Filter \"name = 'chrome.exe'\" | Where-Object { $_.CommandLine -like '*browser-profile*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" 2>nul
powershell -NoProfile -Command "Get-ChildItem -Path 'browser-profile' -Recurse -File -Include '*Singleton*','*.lock','lockfile' -ErrorAction SilentlyContinue | Remove-Item -Force -ErrorAction SilentlyContinue" 2>nul

node src/browser/open-interactive-login.js

echo.
echo ============================================================
echo   Session is now saved and ready!
echo   You can search battery serial numbers in Dwarkadhish Enterprise.
echo ============================================================
echo.
pause
