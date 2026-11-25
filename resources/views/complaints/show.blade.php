@extends('layouts.app')

@section('title', 'Complaint #' . $complaint->complaint_number)

@section('content')
    <div style="max-width: 960px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">

        <!-- Top Action & Meta Bar -->
        <div class="card" style="margin-bottom: 0; padding: 20px 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <a href="{{ route('complaints.index') }}"
                        style="text-decoration: none; font-size: 13px; color: var(--text-secondary); display: inline-flex; align-items: center; gap: 4px;">
                        ← Back to complaints
                    </a>
                    <h2 class="tabular" style="font-size: 20px; font-weight: 600; color: var(--text-main); margin-top: 4px;">
                        Complaint #{{ $complaint->complaint_number }}
                    </h2>
                </div>

                <div style="display: flex; gap: 8px; align-items: center;">
                    @if(!auth()->user()?->isViewer())
                        @if($complaint->complaint_status !== 'Open')
                            <form action="{{ route('complaints.reopen', $complaint->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to reopen Complaint #{{ $complaint->complaint_number }}?');">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    Reopen case
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('complaints.edit', $complaint->id) }}" class="btn btn-outline">Edit record</a>
                    @endif
                    <a href="{{ route('complaints.print', $complaint->id) }}" class="btn btn-primary" target="_blank">Print A4</a>
                </div>
            </div>
        </div>

        <!-- Metadata Summary Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">

            <!-- Complaint Overview -->
            <div class="card" style="margin-bottom: 0; padding: 20px;">
                <h4 style="font-size: 13px; font-weight: 600; color: var(--text-main); margin-bottom: 16px;">
                    Complaint overview
                </h4>

                <div class="tabular" style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Complaint number</span>
                        <span style="font-weight: 500; font-family: 'JetBrains Mono', monospace; color: var(--primary-dark);">#{{ $complaint->complaint_number }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Battery category</span>
                        <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 500; color: var(--text-main);">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background-color: {{ $complaint->battery_type === 'FW' ? '#0284c7' : '#d97706' }};"></span>
                            {{ $complaint->battery_type === 'FW' ? 'FW (Four wheeler)' : 'TW (Two wheeler)' }}
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">CRM case number</span>
                        <span style="font-family: 'JetBrains Mono', monospace;">{{ $complaint->case_number ?: '-' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Warranty / RC status</span>
                        <span>{{ $complaint->online_reg_type ?: '-' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Complaint date</span>
                        <span>{{ $complaint->complaint_date ? $complaint->complaint_date->format('d/m/Y') : '-' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-secondary);">Status</span>
                        <span class="badge {{ $complaint->complaint_status === 'Open' ? 'badge-open' : ($complaint->complaint_status === 'Closed' ? 'badge-info' : 'badge-closed') }}">
                            {{ $complaint->complaint_status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer, Location & Vehicle -->
            <div class="card" style="margin-bottom: 0; padding: 20px;">
                <h4 style="font-size: 13px; font-weight: 600; color: var(--text-main); margin-bottom: 16px;">
                    Customer &amp; vehicle
                </h4>

                <div class="tabular" style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Customer name</span>
                        <span style="font-weight: 500;">{{ $complaint->customer_name }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Mobile number</span>
                        <span style="font-family: 'JetBrains Mono', monospace;">{{ $complaint->customer_mobile ?: '-' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Location</span>
                        <span>{{ $complaint->location ?: '-' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Vehicle</span>
                        <span>{{ $complaint->vehicle_name ?: '-' }} ({{ $complaint->vehicle_number ?: '-' }})</span>
                    </div>

                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-secondary);">Delivery mode</span>
                        <span>{{ $complaint->delivery_mode ?: '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Battery & Settlement Details -->
            <div class="card" style="margin-bottom: 0; padding: 20px;">
                <h4 style="font-size: 13px; font-weight: 600; color: var(--text-main); margin-bottom: 16px;">
                    Battery &amp; settlement
                </h4>

                <div class="tabular" style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Battery serial</span>
                        <span style="font-family: 'JetBrains Mono', monospace; font-weight: 500;">{{ $complaint->battery_serial_number }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Product model</span>
                        <span style="font-family: 'JetBrains Mono', monospace;">{{ $complaint->product_model ?: '-' }} [{{ $complaint->product_segment ?: 'TW' }}]</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Purchase date</span>
                        <span>{{ $complaint->purchase_date ? $complaint->purchase_date->format('d/m/Y') : '-' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Lead time 1</span>
                        @if($complaint->isLeadTimeExceeded())
                            <span class="badge badge-closed">{{ $complaint->lead_time_days }} days (&gt; 90 days)</span>
                        @else
                            <span>{{ $complaint->lead_time_days !== null ? $complaint->lead_time_days . ' days' : '-' }}</span>
                        @endif
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Standby battery</span>
                        <span>{{ $complaint->is_service_battery_given ? ('Yes (' . ($complaint->service_battery_number ?: 'N/A') . ')') : 'No' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Pro-rata</span>
                        @if(!empty($complaint->battery_connect_data['warranty_status']) && preg_match('/out\s*of\s*warranty|expired|no\s*warranty/i', $complaint->battery_connect_data['warranty_status']))
                            <span class="badge badge-closed">Out of warranty (pro-rata)</span>
                        @elseif($complaint->is_prorata)
                            <span class="badge badge-pending">{{ $complaint->prorata_percentage ? $complaint->prorata_percentage . '%' : 'Yes' }}</span>
                        @else
                            <span style="color: var(--text-muted);">Off</span>
                        @endif
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Final decision</span>
                        <span>{{ $complaint->final_decision ?: '-' }} @if($complaint->decision_date)({{ $complaint->decision_date->format('d/m/Y') }})@endif</span>
                    </div>

                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-secondary);">Closing / return</span>
                        <span>
                            {{ $complaint->closing_date ? $complaint->closing_date->format('d/m/Y') : '-' }} /
                            {{ $complaint->return_date ? $complaint->return_date->format('d/m/Y') : '-' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Stored Battery Connect Capture -->
        <div class="card" style="margin-bottom: 0; padding: 20px;">
            <div class="card-header">
                <h3 class="card-title">Battery Connect captured evidence</h3>

                @if($complaint->battery_connect_lookup_at)
                    <span class="tabular" style="font-size: 12px; color: var(--text-muted);">
                        Captured {{ $complaint->battery_connect_lookup_at->format('d/m/Y H:i') }}
                    </span>
                @endif
            </div>

            @if($complaint->battery_connect_image_path)
                <div style="display: flex; justify-content: center; background-color: var(--bg-color); padding: 16px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <img src="{{ asset($complaint->battery_connect_image_path) }}" alt="Battery Connect Capture"
                        style="max-width: 100%; height: auto; border-radius: 6px; box-shadow: var(--shadow-sm);">
                </div>
            @else
                <div style="text-align: center; padding: 36px 16px; color: var(--text-muted); background-color: var(--bg-color); border-radius: 8px; border: 1px dashed var(--border-strong);">
                    <p style="font-size: 13px;">No Battery Connect screenshot attached to this record.</p>
                </div>
            @endif
        </div>

    </div>
@endsection
