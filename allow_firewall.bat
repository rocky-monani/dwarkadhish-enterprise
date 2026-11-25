@echo off
title Dwarkadhish Enterprise - Enable Network Firewall Rule
color 0E

echo ============================================================
echo   ENABLING NETWORK ACCESS IN WINDOWS FIREWALL (PORT 8000)
echo ============================================================
echo.

netsh advfirewall firewall add rule name="Laravel Network Access (Port 8000)" dir=in action=allow protocol=TCP localport=8000 >nul 2>&1

if %errorlevel% equ 0 (
    echo ✅ SUCCESS: Port 8000 opened in Windows Firewall!
    echo    Other PCs on the same WiFi/Network can now access the software.
) else (
    echo ⚠️  Note: Please right-click this file and select "Run as Administrator"
    echo    to allow Windows Firewall permissions for Port 8000.
)

echo.
pause
