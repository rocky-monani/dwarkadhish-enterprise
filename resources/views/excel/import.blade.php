@extends('layouts.app')

@section('title', 'Import Excel')

@section('content')
<div style="max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">

    <div class="card" style="margin-bottom: 0; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 600; color: var(--text-main); margin: 0;">
                Import Excel workbook
            </h2>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                Upload <code style="background-color: var(--raised-bg); padding: 2px 6px; border-radius: 4px; font-family: 'JetBrains Mono', monospace; font-size: 12px;">INWARD REG. SHEET 2026-27RRR.xlsx</code> to preview and import records.
            </p>
        </div>
        <a href="{{ route('complaints.index') }}" class="btn btn-outline">View complaints</a>
    </div>

    @if(session('message'))
        <div class="card" style="background-color: var(--primary-light); border: 1px solid var(--primary-border); border-left: 3px solid var(--primary); padding: 20px; margin-bottom: 0;">
            <h3 style="font-size: 15px; font-weight: 600; color: var(--primary-dark); margin-bottom: 6px;">Import complete</h3>
            <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px;">{{ session('message') }}</p>

            @if(session('import_stats'))
                @php $stats = session('import_stats'); @endphp
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="background-color: #ffffff; border: 1px solid var(--primary-border); padding: 12px 16px; border-radius: 8px;">
                        <div style="font-weight: 500; color: var(--text-main); font-size: 13px; margin-bottom: 6px;">2W sheet</div>
                        <div class="tabular" style="font-size: 12px; color: var(--text-secondary); display: flex; flex-direction: column; gap: 4px;">
                            <div>Inserted: <strong>+{{ $stats['sheets']['2W']['inserted'] }}</strong></div>
                            <div>Updated: <strong>{{ $stats['sheets']['2W']['updated'] }}</strong></div>
                            <div>Unchanged: <strong>{{ $stats['sheets']['2W']['unchanged'] }}</strong></div>
                        </div>
                    </div>
                    <div style="background-color: #ffffff; border: 1px solid var(--primary-border); padding: 12px 16px; border-radius: 8px;">
                        <div style="font-weight: 500; color: var(--text-main); font-size: 13px; margin-bottom: 6px;">4W sheet</div>
                        <div class="tabular" style="font-size: 12px; color: var(--text-secondary); display: flex; flex-direction: column; gap: 4px;">
                            <div>Inserted: <strong>+{{ $stats['sheets']['4W']['inserted'] }}</strong></div>
                            <div>Updated: <strong>{{ $stats['sheets']['4W']['updated'] }}</strong></div>
                            <div>Unchanged: <strong>{{ $stats['sheets']['4W']['unchanged'] }}</strong></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-left: 3px solid var(--accent-red); padding: 12px 16px; border-radius: 8px; color: #991b1b; font-size: 13px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card" style="padding: 24px; margin-bottom: 0;">
        <form action="{{ route('excel.import_preview') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="background-color: var(--bg-color); border: 1px solid var(--border-color); padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                <div style="font-weight: 500; color: var(--text-main); font-size: 13px; margin-bottom: 8px;">
                    Automatic sheet detection and dry-run preview
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 20px; font-size: 13px; color: var(--text-secondary);">
                    <div><span style="color: var(--primary-dark); font-weight: 500;">2W sheet:</span> Two-wheeler complaints (start row 8)</div>
                    <div><span style="color: var(--primary-dark); font-weight: 500;">4W sheet:</span> Four-wheeler and inverter complaints (start row 7)</div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="excel_file" class="form-label">Select Excel workbook (.xlsx)</label>
                <input type="file" id="excel_file" name="excel_file" class="form-control" accept=".xlsx" required style="height: auto; padding: 12px; border-style: dashed; cursor: pointer;">
                <span style="font-size: 12px; color: var(--text-muted); margin-top: 6px; display: block;">
                    Expected file: INWARD REG. SHEET 2026-27RRR.xlsx (max 20 MB)
                </span>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 16px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('complaints.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    Upload &amp; preview import
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
