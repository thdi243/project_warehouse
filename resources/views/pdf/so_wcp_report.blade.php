<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <title>Laporan SO WCP</title>
        <style>
            @page {
                margin: 30px 30px 30px 30px;
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
                text-align: center;
                border-top: none !important;
                border-bottom: none !important;
                text-transform: capitalize;
            }

            .approver-ttd-cell {
                height: 100px;
                border-top: none !important;
                border-bottom: none !important;
                text-align: center;
            }

            /* FIX UNTUK BORDER TABEL DATA SAAT PAGE BREAK */
            .data-table {
                width: 100%;
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
                <td rowspan="4" class="text-center" style="width: 30%;">
                    <img src="{{ public_path('assets/images/logo/logo.png') }}" width="150">
                </td>
                <td class="text-center" style="font-size: 14px; font-weight: bold; width: 30%;">
                    PT. BUMI ALAM SEGAR
                </td>
                <td class="no-border text-left" style="width: 10%;">No Dok</td>
                <td class="no-border text-left" style="width: 30%;">: {{ $so->no_doc }}</td>
            </tr>

            <tr>
                <td class="text-center" style="font-size: 12px; font-weight: bold;">
                    WAREHOUSE
                </td>
                <td class="no-border text-left">Rev</td>
                <td class="no-border text-left">: -</td>
            </tr>

            <tr>
                <td class="text-center" rowspan="2" style="font-size: 12px; font-weight: bold;">
                    STOCK OPNAME
                </td>
                <td class="no-border text-left">Tanggal</td>
                <td class="no-border text-left">: {{ \Carbon\Carbon::parse($tglOpname)->format('d/m/Y') }}</td>
            </tr>

            <tr>
                <td class="no-border text-left border-bottom-only">Hal</td>
                <td class="no-border text-left border-bottom-only" id="page-info">
                    : </td>
            </tr>

            <tr>
                <td colspan="4" style="height: 10px; border: none;"></td>
            </tr>
        </table>

        <!-- TABEL DATA -->
        <table cellspacing="0" cellpadding="4" class="border-top-th text-center data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 13%;">MID</th>
                    <th style="width: 30%;">Nama Barang</th>
                    <th style="width: 5%;">Uom</th>
                    <th style="width: 8%;">SAP</th>
                    <th style="width: 8%;">Fisik</th>
                    <th style="width: 8%;">Selisih</th>
                    <th style="width: 28%;">Keterangan</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($summaries as $dt)
                    <tr class="no-border-row">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $dt->barang->mid }}</td>
                        <td style="text-align: left; padding-left: 5px;">
                            {{ $dt->barang->nama_barang }}
                        </td>
                        <td>{{ $dt->barang->uom }}</td>
                        <td style="padding-right: 5px;">
                            @php
                                $val = $dt->qty_sistem ?? 0;
                                $formatted = number_format($val, 2, ',', '.');
                                $clean = preg_replace('/,00$/', '', $formatted);
                            @endphp
                            {{ $clean }}
                        </td>
                        <td style="padding-right: 5px;">
                            @php
                                $val = $dt->qty_fisik ?? 0;
                                $formatted = number_format($val, 2, ',', '.');
                                $clean = preg_replace('/,00$/', '', $formatted);
                            @endphp
                            {{ $clean }}
                        </td>
                        <td style="padding-right: 5px;">
                            @php
                                $nilai = floatval($dt->selisih ?? 0);
                                $formatted = number_format($nilai, 2, ',', '.');
                                $formatted = rtrim(rtrim($formatted, '0'), ',');
                            @endphp
                            {{ $formatted }}
                        </td>
                        <td style="text-align: left; padding-left: 5px; word-wrap: break-word;">
                            <b>{{ $dt->keterangan }}</b>
                        </td>
                    </tr>
                @endforeach

                {{-- Tambahan baris kosong --}}
                @php
                    $jumlahData = count($summaries);
                    $tambahan = $jumlahData < 10 ? 5 : 3;
                @endphp

                @for ($i = 1; $i <= $tambahan; $i++)
                    <tr class="no-border-row">
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="text-align: left; padding-left: 5px;">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="text-align: left; padding-left: 5px;">&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        {{-- table Note --}}
        <table class="text-left" cellspacing="0" cellpadding="4"
            style="border: 1px solid #000; border-top: 0; width: 100%; border-collapse: collapse;">
            <tr>
                <td colspan="3" style="border: none; border-bottom: 1px solid #000; height: 10px;"></td>
            </tr>
            <tr>
                <td colspan="3" style="padding: 6px;">
                    <strong>Note:</strong>
                    <div style="margin-top: 5px; padding: 5px;">
                        @foreach ($approvers as $app)
                            @if (!empty($app['catatan']))
                                <p style="margin: 0 0 4px 0;">
                                    <strong>- </strong>
                                    {{ $app['catatan'] }}
                                </p>
                            @endif
                        @endforeach

                        @if (collect($approvers)->whereNotNull('catatan')->where('catatan', '!=', '')->isEmpty())
                            <p style="color: #666; font-style: italic; margin: 0;">Tidak ada catatan.</p>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- Table ttd --}}
        <table class="text-center" cellspacing="0" cellpadding="4"
            style="white-space: nowrap; border: 1px solid #000; border-top: 0; width: 100%; border-collapse: collapse;">
            <tr>
                <td colspan="3" style="height: 10px; border: none; border-bottom: 1px solid #000;"></td>
            </tr>

            <tr>
                <td style="width: 33%; border-right: 1px solid #000;">Dibuat oleh,</td>
                <td style="width: 33%; border-right: 1px solid #000;">Diperiksa oleh,</td>
                <td style="width: 34%;">Diketahui oleh,</td>
            </tr>
            <tr>
                <td class="approver-ttd-cell">
                    @if (!empty($approvers[0]['ttd']))
                        <img src="{{ $approvers[0]['ttd'] }}" width="80" alt="TTD {{ $approvers[0]['nama'] }}">
                    @else
                        &nbsp;
                    @endif
                </td>
                <td class="approver-ttd-cell">
                    @if (!empty($approvers[1]['ttd']))
                        <img src="{{ $approvers[1]['ttd'] }}" width="80" alt="TTD {{ $approvers[1]['nama'] }}">
                    @else
                        &nbsp;
                    @endif
                </td>
                <td class="approver-ttd-cell">
                    @if (!empty($approvers[2]['ttd']))
                        <img src="{{ $approvers[2]['ttd'] }}" width="80" alt="TTD {{ $approvers[2]['nama'] }}">
                    @else
                        &nbsp;
                    @endif
                </td>
            </tr>

            <tr>
                <td class="approver-name-cell">
                    @if (isset($approvers[0]))
                        <span style="font-size: 11px;"><strong>{{ $approvers[0]['nama'] }}</strong></span>
                        <br>
                        <span style="font-size: 11px;">{{ $approvers[0]['action_at'] }}</span>
                    @else
                        &nbsp;
                    @endif
                </td>
                <td class="approver-name-cell">
                    @if (isset($approvers[1]))
                        <span style="font-size: 11px;"><strong>{{ $approvers[1]['nama'] }}</strong></span>
                        <br>
                        <span style="font-size: 11px;">{{ $approvers[1]['action_at'] }}</span>
                    @else
                        &nbsp;
                    @endif
                </td>
                <td class="approver-name-cell">
                    @if (isset($approvers[2]))
                        <span style="font-size: 11px;"><strong>{{ $approvers[2]['nama'] }}</strong></span>
                        <br>
                        <span style="font-size: 11px;">{{ $approvers[2]['action_at'] }}</span>
                    @else
                        &nbsp;
                    @endif
                </td>
            </tr>

            <tr style="border-top: 1px solid #000;">
                <td style="text-align: center;"><span style="font-size: 11px;">Stock Control</span></td>
                <td style="text-align: center;"><span style="font-size: 11px;">Foreman</span></td>
                <td style="text-align: center;"><span style="font-size: 11px;">Spv/Dept. Head</span></td>
            </tr>

            <tr>
                <td colspan="3" style="height: 10px; border: none; border-bottom: 1px solid #000;"></td>
            </tr>
        </table>

        {{-- Table footer --}}
        <table class="text-right" cellspacing="0" cellpadding="4"
            style="width: 100%; border: none; padding-top: 5px;">
            <tr>
                <td colspan="3" style="height: 10px; border: none; text-align: right; font-size: 11px;">
                    FRM/WCP/04/000/001-00</td>
            </tr>
        </table>

        <script type="text/php">
            if (isset($pdf)) {
                $pdf->page_script('
                    if ($PAGE_NUM == 1) {
                        $font = $fontMetrics->get_font("helvetica", "normal");
                        $pdf->text(415, 73, "1 of " . $PAGE_COUNT, $font, 8, array(0,0,0));
                    }
                ');
            }
        </script>
    </body>

</html>
