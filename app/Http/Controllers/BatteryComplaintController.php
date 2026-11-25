<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatteryComplaintRequest;
use App\Http\Requests\UpdateBatteryComplaintRequest;
use App\Models\BatteryComplaint;
use App\Services\BatteryConnectService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BatteryComplaintController extends Controller
{
    protected BatteryConnectService $batteryConnectService;

    public function __construct(BatteryConnectService $batteryConnectService)
    {
        $this->batteryConnectService = $batteryConnectService;
    }

    /**
     * New Battery Entry page
     */
    public function create(Request $request)
    {
        $batteryType = strtoupper($request->query('battery_type', 'TW'));
        $nextComplaintNumber = BatteryComplaint::getNextComplaintNumber($batteryType);

        return view('complaints.create', compact('nextComplaintNumber', 'batteryType'));
    }

    /**
     * Get next complaint number via AJAX
     */
    public function nextNumber(Request $request)
    {
        $type = strtoupper($request->query('battery_type', 'TW'));

        return response()->json([
            'next_complaint_number' => BatteryComplaint::getNextComplaintNumber($type),
        ]);
    }

    /**
     * Check if a serial number has previous records (Duplicate Warning)
     */
    public function checkDuplicate(Request $request)
    {
        $serial = trim($request->input('serial_number', ''));
        if (empty($serial)) {
            return response()->json(['has_history' => false, 'count' => 0, 'has_open' => false, 'has_closed' => false]);
        }

        $previousRecords = BatteryComplaint::active()
            ->where('battery_serial_number', $serial)
            ->latest()
            ->get(['id', 'complaint_number', 'case_number', 'complaint_date', 'customer_name', 'complaint_status']);

        $openRecord = $previousRecords->first(function ($rec) {
            return $rec->complaint_status === 'Open';
        });

        $closedRecord = $previousRecords->first(function ($rec) {
            return in_array($rec->complaint_status, ['Closed', 'Rejected']);
        });

        $openDate = ($openRecord && $openRecord->complaint_date) ? Carbon::parse($openRecord->complaint_date)->format('d/m/Y') : '';
        $closedDate = ($closedRecord && $closedRecord->complaint_date) ? Carbon::parse($closedRecord->complaint_date)->format('d/m/Y') : '';

        return response()->json([
            'has_history' => $previousRecords->isNotEmpty(),
            'count' => $previousRecords->count(),
            'has_open' => !is_null($openRecord),
            'open_record' => $openRecord,
            'open_date' => $openDate,
            'has_closed' => !is_null($closedRecord),
            'closed_record' => $closedRecord,
            'closed_date' => $closedDate,
            'records' => $previousRecords,
        ]);
    }

    /**
     * AJAX fetch Battery Connect details via Playwright automation
     */
    public function fetchBatteryConnect(Request $request)
    {
        @set_time_limit(240);
        $request->validate([
            'serial_number' => 'required|string|max:100',
            'complaint_number' => 'nullable|string|max:100',
        ]);

        $serialNumber = trim($request->input('serial_number'));
        $complaintNumber = $request->input('complaint_number') ? trim($request->input('complaint_number')) : null;
        $result = $this->batteryConnectService->lookup($serialNumber, $complaintNumber);

        return response()->json($result);
    }

    /**
     * AJAX endpoint to launch headed browser for Battery Connect session login
     */
    public function openLoginBrowser()
    {
        $result = $this->batteryConnectService->openLoginBrowser();

        return response()->json($result);
    }

    /**
     * Get list of complaints matching range from complaint_number to complaint_number
     */
    public function batchFetchList(Request $request)
    {
        $request->validate([
            'from_complaint' => 'required|string|max:100',
            'to_complaint' => 'required|string|max:100',
            'missing_only' => 'nullable|boolean',
            'battery_type' => 'nullable|string|max:10',
        ]);

        $fromInput = trim($request->input('from_complaint'));
        $toInput = trim($request->input('to_complaint'));
        $missingOnly = $request->boolean('missing_only');
        $category = strtoupper($request->input('battery_type', 'ALL'));

        $fromNum = preg_replace('/[^0-9]/', '', $fromInput);
        $toNum = preg_replace('/[^0-9]/', '', $toInput);

        $query = BatteryComplaint::active();

        if ($category === 'TW') {
            $query->where('battery_type', 'TW');
        } elseif ($category === 'FW') {
            $query->where('battery_type', '!=', 'TW');
        }

        if ($missingOnly) {
            $query->where(function ($q) {
                $q->whereNull('battery_connect_image_path')
                  ->orWhere('battery_connect_image_path', '');
            });
        }

        $allComplaints = $query->toBase()
            ->select(['id', 'complaint_number', 'battery_serial_number', 'customer_name', 'battery_type', 'battery_connect_image_path'])
            ->orderBy('id', 'asc')
            ->get();

        $matched = $allComplaints->filter(function ($c) use ($fromInput, $toInput, $fromNum, $toNum) {
            $cNo = trim($c->complaint_number);

            if (strcasecmp($cNo, $fromInput) === 0 || strcasecmp($cNo, $toInput) === 0) {
                return true;
            }

            $cNum = preg_replace('/[^0-9]/', '', $cNo);
            if ($fromNum !== '' && $toNum !== '' && $cNum !== '') {
                $min = min((int)$fromNum, (int)$toNum);
                $max = max((int)$fromNum, (int)$toNum);
                $val = (int)$cNum;
                if ($val >= $min && $val <= $max) {
                    return true;
                }
            }

            $minStr = min($fromInput, $toInput);
            $maxStr = max($fromInput, $toInput);
            return strcmp($cNo, $minStr) >= 0 && strcmp($cNo, $maxStr) <= 0;
        })->values();

        $resultList = $matched->map(function ($c) {
            return [
                'id' => $c->id,
                'complaint_number' => $c->complaint_number,
                'battery_serial_number' => $c->battery_serial_number,
                'battery_type' => $c->battery_type,
                'customer_name' => $c->customer_name ?? '—',
                'product_model' => $c->product_model ?? '—',
                'has_screenshot' => !empty($c->battery_connect_image_path),
                'image_path' => $c->battery_connect_image_path ? asset($c->battery_connect_image_path) : null,
                'status' => 'PENDING',
            ];
        });

        return response()->json([
            'success' => true,
            'total_found' => $resultList->count(),
            'complaints' => $resultList,
        ]);
    }

    /**
     * Helper to auto-populate ALL missing complaint details from Battery Connect metadata
     */
    protected function applyConnectMetadataToComplaint(BatteryComplaint $complaint, array $metaData, ?string $imagePath = null): array
    {
        $updateData = [];

        if ($imagePath) {
            $updateData['battery_connect_image_path'] = $imagePath;
        }

        $updateData['battery_connect_lookup_at'] = now();
        $updateData['battery_connect_data'] = $metaData;

        // Auto populate product_model if missing
        if (!empty($metaData['product_model']) && empty($complaint->product_model)) {
            $updateData['product_model'] = trim($metaData['product_model']);
        }

        // Auto populate product_segment if missing
        if (!empty($metaData['product_segment']) && empty($complaint->product_segment)) {
            $updateData['product_segment'] = trim($metaData['product_segment']);
        }

        // Auto populate purchase_date (date_of_sale) if missing
        if (!empty($metaData['date_of_sale']) && empty($complaint->purchase_date)) {
            try {
                $time = strtotime($metaData['date_of_sale']);
                if ($time) {
                    $updateData['purchase_date'] = date('Y-m-d', $time);
                }
            } catch (\Exception $e) {
            }
        }

        // Auto populate vehicle_name if missing
        if (!empty($metaData['vehicle_name']) && empty($complaint->vehicle_name)) {
            $updateData['vehicle_name'] = trim($metaData['vehicle_name']);
        }

        // Auto populate vehicle_number if missing
        if (!empty($metaData['vehicle_number']) && empty($complaint->vehicle_number)) {
            $updateData['vehicle_number'] = trim($metaData['vehicle_number']);
        }

        // Auto populate customer_name if missing
        if (!empty($metaData['customer_name']) && (empty($complaint->customer_name) || strtolower(trim($complaint->customer_name)) === 'walk-in' || strtolower(trim($complaint->customer_name)) === 'customer')) {
            $updateData['customer_name'] = trim($metaData['customer_name']);
        }

        // Auto populate customer_mobile if missing
        if (!empty($metaData['customer_mobile']) && empty($complaint->customer_mobile)) {
            $updateData['customer_mobile'] = trim($metaData['customer_mobile']);
        }

        // Auto populate prorata details if missing
        if (isset($metaData['prorata_percentage']) && $metaData['prorata_percentage'] !== null && empty($complaint->prorata_percentage)) {
            $updateData['prorata_percentage'] = (float)$metaData['prorata_percentage'];
            $updateData['is_prorata'] = true;
        }

        $complaint->update($updateData);

        return $updateData;
    }

    /**
     * Get list of ALL Open/Pending complaints requiring Battery Connect screenshot or details
     */
    public function batchFetchPendingList(Request $request)
    {
        $category = strtoupper($request->input('battery_type', 'ALL'));
        $query = BatteryComplaint::active()->where('complaint_status', 'Open');

        if ($category === 'TW') {
            $query->where('battery_type', 'TW');
        } elseif ($category === 'FW') {
            $query->where('battery_type', '!=', 'TW');
        }

        if ($request->boolean('missing_only')) {
            $query->where(function ($q) {
                $q->whereNull('battery_connect_image_path')
                  ->orWhere('battery_connect_image_path', '');
            });
        }

        $allComplaints = $query->orderBy('id', 'desc')->get();

        $resultList = $allComplaints->map(function ($c) {
            return [
                'id' => $c->id,
                'complaint_number' => $c->complaint_number,
                'battery_serial_number' => $c->battery_serial_number,
                'battery_type' => $c->battery_type,
                'customer_name' => $c->customer_name ?? '—',
                'product_model' => $c->product_model ?? '—',
                'has_screenshot' => !empty($c->battery_connect_image_path),
                'image_path' => $c->battery_connect_image_path ? asset($c->battery_connect_image_path) : null,
                'status' => 'PENDING',
            ];
        });

        return response()->json([
            'success' => true,
            'total_found' => $resultList->count(),
            'complaints' => $resultList,
        ]);
    }

    /**
     * Process single complaint screenshot fetch in batch range
     */
    public function batchFetchSingle(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:battery_complaints,id',
        ]);

        $complaint = BatteryComplaint::active()->findOrFail($request->input('id'));

        if (empty($complaint->battery_serial_number)) {
            return response()->json([
                'success' => false,
                'id' => $complaint->id,
                'complaint_number' => $complaint->complaint_number,
                'battery_serial_number' => '—',
                'code' => 'MISSING_SERIAL',
                'message' => 'No battery serial number recorded for this complaint.',
            ]);
        }

        $result = $this->batteryConnectService->lookup($complaint->battery_serial_number, $complaint->complaint_number);

        if ($result['status'] === 'SUCCESS') {
            $this->applyConnectMetadataToComplaint($complaint, $result['battery_connect_data'] ?? [], $result['image_path']);

            return response()->json([
                'success' => true,
                'id' => $complaint->id,
                'complaint_number' => $complaint->complaint_number,
                'battery_serial_number' => $complaint->battery_serial_number,
                'code' => 'CAPTURE_SUCCESS',
                'message' => 'Screenshot & details fetched successfully!',
                'image_path' => asset($result['image_path']),
            ]);
        }

        return response()->json([
            'success' => false,
            'id' => $complaint->id,
            'complaint_number' => $complaint->complaint_number,
            'battery_serial_number' => $complaint->battery_serial_number,
            'code' => $result['code'] ?? 'AUTOMATION_ERROR',
            'message' => $result['message'] ?? 'Screenshot fetch failed.',
        ]);
    }

    /**
     * Save new complaint record
     */
    public function store(StoreBatteryComplaintRequest $request)
    {
        $validated = $request->validated();

        $cleanSerial = trim($validated['battery_serial_number']);
        $existingOpen = BatteryComplaint::active()
            ->where('battery_serial_number', $cleanSerial)
            ->where('complaint_status', 'Open')
            ->first();

        if ($existingOpen) {
            return back()->withInput()->withErrors([
                'battery_serial_number' => "An open complaint (#{$existingOpen->complaint_number}) already exists for battery serial number '{$cleanSerial}'. Please close the previous case of this battery first before creating a new inward entry.",
            ]);
        }

        $imagePath = $validated['battery_connect_image_path'] ?? null;

        // Handle manual screenshot upload if attached
        if ($request->hasFile('manual_screenshot')) {
            $path = $request->file('manual_screenshot')->store('battery_captures', 'public');
            $imagePath = 'storage/' . $path;
        }

        $connectData = null;
        if ($request->input('battery_connect_data')) {
            $connectData = is_array($request->input('battery_connect_data'))
                ? $request->input('battery_connect_data')
                : json_decode($request->input('battery_connect_data'), true);
        }

        $purchaseDate = $validated['purchase_date'] ?? null;
        if (empty($purchaseDate) && !empty($connectData['date_of_sale'])) {
            try {
                $time = strtotime($connectData['date_of_sale']);
                if ($time) {
                    $purchaseDate = date('Y-m-d', $time);
                }
            } catch (\Exception $e) {
            }
        }

        $prorataPct = !empty($validated['prorata_percentage']) ? (float) $validated['prorata_percentage'] : (!empty($connectData['prorata_percentage']) ? (float) $connectData['prorata_percentage'] : null);
        $isProrata = $request->boolean('is_prorata') || !empty($prorataPct) || (!empty($connectData['warranty_status']) && (bool) preg_match('/prorata|out\s*of\s*warranty|expired|no\s*warranty/i', $connectData['warranty_status']));

        $caseNumber = !empty($validated['case_number']) ? trim($validated['case_number']) : (empty($imagePath) ? 'NO ONLINE' : null);

        $complaint = BatteryComplaint::create([
            'battery_type' => !empty($validated['battery_type']) ? strtoupper(trim($validated['battery_type'])) : 'TW',
            'battery_serial_number' => trim($validated['battery_serial_number']),
            'product_model' => !empty($validated['product_model']) ? trim($validated['product_model']) : null,
            'product_segment' => !empty($validated['product_segment']) ? trim($validated['product_segment']) : (!empty($connectData['product_segment']) ? trim($connectData['product_segment']) : (!empty($validated['battery_type']) && strtoupper($validated['battery_type']) === 'FW' ? 'FW' : 'TW')),
            'complaint_number' => trim($validated['complaint_number']),
            'case_number' => $caseNumber,
            'complaint_date' => $validated['complaint_date'],
            'customer_name' => trim($validated['customer_name']),
            'customer_mobile' => !empty($validated['customer_mobile']) ? trim($validated['customer_mobile']) : null,
            'location' => !empty($validated['location']) ? trim($validated['location']) : null,
            'purchase_date' => $purchaseDate,
            'vehicle_name' => isset($validated['vehicle_name']) ? trim($validated['vehicle_name']) : null,
            'vehicle_number' => isset($validated['vehicle_number']) ? trim($validated['vehicle_number']) : null,
            'online_reg_type' => !empty($validated['online_reg_type']) ? trim($validated['online_reg_type']) : null,
            'is_digital_warranty' => $request->boolean('is_digital_warranty'),
            'is_service_battery_given' => $request->boolean('is_service_battery_given'),
            'service_battery_number' => $request->boolean('is_service_battery_given') ? trim($validated['service_battery_number'] ?? '') : null,
            'is_prorata' => $isProrata,
            'prorata_percentage' => $isProrata ? $prorataPct : null,
            'battery_connect_image_path' => $imagePath,
            'battery_connect_lookup_at' => now(),
            'battery_connect_data' => $connectData,
            'created_by' => Auth::id(),
            'complaint_status' => 'Open',
        ]);

        if ($request->input('action') === 'save_and_print') {
            return redirect()->route('complaints.print', $complaint->id);
        }

        return redirect()->route('complaints.create')->with('success', "Battery complaint inward record saved successfully! Sent to Pending Cases list for Operator 2.");
    }

    /**
     * Complaint Records listing & search page
     */
    public function index(Request $request)
    {
        $query = BatteryComplaint::active()->with(['creator', 'closedBy'])->latest('complaint_date');

        // Global search across serial, complaint #, case #, name, mobile, vehicle number
        if ($search = trim($request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('battery_serial_number', 'like', "%{$search}%")
                    ->orWhere('complaint_number', 'like', "%{$search}%")
                    ->orWhere('case_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_name', 'like', "%{$search}%")
                    ->orWhere('service_battery_number', 'like', "%{$search}%")
                    ->orWhere('new_battery_number', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('complaint_status', $status);
        }

        // Category / Battery type filter
        if ($batteryType = strtoupper(trim($request->input('battery_type', '')))) {
            if ($batteryType === 'TW' || $batteryType === '2W') {
                $query->where(function ($q) {
                    $q->where('battery_type', 'TW')
                      ->orWhere('excel_sheet', '2W');
                });
            } elseif (in_array($batteryType, ['FW', '4W'])) {
                $query->where(function ($q) {
                    $q->where('battery_type', 'FW')
                      ->orWhere('excel_sheet', '4W');
                });
            }
        }

        // Date range filtering
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('complaint_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('complaint_date', '<=', $dateTo);
        }

        $complaints = $query->paginate(20)->withQueryString();

        return view('complaints.index', compact('complaints'));
    }

    /**
     * Single record detail view
     */
    public function show($id)
    {
        $complaint = BatteryComplaint::active()->with(['creator', 'closedBy'])->findOrFail($id);

        return view('complaints.show', compact('complaint'));
    }

    /**
     * Edit record form
     */
    public function edit($id)
    {
        $complaint = BatteryComplaint::active()->findOrFail($id);

        return view('complaints.edit', compact('complaint'));
    }

    /**
     * Update record
     */
    public function update(UpdateBatteryComplaintRequest $request, $id)
    {
        $complaint = BatteryComplaint::active()->findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('manual_screenshot')) {
            $path = $request->file('manual_screenshot')->store('battery_captures', 'public');
            $validated['battery_connect_image_path'] = 'storage/' . $path;
        }

        $validated['is_prorata'] = $request->boolean('is_prorata');
        $validated['prorata_percentage'] = $request->boolean('is_prorata') ? $validated['prorata_percentage'] : null;
        $validated['is_digital_warranty'] = $request->boolean('is_digital_warranty');
        $validated['is_service_battery_given'] = $request->boolean('is_service_battery_given');
        $validated['service_battery_number'] = $request->boolean('is_service_battery_given') ? trim($validated['service_battery_number'] ?? '') : null;

        // Auto set closed_by if status changed to Closed or Rejected
        if (isset($validated['complaint_status']) && in_array($validated['complaint_status'], ['Closed', 'Rejected']) && $complaint->complaint_status === 'Open') {
            $validated['closed_by'] = Auth::id();
            $validated['closing_date'] = $validated['closing_date'] ?? date('Y-m-d');
        }

        $complaint->update($validated);

        return redirect()->route('complaints.show', $complaint->id)->with('success', 'Record updated successfully.');
    }

    /**
     * Close / Settle Complaint
     */
    public function closeComplaint(Request $request, $id)
    {
        $complaint = BatteryComplaint::active()->findOrFail($id);

        $validated = $request->validate([
            'final_decision' => 'required|string|max:100',
            'decision_date' => 'required|date',
            'new_battery_number' => 'nullable|string|max:100',
            'delivery_mode' => 'nullable|string|max:150',
            'closing_date' => 'required|date',
            'return_date' => 'nullable|date',
            'complaint_status' => 'required|string|in:Closed,Rejected',
        ]);

        $complaint->update([
            'final_decision' => trim($validated['final_decision']),
            'decision_date' => $validated['decision_date'],
            'new_battery_number' => !empty($validated['new_battery_number']) ? trim($validated['new_battery_number']) : null,
            'delivery_mode' => !empty($validated['delivery_mode']) ? trim($validated['delivery_mode']) : null,
            'closing_date' => $validated['closing_date'],
            'return_date' => !empty($validated['return_date']) ? $validated['return_date'] : null,
            'complaint_status' => $validated['complaint_status'],
            'closed_by' => Auth::id(),
        ]);

        return redirect()->route('complaints.show', $complaint->id)->with('success', "Complaint #{$complaint->complaint_number} closed successfully.");
    }

    /**
     * Reopen a closed or rejected complaint
     */
    public function reopenComplaint($id)
    {
        $complaint = BatteryComplaint::active()->findOrFail($id);

        $complaint->update([
            'complaint_status' => 'Open',
            'closed_by' => null,
            'closing_date' => null,
        ]);

        return redirect()->route('complaints.show', $complaint->id)->with('success', "Complaint #{$complaint->complaint_number} reopened successfully! Status is now OPEN.");
    }

    /**
     * Explicitly re-fetch Battery Connect screenshot for an existing record
     */
    public function refetchBatteryConnect($id)
    {
        $complaint = BatteryComplaint::active()->findOrFail($id);
        $result = $this->batteryConnectService->lookup($complaint->battery_serial_number, $complaint->complaint_number);

        if ($result['status'] === 'SUCCESS') {
            $this->applyConnectMetadataToComplaint($complaint, $result['battery_connect_data'] ?? [], $result['image_path']);

            return back()->with('success', 'Battery Connect screenshot & details re-fetched and updated!');
        }

        return back()->with('error', 'Re-fetch failed: ' . ($result['message'] ?? $result['status']));
    }

    /**
     * Soft delete complaint record
     */
    public function destroy($id)
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized action. Operators cannot delete complaint records.');
        }

        $complaint = BatteryComplaint::active()->findOrFail($id);
        $complaint->update(['is_deleted' => true]);
        $complaint->delete(); // Standard Laravel SoftDelete (sets deleted_at = now())

        return redirect()->route('complaints.index')->with('success', 'Record deleted successfully.');
    }

    /**
     * Pending Cases Page for Operator 2 (CRM Case Entry & Batch / Single Printing)
     * Shows all still OPEN pending cases by default.
     */
    public function pendingCases(Request $request)
    {
        $todayDate = date('Y-m-d');
        $query = BatteryComplaint::active();

        // Default to OPEN complaints only
        $selectedStatus = $request->input('status', 'Open');
        if ($selectedStatus !== 'all') {
            $query->where('complaint_status', $selectedStatus);
        }

        // Default to TODAY's date so Operator 2 sees today's pending cases and unprinted documents
        $selectedDate = $request->input('date', 'today');
        if ($selectedDate === 'today') {
            $query->whereDate('complaint_date', $todayDate);
        } elseif ($selectedDate === 'backdate') {
            $query->whereDate('complaint_date', '<', $todayDate);
        } elseif ($selectedDate !== 'all' && !empty($selectedDate)) {
            $query->whereDate('complaint_date', $selectedDate);
        }

        // Optional filter for unprinted documents only
        if ($request->input('unprinted') === '1') {
            $query->where('print_count', 0);
        }

        // Filter by battery category if specified
        if ($category = $request->input('battery_type')) {
            if ($category === 'TW') {
                $query->where('battery_type', 'TW');
            } elseif ($category === 'FW') {
                $query->where('battery_type', '!=', 'TW');
            }
        }

        // Filter by online_reg_type if specified
        if ($regType = $request->input('online_reg_type')) {
            if ($regType === 'none') {
                $query->where(function($q) {
                    $q->whereNull('online_reg_type')->orWhere('online_reg_type', '');
                });
            } else {
                $query->where('online_reg_type', $regType);
            }
        }

        // Search term
        if ($search = trim($request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('battery_serial_number', 'like', "%{$search}%")
                    ->orWhere('complaint_number', 'like', "%{$search}%")
                    ->orWhere('case_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%");
            });
        }

        $complaints = $query->orderBy('complaint_date', 'desc')->orderBy('id', 'desc')->paginate(30)->withQueryString();

        // Single aggregate query for all pending page counts (replaces 5 separate COUNT queries)
        $counts = BatteryComplaint::active()
            ->toBase()
            ->where('complaint_status', 'Open')
            ->selectRaw("
                COUNT(*) as total_open,
                SUM(CASE WHEN DATE(complaint_date) = ? AND battery_type = 'TW' AND print_count = 0 THEN 1 ELSE 0 END) as unprinted_2w,
                SUM(CASE WHEN DATE(complaint_date) = ? AND battery_type != 'TW' AND print_count = 0 THEN 1 ELSE 0 END) as unprinted_4w,
                SUM(CASE WHEN DATE(complaint_date) < ? AND battery_type = 'TW' THEN 1 ELSE 0 END) as backdate_tw,
                SUM(CASE WHEN DATE(complaint_date) < ? AND battery_type != 'TW' THEN 1 ELSE 0 END) as backdate_fw
            ", [$todayDate, $todayDate, $todayDate, $todayDate])
            ->first();

        $totalOpenPendingCount  = (int) ($counts->total_open ?? 0);
        $unprintedToday2WCount  = (int) ($counts->unprinted_2w ?? 0);
        $unprintedToday4WCount  = (int) ($counts->unprinted_4w ?? 0);
        $unprintedTodayCount    = $unprintedToday2WCount + $unprintedToday4WCount;
        $backdatePendingTWCount = (int) ($counts->backdate_tw ?? 0);
        $backdatePendingFWCount = (int) ($counts->backdate_fw ?? 0);
        $backdatePendingCount   = $backdatePendingTWCount + $backdatePendingFWCount;

        return view('complaints.pending_cases', compact('complaints', 'unprintedTodayCount', 'unprintedToday2WCount', 'unprintedToday4WCount', 'totalOpenPendingCount', 'backdatePendingCount', 'backdatePendingTWCount', 'backdatePendingFWCount', 'todayDate', 'selectedDate', 'selectedStatus'));
    }

    /**
     * Update CRM Case Number for a complaint (Operator 2)
     */
    public function updateCaseNumber(Request $request, $id)
    {
        $complaint = BatteryComplaint::active()->findOrFail($id);

        $validated = $request->validate([
            'case_number' => 'nullable|string|max:100',
        ]);

        $complaint->update([
            'case_number' => !empty($validated['case_number']) ? trim($validated['case_number']) : null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "CRM Case Number updated for Complaint {$complaint->complaint_number}!",
                'case_number' => $complaint->case_number,
            ]);
        }

        return back()->with('success', "CRM Case Number updated for Complaint {$complaint->complaint_number}!");
    }

    /**
     * Update Customer Mobile number for a complaint (Operator 2)
     */
    public function updateCustomerMobile(Request $request, $id)
    {
        $complaint = BatteryComplaint::active()->findOrFail($id);

        $validated = $request->validate([
            'customer_mobile' => 'nullable|string|max:20',
        ]);

        $complaint->update([
            'customer_mobile' => !empty($validated['customer_mobile']) ? trim($validated['customer_mobile']) : null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Customer Mobile updated for Complaint {$complaint->complaint_number}!",
                'customer_mobile' => $complaint->customer_mobile,
            ]);
        }

        return back()->with('success', "Customer Mobile updated for Complaint {$complaint->complaint_number}!");
    }

    /**
     * Update Warranty Card / RC Book status for a complaint (Operator 2)
     */
    public function updateRegType(Request $request, $id)
    {
        $complaint = BatteryComplaint::active()->findOrFail($id);

        $validated = $request->validate([
            'online_reg_type' => 'nullable|string|max:50',
        ]);

        $complaint->update([
            'online_reg_type' => !empty($validated['online_reg_type']) ? trim($validated['online_reg_type']) : null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Doc / Reg Status updated for Complaint {$complaint->complaint_number}!",
                'online_reg_type' => $complaint->online_reg_type,
            ]);
        }

        return back()->with('success', "Doc / Reg Status updated for Complaint {$complaint->complaint_number}!");
    }

    /**
     * Page to search and close complaint cases letter-by-letter (Operator 2 / Settlement Station)
     */
    public function closeCaseView()
    {
        return view('complaints.close_case');
    }

    /**
     * Live AJAX search open complaints letter-by-letter across 4 separate input fields
     */
    public function liveSearchOpen(Request $request)
    {
        $complaintNo = trim($request->input('complaint_number', ''));
        $customerName = trim($request->input('customer_name', ''));
        $customerMobile = trim($request->input('customer_mobile', ''));
        $serialNumber = trim($request->input('battery_serial', ''));

        $query = BatteryComplaint::active()->where('complaint_status', 'Open');
        $hasFilter = false;

        if (!empty($complaintNo)) {
            $query->where('complaint_number', 'like', "%{$complaintNo}%");
            $hasFilter = true;
        }

        if (!empty($customerName)) {
            $query->where('customer_name', 'like', "%{$customerName}%");
            $hasFilter = true;
        }

        if (!empty($customerMobile)) {
            $query->where('customer_mobile', 'like', "%{$customerMobile}%");
            $hasFilter = true;
        }

        if (!empty($serialNumber)) {
            $query->where('battery_serial_number', 'like', "%{$serialNumber}%");
            $hasFilter = true;
        }

        if (!$hasFilter) {
            $results = $query->latest('id')->take(20)->get();
        } else {
            $results = $query->latest('id')->take(50)->get();
        }

        $formatted = $results->map(function ($item) {
            return [
                'id' => $item->id,
                'complaint_number' => $item->complaint_number,
                'battery_type' => $item->battery_type,
                'battery_serial_number' => $item->battery_serial_number ?: 'N/A',
                'customer_name' => $item->customer_name ?: 'N/A',
                'customer_mobile' => $item->customer_mobile ?: 'N/A',
                'complaint_date' => $item->complaint_date ? $item->complaint_date->format('d-m-Y') : 'N/A',
                'raw_complaint_date' => $item->complaint_date ? $item->complaint_date->format('Y-m-d') : null,
                'product_model' => $item->product_model ?: 'N/A',
                'case_number' => $item->case_number ?: 'N/A',
                'delivery_mode' => $item->delivery_mode ?: '',
                'is_prorata' => (bool) $item->is_prorata,
                'prorata_percentage' => $item->prorata_percentage,
                'lead_time_days' => $item->lead_time_days,
                'is_lead_time_exceeded' => $item->isLeadTimeExceeded(),
                'print_count' => (int) $item->print_count,
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $results->count(),
            'results' => $formatted,
        ]);
    }

    /**
     * Direct complaint settlement / closure from Close Case page
     */
    public function closeDirect(Request $request, $id)
    {
        $complaint = BatteryComplaint::active()->findOrFail($id);

        $validated = $request->validate([
            'final_decision' => 'required|string|max:100',
            'decision_date' => 'required|date',
            'new_battery_number' => 'nullable|string|max:100',
            'delivery_mode' => 'nullable|string|max:150',
            'closing_date' => 'required|date',
            'return_date' => 'nullable|date',
            'complaint_status' => 'required|string|in:Closed,Rejected',
        ]);

        $complaint->update([
            'final_decision' => trim($validated['final_decision']),
            'decision_date' => $validated['decision_date'],
            'new_battery_number' => !empty($validated['new_battery_number']) ? trim($validated['new_battery_number']) : null,
            'delivery_mode' => !empty($validated['delivery_mode']) ? trim($validated['delivery_mode']) : null,
            'closing_date' => $validated['closing_date'],
            'return_date' => !empty($validated['return_date']) ? $validated['return_date'] : null,
            'complaint_status' => $validated['complaint_status'],
            'closed_by' => Auth::id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Complaint {$complaint->complaint_number} closed successfully as '{$validated['complaint_status']}'!",
            ]);
        }

        return back()->with('success', "Complaint {$complaint->complaint_number} closed successfully!");
    }
}
