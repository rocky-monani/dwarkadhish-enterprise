@extends('layouts.app')

@section('title', 'Battery Complaints')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Header & Actions -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <h2 style="font-size: 20px; font-weight: 600; color: var(--text-main); margin: 0;">
                Battery complaints
            </h2>
            <span class="badge badge-info tabular" style="font-family: 'JetBrains Mono', monospace;">
                {{ $complaints->total() }}
            </span>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            @if(auth()->user() && auth()->user()->isAdmin())
            <a href="{{ route('excel.import_view') }}" class="btn btn-outline">
                Import Excel
            </a>
            @endif
            <a href="{{ route('excel.export_view') }}" class="btn btn-outline">
                Export Excel
            </a>
            @if(!auth()->user()?->isViewer())
            <a href="{{ route('complaints.create') }}" class="btn btn-primary">
                New inward
            </a>
            @endif
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card" style="padding: 16px 20px; margin-bottom: 0;">
        <form id="filter_form" action="{{ route('complaints.index') }}" method="GET" style="display: grid; grid-template-columns: 2fr repeat(4, minmax(140px, 1fr)) auto; gap: 12px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="search" class="form-label">Search</label>
                <div style="display: flex; gap: 6px;">
                    <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Serial, case, customer, vehicle" autocomplete="off">
                    <button type="submit" class="btn btn-outline" style="padding: 0 12px;">Search</button>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="battery_type" class="form-label">Category</label>
                <select id="battery_type" name="battery_type" class="form-control" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    <option value="TW" {{ strtoupper(request('battery_type', '')) === 'TW' ? 'selected' : '' }}>TW (2W)</option>
                    <option value="FW" {{ in_array(strtoupper(request('battery_type', '')), ['FW', '4W']) ? 'selected' : '' }}>FW (4W)</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                    <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
                    <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="date_from" class="form-label">Date from</label>
                <input type="date" id="date_from" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="date_to" class="form-label">Date to</label>
                <input type="date" id="date_to" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>

            @if(request()->anyFilled(['search', 'status', 'battery_type', 'date_from', 'date_to']))
            <div>
                <a href="{{ route('complaints.index') }}" class="btn btn-outline">Reset</a>
            </div>
            @endif
        </form>
    </div>

    <!-- Records DataTable -->
    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 0;">
        <table class="js-datatable" data-dt-empty="No records found. Start by creating a new battery complaint entry.">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Complaint</th>
                        <th>CRM case</th>
                        <th>Serial number</th>
                        <th>Model</th>
                        @if(auth()->user() && auth()->user()->isAdmin())
                        <th>Customer &amp; city</th>
                        <th>Vehicle reg</th>
                        <th>Delivery mode</th>
                        <th>PR %</th>
                        @endif
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($complaints as $c)
                    <tr>
                        <td class="tabular" style="white-space: nowrap; color: var(--text-secondary);">
                            {{ $c->complaint_date ? $c->complaint_date->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td class="tabular" style="font-weight: 500; color: var(--primary-dark); font-family: 'JetBrains Mono', monospace;">
                            <a href="{{ route('complaints.show', $c->id) }}" style="color: inherit; text-decoration: none;">
                                #{{ $c->complaint_number }}
                            </a>
                        </td>
                        <td class="tabular" style="font-family: 'JetBrains Mono', monospace; color: var(--text-secondary);">
                            {{ $c->case_number ?? '—' }}
                        </td>
                        <td style="font-family: 'JetBrains Mono', monospace; font-weight: 500; color: var(--text-main);">
                            {{ $c->battery_serial_number }}
                        </td>
                        <td style="font-family: 'JetBrains Mono', monospace; color: var(--text-secondary);">
                            {{ $c->product_model ?? '—' }}
                        </td>
                        @if(auth()->user() && auth()->user()->isAdmin())
                        <td>
                            <div style="font-weight: 500; color: var(--text-main);">{{ $c->customer_name }}</div>
                            @if($c->location || $c->customer_mobile)
                                <div class="tabular" style="font-size: 12px; color: var(--text-muted);">
                                    {{ implode(' · ', array_filter([$c->location, $c->customer_mobile])) }}
                                </div>
                            @endif
                        </td>
                        <td style="font-family: 'JetBrains Mono', monospace; color: var(--text-secondary);">
                            {{ $c->vehicle_number ?? '—' }}
                        </td>
                        <td style="font-size: 12px; color: var(--text-secondary);">
                            {{ $c->delivery_mode ?? '—' }}
                        </td>
                        <td>
                            @if($c->is_prorata)
                                <span class="badge badge-pending tabular">{{ $c->prorata_percentage }}%</span>
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                        @endif
                        <td style="text-align: center;">
                            @if($c->complaint_status === 'Open')
                                <span class="badge badge-open">Open</span>
                            @elseif($c->complaint_status === 'Closed')
                                <span class="badge badge-info">Closed</span>
                            @else
                                <span class="badge badge-closed">{{ ucfirst(strtolower($c->complaint_status)) }}</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                <a href="{{ route('complaints.show', $c->id) }}" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;">View</a>
                                <a href="{{ route('complaints.print', $c->id) }}" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;">Print</a>
                                @if(!auth()->user()?->isViewer())
                                    <a href="{{ route('complaints.edit', $c->id) }}" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;">Edit</a>
                                    @if($c->complaint_status !== 'Open')
                                        <form action="{{ route('complaints.reopen', $c->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Reopen Complaint #{{ $c->complaint_number }}?');">
                                            @csrf
                                            <button type="submit" class="btn btn-warning" style="height: 28px; padding: 0 10px; font-size: 12px;">Reopen</button>
                                        </form>
                                    @endif
                                    @if(auth()->user() && auth()->user()->isAdmin())
                                    <button type="button" class="btn btn-danger" style="height: 28px; padding: 0 10px; font-size: 12px;" onclick="confirmDelete('{{ $c->id }}', '{{ $c->complaint_number }}')">Delete</button>
                                    <form id="delete_form_{{ $c->id }}" action="{{ route('complaints.destroy', $c->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        @if($complaints->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--border-color);">
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
