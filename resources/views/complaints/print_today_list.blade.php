<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $category === 'TW' ? '2W' : '4W' }} Inward List ({{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}) - Dwarkadhish Enterprise</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #525659;
            color: #000000;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            min-height: 100vh;
        }

        /* Screen Top Action Bar */
        .no-print-bar {
            width: 297mm;
            max-width: 100%;
            background-color: #ffffff;
            padding: 10px 18px;
            margin-bottom: 15px;
            border-radius: 6px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-print {
            background-color: #047857;
            color: #ffffff;
            border: none;
            padding: 8px 20px;
            font-size: 13px;
            font-weight: 800;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover {
            background-color: #065f46;
        }

        .btn-switch {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            padding: 7px 14px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-back {
            background-color: #64748b;
            color: #ffffff;
            border: none;
            padding: 7px 14px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 5px;
            text-decoration: none;
        }

        /* Printable Document Container (A4 Landscape) */
        .report-page {
            width: 297mm;
            min-height: 210mm;
            background-color: #ffffff;
            padding: 8mm 10mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            box-sizing: border-box;
            position: relative;
            margin-bottom: 20px;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 2px solid #000000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .company-title {
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -0.01em;
            color: #000000;
        }

        .report-title {
            font-size: 13px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .meta-info {
            font-size: 10px;
            font-weight: 700;
            text-align: right;
            line-height: 1.3;
        }

        /* Grid Table matching physical paper ledger */
        .inward-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .inward-table th,
        .inward-table td {
            border: 1px solid #000000;
            padding: 4px 5px;
            vertical-align: middle;
            line-height: 1.15;
            text-align: center;
        }

        .inward-table th {
            background-color: #e2e8f0;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 8.5px;
            letter-spacing: 0.02em;
            text-align: center;
        }

        .mono {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
        }

        .text-center {
            text-align: center;
        }

        /* Print Styling Overrides & Page Breaking */
        @media print {
            @page {
                size: A4 landscape;
                margin: 5mm;
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
                margin-bottom: 0 !important;
                page-break-after: always;
                break-after: page;
            }

            .report-page:last-child {
                page-break-after: avoid;
                break-after: avoid;
            }

            .inward-table th {
                background-color: #cbd5e1 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    @php
        $sections = [];
        if ($category === 'FW') {
            $warrantyOrRc = $complaints->filter(function($c) {
                $reg = strtoupper(trim((string)($c->online_reg_type ?? '')));
                return in_array($reg, ['WARRANTY CARD', 'RC BOOK']);
            });
            $notDownloadedOrBlank = $complaints->filter(function($c) {
                $reg = strtoupper(trim((string)($c->online_reg_type ?? '')));
                return !in_array($reg, ['WARRANTY CARD', 'RC BOOK']);
            });

            $sections = [
                [
                    'title' => '4W DAILY INWARD REGISTER (SHEET 1: WARRANTY CARD / RC BOOK)',
                    'badge' => 'SHEET 1 - WARRANTY CARD / RC BOOK',
                    'items' => $warrantyOrRc,
                    'empty_msg' => 'No 4W entries with Warranty Card or RC Book registered for today.',
                ],
                [
                    'title' => '4W DAILY INWARD REGISTER (SHEET 2: NOT DOWNLOADED / PENDING)',
                    'badge' => 'SHEET 2 - NOT DOWNLOADED / PENDING',
                    'items' => $notDownloadedOrBlank,
                    'empty_msg' => 'No 4W entries with Not Downloaded or Pending status registered for today.',
                ],
            ];
        } else {
            $sections = [
                [
                    'title' => '2W DAILY INWARD COMPLAINT REGISTER',
                    'badge' => 'ALL 2W ENTRIES',
                    'items' => $complaints,
                    'empty_msg' => 'No 2W inward complaint records registered for today.',
                ],
            ];
        }
    @endphp

    <!-- Screen Action Header -->
    <div class="no-print-bar">
        <div style="font-weight: 700; font-size: 14px; color: #0f172a;">
            📜 {{ $category === 'TW' ? '2W (Two-Wheeler)' : '4W (Four-Wheeler)' }} Inward Daily Report ({{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}) - {{ count($complaints) }} Records
            @if($category === 'FW')
                <span style="font-size: 0.8rem; color: #6b21a8; font-weight: 800; margin-left: 8px;">(Printed in 2 Sheets: Sheet 1 Warranty Card / RC Book & Sheet 2 Not Downloaded / Pending)</span>
            @endif
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            @if($category === 'TW')
                <a href="{{ route('complaints.print_today_list', ['category' => 'FW', 'date' => $today]) }}" class="btn-switch">🚗 Switch to 4W List</a>
            @else
                <a href="{{ route('complaints.print_today_list', ['category' => 'TW', 'date' => $today]) }}" class="btn-switch">🏍️ Switch to 2W List</a>
            @endif
            <a href="{{ route('complaints.pending_cases') }}" class="btn-back">← Back to Pending Cases</a>
            <button type="button" class="btn-print" onclick="window.print()">
                🖨️ PRINT {{ $category === 'TW' ? '2W' : '4W' }} INWARD LIST ({{ count($complaints) }})
            </button>
        </div>
    </div>

    @foreach($sections as $sec)
        <!-- Printable Report Container -->
        <div class="report-page">

            <!-- HEADER SECTION -->
            <div class="report-header">
                <div>
                    <div class="company-title">DWARKADHISH AUTOMOBILES</div>
                    <div class="report-title">{{ $sec['title'] }}</div>
                </div>
                <div class="meta-info">
                    <div><strong>DATE:</strong> {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}</div>
                    <div><strong>TOTAL ENTRIES:</strong> {{ count($sec['items']) }} RECORDS</div>
                </div>
            </div>

            <!-- INWARD LEDGER TABLE -->
            <table class="inward-table">
                <thead>
                    <tr>
                        <th style="{{ $category === 'TW' ? 'width: 125px; min-width: 115px;' : 'width: 60px;' }}">{{ $category === 'TW' ? '2W' : '4W' }} COMPLAIN NO</th>
                        <th style="width: 70px;">{{ $category === 'TW' ? '2W' : '4W' }} COMPLAIN DATE</th>
                        <th style="width: 75px;">CASE NO</th>
                        <th style="width: 65px;">DATE OF SALE</th>
                        <th style="width: 65px;">PRODUCT</th>
                        <th style="width: 45px;">PROD. SEG.</th>
                        <th style="width: 105px;">OLD BATTERY SERIAL NO.</th>
                        <th style="width: 60px;">DIGITAL WARRANTY</th>
                        @if($category !== 'TW')
                            <th style="width: 75px;">DIGITAL WARRANTY 2</th>
                            <th style="width: 55px;">DE CASH MEMO</th>
                        @endif
                        <th style="width: 65px;">SERVICE BATT. GIVEN</th>
                        <th style="width: 75px;">MOBILE NO</th>
                        <th>CUSTOMER/DEALER NAME</th>
                        <th style="width: 85px;">LOCATION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sec['items'] as $complaint)
                        <tr>
                            <td class="mono text-center" style="font-weight: 800; font-size: 10px; {{ $category === 'TW' ? 'width: 125px; min-width: 115px; padding: 4px 8px;' : '' }}">{{ $complaint->complaint_number }}</td>
                            <td class="text-center">{{ $complaint->complaint_date ? $complaint->complaint_date->format('d/m/Y') : '' }}</td>
                            <td class="mono text-center">{{ ($complaint->case_number && strtoupper(trim($complaint->case_number)) !== 'NO ONLINE' && strtoupper(trim($complaint->case_number)) !== 'NO_ONLINE') ? $complaint->case_number : '' }}</td>
                            <td class="text-center">{{ $complaint->purchase_date ? $complaint->purchase_date->format('d/m/Y') : '' }}</td>
                            <td class="text-center" style="font-weight: 700;">{{ $complaint->product_model ?: '' }}</td>
                            <td class="text-center" style="font-weight: 700;">{{ $complaint->battery_type === 'TW' ? 'TW' : 'FW' }}</td>
                            <td class="mono text-center" style="font-size: 9.5px; font-weight: 700;">{{ $complaint->battery_serial_number }}</td>
                            <td class="text-center" style="font-weight: 700;">{{ $complaint->is_digital_warranty ? 'YES' : '' }}</td>
                            @if($category !== 'TW')
                                <td class="text-center" style="font-size: 8.5px; font-weight: 700;">{{ $complaint->online_reg_type ?: '' }}</td>
                                <td class="text-center"></td>
                            @endif
                            <td class="text-center" style="font-weight: 700;">
                                @if($complaint->is_service_battery_given)
                                    {{ $complaint->service_battery_number ?: 'YES' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="mono text-center">{{ $complaint->customer_mobile ?: '' }}</td>
                            <td class="text-center" style="font-weight: 700; text-transform: uppercase;">{{ $complaint->customer_name ?: '' }}</td>
                            <td class="text-center" style="text-transform: uppercase;">{{ $complaint->location ?: '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $category === 'TW' ? 12 : 14 }}" style="text-align: center; padding: 20px; font-size: 11px; font-weight: 700; color: #64748b;">
                                {{ $sec['empty_msg'] }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- FOOTER SUMMARY -->
            <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center; font-size: 9px; font-weight: 700; border-top: 1px solid #000000; padding-top: 4px;">
                <div>Dwarkadhish Enterprise - Daily {{ $category === 'TW' ? '2W' : '4W' }} Inward Ledger Report ({{ $sec['badge'] }})</div>
                <div>Printed on: {{ date('d/m/Y H:i') }}</div>
            </div>

        </div>
    @endforeach

    <script>
        // Auto-trigger print window on load
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 300);
        });
    </script>
</body>

</html>
