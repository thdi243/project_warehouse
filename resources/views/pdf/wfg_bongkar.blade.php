<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Form Perintah dan Pemeriksaan Bongkar Muat — {{ $order->no_dokumen }}</title>
    <style>
        @page {
            margin: 28px 26px 28px 26px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
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
            padding: 3px 5px;
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
            margin-right: 5px;
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
            'catatan_tkbm' => '',
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
            <td rowspan="4" class="text-center" style="width: 24%;">
                <img src="{{ public_path('assets/images/logo/logo.png') }}" width="115">
            </td>
            <td class="text-center fw-bold" style="width: 44%; font-size: 12px;">PT. BUMI ALAM SEGAR</td>
            <td style="width: 10%; border-right: none;">No. Dok.</td>
            <td style="width: 22%; border-left: none;">: {{ $order->no_dokumen ?? '-' }}</td>
        </tr>
        <tr>
            <td class="text-center fw-bold" style="font-size: 11px;">WAREHOUSE</td>
            <td style="border-right: none;">Rev.</td>
            <td style="border-left: none;">: 00</td>
        </tr>
        <tr>
            <td rowspan="2" class="text-center fw-bold" style="font-size: 11px; line-height: 1.25;">
                FORM PERINTAH DAN PEMERIKSAAN<br>BONGKAR MUAT
            </td>
            <td style="border-right: none;">Tanggal</td>
            <td style="border-left: none;">: {{ $order->tanggal ? \Carbon\Carbon::parse($order->tanggal)->format('d - m - y') : '-' }}</td>
        </tr>
        <tr>
            <td style="border-right: none;">Halaman</td>
            <td style="border-left: none;">: 1 / 1</td>
        </tr>
    </table>

    {{-- METADATA 2 KOLOM (Hanya field yang ada di database) --}}
    <table class="no-border" style="margin-top: 6px;">
        <tr>
            <td width="48%" valign="top">
                <table class="no-border">
                    <tr>
                        <td width="110">No. SPKB / PO</td>
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
                        <td class="text-right" style="font-size: 9.5px; font-style: italic;">
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
                        <td colspan="2">{{ $order->jam_serah_terima_qc ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jam Release QC</td>
                        <td>:</td>
                        <td colspan="2">{{ $order->jam_release_qc ?? '-' }}</td>
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
                    $mid = $detail->material->mid_barang ?? ($detail->material->mid ?? '-');
                    $namaBarang = $detail->material->nama_barang ?? '-';
                    $uom = $detail->uom ?: ($detail->material->uom ?? 'BOX');
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

    {{-- CHECKLIST 3 KOLOM --}}
    <table class="no-border" style="margin-top: 6px; font-size: 9.5px;">
        <tr>
            {{-- Kolom 1: Kelengkapan Dokumen --}}
            <td width="28%" valign="top">
                <div class="fw-bold" style="margin-bottom: 4px;">Kelengkapan Dokumen :</div>
                <table class="no-border">
                    <tr>
                        <td width="95">COA</td>
                        <td>{!! $choicePair($insp['doc_coa'] ?? 'Ada', 'Ada', 'Tidak ada') !!}</td>
                    </tr>
                    <tr>
                        <td>Kartu Checkpoint</td>
                        <td>{!! $choicePair($insp['doc_kartu_checkpoint'] ?? 'Ada', 'Ada', 'Tidak ada') !!}</td>
                    </tr>
                </table>
                <div style="margin-top: 18px; font-style: italic; font-size: 8.5px;">* Diisi oleh Pengawas</div>
            </td>

            {{-- Kolom 2: Kondisi Kendaraan --}}
            <td width="36%" valign="top">
                <div class="fw-bold" style="margin-bottom: 4px;">Kondisi Kendaraan :</div>
                <table class="no-border">
                    <tr>
                        <td width="145">Lantai Container</td>
                        <td>{!! $choicePair($insp['kondisi_lantai'] ?? 'Baik', 'Baik', 'Rusak') !!}</td>
                    </tr>
                    <tr>
                        <td>Dinding Container Luar &amp; Dalam</td>
                        <td>{!! $choicePair($insp['kondisi_dinding'] ?? 'Baik', 'Baik', 'Rusak') !!}</td>
                    </tr>
                    <tr>
                        <td>Sealed Container</td>
                        <td>{!! $choicePair($insp['kondisi_sealed'] ?? 'Baik', 'Baik', 'Rusak') !!}</td>
                    </tr>
                    <tr>
                        <td>Pintu Container</td>
                        <td>{!! $choicePair($insp['kondisi_pintu'] ?? 'Baik', 'Baik', 'Rusak') !!}</td>
                    </tr>
                    <tr>
                        <td>Kebersihan Kendaraan</td>
                        <td>{!! $choicePair($insp['kondisi_kebersihan'] ?? 'Bersih', 'Bersih', 'Tidak Bersih') !!}</td>
                    </tr>
                    <tr>
                        <td>Berbau Tajam</td>
                        <td>{!! $choicePair($insp['kondisi_bau_tajam'] ?? 'Tidak', 'Ada', 'Tidak') !!}</td>
                    </tr>
                </table>
            </td>

            {{-- Kolom 3: Kelengkapan Foto --}}
            <td width="36%" valign="top">
                <div class="fw-bold" style="margin-bottom: 4px;">Kelengkapan Foto* :</div>
                <table class="no-border">
                    <tr>
                        <td width="50%">({{ !empty($insp['foto_plat_nomor']) ? 'v' : ' ' }}) Foto Plat Nomor</td>
                        <td width="50%">({{ !empty($insp['foto_absorber_fumigan']) ? 'v' : ' ' }}) Foto Absorber/ Fumigan</td>
                    </tr>
                    <tr>
                        <td>({{ !empty($insp['foto_no_container']) ? 'v' : ' ' }}) Foto No Container</td>
                        <td>({{ !empty($insp['foto_material_bermasalah']) ? 'v' : ' ' }}) Foto Material Bermasalah</td>
                    </tr>
                    <tr>
                        <td>({{ !empty($insp['foto_sealed_container']) ? 'v' : ' ' }}) Foto Sealed Container</td>
                        <td>({{ !empty($insp['foto_container_berlubang']) ? 'v' : ' ' }}) Foto Container Berlubang</td>
                    </tr>
                    <tr>
                        <td style="padding-top: 6px;">Jumlah Absorber</td>
                        <td style="padding-top: 6px;">: {{ $insp['jumlah_absorber'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jumlah Fumigan</td>
                        <td>: {{ $insp['jumlah_fumigan'] ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- CATATAN PENYIMPANGAN --}}
    <table class="bordered" style="margin-top: 6px;">
        <tr>
            <td style="height: 28px; vertical-align: top;">
                <span style="font-size: 9px; font-weight: bold;">Mohon dicatat bila ditemukan penyimpangan</span><br>
                <span>{{ $insp['catatan_penyimpangan'] ?: '-' }}</span>
            </td>
        </tr>
    </table>

    {{-- TANDA TANGAN & CATATAN TKBM --}}
    <table class="bordered" style="margin-top: -1px;">
        <thead>
            <tr>
                <th width="14%">Quality Control</th>
                <th width="15%">Pengawas</th>
                <th width="15%">Sopir</th>
                <th width="15%">Administrasi</th>
                <th width="15%">Foreman/ SPV</th>
                <th width="26%" style="text-align: left; font-weight: normal; font-size: 8.5px;">
                    <strong>Catatan :</strong><br>
                    Proses muat tidak perlu tanda tangan QC
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center" style="height: 62px;"></td>
                <td class="text-center" style="height: 62px;">
                    @if ($order->checker_signature)
                        @php
                            $cSig = ltrim($order->checker_signature, '/');
                            if (!Str::startsWith($cSig, 'storage/')) $cSig = 'storage/' . $cSig;
                        @endphp
                        <img src="{{ public_path($cSig) }}" style="max-height: 55px; max-width: 95px;">
                    @endif
                </td>
                <td class="text-center" style="height: 62px;">
                    @if ($order->driver_signature)
                        @php
                            $dSig = ltrim($order->driver_signature, '/');
                            if (!Str::startsWith($dSig, 'storage/')) $dSig = 'storage/' . $dSig;
                        @endphp
                        <img src="{{ public_path($dSig) }}" style="max-height: 55px; max-width: 95px;">
                    @endif
                </td>
                <td class="text-center" style="height: 62px;">
                    @if ($order->verified_signature)
                        @php
                            $vSig = ltrim($order->verified_signature, '/');
                            if (!Str::startsWith($vSig, 'storage/')) $vSig = 'storage/' . $vSig;
                        @endphp
                        <img src="{{ public_path($vSig) }}" style="max-height: 55px; max-width: 95px;">
                    @endif
                </td>
                <td class="text-center" style="height: 62px;"></td>
                <td rowspan="2" valign="top" style="font-size: 11px; font-weight: bold; padding: 8px;">
                    @if ($order->jumlah_tkbm)
                        TKBM : {{ $order->jumlah_tkbm }} orang<br>
                    @endif
                    {{ $insp['catatan_tkbm'] ?? '' }}
                    @if ($order->verified_note)
                        <div style="font-size: 9px; font-weight: normal; margin-top: 4px;">{{ $order->verified_note }}</div>
                    @endif
                </td>
            </tr>
            <tr style="font-size: 9px;">
                <td>
                    Nama :<br>
                    Tanggal :
                </td>
                <td>
                    Nama : <strong>{{ $order->checker->username ?? '' }}</strong><br>
                    Tanggal : {{ $order->approved_at ? \Carbon\Carbon::parse($order->approved_at)->format('d/m/y') : '' }}
                </td>
                <td>
                    Nama : <strong>{{ $order->driver_name ?? '' }}</strong><br>
                    Tanggal : {{ $order->driver_approved_at ? \Carbon\Carbon::parse($order->driver_approved_at)->format('d/m/y') : '' }}
                </td>
                <td>
                    Nama : <strong>{{ $order->verificator->username ?? '' }}</strong><br>
                    Tanggal : {{ $order->verified_at ? \Carbon\Carbon::parse($order->verified_at)->format('d/m/y') : '' }}
                </td>
                <td>
                    Nama :<br>
                    Tanggal :
                </td>
            </tr>
        </tbody>
    </table>

    <div class="text-right" style="margin-top: 4px; font-size: 9px; font-weight: bold;">
        FRM/WFG/01/000/004-01
    </div>
</body>

</html>
