@extends('layouts.app')

@section('title', 'Export Excel')

@section('content')
<div style="max-width: 720px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">

    <div class="card" style="margin-bottom: 0; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 600; color: var(--text-main); margin: 0;">
                Export inward register
            </h2>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                Generates a fresh snapshot using master template <code style="background-color: var(--raised-bg); padding: 2px 6px; border-radius: 4px; font-family: 'JetBrains Mono', monospace; font-size: 12px;">INWARD REG. SHEET 2026-27RRR.xlsx</code>
            </p>
        </div>
        <a href="{{ route('complaints.index') }}" class="btn btn-outline">View complaints</a>
    </div>

    @if(session('error'))
        <div style="padding: 12px 16px; background-color: #fef2f2; border: 1px solid #fecaca; border-left: 3px solid var(--accent-red); border-radius: 8px; color: #991b1b; font-weight: 500; font-size: 13px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card" style="margin-bottom: 0; padding: 24px;">
        <form action="{{ route('excel.export_process') }}" method="GET">

            <div class="form-row">
                <div class="form-group">
                    <label for="from_date" class="form-label">From date (optional)</label>
                    <input type="date" id="from_date" name="from_date" class="form-control" value="{{ old('from_date') }}">
                </div>

                <div class="form-group">
                    <label for="to_date" class="form-label">To date (optional)</label>
                    <input type="date" id="to_date" name="to_date" class="form-control" value="{{ old('to_date') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="vehicle_type" class="form-label">Vehicle type category</label>
                <select id="vehicle_type" name="vehicle_type" class="form-control">
                    <option value="All" {{ old('vehicle_type', 'All') === 'All' ? 'selected' : '' }}>All (2W + 4W sheets)</option>
                    <option value="2W" {{ old('vehicle_type') === '2W' ? 'selected' : '' }}>2W (Two-wheeler sheet only)</option>
                    <option value="4W" {{ old('vehicle_type') === '4W' ? 'selected' : '' }}>4W (Four-wheeler sheet only)</option>
                </select>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('complaints.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    Export Excel
                </button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 16px 20px; background-color: var(--primary-light); border: 1px solid var(--primary-border); margin-bottom: 0;">
        <div style="font-size: 13px; font-weight: 500; color: var(--primary-dark); margin-bottom: 4px;">
            Single source of truth
        </div>
        <div style="font-size: 13px; color: var(--text-secondary); line-height: 1.5;">
            Every export is generated dynamically from the live database into the clean master template file. All updates, edits, deletions, and category changes are reflected immediately in every new export.
        </div>
    </div>

</div>
@endsection
