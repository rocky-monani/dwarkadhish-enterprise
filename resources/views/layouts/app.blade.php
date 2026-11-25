<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Dwarkadhish Enterprise ERP</title>

    <!-- System Fonts + Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Ultra-Fast Clean High-Contrast Palette */
            --primary: #16a34a;
            --primary-dark: #15803d;
            --primary-light: #f0fdf4;
            --primary-border: #bbf7d0;
            
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #16a34a;
            
            --text-main: #0f172a;
            --text-secondary: #334155;
            --text-muted: #64748b;
            
            --border-color: #cbd5e1;
            --border-focus: #16a34a;
            
            --accent-amber: #d97706;
            --accent-red: #dc2626;
            --accent-blue: #0284c7;
            
            --radius-sm: 4px;
            --radius-md: 6px;
            --radius-lg: 8px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.4;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        /* Fast Layout Containers */
        .app-layout {
            display: flex;
            width: 100vw;
            min-height: 100vh;
        }

        /* High-Speed Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 1rem 1.15rem;
            border-bottom: 2px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #ffffff;
            background-color: #0b1329;
        }

        .brand-logo-img {
            width: 38px;
            height: 38px;
            object-fit: cover;
            border-radius: 4px;
            border: 2px solid var(--primary);
            background: #ffffff;
            flex-shrink: 0;
        }

        .brand-title {
            font-weight: 900;
            font-size: 1.05rem;
            color: #ffffff;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 0.65rem;
            color: #86efac;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .sidebar-menu {
            padding: 0.75rem 0.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.55rem 0.85rem;
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: var(--radius-sm);
        }

        .nav-item-link:hover {
            color: #ffffff;
            background-color: var(--sidebar-hover);
        }

        .nav-item-link.active {
            color: #ffffff;
            background-color: var(--primary);
            font-weight: 800;
        }

        .nav-item-link svg {
            width: 18px;
            height: 18px;
            stroke-width: 2.2;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 0.75rem 1rem;
            border-top: 1px solid #1e293b;
            background-color: #090d16;
            font-size: 0.75rem;
        }

        /* Main Shell & Top Bar */
        .main-wrapper {
            margin-left: 250px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: calc(100vw - 250px);
        }

        .top-navbar {
            height: 56px;
            background-color: #ffffff;
            border-bottom: 2px solid var(--primary);
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .page-header-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .top-navbar-actions {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .btn-header-action {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.8rem;
            font-size: 0.82rem;
            font-weight: 700;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            background-color: #ffffff;
            color: var(--text-secondary);
            cursor: pointer;
            text-decoration: none;
        }

        .btn-header-action:hover {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            border-color: var(--primary);
        }

        .user-profile-tag {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-secondary);
            padding-left: 0.85rem;
            border-left: 1px solid var(--border-color);
        }

        .user-avatar {
            width: 30px;
            height: 30px;
            background-color: var(--primary);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.8rem;
        }

        /* Fast Content Container */
        .content-area {
            padding: 1.25rem 1.5rem;
            flex-grow: 1;
        }

        /* Clean High-Speed Cards */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            padding: 1.25rem;
            margin-bottom: 1.15rem;
        }

        .card-header {
            margin-bottom: 1rem;
            padding-bottom: 0.65rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Fast Standard Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.55rem 1.1rem;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: var(--radius-sm);
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            outline: none;
            white-space: nowrap;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-success {
            background-color: var(--primary);
            color: #ffffff;
        }

        .btn-success:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: #64748b;
            color: #ffffff;
        }

        .btn-secondary:hover {
            background-color: #475569;
        }

        .btn-outline {
            background-color: #ffffff;
            border-color: #cbd5e1;
            color: #334155;
        }

        .btn-outline:hover {
            background-color: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .btn-warning {
            background-color: var(--accent-amber);
            color: #ffffff;
        }

        .btn-danger {
            background-color: var(--accent-red);
            color: #ffffff;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Fast Compact Form Controls */
        .form-group {
            margin-bottom: 0.9rem;
        }

        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .form-control {
            width: 100%;
            padding: 0.55rem 0.75rem;
            font-size: 0.9rem;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-sm);
            background-color: #ffffff;
            color: var(--text-main);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.2);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.9rem;
        }

        /* Status Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 800;
        }

        .badge-open {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            border: 1px solid var(--primary-border);
        }

        .badge-closed {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .badge-info {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        /* High-Density Data Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        th {
            background-color: #f8fafc;
            color: var(--text-secondary);
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.6rem 0.75rem;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }

        td {
            padding: 0.6rem 0.75rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
            vertical-align: middle;
        }

        tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Toast Notifications */
        #toast_container {
            position: fixed;
            bottom: 1.25rem;
            right: 1.25rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            max-width: 360px;
        }

        .toast {
            background-color: #ffffff;
            color: var(--text-main);
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-left: 4px solid var(--primary);
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .toast.success { border-left-color: var(--primary); }
        .toast.error { border-left-color: var(--accent-red); }
        .toast.warning { border-left-color: var(--accent-amber); }

        /* Modal Overlays */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(15, 23, 42, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-card {
            background-color: #ffffff;
            width: 90%;
            max-width: 480px;
            border-radius: var(--radius-md);
            padding: 1.35rem;
            border: 1px solid var(--border-color);
        }

        .modal-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }

        .modal-body {
            font-size: 0.88rem;
            color: var(--text-secondary);
            margin-bottom: 1.25rem;
            line-height: 1.4;
        }

        .modal-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.65rem;
        }
    </style>
    @stack('styles')
</head>
<body>

@auth
    <!-- LOGGED IN MAIN APPLICATION SHELL -->
    <div class="app-layout">
        <!-- Left Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <!-- Top Navbar -->
            <header class="top-navbar">
                <h1 class="page-header-title">@yield('title', 'Service Operations')</h1>

                <div class="top-navbar-actions">
                    @if(!auth()->user()?->isViewer())
                        <button type="button" onclick="startBatchFetchPending('ALL')" class="btn-header-action"
                            style="background-color: var(--primary); color: #ffffff; border-color: var(--primary);"
                            title="Fetch Connect details and screenshots for all open pending complaints">
                            ⚡ Fetch Pending Details
                        </button>
                        <button type="button" onclick="openBatchFetchModal()" class="btn-header-action"
                            title="Fetch screenshots for range of complaint numbers">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                                <circle cx="12" cy="13" r="4" />
                            </svg>
                            📷 Fetch Range
                        </button>
                        <button type="button" onclick="openBatteryConnectLoginBrowser()" class="btn-header-action"
                            title="Open Google Chrome session for Battery Connect">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                <polyline points="15 3 21 3 21 9" />
                                <line x1="10" y1="14" x2="21" y2="3" />
                            </svg>
                            🔑 Battery Connect Browser
                        </button>
                    @endif

                    <div class="user-profile-tag">
                        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'O', 0, 1)) }}</div>
                        <div style="display: flex; flex-direction: column; line-height: 1.1;">
                            <span style="font-weight: 800;">{{ auth()->user()->name ?? 'User' }}</span>
                            <span style="font-size: 0.68rem; color: var(--primary); font-weight: 800; text-transform: uppercase;">
                                {{ auth()->user()->role ?? 'operator' }}
                            </span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" style="display:inline; margin-left: 0.35rem;">
                            @csrf
                            <button type="submit" class="btn-header-action" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="content-area">
                @if(session('success'))
                    <div class="card" style="border-left: 4px solid var(--primary); padding: 0.85rem 1.15rem; margin-bottom: 1rem; background-color: var(--primary-light);">
                        <div style="font-weight: 700; color: var(--primary-dark); display: flex; align-items: center; gap: 0.5rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="card" style="border-left: 4px solid var(--accent-red); padding: 0.85rem 1.15rem; margin-bottom: 1rem; background-color: #fef2f2;">
                        <div style="font-weight: 700; color: #991b1b; display: flex; align-items: center; gap: 0.5rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
@else
    <!-- GUEST / UNAUTHENTICATED STANDALONE LOGIN PAGE (NO SIDEBAR, NO TOPBAR) -->
    <div style="min-height: 100vh; width: 100vw; display: flex; align-items: center; justify-content: center; background-color: var(--bg-color); padding: 1rem;">
        @yield('content')
    </div>
@endauth

<!-- Toast Container -->
<div id="toast_container"></div>

<!-- Generic Confirm Modal Overlay -->
<div id="app_modal_overlay" class="modal-overlay">
    <div class="modal-card">
        <h3 id="app_modal_title" class="modal-title">Confirm Action</h3>
        <p id="app_modal_body" class="modal-body">Are you sure you want to proceed?</p>
        <div class="modal-actions">
            <button type="button" id="app_modal_cancel" class="btn btn-outline">Cancel</button>
            <button type="button" id="app_modal_confirm" class="btn btn-primary">Confirm</button>
        </div>
    </div>
</div>

<!-- Batch Fetch Screenshots Modal -->
<div id="batch_fetch_modal_overlay" class="modal-overlay" style="z-index: 1050;">
    <div class="modal-card" style="max-width: 640px; width: 95%;">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            <h3 class="modal-title" style="margin-bottom: 0; display: flex; align-items: center; gap: 0.5rem; color: var(--primary-dark);">
                📷 Fetch Battery Connect Screenshots
            </h3>
            <button type="button" onclick="closeBatchFetchModal()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <div id="batch_fetch_form_container">
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem;">
                Enter a range of Complaint Numbers below to automatically connect to Battery Connect portal and capture screenshots in bulk.
            </p>

            <form id="batch_fetch_form" onsubmit="handleBatchFetchSubmit(event)" style="display: flex; flex-direction: column; gap: 0.85rem;">
                <div class="form-row">
                    <div>
                        <label for="bf_battery_type" class="form-label">Battery Category</label>
                        <select id="bf_battery_type" class="form-control" style="font-weight: 700;">
                            <option value="ALL">All Categories (2W & 4W)</option>
                            <option value="TW">🏍️ 2W (Two-Wheeler Only)</option>
                            <option value="FW">🚗 4W (Four-Wheeler / Heavy Only)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label for="bf_from_complaint" class="form-label">From Complaint Number *</label>
                        <input type="text" id="bf_from_complaint" class="form-control" placeholder="e.g. 1001" autocomplete="off">
                    </div>
                    <div>
                        <label for="bf_to_complaint" class="form-label">To Complaint Number *</label>
                        <input type="text" id="bf_to_complaint" class="form-control" placeholder="e.g. 1005" autocomplete="off">
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="bf_missing_only" style="width: 16px; height: 16px; cursor: pointer;">
                    <label for="bf_missing_only" style="font-size: 0.85rem; font-weight: 600; cursor: pointer; color: var(--text-main);">
                        Only fetch for complaints missing a screenshot
                    </label>
                </div>

                <!-- QUICK BULK PENDING ACTION BUTTONS -->
                <div style="background-color: #f8fafc; padding: 0.75rem; border-radius: 4px; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 0.4rem;">
                    <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">⚡ Quick Pending Bulk Actions:</div>
                    <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                        <button type="button" onclick="startBatchFetchPending('TW')" class="btn" style="background-color: #d97706; color: #ffffff; font-weight: 700; font-size: 0.8rem; padding: 0.35rem 0.75rem;" title="Fetch details for all open 2W pending complaints">
                            🏍️ Fetch 2W Pending
                        </button>
                        <button type="button" onclick="startBatchFetchPending('FW')" class="btn btn-success" style="font-weight: 700; font-size: 0.8rem; padding: 0.35rem 0.75rem;" title="Fetch details for all open 4W pending complaints">
                            🚗 Fetch 4W Pending
                        </button>
                        <button type="button" onclick="startBatchFetchPending('ALL')" class="btn btn-primary" style="font-weight: 700; font-size: 0.8rem; padding: 0.35rem 0.75rem;" title="Fetch details for all open pending complaints">
                            ⚡ Fetch All Pending
                        </button>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.65rem; margin-top: 0.25rem;">
                    <button type="button" onclick="closeBatchFetchModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" id="btn_start_batch_fetch" class="btn btn-success" style="font-weight: 700;">
                        🚀 Fetch Range Screenshots
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Progress Container -->
        <div id="batch_fetch_progress_container" style="display: none;">
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem;">
                    <span id="bf_progress_label">Preparing batch fetch...</span>
                    <span id="bf_progress_percentage">0%</span>
                </div>
                <div style="width: 100%; height: 8px; background-color: #e2e8f0; border-radius: 4px; overflow: hidden;">
                    <div id="bf_progress_bar" style="width: 0%; height: 100%; background-color: var(--primary); transition: width 0.2s linear;"></div>
                </div>
                <div id="bf_current_status" style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.35rem; font-family: 'JetBrains Mono', monospace;">
                    Initializing process...
                </div>
            </div>

            <!-- Log Table -->
            <div style="max-height: 240px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 4px; margin-bottom: 1rem;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted);">
                            <th style="padding: 0.4rem 0.65rem;">Complaint #</th>
                            <th style="padding: 0.4rem 0.65rem;">Serial #</th>
                            <th style="padding: 0.4rem 0.65rem; text-align: center;">Status</th>
                            <th style="padding: 0.4rem 0.65rem; text-align: right;">Action / Result</th>
                        </tr>
                    </thead>
                    <tbody id="bf_results_table_body">
                        <!-- Dynamic rows -->
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <button type="button" id="btn_stop_batch_fetch" onclick="stopBatchFetchProcess()" class="btn btn-warning" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                    ⏹️ Stop Process
                </button>
                <button type="button" id="btn_close_batch_progress" onclick="closeBatchFetchModal(true)" class="btn btn-primary" style="display: none; font-weight: 700;">
                    Done / Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Toast Notification Utility
function showToast(message, type = 'success', duration = 4000) {
    const container = document.getElementById('toast_container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span>${message}</span>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, duration);
}

// Accessible Modal Manager
function showConfirmModal(title, bodyText, onConfirm) {
    const overlay = document.getElementById('app_modal_overlay');
    const modalTitle = document.getElementById('app_modal_title');
    const modalBody = document.getElementById('app_modal_body');
    const btnCancel = document.getElementById('app_modal_cancel');
    const btnConfirm = document.getElementById('app_modal_confirm');

    modalTitle.textContent = title || 'Confirm Action';
    modalBody.textContent = bodyText || 'Are you sure?';

    overlay.classList.add('active');

    function closeModal() {
        overlay.classList.remove('active');
        btnCancel.removeEventListener('click', closeModal);
        btnConfirm.removeEventListener('click', handleConfirm);
    }

    function handleConfirm() {
        closeModal();
        if (typeof onConfirm === 'function') onConfirm();
    }

    btnCancel.addEventListener('click', closeModal);
    btnConfirm.addEventListener('click', handleConfirm);
}

// Global safe JSON fetch helper
async function safeFetchJson(url, options = {}) {
    const defaultHeaders = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    };
    if (options.body && typeof options.body === 'string') {
        defaultHeaders['Content-Type'] = 'application/json';
    }
    options.headers = { ...defaultHeaders, ...options.headers };

    const res = await fetch(url, options);
    const contentType = res.headers.get('content-type') || '';

    if (!contentType.includes('application/json')) {
        if (res.status === 419) {
            throw new Error('Session expired. Please refresh the page.');
        }
        throw new Error(`Server returned status ${res.status}. Ensure backend service is active.`);
    }

    const data = await res.json();
    if (!res.ok) {
        throw new Error(data.message || data.error || `HTTP ${res.status} Error`);
    }
    return data;
}

// Open Battery Connect Browser
function openBatteryConnectLoginBrowser() {
    safeFetchJson("{{ route('complaints.open_login_browser') }}", { method: 'POST' })
        .then(data => {
            showToast('🟢 Google Chrome opened for Battery Connect session.', 'success');
        })
        .catch(err => {
            showToast('❌ Failed to launch browser: ' + err.message, 'error');
        });
}

// Batch fetch controller
let isBatchFetchRunning = false;
let batchFetchShouldReloadPage = false;

function openBatchFetchModal(fromNo = '', toNo = '', category = 'ALL') {
    const overlay = document.getElementById('batch_fetch_modal_overlay');
    if (!overlay) return;

    if (fromNo) document.getElementById('bf_from_complaint').value = fromNo;
    if (toNo) document.getElementById('bf_to_complaint').value = toNo;

    const catSelect = document.getElementById('bf_battery_type');
    if (catSelect && category) {
        catSelect.value = category.toUpperCase();
    }

    document.getElementById('batch_fetch_form_container').style.display = 'block';
    document.getElementById('batch_fetch_progress_container').style.display = 'none';
    document.getElementById('btn_start_batch_fetch').disabled = false;
    document.getElementById('btn_stop_batch_fetch').style.display = 'inline-flex';
    document.getElementById('btn_close_batch_progress').style.display = 'none';

    overlay.classList.add('active');
}

function closeBatchFetchModal(forceReload = false) {
    const overlay = document.getElementById('batch_fetch_modal_overlay');
    if (overlay) overlay.classList.remove('active');

    if (isBatchFetchRunning) {
        isBatchFetchRunning = false;
    }

    if (forceReload || batchFetchShouldReloadPage) {
        window.location.reload();
    }
}

function stopBatchFetchProcess() {
    isBatchFetchRunning = false;
    document.getElementById('bf_current_status').innerText = 'Process stopped by user.';
    document.getElementById('btn_stop_batch_fetch').style.display = 'none';
    document.getElementById('btn_close_batch_progress').style.display = 'inline-flex';
}

async function startBatchFetchPending(category = 'ALL') {
    openBatchFetchModal('', '', category);
    const missingOnly = document.getElementById('bf_missing_only')?.checked || false;
    const catLabel = category === 'TW' ? '2W (Two-Wheeler)' : (category === 'FW' ? '4W (Four-Wheeler)' : 'All');
    showToast(`🔍 Searching open ${catLabel} pending complaint records...`, 'info');

    try {
        const listData = await safeFetchJson("{{ route('complaints.batch_fetch_pending_list') }}", {
            method: 'POST',
            body: JSON.stringify({
                battery_type: category,
                missing_only: missingOnly
            })
        });

        if (!listData.success || !listData.complaints || listData.complaints.length === 0) {
            showToast(`No open ${catLabel} pending complaints found requiring details.`, 'warning');
            return;
        }

        await executeBatchFetchProcess(listData.complaints);
    } catch (err) {
        showToast('❌ Failed to load pending complaints: ' + err.message, 'error');
    }
}

async function handleBatchFetchSubmit(event) {
    event.preventDefault();

    const fromComp = document.getElementById('bf_from_complaint').value.trim();
    const toComp = document.getElementById('bf_to_complaint').value.trim();
    const missingOnly = document.getElementById('bf_missing_only').checked;
    const batteryType = document.getElementById('bf_battery_type').value;

    if (!fromComp || !toComp) {
        showToast('Please enter both From and To complaint numbers.', 'error');
        return;
    }

    const btnStart = document.getElementById('btn_start_batch_fetch');
    btnStart.disabled = true;
    btnStart.innerText = '🔍 Searching Records...';

    try {
        const listData = await safeFetchJson("{{ route('complaints.batch_fetch_list') }}", {
            method: 'POST',
            body: JSON.stringify({
                from_complaint: fromComp,
                to_complaint: toComp,
                missing_only: missingOnly,
                battery_type: batteryType
            })
        });

        if (!listData.success || !listData.complaints || listData.complaints.length === 0) {
            showToast(`No matching complaints found in range ${fromComp} to ${toComp}.`, 'warning');
            btnStart.disabled = false;
            btnStart.innerText = '🚀 Fetch Range Screenshots';
            return;
        }

        await executeBatchFetchProcess(listData.complaints);
    } catch (err) {
        showToast('❌ Batch process failed: ' + err.message, 'error');
        btnStart.disabled = false;
        btnStart.innerText = '🚀 Fetch Range Screenshots';
    }
}

async function executeBatchFetchProcess(complaints) {
    document.getElementById('batch_fetch_form_container').style.display = 'none';
    document.getElementById('batch_fetch_progress_container').style.display = 'block';

    const totalCount = complaints.length;
    isBatchFetchRunning = true;
    batchFetchShouldReloadPage = true;

    const tableBody = document.getElementById('bf_results_table_body');
    tableBody.innerHTML = '';

    complaints.forEach((item) => {
        const tr = document.createElement('tr');
        tr.id = `bf_row_${item.id}`;
        tr.style.borderBottom = '1px solid var(--border-color)';
        tr.innerHTML = `
            <td style="padding: 0.4rem 0.65rem; font-weight: 800; color: var(--primary-dark);">#${item.complaint_number}</td>
            <td style="padding: 0.4rem 0.65rem; font-family: 'JetBrains Mono', monospace; font-weight: 700;">${item.battery_serial_number}</td>
            <td id="bf_status_${item.id}" style="padding: 0.4rem 0.65rem; text-align: center;">
                <span class="badge badge-pending">⏳ Pending</span>
            </td>
            <td id="bf_action_${item.id}" style="padding: 0.4rem 0.65rem; text-align: right;">
                <span style="font-size: 0.75rem; color: var(--text-muted);">Queued</span>
            </td>
        `;
        tableBody.appendChild(tr);
    });

    let successCount = 0;
    let failCount = 0;

    for (let i = 0; i < totalCount; i++) {
        if (!isBatchFetchRunning) break;

        const item = complaints[i];
        const currentPct = Math.round(((i) / totalCount) * 100);

        document.getElementById('bf_progress_bar').style.width = `${currentPct}%`;
        document.getElementById('bf_progress_percentage').innerText = `${currentPct}%`;
        document.getElementById('bf_progress_label').innerText = `Processing ${i + 1} of ${totalCount} (Complaint #${item.complaint_number})`;
        document.getElementById('bf_current_status').innerText = `Fetching Battery Connect details & screenshot for #${item.complaint_number} (${item.battery_serial_number})...`;

        const statusCell = document.getElementById(`bf_status_${item.id}`);
        const actionCell = document.getElementById(`bf_action_${item.id}`);
        const rowElem = document.getElementById(`bf_row_${item.id}`);

        if (rowElem) rowElem.style.backgroundColor = '#eff6ff';
        if (statusCell) statusCell.innerHTML = `<span class="badge badge-info">⚡ Processing...</span>`;

        try {
            const singleRes = await safeFetchJson("{{ route('complaints.batch_fetch_single') }}", {
                method: 'POST',
                body: JSON.stringify({ id: item.id })
            });

            if (singleRes.success && singleRes.image_path) {
                successCount++;
                if (statusCell) statusCell.innerHTML = `<span class="badge badge-open">🟢 Fetched</span>`;
                if (actionCell) actionCell.innerHTML = `<a href="${singleRes.image_path}" target="_blank" class="btn btn-outline" style="padding: 0.15rem 0.5rem; font-size: 0.75rem; text-decoration: none;">🖼️ View Screenshot</a>`;
            } else {
                failCount++;
                const errMsg = singleRes.message || singleRes.code || 'Failed';
                if (statusCell) statusCell.innerHTML = `<span class="badge badge-closed">🔴 Failed</span>`;
                if (actionCell) actionCell.innerHTML = `<span style="font-size: 0.75rem; color: var(--accent-red);">${errMsg}</span>`;
            }
        } catch (err) {
            failCount++;
            if (statusCell) statusCell.innerHTML = `<span class="badge badge-closed">🔴 Error</span>`;
            if (actionCell) actionCell.innerHTML = `<span style="font-size: 0.75rem; color: var(--accent-red);">${err.message}</span>`;
        }

        if (rowElem) rowElem.style.backgroundColor = 'transparent';
    }

    isBatchFetchRunning = false;
    document.getElementById('bf_progress_bar').style.width = '100%';
    document.getElementById('bf_progress_percentage').innerText = '100%';
    document.getElementById('bf_progress_label').innerText = 'Batch details process completed!';
    document.getElementById('bf_current_status').innerText = `Finished! ${successCount} details & screenshots fetched successfully, ${failCount} failed.`;

    document.getElementById('btn_stop_batch_fetch').style.display = 'none';
    document.getElementById('btn_close_batch_progress').style.display = 'inline-flex';

    showToast(`Batch completed: ${successCount} details & screenshots fetched.`, 'success');
}
</script>
@stack('scripts')
</body>
</html>