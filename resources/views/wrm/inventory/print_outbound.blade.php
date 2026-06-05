<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Print Reservation Transfer - {{ $outbound->no_reservasi }}</title>
        <!-- Include Google Fonts for clean typography -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

        <style>
            body {
                font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f8fafc;
                margin: 0;
                padding: 30px;
                color: #1e293b;
            }

            .container {
                max-width: 1000px;
                margin: 0 auto;
                background-color: #ffffff;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }

            /* Floating / Top Actions */
            .actions {
                display: flex;
                gap: 12px;
                margin-bottom: 24px;
                max-width: 1000px;
                margin: 0 auto 20px auto;
            }

            .btn {
                padding: 10px 20px;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                font-size: 12px;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s ease;
                font-family: inherit;
            }

            .btn-print {
                background-color: #0284c7;
                color: #ffffff;
            }

            .btn-print:hover {
                background-color: #0369a1;
            }

            .btn-back {
                background-color: #ffffff;
                color: #475569;
                border: 1px solid #cbd5e1;
            }

            .btn-back:hover {
                background-color: #f1f5f9;
            }

            /* Header styling */
            .doc-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                border-bottom: 2px solid #0f172a;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }

            .doc-title h1 {
                margin: 0;
                font-size: 18px;
                font-weight: 700;
                color: #0f172a;
                letter-spacing: 0.5px;
            }

            .doc-title p {
                margin: 4px 0 0 0;
                font-size: 12px;
                color: #64748b;
                font-weight: 500;
            }

            .metadata-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px 40px;
                font-size: 12px;
            }

            .meta-item {
                display: flex;
                gap: 10px;
            }

            .meta-label {
                font-weight: 600;
                color: #475569;
                width: 120px;
                flex-shrink: 0;
            }

            .meta-value {
                color: #0f172a;
                font-weight: 700;
            }

            /* Table design */
            .table-wrapper {
                margin-bottom: 40px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10px;
                text-align: left;
            }

            th,
            td {
                border: 1px solid #cbd5e1;
                padding: 10px 12px;
                line-height: 1;
            }

            th {
                background-color: #f8fafc;
                color: #0f172a;
                font-weight: 700;
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.5px;
            }

            tr:nth-child(even) {
                background-color: #f8fafc;
            }

            .text-center {
                text-align: center;
            }

            .text-end {
                text-align: right;
            }

            .fw-bold {
                font-weight: 700;
            }

            /* Signatures section */
            .signature-section {
                display: flex;
                justify-content: space-between;
                margin-top: 50px;
                padding-top: 20px;
            }

            .sig-box {
                text-align: center;
                width: 200px;
            }

            .sig-title {
                font-size: 13px;
                font-weight: 600;
                color: #475569;
                margin-bottom: 70px;
            }

            .sig-name {
                font-size: 13px;
                font-weight: 700;
                color: #0f172a;
                border-bottom: 1px solid #000000;
                padding-bottom: 2px;
                display: inline-block;
                width: 100%;
            }

            .sig-role {
                font-size: 11px;
                color: #64748b;
                margin-top: 2px;
            }

            .doc-footer {
                margin-top: 50px;
                border-top: 1px dashed #cbd5e1;
                padding-top: 15px;
                display: flex;
                justify-content: space-between;
                font-size: 11px;
                color: #64748b;
            }

            /* Print Media Query */
            @media print {
                body {
                    background-color: #ffffff;
                    padding: 0;
                    margin: 0;
                    color: #000000;
                    font-size: 9.5pt;
                }

                .container {
                    box-shadow: none;
                    padding: 0;
                    max-width: 100%;
                    width: 100%;
                }

                .actions {
                    display: none;
                }

                /* Compact header for print */
                .doc-header {
                    border-bottom: 2px solid #000000;
                    padding-bottom: 10px;
                    margin-bottom: 15px;
                }

                .doc-title h1 {
                    font-size: 15pt;
                }

                .doc-title p {
                    font-size: 8.5pt;
                }

                .metadata-grid {
                    font-size: 8.5pt;
                    gap: 5px 20px;
                }

                /* Compact table style for print */
                .table-wrapper {
                    margin-bottom: 25px;
                }

                table {
                    font-size: 8pt;
                    width: 100%;
                }

                th,
                td {
                    border: 1px solid #000000 !important;
                    padding: 6px 8px !important;
                    line-height: 1.2 !important;
                }

                th {
                    background-color: #e2e8f0 !important;
                    color: #000000 !important;
                    font-size: 8.5pt;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                tr:nth-child(even) {
                    background-color: #ffffff !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                /* Compact signature boxes */
                .signature-section {
                    margin-top: 30px;
                }

                .sig-box {
                    width: 160px;
                }

                .sig-title {
                    font-size: 8.5pt;
                    margin-bottom: 45px;
                }

                .sig-name {
                    font-size: 8.5pt;
                    border-bottom: 1px solid #000000 !important;
                }

                .sig-role {
                    font-size: 8pt;
                }

                .doc-footer {
                    margin-top: 30px;
                    padding-top: 10px;
                }

                @page {
                    size: A4 portrait;
                    margin: 1cm;
                }
            }
        </style>
    </head>

    <body>
        <!-- Top Action Buttons -->
        <div class="actions">
            <button class="btn btn-back" onclick="window.history.back()">
                <i class='bx bx-arrow-back'></i> Kembali
            </button>
            <button class="btn btn-print" onclick="window.print()">
                <i class='bx bx-printer'></i> Print
            </button>
        </div>

        <!-- Main Print Document Container -->
        <div class="container">
            <!-- Header -->
            <div class="doc-header">
                <div class="doc-title">
                    <h1>RESERVATION TRANSFER</h1>
                    <p>Dokumen Pemindahan Barang (Operator Forklift)</p>
                </div>
                <div class="metadata-grid">
                    <div class="meta-item">
                        <span class="meta-label">No Reservasi</span>
                        <span class="meta-value">: {{ $outbound->no_reservasi }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Shift</span>
                        <span class="meta-value">: Shift {{ $outbound->shift }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Tanggal Reservasi</span>
                        <span class="meta-value">:
                            {{ \Carbon\Carbon::parse($outbound->issued_date)->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Total Pallet</span>
                        <span class="meta-value">: {{ $outbound->details->count() }} Pallet
                            ({{ rtrim(rtrim(number_format($outbound->details->sum('qty'), 2, ',', '.'), '0'), ',') }}
                            KG)</span>
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Pallet ID</th>
                            <th>MID</th>
                            <th>Nama Barang</th>
                            <th class="text-end">Qty (KG)</th>
                            <th>No SPB</th>
                            <th>Supplier</th>
                            <th>Lokasi (Plant - SLoc - Zona - Bin - Kolom.Lvl)</th>
                            <th class="text-center">Group</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outbound->details as $index => $detail)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="fw-bold">{{ $detail->pallet_id }}</td>
                                <td>{{ $detail->barang->mid }}</td>
                                <td>{{ $detail->barang->nama_barang }}</td>
                                <td class="text-end fw-bold">
                                    {{ rtrim(rtrim(number_format($detail->qty, 2, ',', '.'), '0'), ',') }}</td>
                                <td>{{ $detail->no_spb ?? '-' }}</td>
                                <td>{{ $detail->supplier }}</td>
                                <td>
                                    @if ($detail->bin && $detail->bin->location)
                                        {{ $detail->bin->location->plant }} - {{ $detail->bin->location->s_loc }} -
                                        {{ $detail->bin->location->zona }} - <b>{{ $detail->bin->location->bin }}</b>
                                        ({{ $detail->bin->kolom }}.{{ $detail->bin->level }})
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">{{ $detail->group }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center"
                                    style="padding: 30px; color: #64748b; font-style: italic;">
                                    Tidak ada data pemindahan barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Signatures Section -->
            <div class="signature-section">
                <div class="sig-box">
                    <div class="sig-title">Dibuat Oleh,</div>
                    <div class="sig-name">&nbsp;</div>
                    <div class="sig-role">Stock Control</div>
                </div>
                <div class="sig-box">
                    <div class="sig-title">Diserahkan Oleh,</div>
                    <div class="sig-name">&nbsp;</div>
                    <div class="sig-role">Operator Forklift</div>
                </div>
                <div class="sig-box">
                    <div class="sig-title">Diterima Oleh,</div>
                    <div class="sig-name">&nbsp;</div>
                    <div class="sig-role">Foreman / Supervisor</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="doc-footer">
                <span>Printed at: {{ now()->format('d M Y, H:i:s') }}</span>
                <span>-- Project Warehouse --</span>
            </div>
        </div>
    </body>

</html>
