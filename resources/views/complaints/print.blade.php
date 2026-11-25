<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Complaint {{ $complaint->complaint_number }} - Dwarkadhish Automobiles</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #525659;
            color: #000000;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            min-height: 100vh;
        }

        /* Top Action Bar (Screen Only) */
        .no-print-bar {
            width: 210mm;
            background-color: #ffffff;
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-print {
            background-color: #059669;
            color: #ffffff;
            border: none;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover {
            background-color: #047857;
        }

        .btn-back {
            background-color: #64748b;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
        }

        /* A4 Page Container */
        .a4-page {
            width: 210mm;
            height: 297mm;
            background-color: #ffffff;
            padding: 12mm 15mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            position: relative;
            box-sizing: border-box;
        }

        /* EXACT TWO-LINE HEADER SECTION */
        .print-header-two-lines {
            border: 2px solid #000000;
            padding: 6px 12px;
            margin-bottom: 8px;
            font-size: 13px;
            line-height: 1.4;
            font-weight: 700;
        }

        .header-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .header-line:not(:last-child) {
            border-bottom: 1px solid #000000;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }

        .header-item {
            white-space: nowrap;
        }

        /* MIDDLE BATTERY CONNECT CAPTURE SECTION */
        .capture-container {
            width: 100%;
            margin-bottom: 10px;
            text-align: center;
            border: 1px solid #cccccc;
            padding: 4px;
            background-color: #ffffff;
        }

        .capture-wrapper {
            position: relative;
            display: inline-block;
            max-width: 100%;
        }

        .capture-container img {
            max-width: 100%;
            max-height: 165mm;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        /* BARCODE / QR CODE REDACTION CROSS OVERLAY */
        .barcode-cross-overlay {
            position: absolute;
            top: 25.5%;
            left: 20.1%;
            width: 13.8%;
            height: 21.2%;
            pointer-events: none;
            overflow: hidden;
            background-color: rgba(255, 255, 255, 0.7);
            border: 2px solid #dc2626;
            border-radius: 4px;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .barcode-cross-overlay::before,
        .barcode-cross-overlay::after {
            content: "";
            position: absolute;
            width: 150%;
            height: 4px;
            background-color: #dc2626;
            top: 50%;
            left: -25%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .barcode-cross-overlay::before {
            transform: rotate(45deg);
        }

        .barcode-cross-overlay::after {
            transform: rotate(-45deg);
        }

        /* LOWER HANDWRITTEN INSPECTION SPACE */
        .handwritten-inspection-space {
            flex-grow: 1;
            border: 1px dashed #999999;
            border-radius: 4px;
            padding: 8px;
            position: relative;
        }

        .inspection-watermark {
            font-size: 11px;
            color: #888888;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* PRINT STYLING OVERRIDES */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            body {
                background: none !important;
                padding: 0 !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .a4-page {
                width: 210mm !important;
                height: 297mm !important;
                box-shadow: none !important;
                padding: 10mm 12mm !important;
                margin: 0 !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>

<body>

    <!-- Screen Action Header -->
    <div class="no-print-bar">
        <div style="font-weight: 700; font-size: 15px; color: #1e293b;">
            🖨️ A4 Print Preview — Complaint {{ $complaint->complaint_number }}
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('complaints.show', $complaint->id) }}" class="btn-back">← Back</a>
            <button type="button" class="btn-print" onclick="window.print()">
                🖨️ PRINT DOCUMENT
            </button>
        </div>
    </div>

    @php
        $pendingFields = [];
        if (empty($complaint->case_number))
            $pendingFields[] = 'CRM Case Number';
        if (empty($complaint->customer_mobile))
            $pendingFields[] = 'Customer Mobile Number';
        if (empty($complaint->purchase_date))
            $pendingFields[] = 'Purchase Date';
        if (empty($complaint->vehicle_name))
            $pendingFields[] = 'Vehicle Model';
        if (empty($complaint->vehicle_number))
            $pendingFields[] = 'Vehicle Reg Number';
        if (empty($complaint->battery_connect_image_path))
            $pendingFields[] = 'Connect Screenshot';
    @endphp

    @if(count($pendingFields) > 0)
        <!-- Pending Details Screen Alert for Operator (Hidden on Paper Print) -->
        <div class="no-print-bar"
            style="background-color: #fffbe2; border: 1px solid #fcd34d; border-left: 5px solid #d97706; color: #78350f; padding: 12px 18px; margin-bottom: 15px;">
            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.4rem;">⚠️</span>
                    <div>
                        <strong style="font-size: 0.95rem;">PENDING DETAILS ALERT FOR OPERATOR:</strong>
                        <div style="font-size: 0.85rem; margin-top: 2px;">
                            The following record details are currently missing/pending:
                            <span style="font-weight: 800; color: #b45309;">{{ implode(' • ', $pendingFields) }}</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('complaints.edit', $complaint->id) }}" class="btn-back"
                    style="background-color: #d97706; white-space: nowrap; font-size: 13px; font-weight: 700;">
                    ✏️ Edit & Complete Details
                </a>
            </div>
        </div>
    @endif

    <!-- A4 Document Page -->
    <div class="a4-page">

        <!-- TOP HEADER: THREE LINES -->
        <div class="print-header-two-lines">
            <!-- LINE 1 -->
            <div class="header-line">
                <span class="header-item"><strong>Co. No: {{ $complaint->complaint_number }}</strong></span>
                @if($complaint->case_number)
                    <span class="header-item"><strong>Case No: {{ $complaint->case_number }}</strong></span>
                @endif
                <span class="header-item"><strong>Date:
                        {{ $complaint->complaint_date ? $complaint->complaint_date->format('d/m/Y') : '' }}</strong></span>
            </div>

            <!-- LINE 2 (Upper Side: Name, Mobile, Product Model, Pro-Rata) -->
            <div class="header-line">
                @if($complaint->customer_name)
                    <span class="header-item"><strong>C/O: {{ $complaint->customer_name }}</strong></span>
                @endif
                @if($complaint->customer_mobile)
                    <span class="header-item"><strong>Mob: {{ $complaint->customer_mobile }}</strong></span>
                @endif
                @if($complaint->is_prorata || $complaint->prorata_percentage)
                    <span class="header-item" style="color: #b45309;">
                        <strong>PRO-RATA:
                            {{ $complaint->prorata_percentage ? ((float) $complaint->prorata_percentage . '%') : 'YES' }}</strong>
                    </span>
                @endif
                @if($complaint->product_model)
                    <span class="header-item" style="margin-right: 20px;"><strong>Prod:
                            {{ $complaint->product_model }}</strong></span>
                @endif

            </div>

            <!-- LINE 3 (Lower Side: Location, Service Battery, Battery Serial Number) -->
            <div class="header-line">
                @if($complaint->location)
                    <span class="header-item"><strong>Loc: {{ $complaint->location }}</strong></span>
                @endif
                @if($complaint->is_service_battery_given || !empty($complaint->service_battery_number))
                    <span class="header-item" style="color: #047857; {{ $complaint->location ? 'margin-left: 60px;' : '' }}"><strong>Serv. Batt: {{ $complaint->service_battery_number ?: 'YES' }}</strong></span>
                @endif
                <span class="header-item" style="margin-left: auto;"><strong>S/N:
                        {{ $complaint->battery_serial_number }}</strong></span>
            </div>
        </div>

        <!-- MIDDLE SECTION: BATTERY CONNECT CAPTURED RESULT SCREENSHOT -->
        @if($complaint->battery_connect_image_path)
            <div class="capture-container">
                <div class="capture-wrapper">
                    <img src="{{ asset($complaint->battery_connect_image_path) }}" alt="Battery Connect Evidence Capture">
                    @if($complaint->hasReplacementHistory())
                        <div class="barcode-cross-overlay" title="Barcode Redacted (Replaced Battery)"></div>
                    @endif
                </div>
            </div>
        @else
            <div class="capture-container" style="padding: 40px 10px; color: #666666; font-size: 13px;">
                [ Battery Connect Evidence Screenshot Not Attached ]
            </div>
        @endif

    </div>

    <script>
        // Auto-trigger print window if opened directly via save_and_print
        window.addEventListener('load', function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autoprint') === '1') {
                window.print();
            }
        });
    </script>
</body>

</html>