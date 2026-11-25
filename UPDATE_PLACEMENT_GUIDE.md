# 📂 Dwarkadhish Automobiles - File Placement & Update Guide

This guide details the placement for each file inside **`dwarkadhish_update_files.zip`** into your project directory on your new PC (e.g. `G:\xampp\htdocs\dwarkadhish enterprise\`).

---

## ⚡ Option A: Automatic 1-Click Update (Recommended)

1. Place **`apply_update.bat`** and **`dwarkadhish_update_files.zip`** in your project folder on the new PC (`G:\xampp\htdocs\dwarkadhish enterprise\`).
2. Double-click **`apply_update.bat`**.
3. The script will automatically detect `dwarkadhish_update_files.zip`, extract all files into their exact paths, overwrite existing versions, and clear application cache.

---

## ⚡ Option B: Manual Extract & Replace

If you don't use `apply_update.bat`:
1. Copy **`dwarkadhish_update_files.zip`** to your main project directory on the new PC (`G:\xampp\htdocs\dwarkadhish enterprise\`).
2. Right-click **`dwarkadhish_update_files.zip`** -> **Extract Here** (or extract all files and click **Replace All** when Windows prompts you).

---

## 📄 Option C: Individual File Placement List

If you prefer to copy and replace files individually, here is the exact destination path for every file in the ZIP archive:

### 1. Root Executable Scripts (Main Project Folder)
Place these files directly in the root directory `G:\xampp\htdocs\dwarkadhish enterprise\`:
- `setup.bat` -> `G:\xampp\htdocs\dwarkadhish enterprise\setup.bat`
- `start.bat` -> `G:\xampp\htdocs\dwarkadhish enterprise\start.bat`
- `stop.bat` -> `G:\xampp\htdocs\dwarkadhish enterprise\stop.bat`
- `apply_update.bat` -> `G:\xampp\htdocs\dwarkadhish enterprise\apply_update.bat`

### 2. Application Backend Files (`app/` & `routes/`)
Place these in their respective subfolders:
- `app/Http/Controllers/BatteryComplaintController.php` -> `G:\xampp\htdocs\dwarkadhish enterprise\app\Http\Controllers\BatteryComplaintController.php`
- `app/Http/Controllers/PrintController.php` -> `G:\xampp\htdocs\dwarkadhish enterprise\app\Http\Controllers\PrintController.php`
- `app/Models/BatteryComplaint.php` -> `G:\xampp\htdocs\dwarkadhish enterprise\app\Models\BatteryComplaint.php`
- `app/Console/Commands/DatabaseBackupCommand.php` -> `G:\xampp\htdocs\dwarkadhish enterprise\app\Console\Commands\DatabaseBackupCommand.php`
- `routes/web.php` -> `G:\xampp\htdocs\dwarkadhish enterprise\routes\web.php`

### 3. Application Frontend Views & Layouts (`resources/views/`)
Place these in their respective view subfolders:
- `resources/views/layouts/sidebar.blade.php` -> `G:\xampp\htdocs\dwarkadhish enterprise\resources\views\layouts\sidebar.blade.php`
- `resources/views/layouts/app.blade.php` -> `G:\xampp\htdocs\dwarkadhish enterprise\resources\views\layouts\app.blade.php`
- `resources/views/complaints/print.blade.php` -> `G:\xampp\htdocs\dwarkadhish enterprise\resources\views\complaints\print.blade.php`
- `resources/views/complaints/print_batch.blade.php` -> `G:\xampp\htdocs\dwarkadhish enterprise\resources\views\complaints\print_batch.blade.php`
- `resources/views/complaints/pending_cases.blade.php` -> `G:\xampp\htdocs\dwarkadhish enterprise\resources\views\complaints\pending_cases.blade.php`
- `resources/views/complaints/print_backdate_pending.blade.php` -> `G:\xampp\htdocs\dwarkadhish enterprise\resources\views\complaints\print_backdate_pending.blade.php`

### 4. Node Playwright Automation File (`automation/`)
Place this in the automation capture subfolder:
- `automation/src/capture/battery-connect-capture.js` -> `G:\xampp\htdocs\dwarkadhish enterprise\automation\src\capture\battery-connect-capture.js`

---

## 🚀 Step-by-Step Execution After Replacing Files

1. Double-click **`setup.bat`** on your new PC to initialize your database and build assets.
2. Double-click **`start.bat`** to run your web application!
