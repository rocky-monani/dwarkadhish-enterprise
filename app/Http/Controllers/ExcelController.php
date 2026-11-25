<?php

namespace App\Http\Controllers;

use App\Services\ExcelImportService;
use App\Services\InwardExcelExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ExcelController extends Controller
{
    protected ExcelImportService $importService;

    protected InwardExcelExportService $inwardExportService;

    public function __construct(ExcelImportService $importService, InwardExcelExportService $inwardExportService)
    {
        $this->importService = $importService;
        $this->inwardExportService = $inwardExportService;
    }

    /**
     * Ensure only Admin user can access Excel import features
     */
    protected function authorizeAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action. Only Admin can import Excel files.');
        }
    }

    /**
     * Display Import Excel view
     */
    public function importView()
    {
        $this->authorizeAdmin();

        return view('excel.import');
    }

    /**
     * Upload & Generate Import Preview (Dry-Run)
     */
    public function importPreview(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx|max:20480',
        ], [
            'excel_file.mimes' => 'Invalid Excel template file. Please upload an official .xlsx file containing 2W and 4W sheets.',
        ]);

        $file = $request->file('excel_file');
        $originalFilename = $file->getClientOriginalName();

        // Store file temporarily for preview confirmation
        $tempDir = storage_path('app/public/temp_imports');
        if (! File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $token = uniqid('preview_').'_'.time().'.xlsx';
        $tempPath = "{$tempDir}/{$token}";
        $file->move($tempDir, $token);

        try {
            $preview = $this->importService->previewImport($tempPath);

            session(["import_temp_{$token}" => [
                'path' => $tempPath,
                'filename' => $originalFilename,
            ]]);

            return view('excel.import_preview', [
                'preview' => $preview,
                'originalFilename' => $originalFilename,
                'tempFileToken' => $token,
            ]);
        } catch (\Exception $e) {
            if (File::exists($tempPath)) {
                File::delete($tempPath);
            }

            return redirect()->route('excel.import_view')->with('error', 'Excel preview failed: '.$e->getMessage());
        }
    }

    /**
     * Confirm & Execute Database Import inside Transaction
     */
    public function importConfirm(Request $request)
    {
        $this->authorizeAdmin();
        $token = $request->input('temp_file_token');
        $sessionData = session("import_temp_{$token}");

        $tempPath = $sessionData['path'] ?? storage_path("app/public/temp_imports/{$token}");
        $originalFilename = $sessionData['filename'] ?? 'INWARD REG. SHEET 2026-27RRR.xlsx';

        if (! File::exists($tempPath)) {
            return redirect()->route('excel.import_view')->with('error', 'Import session expired or file not found. Please upload the Excel file again.');
        }

        try {
            $stats = $this->importService->executeImport($tempPath, auth()->id());

            if (File::exists($tempPath)) {
                File::delete($tempPath);
            }
            session()->forget("import_temp_{$token}");

            $msg = "Import finished for {$originalFilename}. {$stats['inserted']} records inserted, {$stats['updated']} records updated, {$stats['unchanged']} unchanged.";

            return redirect()->route('excel.import_view')->with([
                'import_success' => true,
                'import_stats' => $stats,
                'message' => $msg,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('excel.import_view')->with('error', 'Database import failed: '.$e->getMessage());
        }
    }

    /**
     * Process Excel Import upload directly (Legacy / Direct Endpoint)
     */
    public function importProcess(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx|max:20480',
        ]);

        $file = $request->file('excel_file');
        $filePath = $file->getRealPath();

        try {
            $stats = $this->importService->executeImport($filePath, auth()->id());

            $msg = "{$stats['inserted']} imported, {$stats['updated']} updated, {$stats['unchanged']} unchanged.";

            return redirect()->route('excel.import_view')->with([
                'import_success' => true,
                'import_stats' => $stats,
                'message' => "Excel file imported successfully! {$msg}",
            ]);
        } catch (\Exception $e) {
            return redirect()->route('excel.import_view')->with('error', 'Excel Import failed: '.$e->getMessage());
        }
    }

    /**
     * Display Export Excel view
     */
    public function exportView()
    {
        return view('excel.export');
    }

    /**
     * Process Excel Export download
     */
    public function exportProcess(Request $request)
    {
        $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'vehicle_type' => 'nullable|string|in:All,2W,4W',
        ], [
            'to_date.after_or_equal' => 'From date cannot be greater than to date.',
        ]);

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $vehicleType = $request->input('vehicle_type', 'All');

        try {
            $result = $this->inwardExportService->generateExport($fromDate, $toDate, $vehicleType);

            if (! $result['success']) {
                return redirect()->route('excel.export_view')->with('error', $result['message']);
            }

            return response()->download($result['file_path'], $result['filename'], [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->route('excel.export_view')->with('error', 'Excel Export error: '.$e->getMessage());
        }
    }
}
