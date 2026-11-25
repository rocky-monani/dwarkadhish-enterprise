@extends('layouts.app')

@section('title', 'Close Complaint Case')

@section('content')
<div style="display: flex; flex-direction: column; gap: 1.5rem;">

    <!-- TOP HEADER BAR -->
    <div class="card" style="margin-bottom: 0; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-top: 4px solid var(--accent-amber);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 0.6rem;">
                    <span>🔒 Close Complaint Case</span>
                </h2>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                    Type into any input field below to auto-find open complaints letter-by-letter. No submit button required.
                </p>
            </div>
            <div>
                <a href="{{ route('complaints.pending_cases') }}" class="btn btn-outline" style="font-weight: 700; font-size: 0.85rem;">
                    📋 View Pending Cases List
                </a>
            </div>
        </div>
    </div>

    <!-- 4 SEPARATE LIVE SEARCH INPUT FIELDS CARD -->
    <div class="card" style="margin-bottom: 0; padding: 1.25rem; border-top: 3px solid var(--primary);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.4rem;">
                <span>⚡ LIVE LETTER-BY-LETTER AUTO-SEARCH</span>
                <span id="search_spinner" class="spinner" style="display: none; border: 2px solid var(--primary); border-top-color: transparent; border-radius: 50%; width: 14px; height: 14px; animation: spin 0.8s linear infinite;"></span>
            </div>
            <button type="button" id="btn_clear_search" class="btn btn-outline" style="padding: 0.25rem 0.65rem; font-size: 0.78rem;">
                🧹 Clear All Inputs
            </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem;">
            <!-- Field 1: Complaint Number -->
            <div class="form-group" style="margin-bottom: 0;">
                <label for="complaint_number_search" class="form-label" style="font-size: 0.8rem; font-weight: 700; color: var(--text-main);">
                    1. COMPLAINT NUMBER
                </label>
                <input type="text" id="complaint_number_search" class="form-control live-search-input" style="font-family: 'JetBrains Mono', monospace; font-weight: 800;" placeholder="e.g. 1001 or 3634" autocomplete="off" autofocus>
            </div>

            <!-- Field 2: Customer Name -->
            <div class="form-group" style="margin-bottom: 0;">
                <label for="customer_name_search" class="form-label" style="font-size: 0.8rem; font-weight: 700; color: var(--text-main);">
                    2. CUSTOMER NAME
                </label>
                <input type="text" id="customer_name_search" class="form-control live-search-input" style="font-weight: 700;" placeholder="e.g. Momai Auto / Rajesh" autocomplete="off">
            </div>

            <!-- Field 3: Customer Mobile Number -->
            <div class="form-group" style="margin-bottom: 0;">
                <label for="customer_mobile_search" class="form-label" style="font-size: 0.8rem; font-weight: 700; color: var(--text-main);">
                    3. CUSTOMER MOBILE NO
                </label>
                <input type="text" id="customer_mobile_search" class="form-control live-search-input" style="font-family: 'JetBrains Mono', monospace; font-weight: 700;" placeholder="e.g. 9876543210" autocomplete="off">
            </div>

            <!-- Field 4: Battery Serial Number -->
            <div class="form-group" style="margin-bottom: 0;">
                <label for="battery_serial_search" class="form-label" style="font-size: 0.8rem; font-weight: 700; color: var(--text-main);">
                    4. BATTERY SERIAL NO
                </label>
                <input type="text" id="battery_serial_search" class="form-control live-search-input" style="font-family: 'JetBrains Mono', monospace; font-weight: 800; text-transform: uppercase;" placeholder="e.g. EAB0206Z10..." autocomplete="off">
            </div>
        </div>
    </div>

    <!-- LIVE MATCHING RESULTS TABLE CARD -->
    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 0;">
        <div style="padding: 0.85rem 1.25rem; background-color: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
                <span>🟢 OPEN CASES RESULTS</span>
                <span id="results_count_badge" class="badge" style="background-color: var(--primary-light); color: var(--primary); font-weight: 800; font-size: 0.78rem;">Loading...</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                Click "Close This Case" to record final settlement & replacement serial.
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="background-color: #f1f5f9; border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em;">
                        <th style="padding: 0.75rem 1rem;">Complaint No</th>
                        <th style="padding: 0.75rem 1rem;">Inward Date</th>
                        <th style="padding: 0.75rem 1rem;">Category / Model</th>
                        <th style="padding: 0.75rem 1rem;">Customer Name</th>
                        <th style="padding: 0.75rem 1rem;">Mobile No</th>
                        <th style="padding: 0.75rem 1rem;">Battery Serial</th>
                        <th style="padding: 0.75rem 1rem;">CRM Case No</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody id="live_results_body">
                    <!-- Populated dynamically via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- SETTLEMENT / CLOSE CASE MODAL -->
<div id="close_case_modal_overlay" class="modal-overlay">
    <div class="modal-card" style="max-width: 540px;">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
                <span>🔒 Close & Settle Complaint</span>
            </h3>
            <button type="button" onclick="closeModalDirect()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <!-- Target Case Quick Summary -->
        <div style="background-color: #f8fafc; padding: 0.85rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); margin-bottom: 1.15rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.85rem;">
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; display: block;">COMPLAINT NO</span>
                <strong id="modal_complaint_no" style="font-family: 'JetBrains Mono', monospace; font-size: 1.05rem; color: var(--primary);">-</strong>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 0.75rem; display: block;">BATTERY SERIAL</span>
                <strong id="modal_battery_serial" style="font-family: 'JetBrains Mono', monospace; color: var(--text-main);">-</strong>
            </div>
            <div style="grid-column: span 2; margin-top: 0.25rem;">
                <span style="color: var(--text-muted); font-size: 0.75rem; display: block;">CUSTOMER NAME</span>
                <strong id="modal_customer_name" style="color: var(--text-main);">-</strong>
            </div>
        </div>

        <form id="close_case_form">
            <input type="hidden" id="modal_complaint_id" value="">

            <div class="form-group">
                <label for="modal_final_decision" class="form-label" style="font-weight: 700;">Final Decision / Settlement Code *</label>
                <select id="modal_final_decision" class="form-control" style="font-weight: 800; font-size: 0.95rem; color: var(--primary);" required>
                    <option value="">-- Select Final Decision --</option>
                    <option value="F">F — Free Replacement Issued</option>
                    <option value="P">P — Pro-Rata Settlement</option>
                    <option value="C">C — Battery Charged & Okay</option>
                    <option value="R">R — Dead Return / Rejected</option>
                </select>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem;">
                <div class="form-group">
                    <label for="modal_decision_date" class="form-label" style="font-weight: 700; font-size: 0.85rem;">Decision Date *</label>
                    <input type="date" id="modal_decision_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="modal_closing_date" class="form-label" style="font-weight: 700; font-size: 0.85rem;">Closing Date *</label>
                    <input type="date" id="modal_closing_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="modal_return_date" class="form-label" style="font-weight: 700; font-size: 0.85rem;">Return Date</label>
                    <input type="date" id="modal_return_date" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="modal_new_battery_number" class="form-label" style="font-weight: 700;">New Replacement Battery Serial Number (If Issued)</label>
                <input type="text" id="modal_new_battery_number" class="form-control" style="font-family: 'JetBrains Mono', monospace; text-transform: uppercase; font-weight: 700;" placeholder="e.g. EAB0206Z19999">
            </div>

            <div class="form-group">
                <label for="modal_delivery_mode" class="form-label" style="font-weight: 700;">Delivery / Take Person Mode</label>
                <input type="text" id="modal_delivery_mode" class="form-control" style="font-weight: 700;" placeholder="e.g. BY HIMSELF / SEND IN ROUTE / BY RETAILER">
            </div>

            <div class="form-group">
                <label for="modal_complaint_status" class="form-label" style="font-weight: 700;">Set Status *</label>
                <select id="modal_complaint_status" class="form-control" style="font-weight: 800;">
                    <option value="Closed" selected>✅ Closed</option>
                    <option value="Rejected">❌ Rejected</option>
                </select>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" onclick="closeModalDirect()" class="btn btn-outline" style="padding: 0.65rem 1.25rem;">Cancel</button>
                <button type="submit" id="btn_submit_close" class="btn btn-success" style="padding: 0.65rem 1.75rem; font-weight: 800;">
                    ✅ CONFIRM & CLOSE CASE
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<style>
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isViewer = {{ auth()->user()?->isViewer() ? 'true' : 'false' }};
    const inputs = document.querySelectorAll('.live-search-input');
    const tbody = document.getElementById('live_results_body');
    const badge = document.getElementById('results_count_badge');
    const spinner = document.getElementById('search_spinner');
    const btnClear = document.getElementById('btn_clear_search');

    let debounceTimer;
    let searchRequestId = 0;

    // Trigger initial search on load
    performLiveSearch();

    // Listen to keyup and input on all 4 fields for smooth letter-by-letter auto search
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performLiveSearch, 250);
        });
    });

    btnClear.addEventListener('click', function() {
        inputs.forEach(input => input.value = '');
        performLiveSearch();
        document.getElementById('complaint_number_search').focus();
    });

    async function performLiveSearch() {
        const thisRequestId = ++searchRequestId;
        spinner.style.display = 'inline-block';
        
        const params = new URLSearchParams({
            complaint_number: document.getElementById('complaint_number_search').value.trim(),
            customer_name: document.getElementById('customer_name_search').value.trim(),
            customer_mobile: document.getElementById('customer_mobile_search').value.trim(),
            battery_serial: document.getElementById('battery_serial_search').value.trim(),
        });

        try {
            const data = await safeFetchJson("{{ route('complaints.live_search_open') }}?" + params.toString());
            if (thisRequestId !== searchRequestId) return;
            renderResults(data.results || []);
        } catch (err) {
            if (thisRequestId === searchRequestId) {
                tbody.innerHTML = `<tr><td colspan="8" style="padding: 2rem; text-align: center; color: var(--accent-red); font-weight: 700;">Error fetching open cases: ${err.message}</td></tr>`;
            }
        } finally {
            if (thisRequestId === searchRequestId) {
                spinner.style.display = 'none';
            }
        }
    }

    function renderResults(results) {
        badge.textContent = results.length + ' Open Cases Found';

        if (results.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="padding: 2.5rem 1rem; text-align: center; color: var(--text-muted);">
                        <div style="font-size: 1.75rem; margin-bottom: 0.35rem;">🔍</div>
                        <div style="font-weight: 700; font-size: 0.95rem;">No open complaint records matched your search query.</div>
                        <div style="font-size: 0.8rem; margin-top: 0.2rem;">Try clearing or adjusting any of the 4 search fields above.</div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = results.map(item => `
            <tr style="border-bottom: 1px solid var(--border-color); background-color: #ffffff;">
                <td style="padding: 0.75rem 1rem; font-weight: 800; font-family: 'JetBrains Mono', monospace; font-size: 1rem; color: var(--primary);">
                    ${escapeHtml(item.complaint_number)}
                </td>
                <td style="padding: 0.75rem 1rem; color: var(--text-secondary); white-space: nowrap;">
                    ${escapeHtml(item.complaint_date)}
                </td>
                <td style="padding: 0.75rem 1rem;">
                    <span class="badge" style="background-color: ${item.battery_type === 'TW' ? '#d1fae5' : '#e0f2fe'}; color: ${item.battery_type === 'TW' ? '#047857' : '#0369a1'}; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">
                        ${escapeHtml(item.battery_type)}
                    </span>
                    <span style="font-weight: 700; font-family: 'JetBrains Mono', monospace;">${escapeHtml(item.product_model)}</span>
                </td>
                <td style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-main);">
                    ${escapeHtml(item.customer_name)}
                </td>
                <td style="padding: 0.75rem 1rem; font-family: 'JetBrains Mono', monospace; color: var(--text-muted);">
                    ${escapeHtml(item.customer_mobile)}
                </td>
                <td style="padding: 0.75rem 1rem; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: var(--text-main);">
                    ${escapeHtml(item.battery_serial_number)}
                    ${item.is_prorata ? '<div style="font-size: 0.72rem; color: #b45309; font-weight: 800;">PRO-RATA</div>' : ''}
                </td>
                <td style="padding: 0.75rem 1rem; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #d97706;">
                    ${escapeHtml(item.case_number)}
                </td>
                <td style="padding: 0.75rem 1rem; text-align: right; white-space: nowrap;">
                    ${isViewer
                        ? `<a href="/complaints/${item.id}" class="btn btn-outline" style="padding: 0.35rem 0.85rem; font-size: 0.8rem; font-weight: 700;">👁️ View</a>`
                        : `<button type="button" class="btn btn-warning" style="padding: 0.35rem 0.85rem; font-size: 0.8rem; font-weight: 800;" onclick='openCloseModal(${JSON.stringify(item)})'>🔒 Close Case</button>`
                    }
                </td>
            </tr>
        `).join('');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    const decisionSelect = document.getElementById('modal_final_decision');
    const decisionDateInput = document.getElementById('modal_decision_date');
    const closingDateInput = document.getElementById('modal_closing_date');
    const returnDateInput = document.getElementById('modal_return_date');
    const newBatteryInput = document.getElementById('modal_new_battery_number');

    let currentInwardDate = null;

    function parseToIsoDateString(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split(/[-/.]/);
        if (parts.length === 3) {
            if (parts[0].length === 4) return `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
            return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
        }
        return dateStr;
    }

    function addDaysToIsoDate(dateStr, days = 1) {
        if (!dateStr) {
            const today = new Date();
            today.setDate(today.getDate() + days);
            return today.toISOString().split('T')[0];
        }
        const isoStr = parseToIsoDateString(dateStr);
        const d = new Date(isoStr + 'T00:00:00');
        if (isNaN(d.getTime())) {
            const today = new Date();
            today.setDate(today.getDate() + days);
            return today.toISOString().split('T')[0];
        }
        d.setDate(d.getDate() + days);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function applyDecisionRules() {
        const val = decisionSelect.value;

        if (val === 'F') { // Free Replacement Issued
            // Decision Date and Closing Date MUST BE SAME
            if (decisionDateInput.value) {
                closingDateInput.value = decisionDateInput.value;
            } else if (closingDateInput.value) {
                decisionDateInput.value = closingDateInput.value;
            }
            if (newBatteryInput.value === 'RECHARGED') {
                newBatteryInput.value = '';
            }
        } else if (val === 'C') { // Battery Charged & Okay
            // Decision Date & Return Date take NEXT DATE (Inward Date + 1 Day)
            if (currentInwardDate) {
                const nextDate = addDaysToIsoDate(currentInwardDate, 1);
                decisionDateInput.value = nextDate;
                closingDateInput.value = nextDate;
                returnDateInput.value = nextDate;
            }
            newBatteryInput.value = 'RECHARGED';
        } else {
            if (newBatteryInput.value === 'RECHARGED') {
                newBatteryInput.value = '';
            }
        }
    }

    decisionSelect.addEventListener('change', applyDecisionRules);

    decisionDateInput.addEventListener('input', function() {
        if (decisionSelect.value === 'F') {
            closingDateInput.value = this.value;
        }
    });

    closingDateInput.addEventListener('input', function() {
        if (decisionSelect.value === 'F') {
            decisionDateInput.value = this.value;
        }
    });

    // Modal Manager
    window.openCloseModal = function(item) {
        currentInwardDate = item.raw_complaint_date || item.complaint_date;
        document.getElementById('modal_complaint_id').value = item.id;
        document.getElementById('modal_complaint_no').textContent = item.complaint_number;
        document.getElementById('modal_battery_serial').textContent = item.battery_serial_number;
        document.getElementById('modal_customer_name').textContent = item.customer_name;
        document.getElementById('modal_delivery_mode').value = item.delivery_mode || '';
        
        // Clear default decision and default dates
        decisionSelect.value = '';
        decisionDateInput.value = '';
        closingDateInput.value = '';
        returnDateInput.value = '';
        newBatteryInput.value = '';

        document.getElementById('close_case_modal_overlay').classList.add('active');
    };

    window.closeModalDirect = function() {
        document.getElementById('close_case_modal_overlay').classList.remove('active');
    };

    // Close Case Submit Form
    document.getElementById('close_case_form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const complaintId = document.getElementById('modal_complaint_id').value;
        const btnSubmit = document.getElementById('btn_submit_close');
        btnSubmit.disabled = true;

        const payload = {
            final_decision: document.getElementById('modal_final_decision').value,
            decision_date: document.getElementById('modal_decision_date').value,
            closing_date: document.getElementById('modal_closing_date').value,
            return_date: document.getElementById('modal_return_date').value,
            new_battery_number: document.getElementById('modal_new_battery_number').value.trim(),
            delivery_mode: document.getElementById('modal_delivery_mode').value.trim(),
            complaint_status: document.getElementById('modal_complaint_status').value,
        };

        try {
            const data = await safeFetchJson("{{ url('/complaints') }}/" + complaintId + "/close-direct", {
                method: 'POST',
                body: JSON.stringify(payload)
            });

            if (data.success) {
                showToast('✓ ' + data.message, 'success');
                closeModalDirect();
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            }
        } catch (err) {
            showToast('❌ Failed to close case: ' + err.message, 'error');
        } finally {
            btnSubmit.disabled = false;
        }
    });
});
</script>
@endpush
