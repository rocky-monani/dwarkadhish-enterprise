@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Top Header -->
    <div class="card" style="margin-bottom: 0; padding: 20px 24px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px; font-size: 12px; color: var(--text-muted);">
                <span style="width: 6px; height: 6px; background-color: var(--primary); border-radius: 50%; display: inline-block;"></span>
                <span>Amaron Authorized Service Center</span>
                <span>·</span>
                <span class="tabular" style="font-family: 'JetBrains Mono', monospace;">10013504S05</span>
            </div>
            <h1 style="font-size: 22px; font-weight: 600; margin: 0; color: var(--text-main);">
                Dwarkadhish Automobiles
            </h1>
            <p style="margin: 2px 0 0 0; color: var(--text-secondary); font-size: 13px;">
                Battery warranty inward register and complaint operations
            </p>
        </div>

        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
            @if(!auth()->user()?->isViewer())
            <a href="{{ route('complaints.create') }}" class="btn btn-primary">
                New inward
            </a>
            @endif

            @if(auth()->user() && auth()->user()->isAdmin())
            <a href="{{ route('excel.import_view') }}" class="btn btn-outline">
                Import Excel
            </a>
            @endif

            <a href="{{ route('excel.export_view') }}" class="btn btn-outline">
                Export Excel
            </a>
        </div>
    </div>

    <!-- KPI Strip -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
        <a href="{{ route('complaints.index', ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" class="card" style="margin-bottom: 0; padding: 20px; text-decoration: none; color: inherit; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="font-size: 13px; font-weight: 500; color: var(--text-secondary);">Today inward</div>
            <div class="tabular" style="font-size: 28px; font-weight: 500; color: var(--primary-dark); margin-top: 8px; line-height: 1;">{{ $todaysComplaints }}</div>
            <div class="tabular" style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">{{ $todaysTwCount }} TW · {{ $todaysFwCount }} FW</div>
        </a>

        <a href="{{ route('complaints.pending_cases') }}" class="card" style="margin-bottom: 0; padding: 20px; text-decoration: none; color: inherit; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="font-size: 13px; font-weight: 500; color: var(--text-secondary);">Pending unprinted</div>
            <div class="tabular" style="font-size: 28px; font-weight: 500; color: {{ $pendingPrintCount > 0 ? 'var(--accent-amber)' : 'var(--text-main)' }}; margin-top: 8px; line-height: 1;">{{ $pendingPrintCount }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">Awaiting receipt print</div>
        </a>

        <a href="{{ route('complaints.pending_cases', ['date' => 'backdate', 'battery_type' => 'TW']) }}" class="card" style="margin-bottom: 0; padding: 20px; text-decoration: none; color: inherit; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="font-size: 13px; font-weight: 500; color: var(--text-secondary);">TW back-date</div>
            <div class="tabular" style="font-size: 28px; font-weight: 500; color: {{ $backdatePendingTWCount > 0 ? '#92400e' : 'var(--text-main)' }}; margin-top: 8px; line-height: 1;">{{ $backdatePendingTWCount }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">Open two-wheeler cases</div>
        </a>

        <a href="{{ route('complaints.pending_cases', ['date' => 'backdate', 'battery_type' => 'FW']) }}" class="card" style="margin-bottom: 0; padding: 20px; text-decoration: none; color: inherit; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="font-size: 13px; font-weight: 500; color: var(--text-secondary);">FW back-date</div>
            <div class="tabular" style="font-size: 28px; font-weight: 500; color: {{ $backdatePendingFWCount > 0 ? 'var(--accent-red)' : 'var(--text-main)' }}; margin-top: 8px; line-height: 1;">{{ $backdatePendingFWCount }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">Open four-wheeler cases</div>
        </a>

        <a href="{{ route('complaints.index') }}" class="card" style="margin-bottom: 0; padding: 20px; text-decoration: none; color: inherit; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="font-size: 13px; font-weight: 500; color: var(--text-secondary);">Total register</div>
            <div class="tabular" style="font-size: 28px; font-weight: 500; color: var(--text-main); margin-top: 8px; line-height: 1;">{{ $totalComplaints }}</div>
            <div class="tabular" style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">{{ $openComplaints }} open · {{ $closedComplaints }} closed</div>
        </a>
    </div>

    <!-- Main 2-Column Workspace -->
    <div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 20px; align-items: start;">

        <!-- Left Column: Monthly TW & FW Return Entry Pending Bar Graph -->
        <div class="card" style="margin-bottom: 0; padding: 20px 24px;">
            <div class="card-header" style="margin-bottom: 20px; padding-bottom: 12px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h3 class="card-title">Return entry pending by month</h3>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">All months with open TW and FW complaints awaiting return</div>
                </div>
                <div style="display: flex; align-items: center; gap: 16px; font-size: 12px; color: var(--text-secondary);">
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 10px; height: 10px; border-radius: 2px; background-color: #b45309;"></span>
                        TW (2W)
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 10px; height: 10px; border-radius: 2px; background-color: #15803d;"></span>
                        FW (4W)
                    </span>
                </div>
            </div>

            @if(empty($monthlyPendingChart))
                <div style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
                    <div style="font-size: 14px; font-weight: 500; color: var(--text-main);">No pending return entries</div>
                    <div style="font-size: 12px; margin-top: 4px;">All inward complaints are closed.</div>
                </div>
            @else
                @php
                    $maxVal = 1;
                    foreach ($monthlyPendingChart as $m) {
                        $maxVal = max($maxVal, $m['tw'], $m['fw']);
                    }
                    $gridMax = (int) (ceil($maxVal / 4) * 4);
                    if ($gridMax < 4) {
                        $gridMax = 4;
                    }
                    $chartHeight = 220;
                    $monthCount = count($monthlyPendingChart);
                @endphp

                <div style="display: flex; align-items: flex-start; gap: 8px; padding-top: 20px;">
                    <!-- Fixed Y-Axis Labels -->
                    <div style="position: relative; width: 32px; height: {{ $chartHeight }}px; flex-shrink: 0;">
                        @foreach([1, 0.75, 0.5, 0.25, 0] as $step)
                            @php
                                $tickVal = (int) round($gridMax * $step);
                                $topPx = (int) round((1 - $step) * $chartHeight);
                            @endphp
                            <span class="tabular" style="position: absolute; right: 4px; top: {{ $topPx }}px; transform: translateY(-50%); font-size: 11px; color: var(--text-muted); font-family: 'JetBrains Mono', monospace; line-height: 1;">
                                {{ $tickVal }}
                            </span>
                        @endforeach
                    </div>

                    <!-- Scrollable Plot Area + Aligned X-Axis -->
                    <div style="flex: 1; overflow-x: auto;">
                        <div style="min-width: {{ max(320, $monthCount * 96) }}px;">
                            <!-- Plot Box -->
                            <div style="position: relative; height: {{ $chartHeight }}px; border-bottom: 1px solid var(--border-strong);">
                                <!-- Horizontal Dashed Gridlines -->
                                @foreach([1, 0.75, 0.5, 0.25] as $step)
                                    @php $topPx = (int) round((1 - $step) * $chartHeight); @endphp
                                    <div style="position: absolute; left: 0; right: 0; top: {{ $topPx }}px; border-top: 1px dashed var(--border-color); pointer-events: none;"></div>
                                @endforeach

                                <!-- Bars Row -->
                                <div style="position: relative; z-index: 2; height: {{ $chartHeight }}px; display: grid; grid-template-columns: repeat({{ $monthCount }}, minmax(88px, 1fr)); gap: 12px; padding: 0 12px;">
                                    @foreach($monthlyPendingChart as $m)
                                        @php
                                            $twHeight = $m['tw'] > 0 ? max(4, (int) round(($m['tw'] / $gridMax) * $chartHeight)) : 0;
                                            $fwHeight = $m['fw'] > 0 ? max(4, (int) round(($m['fw'] / $gridMax) * $chartHeight)) : 0;
                                        @endphp
                                        <div style="height: {{ $chartHeight }}px; display: flex; align-items: flex-end; justify-content: center; gap: 8px;">
                                            <div style="width: 28px; display: flex; flex-direction: column; align-items: center;">
                                                <span class="tabular" style="font-size: 11px; font-weight: 500; line-height: 1; margin-bottom: 4px; color: {{ $m['tw'] > 0 ? '#92400e' : 'var(--text-muted)' }}; font-family: 'JetBrains Mono', monospace;">{{ $m['tw'] }}</span>
                                                <div title="TW: {{ $m['tw'] }} pending in {{ $m['label'] }}" style="width: 100%; height: {{ $twHeight }}px; background-color: #b45309; border-radius: 4px 4px 0 0;"></div>
                                            </div>
                                            <div style="width: 28px; display: flex; flex-direction: column; align-items: center;">
                                                <span class="tabular" style="font-size: 11px; font-weight: 500; line-height: 1; margin-bottom: 4px; color: {{ $m['fw'] > 0 ? 'var(--primary-dark)' : 'var(--text-muted)' }}; font-family: 'JetBrains Mono', monospace;">{{ $m['fw'] }}</span>
                                                <div title="FW: {{ $m['fw'] }} pending in {{ $m['label'] }}" style="width: 100%; height: {{ $fwHeight }}px; background-color: #15803d; border-radius: 4px 4px 0 0;"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- X-Axis Labels (Exact same grid columns) -->
                            <div style="display: grid; grid-template-columns: repeat({{ $monthCount }}, minmax(88px, 1fr)); gap: 12px; padding: 10px 12px 0 12px;">
                                @foreach($monthlyPendingChart as $m)
                                    <div style="text-align: center;">
                                        <div style="font-size: 12px; font-weight: 500; color: var(--text-main);">{{ $m['label'] }}</div>
                                        <div class="tabular" style="font-size: 11px; color: var(--text-muted); font-family: 'JetBrains Mono', monospace; margin-top: 2px;">{{ $m['total'] }} total</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Operational Controls -->
        <div style="display: flex; flex-direction: column; gap: 16px;">

            <!-- Inward Sheets & Back-Date Reports -->
            <div class="card" style="margin-bottom: 0; padding: 20px;">
                <div class="card-header" style="margin-bottom: 12px; padding-bottom: 10px;">
                    <h3 class="card-title">Inward sheets &amp; back-date</h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <a href="{{ route('complaints.print_today_list', ['category' => 'TW']) }}" target="_blank" class="btn btn-outline" style="width: 100%; justify-content: center;">
                            2W inward sheet
                        </a>
                        <a href="{{ route('complaints.print_today_list', ['category' => 'FW']) }}" target="_blank" class="btn btn-outline" style="width: 100%; justify-content: center;">
                            4W inward sheet
                        </a>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 8px; margin-top: 4px;">
                        <div>
                            <div style="font-size: 13px; font-weight: 500; color: var(--text-main);">TW back-date</div>
                            <div class="tabular" style="font-size: 12px; color: {{ $backdatePendingTWCount > 0 ? '#92400e' : 'var(--text-muted)' }};">
                                {{ $backdatePendingTWCount }} open pending
                            </div>
                        </div>
                        <div style="display: inline-flex; gap: 6px;">
                            <a href="{{ route('complaints.pending_cases', ['date' => 'backdate', 'battery_type' => 'TW']) }}" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;">View</a>
                            @if($backdatePendingTWCount > 0)
                                <a href="{{ route('complaints.print_backdate_pending', ['category' => 'TW']) }}" target="_blank" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;">Print</a>
                                <a href="{{ route('complaints.export_backdate_pending', ['category' => 'TW']) }}" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;">Excel</a>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 8px;">
                        <div>
                            <div style="font-size: 13px; font-weight: 500; color: var(--text-main);">FW back-date</div>
                            <div class="tabular" style="font-size: 12px; color: {{ $backdatePendingFWCount > 0 ? '#991b1b' : 'var(--text-muted)' }};">
                                {{ $backdatePendingFWCount }} open pending
                            </div>
                        </div>
                        <div style="display: inline-flex; gap: 6px;">
                            <a href="{{ route('complaints.pending_cases', ['date' => 'backdate', 'battery_type' => 'FW']) }}" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;">View</a>
                            @if($backdatePendingFWCount > 0)
                                <a href="{{ route('complaints.print_backdate_pending', ['category' => 'FW']) }}" target="_blank" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;">Print</a>
                                <a href="{{ route('complaints.export_backdate_pending', ['category' => 'FW']) }}" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;">Excel</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Automation Service -->
            <div class="card" style="margin-bottom: 0; padding: 20px;">
                <div class="card-header" style="margin-bottom: 12px; padding-bottom: 10px;">
                    <h3 class="card-title">Automation service</h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background-color: var(--bg-color); border-radius: 8px; border: 1px solid var(--border-color);">
                        <span style="font-size: 13px; color: var(--text-secondary);">Playwright service</span>
                        @if(($automationHealth['online'] ?? false))
                            <span class="badge badge-open">
                                <span style="width: 6px; height: 6px; background-color: var(--primary); border-radius: 50%;"></span>
                                Online (3000)
                            </span>
                        @else
                            <span class="badge badge-closed">Offline</span>
                        @endif
                    </div>

                    @if(!auth()->user()?->isViewer())
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <button type="button" onclick="openBatteryConnectLoginBrowser()" class="btn btn-outline" style="width: 100%; justify-content: center;">
                            Battery Connect session
                        </button>
                        <button type="button" onclick="startBatchFetchPending('ALL')" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            Fetch all open pending
                        </button>
                    </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
