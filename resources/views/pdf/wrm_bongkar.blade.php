<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Form Perintah dan Pemeriksaan Bongkar Muat WRM — {{ $order->no_dokumen }}</title>
    <style>
        @page {
            margin: 25px 25px 25px 25px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            color: #000;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .bordered td,
        .bordered th {
            border: 1px solid #000;
            padding: 2.5px 4px;
        }

        .no-border td {
            border: none;
            padding: 1.5px 3px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .strike {
            text-decoration: line-through;
            color: #666;
        }

        .tally-five {
            text-decoration: line-through;
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
            letter-spacing: 1px;
            margin-right: 4px;
        }

        .tally-rem {
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>
    @php
        $insp = array_merge([
            'doc_coa' => 'Ada',
            'doc_kartu_checkpoint' => 'Ada',
            'kondisi_lantai' => 'Baik',
            'kondisi_dinding' => 'Baik',
            'kondisi_sealed' => 'Baik',
            'kondisi_pintu' => 'Baik',
            'kondisi_kebersihan' => 'Bersih',
            'kondisi_bau_tajam' => 'Tidak',
            'foto_plat_nomor' => true,
            'foto_no_container' => false,
            'foto_sealed_container' => false,
            'foto_absorber_fumigan' => false,
            'foto_material_bermasalah' => false,
            'foto_container_berlubang' => false,
            'jumlah_absorber' => '-',
            'jumlah_fumigan' => '-',
            'catatan_penyimpangan' => '',
        ], is_array($order->inspection_data) ? $order->inspection_data : []);

        $choicePair = function ($current, $optA, $optB) {
            if (strcasecmp((string) $current, $optA) === 0) {
                return '<strong>' . $optA . '</strong> / <span class="strike">' . $optB . '</span>';
            }
            return '<span class="strike">' . $optA . '</span> / <strong>' . $optB . '</strong>';
        };
    @endphp

    {{-- HEADER BOX --}}
    <table class="bordered">
        <tr>
            <td rowspan="4" class="text-center" style="width: 22%;">
                <img src="{{ public_path('assets/images/logo/logo.png') }}" width="110">
            </td>
            <td class="text-center fw-bold" style="width: 46%; font-size: 12px;">PT. BUMI ALAM SEGAR</td>
            <td style="width: 10%; border-right: none;">No. Dok.</td>
            <td style="width: 22%; border-left: none;">: {{ $order->no_dokumen ?? '-' }}</td>
        </tr>
        <tr>
            <td class="text-center fw-bold" style="font-size: 10.5px;">WAREHOUSE (WRM)</td>
            <td style="border-right: none;">Rev.</td>
            <td style="border-left: none;">: 00</td>
        </tr>
        <tr>
            <td rowspan="2" class="text-center fw-bold" style="font-size: 10.5px; line-height: 1.25;">
                FORM PERINTAH DAN PEMERIKSAAN<br>BONGKAR MUAT
            </td>
            <td style="border-right: none;">Tanggal</td>
            <td style="border-left: none;">: {{ $order->tanggal ? \Carbon\Carbon::parse($order->tanggal)->format('d . m . y') : '-' }}</td>
        </tr>
        <tr>
            <td style="border-right: none;">Halaman</td>
            <td style="border-left: none;">: 1 / 1</td>
        </tr>
    </table>

    {{-- METADATA 2 KOLOM (Hanya field yang ada di database) --}}
    <table class="no-border" style="margin-top: 5px;">
        <tr>
            <td width="48%" valign="top">
                <table class="no-border">
                    <tr>
                        <td width="115">No. SPKB / PO</td>
                        <td width="8">:</td>
                        <td style="border: 1px solid #000; padding: 2px 5px; font-weight: bold;">{{ $order->no_spkb ?? '-' }}</td>
                    </tr>
                    @if ($order->no_ecp)
                    <tr>
                        <td>No. ECP</td>
                        <td>:</td>
                        <td class="fw-bold">{{ $order->no_ecp }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Sloc</td>
                        <td>:</td>
                        <td class="fw-bold">{{ $order->sloc ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Supplier / Vendor</td>
                        <td>:</td>
                        <td class="fw-bold">{{ $order->supplier_vendor ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Gate</td>
                        <td>:</td>
                        <td>{{ $order->gate ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>No. Kontainer</td>
                        <td>:</td>
                        <td>{{ $order->no_kontainer ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>No. Segel BAS</td>
                        <td>:</td>
                        <td>{{ $order->no_segel_bas ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jumlah TKBM</td>
                        <td>:</td>
                        <td>{{ $order->jumlah_tkbm ? $order->jumlah_tkbm . ' Orang' : '-' }}</td>
                    </tr>
                </table>
            </td>
            <td width="52%" valign="top">
                <table class="no-border">
                    <tr>
                        <td width="165">Jenis Pengiriman</td>
                        <td width="8">:</td>
                        <td class="fw-bold">{!! $choicePair($order->jenis_pengiriman ?? 'Lokal', 'Import', 'Lokal') !!}</td>
                        <td class="text-right" style="font-size: 9px; font-style: italic;">
                            <strong>Bongkar</strong> / <span class="strike">Muat</span>
                        </td>
                    </tr>
                    <tr>
                        <td>No. Polisi / Mobil</td>
                        <td>:</td>
                        <td colspan="2" class="fw-bold" style="font-size: 11px;">{{ $order->no_mobil ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Nama Supir</td>
                        <td>:</td>
                        <td colspan="2">{{ $order->driver_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jam Serah Terima ke QC</td>
                        <td>:</td>
                        <td colspan="2" class="fw-bold">{{ $order->jam_serah_terima_qc ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jam Release QC</td>
                        <td>:</td>
                        <td colspan="2" class="fw-bold">{{ $order->jam_release_qc ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jam Mulai Bongkar</td>
                        <td>:</td>
                        <td colspan="2">{{ $order->jam_muat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jam Selesai Bongkar</td>
                        <td>:</td>
                        <td colspan="2">{{ $order->jam_selesai ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @php
        $totalFullPallet = $order->details->where('jenis', 'P')->count();
        $totalReceh = $order->details->where('jenis', 'R')->count();
    @endphp

    {{-- TABEL 1: RINCIAN ITEM MATERIAL (DETAIL SEPERTI WFG MUAT) --}}
    <table class="bordered" style="margin-top: 6px;">
        <thead>
            <tr style="background: #f2f2f2;">
                <th width="5%">No</th>
                <th width="14%">MID</th>
                <th width="28%">Nama Barang</th>
                <th width="14%">No. Batch / Lot</th>
                <th width="7%">Jenis</th>
                <th width="7%">UoM</th>
                <th width="11%">Jumlah</th>
                <th width="14%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($order->details as $index => $detail)
                @php
                    $mid = $detail->mid ?: ($detail->material->mid ?? ($detail->material->mid_barang ?? '-'));
                    $namaBarang = $detail->nama_barang ?: ($detail->material->nama_barang ?? '-');
                    $uom = $detail->uom ?: ($detail->material->uom ?? 'KG');
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center fw-bold">{{ $mid }}</td>
                    <td>{{ $namaBarang }}</td>
                    <td class="text-center">{{ $detail->batch_number ?? '-' }}</td>
                    <td class="text-center">
                        @if (($detail->jenis ?? 'P') === 'P')
                            <span style="background-color: #d1e7dd; color: #0f5132; padding: 1px 5px; border-radius: 3px; font-weight: bold; font-size: 8.5px;">P</span>
                        @elseif (($detail->jenis ?? '') === 'R')
                            <span style="background-color: #f8d7da; color: #842029; padding: 1px 5px; border-radius: 3px; font-weight: bold; font-size: 8.5px;">R</span>
                        @else
                            {{ $detail->jenis ?? '-' }}
                        @endif
                    </td>
                    <td class="text-center">{{ $uom }}</td>
                    <td class="text-end fw-bold">{{ number_format($detail->qty ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $detail->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-2">- Tidak ada rincian item -</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TOTAL FULL PALLET & RECEH --}}
    <table cellspacing="0" cellpadding="2" style="width: 100%; border: none; margin-top: 2px; margin-bottom: 4px;">
        <tr>
            <td style="border: none; text-align: left; font-size: 9.5px;">
                <strong>Total Full Pallet:</strong> {{ $totalFullPallet }} Pallet
                &nbsp;&nbsp;&nbsp;
                <strong>Total Receh:</strong> {{ $totalReceh }} Receh
                &nbsp;&nbsp;&nbsp;
                <strong>Total Keseluruhan Qty:</strong> {{ number_format($totalAllQty, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    {{-- TABEL 2: SUMMARY / RINGKASAN REKAP BONGKAR (TALLY) DI BAWAH DETAIL --}}
    <table class="bordered" style="margin-top: 2px;">
        <thead>
            <tr style="background: #f2f2f2;">
                <th width="5%">No</th>
                <th width="14%">MID</th>
                <th width="28%">Ringkasan Nama Barang</th>
                <th width="7%">UoM</th>
                <th width="13%">Total Qty</th>
                <th width="12%">Total Pallet</th>
                <th width="21%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groupedBongkar as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center fw-bold">{{ $row['mid'] }}</td>
                    <td>{{ $row['nama_barang'] }}</td>
                    <td class="text-center">{{ $row['uom'] }}</td>
                    <td class="text-end fw-bold">{{ number_format($row['qty'], 0, ',', '.') }}</td>
                    <td class="text-center fw-bold">{{ $row['pallet_count'] }} Pallet</td>
                    <td>{{ $row['keterangan'] ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">- Tidak ada summary item -</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="fw-bold" style="background: #f9f9f9;">
                <td colspan="4" class="text-end">Total Keseluruhan:</td>
                <td class="text-end">{{ number_format($totalAllQty, 0, ',', '.') }}</td>
                <td class="text-center">{{ $totalAllPallet }} Pallet</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- KELENGKAPAN DOKUMEN, KONDISI KENDARAAN, KELENGKAPAN FOTO --}}
    <table class="bordered" style="margin-top: 6px;">
        <tr>
            <td width="30%" valign="top">
                <div class="fw-bold" style="border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 3px;">
                    Kelengkapan Dokumen :
                </div>
                <table class="no-border" style="width: 100%;">
                    <tr>
                        <td width="55%">COA</td>
                        <td>: {!! $choicePair($insp['doc_coa'] ?? 'Ada', 'Ada', 'Tidak ada') !!}</td>
                    </tr>
                    <tr>
                        <td>Kartu Checkpoint</td>
                        <td>: {!! $choicePair($insp['doc_kartu_checkpoint'] ?? 'Ada', 'Ada', 'Tidak ada') !!}</td>
                    </tr>
                </table>
                <div style="font-size: 8.5px; margin-top: 10px; font-style: italic;">* Diisi oleh Pengawas</div>
            </td>
            <td width="38%" valign="top">
                <div class="fw-bold" style="border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 3px;">
                    Kondisi Kendaraan :
                </div>
                <table class="no-border" style="width: 100%;">
                    <tr>
                        <td>Lantai Container</td>
                        <td>: {!! $choicePair($insp['kondisi_lantai'] ?? 'Baik', 'Baik', 'Rusak') !!}</td>
                    </tr>
                    <tr>
                        <td>Dinding Container Luar & Dalam</td>
                        <td>: {!! $choicePair($insp['kondisi_dinding'] ?? 'Baik', 'Baik', 'Rusak') !!}</td>
                    </tr>
                    <tr>
                        <td>Sealed Container</td>
                        <td>: {!! $choicePair($insp['kondisi_sealed'] ?? 'Baik', 'Baik', 'Rusak') !!}</td>
                    </tr>
                    <tr>
                        <td>Pintu Container</td>
                        <td>: {!! $choicePair($insp['kondisi_pintu'] ?? 'Baik', 'Baik', 'Rusak') !!}</td>
                    </tr>
                    <tr>
                        <td>Kebersihan Kendaraan</td>
                        <td>: {!! $choicePair($insp['kondisi_kebersihan'] ?? 'Bersih', 'Bersih', 'Tidak Bersih') !!}</td>
                    </tr>
                    <tr>
                        <td>Berbau Tajam</td>
                        <td>: {!! $choicePair($insp['kondisi_bau_tajam'] ?? 'Tidak', 'Ada', 'Tidak') !!}</td>
                    </tr>
                </table>
            </td>
            <td width="32%" valign="top">
                <div class="fw-bold" style="border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 3px;">
                    Kelengkapan Foto* :
                </div>
                <table class="no-border" style="width: 100%;">
                    <tr>
                        <td>({{ !empty($insp['foto_plat_nomor']) ? 'V' : ' ' }}) Foto Plat Nomor</td>
                    </tr>
                    <tr>
                        <td>({{ !empty($insp['foto_no_container']) ? 'V' : ' ' }}) Foto No Container</td>
                    </tr>
                    <tr>
                        <td>({{ !empty($insp['foto_sealed_container']) ? 'V' : ' ' }}) Foto Sealed Container</td>
                    </tr>
                    <tr>
                        <td>({{ !empty($insp['foto_absorber_fumigan']) ? 'V' : ' ' }}) Foto Absorber/ Fumigan</td>
                    </tr>
                    <tr>
                        <td>({{ !empty($insp['foto_material_bermasalah']) ? 'V' : ' ' }}) Foto Material Bermasalah</td>
                    </tr>
                    <tr>
                        <td>({{ !empty($insp['foto_container_berlubang']) ? 'V' : ' ' }}) Foto Container Berlubang</td>
                    </tr>
                    <tr>
                        <td>Jumlah Absorber: <b>{{ $insp['jumlah_absorber'] ?? '-' }}</b></td>
                    </tr>
                    <tr>
                        <td>Jumlah Fumigan: <b>{{ $insp['jumlah_fumigan'] ?? '-' }}</b></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- BOX CATATAN PENYIMPANGAN --}}
    <table class="bordered" style="margin-top: 4px;">
        <tr>
            <td style="font-size: 8.5px; font-style: italic; background: #fafafa;" colspan="2">
                Mohon dicatat bila ditemukan penyimpangan:
            </td>
        </tr>
        <tr>
            <td colspan="2" style="height: 24px; vertical-align: top; font-weight: bold; font-size: 9.5px;">
                {{ $insp['catatan_penyimpangan'] ?? '-' }}
            </td>
        </tr>
    </table>

    {{-- TANDA TANGAN (5 KOLOM: QC, PENGAWAS, SOPIR, ADMINISTRASI, FOREMAN/SPV) + CATATAN TKBM --}}
    <table class="bordered" style="margin-top: 4px;">
        <tr class="text-center" style="background: #f2f2f2; font-weight: bold;">
            <td width="15%">Quality Control</td>
            <td width="15%">Pengawas</td>
            <td width="15%">Sopir</td>
            <td width="15%">Administrasi</td>
            <td width="16%">Foreman/ SPV</td>
            <td width="24%">Catatan :</td>
        </tr>
        <tr>
            <td style="height: 48px; text-align: center; vertical-align: middle;">
                @if ($order->qc_signature)
                    <img src="{{ public_path($order->qc_signature) }}" style="max-height: 42px; max-width: 95%;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle;">
                @if ($order->checker_signature)
                    <img src="{{ public_path($order->checker_signature) }}" style="max-height: 42px; max-width: 95%;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle;">
                @if ($order->driver_signature)
                    <img src="{{ public_path($order->driver_signature) }}" style="max-height: 42px; max-width: 95%;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle;">
                @if ($order->verified_signature)
                    <img src="{{ public_path($order->verified_signature) }}" style="max-height: 42px; max-width: 95%;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle;">
                {{-- Foreman / SPV --}}
            </td>
            <td valign="top" style="font-size: 8.5px;">
                <div style="font-style: italic; margin-bottom: 3px;">Proses muat tidak perlu tanda tangan QC</div>
                @if ($order->jumlah_tkbm)
                    <div class="fw-bold" style="font-size: 10px;">TKBM: {{ $order->jumlah_tkbm }} orang</div>
                @endif
                @if ($order->catatan)
                    <div style="margin-top: 2px;">{{ $order->catatan }}</div>
                @endif
            </td>
        </tr>
        <tr style="font-size: 8.5px;">
            <td>
                Nama: {{ $order->qcUser->username ?? $order->qcUser->name ?? '-' }}<br>
                Tanggal: {{ $order->qc_approved_at ? \Carbon\Carbon::parse($order->qc_approved_at)->format('d/m/y') : '-' }}
            </td>
            <td>
                Nama: {{ $order->checker->username ?? $order->checker->name ?? '-' }}<br>
                Tanggal: {{ $order->approved_at ? \Carbon\Carbon::parse($order->approved_at)->format('d/m/y') : '-' }}
            </td>
            <td>
                Nama: {{ $order->driver_name ?? '-' }}<br>
                Tanggal: {{ $order->driver_approved_at ? \Carbon\Carbon::parse($order->driver_approved_at)->format('d/m/y') : '-' }}
            </td>
            <td>
                Nama: {{ $order->verificator->username ?? $order->verificator->name ?? '-' }}<br>
                Tanggal: {{ $order->verified_at ? \Carbon\Carbon::parse($order->verified_at)->format('d/m/y') : '-' }}
            </td>
            <td>
                Nama:<br>
                Tanggal:
            </td>
            <td class="text-right" style="vertical-align: bottom; font-weight: bold; font-size: 8px;">
                FRM/WRM/01/000/001-01
            </td>
        </tr>
    </table>

</body>

</html>
