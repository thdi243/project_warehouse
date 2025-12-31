<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Document</title>

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
                border-top: none;
            }

            .info-table td {
                border: none !important;
            }

            .no-border-row td {
                border-top: none !important;
                border-bottom: none !important;
            }

            .approver-ttd-cell {
                height: 70px;
                border-top: none !important;
                border-bottom: none !important;
                text-align: center;
            }

            .approver-name-cell {
                text-align: center;
                border-top: none !important;
                border-bottom: none !important;
                text-transform: capitalize;
            }
        </style>
    </head>

    <body>
        @php
            function formatDept($dept)
            {
                if (!$dept) {
                    return '';
                }

                return collect(explode('_', strtolower($dept)))
                    ->map(fn($w) => ucfirst($w))
                    ->implode(' ');
            }
        @endphp


        <!-- HEADER -->
        <table class="header" cellspacing="0" cellpadding="4"
            style="white-space: nowrap; border: 1px solid #000; border-bottom: none !important;">
            <tr>
                <td rowspan="3" class="text-center" style="width: 25%;">
                    <img src="{{ public_path('assets/images//logo/logo.png') }}" width="120">
                </td>
                <td rowspan="2" class="text-center" style="font-size: 14px; font-weight: bold; width: 40%;">
                    PURCHASE REQUESITION
                </td>
                <td class="no-border text-left" style="width: 10%;">No Rec</td>
                <td class="no-border text-left" style="width: 25%;">{{ $pr->no_rec ?? '-' }}</td>
            </tr>

            <tr>
                <td class="no-border text-left">Date</td>
                <td class="no-border text-left">{{ \Carbon\Carbon::parse($pr->pr_date)->format('d/m/Y') }}</td>
            </tr>

            <tr>
                <td class="text-center" style="font-size: 12px; font-weight: bold;">
                    WAREHOUSE SPAREPART
                </td>
                <td class="no-border text-left">Hal</td>
                <td class="no-border text-left">{{ $pr->hal ?? '-' }}</td>
            </tr>
        </table>

        {{-- Information --}}
        <table class="info-table" cellspacing="0" cellpadding="4" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td
                    style="width: 15%; padding-left: 30px; padding-top: 10px; border-left: 1px solid black !important;;">
                    <strong>PR No</strong>
                </td>
                <td style="width: 35%; padding-top: 10px;">
                    : {{ $pr->pr_number ?? '-' }}
                </td>
                <td style="width: 15%; padding-left: 30px; padding-top: 10px;">
                    <strong>Jenis PR</strong>
                </td>
                <td style="width: 35%; padding-top: 10px; border-right: 1px solid black !important;">
                    : {{ $pr->jenis . ' / ' . $pr->detail_jenis ?? '-' }}
                </td>
            </tr>
            <tr>
                <td style="padding-left: 30px; border-left: 1px solid black !important;">
                    <strong>Nama User</strong>
                </td>
                <td>
                    : {{ $pr->requested_by ?? '-' }}
                </td>
                <td style="padding-left: 30px;">
                    <strong>No IO</strong>
                </td>
                <td style="border-right: 1px solid black !important;">
                    : {{ $pr->no_io ?? '-' }}
                </td>
            </tr>
            <tr>
                <td style="padding-left: 30px; padding-bottom: 10px; border-left: 1px solid black !important;;">
                    <strong>Dept</strong>
                </td>
                <td colspan="3" style="padding-bottom: 10px; border-right: 1px solid black !important;">
                    : {{ strToUpper($pr->department ?? '-') }}
                </td>

            </tr>
        </table>

        <!-- TABEL DATA -->
        <table cellspacing="0" cellpadding="4" class="text-center data-table">
            <thead style="border-top: none;">
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 10%;">MID</th>
                    <th style="width: 24%;">DESC</th>
                    <th style="width: 6%;">UOM</th>
                    <th style="width: 6%;">QTY</th>
                    <th style="width: 6%;">QTY</th>
                    <th style="width: 6%;">QTY</th>
                    <th style="width: 24%;">KETERANGAN</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($pr->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->barang->mid_barang ?? '-' }}</td>
                        <td style="text-align: left; padding-left: 5px;">
                            {{ $item->barang->nama_barang ?? '-' }}
                        </td>
                        <td>{{ $item->barang->uom ?? '-' }}</td>
                        <td style="padding-right: 5px;">
                            {{ rtrim(rtrim($item->qty ?? 0, '0'), '.') }}
                        </td>
                        <td style="padding-right: 5px;">
                            {{ rtrim(rtrim($item->latestStock->qty_soh ?? 0, '0'), '.') }}
                        </td>
                        <td style="padding-right: 5px;">
                            {{ rtrim(rtrim($item->latestStock->qty_soh - $item->qty ?? 0, '0'), '.') }}
                        </td>
                        <td style="padding-right: 5px;">
                            {{ $item->keterangan ?? '-' }}
                        </td>
                    </tr>
                @endforeach

                @php
                    $jumlahData = $pr->items->count();

                    $minRow = 5;

                    $tambahan = $jumlahData < $minRow ? $minRow - $jumlahData : 0;
                @endphp

                @for ($i = 0; $i < $tambahan; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        {{-- Table ttd --}}
        <table class="text-center" cellspacing="0" cellpadding="4"
            style="white-space: nowrap; border: 1px solid #000; border-top: 0; width: 100%; border-collapse: collapse;">
            <tr>
                <td colspan="4" style="height: 10px; border: none; border-bottom: 1px solid #000;"></td>
            </tr>
            <tr>
                <td style="width: 33%; border-right: 1px solid #000;">
                    <strong>Dibuat oleh,</strong>
                </td>
                <td style="width: 33%; border-right: 1px solid #000;">
                    <strong>Disetujui
                        oleh,</strong>
                </td>
                <td style="width: 33%; border-right: 1px solid #000;">
                    <strong>Diketahui
                        oleh,</strong>
                </td>
                <td style="width: 34%;"><strong>Diterima oleh,</strong></td>
            </tr>

            <tr>
                <td class="approver-ttd-cell">
                    <img src="{{ $approvers[0]['ttd'] ?? '' }}" width="100" alt="TTD">
                </td>
                <td class="approver-ttd-cell">
                    <img src="{{ $approvers[1]['ttd'] ?? '' }}" width="100" alt="TTD ">
                </td>
                <td class="approver-ttd-cell">
                    <img src="{{ $approvers[2]['ttd'] ?? '' }}" width="100" alt="TTD ">
                </td>
                <td class="approver-ttd-cell">
                    <img src="{{ $approvers[3]['ttd'] ?? '' }}" width="100" alt="TTD ">
                </td>
            </tr>

            <tr>
                <td class="approver-name-cell">

                    <span style="font-size: 11px;"><strong>{{ $approvers[0]['nama'] ?? '' }}</strong></span>
                    <br>
                    <span style="font-size: 11px;">{{ $approvers[0]['action_at'] ?? '' }}</span>

                </td>
                <td class="approver-name-cell">

                    <span style="font-size: 11px;"><strong>{{ $approvers[1]['nama'] ?? '' }}</strong></span>
                    <br>
                    <span style="font-size: 11px;">{{ $approvers[1]['action_at'] ?? '' }}</span>

                </td>
                <td class="approver-name-cell">

                    <span style="font-size: 11px;"><strong>{{ $approvers[2]['nama'] ?? '' }}</strong></span>
                    <br>
                    <span style="font-size: 11px;">{{ $approvers[2]['action_at'] ?? '' }}</span>

                </td>
                <td class="approver-name-cell">

                    <span style="font-size: 11px;"><strong>{{ $approvers[3]['nama'] ?? '' }}</strong></span>
                    <br>
                    <span style="font-size: 11px;">{{ $approvers[3]['action_at'] ?? '' }}</span>

                </td>
            </tr>

            <tr>
                <td style="text-align: center;"><strong style="font-size: 11px;">Dept.
                        {{ formatDept($approvers[0]['dept'] ?? '') }}</strong>
                </td>
                <td style="text-align: center;"><strong style="font-size: 11px;">Dept.
                        Head {{ formatDept($approvers[1]['dept'] ?? '') }}</strong>
                </td>
                <td style="text-align: center;"><strong style="font-size: 11px;">Dept.
                        Head Warehouse</strong>
                </td>
                <td style="text-align: center;"><strong style="font-size: 11px;">Dept. WSP</span></td>
            </tr>

            <tr>
                <td class="text-left" colspan="4"
                    style="height: 10px; border: none; border-bottom: 1px solid #000;">
                    Note: Apabila PR direvisi atau dihapus, maka User wajib membuat Internal Memo ditanda tangani
                    oleh
                    Dept. Head
                </td>
            </tr>
        </table>
    </body>

</html>
