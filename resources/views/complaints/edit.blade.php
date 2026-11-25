@extends('layouts.app')

@section('title', 'Edit Record - ' . $complaint->complaint_number)

@section('content')
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">✏️ EDIT RECORD: {{ $complaint->complaint_number }}</h2>
            <a href="{{ route('complaints.show', $complaint->id) }}" class="btn btn-outline">⬅️ CANCEL</a>
        </div>

        <form action="{{ route('complaints.update', $complaint->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- SECTION 1: CORE COMPLAINT IDENTIFICATION -->
            <div
                style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--primary); margin-bottom: 0.75rem;">
                    📌 Core Complaint Identification
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="battery_type">Battery Category / Type <span
                                style="color:red;">*</span></label>
                        <select name="battery_type" id="battery_type" class="form-control"
                            style="font-weight: 800; color: var(--primary);">
                            <option value="TW" {{ old('battery_type', $complaint->battery_type) === 'TW' ? 'selected' : '' }}>
                                🏍️ TW (Two Wheeler)</option>
                            <option value="FW" {{ old('battery_type', $complaint->battery_type) === 'FW' ? 'selected' : '' }}>
                                🚗 FW (Four Wheeler / Others)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="battery_serial_number">Battery Serial Number <span
                                style="color:red;">*</span></label>
                        <input type="text" name="battery_serial_number" id="battery_serial_number" class="form-control"
                            style="font-family: 'JetBrains Mono', monospace; font-weight: 700; text-transform: uppercase;"
                            value="{{ old('battery_serial_number', $complaint->battery_serial_number) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="complaint_number">Complaint Number <span
                                style="color:red;">*</span></label>
                        <input type="text" name="complaint_number" id="complaint_number" class="form-control"
                            style="font-family: 'JetBrains Mono', monospace; font-weight: 700;"
                            value="{{ old('complaint_number', $complaint->complaint_number) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="case_number">CRM Case Number (Optional)</label>
                        <input type="text" name="case_number" id="case_number" class="form-control"
                            style="font-family: 'JetBrains Mono', monospace;"
                            value="{{ old('case_number', $complaint->case_number) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="complaint_date">Complaint Date <span
                                style="color:red;">*</span></label>
                        <input type="date" name="complaint_date" id="complaint_date" class="form-control"
                            value="{{ old('complaint_date', $complaint->complaint_date ? $complaint->complaint_date->format('Y-m-d') : '') }}"
                            required>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: CUSTOMER & LOCATION INFORMATION -->
            <div
                style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--primary); margin-bottom: 0.75rem;">
                    👤 Customer & Location Details
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="customer_name">Customer Name <span
                                style="color:red;">*</span></label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control"
                            value="{{ old('customer_name', $complaint->customer_name) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="customer_mobile">Customer Mobile Number (Optional)</label>
                        <input type="text" name="customer_mobile" id="customer_mobile" class="form-control"
                            style="font-family: 'JetBrains Mono', monospace;"
                            value="{{ old('customer_mobile', $complaint->customer_mobile) }}" maxlength="15">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="location">Customer Location / City (Optional)</label>
                        <input type="text" name="location" id="location" class="form-control"
                            value="{{ old('location', $complaint->location) }}"
                            placeholder="e.g. Jamnagar / Lalpur / Dhrol">
                    </div>
                </div>
            </div>

            <!-- SECTION 3: PRODUCT & PURCHASE SPECIFICATIONS -->
            <div
                style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--primary); margin-bottom: 0.75rem;">
                    🔋 Product, Purchase & Delivery Information
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="product_model">Product Model / Type</label>
                        <input type="text" name="product_model" id="product_model" class="form-control"
                            style="font-weight: 700;" value="{{ old('product_model', $complaint->product_model) }}"
                            placeholder="e.g. APBTZ4A / AP-BTZ5L">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="product_segment">Product Segment (PROD.SEG.)</label>
                        <select name="product_segment" id="product_segment" class="form-control"
                            style="font-weight: 800; color: var(--primary);">
                            <option value="TW" {{ old('product_segment', $complaint->product_segment) === 'TW' ? 'selected' : '' }}>TW (Two Wheeler)</option>
                            <option value="FW" {{ old('product_segment', $complaint->product_segment) === 'FW' ? 'selected' : '' }}>FW (Four Wheeler)</option>
                            <option value="INV." {{ old('product_segment', $complaint->product_segment) === 'INV.' ? 'selected' : '' }}>INV. (Inverter)</option>
                            <option value="HUPS" {{ old('product_segment', $complaint->product_segment) === 'HUPS' ? 'selected' : '' }}>HUPS (Home UPS)</option>
                            <option value="TT" {{ old('product_segment', $complaint->product_segment) === 'TT' ? 'selected' : '' }}>TT (Tubular / Tractor)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="purchase_date">Purchase Date / Date of Sale</label>
                        <input type="date" name="purchase_date" id="purchase_date" class="form-control"
                            value="{{ old('purchase_date', $complaint->purchase_date ? $complaint->purchase_date->format('Y-m-d') : '') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="delivery_mode">Delivery / Take Person Mode</label>
                        <input type="text" name="delivery_mode" id="delivery_mode" class="form-control"
                            value="{{ old('delivery_mode', $complaint->delivery_mode) }}"
                            placeholder="e.g. BY HIMSELF / SEND IN ROUTE">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="vehicle_name">Vehicle Name / Model</label>
                        <input type="text" name="vehicle_name" id="vehicle_name" class="form-control"
                            value="{{ old('vehicle_name', $complaint->vehicle_name) }}"
                            placeholder="e.g. Activa / Splendor / Pulsar">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="vehicle_number">Vehicle Registration Number</label>
                        <input type="text" name="vehicle_number" id="vehicle_number" class="form-control"
                            style="font-family: 'JetBrains Mono', monospace; text-transform: uppercase;"
                            value="{{ old('vehicle_number', $complaint->vehicle_number) }}" placeholder="e.g. GJ10AB1234">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="online_reg_type" style="font-weight: 700;">Warranty Card / RC Book
                            Status</label>
                        <select name="online_reg_type" id="online_reg_type" class="form-control" style="font-weight: 700;">
                            <option value="">-- Select Download Status --</option>
                            <option value="WARRANTY CARD" {{ old('online_reg_type', $complaint->online_reg_type) === 'WARRANTY CARD' ? 'selected' : '' }}>📄 WARRANTY CARD</option>
                            <option value="RC BOOK" {{ old('online_reg_type', $complaint->online_reg_type) === 'RC BOOK' ? 'selected' : '' }}>🚘 RC BOOK</option>
                            <option value="NOT DOWNLOADED" {{ old('online_reg_type', $complaint->online_reg_type) === 'NOT DOWNLOADED' ? 'selected' : '' }}>❌ NOT DOWNLOADED</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: WARRANTY & SERVICE BATTERY OPTIONS -->
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                <!-- Digital Warranty Toggle -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 1rem;">
                    <label
                        style="font-weight: 700; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_digital_warranty" value="1" {{ old('is_digital_warranty', $complaint->is_digital_warranty) ? 'checked' : '' }}>
                        <span>📱 Digital Warranty Registered</span>
                    </label>
                </div>

                <!-- Standby / Service Battery Toggle -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 1rem;">
                    <label
                        style="font-weight: 700; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_service_battery_given" id="edit_is_service" value="1" {{ old('is_service_battery_given', $complaint->is_service_battery_given) ? 'checked' : '' }}>
                        <span>🔋 Service / Standby Battery Issued</span>
                    </label>
                    <div id="edit_service_wrap"
                        style="display: {{ old('is_service_battery_given', $complaint->is_service_battery_given) ? 'block' : 'none' }}; margin-top: 0.65rem;">
                        <input type="text" name="service_battery_number" id="service_battery_number" class="form-control"
                            style="font-family: 'JetBrains Mono', monospace; text-transform: uppercase;"
                            placeholder="Standby Battery Serial Number"
                            value="{{ old('service_battery_number', $complaint->service_battery_number) }}">
                    </div>
                </div>

                <!-- Pro-Rata Settlement Toggle -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 1rem;">
                    <label
                        style="font-weight: 700; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_prorata" id="edit_is_prorata" value="1" {{ old('is_prorata', $complaint->is_prorata) ? 'checked' : '' }}>
                        <span style="color: #b45309;">📊 Pro-Rata Discount Applicable</span>
                    </label>
                    <div id="edit_prorata_wrap"
                        style="display: {{ old('is_prorata', $complaint->is_prorata) ? 'block' : 'none' }}; margin-top: 0.65rem;">
                        <input type="number" step="0.01" min="0" max="100" name="prorata_percentage" id="prorata_percentage"
                            class="form-control" style="font-weight: 700; color: #b45309;"
                            value="{{ old('prorata_percentage', $complaint->prorata_percentage ? (float) $complaint->prorata_percentage : '') }}"
                            placeholder="Pro-Rata % (e.g. 42 or 45)">
                    </div>
                </div>
            </div>

            <!-- SECTION 5: SETTLEMENT & CLOSING INFORMATION -->
            <div
                style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--secondary); margin-bottom: 0.75rem;">
                    🔒 Settlement & Closing Information
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="complaint_status">Complaint Status</label>
                        <select name="complaint_status" id="complaint_status" class="form-control"
                            style="font-weight: 700;">
                            <option value="Open" {{ old('complaint_status', $complaint->complaint_status) === 'Open' ? 'selected' : '' }}>🟢 Open</option>
                            <option value="Closed" {{ old('complaint_status', $complaint->complaint_status) === 'Closed' ? 'selected' : '' }}>🔒 Closed</option>
                            <option value="Rejected" {{ old('complaint_status', $complaint->complaint_status) === 'Rejected' ? 'selected' : '' }}>❌ Rejected</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="final_decision">Final Decision</label>
                        <select name="final_decision" id="final_decision" class="form-control" style="font-weight: 700;">
                            <option value="">-- Select Decision --</option>
                            <option value="F" {{ old('final_decision', $complaint->final_decision) === 'F' ? 'selected' : '' }}>F (Free Rep.)</option>
                            <option value="P" {{ old('final_decision', $complaint->final_decision) === 'P' ? 'selected' : '' }}>P (Pro-Rata)</option>
                            <option value="C" {{ old('final_decision', $complaint->final_decision) === 'C' ? 'selected' : '' }}>C (Charging)</option>
                            <option value="R" {{ old('final_decision', $complaint->final_decision) === 'R' ? 'selected' : '' }}>R (Dead Return)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="decision_date">Decision Date</label>
                        <input type="date" name="decision_date" id="decision_date" class="form-control"
                            value="{{ old('decision_date', $complaint->decision_date ? $complaint->decision_date->format('Y-m-d') : '') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="new_battery_number">New Battery Serial Number (If Replaced)</label>
                        <input type="text" name="new_battery_number" id="new_battery_number" class="form-control"
                            style="font-family: 'JetBrains Mono', monospace; text-transform: uppercase;"
                            placeholder="New Battery Serial Number"
                            value="{{ old('new_battery_number', $complaint->new_battery_number) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="closing_date">Closing Date</label>
                        <input type="date" name="closing_date" id="closing_date" class="form-control"
                            value="{{ old('closing_date', $complaint->closing_date ? $complaint->closing_date->format('Y-m-d') : '') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="return_date">Return Date</label>
                        <input type="date" name="return_date" id="return_date" class="form-control"
                            value="{{ old('return_date', $complaint->return_date ? $complaint->return_date->format('Y-m-d') : '') }}">
                    </div>
                </div>
            </div>

            <!-- SECTION 6: OPTIONAL REPLACE SCREENSHOT UPLOAD -->
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="manual_screenshot">Replace Battery Connect Screenshot (Optional):</label>
                <input type="file" name="manual_screenshot" id="manual_screenshot" class="form-control" accept="image/*">
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-weight: 800;">💾 UPDATE
                    RECORD</button>
                <a href="{{ route('complaints.show', $complaint->id) }}" class="btn btn-outline"
                    style="padding: 0.75rem 1.5rem;">CANCEL</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isProrata = document.getElementById('edit_is_prorata');
            const prorataWrap = document.getElementById('edit_prorata_wrap');
            if (isProrata && prorataWrap) {
                isProrata.addEventListener('change', function () {
                    prorataWrap.style.display = this.checked ? 'block' : 'none';
                    if (!this.checked) {
                        document.getElementById('prorata_percentage').value = '';
                    }
                });
            }

            const isService = document.getElementById('edit_is_service');
            const serviceWrap = document.getElementById('edit_service_wrap');
            if (isService && serviceWrap) {
                isService.addEventListener('change', function () {
                    serviceWrap.style.display = this.checked ? 'block' : 'none';
                    if (!this.checked) {
                        document.getElementById('service_battery_number').value = '';
                    }
                });
            }
        });
    </script>
@endpush