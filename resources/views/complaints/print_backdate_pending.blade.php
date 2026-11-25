<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back-Date Pending Cases Report{{ !empty($category) ? ' - ' . ($category === 'TW' ? 'TW (2W)' : 'FW (4W)') : '' }} ({{ count($complaints) }} Records) - Dwarkadhish Automobiles</title>

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
            width: 297mm;
            max-width: 100%;
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
            background-color: #dc2626;
            color: #ffffff;
            border: none;
            padding: 9px 22px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover {
            background-color: #b91c1c;
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

        /* Printable Document Container (A4 Landscape) */
        .report-page {
            width: 297mm;
            min-height: 210mm;
            background-color: #ffffff;
            padding: 12mm 15mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            box-sizing: border-box;
            position: relative;
        }

        .report-header {
            border-bottom: 2px solid #000000;
            padding-bottom: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .company-title {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.02em;
            color: #000000;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 14px;
            font-weight: 800;
            color: #dc2626;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 2px;
        }

        .meta-info {
            font-size: 11px;
            font-weight: 700;
            text-align: right;
            line-height: 1.4;
        }

        /* Report Table */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #000000;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .report-table th {
            background-color: #f1f5f9;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.03em;
            text-align: left;
        }

        .days-badge {
            background-color: #fee2e2;
            color: #991b1b;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            display: inline-block;
            white-space: nowrap;
        }

        .mono {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
        }

        .notes-box {
            min-width: 110px;
            height: 24px;
        }

        /* Print Styling Overrides */
        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }

            body {
                background: none !important;
                padding: 0 !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .report-page {
                width: 100% !important;
                box-shadow: none !important;
                padding: 0 !important;
            }

            .report-table th {
                background-color: #e2e8f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .days-badge {
                border: 1px solid #991b1b !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <!-- Screen Action Header -->
    <div class="no-print-bar">
        <div style="font-weight: 700; font-size: 15px; color: #1e293b;">
            📋 Back-Date Pending Cases Report{{ !empty($category) ? ' - ' . ($category === 'TW' ? 'TW (2W)' : 'FW (4W)') : '' }} ({{ count($complaints) }} Open Pending Records)
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('complaints.pending_cases') }}" class="btn-back">← Back to Pending Cases</a>
            <a href="{{ route('complaints.export_backdate_pending', !empty($category) ? ['category' => $category] : []) }}" class="btn-print" style="background-color: #15803d; text-decoration: none;">
                📥 EXPORT TO EXCEL
            </a>
            <button type="button" class="btn-print" onclick="window.print()">
                🖨️ PRINT REPORT ({{ count($complaints) }} RECORDS)
            </button>
        </div>
    </div>

    <!-- Printable Report Container -->
    <div class="report-page">

        <!-- HEADER SECTION -->
        <div class="report-header">
            <div>
                <div class="company-title">DWARKADHISH AUTOMOBILES</div>
                <div class="report-title">⚠️ BACK-DATE OPEN PENDING CASES REPORT{{ !empty($category) ? ' - ' . ($category === 'TW' ? 'TW (2W)' : 'FW (4W)') : '' }} (CLOSURE / RETURN PENDING)</div>
            </div>
            <div class="meta-info">
                <div><strong>PRINTED ON:</strong> {{ date('d/m/Y H:i') }}</div>
                <div><strong>TOTAL BACK-DATE CASES:</strong> {{ count($complaints) }} RECORDS</div>
            </div>
        </div>

        <!-- REPORT DATA TABLE -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 30px; text-align: center;">#</th>
                    <th style="width: 85px;">Inward Date</th>
                    <th style="width: 75px; text-align: center;">Age</th>
                    <th style="width: 75px;">Co. No.</th>
                    <th style="width: 85px;">Case No.</th>
                    <th>Customer Details (C/O, Mobile, Loc)</th>
                    <th>Vehicle & Segment</th>
                    <th>Product Model</th>
                    <th>Battery Serial No.</th>
                    <th style="width: 90px;">Standby Batt.</th>
                    <th style="width: 120px;">Inspection / Action Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($complaints as $index => $complaint)
                    @php
                        $inwardDate = $complaint->complaint_date;
                        $daysOld = $inwardDate ? (int) $inwardDate->diffInDays(now()) : 0;
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: 700;">{{ $index + 1 }}</td>
                        <td style="font-weight: 700;">
                            {{ $inwardDate ? $inwardDate->format('d/m/Y') : '-' }}
                        </td>
                        <td style="text-align: center;">
                            <span class="days-badge">{{ $daysOld }} {{ $daysOld === 1 ? 'Day' : 'Days' }}</span>
                        </td>
                        <td class="mono">{{ $complaint->complaint_number }}</td>
                        <td class="mono">{{ $complaint->case_number ?: '-' }}</td>
                        <td>
                            <strong>{{ $complaint->customer_name ?: 'N/A' }}</strong><br>
                            @if($complaint->customer_mobile) Mob: {{ $complaint->customer_mobile }} | @endif
                            @if($complaint->location) Loc: {{ $complaint->location }} @endif
                        </td>
                        <td>
                            <span style="font-weight: 800; color: {{ $complaint->battery_type === 'TW' ? '#b45309' : '#1d4ed8' }};">
                                [{{ $complaint->battery_type === 'TW' ? '2W' : '4W' }}]
                            </span>
                            {{ $complaint->vehicle_name ?: '-' }}
                            @if($complaint->vehicle_number) ({{ $complaint->vehicle_number }}) @endif
                        </td>
                        <td><strong>{{ $complaint->product_model ?: '-' }}</strong></td>
                        <td class="mono" style="font-size: 11.5px;">{{ $complaint->battery_serial_number }}</td>
                        <td>
                            @if($complaint->is_service_battery_given)
                                <span style="font-weight: 700; color: #047857;">YES ({{ $complaint->service_battery_number ?: 'N/A' }})</span>
                            @else
                                <span style="color: #64748b;">No</span>
                            @endif
                        </td>
                        <td>
                            <div class="notes-box"></div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- FOOTER SUMMARY -->
        <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center; font-size: 10px; font-weight: 700; border-top: 1px solid #000000; padding-top: 6px;">
            <div>Dwarkadhish Automobiles - Internal Back-Date Inspection Ledger</div>
            <div>Page 1 of 1</div>
        </div>

    </div>

    <script>
        // Auto-trigger print window on load
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 400);
        });
    </script>
</body>

</html>
