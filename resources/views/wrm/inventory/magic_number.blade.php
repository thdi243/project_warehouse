<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Magic Number - {{ $outbound->no_spb }}</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f5f5f5;
                padding: 20px;
            }

            .container {
                max-width: 900px;
                margin: 0 auto;
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 3px solid #2c3e50;
                padding-bottom: 20px;
            }

            .header h1 {
                color: #2c3e50;
                font-size: 28px;
                margin-bottom: 10px;
            }

            .header p {
                color: #666;
                font-size: 14px;
            }

            .info-box {
                display: flex;
                gap: 20px;
                margin-bottom: 30px;
                padding: 15px;
                background-color: #ecf0f1;
                border-radius: 5px;
            }

            .info-item {
                flex: 1;
            }

            .info-label {
                font-weight: 600;
                color: #2c3e50;
                font-size: 12px;
                text-transform: uppercase;
                margin-bottom: 5px;
            }

            .info-value {
                font-size: 16px;
                color: #34495e;
                font-weight: 500;
            }

            .table-section {
                margin-bottom: 30px;
            }

            .section-title {
                font-size: 16px;
                font-weight: 700;
                color: #2c3e50;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 2px solid #3498db;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            thead {
                background-color: #34495e;
                color: white;
            }

            th {
                padding: 12px;
                text-align: left;
                font-weight: 600;
                font-size: 13px;
                border: 1px solid #bdc3c7;
            }

            td {
                padding: 12px;
                border: 1px solid #bdc3c7;
                font-size: 13px;
            }

            tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }

            tbody tr:hover {
                background-color: #ecf0f1;
            }

            .location-info {
                background-color: #d5f4e6;
                font-weight: 500;
                color: #27ae60;
            }

            .qty-col {
                text-align: center;
                font-weight: 600;
            }

            .group-badge {
                display: inline-block;
                background-color: #3498db;
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 600;
            }

            .mid-code {
                font-family: 'Courier New', monospace;
                font-weight: 600;
                color: #c0392b;
                font-size: 14px;
            }

            .footer {
                margin-top: 40px;
                text-align: center;
                color: #7f8c8d;
                font-size: 12px;
                border-top: 1px solid #bdc3c7;
                padding-top: 20px;
            }

            .print-controls {
                text-align: center;
                margin-bottom: 20px;
                display: flex;
                gap: 10px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn {
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .btn-print {
                background-color: #27ae60;
                color: white;
            }

            .btn-print:hover {
                background-color: #229954;
            }

            .btn-back {
                background-color: #95a5a6;
                color: white;
            }

            .btn-back:hover {
                background-color: #7f8c8d;
            }

            .summary-box {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin-bottom: 30px;
            }

            .summary-item {
                background-color: #ecf0f1;
                padding: 15px;
                border-radius: 5px;
                text-align: center;
            }

            .summary-label {
                font-size: 12px;
                color: #666;
                text-transform: uppercase;
                margin-bottom: 8px;
                font-weight: 600;
            }

            .summary-value {
                font-size: 24px;
                color: #2c3e50;
                font-weight: 700;
            }

            @media print {
                body {
                    background-color: white;
                    padding: 0;
                }

                .container {
                    box-shadow: none;
                    padding: 0;
                    max-width: 100%;
                }

                .print-controls {
                    display: none;
                }

                .footer {
                    border-top: none;
                    margin-top: 20px;
                }

                table {
                    page-break-inside: avoid;
                }

                tbody tr {
                    page-break-inside: avoid;
                }
            }

            @media (max-width: 768px) {
                .container {
                    padding: 15px;
                }

                .header h1 {
                    font-size: 22px;
                }

                .info-box {
                    flex-direction: column;
                    gap: 10px;
                }

                th,
                td {
                    padding: 8px;
                    font-size: 12px;
                }

                table {
                    font-size: 11px;
                }
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="print-controls">
                <button class="btn btn-print" onclick="window.print()">
                    <i class="mdi mdi-printer"></i> Print Magic Number
                </button>
                <button class="btn btn-back" onclick="window.history.back()">
                    <i class="mdi mdi-chevron-left"></i> Kembali
                </button>
            </div>

            <div class="header">
                <h1>📦 MAGIC NUMBER - OPERATOR FORKLIFT</h1>
                <p>Data Transfer Barang - No SPB: <strong>{{ $outbound->no_spb }}</strong></p>
            </div>

            <div class="info-box">
                <div class="info-item">
                    <div class="info-label">No SPB</div>
                    <div class="info-value">{{ $outbound->no_spb }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Supplier</div>
                    <div class="info-value">{{ $outbound->supplier }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Incoming Date</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($outbound->incoming_date)->format('d/m/Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Issued Date</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($outbound->issued_date)->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-item">
                    <div class="summary-label">Total Item</div>
                    <div class="summary-value">{{ $outbound->details->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Qty</div>
                    <div class="summary-value">{{ $outbound->details->sum('qty') }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Pallet</div>
                    <div class="summary-value">{{ $outbound->details->unique('pallet_id')->count() }}</div>
                </div>
            </div>

            <div class="table-section">
                <h2 class="section-title">📋 DAFTAR BARANG UNTUK DIPINDAHKAN</h2>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 12%;">MID</th>
                            <th style="width: 30%;">Nama Barang</th>
                            <th style="width: 10%;">UOM</th>
                            <th style="width: 8%;">Qty</th>
                            <th style="width: 8%;">Group</th>
                            <th style="width: 27%;">Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outbound->details as $index => $detail)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <span class="mid-code">{{ $detail->barang->mid }}</span>
                                </td>
                                <td>{{ $detail->barang->nama_barang }}</td>
                                <td style="text-align: center;">{{ $detail->barang->uom }}</td>
                                <td class="qty-col">{{ $detail->qty }}</td>
                                <td>
                                    <span class="group-badge">{{ $detail->group }}</span>
                                </td>
                                <td class="location-info">
                                    <strong>🏭 {{ $detail->bin->location->plant }}</strong><br>
                                    📍 {{ $detail->bin->location->gudang }} - {{ $detail->bin->location->zona }}<br>
                                    📦 Bin: <strong>{{ $detail->bin->bin }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: #999;">Tidak ada data detail</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="footer">
                <p>
                    Generated: {{ now()->format('d/m/Y H:i:s') }} | Petunjuk: Print dokumen ini dan berikan ke operator
                    forklift
                    untuk proses transfer barang sesuai lokasi yang tertera.
                </p>
            </div>
        </div>

        <script>
            // Jika ingin auto print saat halaman terbuka, uncomment baris berikut:
            // window.print();
        </script>
    </body>

</html>
