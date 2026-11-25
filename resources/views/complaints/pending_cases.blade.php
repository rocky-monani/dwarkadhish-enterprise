@extends('layouts.app')

@section('title', 'Pending Cases & Case Entry')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Header & Today Batch Print -->
    <div class="card" style="margin-bottom: 0; padding: 20px 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 600; color: var(--text-main); margin: 0;">
                    Pending cases &amp; CRM entry
                </h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                    Showing today's open pending cases and unprinted entries for CRM case entry and receipt printing.
                </p>
            </div>

            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 16px; padding-right: 16px; border-right: 1px solid var(--border-color);">
                    <div style="text-align: right;">
                        <div style="font-size: 12px; color: var(--text-muted);">Total open</div>
                        <div class="tabular" style="font-size: 20px; font-weight: 500; color: var(--text-main); font-family: 'JetBrains Mono', monospace;">
                            {{ $totalOpenPendingCount }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 12px; color: var(--text-muted);">Today unprinted</div>
                        <div class="tabular" style="font-size: 20px; font-weight: 500; color: {{ $unprintedTodayCount > 0 ? 'var(--accent-red)' : 'var(--primary-dark)' }}; font-family: 'JetBrains Mono', monospace;">
                            {{ $unprintedTodayCount }}
                        </div>
                    </div>
                </div>

                <!-- Today's 2W & 4W Batch Print -->
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    @if($unprintedToday2WCount > 0)
                        <a href="{{ route('complaints.print_batch_today', ['category' => 'TW']) }}" target="_blank" class="btn btn-warning tabular">
                            Print 2W ({{ $unprintedToday2WCount }})
                        </a>
                    @else
                        <button type="button" class="btn btn-secondary tabular" onclick="showToast('All today\'s 2W entries have already been printed. Click Reset 2W to print again.', 'info');">
                            2W printed (0)
                        </button>
                        @if(!auth()->user()?->isViewer())
                        <form action="{{ route('complaints.reset_print_today') }}" method="POST" style="display: inline;" onsubmit="return confirm('Reset print status of all today\'s 2W entries so you can print them again?');">
                            @csrf
                            <input type="hidden" name="category" value="TW">
                            <button type="submit" class="btn btn-outline" title="Reset print count to re-print today's 2W entries">
                                Reset 2W
                            </button>
                        </form>
                        @endif
                    @endif

                    @if($unprintedToday4WCount > 0)
                        <a href="{{ route('complaints.print_batch_today', ['category' => 'FW']) }}" target="_blank" class="btn btn-primary tabular">
                            Print 4W ({{ $unprintedToday4WCount }})
                        </a>
                    @else
                        <button type="button" class="btn btn-secondary tabular" onclick="showToast('All today\'s 4W entries have already been printed. Click Reset 4W to print again.', 'info');">
                            4W printed (0)
                        </button>
                        @if(!auth()->user()?->isViewer())
                        <form action="{{ route('complaints.reset_print_today') }}" method="POST" style="display: inline;" onsubmit="return confirm('Reset print status of all today\'s 4W entries so you can print them again?');">
                            @csrf
                            <input type="hidden" name="category" value="FW">
                            <button type="submit" class="btn btn-outline" title="Reset print count to re-print today's 4W entries">
                                Reset 4W
                            </button>
                        </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card" style="margin-bottom: 0; padding: 16px 20px;">
        <form id="pending_filter_form" action="{{ route('complaints.pending_cases') }}" method="GET" style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: grid; grid-template-columns: 2fr repeat(4, minmax(130px, 1fr)); gap: 12px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Complaint no, serial, customer, mobile" autocomplete="off">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="Open" {{ ($selectedStatus ?? 'Open') === 'Open' ? 'selected' : '' }}>Open only</option>
                        <option value="Closed" {{ ($selectedStatus ?? '') === 'Closed' ? 'selected' : '' }}>Closed</option>
                        <option value="Rejected" {{ ($selectedStatus ?? '') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="all" {{ ($selectedStatus ?? '') === 'all' ? 'selected' : '' }}>All statuses</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="date" class="form-label">Inward date</label>
                    <select id="date" name="date" class="form-control">
                        <option value="today" {{ ($selectedDate ?? 'today') === 'today' ? 'selected' : '' }}>Today only</option>
                        <option value="backdate" {{ ($selectedDate ?? '') === 'backdate' ? 'selected' : '' }}>Back-date pending</option>
                        <option value="all" {{ ($selectedDate ?? '') === 'all' ? 'selected' : '' }}>All dates</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="battery_type" class="form-label">Category</label>
                    <select id="battery_type" name="battery_type" class="form-control">
                        <option value="">All categories</option>
                        <option value="TW" {{ request('battery_type') === 'TW' ? 'selected' : '' }}>TW (2W)</option>
                        <option value="FW" {{ request('battery_type') === 'FW' ? 'selected' : '' }}>FW (4W)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="online_reg_type_filter" class="form-label">Doc status</label>
                    <select id="online_reg_type_filter" name="online_reg_type" class="form-control">
                        <option value="">All doc statuses</option>
                        <option value="WARRANTY CARD" {{ request('online_reg_type') === 'WARRANTY CARD' ? 'selected' : '' }}>Warranty card</option>
                        <option value="RC BOOK" {{ request('online_reg_type') === 'RC BOOK' ? 'selected' : '' }}>RC book</option>
                        <option value="NOT DOWNLOADED" {{ request('online_reg_type') === 'NOT DOWNLOADED' ? 'selected' : '' }}>Not downloaded</option>
                        <option value="none" {{ request('online_reg_type') === 'none' ? 'selected' : '' }}>Blank / unset</option>
                    </select>
                </div>
            </div>

            <!-- Quick Views & Back-date Actions -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <a href="{{ route('complaints.pending_cases', ['date' => 'today', 'unprinted' => '1']) }}" class="btn {{ request('unprinted') === '1' ? 'btn-primary' : 'btn-outline' }}" style="height: 32px; padding: 0 12px; font-size: 12px;">
                        Today unprinted
                    </a>
                    <a href="{{ route('complaints.pending_cases', ['date' => 'backdate', 'battery_type' => 'TW']) }}" class="btn {{ ($selectedDate ?? '') === 'backdate' && request('battery_type') === 'TW' ? 'btn-warning' : 'btn-outline' }} tabular" style="height: 32px; padding: 0 12px; font-size: 12px;">
                        TW back-date ({{ $backdatePendingTWCount }})
                    </a>
                    <a href="{{ route('complaints.pending_cases', ['date' => 'backdate', 'battery_type' => 'FW']) }}" class="btn {{ ($selectedDate ?? '') === 'backdate' && request('battery_type') === 'FW' ? 'btn-danger' : 'btn-outline' }} tabular" style="height: 32px; padding: 0 12px; font-size: 12px;">
                        FW back-date ({{ $backdatePendingFWCount }})
                    </a>
                    <a href="{{ route('complaints.pending_cases', ['date' => 'all']) }}" class="btn {{ ($selectedDate ?? '') === 'all' ? 'btn-primary' : 'btn-outline' }}" style="height: 32px; padding: 0 12px; font-size: 12px;">
                        All open
                    </a>
                </div>

                @if(($selectedDate ?? '') === 'backdate')
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    @if(request('battery_type') === 'TW' && $backdatePendingTWCount > 0)
                        <a href="{{ route('complaints.print_backdate_pending', ['category' => 'TW']) }}" target="_blank" class="btn btn-outline" style="height: 32px; padding: 0 12px; font-size: 12px;">Print TW back-date</a>
                        <a href="{{ route('complaints.export_backdate_pending', ['category' => 'TW']) }}" class="btn btn-outline" style="height: 32px; padding: 0 12px; font-size: 12px;">Export TW Excel</a>
                    @elseif(request('battery_type') === 'FW' && $backdatePendingFWCount > 0)
                        <a href="{{ route('complaints.print_backdate_pending', ['category' => 'FW']) }}" target="_blank" class="btn btn-outline" style="height: 32px; padding: 0 12px; font-size: 12px;">Print FW back-date</a>
                        <a href="{{ route('complaints.export_backdate_pending', ['category' => 'FW']) }}" class="btn btn-outline" style="height: 32px; padding: 0 12px; font-size: 12px;">Export FW Excel</a>
                    @elseif($backdatePendingCount > 0)
                        <a href="{{ route('complaints.print_backdate_pending') }}" target="_blank" class="btn btn-outline" style="height: 32px; padding: 0 12px; font-size: 12px;">Print back-date</a>
                        <a href="{{ route('complaints.export_backdate_pending') }}" class="btn btn-outline" style="height: 32px; padding: 0 12px; font-size: 12px;">Export Excel</a>
                    @endif
                </div>
                @endif
            </div>
        </form>
    </div>

    <!-- Pending Complaints DataTable -->
    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 0;">
        <table class="js-datatable" data-dt-empty="No inward records found for the selected filter.">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Inward date</th>
                        <th>Category / model</th>
                        <th>Customer</th>
                        <th>Battery serial</th>
                        <th style="min-width: 220px;">CRM case number</th>
                        <th>Print status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($complaints as $complaint)
                        <tr style="background-color: {{ $complaint->print_count === 0 ? '#ffffff' : '#fafafa' }};">
                            <td class="tabular" style="font-weight: 500; font-family: 'JetBrains Mono', monospace; font-size: 14px; color: var(--primary-dark);">
                                {{ $complaint->complaint_number }}
                            </td>

                            <td class="tabular" style="white-space: nowrap; color: var(--text-secondary);">
                                {{ $complaint->complaint_date ? $complaint->complaint_date->format('d-m-Y') : 'N/A' }}
                            </td>

                            <td>
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; color: var(--text-secondary);">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: {{ $complaint->battery_type === 'TW' ? '#d97706' : '#0284c7' }};"></span>
                                        {{ $complaint->battery_type }}
                                    </span>
                                    <span style="font-weight: 500; color: var(--text-main); font-family: 'JetBrains Mono', monospace;">{{ $complaint->product_model ?: '-' }}</span>
                                </div>

                                @if($complaint->online_reg_type === 'WARRANTY CARD')
                                    <span class="badge badge-info">Warranty card</span>
                                @elseif($complaint->online_reg_type === 'RC BOOK')
                                    <span class="badge badge-info">RC book</span>
                                @elseif($complaint->online_reg_type === 'NOT DOWNLOADED')
                                    <span class="badge badge-closed">Not downloaded</span>
                                @elseif(!empty($complaint->online_reg_type))
                                    <span class="badge badge-info">{{ $complaint->online_reg_type }}</span>
                                @else
                                    <span style="font-size: 12px; color: var(--text-muted);">Doc: -</span>
                                @endif
                            </td>

                            <td>
                                <div style="font-weight: 500; color: var(--text-main);">{{ $complaint->customer_name }}</div>
                                @if($complaint->customer_mobile)
                                    <div class="tabular" style="font-size: 12px; color: var(--text-muted); font-family: 'JetBrains Mono', monospace;">{{ $complaint->customer_mobile }}</div>
                                @elseif(!auth()->user()?->isViewer())
                                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                                        <input type="text" id="mobile_input_{{ $complaint->id }}" class="form-control" style="height: 28px; padding: 0 8px; font-size: 12px; font-family: 'JetBrains Mono', monospace; width: 116px; border-color: #d97706;" placeholder="Mobile no" maxlength="15">
                                        <button type="button" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;" onclick="saveCustomerMobile({{ $complaint->id }})">
                                            Save
                                        </button>
                                    </div>
                                @else
                                    <div style="font-size: 12px; color: var(--text-muted);">-</div>
                                @endif
                            </td>

                            <td style="font-family: 'JetBrains Mono', monospace; font-weight: 500; color: var(--text-main);">
                                {{ $complaint->battery_serial_number }}
                                @if(!empty($complaint->battery_connect_data['warranty_status']) && preg_match('/out\s*of\s*warranty|expired|no\s*warranty/i', $complaint->battery_connect_data['warranty_status']))
                                    <div style="margin-top: 4px;">
                                        <span class="badge badge-closed">Out of warranty (pro-rata)</span>
                                    </div>
                                @elseif($complaint->is_prorata)
                                    <div style="margin-top: 4px;">
                                        <span class="badge badge-pending">Pro-rata ({{ $complaint->prorata_percentage ? (float)$complaint->prorata_percentage . '%' : 'Yes' }})</span>
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if($complaint->isLeadTimeExceeded())
                                    <div style="margin-bottom: 6px;">
                                        <span class="badge badge-closed tabular">Lead time &gt; 90d ({{ $complaint->lead_time_days }}d) — do not enter</span>
                                    </div>
                                @endif
                                @if(auth()->user()?->isViewer())
                                    <span style="font-family: 'JetBrains Mono', monospace; color: var(--text-secondary);">
                                        {{ $complaint->case_number ?: '—' }}
                                    </span>
                                @else
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <input type="text" id="case_input_{{ $complaint->id }}" class="form-control" style="height: 32px; padding: 0 10px; font-size: 13px; font-family: 'JetBrains Mono', monospace; border-color: {{ $complaint->isLeadTimeExceeded() ? '#dc2626' : (empty($complaint->case_number) ? '#d97706' : 'rgba(23,23,23,0.16)') }};" value="{{ $complaint->case_number }}" placeholder="{{ $complaint->isLeadTimeExceeded() ? 'Lead time > 90d' : ($complaint->is_prorata ? 'Optional (pro-rata)' : 'CRM case no') }}">
                                        <button type="button" class="btn btn-outline" style="height: 32px; padding: 0 12px; font-size: 12px;" onclick="saveCaseNumber({{ $complaint->id }})">
                                            Save
                                        </button>
                                    </div>
                                @endif
                            </td>

                            <td style="white-space: nowrap;">
                                @if($complaint->print_count === 0)
                                    <span class="badge badge-closed tabular">Unprinted (0)</span>
                                @else
                                    <span class="badge badge-open tabular">Printed ({{ $complaint->print_count }})</span>
                                @endif
                            </td>

                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                    <a href="{{ route('complaints.print', $complaint->id) }}?autoprint=1" target="_blank" class="btn btn-primary" style="height: 32px; padding: 0 12px; font-size: 12px;" title="Print customer receipt (Increments print counter)">
                                        Print
                                    </a>
                                    <a href="{{ route('complaints.show', $complaint->id) }}" class="btn btn-outline" style="height: 32px; padding: 0 12px; font-size: 12px;">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @if($complaints->hasPages())
            <div style="padding: 16px; border-top: 1px solid var(--border-color);">
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
            showToast('CRM Case Number saved.', 'success');
            input.style.borderColor = '#16a34a';
            setTimeout(() => { input.style.borderColor = val ? 'rgba(23,23,23,0.16)' : '#d97706'; }, 2000);
        }
    } catch (err) {
        showToast('Failed to save CRM Case Number: ' + err.message, 'error');
    }
}

async function saveCustomerMobile(complaintId) {
    const input = document.getElementById('mobile_input_' + complaintId);
    if (!input) return;

    const val = input.value.trim();
    if (!val) {
        showToast('Please enter a mobile number first.', 'warning');
        return;
    }

    try {
        const data = await safeFetchJson("{{ url('/complaints') }}/" + complaintId + "/customer-mobile", {
            method: 'POST',
            body: JSON.stringify({ customer_mobile: val })
        });

        if (data.success) {
            showToast('Customer mobile saved.', 'success');
            input.style.borderColor = '#16a34a';
            setTimeout(() => { location.reload(); }, 800);
        }
    } catch (err) {
        showToast('Failed to save Customer Mobile: ' + err.message, 'error');
    }
}

async function saveRegType(complaintId, val) {
    try {
        const data = await safeFetchJson("{{ url('/complaints') }}/" + complaintId + "/reg-type", {
            method: 'POST',
            body: JSON.stringify({ online_reg_type: val })
        });

        if (data.success) {
            showToast('Warranty / RC status updated.', 'success');
        }
    } catch (err) {
        showToast('Failed to save Warranty / RC Status: ' + err.message, 'error');
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
            debounceTimer = setTimeout(autoSubmit, 450);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(debounceTimer);
                autoSubmit();
            }
        });
    }

    [statusSelect, dateSelect, batteryTypeSelect, docStatusSelect].forEach(el => {
        if (el) {
            el.addEventListener('change', autoSubmit);
        }
    });
});
</script>
@endpush
