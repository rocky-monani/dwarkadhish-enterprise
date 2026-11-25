@extends('layouts.app')

@section('title', '📋 Pending Cases & Case Entry')

@section('content')
<div style="display: flex; flex-direction: column; gap: 1.5rem;">

    <!-- TOP HEADER BAR WITH PRINT ALL TODAY'S ENTRY BUTTON -->
    <div class="card" style="margin-bottom: 0; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-top: 4px solid var(--primary);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 0.6rem;">
                    <span>📋 OPEN PENDING CASES & CRM CASE NUMBER ENTRY</span>
                </h2>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">
                    Operator 2 Station: Showing <strong style="color: var(--primary);">TODAY's OPEN pending cases</strong> and unprinted document entries requiring CRM Case Numbers or receipt printing.
                </p>
            </div>

            <!-- STAT BADGES & BATCH PRINT BUTTON -->
            <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
                <div style="text-align: right; padding-right: 0.85rem; border-right: 1px solid var(--border-color);">
                    <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Open Cases</div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: var(--primary); font-family: 'JetBrains Mono', monospace;">
                        {{ $totalOpenPendingCount }} Open
                    </div>
                </div>

                <div style="text-align: right;">
                    <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Today's Unprinted</div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: {{ $unprintedTodayCount > 0 ? 'var(--accent-red)' : 'var(--amaron-green)' }}; font-family: 'JetBrains Mono', monospace;">
                        {{ $unprintedTodayCount }} Unprinted
                    </div>
                </div>

                @if(!auth()->user()?->isViewer())
                <button type="button" onclick="startBatchFetchPending('ALL')" class="btn" style="background-color: var(--primary); color: #ffffff; font-size: 0.85rem; font-weight: 700; box-shadow: var(--shadow-sm);" title="Fetch Connect details & screenshots for all open pending cases">
                    ⚡ Fetch Pending Details
                </button>
                <button type="button" onclick="openBatchFetchModal()" class="btn btn-outline" style="font-size: 0.85rem; font-weight: 700; background-color: #ffffff;">
                    📷 Fetch Range
                </button>
                @endif

                <!-- 2W BATCH PRINT BUTTON -->
                @if($unprintedToday2WCount > 0)
                    <a href="{{ route('complaints.print_batch_today', ['category' => 'TW']) }}" target="_blank" class="btn" style="background-color: #d97706; color: #ffffff; padding: 0.75rem 1.25rem; font-size: 0.95rem; font-weight: 800; box-shadow: var(--shadow-md); display: inline-flex; align-items: center; gap: 0.4rem;">
                        🖨️ PRINT ALL 2W ({{ $unprintedToday2WCount }})
                    </a>
                @else
                    <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <button type="button" class="btn btn-secondary" style="padding: 0.75rem 1rem; font-size: 0.88rem; font-weight: 700; opacity: 0.85;" onclick="showToast('ℹ️ All today\'s 2W entries have already been printed. Click Reset 2W Print to print again.', 'info');">
                            🏍️ 2W PRINTED (0)
                        </button>
                        @if(!auth()->user()?->isViewer())
                        <form action="{{ route('complaints.reset_print_today') }}" method="POST" style="display: inline;" onsubmit="return confirm('Reset print status of all today\'s 2W entries so you can print them again?');">
                            @csrf
                            <input type="hidden" name="category" value="TW">
                            <button type="submit" class="btn" style="background-color: #d97706; color: #ffffff; padding: 0.75rem 1rem; font-size: 0.88rem; font-weight: 800; box-shadow: var(--shadow-sm);" title="Reset print count to re-print today\'s 2W entries">
                                🔄 Reset 2W Print
                            </button>
                        </form>
                        @endif
                    </div>
                @endif

                <!-- 4W BATCH PRINT BUTTON -->
                @if($unprintedToday4WCount > 0)
                    <a href="{{ route('complaints.print_batch_today', ['category' => 'FW']) }}" target="_blank" class="btn btn-success" style="padding: 0.75rem 1.25rem; font-size: 0.95rem; font-weight: 800; box-shadow: var(--shadow-md); display: inline-flex; align-items: center; gap: 0.4rem;">
                        🖨️ PRINT ALL 4W ({{ $unprintedToday4WCount }})
                    </a>
                @else
                    <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <button type="button" class="btn btn-secondary" style="padding: 0.75rem 1rem; font-size: 0.88rem; font-weight: 700; opacity: 0.85;" onclick="showToast('ℹ️ All today\'s 4W entries have already been printed. Click Reset 4W Print to print again.', 'info');">
                            🚗 4W PRINTED (0)
                        </button>
                        @if(!auth()->user()?->isViewer())
                        <form action="{{ route('complaints.reset_print_today') }}" method="POST" style="display: inline;" onsubmit="return confirm('Reset print status of all today\'s 4W entries so you can print them again?');">
                            @csrf
                            <input type="hidden" name="category" value="FW">
                            <button type="submit" class="btn btn-success" style="padding: 0.75rem 1rem; font-size: 0.88rem; font-weight: 800; box-shadow: var(--shadow-sm);" title="Reset print count to re-print today\'s 4W entries">
                                🔄 Reset 4W Print
                            </button>
                        </form>
                        @endif
                    </div>
                @endif

                <!-- TODAY'S INWARD LEDGER LIST PRINT BUTTONS -->
                <a href="{{ route('complaints.print_today_list', ['category' => 'TW']) }}" target="_blank" class="btn" style="background-color: #0284c7; color: #ffffff; padding: 0.75rem 1.15rem; font-size: 0.92rem; font-weight: 800; box-shadow: var(--shadow-md); display: inline-flex; align-items: center; gap: 0.4rem;" title="Print Full 2W Inward Register Sheet (14 Columns Ledger Format)">
                    📜 2W INWARD SHEET
                </a>
                <a href="{{ route('complaints.print_today_list', ['category' => 'FW']) }}" target="_blank" class="btn" style="background-color: #7c3aed; color: #ffffff; padding: 0.75rem 1.15rem; font-size: 0.92rem; font-weight: 800; box-shadow: var(--shadow-md); display: inline-flex; align-items: center; gap: 0.4rem;" title="Print Full 4W Inward Register Sheet (14 Columns Ledger Format)">
                    📜 4W INWARD SHEET
                </a>

                <!-- BACK-DATE PENDING PRINT & EXCEL EXPORT BUTTONS (TW & FW SEPARATE) -->
                @if($backdatePendingTWCount > 0)
                    <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                        <a href="{{ route('complaints.print_backdate_pending', ['category' => 'TW']) }}" target="_blank" class="btn" style="background-color: #d97706; color: #ffffff; padding: 0.75rem 1.15rem; font-size: 0.92rem; font-weight: 800; box-shadow: var(--shadow-md); display: inline-flex; align-items: center; gap: 0.4rem;" title="Print TW (2W) Back-Date Pending Cases Report">
                            🖨️ TW BACK-DATE ({{ $backdatePendingTWCount }})
                        </a>
                        <a href="{{ route('complaints.export_backdate_pending', ['category' => 'TW']) }}" class="btn" style="background-color: #15803d; color: #ffffff; padding: 0.75rem 1rem; font-size: 0.9rem; font-weight: 800; box-shadow: var(--shadow-md); display: inline-flex; align-items: center; gap: 0.35rem;" title="Export TW (2W) Back-Date Pending Cases to Excel">
                            📥 TW EXCEL
                        </a>
                    </div>
                @else
                    <button type="button" class="btn btn-secondary" style="padding: 0.75rem 1rem; font-size: 0.88rem; font-weight: 700; opacity: 0.75;" onclick="showToast('ℹ️ No TW back-date pending cases found.', 'info');">
                        ✅ TW BACK-DATE (0)
                    </button>
                @endif

                @if($backdatePendingFWCount > 0)
                    <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                        <a href="{{ route('complaints.print_backdate_pending', ['category' => 'FW']) }}" target="_blank" class="btn" style="background-color: #dc2626; color: #ffffff; padding: 0.75rem 1.15rem; font-size: 0.92rem; font-weight: 800; box-shadow: var(--shadow-md); display: inline-flex; align-items: center; gap: 0.4rem;" title="Print FW (4W) Back-Date Pending Cases Report">
                            🖨️ FW BACK-DATE ({{ $backdatePendingFWCount }})
                        </a>
                        <a href="{{ route('complaints.export_backdate_pending', ['category' => 'FW']) }}" class="btn" style="background-color: #15803d; color: #ffffff; padding: 0.75rem 1rem; font-size: 0.9rem; font-weight: 800; box-shadow: var(--shadow-md); display: inline-flex; align-items: center; gap: 0.35rem;" title="Export FW (4W) Back-Date Pending Cases to Excel">
                            📥 FW EXCEL
                        </a>
                    </div>
                @else
                    <button type="button" class="btn btn-secondary" style="padding: 0.75rem 1rem; font-size: 0.88rem; font-weight: 700; opacity: 0.75;" onclick="showToast('ℹ️ No FW back-date pending cases found.', 'info');">
                        ✅ FW BACK-DATE (0)
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="card" style="margin-bottom: 0; padding: 1.15rem;">
        <form id="pending_filter_form" action="{{ route('complaints.pending_cases') }}" method="GET" style="display: flex; gap: 0.85rem; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="flex-grow: 1; min-width: 200px; margin-bottom: 0;">
                <label for="search" class="form-label" style="font-size: 0.8rem; font-weight: 700;">SEARCH RECORDS (AUTO SEARCH)</label>
                <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Type Complaint No, Serial, Name, Mobile..." autocomplete="off">
            </div>

            <div class="form-group" style="width: 140px; margin-bottom: 0;">
                <label for="status" class="form-label" style="font-size: 0.8rem; font-weight: 700;">STATUS</label>
                <select id="status" name="status" class="form-control" style="font-weight: 700;">
                    <option value="Open" {{ ($selectedStatus ?? 'Open') === 'Open' ? 'selected' : '' }}>🟢 Open Only</option>
                    <option value="Closed" {{ ($selectedStatus ?? '') === 'Closed' ? 'selected' : '' }}>✅ Closed</option>
                    <option value="Rejected" {{ ($selectedStatus ?? '') === 'Rejected' ? 'selected' : '' }}>❌ Rejected</option>
                    <option value="all" {{ ($selectedStatus ?? '') === 'all' ? 'selected' : '' }}>All Statuses</option>
                </select>
            </div>

            <div class="form-group" style="width: 160px; margin-bottom: 0;">
                <label for="date" class="form-label" style="font-size: 0.8rem; font-weight: 700;">INWARD DATE</label>
                <select id="date" name="date" class="form-control" style="font-weight: 700;">
                    <option value="today" {{ ($selectedDate ?? 'today') === 'today' ? 'selected' : '' }}>📅 Today Only</option>
                    <option value="backdate" {{ ($selectedDate ?? '') === 'backdate' ? 'selected' : '' }}>⚠️ Back-Date Pending Only</option>
                    <option value="all" {{ ($selectedDate ?? '') === 'all' ? 'selected' : '' }}>🌐 All Dates</option>
                </select>
            </div>

            <div class="form-group" style="width: 140px; margin-bottom: 0;">
                <label for="battery_type" class="form-label" style="font-size: 0.8rem; font-weight: 700;">CATEGORY</label>
                <select id="battery_type" name="battery_type" class="form-control">
                    <option value="">All Categories</option>
                    <option value="TW" {{ request('battery_type') === 'TW' ? 'selected' : '' }}>🏍️ TW</option>
                    <option value="FW" {{ request('battery_type') === 'FW' ? 'selected' : '' }}>🚗 FW</option>
                </select>
            </div>

            <div class="form-group" style="width: 160px; margin-bottom: 0;">
                <label for="online_reg_type_filter" class="form-label" style="font-size: 0.8rem; font-weight: 700;">DOC STATUS</label>
                <select id="online_reg_type_filter" name="online_reg_type" class="form-control" style="font-weight: 700;">
                    <option value="">All Doc Statuses</option>
                    <option value="WARRANTY CARD" {{ request('online_reg_type') === 'WARRANTY CARD' ? 'selected' : '' }}>📄 Warranty Card</option>
                    <option value="RC BOOK" {{ request('online_reg_type') === 'RC BOOK' ? 'selected' : '' }}>🚘 RC Book</option>
                    <option value="NOT DOWNLOADED" {{ request('online_reg_type') === 'NOT DOWNLOADED' ? 'selected' : '' }}>❌ Not Downloaded</option>
                    <option value="none" {{ request('online_reg_type') === 'none' ? 'selected' : '' }}>Blank / Unset</option>
                </select>
            </div>

            <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                <a href="{{ route('complaints.pending_cases', ['date' => 'today', 'unprinted' => '1']) }}" class="btn btn-warning" style="padding: 0.65rem 0.85rem; font-weight: 700;" title="Show Today's Unprinted Documents">
                    ⚠️ Today's Unprinted
                </a>
                <a href="{{ route('complaints.pending_cases', ['date' => 'backdate', 'battery_type' => 'TW']) }}" class="btn" style="background-color: #d97706; color: #ffffff; padding: 0.65rem 0.85rem; font-weight: 700;" title="Show TW (2W) Back-Date Open Pending Cases">
                    ⚠️ TW Back-Date ({{ $backdatePendingTWCount }})
                </a>
                <a href="{{ route('complaints.pending_cases', ['date' => 'backdate', 'battery_type' => 'FW']) }}" class="btn" style="background-color: #dc2626; color: #ffffff; padding: 0.65rem 0.85rem; font-weight: 700;" title="Show FW (4W) Back-Date Open Pending Cases">
                    ⚠️ FW Back-Date ({{ $backdatePendingFWCount }})
                </a>
                <a href="{{ route('complaints.pending_cases', ['date' => 'all']) }}" class="btn btn-outline" style="padding: 0.65rem 0.85rem;">All Open Cases</a>
            </div>
        </form>
    </div>

    <!-- PENDING COMPLAINTS TABLE -->
    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 0;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="background-color: #f1f5f9; border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em;">
                        <th style="padding: 0.85rem 1rem;">No.</th>
                        <th style="padding: 0.85rem 1rem;">Inward Date</th>
                        <th style="padding: 0.85rem 1rem;">Category / Model</th>
                        <th style="padding: 0.85rem 1rem;">Customer Details</th>
                        <th style="padding: 0.85rem 1rem;">Battery Serial</th>
                        <th style="padding: 0.85rem 1rem; min-width: 220px;">CRM Case Number (Operator 2)</th>
                        <th style="padding: 0.85rem 1rem;">Print Counter</th>
                        <th style="padding: 0.85rem 1rem; text-align: right;">Action / Print</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $complaint)
                        <tr style="border-bottom: 1px solid var(--border-color); background-color: {{ $complaint->print_count === 0 ? '#ffffff' : '#f8fafc' }};">
                            <!-- Complaint Number (Without # in starting) -->
                            <td style="padding: 0.85rem 1rem; font-weight: 800; font-family: 'JetBrains Mono', monospace; font-size: 1rem; color: var(--primary);">
                                {{ $complaint->complaint_number }}
                            </td>

                            <!-- Date -->
                            <td style="padding: 0.85rem 1rem; white-space: nowrap; color: var(--text-secondary);">
                                {{ $complaint->complaint_date ? $complaint->complaint_date->format('d-m-Y') : 'N/A' }}
                            </td>

                            <!-- Category / Model & Doc Status -->
                            <td style="padding: 0.85rem 1rem;">
                                <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.35rem;">
                                    <span class="badge" style="background-color: {{ $complaint->battery_type === 'TW' ? '#d1fae5' : '#e0f2fe' }}; color: {{ $complaint->battery_type === 'TW' ? '#047857' : '#0369a1' }}; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">
                                        {{ $complaint->battery_type }}
                                    </span>
                                    <span style="font-weight: 700; color: var(--text-main); font-family: 'JetBrains Mono', monospace;">{{ $complaint->product_model ?: '-' }}</span>
                                </div>

                                @if($complaint->online_reg_type === 'WARRANTY CARD')
                                    <span style="font-size: 0.75rem; font-weight: 800; color: #0284c7; background-color: #e0f2fe; padding: 0.15rem 0.4rem; border-radius: 4px; display: inline-block;">📄 WARRANTY CARD</span>
                                @elseif($complaint->online_reg_type === 'RC BOOK')
                                    <span style="font-size: 0.75rem; font-weight: 800; color: #7c3aed; background-color: #f3e8ff; padding: 0.15rem 0.4rem; border-radius: 4px; display: inline-block;">🚘 RC BOOK</span>
                                @elseif($complaint->online_reg_type === 'NOT DOWNLOADED')
                                    <span style="font-size: 0.75rem; font-weight: 800; color: #dc2626; background-color: #fee2e2; padding: 0.15rem 0.4rem; border-radius: 4px; display: inline-block;">❌ NOT DOWNLOADED</span>
                                @elseif(!empty($complaint->online_reg_type))
                                    <span style="font-size: 0.75rem; font-weight: 800; color: #334155; background-color: #f1f5f9; padding: 0.15rem 0.4rem; border-radius: 4px; display: inline-block;">📑 {{ $complaint->online_reg_type }}</span>
                                @else
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Doc: -</span>
                                @endif
                            </td>

                            <!-- Customer Details -->
                            <td style="padding: 0.85rem 1rem;">
                                <div style="font-weight: 700; color: var(--text-main);">{{ $complaint->customer_name }}</div>
                                @if($complaint->customer_mobile)
                                    <div style="font-size: 0.8rem; color: var(--text-muted); font-family: 'JetBrains Mono', monospace;">📞 {{ $complaint->customer_mobile }}</div>
                                @elseif(!auth()->user()?->isViewer())
                                    <div style="display: flex; align-items: center; gap: 0.35rem; margin-top: 0.35rem;">
                                        <input type="text" id="mobile_input_{{ $complaint->id }}" class="form-control" style="padding: 0.25rem 0.45rem; font-size: 0.78rem; font-weight: 700; font-family: 'JetBrains Mono', monospace; width: 115px; border-color: #f59e0b;" placeholder="Mobile No" maxlength="15">
                                        <button type="button" class="btn btn-outline" style="padding: 0.25rem 0.45rem; font-size: 0.75rem; font-weight: 700;" onclick="saveCustomerMobile({{ $complaint->id }})">
                                            Save
                                        </button>
                                    </div>
                                @else
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">📞 -</div>
                                @endif
                            </td>

                            <!-- Serial Number -->
                            <td style="padding: 0.85rem 1rem; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: var(--text-main);">
                                {{ $complaint->battery_serial_number }}
                                @if(!empty($complaint->battery_connect_data['warranty_status']) && preg_match('/out\s*of\s*warranty|expired|no\s*warranty/i', $complaint->battery_connect_data['warranty_status']))
                                    <div style="font-size: 0.72rem; color: #dc2626; font-weight: 800; background-color: #fee2e2; border: 1px solid #fca5a5; padding: 0.15rem 0.4rem; border-radius: 4px; margin-top: 0.2rem; display: inline-block;">
                                        ⚠️ OUT OF WARRANTY (PRO-RATA)
                                    </div>
                                @elseif($complaint->is_prorata)
                                    <div style="font-size: 0.72rem; color: #b45309; font-weight: 800;">PRO-RATA ({{ $complaint->prorata_percentage ? (float)$complaint->prorata_percentage . '%' : 'YES' }})</div>
                                @endif
                            </td>

                            <!-- CRM Case Number Inline Editor -->
                            <td style="padding: 0.85rem 1rem;">
                                @if($complaint->isLeadTimeExceeded())
                                    <div style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.35rem 0.6rem; border-radius: 6px; font-weight: 800; font-size: 0.75rem; margin-bottom: 0.4rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                        ⛔ LEAD TIME 1 > 90 DAYS ({{ $complaint->lead_time_days }}d) — DO NOT ENTER CASE
                                    </div>
                                @endif
                                @if(auth()->user()?->isViewer())
                                    <span style="font-family: 'JetBrains Mono', monospace; font-weight: 700; color: var(--text-secondary);">
                                        {{ $complaint->case_number ?: '—' }}
                                    </span>
                                @else
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="text" id="case_input_{{ $complaint->id }}" class="form-control" style="padding: 0.35rem 0.6rem; font-size: 0.85rem; font-weight: 700; font-family: 'JetBrains Mono', monospace; border-color: {{ $complaint->isLeadTimeExceeded() ? '#ef4444' : (empty($complaint->case_number) ? '#f59e0b' : '#cbd5e1') }};" value="{{ $complaint->case_number }}" placeholder="{{ $complaint->isLeadTimeExceeded() ? 'DO NOT ENTER CASE (Lead Time > 90d)' : ($complaint->is_prorata ? 'Optional (Prorata)' : 'Enter CRM Case No') }}">
                                        <button type="button" class="btn btn-outline" style="padding: 0.35rem 0.65rem; font-size: 0.78rem; font-weight: 700;" onclick="saveCaseNumber({{ $complaint->id }})">
                                            Save
                                        </button>
                                    </div>
                                @endif
                            </td>

                            <!-- Print Counter Badge -->
                            <td style="padding: 0.85rem 1rem; white-space: nowrap;">
                                @if($complaint->print_count === 0)
                                    <span style="background-color: #fef2f2; color: #dc2626; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 800; font-size: 0.78rem; border: 1px solid #fecaca;">
                                        ⚠️ Unprinted (0)
                                    </span>
                                @else
                                    <span style="background-color: #ecfdf5; color: #047857; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 800; font-size: 0.78rem; border: 1px solid #a7f3d0;">
                                        ✓ Printed ({{ $complaint->print_count }})
                                    </span>
                                @endif
                            </td>

                            <!-- Actions & Single Print Button -->
                            <td style="padding: 0.85rem 1rem; text-align: right; white-space: nowrap;">
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.4rem;">
                                    <!-- Single Print Button: Always allowed whether case_number is present or missing, and whether print_count is 0 or >0 -->
                                    <a href="{{ route('complaints.print', $complaint->id) }}?autoprint=1" target="_blank" class="btn btn-success" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; font-weight: 700;" title="Print customer receipt (Increments print counter)">
                                        🖨️ Print
                                    </a>
                                    <a href="{{ route('complaints.show', $complaint->id) }}" class="btn btn-outline" style="padding: 0.35rem 0.65rem; font-size: 0.8rem;">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 3rem 1rem; text-align: center; color: var(--text-muted);">
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📋</div>
                                <div style="font-weight: 700; font-size: 1rem;">No inward records found for the selected filter.</div>
                                <div style="font-size: 0.85rem; margin-top: 0.25rem;">New inward entries submitted by Operator 1 will appear here.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($complaints->hasPages())
            <div style="padding: 1rem; border-top: 1px solid var(--border-color);">
                {{ $complaints->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
async function saveCaseNumber(complaintId) {
    const input = document.getElementById('case_input_' + complaintId);
    if (!input) return;

    const val = input.value.trim();

    try {
        const data = await safeFetchJson("{{ url('/complaints') }}/" + complaintId + "/case-number", {
            method: 'POST',
            body: JSON.stringify({ case_number: val })
        });

        if (data.success) {
            showToast('✓ CRM Case Number saved for Complaint!', 'success');
            input.style.borderColor = '#10b981';
            setTimeout(() => { input.style.borderColor = val ? '#cbd5e1' : '#f59e0b'; }, 2000);
        }
    } catch (err) {
        showToast('❌ Failed to save CRM Case Number: ' + err.message, 'error');
    }
}

async function saveCustomerMobile(complaintId) {
    const input = document.getElementById('mobile_input_' + complaintId);
    if (!input) return;

    const val = input.value.trim();
    if (!val) {
        showToast('⚠️ Please enter a mobile number first.', 'warning');
        return;
    }

    try {
        const data = await safeFetchJson("{{ url('/complaints') }}/" + complaintId + "/customer-mobile", {
            method: 'POST',
            body: JSON.stringify({ customer_mobile: val })
        });

        if (data.success) {
            showToast('✓ Customer Mobile saved successfully!', 'success');
            input.style.borderColor = '#10b981';
            setTimeout(() => { location.reload(); }, 800);
        }
    } catch (err) {
        showToast('❌ Failed to save Customer Mobile: ' + err.message, 'error');
    }
}

async function saveRegType(complaintId, val) {
    try {
        const data = await safeFetchJson("{{ url('/complaints') }}/" + complaintId + "/reg-type", {
            method: 'POST',
            body: JSON.stringify({ online_reg_type: val })
        });

        if (data.success) {
            showToast('✓ Warranty / RC Status updated!', 'success');
        }
    } catch (err) {
        showToast('❌ Failed to save Warranty / RC Status: ' + err.message, 'error');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pending_filter_form');
    if (!form) return;

    const searchInput = document.getElementById('search');
    const statusSelect = document.getElementById('status');
    const dateSelect = document.getElementById('date');
    const batteryTypeSelect = document.getElementById('battery_type');
    const docStatusSelect = document.getElementById('online_reg_type_filter');

    let debounceTimer;

    function autoSubmit() {
        form.submit();
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(autoSubmit, 350);
        });

        if (searchInput.value) {
            searchInput.focus();
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }
    }

    [statusSelect, dateSelect, batteryTypeSelect, docStatusSelect].forEach(elem => {
        if (elem) {
            elem.addEventListener('change', autoSubmit);
        }
    });
});
</script>
@endpush
