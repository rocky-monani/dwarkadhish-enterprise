@echo off
title Open Battery Connect Login Window
echo Opening Chrome for Battery Connect Session Login...
echo Profile Directory: %~dp0browser-profile
start "" "https://batteryconnect.com/amaron/app/warranty/register"
pause
