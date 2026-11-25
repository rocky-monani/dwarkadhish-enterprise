@echo off
title Dwarkadhish Enterprise - Database Login Credentials
color 0B

cd /d "%~dp0"

echo.
call php get_login_details.php
echo.
pause
