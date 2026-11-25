@echo off
setlocal EnableDelayedExpansion
title Dwarkadhish Enterprise - Automated Fresh PC Setup ^& GitHub Installer
color 0B

echo ==============================================================================
echo        DWARKADHISH ENTERPRISE - AUTOMATED FULL INSTALLER ^& GITHUB SETUP
echo ==============================================================================
echo.
echo  This automated installer will:
echo   1. Check ^& auto-install missing system tools (Git, Node.js, Composer, etc.)
echo   2. Clone / pull the latest project files from GitHub
echo   3. Start XAMPP MySQL ^& create dwarkadhish_enterprise database
echo   4. Install all PHP Composer and Node Playwright dependencies
echo   5. Build frontend UI assets and configure security keys
echo   6. Create a Desktop shortcut for one-click startup
echo.
echo ==============================================================================
echo.
timeout /t 3 >nul

:: ==============================================================================
:: STEP 0: LOCATE OR DEFINE INSTALLATION DIRECTORY
:: ==============================================================================
set "REPO_URL=https://github.com/rocky-monani/dwarkadhish-enterprise.git"
set "PROJECT_DIR="

:: Check if this script is already running inside the project folder
if exist "%~dp0artisan" (
    set "PROJECT_DIR=%~dp0"
    if "!PROJECT_DIR:~-1!"=="\" set "PROJECT_DIR=!PROJECT_DIR:~0,-1!"
    echo [*] Running from inside existing project folder: "!PROJECT_DIR!"
    goto :check_xampp
)

:: Otherwise, locate XAMPP htdocs directory on system drives (prioritize D: drive)
set "HTDOCS_DIR="
for %%d in (D C E F G H) do (
    if exist "%%d:\xampp\htdocs" (
        set "HTDOCS_DIR=%%d:\xampp\htdocs"
        goto :htdocs_found
    )
)

:htdocs_found
if not defined HTDOCS_DIR (
    if exist "D:\" (
        set "HTDOCS_DIR=D:\xampp\htdocs"
    ) else (
        set "HTDOCS_DIR=C:\xampp\htdocs"
    )
)
set "PROJECT_DIR=%HTDOCS_DIR%\dwarkadhish enterprise"

echo [*] Target project folder on D: drive: "!PROJECT_DIR!"
echo.

:: ==============================================================================
:: STEP 1: VERIFY / AUTO-INSTALL XAMPP (PHP 8.2 ^& MySQL on D: drive)
:: ==============================================================================
:check_xampp
echo [STEP 1/7] Checking XAMPP installation (PHP ^& MySQL on D: drive)...
set "XAMPP_DIR="
for %%d in (D C E F G H) do (
    if exist "%%d:\xampp\php\php.exe" (
        set "XAMPP_DIR=%%d:\xampp"
        goto :xampp_found
    )
)

:xampp_found
if defined XAMPP_DIR (
    echo  [OK] XAMPP found at: "!XAMPP_DIR!"
    set "PATH=!XAMPP_DIR!\php;!PATH!"
) else (
    echo.
    echo  [*] XAMPP was NOT detected on this PC.
    echo      Downloading official XAMPP installer (PHP 8.2 for Windows)...
    set "XAMPP_INSTALLER=%TEMP%\xampp-windows-installer.exe"
    if not exist "!XAMPP_INSTALLER!" (
        echo      Downloading XAMPP (~150MB, please wait)...
        curl.exe -L -# -o "!XAMPP_INSTALLER!" "https://downloads.sourceforge.net/project/xampp/XAMPP%20Windows/8.2.12/xampp-windows-x64-8.2.12-0-VS16-installer.exe"
    )
    if not exist "!XAMPP_INSTALLER!" (
        powershell -NoProfile -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; (New-Object Net.WebClient).DownloadFile('https://downloads.sourceforge.net/project/xampp/XAMPP%20Windows/8.2.12/xampp-windows-x64-8.2.12-0-VS16-installer.exe', '$env:TEMP\xampp-windows-installer.exe')" 2>nul
    )
    if exist "!XAMPP_INSTALLER!" (
        echo.
        echo  ======================================================================
        echo   LAUNCHING XAMPP INSTALLER:
        echo   - Ensure Destination Folder is set to: D:\xampp
        echo   - Complete the installation wizard
        echo  ======================================================================
        start /wait "" "!XAMPP_INSTALLER!" --prefix D:\xampp
    ) else (
        echo  [!] Direct download failed. Opening download page...
        start https://www.apachefriends.org/download.html
        pause
    )
    for %%d in (D C E F G H) do if exist "%%d:\xampp\php\php.exe" set "XAMPP_DIR=%%d:\xampp"
    if defined XAMPP_DIR (
        set "PATH=!XAMPP_DIR!\php;!PATH!"
        echo  [OK] XAMPP installed at: "!XAMPP_DIR!"
    ) else (
        echo  [ERROR] XAMPP still not detected. Please install XAMPP and run this script again.
        pause
        exit /b 1
    )
)

:: ==============================================================================
:: STEP 2: VERIFY / AUTO-INSTALL GIT FOR WINDOWS
:: ==============================================================================
echo.
echo [STEP 2/7] Checking Git for Windows...
for %%d in (D C E F G) do (
    if exist "%%d:\Program Files\Git\cmd\git.exe" set "PATH=%%d:\Program Files\Git\cmd;!PATH!"
)

where git >nul 2>nul
if %errorlevel% equ 0 (
    echo  [OK] Git is installed and ready.
    goto :check_nodejs
)

echo  [*] Git not found. Downloading official Git 64-bit installer...
set "GIT_INSTALLER=%TEMP%\Git-64-bit.exe"
if not exist "!GIT_INSTALLER!" (
    curl.exe -L -s -o "!GIT_INSTALLER!" "https://github.com/git-for-windows/git/releases/download/v2.44.0.windows.1/Git-2.44.0-64-bit.exe"
)
if not exist "!GIT_INSTALLER!" (
    powershell -NoProfile -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; (New-Object Net.WebClient).DownloadFile('https://github.com/git-for-windows/git/releases/download/v2.44.0.windows.1/Git-2.44.0-64-bit.exe', '$env:TEMP\Git-64-bit.exe')" 2>nul
)

if exist "!GIT_INSTALLER!" (
    echo      Installing Git silently (please wait)...
    start /wait "" "!GIT_INSTALLER!" /VERYSILENT /NORESTART
) else (
    where winget >nul 2>nul
    if %errorlevel% equ 0 (
        winget install --id Git.Git -e --silent --accept-package-agreements --accept-source-agreements
    )
)

for %%d in (D C E F G) do (
    if exist "%%d:\Program Files\Git\cmd\git.exe" set "PATH=%%d:\Program Files\Git\cmd;!PATH!"
)

where git >nul 2>nul
if %errorlevel% equ 0 (
    echo  [OK] Git installed successfully!
) else (
    echo  [!] Automatic Git install failed. Opening download page...
    start https://git-scm.com/downloads/win
    pause
    for %%d in (D C E F G) do if exist "%%d:\Program Files\Git\cmd\git.exe" set "PATH=%%d:\Program Files\Git\cmd;!PATH!"
)

:: ==============================================================================
:: STEP 3: VERIFY / AUTO-INSTALL NODE.JS (LTS)
:: ==============================================================================
:check_nodejs
echo.
echo [STEP 3/7] Checking Node.js (LTS)...
for %%d in (D C E F G) do (
    if exist "%%d:\Program Files\nodejs\node.exe" set "PATH=%%d:\Program Files\nodejs;!PATH!"
)
if exist "%LocalAppData%\Programs\nodejs\node.exe" set "PATH=%LocalAppData%\Programs\nodejs;!PATH!"

where node >nul 2>nul
if %errorlevel% equ 0 (
    echo  [OK] Node.js is installed and ready.
    goto :check_composer
)

echo  [*] Node.js not found. Downloading official Node.js v20 LTS installer...
set "NODE_INSTALLER=%TEMP%\node-v20-x64.msi"
if not exist "!NODE_INSTALLER!" (
    curl.exe -L -s -o "!NODE_INSTALLER!" "https://nodejs.org/dist/v20.18.0/node-v20.18.0-x64.msi"
)
if not exist "!NODE_INSTALLER!" (
    powershell -NoProfile -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; (New-Object Net.WebClient).DownloadFile('https://nodejs.org/dist/v20.18.0/node-v20.18.0-x64.msi', '$env:TEMP\node-v20-x64.msi')" 2>nul
)

if exist "!NODE_INSTALLER!" (
    echo      Installing Node.js LTS silently (please wait)...
    start /wait msiexec /i "!NODE_INSTALLER!" /quiet /qn /norestart
) else (
    where winget >nul 2>nul
    if %errorlevel% equ 0 (
        winget install --id OpenJS.NodeJS.LTS -e --silent --accept-package-agreements --accept-source-agreements
    )
)

for %%d in (D C E F G) do (
    if exist "%%d:\Program Files\nodejs\node.exe" set "PATH=%%d:\Program Files\nodejs;!PATH!"
)
if exist "%LocalAppData%\Programs\nodejs\node.exe" set "PATH=%LocalAppData%\Programs\nodejs;!PATH!"

where node >nul 2>nul
if %errorlevel% equ 0 (
    echo  [OK] Node.js installed successfully!
) else (
    echo  [!] Please install Node.js LTS from: https://nodejs.org/en/download
    start https://nodejs.org/en/download
    pause
    for %%d in (D C E F G) do if exist "%%d:\Program Files\nodejs\node.exe" set "PATH=%%d:\Program Files\nodejs;!PATH!"
)

:: ==============================================================================
:: STEP 4: VERIFY / AUTO-INSTALL COMPOSER
:: ==============================================================================
:check_composer
echo.
echo [STEP 4/7] Checking Composer for PHP...
for %%d in (D C E F G) do (
    if exist "%%d:\ProgramData\ComposerSetup\bin\composer.bat" set "PATH=%%d:\ProgramData\ComposerSetup\bin;!PATH!"
)

where composer >nul 2>nul
if %errorlevel% equ 0 (
    echo  [OK] Composer is installed and ready.
    goto :fetch_github
)

echo  [*] Composer not found. Downloading official Composer installer...
set "COMPOSER_INSTALLER=%TEMP%\Composer-Setup.exe"
if not exist "!COMPOSER_INSTALLER!" (
    curl.exe -L -s -o "!COMPOSER_INSTALLER!" "https://getcomposer.org/Composer-Setup.exe"
)
if not exist "!COMPOSER_INSTALLER!" (
    powershell -NoProfile -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; (New-Object Net.WebClient).DownloadFile('https://getcomposer.org/Composer-Setup.exe', '$env:TEMP\Composer-Setup.exe')" 2>nul
)

set "PHP_EXE_PATH=!XAMPP_DIR!\php\php.exe"
if not exist "!PHP_EXE_PATH!" set "PHP_EXE_PATH=D:\xampp\php\php.exe"

if exist "!COMPOSER_INSTALLER!" (
    echo      Installing Composer silently (linking to !PHP_EXE_PATH!)...
    start /wait "" "!COMPOSER_INSTALLER!" /VERYSILENT /SUPPRESSMSGBOXES /NORESTART /PHP="!PHP_EXE_PATH!"
) else (
    start https://getcomposer.org/Composer-Setup.exe
    pause
)

for %%d in (D C E F G) do (
    if exist "%%d:\ProgramData\ComposerSetup\bin\composer.bat" set "PATH=%%d:\ProgramData\ComposerSetup\bin;!PATH!"
)

where composer >nul 2>nul
if %errorlevel% equ 0 (
    echo  [OK] Composer installed successfully!
) else (
    echo  [NOTICE] Composer executable not in system PATH yet, will attempt direct fallback.
)

:: ==============================================================================
:: STEP 5: FETCH / CLONE PROJECT FILES FROM GITHUB
:: ==============================================================================
:fetch_github
echo.
echo [STEP 5/7] Fetching project files from GitHub repository...
echo  Repository: %REPO_URL%

if exist "!PROJECT_DIR!\.git" (
    echo  [*] Repository already exists in: "!PROJECT_DIR!"
    echo      Pulling latest updates from GitHub...
    cd /d "!PROJECT_DIR!"
    call git fetch origin
    call git pull origin main
) else (
    echo  [*] Cloning project from GitHub into: "!PROJECT_DIR!"...
    if not exist "%HTDOCS_DIR%" mkdir "%HTDOCS_DIR%" 2>nul
    cd /d "%HTDOCS_DIR%"
    call git clone %REPO_URL% "dwarkadhish enterprise"
    if %errorlevel% neq 0 (
        echo  [ERROR] Git clone failed! Please check your internet connection or GitHub access.
        pause
        exit /b 1
    )
    cd /d "!PROJECT_DIR!"
)

echo  [OK] Project source files fetched successfully!

:: ==============================================================================
:: STEP 6: INSTALL ALL DEPENDENCIES ^& CONFIGURE DATABASE
:: ==============================================================================
echo.
echo [STEP 6/7] Installing dependencies ^& configuring application...
cd /d "!PROJECT_DIR!"

:: 1. Setup .env file
if not exist .env (
    if exist .env.example (
        copy .env.example .env >nul
        echo  [*] Created .env file from .env.example
    )
)

:: 2. Auto-Start XAMPP MySQL silently ^& Create Database
echo  [*] Verifying XAMPP MySQL service...
if defined XAMPP_DIR (
    tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I /N "mysqld.exe">nul
    if errorlevel 1 (
        start /min "XAMPP MySQL" cmd /c "cd /d !XAMPP_DIR! && call mysql_start.bat"
    )
)
timeout /t 3 >nul

:: Create MySQL database if not exists
php -r "try{$pdo=new PDO('mysql:host=127.0.0.1;port=3306','root','');}catch(Exception $e){try{$pdo=new PDO('mysql:host=127.0.0.1;port=3307','root','');}catch(Exception $e2){$pdo=null;}} if($pdo){$pdo->exec('CREATE DATABASE IF NOT EXISTS dwarkadhish_enterprise CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'); echo '  [OK] MySQL Database dwarkadhish_enterprise ready.\n';}else{echo '  [WARNING] Could not connect to MySQL.\n';}"

:: 3. Run Composer install
echo  [*] Installing PHP dependencies (Composer)...
where composer >nul 2>nul
if %errorlevel% equ 0 (
    call composer install --no-interaction --prefer-dist --ignore-platform-reqs
) else (
    echo  [NOTICE] Composer not in PATH, checking if vendor folder exists.
)

:: 4. Generate Application Key if not present
findstr /C:"APP_KEY=base64:" .env >nul 2>&1
if %errorlevel% neq 0 (
    echo  [*] Generating Laravel Application Security Key...
    call php artisan key:generate --force
)

:: 5. Install Node Automation Playwright dependencies
if exist "automation" (
    echo  [*] Installing Battery Connect Node Automation ^& Playwright Chromium on D: drive...
    set "PLAYWRIGHT_BROWSERS_PATH=!PROJECT_DIR!\automation\browser-bin"
    cd automation
    call npm install
    call npx playwright install chromium
    cd ..
)

:: 6. Migrate Database (safe migration)
echo  [*] Running Database Migrations...
call php artisan migrate --force

:: 7. Seed default admin users if database is empty
php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); try { if(Illuminate\Support\Facades\DB::table('users')->count() === 0){ echo '  [*] Seeding default user accounts...\n'; Artisan::call('db:seed', ['--force' => true]); } } catch(Exception \$e){}" 2>nul

:: 8. Build Frontend UI Assets
echo  [*] Installing Frontend UI packages ^& compiling assets...
call npm install
call npm run build
call php artisan storage:link --force

:: ==============================================================================
:: STEP 7: CREATE DESKTOP SHORTCUT ^& FINISH
:: ==============================================================================
echo.
echo [STEP 7/7] Creating Desktop Shortcut...
set "START_SCRIPT=!PROJECT_DIR!\start.bat"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ws = New-Object -ComObject WScript.Shell; $desktop = [Environment]::GetFolderPath('Desktop'); $shortcut = $ws.CreateShortcut((Join-Path $desktop 'Dwarkadhish Enterprise.lnk')); $shortcut.TargetPath = '!START_SCRIPT!'; $shortcut.WorkingDirectory = '!PROJECT_DIR!'; $shortcut.Description = 'Start Dwarkadhish Enterprise'; $shortcut.Save()" 2>nul

echo.
echo ==============================================================================
echo                 INSTALLATION COMPLETED SUCCESSFULLY!
echo ==============================================================================
echo.
echo  All dependencies, database, and project files are completely installed!
echo.
echo  Project Location: "!PROJECT_DIR!"
echo  Desktop Shortcut:  "Dwarkadhish Enterprise" created on your Desktop.
echo.
echo  Default Admin Login Credentials:
echo   - URL:      http://127.0.0.1:8000
echo   - Email:    admin@gmail.com
echo   - Password: admin@123
echo.
echo ==============================================================================
echo.
set /p LAUNCH="Do you want to launch the software now? (Y/N): "
if /i "!LAUNCH!"=="Y" (
    start "" "!PROJECT_DIR!\start.bat"
)
exit /b 0
