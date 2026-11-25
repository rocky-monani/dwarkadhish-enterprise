@extends('layouts.app')

@section('title', 'Import Preview')

@section('content')
<div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">

    <div class="card" style="margin-bottom: 0; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 600; color: var(--text-main); margin: 0;">
                Import preview &amp; validation
            </h2>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                File: <span style="font-weight: 500; color: var(--primary-dark);">{{ $originalFilename }}</span>
            </p>
        </div>
        <a href="{{ route('excel.import_view') }}" class="btn btn-outline">Cancel import</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
        <div class="card" style="margin-bottom: 0; padding: 20px;">
            <div style="font-size: 13px; color: var(--text-secondary);">Total Excel rows</div>
            <div class="tabular" style="font-size: 28px; font-weight: 500; color: var(--text-main); margin-top: 8px; line-height: 1; font-family: 'JetBrains Mono', monospace;">{{ $preview['total_rows'] }}</div>
        </div>

        <div class="card" style="margin-bottom: 0; padding: 20px;">
            <div style="font-size: 13px; color: var(--text-secondary);">Ready to import</div>
            <div class="tabular" style="font-size: 28px; font-weight: 500; color: var(--primary-dark); margin-top: 8px; line-height: 1; font-family: 'JetBrains Mono', monospace;">{{ $preview['ready_count'] }}</div>
        </div>

        <div class="card" style="margin-bottom: 0; padding: 20px;">
            <div style="font-size: 13px; color: var(--text-secondary);">Skipped / errors</div>
            <div class="tabular" style="font-size: 28px; font-weight: 500; color: {{ $preview['error_count'] > 0 ? 'var(--accent-red)' : 'var(--text-main)' }}; margin-top: 8px; line-height: 1; font-family: 'JetBrains Mono', monospace;">{{ $preview['error_count'] }}</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div class="card" style="padding: 20px; margin-bottom: 0;">
            <div class="card-header" style="margin-bottom: 12px; padding-bottom: 10px;">
                <span class="card-title">2W sheet records</span>
                <span class="badge badge-open tabular">{{ $preview['sheets']['2W']['total'] }} rows</span>
            </div>
            <ul class="tabular" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">New records (insert)</span>
                    <strong style="color: var(--primary-dark); font-weight: 500;">+{{ $preview['sheets']['2W']['new'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">Existing records (update)</span>
                    <strong style="color: #92400e; font-weight: 500;">{{ $preview['sheets']['2W']['existing'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">Identical (unchanged)</span>
                    <strong style="color: var(--text-muted); font-weight: 500;">{{ $preview['sheets']['2W']['unchanged'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">Duplicates / errors</span>
                    <strong style="color: {{ ($preview['sheets']['2W']['duplicates'] + $preview['sheets']['2W']['errors']) > 0 ? 'var(--accent-red)' : 'var(--text-muted)' }}; font-weight: 500;">
                        {{ $preview['sheets']['2W']['duplicates'] + $preview['sheets']['2W']['errors'] }}
                    </strong>
                </li>
            </ul>
        </div>

        <div class="card" style="padding: 20px; margin-bottom: 0;">
            <div class="card-header" style="margin-bottom: 12px; padding-bottom: 10px;">
                <span class="card-title">4W sheet records</span>
                <span class="badge badge-info tabular">{{ $preview['sheets']['4W']['total'] }} rows</span>
            </div>
            <ul class="tabular" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">New records (insert)</span>
                    <strong style="color: var(--primary-dark); font-weight: 500;">+{{ $preview['sheets']['4W']['new'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">Existing records (update)</span>
                    <strong style="color: #92400e; font-weight: 500;">{{ $preview['sheets']['4W']['existing'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">Identical (unchanged)</span>
                    <strong style="color: var(--text-muted); font-weight: 500;">{{ $preview['sheets']['4W']['unchanged'] }}</strong>
                </li>
                <li style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">Duplicates / errors</span>
                    <strong style="color: {{ ($preview['sheets']['4W']['duplicates'] + $preview['sheets']['4W']['errors']) > 0 ? 'var(--accent-red)' : 'var(--text-muted)' }}; font-weight: 500;">
                        {{ $preview['sheets']['4W']['duplicates'] + $preview['sheets']['4W']['errors'] }}
                    </strong>
                </li>
            </ul>
        </div>
    </div>

    @if(!empty($preview['errors']))
        <div class="card" style="padding: 20px; border-left: 3px solid var(--accent-red); margin-bottom: 0;">
            <h4 style="font-size: 14px; font-weight: 600; color: #991b1b; margin-bottom: 10px;">
                Validation &amp; duplicate report ({{ count($preview['errors']) }} issues)
            </h4>
            <div style="max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 4px; font-size: 12px; font-family: 'JetBrains Mono', monospace; background: #fef2f2; padding: 12px; border-radius: 8px;">
                @foreach($preview['errors'] as $err)
                    <div style="color: #991b1b;">• {{ $err }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card" style="padding: 20px; margin-bottom: 0;">
        <form action="{{ route('excel.import_confirm') }}" method="POST" style="display: flex; align-items: center; justify-content: space-between;">
            @csrf
            <input type="hidden" name="temp_file_token" value="{{ $tempFileToken }}">

            <a href="{{ route('excel.import_view') }}" class="btn btn-outline">
                Cancel import
            </a>

            <button type="submit" class="btn btn-primary tabular" {{ $preview['ready_count'] === 0 ? 'disabled' : '' }}>
                Confirm &amp; import ({{ $preview['ready_count'] }} records)
            </button>
        </form>
    </div>

</div>
@endsection
