@extends('layouts.app')

@section('title', 'Admin Settings & Automation Configuration')

@section('content')
<div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">

    <!-- System Status & Connection Card -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Automation Service Health & Diagnostics
            </h3>

            <button type="button" id="btn_test_conn" class="btn btn-outline" style="padding: 0.35rem 0.85rem; font-size: 0.85rem;">
                🔄 Test Microservice Connection
            </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <div style="padding: 1rem; background-color: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Microservice Endpoint</div>
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-top: 0.25rem;">http://127.0.0.1:3000</div>
            </div>

            <div style="padding: 1rem; background-color: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Connection Status</div>
                <div id="conn_status_text" style="font-size: 0.95rem; font-weight: 800; color: var(--amaron-green); margin-top: 0.25rem;">
                    Active (Online)
                </div>
            </div>

            <div style="padding: 1rem; background-color: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Google Chrome Integration</div>
                <div style="font-size: 0.95rem; font-weight: 800; color: var(--text-main); margin-top: 0.25rem;">
                    CDP Port 9222 / Persistent
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Settings Configuration Form -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Google Chrome & Microservice Configuration
            </h3>
        </div>

        <form action="{{ route('settings.update') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="brave_executable_path" class="form-label">Google Chrome Executable Path (Windows)</label>
                <input type="text" id="brave_executable_path" name="brave_executable_path" class="form-control" style="font-family: 'JetBrains Mono', monospace;" value="{{ old('brave_executable_path', $settings['brave_executable_path'] ?? 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe') }}" required>
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.25rem;">Path to installed Google Chrome binary on Windows PC.</div>
            </div>

            <div class="form-group">
                <label for="brave_user_data_dir" class="form-label">Google Chrome User Data Directory</label>
                <input type="text" id="brave_user_data_dir" name="brave_user_data_dir" class="form-control" style="font-family: 'JetBrains Mono', monospace;" value="{{ old('brave_user_data_dir', $settings['brave_user_data_dir'] ?? '%LOCALAPPDATA%\\Google\\Chrome\\User Data') }}" required>
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.25rem;">Directory containing your authenticated Google Chrome browser profile.</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="brave_profile_name" class="form-label">Profile Name</label>
                    <input type="text" id="brave_profile_name" name="brave_profile_name" class="form-control" value="{{ old('brave_profile_name', $settings['brave_profile_name'] ?? 'Default') }}" required>
                </div>

                <div class="form-group">
                    <label for="automation_service_url" class="form-label">Automation Service URL</label>
                    <input type="text" id="automation_service_url" name="automation_service_url" class="form-control" style="font-family: 'JetBrains Mono', monospace;" value="{{ old('automation_service_url', $settings['automation_service_url'] ?? 'http://127.0.0.1:3000') }}" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem;">
                    💾 Save Configuration
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.getElementById('btn_test_conn').addEventListener('click', async function() {
    const statusText = document.getElementById('conn_status_text');
    statusText.innerText = 'Testing...';
    statusText.style.color = 'var(--text-muted)';

    try {
        const data = await safeFetchJson("{{ route('settings.test_connection') }}");
        if (data.online) {
            statusText.innerText = 'Active (Online)';
            statusText.style.color = 'var(--amaron-green)';
            showToast('✓ Playwright automation microservice is active and healthy!', 'success');
        } else {
            statusText.innerText = 'Offline';
            statusText.style.color = 'var(--accent-red)';
            showToast('✕ Could not connect to automation microservice on port 3000.', 'error');
        }
    } catch (err) {
        statusText.innerText = 'Offline';
        statusText.style.color = 'var(--accent-red)';
        showToast('✕ Error: ' + err.message, 'error');
    }
});
</script>
@endpush
