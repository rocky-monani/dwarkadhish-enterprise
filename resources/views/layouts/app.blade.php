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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <style>
        :root {
            /* Subtle Professional Slate & Forest Theme */
            --primary: #15803d;
            --primary-dark: #166534;
            --primary-light: #f0fdf4;
            --primary-border: #bbf7d0;
            --amaron-green: #15803d;

            --bg-color: #f4f6f8;
            --card-bg: #ffffff;
            --raised-bg: #eef2f6;

            --sidebar-bg: #141b24;
            --sidebar-hover: #1d2633;
            --sidebar-active: #243040;
            --sidebar-border: rgba(226, 232, 240, 0.08);

            --text-main: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;

            --border-color: rgba(15, 23, 42, 0.08);
            --border-strong: rgba(15, 23, 42, 0.14);
            --border-focus: #15803d;

            --accent-amber: #b45309;
            --accent-red: #b91c1c;
            --accent-blue: #0369a1;

            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-lg: 16px;

            --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 8px 24px rgba(15, 23, 42, 0.07);
            --ease-out: cubic-bezier(0.22, 1, 0.36, 1);
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
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        .tabular {
            font-variant-numeric: tabular-nums;
        }

        /* Layout Shell */
        .app-layout {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 256px;
            background-color: var(--sidebar-bg);
            color: #ededed;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            border-right: 1px solid var(--sidebar-border);
        }

        .sidebar-brand {
            padding: 16px 20px;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #ededed;
        }

        .brand-logo-img {
            width: 36px;
            height: 36px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: #ffffff;
            flex-shrink: 0;
        }

        .brand-text-container {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .brand-title {
            font-weight: 600;
            font-size: 15px;
            color: #ededed;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 12px;
            color: #85858b;
            font-weight: 400;
        }

        .sidebar-menu {
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-section-label {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
            padding: 20px 12px 4px 12px;
            border-top: 1px solid var(--sidebar-border);
            margin-top: 8px;
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            gap: 10px;
            height: 36px;
            padding: 0 12px;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            border-radius: 8px;
            transition: background-color 150ms var(--ease-out), color 150ms var(--ease-out);
        }

        .nav-item-link:hover {
            color: #f1f5f9;
            background-color: var(--sidebar-hover);
        }

        .nav-item-link.active {
            color: #f8fafc;
            background-color: var(--sidebar-active);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        .nav-item-link.active svg {
            color: #4ade80;
        }

        .nav-item-link svg {
            width: 16px;
            height: 16px;
            stroke-width: 1.5;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--sidebar-border);
            font-size: 12px;
            color: #85858b;
        }

        /* Main Shell & Top Bar */
        .main-wrapper {
            margin-left: 256px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: calc(100% - 256px);
        }

        .top-navbar {
            height: 56px;
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .page-header-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-main);
        }

        .top-navbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-header-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 32px;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            border: 1px solid var(--border-strong);
            background-color: #ffffff;
            color: var(--text-secondary);
            cursor: pointer;
            text-decoration: none;
            transition: background-color 150ms var(--ease-out), color 150ms var(--ease-out), border-color 150ms var(--ease-out);
        }

        .btn-header-action:hover {
            background-color: var(--raised-bg);
            color: var(--text-main);
        }

        .btn-header-action svg {
            width: 16px;
            height: 16px;
            stroke-width: 1.5;
            flex-shrink: 0;
        }

        .user-profile-tag {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            padding-left: 12px;
            margin-left: 4px;
            border-left: 1px solid var(--border-color);
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            background-color: var(--raised-bg);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            font-size: 12px;
        }

        /* Content Container */
        .content-area {
            padding: 24px;
            flex-grow: 1;
            max-width: 1560px;
            width: 100%;
        }

        /* Cards */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header {
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 36px;
            padding: 0 16px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            outline: none;
            white-space: nowrap;
            transition: background-color 150ms var(--ease-out), border-color 150ms var(--ease-out), color 150ms var(--ease-out), transform 150ms var(--ease-out);
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        .btn-primary,
        .btn-success {
            background-color: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover,
        .btn-success:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: var(--raised-bg);
            color: var(--text-secondary);
            border-color: var(--border-strong);
        }

        .btn-secondary:hover {
            background-color: #e4e4e7;
            color: var(--text-main);
        }

        .btn-outline {
            background-color: #ffffff;
            border-color: var(--border-strong);
            color: var(--text-main);
        }

        .btn-outline:hover {
            background-color: var(--raised-bg);
        }

        .btn-warning {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }

        .btn-warning:hover {
            background-color: #fde68a;
        }

        .btn-danger {
            background-color: var(--accent-red);
            color: #ffffff;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        .btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            height: 36px;
            padding: 0 12px;
            font-size: 14px;
            border: 1px solid var(--border-strong);
            border-radius: 8px;
            background-color: #ffffff;
            color: var(--text-main);
            font-family: inherit;
            transition: border-color 150ms var(--ease-out), box-shadow 150ms var(--ease-out);
        }

        textarea.form-control {
            height: auto;
            padding: 8px 12px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.16);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        /* Status Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
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
            background-color: #f4f4f5;
            color: #525252;
            border: 1px solid var(--border-strong);
        }

        /* Data Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            font-variant-numeric: tabular-nums;
        }

        th {
            background-color: var(--bg-color);
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 500;
            padding: 10px 16px;
            border-bottom: 1px solid var(--border-strong);
            text-align: left;
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
            vertical-align: middle;
        }

        tbody tr:hover {
            background-color: #fafafa;
        }

        /* DataTables Theme Overrides */
        .dataTables_wrapper {
            padding: 0;
            font-size: 13px;
            color: var(--text-main);
        }

        .dataTables_wrapper .dt-top,
        .dataTables_wrapper .dt-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 12px 20px;
            background-color: #ffffff;
        }

        .dataTables_wrapper .dt-top {
            border-bottom: 1px solid var(--border-color);
        }

        .dataTables_wrapper .dt-top:empty,
        .dataTables_wrapper .dt-bottom:empty {
            display: none;
        }

        .dataTables_wrapper .dt-bottom {
            border-top: 1px solid var(--border-color);
        }

        .dataTables_wrapper .dt-scroll {
            width: 100%;
            overflow-x: auto;
        }

        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            float: none !important;
            text-align: left !important;
            margin: 0 !important;
            padding: 0 !important;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_length label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            font-weight: 500;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            height: 32px;
            padding: 0 10px;
            border: 1px solid var(--border-strong);
            border-radius: 8px;
            background-color: #ffffff;
            color: var(--text-main);
            font-family: inherit;
            font-size: 13px;
            margin: 0 !important;
        }

        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .dataTables_wrapper table.dataTable {
            border-collapse: collapse !important;
            border-bottom: none !important;
            width: 100% !important;
            margin: 0 !important;
        }

        .dataTables_wrapper table.dataTable thead th {
            background-color: var(--bg-color);
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 500;
            padding: 11px 14px !important;
            border-bottom: 1px solid var(--border-strong) !important;
            white-space: nowrap;
        }

        .dataTables_wrapper table.dataTable thead th.sorting,
        .dataTables_wrapper table.dataTable thead th.sorting_asc,
        .dataTables_wrapper table.dataTable thead th.sorting_desc {
            padding-right: 26px !important;
        }

        .dataTables_wrapper table.dataTable tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .dataTables_wrapper table.dataTable tbody tr:last-child td {
            border-bottom: none;
        }

        .dataTables_wrapper table.dataTable.no-footer {
            border-bottom: none;
        }

        .dataTables_wrapper .dataTables_paginate {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 6px !important;
            border: 1px solid var(--border-color) !important;
            padding: 4px 10px !important;
            margin: 0 !important;
            font-size: 12px !important;
            color: var(--text-secondary) !important;
            background: #ffffff !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--primary) !important;
            color: #ffffff !important;
            border-color: var(--primary) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
            background: var(--raised-bg) !important;
            color: var(--text-main) !important;
            border-color: var(--border-strong) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.45;
            cursor: default;
        }

        /* Toast Notifications */
        #toast_container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: 360px;
        }

        .toast {
            background-color: #ffffff;
            color: var(--text-main);
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            border-left: 3px solid var(--primary);
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .toast.success { border-left-color: var(--primary); }
        .toast.error { border-left-color: var(--accent-red); }
        .toast.warning { border-left-color: var(--accent-amber); }

        /* Modal Overlays */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(15, 15, 16, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 180ms var(--ease-out);
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
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }

        .modal-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .modal-body {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .modal-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
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
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                            </svg>
                            Fetch pending
                        </button>
                        <button type="button" onclick="openBatchFetchModal()" class="btn-header-action"
                            title="Fetch screenshots for range of complaint numbers">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                                <circle cx="12" cy="13" r="4" />
                            </svg>
                            Fetch range
                        </button>
                        <button type="button" onclick="openBatteryConnectLoginBrowser()" class="btn-header-action"
                            title="Open Google Chrome session for Battery Connect">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                <polyline points="15 3 21 3 21 9" />
                                <line x1="10" y1="14" x2="21" y2="3" />
                            </svg>
                            Battery Connect
                        </button>
                    @endif

                    <div class="user-profile-tag">
                        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'O', 0, 1)) }}</div>
                        <div style="display: flex; flex-direction: column; line-height: 1.2;">
                            <span style="font-weight: 500; color: var(--text-main);">{{ auth()->user()->name ?? 'User' }}</span>
                            <span style="font-size: 12px; color: var(--text-muted); font-weight: 400;">
                                {{ ucfirst(auth()->user()->role ?? 'operator') }}
                            </span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" style="display:inline; margin-left: 8px;">
                            @csrf
                            <button type="submit" class="btn-header-action" style="height: 28px; padding: 0 10px; font-size: 12px;">Log out</button>
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

                <!-- Quick Pending Bulk Actions -->
                <div style="background-color: var(--bg-color); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 8px;">
                    <div style="font-size: 12px; font-weight: 500; color: var(--text-muted);">Quick pending bulk actions</div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="button" onclick="startBatchFetchPending('TW')" class="btn btn-warning" style="height: 32px; padding: 0 12px; font-size: 12px;" title="Fetch details for all open 2W pending complaints">
                            Fetch 2W pending
                        </button>
                        <button type="button" onclick="startBatchFetchPending('FW')" class="btn btn-primary" style="height: 32px; padding: 0 12px; font-size: 12px;" title="Fetch details for all open 4W pending complaints">
                            Fetch 4W pending
                        </button>
                        <button type="button" onclick="startBatchFetchPending('ALL')" class="btn btn-outline" style="height: 32px; padding: 0 12px; font-size: 12px;" title="Fetch details for all open pending complaints">
                            Fetch all pending
                        </button>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 4px;">
                    <button type="button" onclick="closeBatchFetchModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" id="btn_start_batch_fetch" class="btn btn-primary">
                        Fetch range screenshots
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Progress Container -->
        <div id="batch_fetch_progress_container" style="display: none;">
            <div style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 500; margin-bottom: 6px;">
                    <span id="bf_progress_label">Preparing batch fetch...</span>
                    <span id="bf_progress_percentage" class="tabular">0%</span>
                </div>
                <div style="width: 100%; height: 8px; background-color: var(--raised-bg); border-radius: 4px; overflow: hidden;">
                    <div id="bf_progress_bar" style="width: 0%; height: 100%; background-color: var(--primary); transition: width 0.2s linear;"></div>
                </div>
                <div id="bf_current_status" style="font-size: 12px; color: var(--text-muted); margin-top: 6px; font-family: 'JetBrains Mono', monospace;">
                    Initializing process...
                </div>
            </div>

            <!-- Log Table -->
            <div style="max-height: 260px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 16px;">
                <table id="batch_progress_table">
                    <thead>
                        <tr>
                            <th>Complaint #</th>
                            <th>Serial #</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: right;">Action / Result</th>
                        </tr>
                    </thead>
                    <tbody id="bf_results_table_body">
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <button type="button" id="btn_stop_batch_fetch" onclick="stopBatchFetchProcess()" class="btn btn-warning" style="height: 32px; padding: 0 12px; font-size: 12px;">
                    Stop process
                </button>
                <button type="button" id="btn_close_batch_progress" onclick="closeBatchFetchModal(true)" class="btn btn-primary" style="display: none;">
                    Done
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.DataTable) {
        jQuery('.js-datatable').each(function() {
            const $tbl = jQuery(this);
            if (!jQuery.fn.DataTable.isDataTable(this)) {
                const usePaging = $tbl.data('dt-paging') === true;
                $tbl.DataTable({
                    dom: usePaging ? '<"dt-top"lf><"dt-scroll"t><"dt-bottom"ip>' : '<"dt-top"f><"dt-scroll"t>',
                    paging: usePaging,
                    info: usePaging,
                    autoWidth: false,
                    pageLength: 25,
                    order: [],
                    language: {
                        search: 'Filter:',
                        emptyTable: $tbl.data('dt-empty') || 'No records found.'
                    }
                });
            }
        });
    }
});
</script>
@stack('scripts')
</body>
</html>