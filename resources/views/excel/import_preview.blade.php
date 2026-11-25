@extends('layouts.app')

@section('title', '🔍 Import Excel Preview & Validation Summary')

@section('content')
<div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">

    <!-- HEADER BAR -->
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid var(--border-color); padding-bottom: 0.75rem;">
        <div>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
                🔍 IMPORT PREVIEW & VALIDATION SUMMARY
            </h2>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">
                File: <strong style="color: var(--primary);">{{ $originalFilename }}</strong>
            </p>
        </div>
        <div>
            <a href="{{ route('excel.import_view') }}" class="btn btn-outline" style="font-size: 0.85rem; font-weight: 700;">⬅️ Cancel Import</a>
        </div>
    </div>

    <!-- OVERALL SUMMARY CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
        <div style="background: #ffffff; padding: 1.15rem; border-radius: 8px; border: 1px solid var(--border-color); border-top: 4px solid var(--primary); text-align: center;">
            <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Excel Rows</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--primary); font-family: 'JetBrains Mono', monospace;">{{ $preview['total_rows'] }}</div>
        </div>

        <div style="background: #ffffff; padding: 1.15rem; border-radius: 8px; border: 1px solid var(--border-color); border-top: 4px solid var(--amaron-green); text-align: center;">
            <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Ready To Import</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--amaron-green); font-family: 'JetBrains Mono', monospace;">{{ $preview['ready_count'] }}</div>
        </div>

        <div style="background: #ffffff; padding: 1.15rem; border-radius: 8px; border: 1px solid var(--border-color); border-top: 4px solid {{ $preview['error_count'] > 0 ? 'var(--accent-red)' : '#10b981' }}; text-align: center;">
            <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Skipped / Errors</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: {{ $preview['error_count'] > 0 ? 'var(--accent-red)' : '#10b981' }}; font-family: 'JetBrains Mono', monospace;">{{ $preview['error_count'] }}</div>
        </div>
    </div>

    <!-- CATEGORY SHEET SUMMARY BREAKDOWN -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
        <!-- 2W SHEET SUMMARY -->
        <div class="card" style="padding: 1.25rem; margin-bottom: 0;">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.85rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
                <span>🏍️ 2W Sheet Records</span>
                <span class="badge" style="background-color: var(--primary-light); color: var(--primary);">{{ $preview['sheets']['2W']['total'] }} rows</span>
            </h3>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.9rem;">
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">🆕 New Records (Insert):</span>
                    <strong style="color: #047857;">+{{ $preview['sheets']['2W']['new'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">✏️ Existing Records (Update):</span>
                    <strong style="color: #d97706;">{{ $preview['sheets']['2W']['existing'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">⏸️ Identical Records (Unchanged):</span>
                    <strong style="color: var(--text-muted);">{{ $preview['sheets']['2W']['unchanged'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">⚠️ File Duplicates / Errors:</span>
                    <strong style="color: {{ ($preview['sheets']['2W']['duplicates'] + $preview['sheets']['2W']['errors']) > 0 ? 'var(--accent-red)' : 'var(--text-muted)' }};">
                        {{ $preview['sheets']['2W']['duplicates'] + $preview['sheets']['2W']['errors'] }}
                    </strong>
                </li>
            </ul>
        </div>

        <!-- 4W SHEET SUMMARY -->
        <div class="card" style="padding: 1.25rem; margin-bottom: 0;">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.85rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
                <span>🚗 4W Sheet Records</span>
                <span class="badge" style="background-color: #f1f5f9; color: var(--text-main);">{{ $preview['sheets']['4W']['total'] }} rows</span>
            </h3>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.9rem;">
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">🆕 New Records (Insert):</span>
                    <strong style="color: #047857;">+{{ $preview['sheets']['4W']['new'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">✏️ Existing Records (Update):</span>
                    <strong style="color: #d97706;">{{ $preview['sheets']['4W']['existing'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">⏸️ Identical Records (Unchanged):</span>
                    <strong style="color: var(--text-muted);">{{ $preview['sheets']['4W']['unchanged'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">⚠️ File Duplicates / Errors:</span>
                    <strong style="color: {{ ($preview['sheets']['4W']['duplicates'] + $preview['sheets']['4W']['errors']) > 0 ? 'var(--accent-red)' : 'var(--text-muted)' }};">
                        {{ $preview['sheets']['4W']['duplicates'] + $preview['sheets']['4W']['errors'] }}
                    </strong>
                </li>
            </ul>
        </div>
    </div>

    <!-- ERROR DETAILS LIST (IF ANY) -->
    @if(!empty($preview['errors']))
        <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--accent-red);">
            <h4 style="font-size: 0.95rem; font-weight: 800; color: #991b1b; margin-bottom: 0.65rem;">
                ⚠️ Validation & Duplicate Report ({{ count($preview['errors']) }} Issues)
            </h4>
            <div style="max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.85rem; font-family: 'JetBrains Mono', monospace; background: #fef2f2; padding: 0.75rem; border-radius: 6px;">
                @foreach($preview['errors'] as $err)
                    <div style="color: #991b1b;">• {{ $err }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- CONFIRMATION FORM BUTTON BAR -->
    <div class="card" style="padding: 1.25rem; background-color: #f8fafc; border: 1.5px solid var(--border-color); margin-bottom: 0;">
        <form action="{{ route('excel.import_confirm') }}" method="POST" style="display: flex; align-items: center; justify-content: space-between;">
            @csrf
            <input type="hidden" name="temp_file_token" value="{{ $tempFileToken }}">

            <a href="{{ route('excel.import_view') }}" class="btn btn-outline" style="padding: 0.75rem 1.5rem;">
                ❌ Cancel Import
            </a>

            <button type="submit" class="btn btn-success" style="padding: 0.85rem 2.5rem; font-weight: 800; font-size: 1.05rem; box-shadow: var(--shadow-md);" {{ $preview['ready_count'] === 0 ? 'disabled' : '' }}>
                ✅ CONFIRM & IMPORT DATA ({{ $preview['ready_count'] }} RECORDS)
            </button>
        </form>
    </div>

</div>
@endsection
