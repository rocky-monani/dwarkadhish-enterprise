@extends('layouts.app')

@section('title', 'New Inward Complaint')

@section('content')
<div style="max-width: 1050px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">

    <!-- Open Complaint Duplicate Alert Banner -->
    <div id="duplicate_alert" class="card" style="display: none; border-left: 3px solid var(--accent-red); background-color: #fef2f2; margin-bottom: 0;">
        <div style="flex-grow: 1;">
            <div style="font-weight: 600; color: #991b1b; font-size: 14px;">Open complaint exists for this battery</div>
            <div id="duplicate_msg" style="color: #7f1d1d; font-size: 13px; margin-top: 4px;"></div>
            <div style="color: #991b1b; font-size: 12px; margin-top: 4px;">Close the previous open case before creating a new inward entry.</div>
        </div>
    </div>

    <!-- Step 1: Battery Identification & Search -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">Battery identification &amp; warranty lookup</h3>
            <span class="badge badge-open">Inward entry</span>
        </div>

        <div style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="flex-grow: 1; min-width: 250px; margin-bottom: 0;">
                <label for="lookup_serial" class="form-label">Battery serial number (scan barcode or type)</label>
                <input type="text" id="lookup_serial" class="form-control" style="font-family: 'JetBrains Mono', monospace; font-size: 16px; font-weight: 500; text-transform: uppercase; height: 42px;" placeholder="e.g. EJE0231A644397" autofocus autocomplete="off">
            </div>

            <button type="button" id="btn_fetch" class="btn btn-primary" style="height: 42px; padding: 0 20px;">
                <span id="fetch_spinner" class="spinner" style="display: none; border: 2px solid #ffffff; border-top-color: transparent; border-radius: 50%; width: 16px; height: 16px; animation: spin 0.8s linear infinite;"></span>
                <span id="fetch_text">Check battery</span>
            </button>

            <button type="button" id="btn_manual_entry" class="btn btn-outline" style="height: 42px;" title="Enter battery manually if not registered in Battery Connect app">
                Manual / offline entry
            </button>
        </div>

        <!-- Offline / Manual Notice Banner -->
        <div id="offline_notice_wrap" style="display: none; margin-top: 16px; padding: 12px 16px; border-left: 3px solid var(--accent-blue); background-color: #f0f9ff; border-radius: 8px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                <div>
                    <div style="font-weight: 600; color: #0369a1; font-size: 13px;">Offline / manual battery entry mode</div>
                    <div style="color: #075985; font-size: 12px; margin-top: 2px;">Fill available complaint details below.</div>
                </div>
                <button type="button" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;" onclick="document.getElementById('offline_notice_wrap').style.display='none';">Dismiss</button>
            </div>
        </div>

        <!-- Real-time Step Progress Visualizer -->
        <div id="lookup_progress_wrap" style="display: none; margin-top: 1.5rem; padding: 1.25rem; background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.85rem; font-weight: 700; color: var(--text-secondary);">
                <span id="progress_step_1" style="color: var(--primary);">1. Connecting</span>
                <span id="progress_step_2" style="color: var(--text-muted);">2. Searching Serial</span>
                <span id="progress_step_3" style="color: var(--text-muted);">3. Loading Details</span>
                <span id="progress_step_4" style="color: var(--text-muted);">4. Capturing Evidence</span>
            </div>
            <div style="width: 100%; height: 6px; background-color: #e2e8f0; border-radius: 3px; overflow: hidden;">
                <div id="progress_bar" style="width: 25%; height: 100%; background-color: var(--primary); transition: width 0.4s ease;"></div>
            </div>
            <div id="progress_status_text" style="font-size: 0.85rem; font-weight: 600; color: var(--primary); margin-top: 0.65rem; text-align: center;">
                Checking battery information...
            </div>
        </div>
    </div>

    <!-- BATTERY CONNECT CAPTURED RESULT PREVIEW CARD -->
    <div id="preview_card" class="card" style="display: none; margin-bottom: 0; border-top: 4px solid var(--amaron-green);">
        <div class="card-header">
            <h3 class="card-title" style="color: var(--amaron-green-dark); display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Battery Connect Details Fetched Successfully
            </h3>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" id="btn_retry" onclick="document.getElementById('btn_fetch').click();" class="btn btn-outline" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">Re-Fetch</button>
                <button type="button" id="btn_remove_preview" class="btn btn-outline" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; color: var(--accent-red);">Clear</button>
            </div>
        </div>

        <div id="preview_details_summary" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.85rem; padding: 1rem; background-color: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
            <!-- Populated via JavaScript -->
        </div>

        <div id="preview_img_wrap" style="display: none; margin-top: 0.85rem; flex-direction: column; align-items: center; justify-content: center; background-color: #f1f5f9; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); max-height: 450px; overflow-y: auto;">
            <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; width: 100%; text-align: left;">Screenshot Evidence Attachment:</div>
            <img id="preview_img" src="" alt="Battery Connect Capture Preview" style="max-width: 100%; height: auto; border-radius: 4px; box-shadow: var(--shadow-md);">
        </div>
    </div>

    <!-- MANUAL INFORMATION FORM -->
    <form id="complaint_form" action="{{ route('complaints.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="form_battery_serial" name="battery_serial_number" value="{{ old('battery_serial_number') }}">
        <input type="hidden" id="form_image_path" name="battery_connect_image_path" value="{{ old('battery_connect_image_path') }}">
        <input type="hidden" id="form_battery_connect_data" name="battery_connect_data" value="">
        <input type="hidden" id="form_action" name="action" value="save">

        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h3 class="card-title">
                    <span style="background-color: var(--primary); color: #ffffff; width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">2</span>
                    Service Center Complaint Details (5 Core Fields)
                </h3>
            </div>

            <!-- Row 1: Battery Type, Complaint Number, Complaint Date -->
            <div class="form-row">
                <div class="form-group">
                    <label for="battery_type" class="form-label">Battery Category / Type *</label>
                    <select id="battery_type" name="battery_type" class="form-control" style="font-weight: 800; font-size: 0.95rem; color: var(--primary); background-color: var(--primary-light); border-color: var(--primary);">
                        <option value="TW" {{ old('battery_type', $batteryType ?? 'TW') === 'TW' ? 'selected' : '' }}>🏍️ TW (Two Wheeler)</option>
                        <option value="FW" {{ old('battery_type', $batteryType ?? 'TW') === 'FW' ? 'selected' : '' }}>🚗 FW (Four Wheeler)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="complaint_number" class="form-label">Complaint Number *</label>
                    <input type="text" id="complaint_number" name="complaint_number" class="form-control" style="font-weight: 800; font-family: 'JetBrains Mono', monospace;" value="{{ old('complaint_number', $nextComplaintNumber) }}" required>
                </div>

                <div class="form-group">
                    <label for="complaint_date" class="form-label">Complaint Date *</label>
                    <input type="date" id="complaint_date" name="complaint_date" class="form-control" value="{{ old('complaint_date', date('Y-m-d')) }}" required>
                </div>
            </div>

            <!-- Row 2: Customer Name & Customer Mobile Number -->
            <div class="form-row" style="margin-top: 0.5rem;">
                <div class="form-group">
                    <label for="customer_name" class="form-label">Customer Name *</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control" value="{{ old('customer_name') }}" placeholder="Full Customer Name" required>
                </div>

                <div class="form-group">
                    <label for="customer_mobile" class="form-label">Customer Mobile Number (Optional)</label>
                    <input type="text" id="customer_mobile" name="customer_mobile" class="form-control" value="{{ old('customer_mobile') }}" placeholder="10-digit mobile number" maxlength="15">
                </div>
            </div>

            <!-- Row 3: Location, Product Model, & Product Segment -->
            <div class="form-row" style="margin-top: 0.5rem;">
                <div class="form-group">
                    <label for="location" class="form-label">Customer Location / City (Optional)</label>
                    <input type="text" id="location" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g. Jamnagar / Lalpur / Dhrol">
                </div>

                <div class="form-group">
                    <label for="product_model" class="form-label">Product Model / Type</label>
                    <input type="text" id="product_model" name="product_model" class="form-control" style="font-weight: 700;" value="{{ old('product_model') }}" placeholder="e.g. APBTZ4A / AP-BTZ5L">
                </div>

                <div class="form-group">
                    <label for="product_segment" class="form-label">Product Segment (PROD.SEG.)</label>
                    <select id="product_segment" name="product_segment" class="form-control" style="font-weight: 800; font-family: 'JetBrains Mono', monospace; color: var(--primary);">
                        <option value="TW" {{ old('product_segment') === 'TW' ? 'selected' : '' }}>TW (Two Wheeler)</option>
                        <option value="FW" {{ old('product_segment') === 'FW' ? 'selected' : '' }}>FW (Four Wheeler)</option>
                        <option value="INV." {{ old('product_segment') === 'INV.' ? 'selected' : '' }}>INV. (Inverter)</option>
                        <option value="HUPS" {{ old('product_segment') === 'HUPS' ? 'selected' : '' }}>HUPS (Home UPS)</option>
                        <option value="TT" {{ old('product_segment') === 'TT' ? 'selected' : '' }}>TT (Tubular / Tractor)</option>
                    </select>
                </div>
            </div>

            <div class="form-row" id="auto_fetched_fields_row" style="margin-top: 0.5rem;">
                <div class="form-group">
                    <label for="purchase_date" class="form-label">Purchase Date / Date of Sale</label>
                    <input type="date" id="purchase_date" name="purchase_date" class="form-control" value="{{ old('purchase_date') }}">
                </div>

                <div class="form-group">
                    <label for="vehicle_name" class="form-label">Vehicle Name / Model</label>
                    <input type="text" id="vehicle_name" name="vehicle_name" class="form-control" value="{{ old('vehicle_name') }}" placeholder="e.g. Activa / Splendor / Pulsar">
                </div>

                <div class="form-group">
                    <label for="vehicle_number" class="form-label">Vehicle Registration Number</label>
                    <input type="text" id="vehicle_number" name="vehicle_number" class="form-control" style="font-family: 'JetBrains Mono', monospace; text-transform: uppercase;" value="{{ old('vehicle_number') }}" placeholder="e.g. GJ10AB1234">
                </div>
            </div>

            <!-- Additional Options Row -->
            <div class="form-row" style="margin-top: 0.5rem; padding-top: 1rem; border-top: 1px dashed var(--border-color);">
                <div class="form-group" style="max-width: 320px;">
                    <label for="online_reg_type" class="form-label" style="font-weight: 700;">Warranty Card / RC Book Status</label>
                    <select id="online_reg_type" name="online_reg_type" class="form-control" style="font-weight: 700;">
                        <option value="">-- Select Download Status --</option>
                        <option value="WARRANTY CARD" {{ old('online_reg_type') === 'WARRANTY CARD' ? 'selected' : '' }}>📄 WARRANTY CARD</option>
                        <option value="RC BOOK" {{ old('online_reg_type') === 'RC BOOK' ? 'selected' : '' }}>🚘 RC BOOK</option>
                        <option value="NOT DOWNLOADED" {{ old('online_reg_type') === 'NOT DOWNLOADED' ? 'selected' : '' }}>❌ NOT DOWNLOADED</option>
                    </select>
                </div>

                <div class="form-group" style="display: flex; flex-direction: column; justify-content: center;">
                    <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" id="is_digital_warranty" name="is_digital_warranty" value="1" {{ old('is_digital_warranty') ? 'checked' : '' }} style="width: 18px; height: 18px;">
                        <span>📱 Digital Warranty Registered</span>
                    </label>
                </div>
            </div>

            <!-- Standby / Service Battery Toggle -->
            <div style="margin-top: 0.5rem; padding-top: 0.85rem; border-top: 1px dashed var(--border-color);">
                <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" id="is_service_battery_given" name="is_service_battery_given" value="1" {{ old('is_service_battery_given') ? 'checked' : '' }} style="width: 18px; height: 18px;">
                    <span>🔋 Service / Standby Battery Issued to Customer</span>
                </label>

                <div id="service_battery_wrap" style="display: {{ old('is_service_battery_given') ? 'block' : 'none' }}; margin-top: 0.75rem; max-width: 320px;">
                    <label for="service_battery_number" class="form-label">Service Battery Serial Number</label>
                    <input type="text" id="service_battery_number" name="service_battery_number" class="form-control" style="font-family: 'JetBrains Mono', monospace; text-transform: uppercase;" value="{{ old('service_battery_number') }}" placeholder="Enter Standby Serial">
                </div>
            </div>

            <!-- PRO-RATA DISCOUNT SECTION -->
            <div style="margin-top: 0.5rem; padding-top: 0.85rem; border-top: 1px dashed var(--border-color);">
                <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" id="is_prorata_check" name="is_prorata" value="1" {{ old('is_prorata') ? 'checked' : '' }} style="width: 18px; height: 18px;">
                    <span style="font-weight: 700; color: var(--text-main);">📊 Pro-Rata Discount / Settlement Applicable</span>
                </label>
                <input type="hidden" id="is_prorata" value="{{ old('is_prorata', 0) }}">

                <div id="prorata_wrap" style="display: {{ old('is_prorata') ? 'block' : 'none' }}; margin-top: 0.75rem; max-width: 320px;">
                    <label for="prorata_percentage" class="form-label" style="font-weight: 700; color: #b45309;">Pro-Rata Discount Percentage (%)</label>
                    <input type="number" step="0.01" min="0" max="100" id="prorata_percentage" name="prorata_percentage" class="form-control" style="font-weight: 700; font-size: 1.05rem;" value="{{ old('prorata_percentage') }}" placeholder="e.g. 42 or 45">
                </div>
            </div>

            <!-- Manual Screenshot Attachment (Optional Fallback) -->
            <div style="margin-top: 1.25rem;">
                <label for="manual_screenshot" class="form-label">Attach Manual Battery Connect Screenshot (Optional)</label>
                <input type="file" id="manual_screenshot" name="manual_screenshot" class="form-control" accept="image/png,image/jpeg">
            </div>
        </div>

        <!-- PRIMARY ACTION BUTTON BAR -->
        <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 0.5rem;">
            <button type="button" id="btn_clear" class="btn btn-outline" style="padding: 0.75rem 1.5rem;">Clear Form</button>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" id="btn_save_record" onclick="document.getElementById('form_action').value='save'" class="btn btn-success" style="padding: 0.75rem 2.25rem; font-size: 1.05rem; font-weight: 800; box-shadow: var(--shadow-md);">
                    💾 SAVE INWARD RECORD
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<style>
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lookupInput = document.getElementById('lookup_serial');
    const fetchBtn = document.getElementById('btn_fetch');
    const fetchSpinner = document.getElementById('fetch_spinner');
    const fetchText = document.getElementById('fetch_text');
    
    const progressWrap = document.getElementById('lookup_progress_wrap');
    const progressBar = document.getElementById('progress_bar');
    const progressStatusText = document.getElementById('progress_status_text');
    
    const duplicateAlert = document.getElementById('duplicate_alert');
    const duplicateMsg = document.getElementById('duplicate_msg');
    
    const previewCard = document.getElementById('preview_card');
    const previewImg = document.getElementById('preview_img');
    const btnRemovePreview = document.getElementById('btn_remove_preview');

    const formSerial = document.getElementById('form_battery_serial');
    const formImagePath = document.getElementById('form_image_path');
    const formConnectData = document.getElementById('form_battery_connect_data');

    const isProrataCheck = document.getElementById('is_prorata_check');
    const isProrataHidden = document.getElementById('is_prorata');
    const prorataWrap = document.getElementById('prorata_wrap');

    const isServiceCheck = document.getElementById('is_service_battery_given');
    const serviceWrap = document.getElementById('service_battery_wrap');

    const btnSave = document.getElementById('btn_save_record');
    const btnPrint = document.getElementById('btn_save_print');
    const btnClear = document.getElementById('btn_clear');

    // Auto-focus lookup serial on load
    if (lookupInput) lookupInput.focus();

    // Battery Type Change -> Auto Fetch Next Complaint Number for TW or FW
    const batteryTypeSelect = document.getElementById('battery_type');
    const productSegInput = document.getElementById('product_segment');

    if (batteryTypeSelect) {
        batteryTypeSelect.addEventListener('change', async function() {
            const selectedType = this.value;
            if (productSegInput && (!productSegInput.value || productSegInput.value === 'TW' || productSegInput.value === 'FW')) {
                productSegInput.value = selectedType === 'TW' ? 'TW' : 'FW';
            }
            try {
                const data = await safeFetchJson("{{ route('complaints.next_number') }}?battery_type=" + selectedType);
                if (data && data.next_complaint_number) {
                    document.getElementById('complaint_number').value = data.next_complaint_number;
                    showToast('Complaint number updated to #' + data.next_complaint_number + ' for ' + selectedType + '.', 'info');
                }
            } catch (e) {}
        });
    }

    // Pro-Rata Toggle
    if (isProrataCheck && prorataWrap) {
        isProrataCheck.addEventListener('change', function() {
            if (isProrataHidden) isProrataHidden.value = this.checked ? '1' : '0';
            prorataWrap.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) document.getElementById('prorata_percentage').value = '';
        });
    }

    // Service Battery Toggle
    if (isServiceCheck) {
        isServiceCheck.addEventListener('change', function() {
            serviceWrap.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) document.getElementById('service_battery_number').value = '';
        });
    }

    // Barcode Scanner Enter Key Listener
    lookupInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            triggerFetch();
        }
    });

    fetchBtn.addEventListener('click', triggerFetch);

    const btnManualEntry = document.getElementById('btn_manual_entry');
    const offlineNotice = document.getElementById('offline_notice_wrap');

    if (btnManualEntry) {
        btnManualEntry.addEventListener('click', async function() {
            let serial = lookupInput.value.trim();
            if (!serial) {
                serial = prompt("Enter the Battery Serial Number for manual entry:");
                if (serial) {
                    lookupInput.value = serial.trim().toUpperCase();
                    serial = serial.trim().toUpperCase();
                } else {
                    return;
                }
            }

            formSerial.value = serial;
            await checkDuplicateSerial(serial);

            if (offlineNotice) offlineNotice.style.display = 'block';
            const autoFetchedRow = document.getElementById('auto_fetched_fields_row');
            if (autoFetchedRow) autoFetchedRow.style.display = 'grid';

            const digitalWarrantyCheck = document.getElementById('is_digital_warranty');
            if (digitalWarrantyCheck) digitalWarrantyCheck.checked = false;

            showToast('ℹ️ Manual / Offline Entry mode active for Serial #' + serial + '. Enter available details below.', 'info');

            const customerNameInput = document.getElementById('customer_name');
            if (customerNameInput) customerNameInput.focus();
        });
    }

    // Duplicate Serial Check
    async function checkDuplicateSerial(serial) {
        if (!serial) return;
        try {
            const data = await safeFetchJson("{{ route('complaints.check_duplicate') }}", {
                method: 'POST',
                body: JSON.stringify({ serial_number: serial })
            });

            if (data.has_open) {
                duplicateAlert.style.display = 'block';
                const dateStr = data.open_date || 'previous date';
                duplicateMsg.innerHTML = `Complaint <strong>#${data.open_record.complaint_number}</strong> for this battery serial is currently OPEN (entered on <strong>${dateStr}</strong>). Please close the previous case of this battery first before creating a new inward entry.`;
                if (btnSave) btnSave.disabled = true;
                if (btnPrint) btnPrint.disabled = true;
            } else {
                duplicateAlert.style.display = 'none';
                if (btnSave) btnSave.disabled = false;
                if (btnPrint) btnPrint.disabled = false;
                if (data.has_closed) {
                    showToast('ℹ️ Notice: This battery serial has previous closed history (Complaint #' + data.closed_record.complaint_number + '). Re-entry allowed because previous case is closed.', 'info');
                }
            }
        } catch (e) {}
    }

    // Trigger Battery Connect Lookup
    async function triggerFetch() {
        const serial = lookupInput.value.trim();
        if (!serial) {
            showToast('Please scan or enter a battery serial number.', 'warning');
            return;
        }

        formSerial.value = serial;
        await checkDuplicateSerial(serial);

        if (btnSave && btnSave.disabled) return;

        // UI Progress State
        fetchBtn.disabled = true;
        fetchSpinner.style.display = 'inline-block';
        fetchText.innerText = 'CHECKING...';
        progressWrap.style.display = 'block';
        
        updateProgress(25, '1. Connecting to Battery Connect...');

        setTimeout(() => updateProgress(50, '2. Searching battery serial...'), 1000);
        setTimeout(() => updateProgress(75, '3. Loading battery details & warranty history...'), 2500);

        try {
            const complaintNo = document.getElementById('complaint_number').value || '';
            const data = await safeFetchJson("{{ route('complaints.fetch_battery') }}", {
                method: 'POST',
                body: JSON.stringify({ serial_number: serial, complaint_number: complaintNo })
            });

            updateProgress(100, '4. Details fetched & processed successfully!');

            if (data.status === 'SUCCESS' || data.success) {
                populateFetchedDetails(data);
                showToast('Battery details fetched and populated.', 'success');

                // Focus operator entry field: Customer Name
                document.getElementById('customer_name').focus();
            } else if (data.status === 'AUTH_REQUIRED') {
                showToast('Battery Connect session requires login / OTP verification.', 'warning');
                openBatteryConnectLoginBrowser();
            } else {
                showToast(data.message || 'Battery lookup completed.', 'warning');
            }

        } catch (err) {
            showToast('Lookup error: ' + err.message, 'error');
        } finally {
            fetchBtn.disabled = false;
            fetchSpinner.style.display = 'none';
            fetchText.innerText = 'Check battery';
            setTimeout(() => { progressWrap.style.display = 'none'; }, 2000);
        }
    }

    function populateFetchedDetails(data) {
        const meta = data.metadata || data.battery_connect_data || {};
        
        // Auto-detect Battery Category (TW vs FW) from fetched site data
        let detectedType = 'FW'; // Default to FW for Four Wheeler, Inverter, CV, Tractor, UPS, or any non-TW category
        const combinedText = [
            meta.product_segment || '',
            meta.product_model || '',
            meta.vehicle_name || '',
            meta.product_code || '',
            JSON.stringify(meta)
        ].join(' ').toLowerCase();

        const isTwoWheeler = /two\s*wheeler|2\s*wheeler|motorcycle|scooter|bike|tw\b|btz|apbtz|atz|2w\b/i.test(combinedText);
        if (isTwoWheeler) {
            detectedType = 'TW';
        }

        const batteryTypeSelect = document.getElementById('battery_type');
        if (batteryTypeSelect && batteryTypeSelect.value !== detectedType) {
            batteryTypeSelect.value = detectedType;
            batteryTypeSelect.dispatchEvent(new Event('change'));
        }

        if (meta.product_model) {
            const pmInput = document.getElementById('product_model');
            if (pmInput) pmInput.value = meta.product_model;
        }

        if (meta.customer_name) {
            document.getElementById('customer_name').value = meta.customer_name;
        }
        
        if (meta.customer_mobile) {
            document.getElementById('customer_mobile').value = meta.customer_mobile;
        }

        if (meta.vehicle_number) {
            document.getElementById('vehicle_number').value = meta.vehicle_number;
        }

        if (meta.vehicle_name) {
            document.getElementById('vehicle_name').value = meta.vehicle_name;
        }

        if (meta.date_of_sale) {
            const formatted = parseToIsoDate(meta.date_of_sale);
            if (formatted) {
                document.getElementById('purchase_date').value = formatted;
            }
        }

        if (meta.prorata_percentage || (meta.warranty_status && /prorata|out\s*of\s*warranty|expired|no\s*warranty/i.test(meta.warranty_status))) {
            const isProrataHidden = document.getElementById('is_prorata');
            const isProrataCheck = document.getElementById('is_prorata_check');
            const prorataInput = document.getElementById('prorata_percentage');
            const prorataWrap = document.getElementById('prorata_wrap');

            if (isProrataHidden) isProrataHidden.value = '1';
            if (isProrataCheck) isProrataCheck.checked = true;
            if (prorataWrap) prorataWrap.style.display = 'block';
            if (prorataInput && meta.prorata_percentage) {
                prorataInput.value = meta.prorata_percentage;
            }
        }

        if (meta.warranty_status) {
            const isDigitalCheck = document.getElementById('is_digital_warranty');
            if (isDigitalCheck && /digital|warranty|registered|active/i.test(meta.warranty_status)) {
                isDigitalCheck.checked = true;
            }
        }

        const summaryBox = document.getElementById('preview_details_summary');
        if (summaryBox) {
            summaryBox.innerHTML = `
                <div style="background:#ffffff; padding:0.65rem 0.85rem; border-radius:4px; border:1px solid #e2e8f0;">
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Product Model / Type</div>
                    <div style="font-weight:800; font-size:1rem; color:var(--primary); font-family:'JetBrains Mono',monospace;">${meta.product_model || 'N/A'}</div>
                </div>
                <div style="background:#ffffff; padding:0.65rem 0.85rem; border-radius:4px; border:1px solid #e2e8f0;">
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Vehicle Reg Number</div>
                    <div style="font-weight:800; font-size:1rem; color:var(--primary); font-family:'JetBrains Mono',monospace;">${meta.vehicle_number || 'N/A'}</div>
                </div>
                <div style="background:#ffffff; padding:0.65rem 0.85rem; border-radius:4px; border:1px solid #e2e8f0;">
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Vehicle Model / Name</div>
                    <div style="font-weight:700; font-size:0.95rem; color:var(--text-main);">${meta.vehicle_name || 'N/A'}</div>
                </div>
                <div style="background:#ffffff; padding:0.65rem 0.85rem; border-radius:4px; border:1px solid #e2e8f0;">
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Customer Name</div>
                    <div style="font-weight:700; font-size:0.95rem; color:var(--text-main);">${meta.customer_name || 'N/A'}</div>
                </div>
                <div style="background:#ffffff; padding:0.65rem 0.85rem; border-radius:4px; border:1px solid #e2e8f0;">
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Date of Sale / Purchase</div>
                    <div style="font-weight:700; font-size:0.95rem; color:var(--text-main);">${meta.date_of_sale || 'N/A'}</div>
                </div>
                <div style="background:#ffffff; padding:0.65rem 0.85rem; border-radius:4px; border:1px solid #e2e8f0;">
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Warranty Status</div>
                    <div style="font-weight:800; font-size:0.95rem; color:#047857;">${meta.warranty_status || 'Registered'}</div>
                </div>
            `;
        }

        const imgWrap = document.getElementById('preview_img_wrap');
        const imgPath = data.image_path || data.screenshot_path;
        if (imgPath) {
            formImagePath.value = imgPath;
            previewImg.src = (imgPath.startsWith('http') || imgPath.startsWith('/')) ? imgPath : '/' + imgPath;
            if (imgWrap) imgWrap.style.display = 'flex';
        } else {
            if (imgWrap) imgWrap.style.display = 'none';
        }

        formConnectData.value = JSON.stringify(meta);
        previewCard.style.display = 'block';

        const autoFetchedRow = document.getElementById('auto_fetched_fields_row');
        if (autoFetchedRow) autoFetchedRow.style.display = 'none';
    }

    function updateProgress(percent, statusMsg) {
        progressBar.style.width = percent + '%';
        progressStatusText.innerText = statusMsg;
    }

    btnRemovePreview.addEventListener('click', function() {
        previewCard.style.display = 'none';
        previewImg.src = '';
        formImagePath.value = '';
        formConnectData.value = '';
        const autoFetchedRow = document.getElementById('auto_fetched_fields_row');
        if (autoFetchedRow) autoFetchedRow.style.display = 'grid';
    });

    function parseToIsoDate(str) {
        if (!str) return '';
        str = str.trim();

        const monthMap = {
            jan: '01', feb: '02', mar: '03', apr: '04', may: '05', jun: '06',
            jul: '07', aug: '08', sep: '09', oct: '10', nov: '11', dec: '12'
        };

        const parts = str.split(/[\/\-\.\s]+/);
        if (parts.length === 3) {
            let p0 = parts[0], p1 = parts[1], p2 = parts[2];
            let yyyy, mm, dd;

            if (p0.length === 4) {
                yyyy = p0;
                mm = monthMap[p1.toLowerCase()] || p1.padStart(2, '0');
                dd = p2.padStart(2, '0');
            } else {
                dd = p0.padStart(2, '0');
                mm = monthMap[p1.toLowerCase()] || p1.padStart(2, '0');
                yyyy = p2.length === 2 ? '20' + p2 : p2;
            }

            if (/^\d{4}$/.test(yyyy) && /^\d{2}$/.test(mm) && /^\d{2}$/.test(dd)) {
                return `${yyyy}-${mm}-${dd}`;
            }
        }

        try {
            const d = new Date(str);
            if (!isNaN(d.getTime())) {
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${y}-${m}-${day}`;
            }
        } catch (e) {}

        return '';
    }

    btnClear.addEventListener('click', function() {
        lookupInput.value = '';
        formSerial.value = '';
        formImagePath.value = '';
        formConnectData.value = '';
        previewCard.style.display = 'none';
        duplicateAlert.style.display = 'none';
        if (btnSave) btnSave.disabled = false;
        if (btnPrint) btnPrint.disabled = false;
        const caseInput = document.getElementById('case_number');
        if (caseInput) caseInput.value = '';
        document.getElementById('customer_name').value = '';
        document.getElementById('customer_mobile').value = '';
        document.getElementById('purchase_date').value = '';
        document.getElementById('vehicle_name').value = '';
        document.getElementById('vehicle_number').value = '';
        const regType = document.getElementById('online_reg_type');
        if (regType) regType.value = '';
        const autoFetchedRow = document.getElementById('auto_fetched_fields_row');
        if (autoFetchedRow) autoFetchedRow.style.display = 'grid';
        lookupInput.focus();
    });
});
</script>
@endpush
