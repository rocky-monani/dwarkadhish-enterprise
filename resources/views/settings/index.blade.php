@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">

    <!-- System Status & Connection Card -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">Automation service health</h3>

            <button type="button" id="btn_test_conn" class="btn btn-outline" style="height: 32px; padding: 0 12px; font-size: 12px;">
                Test connection
            </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            <div style="padding: 16px; background-color: var(--bg-color); border-radius: 8px; border: 1px solid var(--border-color);">
                <div style="font-size: 12px; color: var(--text-muted);">Microservice endpoint</div>
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: 500; color: var(--text-main); margin-top: 4px;">http://127.0.0.1:3000</div>
            </div>

            <div style="padding: 16px; background-color: var(--bg-color); border-radius: 8px; border: 1px solid var(--border-color);">
                <div style="font-size: 12px; color: var(--text-muted);">Connection status</div>
                <div id="conn_status_text" style="font-size: 14px; font-weight: 500; color: var(--primary-dark); margin-top: 4px;">
                    Active (online)
                </div>
            </div>

            <div style="padding: 16px; background-color: var(--bg-color); border-radius: 8px; border: 1px solid var(--border-color);">
                <div style="font-size: 12px; color: var(--text-muted);">Chrome integration</div>
                <div style="font-size: 14px; font-weight: 500; color: var(--text-main); margin-top: 4px;">
                    CDP port 9222 / persistent
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Settings Configuration Form -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">Google Chrome &amp; microservice configuration</h3>
        </div>

        <form action="{{ route('settings.update') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="brave_executable_path" class="form-label">Google Chrome executable path (Windows)</label>
                <input type="text" id="brave_executable_path" name="brave_executable_path" class="form-control" style="font-family: 'JetBrains Mono', monospace;" value="{{ old('brave_executable_path', $settings['brave_executable_path'] ?? 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe') }}" required>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Path to installed Google Chrome binary on Windows PC.</div>
            </div>

            <div class="form-group">
                <label for="brave_user_data_dir" class="form-label">Google Chrome user data directory</label>
                <input type="text" id="brave_user_data_dir" name="brave_user_data_dir" class="form-control" style="font-family: 'JetBrains Mono', monospace;" value="{{ old('brave_user_data_dir', $settings['brave_user_data_dir'] ?? '%LOCALAPPDATA%\\Google\\Chrome\\User Data') }}" required>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Directory containing your authenticated Google Chrome browser profile.</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="brave_profile_name" class="form-label">Profile name</label>
                    <input type="text" id="brave_profile_name" name="brave_profile_name" class="form-control" value="{{ old('brave_profile_name', $settings['brave_profile_name'] ?? 'Default') }}" required>
                </div>

                <div class="form-group">
                    <label for="automation_service_url" class="form-label">Automation service URL</label>
                    <input type="text" id="automation_service_url" name="automation_service_url" class="form-control" style="font-family: 'JetBrains Mono', monospace;" value="{{ old('automation_service_url', $settings['automation_service_url'] ?? 'http://127.0.0.1:3000') }}" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
                <button type="submit" class="btn btn-primary">
                    Save configuration
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
            statusText.innerText = 'Active (online)';
            statusText.style.color = 'var(--primary-dark)';
            showToast('Playwright automation microservice is active.', 'success');
        } else {
            statusText.innerText = 'Offline';
            statusText.style.color = 'var(--accent-red)';
            showToast('Could not connect to automation microservice on port 3000.', 'error');
        }
    } catch (err) {
        statusText.innerText = 'Offline';
        statusText.style.color = 'var(--accent-red)';
        showToast('Error: ' + err.message, 'error');
    }
});
</script>
@endpush
