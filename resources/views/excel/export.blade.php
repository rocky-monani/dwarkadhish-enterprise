@extends('layouts.app')

@section('title', 'EXPORT INWARD REGISTER — EXCEL')

@section('content')
<div style="max-width: 700px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">

    <!-- HEADER BAR -->
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid var(--border-color); padding-bottom: 0.75rem;">
        <div>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
                📊 EXPORT INWARD REGISTER
            </h2>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">
                Generates a fresh snapshot Excel file based on master template <code style="background-color: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 700;">INWARD REG. SHEET 2026-27RRR.xlsx</code>
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('complaints.index') }}" class="btn btn-outline" style="font-size: 0.85rem; font-weight: 700;">📋 View Complaints</a>
        </div>
    </div>

    @if(session('error'))
        <div style="padding: 1rem 1.25rem; background-color: #fef2f2; border-left: 4px solid var(--accent-red); border-radius: 6px; color: #991b1b; font-weight: 700; font-size: 0.9rem;">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <!-- MAIN EXPORT FORM CARD -->
    <div class="card" style="padding: 1.75rem; border-top: 4px solid var(--amaron-green);">
        <form action="{{ route('excel.export_process') }}" method="GET">

            <div class="form-row">
                <div class="form-group">
                    <label for="from_date" class="form-label" style="font-weight: 700;">From Date (Optional)</label>
                    <input type="date" id="from_date" name="from_date" class="form-control" value="{{ old('from_date') }}">
                </div>

                <div class="form-group">
                    <label for="to_date" class="form-label" style="font-weight: 700;">To Date (Optional)</label>
                    <input type="date" id="to_date" name="to_date" class="form-control" value="{{ old('to_date') }}">
                </div>
            </div>

            <div class="form-group" style="margin-top: 0.5rem;">
                <label for="vehicle_type" class="form-label" style="font-weight: 700;">Vehicle Type Category</label>
                <select id="vehicle_type" name="vehicle_type" class="form-control" style="font-weight: 700; font-size: 1rem;">
                    <option value="All" {{ old('vehicle_type', 'All') === 'All' ? 'selected' : '' }}>[ All (2W + 4W Sheets) ▼ ]</option>
                    <option value="2W" {{ old('vehicle_type') === '2W' ? 'selected' : '' }}>🏍️ 2W (Two Wheeler Sheet Only)</option>
                    <option value="4W" {{ old('vehicle_type') === '4W' ? 'selected' : '' }}>🚗 4W (Four Wheeler Sheet Only)</option>
                </select>
            </div>

            <!-- BUTTON BAR -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px dashed var(--border-color);">
                <a href="{{ route('complaints.index') }}" class="btn btn-outline" style="padding: 0.65rem 1.25rem;">Cancel</a>
                <button type="submit" class="btn btn-success" style="padding: 0.85rem 2.25rem; font-weight: 800; font-size: 1.05rem; box-shadow: var(--shadow-md);">
                    📥 EXPORT EXCEL
                </button>
            </div>
        </form>
    </div>

    <!-- SINGLE SOURCE OF TRUTH NOTICE -->
    <div class="card" style="padding: 1.25rem; background-color: #f0fdf4; border: 1px solid #bbf7d0; margin-bottom: 0;">
        <h4 style="font-size: 0.9rem; font-weight: 800; color: #166534; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
            <span>🛡️ Single Source of Truth Guarantee</span>
        </h4>
        <div style="font-size: 0.83rem; color: #15803d; line-height: 1.5;">
            Every export is generated dynamically directly from the live database into the clean master template file. Previous Excel files are never reused or appended to. All updates, edits, deletions, or category changes in the database are immediately reflected in every new export.
        </div>
    </div>

</div>
@endsection
