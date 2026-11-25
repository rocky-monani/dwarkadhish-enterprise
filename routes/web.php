<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatteryComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\SettingsController;
use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\EnsureCanEdit;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Service Center Operator Routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==========================================
    // BATTERY COMPLAINTS WRITE / EDIT ROUTES (Requires Editor/Admin/Operator Role)
    // Static paths MUST precede parameterized {id} routes
    // ==========================================
    Route::middleware(EnsureCanEdit::class)->group(function () {
        Route::get('/complaints/create', [BatteryComplaintController::class, 'create'])->name('complaints.create');
        Route::post('/complaints/check-duplicate', [BatteryComplaintController::class, 'checkDuplicate'])->name('complaints.check_duplicate');
        Route::post('/complaints/fetch-battery', [BatteryComplaintController::class, 'fetchBatteryConnect'])->name('complaints.fetch_battery');
        Route::post('/complaints/open-login-browser', [BatteryComplaintController::class, 'openLoginBrowser'])->name('complaints.open_login_browser');
        Route::post('/complaints/batch-fetch-list', [BatteryComplaintController::class, 'batchFetchList'])->name('complaints.batch_fetch_list');
        Route::post('/complaints/batch-fetch-pending-list', [BatteryComplaintController::class, 'batchFetchPendingList'])->name('complaints.batch_fetch_pending_list');
        Route::post('/complaints/batch-fetch-single', [BatteryComplaintController::class, 'batchFetchSingle'])->name('complaints.batch_fetch_single');
        Route::post('/complaints', [BatteryComplaintController::class, 'store'])->name('complaints.store');
        Route::get('/complaints/{id}/edit', [BatteryComplaintController::class, 'edit'])->name('complaints.edit');
        Route::put('/complaints/{id}', [BatteryComplaintController::class, 'update'])->name('complaints.update');
        Route::post('/complaints/{id}/refetch', [BatteryComplaintController::class, 'refetchBatteryConnect'])->name('complaints.refetch');
        Route::post('/complaints/{id}/close', [BatteryComplaintController::class, 'closeComplaint'])->name('complaints.close');
        Route::post('/complaints/{id}/close-direct', [BatteryComplaintController::class, 'closeDirect'])->name('complaints.close_direct');
        Route::post('/complaints/{id}/reopen', [BatteryComplaintController::class, 'reopenComplaint'])->name('complaints.reopen');
        Route::post('/complaints/reset-print-today', [PrintController::class, 'resetPrintToday'])->name('complaints.reset_print_today');
        Route::delete('/complaints/{id}', [BatteryComplaintController::class, 'destroy'])->name('complaints.destroy');
        Route::post('/complaints/{id}/case-number', [BatteryComplaintController::class, 'updateCaseNumber'])->name('complaints.update_case_number');
        Route::post('/complaints/{id}/customer-mobile', [BatteryComplaintController::class, 'updateCustomerMobile'])->name('complaints.update_customer_mobile');
        Route::post('/complaints/{id}/reg-type', [BatteryComplaintController::class, 'updateRegType'])->name('complaints.update_reg_type');
    });

    // ==========================================
    // BATTERY COMPLAINTS READ-ONLY ROUTES
    // ==========================================
    Route::get('/complaints', [BatteryComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/next-number', [BatteryComplaintController::class, 'nextNumber'])->name('complaints.next_number');
    Route::get('/complaints/pending-cases', [BatteryComplaintController::class, 'pendingCases'])->name('complaints.pending_cases');
    Route::get('/complaints/close-case', [BatteryComplaintController::class, 'closeCaseView'])->name('complaints.close_case_view');
    Route::get('/complaints/live-search-open', [BatteryComplaintController::class, 'liveSearchOpen'])->name('complaints.live_search_open');
    Route::get('/complaints/print-batch-today', [PrintController::class, 'printBatchToday'])->name('complaints.print_batch_today');
    Route::get('/complaints/print-today-list', [PrintController::class, 'printTodayList'])->name('complaints.print_today_list');
    Route::get('/complaints/print-backdate-pending', [PrintController::class, 'printBackdatePending'])->name('complaints.print_backdate_pending');
    Route::get('/complaints/export-backdate-pending', [PrintController::class, 'exportBackdatePending'])->name('complaints.export_backdate_pending');
    Route::get('/complaints/{id}', [BatteryComplaintController::class, 'show'])->name('complaints.show');
    Route::get('/complaints/{id}/print', [PrintController::class, 'show'])->name('complaints.print');

    // Export Route
    Route::get('/export/csv', [ExportController::class, 'exportCsv'])->name('export.csv');

    // Excel Export Routes (Accessible by all authorized users)
    Route::get('/excel/export', [ExcelController::class, 'exportView'])->name('excel.export_view');
    Route::get('/excel/export/download', [ExcelController::class, 'exportProcess'])->name('excel.export_process');

    // Admin Only Routes (Excel Import & System Settings)
    Route::middleware(EnsureAdminRole::class)->group(function () {
        Route::get('/excel/import', [ExcelController::class, 'importView'])->name('excel.import_view');
        Route::post('/excel/import/preview', [ExcelController::class, 'importPreview'])->name('excel.import_preview');
        Route::post('/excel/import/confirm', [ExcelController::class, 'importConfirm'])->name('excel.import_confirm');
        Route::post('/excel/import', [ExcelController::class, 'importProcess'])->name('excel.import_process');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/test-connection', [SettingsController::class, 'testConnection'])->name('settings.test_connection');
    });
});
