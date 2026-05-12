<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Loading Order</title>

        <style>
            @page {
                margin: 50px 30px 50px 30px;
            }

            body {
                font-family: 'Calibri', Arial, sans-serif;
                font-size: 11px;
                margin: 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                line-height: 1;
                page-break-inside: auto;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 4px;
                word-wrap: break-word;
            }

            th {
                text-align: center;
                font-weight: bold;
                background: #f4f4f4;
            }

            thead {
                display: table-header-group !important;
            }

            tbody {
                display: table-row-group;
            }

            tfoot {
                display: table-footer-group;
                page-break-inside: avoid;
            }

            tr {
                page-break-inside: avoid;
            }

            tbody tr:first-child td {
                border-top: none !important;
            }

            .header {
                line-height: 1;
                height: 20px;
            }

            .no-border {
                border: none !important;
            }

            .text-center {
                text-align: center;
            }

            .text-right {
                text-align: right;
            }

            .text-left {
                text-align: left;
            }

            .border-bottom-only {
                border: none !important;
                border-bottom: 1px solid #000 !important;
            }

            .border-top-th th {
                height: 25px !important;
                border-top: 1px solid black;
                background: white !important;
            }

            .no-border-row td {
                border-top: none !important;
                border-bottom: none !important;
            }

            .approver-name-cell {
                gap: 5px;
                text-align: center;
                border-top: none !important;
                border-bottom: none !important;
                text-transform: capitalize;
            }

            .approver-ttd-cell {
                height: 100px;
                border-bottom: none !important;
                text-align: center;
            }

            /* FIX UNTUK BORDER TABEL DATA SAAT PAGE BREAK */
            .data-table {
                width: 100%;
                line-height: 1.5;
                border-left: 1px solid #000;
                border-right: 1px solid #000;
                border-bottom: 1px solid #000;
            }

            /* Hilangkan border antar row di dalam tbody */
            .data-table tbody tr td {
                border-top: none !important;
                border-bottom: none !important;
                border-left: 1px solid #000;
                border-right: 1px solid #000;
            }

            /* Untuk print */
            @media print {
                .data-table tbody tr {
                    page-break-inside: avoid;
                }
            }
        </style>
    </head>

    <body>
        <!-- HEADER -->
        <table class="header" cellspacing="0" cellpadding="4"
            style="white-space: nowrap; border: 1px solid #000; border-bottom: none !important;">
            <tr>
                <td rowspan="4" class="text-center" style="width: 25%;">
                    <img src="{{ public_path('assets/images/logo/logo.png') }}" width="120">
                </td>
                <td class="text-center" style="font-size: 14px; font-weight: bold; width: 35%;">
                    PT. BUMI ALAM SEGAR
                </td>
                <td class="text-left" style="width: 10%;">No Dok</td>
                <td class="text-left" style="width: 30%;">: {{ $order->no_dokumen }}</td>
            </tr>

            <tr>
                <td class="text-center" style="font-size: 12px; font-weight: bold;">
                    WAREHOUSE
                </td>
                <td class="text-left">Rev</td>
                <td class="text-left">: 00</td>
            </tr>

            <tr>
                <td class="text-center" rowspan="2" style="font-size: 12px; font-weight: bold;">
                    FORM PERINTAH MUAT BARANG
                </td>
                <td class="text-left">Tanggal</td>
                <td class="text-left">: {{ \Carbon\Carbon::parse($order->tanggal)->format('d/m/Y') }}</td>
            </tr>

            <tr>
                <td class="text-left">Halaman</td>
                <td class="text-left" id="page-info">:</td>
            </tr>
        </table>

        {{-- INFO --}}
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:10px;">
            <tr>
                <td width="50%" valign="top" class="no-border">
                    <table cellspacing="0" cellpadding="4" width="100%" style="line-height: 1.5;">
                        <tr>
                            <td width="140" class="text-muted no-border">Wavepick SMU</td>
                            <td class="fw-bold no-border">: {{ $order->wavepick_smu }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted no-border">Shipment SMU</td>
                            <td class="fw-bold no-border">: {{ $order->shipment_smu }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted no-border">Wavepick BAS</td>
                            <td class="fw-bold no-border">: {{ $order->wavepick_bas }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted no-border">Shipment BAS</td>
                            <td class="fw-bold no-border">: {{ $order->shipment_bas }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted no-border">Forklift Driver</td>
                            <td class="fw-bold no-border">: {{ $order->forkliftDriver->username }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted no-border">Tujuan</td>
                            <td class="fw-bold no-border">: {{ $order->destinasi->destinasi }}</td>
                        </tr>
                    </table>
                </td>

                <!-- KOLOM KANAN -->
                <td width="50%" valign="top" class="no-border">
                    <table cellspacing="0" cellpadding="4" width="100%" style="line-height: 1.5;">
                        <tr>
                            <td width="160" class="text-muted no-border">Nomor Mobil (Gate)</td>
                            <td class="fw-bold no-border">
                                : {{ $order->no_mobil . ' (' . $order->gate . ')' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted no-border">Nomor Kontainer</td>
                            <td class="fw-bold no-border">: {{ $order->no_kontainer }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted no-border">Nomor Segel BAS</td>
                            <td class="fw-bold no-border">: {{ $order->no_segel_bas }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted no-border">Nomor Segel Vendor</td>
                            <td class="fw-bold no-border">: {{ $order->no_segel_vendor }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted no-border">Jumlah Slipsheet</td>
                            <td class="fw-bold no-border">: {{ $order->jumlah_slipsheet }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted no-border">Jam Muat</td>
                            <td class="fw-bold no-border">: {{ $order->jam_muat }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- DATA --}}
        <table width="100%" cellspacing="0" cellpadding="4" class="data-table" style="margin-top:10px;">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Mid</th>
                    <th>No Batch</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>TO Dummy</th>
                    <th>TO SAP</th>
                    <th>2 PO</th>
                    <th>Cancel TO</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($order->details as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $detail->material->mid_barang ?? '-' }}</td>
                        <td class="text-center">{{ $detail->batch_number ?? '-' }}</td>
                        <td class="text-center">{{ $detail->jenis ?? '-' }}</td>
                        <td class="text-center">{{ $detail->qty ?? '-' }}</td>
                        <td class="text-center">{{ $detail->to_dummy ?? '-' }}</td>
                        <td class="text-center">{{ $detail->to_sap ?? '-' }}</td>
                        <td class="text-center">
                            {{ $detail->double_po ? 'Yes' : '-' }}
                        </td>

                        <td class="text-center">
                            {{ $detail->cancel_to ? 'Yes' : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Table Form --}}
        <table cellspacing="0" cellpadding="4" style="width: 100%; border: none; padding-top: 10px;">

            <tr>
                {{-- Left --}}
                <td style="border: none; text-align: left; font-size: 11px;">
                    <strong>Total Full Pallet:</strong> {{ $totalFullPallet ?? 0 }}
                    &nbsp;&nbsp;&nbsp;
                    <strong>Total Receh:</strong> {{ $totalReceh ?? 0 }}
                </td>

                {{-- Right --}}
                <td style="border: none; text-align: right; font-size: 11px;">
                    FRM/WRM/01/000/001-01
                </td>
            </tr>

        </table>

        {{-- TTD & Summary --}}
        <table class="text-center" cellspacing="0" cellpadding="4"
            style="white-space: nowrap; border: 1px solid #000; margin-top: 10px; width: 100%; border-collapse: collapse;">

            <tr>
                {{-- Checker --}}
                <td class="approver-ttd-cell" style="width: 33.33%;">
                    <img src="{{ $order->checker_signature }}" width="200"
                        alt="TTD {{ $order->checker->username }}">
                </td>

                {{-- Driver --}}
                <td class="approver-ttd-cell" style="width: 33.33%;">
                    <img src="{{ $order->driver_signature }}" width="200" alt="TTD {{ $order->driver_name }}">
                </td>

                {{-- Summary --}}
                <td rowspan="3"
                    style="vertical-align: top; text-align: left; padding: 10px; width: 33.33%; border-left: 1px solid #000;">

                    <strong>Summary Data</strong>

                    <br><br>

                    <strong>SMU</strong><br>
                    @forelse ($summarySMU as $item)
                        {{ $item['mid'] }} : {{ $item['qty'] }} Box<br>
                    @empty
                        -
                    @endforelse

                    <br>

                    <strong>BAS</strong><br>
                    @forelse ($summaryBAS as $item)
                        {{ $item['mid'] }} : {{ $item['qty'] }} Box<br>
                    @empty
                        -
                    @endforelse
                </td>
            </tr>

            <tr>
                <td class="approver-name-cell">
                    <span style="font-size: 11px;">
                        <strong>{{ $order->checker->username }}</strong>
                    </span>
                    <br>
                    <span style="font-size: 11px;">
                        {{ $order->approved_at }}
                    </span>
                </td>

                <td class="approver-name-cell">
                    <span style="font-size: 11px;">
                        <strong>{{ $order->driver_name }}</strong>
                    </span>
                    <br>
                    <span style="font-size: 11px;">
                        {{ $order->driver_approved_at }}
                    </span>
                </td>
            </tr>

            <tr style="border-top: 1px solid #000; padding: 0;">
                <td style="text-align: center;">
                    <span style="font-size: 11px;">Checker</span>
                </td>

                <td style="text-align: center;">
                    <span style="font-size: 11px;">Driver</span>
                </td>
            </tr>
        </table>
    </body>

</html>
