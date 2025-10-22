<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <title>Laporan SOP WFG</title>
        <style>
            body {
                font-family: 'Calibri', Arial, sans-serif;
                font-size: 12px;
                margin-top: 1rem;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                line-height: 1;
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

            /* hanya garis bawah saja */
            .border-bottom-only {
                border: none !important;
                border-bottom: 1px solid #000 !important;
            }

            /* Hapus border atas pada tabel kedua agar tidak double */
            .no-top-border th,
            .no-top-border td {
                border-top: none !important;
                background: white !important;
            }

            .no-top-border th {
                height: 25px !important;
            }

            .no-border-row td {
                border-top: none !important;
                border-bottom: none !important;
            }
        </style>
    </head>

    <body>

        <!-- HEADER -->
        <table class="header" cellspacing="0" cellpadding="4" style="white-space: nowrap; border: 1px solid #000;">
            <tr>
                <td rowspan="4" class="text-center" style="width: 30%;">
                    <img src="{{ public_path('assets/images/logo/logo.png') }}" width="170">
                </td>
                <td class="text-center" style="font-size: 14px; font-weight: bold; width: 30%;">
                    PT. BUMI ALAM SEGAR
                </td>
                <td class="no-border text-left" style="width: 10%;">No Dok</td>
                <td class="no-border text-left" style="width: 30%;">: 005/WFG/X/2025</td>
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
                <td class="no-border text-left">: {{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}</td>
            </tr>

            <tr>
                <td class="no-border text-left border-bottom-only">Hal</td>
                <td class="no-border text-left border-bottom-only">: 1 of 1</td>
            </tr>

            <tr>
                <td colspan="4" style="height: 25px; border: none;"></td>
            </tr>
        </table>

        <!-- TABEL DATA -->
        <table cellspacing="0" cellpadding="4" class="no-top-border text-center"
            style="white-space: nowrap; border: 1px solid #000; border-top: none;">
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 10%;">MID</th>
                <th style="width: 30%;">Nama Barang</th>
                <th style="width: 6%;">Uom</th>
                <th style="width: 6%;">SAP</th>
                <th style="width: 6%;">Fisik</th>
                <th style="width: 7%;">Selisih</th>
                <th style="width: 30%;">Keterangan</th>
            </tr>

            @foreach ($summaries as $dt)
                <tr class="no-border-row">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $dt->barang->mid_barang }}</td>
                    <td style="text-align: left; padding-left: 5px; word-wrap: break-word;">
                        {{ $dt->barang->nama_barang }}
                    </td>
                    <td>{{ $dt->barang->uom }}</td>
                    <td style="padding-right: 5px;">
                        {{ $dt->barang->stockOnHand->qty_soh ?? 0 }}
                    </td>
                    <td style="padding-right: 5px;">
                        {{ rtrim(rtrim($dt->qty_fisik ?? 0, '0'), '.') }}
                    </td>
                    <td style="padding-right: 5px;">
                        {{ rtrim(rtrim($dt->selisih ?? 0, '0'), '.') }}
                    </td>
                    <td style="text-align: left; padding-left: 5px; word-wrap: break-word;">
                        <b>{{ $dt->keterangan }}</b>
                    </td>
                </tr>
            @endforeach

            {{-- Tambahan 5 baris kosong --}}
            @for ($i = 1; $i <= 5; $i++)
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
        </table>

        {{-- Table ttd --}}
        <table class="text-center" cellspacing="0" cellpadding="4"
            style="white-space: nowrap; border: 1px solid #000; border-top: 0; width: 100%; border-collapse: collapse;">
            <!-- Spacer -->
            <tr>
                <td colspan="3" style="height: 10px; border: none; border-bottom: 1px solid #000;"></td>
            </tr>

            <!-- Judul Tanda Tangan -->
            <tr>
                <td style="width: 33%; border-right: 1px solid #000;">Dibuat oleh,</td>
                <td style="width: 33%; border-right: 1px solid #000;">Diperiksa oleh,</td>
                <td style="width: 34%;">Diketahui oleh,</td>
            </tr>

            {{-- ttd --}}
            <tr>
                <td class="no-border-row"
                    style="height: 100px; border-top: none !important; border-bottom: none !important;">
                    <img src="{{ $approvers[0]['ttd'] }}" width="80" alt="Tanda Tangan Operator">

                    {{-- @if (isset($approvers[0]['ttd']))
                        @php
                            $path0 = str_replace(asset(''), '', $approvers[0]['ttd']);
                            $localPath0 = public_path($path0);
                        @endphp
                        <img src="{{ file_exists($localPath0) ? $localPath0 : public_path('storage/images/ttd/dummy.jpg') }}"
                            width="80" alt="Tanda Tangan Stock Control">
                    @endif --}}
                </td>

                <td class="no-border-row"
                    style="height: 100px; border-top: none !important; border-bottom: none !important;">
                    <img src="{{ $approvers[1]['ttd'] }}" width="80" alt="Tanda Tangan Operator">

                    {{-- @if (isset($approvers[1]['ttd']))
                        @php
                            $path1 = str_replace(asset(''), '', $approvers[1]['ttd']);
                            $localPath1 = public_path($path1);
                        @endphp
                        <img src="{{ file_exists($localPath1) ? $localPath1 : public_path('storage/images/ttd/dummy.jpg') }}"
                            width="80" alt="Tanda Tangan Foreman">
                    @endif --}}
                </td>

                <td class="no-border-row"
                    style="height: 100px; border-top: none !important; border-bottom: none !important;">
                    <img src="{{ $approvers[2]['ttd'] }}" width="80" alt="Tanda Tangan Operator">
                    {{-- @if (isset($approvers[2]['ttd']))
                        @php
                            $path2 = str_replace(asset(''), '', $approvers[2]['ttd']);
                            $localPath2 = public_path($path2);
                        @endphp
                        <img src="{{ file_exists($localPath2) ? $localPath2 : public_path('storage/images/ttd/dummy.jpg') }}"
                            width="80" alt="Tanda Tangan Supervisor">
                    @endif --}}
                </td>
            </tr>

            <!-- Nama Approver (tanpa border) -->
            <tr>
                <td style="text-align: center; border-top: none !important; border-bottom: none !important;">
                    @if (isset($approvers[0]))
                        <span style="font-size: 11px;">{{ $approvers[0]['nama'] }}</span>
                    @else
                        &nbsp;
                    @endif
                </td>
                <td style="text-align: center; border-top: none !important; border-bottom: none !important;">
                    @if (isset($approvers[1]))
                        <span style="font-size: 11px;">{{ $approvers[1]['nama'] }}</span>
                    @else
                        &nbsp;
                    @endif
                </td>
                <td style="text-align: center; border-top: none !important; border-bottom: none !important;">
                    @if (isset($approvers[2]))
                        <span style="font-size: 11px;">{{ $approvers[2]['nama'] }}</span>
                    @else
                        &nbsp;
                    @endif
                </td>
            </tr>

            <!-- Jabatan -->
            <tr>
                <td style="text-align: center;"><span style="font-size: 11px;">Stock
                        Control</span></td>
                <td style="text-align: center;"><span style="font-size: 11px;">Foreman</span></td>
                <td style="text-align: center;"><span style="font-size: 11px;">Spv/Dept.
                        Head</span></td>
            </tr>

            <!-- Spacer Bottom -->
            <tr>
                <td colspan="3" style="height: 20px; border: none; border-bottom: 1px solid #000;"></td>
            </tr>
        </table>


        {{-- Table footer --}}
        <table class="text-right" cellspacing="0" cellpadding="4" style="width: 100%; border: none; padding-top: 5px;">
            <tr>
                <td colspan="3" style="height: 10px; border: none; text-align: right; font-size: 11px;">
                    FRM/WFG/04/000/001-00</td>
            </tr>
        </table>


    </body>

</html>
