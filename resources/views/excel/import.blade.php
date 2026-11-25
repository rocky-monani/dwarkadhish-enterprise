@extends('layouts.app')

@section('title', '📥 IMPORT EXCEL WORKBOOK — INWARD REG. SHEET')

@section('content')
<div style="max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">

    <!-- HEADER / ACTION BAR -->
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid var(--border-color); padding-bottom: 0.75rem;">
        <div>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
                📥 Import Official Excel Workbook
            </h2>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">
                Upload <code style="background-color: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 700;">INWARD REG. SHEET 2026-27RRR.xlsx</code> to preview and import historical data into the database.
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('complaints.index') }}" class="btn btn-outline" style="font-size: 0.85rem; font-weight: 700;">📋 View Complaints</a>
        </div>
    </div>

    <!-- FLASH MESSAGES & IMPORT STATS SUMMARY -->
    @if(session('message'))
        <div style="background-color: #ecfdf5; border: 1px solid #6ee7b7; border-left: 5px solid #10b981; padding: 1.25rem; border-radius: var(--radius-sm);">
            <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                <div style="font-size: 1.5rem; color: #047857;">✅</div>
                <div style="flex-grow: 1;">
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #065f46; margin-bottom: 0.5rem;">IMPORT COMPLETE</h3>
                    <p style="font-size: 0.9rem; color: #047857; margin-bottom: 0.75rem;">{{ session('message') }}</p>

                    @if(session('import_stats'))
                        @php $stats = session('import_stats'); @endphp
                        <div style="margin-top: 0.75rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                            <!-- 2W STATS -->
                            <div style="background-color: #ffffff; border: 1px solid #a7f3d0; padding: 0.85rem; border-radius: 6px;">
                                <div style="font-weight: 800; color: #065f46; font-size: 0.9rem; margin-bottom: 0.35rem;">
                                    🏍️ 2W Sheet
                                </div>
                                <div style="font-size: 0.82rem; color: #047857; display: flex; flex-direction: column; gap: 0.2rem;">
                                    <div>Inserted: <strong>+{{ $stats['sheets']['2W']['inserted'] }}</strong> new</div>
                                    <div>Updated: <strong>{{ $stats['sheets']['2W']['updated'] }}</strong> existing</div>
                                    <div>Unchanged: <strong>{{ $stats['sheets']['2W']['unchanged'] }}</strong></div>
                                </div>
                            </div>
                            <!-- 4W STATS -->
                            <div style="background-color: #ffffff; border: 1px solid #a7f3d0; padding: 0.85rem; border-radius: 6px;">
                                <div style="font-weight: 800; color: #065f46; font-size: 0.9rem; margin-bottom: 0.35rem;">
                                    🚗 4W Sheet
                                </div>
                                <div style="font-size: 0.82rem; color: #047857; display: flex; flex-direction: column; gap: 0.2rem;">
                                    <div>Inserted: <strong>+{{ $stats['sheets']['4W']['inserted'] }}</strong> new</div>
                                    <div>Updated: <strong>{{ $stats['sheets']['4W']['updated'] }}</strong> existing</div>
                                    <div>Unchanged: <strong>{{ $stats['sheets']['4W']['unchanged'] }}</strong></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div style="background-color: #fef2f2; border: 1px solid #fca5a5; border-left: 5px solid #ef4444; padding: 1rem 1.25rem; border-radius: var(--radius-sm); color: #991b1b;">
            <strong>⚠️ Error:</strong> {{ session('error') }}
        </div>
    @endif

    <!-- MAIN UPLOAD FORM CARD -->
    <div class="card" style="padding: 1.5rem; border-top: 4px solid var(--primary);">
        <form action="{{ route('excel.import_preview') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- AUTOMATIC EXCEL SHEET DETECTION NOTICE -->
            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 1rem 1.25rem; border-radius: 6px; margin-bottom: 1.5rem;">
                <div style="font-weight: 700; color: var(--text-main); font-size: 0.9rem; margin-bottom: 0.5rem;">
                    📌 Automatic Excel Sheet Detection & Dry-Run Preview
                </div>
                <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; color: var(--text-secondary);">
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="color: var(--amaron-green); font-weight: 800;">✓ 2W Sheet</span>
                        <span>Two-Wheeler Complaints (Start Row 8)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="color: var(--amaron-green); font-weight: 800;">✓ 4W Sheet</span>
                        <span>Four-Wheeler & Inverter Complaints (Start Row 7)</span>
                    </div>
                </div>
            </div>

            <!-- FILE DROPZONE INPUT -->
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="excel_file" class="form-label" style="font-size: 0.95rem; font-weight: 700; color: var(--text-main);">
                    Select Excel Workbook File (.xlsx)
                </label>
                <input type="file" id="excel_file" name="excel_file" class="form-control" accept=".xlsx" required style="padding: 0.75rem; font-size: 0.95rem; border: 2px dashed #cbd5e1; background-color: #ffffff; cursor: pointer;">
                <span style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.35rem; display: block;">
                    Expected workbook file: <strong>INWARD REG. SHEET 2026-27RRR.xlsx</strong> (Maximum size: 20 MB)
                </span>
            </div>

            <!-- BUTTON BAR -->
            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <a href="{{ route('complaints.index') }}" class="btn btn-outline" style="padding: 0.65rem 1.25rem;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-weight: 800; font-size: 1rem; box-shadow: var(--shadow-sm);">
                    🔍 UPLOAD & PREVIEW IMPORT
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
