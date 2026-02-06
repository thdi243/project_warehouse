<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <title>Report Ikat Terpal</title>
        <style>
            @page {
                margin: 30px;
            }

            body {
                font-family: 'Calibri', Arial, sans-serif;
                font-size: 12px;
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

            thead {
                display: table-header-group !important;
            }

            tbody {
                display: table-row-group;
            }

            .fw-bold {
                font-weight: bold;
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

            .data-table {
                border-top: none !important;
            }

            .data-table th {
                border-top: none !important;
                line-height: 1.5;
            }

            .info-table td {
                border: none !important;
            }

            .tembusan {
                padding-top: 20px;
                text-align: center;
                border: none !important;
                /* text-transform: capitalize; */
            }

            .approver-ttd-cell {
                height: 90px;
                border: none !important;
                text-align: center;
            }

            .approver-name {
                padding-bottom: 20px;
                text-align: center;
                border: none !important;
                /* text-transform: capitalize; */
            }

            .footer td {
                border-top: none !important;
            }
        </style>
    </head>

    <body>
        <?php
        
        function smart_number_format($number, $decimals = 2, $decimal_sep = ',', $thousands_sep = '.')
        {
            if (!is_numeric($number)) {
                return $number;
            }
        
            $formatted = number_format((float) $number, $decimals, $decimal_sep, $thousands_sep);
        
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, $decimal_sep);
        
            return $formatted === '' ? '0' : $formatted;
        }
        
        ?>

        <!-- HEADER -->
        <table class="header" cellspacing="0" cellpadding="4"
            style="white-space: nowrap; border: 1px solid #000; border-bottom: none !important;">
            <tr>
                <td rowspan="4" class="text-center" style="width: 25%;">
                    <img src="{{ public_path('assets/images//logo/logo.png') }}" width="120">
                </td>
                <td rowspan="3" class="text-center" style="font-size: 14px; font-weight: bold; width: 40%;">
                    PT. BUMI ALAM SEGAR
                </td>
                <td class="no-border text-left" style="width: 10%;">No Dok</td>
                <td class="no-border text-left" style="width: 25%;">{{ $noDok }}</td>
            </tr>

            <tr>
                <td class="no-border text-left">Rev</td>
                <td class="no-border text-left">0</td>
            </tr>
            <tr>
                <td class="no-border text-left">Tanggal</td>
                <td class="no-border text-left">{{ \Carbon\Carbon::now()->format('d M Y') }}</td>
            </tr>

            <tr>
                <td class="text-center" style="font-size: 12px; font-weight: bold;">
                    UPAH IKAT TERPAL
                </td>
                <td class="no-border text-left">Halaman</td>
                <td class="no-border text-left">1 of 1</td>
            </tr>
            <tr>
                <td style="padding-top: 20px;" colspan="4">
                    <p style="text-align: left; margin: 0; font-weight: bold;">Grup: Alvi Yana Jaya</p>
                </td>
            </tr>
        </table>

        <table cellspacing="0" cellpadding="4" class="data-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Qty Pallet</th>
                    <th>Rp/<br>Pallet</th>
                    <th>Jumlah<br>Buruh</th>
                    <th>Total</th>
                    <th>Keterangan <br>(Fee {{ smart_number_format($fee_percent) }}%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $index => $item)
                    <tr>
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d/M/Y') ?? '-' }}
                        </td>
                        <td class="text-right">{{ $item->qty_pallet ?? '-' }}</td>
                        <td class="text-right">
                            {{ smart_number_format($item->produk->harga_pallet ?? '-') }}
                        </td>
                        <td class="text-center">
                            {{ $item->jml_buruh ?? '-' }}
                        </td>
                        <td class="text-right fw-bold">
                            {{ number_format($item->subtotal_barang, 0, ',', '.') ?? '-' }}
                            {{-- {{ smart_number_format($item->subtotal_barang) }} --}}
                        </td>
                        <td class="text-right fw-bold">
                            {{ number_format($item->total_fee, 0, ',', '.') ?? '-' }}
                            {{-- {{ smart_number_format($item->total_fee) }} --}}
                        </td>
                    </tr>
                @endforeach

                @php
                    $jumlahData = count($data);
                    $tambahan = $jumlahData < 10 ? 5 : 3;
                @endphp

                @for ($i = 1; $i <= $tambahan; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>


            {{-- Footer --}}
            <tr>
                <td style="padding-top: 20px;" colspan="6">
                    <p style="text-align: left; margin: 0; font-weight: bold;"></p>
                </td>
            </tr>
            <tr>
                <td colspan="1">
                    <p style="text-align: center; margin: 5px; font-weight: bold;">Total</p>
                </td>
                <td colspan="1">
                    <p class="text-right fw-bold" style=" margin: 0;">
                        {{ smart_number_format($summary['total_qty_pallet'] ?? '0') }}
                    </p>
                </td>
                <td colspan="2">
                    <p style=" margin: 0; font-weight: bold;"></p>
                </td>
                <td colspan="1">
                    <table width="100%" style="border:none; border-collapse: collapse; font-weight: bold;">
                        <tr>
                            <td style="text-align:left; border:none;">Rp</td>
                            <td style="text-align:right; border:none;">
                                {{ number_format($summary['total_subtotal'], 0, ',', '.') }}
                                {{-- {{ smart_number_format($summary['total_subtotal'] ?? '0') }} --}}
                            </td>
                        </tr>
                    </table>
                </td>
                <td colspan="1">
                    <table width="100%" style="border:none; border-collapse: collapse; font-weight: bold;">
                        <tr>
                            <td style="text-align:left; border:none;">Rp</td>
                            <td style="text-align:right; border:none;">
                                {{ number_format($summary['total_fee'], 0, ',', '.') }}
                                {{-- {{ smart_number_format($summary['total_fee'] ?? '0') }} --}}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <p style="text-align: center; margin: 5px; font-weight: bold;">PPn
                        {{ smart_number_format($ppn_percent) }}%</p>
                </td>
                <td colspan="2">
                    <table width="100%" style="border:none; border-collapse: collapse; font-weight: bold;">
                        <tr>
                            <td style="text-align:left; border:none;">Rp</td>
                            <td style="text-align:right; border:none;">
                                {{ number_format($summary['total_ppn'], 0, ',', '.') }}
                                {{-- {{ smart_number_format($summary['total_ppn'] ?? '0') }} --}}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <p style="text-align: center; margin: 5px; font-weight: bold;">PPh
                        {{ smart_number_format($pph_percent) }}%</p>
                </td>
                <td colspan="2">
                    <table width="100%" style="border:none; border-collapse: collapse; font-weight: bold;">
                        <tr>
                            <td style="text-align:left; border:none;">Rp</td>
                            <td style="text-align:right; border:none;">
                                ( {{ number_format($summary['total_pph'], 0, ',', '.') }} )
                                {{-- {{ smart_number_format($summary['total_pph'] ?? '0') }} --}}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <p style="text-align: center; margin: 5px; font-weight: bold;">Grand Total Ikat Terpal</p>
                </td>
                <td colspan="2" style="height:40px; vertical-align: middle;">
                    <table width="100%" style="border:none; border-collapse: collapse; font-weight: bold;">
                        <tr>
                            <td style="text-align:left; border:none;">Rp</td>
                            <td style="text-align:right; border:none;">
                                {{ number_format($summary['grand_total'], 0, ',', '.') ?? '-' }}
                                {{-- {{ smart_number_format($summary['grand_total'] ?? '0') }} --}}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Table ttd --}}
        <table class="text-center" cellspacing="0" cellpadding="4"
            style="white-space: nowrap; border: 1px solid #000; border-top: 0; width: 100%; border-collapse: collapse;">
            {{-- <tr>
                <td colspan="3" style="height: 10px; border: none; border-bottom: 1px solid #000;"></td>
            </tr> --}}
            <tr>
                <td class="tembusan" style="width:25%;">
                    <strong>Dibuat oleh,</strong>
                </td>
                <td class="tembusan" style="width:25%;">
                    <strong>Diketahui
                        oleh,</strong>
                </td>
                <td class="tembusan" style="width: 50%;">
                    <strong>Disetujui
                        oleh,</strong>
                </td>
            </tr>

            <tr>
                <td class="approver-ttd-cell">
                    <img src="" width="100" alt="TTD">
                </td>
                <td class="approver-ttd-cell">
                    <img src="" width="100" alt="TTD ">
                </td>
                <td class="approver-ttd-cell">
                    <img src="" width="100" alt="TTD ">
                </td>
            </tr>

            <tr>
                <td class="approver-name" style="text-align: center;">
                    <strong style="font-size: 11px;">Foreman</strong>
                </td>
                <td class="approver-name" style="text-align: center;">
                    <strong style="font-size: 11px;">SPV</strong>
                </td>
                <td class="approver-name" style="text-align: center;">
                    <strong style="font-size: 11px; padding-right: 80px">WRH Manager</strong>
                    <strong style="font-size: 11px;">Factory Manager</strong>
                </td>
            </tr>
        </table>
    </body>

</html>
