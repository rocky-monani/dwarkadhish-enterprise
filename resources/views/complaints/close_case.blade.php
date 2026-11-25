@extends('layouts.app')

@section('title', 'Close Complaint Case')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Header -->
    <div class="card" style="margin-bottom: 0; padding: 20px 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 600; color: var(--text-main); margin: 0;">
                    Close complaint case
                </h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                    Type into any field below to filter open complaints automatically.
                </p>
            </div>
            <a href="{{ route('complaints.pending_cases') }}" class="btn btn-outline">
                Pending cases
            </a>
        </div>
    </div>

    <!-- 4 Live Search Inputs -->
    <div class="card" style="margin-bottom: 0; padding: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                <span>Live search</span>
                <span id="search_spinner" class="spinner" style="display: none; border: 2px solid var(--primary); border-top-color: transparent; border-radius: 50%; width: 14px; height: 14px; animation: spin 0.8s linear infinite;"></span>
            </div>
            <button type="button" id="btn_clear_search" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;">
                Clear all
            </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="complaint_number_search" class="form-label">Complaint number</label>
                <input type="text" id="complaint_number_search" class="form-control live-search-input" style="font-family: 'JetBrains Mono', monospace;" placeholder="e.g. 1001" autocomplete="off" autofocus>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="customer_name_search" class="form-label">Customer name</label>
                <input type="text" id="customer_name_search" class="form-control live-search-input" placeholder="e.g. Rajesh" autocomplete="off">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="customer_mobile_search" class="form-label">Customer mobile</label>
                <input type="text" id="customer_mobile_search" class="form-control live-search-input" style="font-family: 'JetBrains Mono', monospace;" placeholder="e.g. 9876543210" autocomplete="off">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="battery_serial_search" class="form-label">Battery serial</label>
                <input type="text" id="battery_serial_search" class="form-control live-search-input" style="font-family: 'JetBrains Mono', monospace; text-transform: uppercase;" placeholder="e.g. EAB0206Z10..." autocomplete="off">
            </div>
        </div>
    </div>

    <!-- Live Matching Results -->
    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 0;">
        <div style="padding: 16px 20px; background-color: #ffffff; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div style="font-size: 14px; font-weight: 600; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                <span>Open cases</span>
                <span id="results_count_badge" class="badge badge-open tabular">Loading...</span>
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">
                Select Close case to record final settlement and replacement serial.
            </div>
        </div>

        <table id="close_case_table">
            <thead>
                    <tr>
                        <th>Complaint no</th>
                        <th>Inward date</th>
                        <th>Category / model</th>
                        <th>Customer</th>
                        <th>Mobile</th>
                        <th>Battery serial</th>
                        <th>CRM case</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
            <tbody id="live_results_body">
            </tbody>
        </table>
    </div>

</div>

<!-- Settlement Modal -->
<div id="close_case_modal_overlay" class="modal-overlay">
    <div class="modal-card" style="max-width: 540px;">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 600; color: var(--text-main); margin: 0;">
                Close &amp; settle complaint
            </h3>
            <button type="button" onclick="closeModalDirect()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <div style="background-color: var(--bg-color); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 13px;">
            <div>
                <span style="color: var(--text-muted); font-size: 12px; display: block;">Complaint no</span>
                <strong id="modal_complaint_no" class="tabular" style="font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: 500; color: var(--primary-dark);">-</strong>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 12px; display: block;">Battery serial</span>
                <strong id="modal_battery_serial" style="font-family: 'JetBrains Mono', monospace; font-weight: 500; color: var(--text-main);">-</strong>
            </div>
            <div style="grid-column: span 2; margin-top: 4px;">
                <span style="color: var(--text-muted); font-size: 12px; display: block;">Customer name</span>
                <strong id="modal_customer_name" style="font-weight: 500; color: var(--text-main);">-</strong>
            </div>
        </div>

        <form id="close_case_form">
            <input type="hidden" id="modal_complaint_id" value="">

            <div class="form-group">
                <label for="modal_final_decision" class="form-label">Final decision *</label>
                <select id="modal_final_decision" class="form-control" required>
                    <option value="">Select final decision</option>
                    <option value="F">F — Free replacement issued</option>
                    <option value="P">P — Pro-rata settlement</option>
                    <option value="C">C — Battery charged &amp; okay</option>
                    <option value="R">R — Dead return / rejected</option>
                </select>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
                <div class="form-group">
                    <label for="modal_decision_date" class="form-label">Decision date *</label>
                    <input type="date" id="modal_decision_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="modal_closing_date" class="form-label">Closing date *</label>
                    <input type="date" id="modal_closing_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="modal_return_date" class="form-label">Return date</label>
                    <input type="date" id="modal_return_date" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="modal_new_battery_number" class="form-label">New replacement battery serial (if issued)</label>
                <input type="text" id="modal_new_battery_number" class="form-control" style="font-family: 'JetBrains Mono', monospace; text-transform: uppercase;" placeholder="e.g. EAB0206Z19999">
            </div>

            <div class="form-group">
                <label for="modal_delivery_mode" class="form-label">Delivery / take person mode</label>
                <input type="text" id="modal_delivery_mode" class="form-control" placeholder="e.g. BY HIMSELF / SEND IN ROUTE">
            </div>

            <div class="form-group">
                <label for="modal_complaint_status" class="form-label">Status *</label>
                <select id="modal_complaint_status" class="form-control">
                    <option value="Closed" selected>Closed</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; margin-top: 20px;">
                <button type="button" onclick="closeModalDirect()" class="btn btn-outline">Cancel</button>
                <button type="submit" id="btn_submit_close" class="btn btn-primary">
                    Confirm &amp; close case
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

        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#close_case_table')) {
            jQuery('#close_case_table').DataTable().destroy();
        }

        if (results.length === 0) {
            tbody.innerHTML = '';
        } else {
            tbody.innerHTML = results.map(item => `
                <tr>
                    <td class="tabular" style="font-weight: 500; font-family: 'JetBrains Mono', monospace; color: var(--primary-dark);">
                        ${escapeHtml(item.complaint_number)}
                    </td>
                    <td class="tabular" style="color: var(--text-secondary); white-space: nowrap;">
                        ${escapeHtml(item.complaint_date)}
                    </td>
                    <td>
                        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; color: var(--text-secondary); margin-right: 6px;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background-color: ${item.battery_type === 'TW' ? '#b45309' : '#0369a1'};"></span>
                            ${escapeHtml(item.battery_type)}
                        </span>
                        <span style="font-family: 'JetBrains Mono', monospace;">${escapeHtml(item.product_model)}</span>
                    </td>
                    <td style="font-weight: 500; color: var(--text-main);">
                        ${escapeHtml(item.customer_name)}
                    </td>
                    <td class="tabular" style="font-family: 'JetBrains Mono', monospace; color: var(--text-muted);">
                        ${escapeHtml(item.customer_mobile)}
                    </td>
                    <td style="font-family: 'JetBrains Mono', monospace; font-weight: 500; color: var(--text-main);">
                        ${escapeHtml(item.battery_serial_number)}
                        ${item.is_prorata ? '<div style="margin-top: 2px;"><span class="badge badge-pending">Pro-rata</span></div>' : ''}
                    </td>
                    <td class="tabular" style="font-family: 'JetBrains Mono', monospace; color: var(--text-secondary);">
                        ${escapeHtml(item.case_number)}
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        ${isViewer
                            ? `<a href="/complaints/${item.id}" class="btn btn-outline" style="height: 28px; padding: 0 10px; font-size: 12px;">View</a>`
                            : `<button type="button" class="btn btn-warning" style="height: 28px; padding: 0 10px; font-size: 12px;" onclick='openCloseModal(${JSON.stringify(item)})'>Close case</button>`
                        }
                    </td>
                </tr>
            `).join('');
        }

        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.DataTable) {
            jQuery('#close_case_table').DataTable({
                dom: '<"dt-top"lf><"dt-scroll"t><"dt-bottom"ip>',
                autoWidth: false,
                pageLength: 15,
                order: [],
                language: {
                    search: 'Filter:',
                    emptyTable: 'No open complaints matched your search.'
                }
            });
        }
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
