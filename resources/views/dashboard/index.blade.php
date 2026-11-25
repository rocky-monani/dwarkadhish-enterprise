@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div style="display: flex; flex-direction: column; gap: 1.25rem;">

    <!-- TOP EXECUTIVE HEADER BANNER -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 1.35rem 1.6rem; border-radius: var(--radius-md); border-left: 5px solid var(--primary); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span style="background-color: var(--primary); color: #ffffff; font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.55rem; border-radius: 4px; text-transform: uppercase;">
                    ⚡ AMARON AUTHORIZED DISTRIBUTOR
                </span>
                <span style="color: #94a3b8; font-size: 0.78rem; font-weight: 600;">Service Center Code: 10013504S05</span>
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 900; margin: 0; color: #ffffff; letter-spacing: -0.02em;">
                DWARKADHISH AUTOMOBILES
            </h1>
            <p style="margin: 0.2rem 0 0 0; color: #cbd5e1; font-size: 0.88rem;">
                Battery Warranty & Complaint Operations ERP
            </p>
        </div>

        <!-- PRIMARY QUICK ACTION BUTTONS -->
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.65rem;">
            @if(!auth()->user()?->isViewer())
            <a href="{{ route('complaints.create', ['battery_type' => 'TW']) }}" class="btn btn-primary" style="font-weight: 800; font-size: 0.88rem; padding: 0.6rem 1.15rem;">
                🏍️ New TW Complaint
            </a>
            <a href="{{ route('complaints.create', ['battery_type' => 'FW']) }}" class="btn" style="background-color: #0284c7; color: #ffffff; font-weight: 800; font-size: 0.88rem; padding: 0.6rem 1.15rem;">
                🚗 New FW Complaint
            </a>
            @endif

            @if(auth()->user() && auth()->user()->isAdmin())
            <a href="{{ route('excel.import_view') }}" class="btn btn-outline" style="background-color: rgba(255, 255, 255, 0.1); color: #ffffff; border-color: rgba(255, 255, 255, 0.2); font-weight: 700; font-size: 0.85rem; padding: 0.6rem 1rem;">
                📥 IMPORT EXCEL DATA
            </a>
            @endif

            <a href="{{ route('excel.export_view') }}" class="btn btn-outline" style="background-color: rgba(255, 255, 255, 0.1); color: #ffffff; border-color: rgba(255, 255, 255, 0.2); font-weight: 700; font-size: 0.85rem; padding: 0.6rem 1rem;">
                📤 EXPORT EXCEL SHEET
            </a>
        </div>
    </div>

    <!-- METRICS KPI GRID (5 CARDS) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
        
        <!-- Metric 1: Total Complaints -->
        <a href="{{ route('complaints.index') }}" class="card" style="margin-bottom: 0; padding: 1rem 1.15rem; border-top: 3px solid var(--primary); text-decoration: none; color: inherit; display: block;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Total Complaints</div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: var(--text-main); margin-top: 0.15rem; line-height: 1;">{{ $totalComplaints }}</div>
                </div>
                <span style="font-size: 1.3rem;">📁</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.4rem;">Master record count</div>
        </a>

        <!-- Metric 2: Today Total Inward -->
        <a href="{{ route('complaints.index', ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" class="card" style="margin-bottom: 0; padding: 1rem 1.15rem; border-top: 3px solid #16a34a; text-decoration: none; color: inherit; display: block;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 0.72rem; font-weight: 800; color: #15803d; text-transform: uppercase;">Today Total Inward</div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: #16a34a; margin-top: 0.15rem; line-height: 1;">{{ $todaysComplaints }}</div>
                </div>
                <span style="font-size: 1.3rem;">📅</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.4rem;">Entries added today</div>
        </a>

        <!-- Metric 3: Today TW -->
        <a href="{{ route('complaints.index', ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d'), 'battery_type' => 'TW']) }}" class="card" style="margin-bottom: 0; padding: 1rem 1.15rem; border-top: 3px solid #15803d; text-decoration: none; color: inherit; display: block;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 0.72rem; font-weight: 800; color: #15803d; text-transform: uppercase;">Today TW (2W)</div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: #15803d; margin-top: 0.15rem; line-height: 1;">{{ $todaysTwCount }}</div>
                </div>
                <span style="font-size: 1.3rem;">🛵</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.4rem;">Two wheeler entries</div>
        </a>

        <!-- Metric 4: Today FW -->
        <a href="{{ route('complaints.index', ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d'), 'battery_type' => 'FW']) }}" class="card" style="margin-bottom: 0; padding: 1rem 1.15rem; border-top: 3px solid #0284c7; text-decoration: none; color: inherit; display: block;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 0.72rem; font-weight: 800; color: #0369a1; text-transform: uppercase;">Today 4W (4W)</div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: #0284c7; margin-top: 0.15rem; line-height: 1;">{{ $todaysFwCount }}</div>
                </div>
                <span style="font-size: 1.3rem;">🚗</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.4rem;">Four wheeler entries</div>
        </a>

        <!-- Metric 5: Unprinted Pending -->
        <a href="{{ route('complaints.pending_cases') }}" class="card" style="margin-bottom: 0; padding: 1rem 1.15rem; border-top: 3px solid var(--accent-amber); text-decoration: none; color: inherit; display: block;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 0.72rem; font-weight: 800; color: #b45309; text-transform: uppercase;">Pending Unprinted</div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: #d97706; margin-top: 0.15rem; line-height: 1;">{{ $pendingPrintCount }}</div>
                </div>
                <span style="font-size: 1.3rem;">📋</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.4rem;">Unprinted pending cases</div>
        </a>

    </div>

    <!-- QUICK WORKFLOW STRIP -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem;">
        
        <a href="{{ route('complaints.create') }}" style="background: #ffffff; border: 1.5px solid var(--primary-border); border-radius: var(--radius-sm); padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--text-main);">
            <div style="background-color: var(--primary-light); color: var(--primary-dark); width: 36px; height: 36px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800;">
                ➕
            </div>
            <div>
                <div style="font-weight: 800; font-size: 0.88rem;">New Complaint</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Inward Entry Form</div>
            </div>
        </a>

        <a href="{{ route('complaints.pending_cases') }}" style="background: #ffffff; border: 1.5px solid var(--border-color); border-radius: var(--radius-sm); padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--text-main);">
            <div style="background-color: #fef3c7; color: #d97706; width: 36px; height: 36px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800;">
                📋
            </div>
            <div>
                <div style="font-weight: 800; font-size: 0.88rem;">Pending Cases Queue</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $pendingPrintCount }} Unprinted Entries</div>
            </div>
        </a>

        <a href="{{ route('complaints.close_case_view') }}" style="background: #ffffff; border: 1.5px solid var(--border-color); border-radius: var(--radius-sm); padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--text-main);">
            <div style="background-color: #fee2e2; color: #dc2626; width: 36px; height: 36px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800;">
                🔒
            </div>
            <div>
                <div style="font-weight: 800; font-size: 0.88rem;">Close Complaint Case</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Live Search & Settlement</div>
            </div>
        </a>

        <a href="{{ route('complaints.index') }}" style="background: #ffffff; border: 1.5px solid var(--border-color); border-radius: var(--radius-sm); padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--text-main);">
            <div style="background-color: #e0f2fe; color: #0284c7; width: 36px; height: 36px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800;">
                📁
            </div>
            <div>
                <div style="font-weight: 800; font-size: 0.88rem;">All Complaints Master</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $totalComplaints }} Database Records</div>
            </div>
        </a>

    </div>

    <!-- MAIN DASHBOARD 2-COLUMN OPERATIONAL LAYOUT -->
    <div style="display: grid; grid-template-columns: 2.3fr 1fr; gap: 1.15rem;">
        
        <!-- Left Column: Recent Complaints Feed Table -->
        <div class="card" style="margin-bottom: 0; padding: 0; overflow: hidden;">
            <div class="card-header" style="padding: 0.85rem 1.15rem; margin-bottom: 0; background-color: #f8fafc;">
                <h3 class="card-title" style="font-size: 0.95rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Recent Inward Feed
                </h3>
                <a href="{{ route('complaints.index') }}" class="btn btn-outline" style="padding: 0.3rem 0.65rem; font-size: 0.78rem;">View All →</a>
            </div>

            @if($recentComplaints->isEmpty())
                <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
                    <div style="font-size: 2rem; margin-bottom: 0.35rem;">🛵</div>
                    <p style="font-weight: 800; font-size: 0.95rem; margin: 0; color: var(--text-main);">No complaint records in database yet.</p>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">Import historical records from Excel or create new inward entries.</p>
                    <div style="display: flex; justify-content: center; gap: 0.65rem; margin-top: 0.85rem;">
                        @if(auth()->user() && auth()->user()->isAdmin())
                        <a href="{{ route('excel.import_view') }}" class="btn btn-outline" style="font-size: 0.8rem;">Import Excel File</a>
                        @endif
                        <a href="{{ route('complaints.create') }}" class="btn btn-primary" style="font-size: 0.8rem;">Create Inward Entry</a>
                    </div>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Complaint #</th>
                                <th>Serial #</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentComplaints as $rec)
                            <tr>
                                <td style="font-weight: 600; color: #475569; white-space: nowrap;">{{ $rec->complaint_date ? $rec->complaint_date->format('d/m/Y') : '-' }}</td>
                                <td style="font-weight: 800; color: var(--primary-dark);">
                                    <a href="{{ route('complaints.show', $rec->id) }}" style="color: inherit; text-decoration: none;">
                                        #{{ $rec->complaint_number }}
                                    </a>
                                </td>
                                <td style="font-family: 'JetBrains Mono', monospace; font-weight: 800; color: var(--text-main);">{{ $rec->battery_serial_number }}</td>
                                <td style="font-weight: 600; color: #334155;">{{ $rec->customer_name }}</td>
                                <td>
                                    <span style="background-color: #f1f5f9; color: #475569; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 800; font-size: 0.72rem;">
                                        {{ $rec->battery_type ?? ($rec->excel_sheet ?? '2W') }}
                                    </span>
                                </td>
                                <td>
                                    @if(in_array($rec->complaint_status, ['Closed', 'Rejected']))
                                        <span class="badge badge-closed">Closed</span>
                                    @else
                                        <span class="badge badge-open">Open</span>
                                    @endif
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <div style="display: inline-flex; gap: 0.3rem;">
                                        <a href="{{ route('complaints.show', $rec->id) }}" class="btn btn-outline" style="padding: 0.2rem 0.45rem; font-size: 0.75rem;">View</a>
                                        <a href="{{ route('complaints.print', $rec->id) }}" class="btn btn-primary" style="padding: 0.2rem 0.45rem; font-size: 0.75rem;" target="_blank">Print</a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Right Column: Operations & Automation Control Panel -->
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            
            <!-- Automation Service Panel -->
            <div class="card" style="margin-bottom: 0; padding: 1rem;">
                <div class="card-header" style="margin-bottom: 0.75rem; padding-bottom: 0.5rem;">
                    <h3 class="card-title" style="font-size: 0.88rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        Automation Service Health
                    </h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.75rem; background-color: #f8fafc; border-radius: 4px; border: 1px solid var(--border-color);">
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-secondary);">Playwright Service:</span>
                        @if(($automationHealth['online'] ?? false))
                            <span style="background-color: var(--primary-light); color: var(--primary-dark); padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 800; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <span style="width: 7px; height: 7px; background-color: var(--primary); border-radius: 50%;"></span>
                                ONLINE (Port 3000)
                            </span>
                        @else
                            <span style="background-color: #fef2f2; color: #991b1b; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 800; font-size: 0.75rem;">
                                OFFLINE
                            </span>
                        @endif
                    </div>

                    @if(!auth()->user()?->isViewer())
                    <div style="display: flex; flex-direction: column; gap: 0.45rem;">
                        <button type="button" onclick="openBatteryConnectLoginBrowser()" class="btn btn-outline" style="width: 100%; justify-content: center; font-size: 0.8rem; font-weight: 700; padding: 0.5rem;">
                            🔑 Battery Connect Browser Session
                        </button>
                        <button type="button" onclick="startBatchFetchPending('ALL')" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 0.8rem; font-weight: 700; padding: 0.5rem;">
                            ⚡ Fetch All Open Pending Details
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Excel Data Center Shortcuts Card -->
            <div class="card" style="margin-bottom: 0; padding: 1rem;">
                <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.65rem;">
                    📊 Excel & Data Tools
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <a href="{{ route('excel.export_view') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.75rem; background: #ffffff; border: 1px solid var(--border-color); border-radius: 4px; text-decoration: none; color: var(--text-main);">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1rem;">📄</span>
                            <div>
                                <div style="font-size: 0.82rem; font-weight: 800;">Export 2W & 4W Excel</div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">INWARD REG. SHEET</div>
                            </div>
                        </div>
                        <span style="font-size: 0.78rem; font-weight: 800; color: var(--primary);">Export →</span>
                    </a>

                    @if(auth()->user() && auth()->user()->isAdmin())
                    <a href="{{ route('excel.import_view') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.75rem; background: #ffffff; border: 1px solid var(--border-color); border-radius: 4px; text-decoration: none; color: var(--text-main);">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1rem;">📥</span>
                            <div>
                                <div style="font-size: 0.82rem; font-weight: 800;">Import Legacy Excel</div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">Bulk Load & Overwrite</div>
                            </div>
                        </div>
                        <span style="font-size: 0.78rem; font-weight: 800; color: #0284c7;">Import →</span>
                    </a>
                    @endif
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
