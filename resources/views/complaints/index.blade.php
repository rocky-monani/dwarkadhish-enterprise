@extends('layouts.app')

@section('title', '📋 Battery Complaints List')

@section('content')
<div style="display: flex; flex-direction: column; gap: 1.25rem;">

    <!-- TOP NAVIGATION & ACTIONS -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.75rem;">
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main);">
                Battery Complaints
                <span style="background-color: var(--amaron-green); color: #ffffff; padding: 0.2rem 0.65rem; border-radius: 12px; font-size: 0.8rem; margin-left: 0.4rem;">
                    {{ $complaints->total() }}
                </span>
            </h2>
        </div>

        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            @if(!auth()->user()?->isViewer())
            <button type="button" onclick="startBatchFetchPending('ALL')" class="btn" style="background-color: var(--primary); color: #ffffff; font-size: 0.85rem; font-weight: 700; box-shadow: var(--shadow-sm);" title="Fetch Connect details & screenshots for all open pending complaints">
                ⚡ Fetch Pending Details
            </button>
            <button type="button" onclick="openBatchFetchModal()" class="btn btn-outline" style="font-size: 0.85rem; font-weight: 700; background-color: #ffffff;">
                📷 Fetch Range
            </button>
            @endif
            @if(auth()->user() && auth()->user()->isAdmin())
            <a href="{{ route('excel.import_view') }}" class="btn btn-outline" style="font-size: 0.85rem; font-weight: 700; background-color: #ffffff;">
                📥 Import Excel
            </a>
            @endif
            <a href="{{ route('excel.export_view') }}" class="btn btn-outline" style="font-size: 0.85rem; font-weight: 700; background-color: #ffffff;">
                📤 Export Excel
            </a>
            @if(!auth()->user()?->isViewer())
            <a href="{{ route('complaints.create') }}" class="btn btn-success" style="font-size: 0.85rem; font-weight: 800;">
                ➕ New Complaint Entry
            </a>
            @endif
        </div>
    </div>

    <!-- SEARCH & FILTER BAR CARD -->
    <div class="card" style="padding: 1rem 1.25rem; margin-bottom: 0;">
        <form id="filter_form" action="{{ route('complaints.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)) auto; gap: 0.85rem; align-items: flex-end;">
            <div>
                <label for="search" class="form-label" style="font-size: 0.78rem;">Search Keyword</label>
                <div style="display: flex; gap: 0.35rem;">
                    <input type="text" id="search" name="search" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.88rem;" value="{{ request('search') }}" placeholder="Search Serial / Case / Customer / Vehicle..." autocomplete="off">
                    <button type="submit" class="btn btn-primary" style="padding: 0.45rem 0.85rem; font-size: 0.85rem;">🔍 Search</button>
                </div>
            </div>

            <div>
                <label for="battery_type" class="form-label" style="font-size: 0.78rem;">Category (TW / 4W)</label>
                <select id="battery_type" name="battery_type" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.88rem;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <option value="TW" {{ strtoupper(request('battery_type', '')) === 'TW' ? 'selected' : '' }}>TW (2W)</option>
                    <option value="FW" {{ in_array(strtoupper(request('battery_type', '')), ['FW', '4W']) ? 'selected' : '' }}>FW (4W)</option>
                </select>
            </div>

            <div>
                <label for="status" class="form-label" style="font-size: 0.78rem;">Filter Status</label>
                <select id="status" name="status" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.88rem;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                    <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
                    <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div>
                <label for="date_from" class="form-label" style="font-size: 0.78rem;">Date From</label>
                <input type="date" id="date_from" name="date_from" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.88rem;" value="{{ request('date_from') }}">
            </div>

            <div>
                <label for="date_to" class="form-label" style="font-size: 0.78rem;">Date To</label>
                <input type="date" id="date_to" name="date_to" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.88rem;" value="{{ request('date_to') }}">
            </div>

            @if(request()->anyFilled(['search', 'status', 'battery_type', 'date_from', 'date_to']))
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('complaints.index') }}" class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">Reset Filters</a>
            </div>
            @endif
        </form>
    </div>

    <!-- RECORDS DATA TABLE CARD -->
    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 0;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em;">
                        <th style="padding: 0.85rem 1rem;">Date</th>
                        <th style="padding: 0.85rem 1rem;">Complaint #</th>
                        <th style="padding: 0.85rem 1rem;">CRM Case #</th>
                        <th style="padding: 0.85rem 1rem;">Serial Number</th>
                        <th style="padding: 0.85rem 1rem;">Product Model</th>
                        @if(auth()->user() && auth()->user()->isAdmin())
                        <th style="padding: 0.85rem 1rem;">Customer Name & City</th>
                        <th style="padding: 0.85rem 1rem;">Vehicle Reg</th>
                        <th style="padding: 0.85rem 1rem;">Delivery Mode</th>
                        <th style="padding: 0.85rem 1rem;">PR %</th>
                        @endif
                        <th style="padding: 0.85rem 1rem; text-align: center;">Status</th>
                        <th style="padding: 0.85rem 1rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $c)
                    <tr style="border-bottom: 1px solid var(--border-color); transition: background-color 0.15s ease;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                        <td style="padding: 0.85rem 1rem; font-weight: 600; white-space: nowrap;">
                            {{ $c->complaint_date ? $c->complaint_date->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td style="padding: 0.85rem 1rem; font-weight: 800; color: var(--amaron-green-dark);">
                            <a href="{{ route('complaints.show', $c->id) }}" style="color: inherit; text-decoration: none;">
                                #{{ $c->complaint_number }}
                            </a>
                        </td>
                        <td style="padding: 0.85rem 1rem; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: var(--text-secondary);">
                            {{ $c->case_number ?? '—' }}
                        </td>
                        <td style="padding: 0.85rem 1rem; font-family: 'JetBrains Mono', monospace; font-weight: 800; color: var(--text-main);">
                            {{ $c->battery_serial_number }}
                        </td>
                        <td style="padding: 0.85rem 1rem; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: var(--primary);">
                            {{ $c->product_model ?? '—' }}
                        </td>
                        @if(auth()->user() && auth()->user()->isAdmin())
                        <td style="padding: 0.85rem 1rem; font-weight: 700;">
                            {{ $c->customer_name }}
                            @if($c->location || $c->customer_mobile)
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                                    {{ implode(' • ', array_filter([$c->location, $c->customer_mobile])) }}
                                </div>
                            @endif
                        </td>
                        <td style="padding: 0.85rem 1rem; font-family: 'JetBrains Mono', monospace; font-weight: 700;">
                            {{ $c->vehicle_number ?? '—' }}
                        </td>
                        <td style="padding: 0.85rem 1rem; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);">
                            {{ $c->delivery_mode ?? '—' }}
                        </td>
                        <td style="padding: 0.85rem 1rem;">
                            @if($c->is_prorata)
                                <span style="background-color: #fef3c7; color: #b45309; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 800; font-size: 0.75rem;">
                                    {{ $c->prorata_percentage }}%
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.78rem;">—</span>
                            @endif
                        </td>
                        @endif
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            @if($c->complaint_status === 'Open')
                                <span style="background-color: #fef3c7; color: #92400e; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.75rem; font-weight: 800;">OPEN</span>
                            @elseif($c->complaint_status === 'Closed')
                                <span style="background-color: #d1fae5; color: #065f46; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.75rem; font-weight: 800;">CLOSED</span>
                            @else
                                <span style="background-color: #fee2e2; color: #991b1b; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.75rem; font-weight: 800;">{{ strtoupper($c->complaint_status) }}</span>
                            @endif
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: right; white-space: nowrap;">
                            <div style="display: flex; gap: 0.4rem; justify-content: flex-end; align-items: center;">
                                <a href="{{ route('complaints.show', $c->id) }}" class="btn btn-outline" style="padding: 0.25rem 0.55rem; font-size: 0.78rem;" title="View Detail">👁️ View</a>
                                <a href="{{ route('complaints.print', $c->id) }}" class="btn btn-outline" style="padding: 0.25rem 0.55rem; font-size: 0.78rem;" title="Print A4">🖨️ Print</a>
                                @if(!auth()->user()?->isViewer())
                                    <a href="{{ route('complaints.edit', $c->id) }}" class="btn btn-outline" style="padding: 0.25rem 0.55rem; font-size: 0.78rem;" title="Edit Record">✏️ Edit</a>
                                    @if($c->complaint_status !== 'Open')
                                        <form action="{{ route('complaints.reopen', $c->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Reopen Complaint #{{ $c->complaint_number }}?');">
                                            @csrf
                                            <button type="submit" class="btn" style="padding: 0.25rem 0.55rem; font-size: 0.78rem; background-color: #d97706; color: #ffffff;" title="Reopen Case">🔓 Reopen</button>
                                        </form>
                                    @endif
                                    @if(auth()->user() && auth()->user()->isAdmin())
                                    <button type="button" class="btn btn-danger" style="padding: 0.25rem 0.55rem; font-size: 0.78rem;" onclick="confirmDelete('{{ $c->id }}', '{{ $c->complaint_number }}')">🗑️</button>
                                    <form id="delete_form_{{ $c->id }}" action="{{ route('complaints.destroy', $c->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ (auth()->user() && auth()->user()->isAdmin()) ? '11' : '7' }}" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🛵</div>
                            <div style="font-weight: 700; font-size: 1rem; color: var(--text-main);">No Records Found</div>
                            <div style="font-size: 0.85rem; margin-top: 0.25rem;">Start by creating a new battery complaint entry.</div>
                            <a href="{{ route('complaints.create') }}" class="btn btn-success" style="margin-top: 1rem; font-size: 0.85rem;">➕ Create First Entry</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($complaints->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color);">
            {{ $complaints->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id, complaintNo) {
    showConfirmModal(
        'Delete Complaint #' + complaintNo,
        'Are you sure you want to delete this complaint record?',
        function() {
            document.getElementById('delete_form_' + id).submit();
        }
    );
}

document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter_form');
    if (!filterForm) return;

    const statusSelect = document.getElementById('status');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');

    function autoSubmit() {
        filterForm.submit();
    }

    [statusSelect, dateFromInput, dateToInput].forEach(elem => {
        if (elem) {
            elem.addEventListener('change', autoSubmit);
        }
    });
});
</script>
@endpush
