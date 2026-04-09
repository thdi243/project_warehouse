<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Draft Outbound - {{ $outbound->no_reservasi }}</title>
    <!-- Include Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Include BoxIcons if needed for icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px;
            color: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 400px;
        }

        .btn {
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            font-family: inherit;
            flex: 1;
        }

        .btn-print {
            background: #2563eb;
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-back {
            background: #fff;
            color: #475569;
            border: 1px solid #cbd5e1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .btn-back:hover {
            background: #f8fafc;
        }

        .ticket {
            background: #fff;
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            position: relative;
        }

        .ticket-header {
            background: #1e293b;
            color: #fff;
            padding: 24px 20px;
            text-align: center;
            border-bottom: 4px solid #3b82f6;
        }

        .ticket-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .ticket-header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .ticket-body {
            padding: 24px 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 2px dashed #e2e8f0;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 16px 0;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #3b82f6;
            font-size: 18px;
        }

        .item-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .item-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .item-name {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.4;
            flex: 1;
            padding-right: 12px;
        }

        .item-qty {
            background: #eff6ff;
            color: #2563eb;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            white-space: nowrap;
            border: 1px solid #bfdbfe;
        }

        .item-meta {
            display: flex;
            gap: 12px;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .item-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .item-location {
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .loc-details {
            font-size: 12px;
            color: #334155;
            font-weight: 500;
        }

        .loc-bin {
            background: #10b981;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .ticket-footer {
            text-align: center;
            padding-top: 20px;
            margin-top: 24px;
            border-top: 2px dashed #e2e8f0;
        }

        .barcode {
            margin: 0 auto 12px;
            width: 80%;
            height: 40px;
            background: repeating-linear-gradient(90deg,
                    #0f172a,
                    #0f172a 2px,
                    transparent 2px,
                    transparent 4px,
                    #0f172a 4px,
                    #0f172a 5px,
                    transparent 5px,
                    transparent 8px);
        }

        .footer-text {
            font-size: 11px;
            color: #94a3b8;
            margin: 0;
        }

        /* --- PRINT STYLES --- */
        @media print {
            body {
                background: none;
                padding: 0;
            }

            .actions {
                display: none;
            }

            .ticket {
                max-width: 100%;
                box-shadow: none;
                border-radius: 0;
            }

            .ticket-header {
                background: transparent;
                color: #000;
                border-bottom: 2px dashed #000;
                padding: 10px 0;
            }

            .ticket-header h1 {
                font-size: 18px;
            }

            .ticket-header p {
                color: #000;
            }

            .ticket-body {
                padding: 10px 0;
            }

            .info-grid {
                border-bottom: 1px dashed #000;
                margin-bottom: 15px;
                padding-bottom: 15px;
            }

            .info-label {
                color: #555;
            }

            .item-card {
                border: none;
                border-bottom: 1px dashed #ccc;
                border-radius: 0;
                padding: 10px 0;
                background: transparent;
            }

            .item-qty {
                background: transparent;
                color: #000;
                border: 1px solid #000;
            }

            .item-location {
                border: 1px solid #000;
                background: transparent;
            }

            .loc-bin {
                background: transparent;
                color: #000;
                border: 1px solid #000;
            }

            .section-title i {
                display: none;
            }

            .ticket-footer {
                border-top: 2px dashed #000;
            }

            .barcode {
                background: repeating-linear-gradient(90deg, #000, #000 2px, transparent 2px, transparent 4px, #000 4px, #000 5px, transparent 5px, transparent 8px);
            }

            @page {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Floating Action Buttons -->
    <div class="actions">
        <button class="btn btn-back" onclick="window.history.back()">
            <i class='bx bx-arrow-back'></i> Kembali
        </button>
        <button class="btn btn-print" onclick="window.print()">
            <i class='bx bx-printer'></i> Print
        </button>
    </div>

    <!-- Main Ticket Card -->
    <div class="ticket">
        <div class="ticket-header">
            <h1>RESERVATION TRANSFER</h1>
            <p>Operator Forklift</p>
        </div>

        <div class="ticket-body">
            <!-- Order Details -->
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">No Reservasi</span>
                    <span class="info-value">{{ $outbound->no_reservasi }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Shift</span>
                    <span class="info-value">Shift {{ $outbound->shift }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Issued Date</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($outbound->issued_date)->format('d M Y, H:i') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Pallet</span>
                    <span class="info-value">{{ $outbound->details->count() }} Pallet ({{ rtrim(rtrim(number_format($outbound->details->sum('qty'), 2, ',', '.'), '0'), ',') }} Qty)</span>
                </div>
            </div>

            <!-- Items Section -->
            <h3 class="section-title">
                <i class='bx bx-list-ul'></i> Daftar Barang
            </h3>

            <div class="item-list">
                @forelse($outbound->details as $index => $detail)
                <div class="item-card">
                    <div class="item-header">
                        <div class="item-name">{{ $index + 1 }}. {{ $detail->barang->nama_barang }}</div>
                        <div class="item-qty">{{ rtrim(rtrim(number_format($detail->qty, 2, ',', '.'), '0'), ',') }} {{ $detail->barang->uom }}</div>
                    </div>

                    <div class="item-meta">
                        <span><i class='bx bx-barcode'></i> MID: {{ $detail->barang->mid }}</span>
                        <span><i class='bx bx-layer'></i> Grp: {{ $detail->group }}</span>
                        <span><i class='bx bx-file'></i> SPB: {{ $detail->no_spb ?? '-' }}</span>
                    </div>
                    <div class="item-meta" style="margin-top: -8px;">
                        <span><i class='bx bx-buildings'></i> Sup: {{ \Illuminate\Support\Str::limit($detail->supplier, 30) }}</span>
                    </div>

                    <div class="item-location">
                        <div class="loc-details">
                            <i class='bx bx-map pin'></i> {{ $detail->bin->location->plant }} &bull; {{ $detail->bin->location->s_loc }} &bull; {{ $detail->bin->location->zona }}
                        </div>
                        <div class="loc-bin">
                            {{ $detail->bin->location->bin }} - ({{ $detail->bin->kolom }}.{{ $detail->bin->level }})
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align:center; padding: 20px; color: #94a3b8; font-style: italic;">
                    Tidak ada barang untuk dipindahkan.
                </div>
                @endforelse
            </div>

            <!-- Ticket Footer -->
            <div class="ticket-footer">
                <p class="footer-text">Printed at {{ now()->format('d M Y, H:i') }}</p>
                <p class="footer-text" style="margin-top: 4px;">-- Serahkan ke Operator Forklift --</p>
            </div>
        </div>
    </div>
</body>

</html>