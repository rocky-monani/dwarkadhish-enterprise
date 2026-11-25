@extends('layouts.app')

@section('title', 'Complaint Record #' . $complaint->complaint_number)

@section('content')
    <div style="max-width: 950px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">

        <!-- TOP ACTION & META BAR -->
        <div class="card" style="margin-bottom: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <a href="{{ route('complaints.index') }}"
                        style="text-decoration: none; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); display: inline-flex; align-items: center; gap: 0.35rem;">
                        ← Back to Records List
                    </a>
                    <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text-main); margin-top: 0.25rem;">
                        Battery Complaint Record #{{ $complaint->complaint_number }}
                    </h2>
                </div>

                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    @if(!auth()->user()?->isViewer())
                        @if($complaint->complaint_status !== 'Open')
                            <form action="{{ route('complaints.reopen', $complaint->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to REOPEN Complaint #{{ $complaint->complaint_number }}?');">
                                @csrf
                                <button type="submit" class="btn" style="background-color: #d97706; color: #ffffff; font-weight: 700;">
                                    🔓 Reopen Case
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('complaints.edit', $complaint->id) }}" class="btn btn-outline">Edit Record</a>
                    @endif
                    <a href="{{ route('complaints.print', $complaint->id) }}" class="btn btn-primary" target="_blank">🖨️ Print A4 Document</a>
                </div>
            </div>
        </div>

        <!-- METADATA SUMMARY GRID -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">

            <!-- Complaint Overview & Category -->
            <div class="card" style="margin-bottom: 0;">
                <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.85rem;">
                    Complaint Overview</h4>

                <div style="display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.9rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Complaint Number:</span>
                        <span style="font-weight: 800; color: var(--primary);">#{{ $complaint->complaint_number }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Battery Category:</span>
                        <span style="font-weight: 800; color: var(--primary);">{{ $complaint->battery_type === 'FW' ? '🚗 FW (Four Wheeler)' : '🏍️ TW (Two Wheeler)' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">CRM Case Number:</span>
                        <span style="font-weight: 700;">{{ $complaint->case_number ?: '-' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Warranty Card / RC Status:</span>
                        <span style="font-weight: 700; color: var(--primary);">{{ $complaint->online_reg_type ?: '-' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Complaint Date:</span>
                        <span style="font-weight: 600;">{{ $complaint->complaint_date ? $complaint->complaint_date->format('d/m/Y') : '-' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-secondary);">Status:</span>
                        <span style="font-weight: 700; color: {{ $complaint->complaint_status === 'Closed' ? '#991b1b' : ($complaint->complaint_status === 'Rejected' ? '#b91c1c' : '#047857') }};">
                            {{ $complaint->complaint_status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer, Location & Delivery Info -->
            <div class="card" style="margin-bottom: 0;">
                <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.85rem;">
                    Customer, Location & Vehicle</h4>

                <div style="display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.9rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Customer Name:</span>
                        <span style="font-weight: 700;">{{ $complaint->customer_name }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Mobile Number:</span>
                        <span style="font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ $complaint->customer_mobile ?: '-' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Customer Location:</span>
                        <span style="font-weight: 600;">{{ $complaint->location ?: '-' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Vehicle Model & Reg:</span>
                        <span style="font-weight: 600;">{{ $complaint->vehicle_name ?: '-' }} ({{ $complaint->vehicle_number ?: '-' }})</span>
                    </div>

                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-secondary);">Delivery / Take Mode:</span>
                        <span style="font-weight: 600;">{{ $complaint->delivery_mode ?: '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Battery, Product & Settlement Details -->
            <div class="card" style="margin-bottom: 0;">
                <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.85rem;">
                    Battery & Settlement Details</h4>

                <div style="display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.9rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Battery Serial:</span>
                        <span style="font-family: 'JetBrains Mono', monospace; font-weight: 800; color: var(--text-main);">{{ $complaint->battery_serial_number }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Product Model / Segment:</span>
                        <span style="font-weight: 800; color: var(--primary);">{{ $complaint->product_model ?: '-' }} [{{ $complaint->product_segment ?: 'TW' }}]</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Purchase Date:</span>
                        <span style="font-weight: 600;">{{ $complaint->purchase_date ? $complaint->purchase_date->format('d/m/Y') : '-' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Lead Time 1 (Days):</span>
                        @if($complaint->isLeadTimeExceeded())
                            <span style="font-weight: 800; color: #dc2626; background-color: #fee2e2; padding: 0.1rem 0.4rem; border-radius: 4px;">
                                ⚠️ {{ $complaint->lead_time_days }} Days (> 90 Days)
                            </span>
                        @else
                            <span style="font-weight: 700;">{{ $complaint->lead_time_days !== null ? $complaint->lead_time_days . ' Days' : '-' }}</span>
                        @endif
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Standby Battery Issued:</span>
                        <span style="font-weight: 700;">{{ $complaint->is_service_battery_given ? ('Yes (' . ($complaint->service_battery_number ?: 'N/A') . ')') : 'No' }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Pro-Rata Settlement:</span>
                        @if(!empty($complaint->battery_connect_data['warranty_status']) && preg_match('/out\s*of\s*warranty|expired|no\s*warranty/i', $complaint->battery_connect_data['warranty_status']))
                            <span style="font-weight: 800; color: #dc2626; background-color: #fee2e2; padding: 0.1rem 0.4rem; border-radius: 4px;">
                                ⚠️ Out of Warranty (Pro-Rata)
                            </span>
                        @elseif($complaint->is_prorata)
                            <span style="font-weight: 800; color: #b45309; background-color: #fef3c7; padding: 0.1rem 0.4rem; border-radius: 4px;">
                                {{ $complaint->prorata_percentage ? $complaint->prorata_percentage . '%' : 'Yes' }}
                            </span>
                        @else
                            <span style="font-weight: 600; color: var(--text-muted);">Off</span>
                        @endif
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                        <span style="color: var(--text-secondary);">Final Decision:</span>
                        <span style="font-weight: 700; color: var(--primary);">{{ $complaint->final_decision ?: '-' }} @if($complaint->decision_date)({{ $complaint->decision_date->format('d/m/Y') }})@endif</span>
                    </div>

                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-secondary);">Closing / Return Date:</span>
                        <span style="font-weight: 600;">
                            Closing: {{ $complaint->closing_date ? $complaint->closing_date->format('d/m/Y') : '-' }} |
                            Return: {{ $complaint->return_date ? $complaint->return_date->format('d/m/Y') : '-' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- STORED BATTERY CONNECT CAPTURE EVIDENCE CARD -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <polyline points="21 15 16 10 5 21" />
                    </svg>
                    Battery Connect Captured Result (Stored Evidence)
                </h3>

                @if($complaint->battery_connect_lookup_at)
                    <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted);">
                        Captured on {{ $complaint->battery_connect_lookup_at->format('d/m/Y H:i') }}
                    </span>
                @endif
            </div>

            @if($complaint->battery_connect_image_path)
                <div
                    style="display: flex; justify-content: center; background-color: #f1f5f9; padding: 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <img src="{{ asset($complaint->battery_connect_image_path) }}" alt="Battery Connect Capture"
                        style="max-width: 100%; height: auto; border-radius: 4px; box-shadow: var(--shadow-md);">
                </div>
            @else
                <div
                    style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); background-color: #f8fafc; border-radius: var(--radius-sm); border: 1px dashed var(--border-color);">
                    <p style="font-weight: 600;">No Battery Connect screenshot attached to this record.</p>
                </div>
            @endif
        </div>

    </div>
@endsection