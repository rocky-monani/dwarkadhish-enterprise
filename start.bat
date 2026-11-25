@echo off
title Dwarkadhish Enterprise - Application Startup (D: Drive)
color 0A

echo ============================================================
echo   DWARKADHISH ENTERPRISE - STARTING APPLICATION SERVICES
echo ============================================================
echo.

cd /d "%~dp0."

:: 1. Locate PHP (Prioritize D:\xampp\php)
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
echo Press any key to close this window...
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
if %errorlevel% equ 0 (
    echo [*] Node.js runtime ready.
) else (
    echo [WARNING] Node.js not detected in PATH. Automation server may not start.
)

:: 3. Locate & Start XAMPP Apache and MySQL (D: Drive Priority)
echo.
echo [1/4] Starting XAMPP Apache ^& MySQL background services on D: drive...

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
    )
    tasklist /FI "IMAGENAME eq httpd.exe" 2>nul | find /I /N "httpd.exe">nul
    if errorlevel 1 (
        start /min "XAMPP Apache" cmd /c "cd /d %XAMPP_DIR% && call apache_start.bat"
    )
) else (
    echo [WARNING] XAMPP background scripts not found in D:\xampp. Please start MySQL manually if needed.
)

:: 4. Start Node Playwright Automation Server (Port 3000)
echo.
echo [2/4] Starting Battery Connect Node Automation Server (Port 3000)...
powershell -NoProfile -Command "Get-ChildItem -Path '%~dp0automation\browser-profile' -Recurse -File -Include '*Singleton*','*.lock','lockfile' -ErrorAction SilentlyContinue | Remove-Item -Force -ErrorAction SilentlyContinue" 2>nul
if exist "%~dp0automation" (
    if not exist "%~dp0automation\node_modules" (
        echo       [*] Node packages missing in automation folder. Installing now...
        pushd "%~dp0automation"
        call npm install
        call npx playwright install chromium
        popd
    )
    if exist "%~dp0automation\browser-bin" (
        start /min "Battery Automation Server" cmd /c "cd /d %~dp0automation && set PLAYWRIGHT_BROWSERS_PATH=%~dp0automation\browser-bin && node server.js"
    ) else (
        start /min "Battery Automation Server" cmd /c "cd /d %~dp0automation && node server.js"
    )
)

:: 5. Start Laravel Web Server (Port 8000 on 0.0.0.0 for LAN access)
echo.
echo [3/4] Starting Laravel Application Server on Network (0.0.0.0:8000)...
start /min "Laravel App Server" cmd /c "cd /d %~dp0 && php artisan serve --host=0.0.0.0 --port=8000"

:: 6. Auto-Detect Local IP Address for LAN sharing
set LOCAL_IP=
for /f "tokens=3 delims=: " %%i in ('netsh interface ip show addresses ^| findstr /i "IP Address" ^| findstr /v "127.0.0.1"') do (
    set LOCAL_IP=%%i
)

:: 7. Launch Web Application in Chrome or Default Browser
echo.
echo [4/4] Launching Dwarkadhish Enterprise in browser...
timeout /t 3 >nul

if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles%\Google\Chrome\Application\chrome.exe" "http://127.0.0.1:8000"
) else if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" "http://127.0.0.1:8000"
) else if exist "%LocalAppData%\Google\Chrome\Application\chrome.exe" (
    start "" "%LocalAppData%\Google\Chrome\Application\chrome.exe" "http://127.0.0.1:8000"
) else (
    start "" "http://127.0.0.1:8000"
)

echo.
echo ============================================================
echo   APPLICATION IS NOW RUNNING SUCCESSFULLY!
echo ============================================================
echo   - THIS PC URL:       http://127.0.0.1:8000
if defined LOCAL_IP echo   - OTHER PCs ON LAN:  http://%LOCAL_IP%:8000
echo.
echo   You can open the LAN URL on any mobile/laptop on same WiFi!
echo   To stop all background services cleanly, run STOP.BAT
echo ============================================================
echo Press any key to exit this window...
pause
