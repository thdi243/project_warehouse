<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <title>Laporan TKBM</title>
        <style>
            body {
                font-family: 'Calibri', Arial, sans-serif;
                font-size: 12px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #000;
                /* text-align: center; */
            }

            p {
                margin: 0;
                padding: 0;
            }

            .header-table td {
                padding: 0 4px 0 4px;
                line-height: 1;
                height: 20px;
            }

            /* .grup-table td {
                border: none;
            } */

            .body-value {
                text-align: right;
            }


            .total td {
                font-weight: bold;
                /* border: none; */
            }

            .ttd td {
                text-align: center;
                font-weight: bold;
                /* border: none; */
            }
        </style>
    </head>

    <body>
        <table class="header-table">
            <tr>
                <td rowspan="4" colspan="7" style="text-align: center; vertical-align: middle;">
                    <img src="{{ public_path('assets/images//logo/logo.png') }}" width="150">
                </td>
                <td colspan="11" rowspan="3" style="text-align: center; font-size: 14px;">
                    <b>PT. BUMI ALAM SEGAR</b>
                </td>
                <td colspan="3" style="text-align: left;">No Dok</td>
                <td colspan="8" style="text-align: left;">{{ $summary->no_dok }}</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: left;">Rev</td>
                <td colspan="8" style="text-align: left;">0</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: left;">Tanggal</td>
                <td colspan="8" style="text-align: left;">{{ \Carbon\Carbon::now()->format('d/M/Y') }}</td>
            </tr>
            <tr>
                <td colspan="11" style="text-align: center; font-size: 12px;">
                    <p>UPAH BPS</p>
                </td>
                <td colspan="3" style="text-align: left;">Halaman</td>
                <td colspan="8" style="text-align: left;">1 of 1</td>
            </tr>
            <tr>
                <td style="padding-top: 20px;" colspan="29">
                    <p style="text-align: left; margin: 0; font-weight: bold;">Grup: Alvi Yana Jaya</p>
                </td>
            </tr>
            <tr>
                <th colspan="6">Tanggal</th>
                <th colspan="4">Qty<br>Terpal</th>
                <th colspan="4">Qty Slipsheet</th>
                <th colspan="4">Qty<br>Pallet</th>
                <th colspan="5">Total</th>
                <th colspan="6">Keterangan<br>(Fee 6.5%)</th>
            </tr>
            @foreach ($data as $item)
                <tr class="body-value">
                    <td colspan="6" style="text-align: center;">
                        {{ $item->date ? \Carbon\Carbon::parse($item->date)->format('d/M/Y') : '-' }}
                    </td>
                    <td colspan="4">
                        {{ ($item->qty_terpal ?? 0) == 0 ? '-' : $item->qty_terpal }}
                    </td>
                    <td colspan="4">
                        {{ ($item->qty_slipsheet ?? 0) == 0 ? '-' : $item->qty_slipsheet }}
                    </td>
                    <td colspan="4">
                        {{ ($item->qty_pallet ?? 0) == 0 ? '-' : $item->qty_pallet }}
                    </td>
                    <td colspan="5" style="font-weight: bold;">
                        {{ ($item->total_qty ?? 0) == 0 ? '-' : number_format($item->total_qty) }}
                    </td>
                    <td colspan="6" style="font-weight: bold;">
                        {{ ($item->total_fee ?? 0) == 0 ? '-' : number_format($item->total_fee) }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td style="padding-top: 10px; border: none;" colspan="29">
                    <p style="text-align: left; margin: 0;"></p>
                </td>
            </tr>
            <tr class="total">
                <td colspan="6" style="height:40px; text-align:center;">Total</td>

                <!-- Qty Terpal -->
                <td colspan="4" style="height:40px; text-align:right;">
                    {{ number_format($summary->sum_terpal, 0, ',', '.') }}
                </td>

                <!-- Qty Slipsheet -->
                <td colspan="4" style="height:40px; text-align:right;">
                    {{ number_format($summary->sum_slipsheet, 0, ',', '.') }}
                </td>

                <!-- Qty Pallet -->
                <td colspan="4" style="height:40px; text-align:right;">
                    {{ number_format($summary->sum_pallet, 0, ',', '.') }}
                </td>

                <!-- Total Qty -->
                <td colspan="5" style="height:40px; vertical-align: middle;">
                    <table width="100%" style="border:none; border-collapse: collapse;">
                        <tr>
                            <td style="text-align:left; width:10%; border:none;">Rp</td>
                            <td style="text-align:right; border:none;">
                                {{ number_format($summary->sum_total_qty, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>

                <!-- Total Fee -->
                <td colspan="6" style="height:40px; vertical-align: middle;">
                    <table width="100%" style="border:none; border-collapse: collapse;">
                        <tr>
                            <td style="text-align:left; width:10%; border:none;">Rp</td>
                            <td style="text-align:right; border:none;">
                                {{ number_format($summary->sum_total_fee, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="total">
                <td colspan="18" style="height:40px; text-align:center; vertical-align: middle;">
                    PPn {{ $latestFee->ppn }}%
                </td>
                <td colspan="11" style="height:40px; vertical-align: middle;">
                    <table width="100%" style="border:none; border-collapse: collapse;">
                        <tr>
                            <td style="text-align:left; width:10%; border:none;">Rp</td>
                            <td style="text-align:right; border:none;">{{ number_format($summary->ppn, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="total">
                <td colspan="18" style="height:40px; text-align:center; vertical-align: middle;">
                    PPh {{ $latestFee->pph }}%
                </td>
                <td colspan="11" style="height:40px; vertical-align: middle;">
                    <table width="100%" style="border:none; border-collapse: collapse;">
                        <tr>
                            <td style="text-align:left; width:10%; border:none;">Rp</td>
                            <td style="text-align:right; border:none;">{{ number_format($summary->pph, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="total">
                <td colspan="18" style="height:40px; text-align:center; vertical-align: middle;">Grand Total</td>
                <td colspan="11" style="height:40px; vertical-align: middle;">
                    <table width="100%" style="border:none; border-collapse: collapse;">
                        <tr>
                            <td style="text-align:left; width:10%; border:none;">Rp</td>
                            <td style="text-align:right; border:none;">
                                {{ number_format($summary->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>


            <tr class="ttd">
                <!-- Baris pertama: top + left + right -->
                <td colspan="7"
                    style="border-left:1px solid black; border-bottom:0; border-top:0; border-right:0; text-align:center; font-weight:bold; height: 40px">
                    Dibuat Oleh,</td>
                <td colspan="8" style="border: none; text-align:center; font-weight:bold; height: 40px">
                    Diketahui Oleh,</td>
                <td colspan="14"
                    style="border-right:1px solid black; border-bottom:0; border-top:0; border-left:0; text-align:center; font-weight:bold; height: 40px">
                    Disetujui Oleh,</td>
            </tr>

            <tr class="ttd">
                <!-- Baris kedua: left + right -->
                <td colspan="7"
                    style="border-left:1px solid black; border-bottom:0; border-top:0; border-right:0; height:80px;">
                </td>
                <td colspan="8" style="border: none; height:80px;"></td>
                <td colspan="14"
                    style="border-right:1px solid black; border-bottom:0; border-top:0; border-left:0; height:80px;">
                </td>
            </tr>

            <tr class="ttd">
                <!-- Baris terakhir: bottom + left + right -->
                <td colspan="7"
                    style="border-bottom:1px solid black; border-top: 0; border-right: 0; border-left:1px solid black; text-align:center; font-weight:bold; height: 40px">
                    Foreman</td>
                <td colspan="8"
                    style="border-bottom:1px solid black; border-top:0; border-left:0; border-right:0; text-align:center; font-weight:bold; height: 40px">
                    SPV</td>
                <td colspan="7"
                    style="border-bottom:1px solid black; border-top:0; border-left:0; border-right:0; text-align:center; font-weight:bold; height: 40px">
                    WRH Manager</td>
                <td colspan="7"
                    style="border-bottom:1px solid black; border-right:1px solid black; border-top:0; border-left:0; text-align:center; font-weight:bold;">
                    Factory Manager</td>
            </tr>

        </table>

        <table style="margin-top: 15px;">
        </table>

        <table class="no-border" style="margin-top: 50px; text-align: center;">
        </table>
    </body>

</html>
