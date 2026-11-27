@extends('layouts.app')

@section('title', '| Form Bongkar WFG')

@section('sidebar-size', 'sm')

@section('styles')
    <style>
        .select2-container .select2-selection--single {
            height: 37px !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 35px !important;
            padding-left: 12px !important;
            color: #495057 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 35px !important;
        }

        .table-input {
            border: 1px solid transparent !important;
            background-color: transparent !important;
            padding: 0.25rem 0.5rem !important;
            border-radius: 4px !important;
            transition: all 0.2s ease-in-out !important;
        }

        .table-input:hover {
            border-color: #ced4da !important;
            background-color: #fff !important;
        }

        .table-input:focus {
            border-color: #86b7fe !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
            outline: 0 !important;
        }

        .tally-group {
            display: inline-block;
            font-family: monospace;
            font-weight: 700;
            letter-spacing: 1px;
            margin-right: 6px;
            color: #0f172a;
        }

        .tally-five {
            text-decoration: line-through;
        }
    </style>
@endsection

@section('content')
    @php
        $insp = array_merge(
            [
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
            ],
            is_array($draft->inspection_data) ? $draft->inspection_data : [],
        );
    @endphp
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <div>
                                <h4 class="mb-sm-0">Form Perintah & Pemeriksaan Bongkar (WFG)</h4>
                                <small class="text-muted">Aktivitas: <span
                                        class="badge bg-warning text-dark">BONGKAR</span></small>
                            </div>
                            <span id="autosave-status" class="badge bg-light text-muted border px-3 py-2 fw-medium ms-md-2">
                                <i class="ri-checkbox-circle-line text-success me-1"></i> Draft tersimpan otomatis
                            </span>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">WFG</a></li>
                                <li class="breadcrumb-item active">Form Bongkar</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs for Active Bongkar Drafts --}}
            <div class="row mb-3">
                <div class="col-12">
                    <ul class="nav nav-tabs nav-tabs-custom nav-warning" role="tablist">
                        @foreach ($allDrafts as $d)
                            @php
                                $isCurrent = $draft && $draft->id == $d->id;
                                $tabUrl =
                                    $d->status === 'draft'
                                        ? route('wfg.bongkar_muat.form_bongkar', ['draft_id' => $d->id])
                                        : route('wfg.bongkar_muat.show', $d->id);

                                $statusBadge = '';
                                if ($d->status === 'submitted') {
                                    $statusBadge =
                                        '<span class="badge bg-warning-subtle text-warning rounded-pill">Submitted</span>';
                                } elseif ($d->status === 'approved') {
                                    $statusBadge =
                                        '<span class="badge bg-primary-subtle text-primary rounded-pill">Approved</span>';
                                }
                            @endphp
                            <li class="nav-item">
                                <a class="nav-link {{ $isCurrent ? 'active' : '' }} d-flex align-items-center gap-2"
                                    href="{{ $tabUrl }}">
                                    <i class="ri-draft-line"></i>
                                    <span>Draft Bongkar #{{ $d->id }}</span>
                                    @if ($d->no_mobil)
                                        <span class="badge bg-info-subtle text-info rounded-pill">{{ $d->no_mobil }}</span>
                                    @endif
                                    {!! $statusBadge !!}
                                </a>
                            </li>
                        @endforeach
                        <li class="nav-item ms-auto">
                            <a class="nav-link fw-medium bg-warning text-dark"
                                href="{{ route('wfg.bongkar_muat.form_bongkar', ['create_new' => 1]) }}">
                                <i class="ri-add-line me-1"></i> Draft Bongkar Baru
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <form id="form-bongkar-wfg">
                @csrf
                <input type="hidden" name="id" value="{{ $draft->id }}">
                <input type="hidden" name="tipe" value="bongkar">

                <div class="row g-3">
                    {{-- Kolom Kiri: Informasi Dokumen & Tujuan --}}
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-primary bg-gradient text-white">
                                <h5 class="card-title mb-0 text-white">
                                    <i class="ri-file-list-3-line me-2"></i>Informasi Dokumen & Pengiriman
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal" class="form-control"
                                            value="{{ isset($draft->tanggal) ? (is_string($draft->tanggal) ? $draft->tanggal : $draft->tanggal->format('Y-m-d')) : date('Y-m-d') }}"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">No. SPKB / SPB</label>
                                        <input type="text" name="no_spkb" id="no_spkb" class="form-control"
                                            placeholder="Masukkan No. SPKB / SPB..." value="{{ $draft->no_spkb ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">No. ECP <span class="text-danger">*</span></label>
                                        <input type="text" name="no_ecp" id="no_ecp" class="form-control"
                                            placeholder="No. ECP..." value="{{ $draft->no_ecp ?? '' }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Sloc</label>
                                        <input type="text" name="sloc" class="form-control" placeholder="A001"
                                            value="{{ $draft->sloc ?? 'A001' }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Supplier / Vendor</label>
                                        <input type="text" name="supplier_vendor" id="supplier_vendor"
                                            class="form-control" placeholder="Nama Supplier / Vendor..."
                                            value="{{ $draft->supplier_vendor ?? ($matchedWfgTx->vendor ?? '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">No. Segel</label>
                                        <input type="text" name="no_segel_bas" class="form-control"
                                            placeholder="No. Segel..." value="{{ $draft->no_segel_bas ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Jumlah TKBM</label>
                                        <div class="input-group">
                                            <input type="number" name="jumlah_tkbm" class="form-control"
                                                placeholder="Contoh: 2" value="{{ $draft->jumlah_tkbm ?? '' }}">
                                            <span class="input-group-text">Orang</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Kendaraan & Operasional --}}
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-info bg-gradient text-white">
                                <h5 class="card-title mb-0 text-white">
                                    <i class="ri-truck-line me-2"></i>Kendaraan & Operasional
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">No. Polisi <span class="text-danger">*</span> <small
                                                class="text-muted">(Pilih / Ketik)</small></label>
                                        @php
                                            $cleanDraftNoMobil = strtoupper(
                                                preg_replace('/[^A-Z0-9]/i', '', $draft->no_mobil ?? ''),
                                            );
                                            $draftSuffix = preg_replace('/^[A-Z]{1,2}/', '', $cleanDraftNoMobil);

                                            $matchVehicle = function ($noPol) use ($cleanDraftNoMobil, $draftSuffix) {
                                                if (empty($cleanDraftNoMobil) || empty($noPol)) {
                                                    return false;
                                                }
                                                $cleanV = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $noPol));
                                                if ($cleanV === $cleanDraftNoMobil) {
                                                    return true;
                                                }
                                                $vSuffix = preg_replace('/^[A-Z]{1,2}/', '', $cleanV);
                                                return !empty($draftSuffix) &&
                                                    !empty($vSuffix) &&
                                                    $draftSuffix === $vSuffix;
                                            };

                                            $matchedWfgTx = $wfgVehicles->first(
                                                fn($v) => $matchVehicle($v->vehicle->no_pol ?? ''),
                                            );
                                            $isDraftInWfg = !empty($matchedWfgTx);
                                            $driverNameVal = $draft->driver_name ?? ($matchedWfgTx->nama_driver ?? '');
                                        @endphp
                                        <select name="no_mobil" id="no_mobil" class="form-select select2-tags" required>
                                            <option value="" @selected(empty($draft->no_mobil))></option>
                                            @foreach ($wfgVehicles as $vTx)
                                                @php
                                                    $vNoPol = $vTx->vehicle->no_pol;
                                                    $vDriver = $vTx->nama_driver ?? '';
                                                    $vVendor = $vTx->vendor ?? '';
                                                    $vSpb = $vTx->no_spb ?? '';
                                                    $vArea =
                                                        $vTx->status === 'smu' ||
                                                        ($vTx->targetLocation &&
                                                            str_contains(strtoupper($vTx->targetLocation->name), 'SMU'))
                                                            ? 'SMU'
                                                            : 'WFG';
                                                    $isSelected = $matchVehicle($vNoPol);
                                                @endphp
                                                <option value="{{ $vNoPol }}" data-driver="{{ $vDriver }}"
                                                    data-vendor="{{ $vVendor }}" data-spb="{{ $vSpb }}"
                                                    data-area="{{ $vArea }}" @selected($isSelected)>
                                                    {{ $vNoPol }}{{ !empty($vDriver) ? ' - ' . $vDriver : '' }}{{ !empty($vVendor) ? ' [' . $vVendor . ']' : '' }}
                                                    ({{ $vArea }})
                                                </option>
                                            @endforeach
                                            @if (!empty($draft->no_mobil) && !$isDraftInWfg)
                                                <option value="{{ $draft->no_mobil }}"
                                                    data-driver="{{ $draft->driver_name ?? '' }}"
                                                    data-vendor="{{ $draft->supplier_vendor ?? '' }}"
                                                    data-spb="{{ $draft->no_spkb ?? '' }}" selected>
                                                    {{ $draft->no_mobil }}{{ !empty($draft->driver_name) ? ' - ' . $draft->driver_name : '' }}{{ !empty($draft->supplier_vendor) ? ' [' . $draft->supplier_vendor . ']' : '' }}
                                                </option>
                                            @endif
                                        </select>
                                        <input type="hidden" name="driver_name" id="driver_name"
                                            value="{{ $driverNameVal }}">
                                        <div id="driver-indicator"
                                            class="mt-1 small text-success {{ !empty($driverNameVal) ? '' : 'd-none' }}">
                                            <i class="ri-user-line me-1"></i> Sopir: <strong
                                                id="driver-indicator-name">{{ $driverNameVal }}</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Gate / Dock</label>
                                        <select name="gate" id="gate" class="form-select select2">
                                            <option value="">-- Pilih Gate --</option>
                                            @foreach (range(1, 30) as $g)
                                                @php
                                                    $isBooked =
                                                        in_array((int) $g, $bookedGates) &&
                                                        (!isset($draft) || (int) $draft->gate !== (int) $g);
                                                @endphp
                                                <option value="{{ $g }}" @selected(isset($draft) && (int) $draft->gate === (int) $g)
                                                    @disabled($isBooked)>
                                                    Gate {{ $g }}{{ $isBooked ? ' (Terpakai)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Jenis Pengiriman</label>
                                        <select name="jenis_pengiriman" class="form-select">
                                            <option value="Lokal" @selected(($draft->jenis_pengiriman ?? 'Lokal') === 'Lokal')>Lokal</option>
                                            <option value="Import" @selected(($draft->jenis_pengiriman ?? '') === 'Import')>Import</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Forklift Driver</label>
                                        <select name="forklift_driver_id" class="form-select select2">
                                            <option value="">-- Pilih Operator --</option>
                                            @foreach ($forkliftDrivers as $driver)
                                                <option value="{{ $driver->id }}" @selected(isset($draft) && $draft->forklift_driver_id == $driver->id)>
                                                    {{ $driver->username }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Jam Mulai Bongkar</label>
                                        <input type="text" name="jam_muat" id="jam"
                                            class="form-control bg-light" placeholder="Otomatis saat item pertama diisi"
                                            value="{{ $draft->jam_muat ?? '' }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">No. Kontainer <small
                                                class="text-muted">(Opsional)</small></label>
                                        <input type="text" name="no_kontainer" class="form-control"
                                            placeholder="No. Kontainer..." value="{{ $draft->no_kontainer ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabel Detail Item Bongkar (Per Pallet / Row) --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-0"><i class="ri-stack-line me-2"></i>Daftar Item / Pallet
                                        Bongkar</h5>
                                    <small class="text-muted">Setiap 1 baris mewakili 1 pallet/angkatan. Rekap jumlah &
                                        turus pallet per MID otomatis dihitung di bawah.</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-soft-primary btn-sm" onclick="addEmptyRow()">
                                        <i class="ri-add-line me-1"></i> Add Row
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="showManualModal()">
                                        <i class="ri-list-check-2 me-1"></i> Add List (Multi Pallet)
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                                    <table class="table table-hover align-middle" id="table-items">
                                        <thead class="table-light sticky-top" style="z-index: 1;">
                                            <tr>
                                                <th width="50" class="text-center">No</th>
                                                <th style="min-width: 260px;">MID / Nama Barang</th>
                                                <th width="140">Batch</th>
                                                <th width="90" class="text-center">Jenis</th>
                                                <th width="100" class="text-center">UoM</th>
                                                <th width="130" class="text-center">Jumlah (Qty)</th>
                                                <th>Keterangan</th>
                                                <th width="90" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div id="empty-state" class="text-center py-5">
                                    <i class="ri-inbox-unarchive-line fs-1 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">Belum ada item pallet yang ditambahkan. Klik
                                        <strong>Add Row</strong> atau <strong>Add List (Multi Pallet)</strong>.
                                    </p>
                                </div>

                                {{-- Live Rekap Per MID + Turus Pallet (Sesuai Kertas Form) --}}
                                <div class="mt-4 d-none" id="summary-section">
                                    <h6 class="fw-bold text-dark mb-2">
                                        <i class="ri-table-line me-1 text-primary"></i>
                                        Preview Rekapitulasi Bongkar (Per MID & Keterangan)
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm align-middle mb-2">
                                            <thead class="table-light text-center">
                                                <tr>
                                                    <th width="50">NO</th>
                                                    <th width="140">MID</th>
                                                    <th>NAMA BARANG</th>
                                                    <th width="90">UOM</th>
                                                    <th width="150">JUMLAH</th>
                                                    <th width="130">TOTAL PALLET</th>
                                                    <th>KETERANGAN</th>
                                                </tr>
                                            </thead>
                                            <tbody id="rekap-mid-tbody"></tbody>
                                            <tfoot class="table-light fw-bold">
                                                <tr>
                                                    <td colspan="4" class="text-end">Total (Jumlah Keseluruhan)</td>
                                                    <td class="text-center text-primary" id="rekap-grand-qty">0</td>
                                                    <td class="text-center" id="rekap-grand-pallet">0 Pallet</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bagian Pemeriksaan: Kelengkapan Dokumen, Kondisi Kendaraan, Kelengkapan Foto, Penyimpangan & Catatan --}}
                <div class="row g-3 mt-1">
                    {{-- 1. Kelengkapan Dokumen --}}
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0 fw-bold"><i
                                        class="ri-file-check-line me-1 text-primary"></i>Kelengkapan Dokumen</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">COA</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="inspection[doc_coa]"
                                                id="coa_ada" value="Ada" @checked(($insp['doc_coa'] ?? 'Ada') === 'Ada')>
                                            <label class="form-check-label" for="coa_ada">Ada</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="inspection[doc_coa]"
                                                id="coa_tidak" value="Tidak ada" @checked(($insp['doc_coa'] ?? '') === 'Tidak ada')>
                                            <label class="form-check-label" for="coa_tidak">Tidak ada</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Kartu Checkpoint</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="inspection[doc_kartu_checkpoint]" id="kc_ada" value="Ada"
                                                @checked(($insp['doc_kartu_checkpoint'] ?? 'Ada') === 'Ada')>
                                            <label class="form-check-label" for="kc_ada">Ada</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="inspection[doc_kartu_checkpoint]" id="kc_tidak" value="Tidak ada"
                                                @checked(($insp['doc_kartu_checkpoint'] ?? '') === 'Tidak ada')>
                                            <label class="form-check-label" for="kc_tidak">Tidak ada</label>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Catatan Penyimpangan</label>
                                    <textarea name="inspection[catatan_penyimpangan]" class="form-control" rows="2"
                                        placeholder="Mohon dicatat bila ditemukan penyimpangan...">{{ $insp['catatan_penyimpangan'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Kondisi Kendaraan --}}
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0 fw-bold"><i
                                        class="ri-shield-check-line me-1 text-success"></i>Kondisi Kendaraan</h6>
                            </div>
                            <div class="card-body">
                                @php
                                    $vehicleChecks = [
                                        'kondisi_lantai' => [
                                            'label' => 'Lantai Container',
                                            'opts' => ['Baik', 'Rusak'],
                                        ],
                                        'kondisi_dinding' => [
                                            'label' => 'Dinding Container Luar & Dalam',
                                            'opts' => ['Baik', 'Rusak'],
                                        ],
                                        'kondisi_sealed' => [
                                            'label' => 'Sealed Container',
                                            'opts' => ['Baik', 'Rusak'],
                                        ],
                                        'kondisi_pintu' => ['label' => 'Pintu Container', 'opts' => ['Baik', 'Rusak']],
                                        'kondisi_kebersihan' => [
                                            'label' => 'Kebersihan Kendaraan',
                                            'opts' => ['Bersih', 'Tidak Bersih'],
                                        ],
                                        'kondisi_bau_tajam' => ['label' => 'Berbau Tajam', 'opts' => ['Ada', 'Tidak']],
                                    ];
                                @endphp
                                @foreach ($vehicleChecks as $fieldKey => $cfg)
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <span class="small fw-semibold">{{ $cfg['label'] }}</span>
                                        <div class="d-flex gap-3">
                                            @foreach ($cfg['opts'] as $optIdx => $optVal)
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="radio"
                                                        name="inspection[{{ $fieldKey }}]"
                                                        id="{{ $fieldKey }}_{{ $optIdx }}"
                                                        value="{{ $optVal }}" @checked(($insp[$fieldKey] ?? $cfg['opts'][0]) === $optVal)>
                                                    <label class="form-check-label small"
                                                        for="{{ $fieldKey }}_{{ $optIdx }}">{{ $optVal }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- 3. Kelengkapan Foto* --}}
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0 fw-bold"><i
                                        class="ri-camera-check-line me-1 text-info"></i>Kelengkapan Foto*</h6>
                                <small class="text-muted">*Diisi oleh Pengawas</small>
                            </div>
                            <div class="card-body">
                                @php
                                    $photoChecks = [
                                        'foto_plat_nomor' => 'Foto Plat Nomor',
                                        'foto_no_container' => 'Foto No Container',
                                        'foto_sealed_container' => 'Foto Sealed Container',
                                        'foto_absorber_fumigan' => 'Foto Absorber / Fumigan',
                                        'foto_material_bermasalah' => 'Foto Material Bermasalah',
                                        'foto_container_berlubang' => 'Foto Container Berlubang',
                                    ];
                                @endphp
                                <div class="row g-2 mb-3">
                                    @foreach ($photoChecks as $pKey => $pLabel)
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    name="inspection[{{ $pKey }}]" id="chk_{{ $pKey }}"
                                                    value="1" @checked(!empty($insp[$pKey]))>
                                                <label class="form-check-label small"
                                                    for="chk_{{ $pKey }}">{{ $pLabel }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <hr>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Jumlah Absorber</label>
                                        <input type="text" name="inspection[jumlah_absorber]"
                                            class="form-control form-control-sm" placeholder="-"
                                            value="{{ $insp['jumlah_absorber'] ?? '-' }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Jumlah Fumigan</label>
                                        <input type="text" name="inspection[jumlah_fumigan]"
                                            class="form-control form-control-sm" placeholder="-"
                                            value="{{ $insp['jumlah_fumigan'] ?? '-' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Submit / Cancel --}}
                <div class="card shadow-sm border-0 mt-3">
                    <div
                        class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 bg-light p-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if (isset($draft))
                                <button type="button" class="btn btn-outline-danger px-4 shadow-sm" id="btnCancelDraft">
                                    <i class="ri-delete-bin-line me-1"></i> CANCEL DRAFT
                                </button>
                            @endif
                            <span class="autosave-status-bottom badge bg-light text-muted border px-3 py-2 fw-medium">
                                <i class="ri-checkbox-circle-line text-success me-1"></i> Draft tersimpan otomatis
                            </span>
                        </div>
                        <button type="submit" class="btn btn-success px-5 shadow-sm">
                            <i class="ri-save-line me-1"></i> SUBMIT FORM BONGKAR
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Add List (Multi Pallet) --}}
    <div class="modal fade" id="manualItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <h5 class="modal-title">Tambah Item / Pallet Bongkar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Search Material (MID / Nama Barang) <span
                                class="text-danger">*</span></label>
                        <select id="manual-material-select" class="form-select"></select>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Banyak Pallet / Row <span class="text-danger">*</span></label>
                            <input type="number" id="manual-row-count" class="form-control fw-bold text-primary"
                                min="1" max="100" value="1" required>
                            <small class="text-muted">Jumlah turus/pallet</small>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Jenis</label>
                            <select id="manual-jenis" class="form-select">
                                <option value="P">P (Full Pallet)</option>
                                <option value="R">R (Receh)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">UoM</label>
                            <input type="text" id="manual-uom" class="form-control" placeholder="LSN / BOX / PCS">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Qty per Pallet <span class="text-danger">*</span></label>
                            <input type="number" id="manual-qty" class="form-control" min="1" step="any">
                            <small class="text-muted" id="qty-hint">Otomatis dari Qty Master / isi manual</small>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Batch Number</label>
                            <input type="text" id="manual-batch" class="form-control" placeholder="Opsional...">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Keterangan Tambahan</label>
                        <input type="text" id="manual-keterangan" class="form-control" placeholder="Opsional...">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary px-4" onclick="addManualItem()">
                        <i class="ri-add-line me-1"></i> Tambahkan ke Tabel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let items = @json($draft->details ?? []);

        if (items.length > 0) {
            items = items.map(i => ({
                material_id: i.material_id,
                mid: i.material ? i.material.mid_barang : '',
                nama_barang: i.material ? i.material.nama_barang : '',
                batch: i.batch_number || '',
                jenis: i.jenis || 'P',
                uom: i.uom || (i.material ? (i.material.uom || 'BOX') : 'BOX'),
                qty: i.qty || 0,
                qty_box: i.material ? i.material.qty_box : 0,
                keterangan: i.keterangan || ''
            }));
        }

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function isRowComplete(item) {
            return item.material_id && parseFloat(item.qty) > 0;
        }

        function buildTallyHtml(count) {
            const groupsOfFive = Math.floor(count / 5);
            const remainder = count % 5;
            let parts = [];
            for (let g = 0; g < groupsOfFive; g++) {
                parts.push('<span class="tally-group tally-five">||||</span>');
            }
            if (remainder > 0) {
                parts.push('<span class="tally-group">' + '|'.repeat(remainder) + '</span>');
            }
            return parts.join(' ');
        }

        let currentSaveRequest = null;
        let saveTimeout = null;

        function setAutoSaveStatus(status) {
            const $badges = $('#autosave-status, .autosave-status-bottom');
            if (!$badges.length) return;

            if (status === 'saving') {
                $badges.removeClass('bg-light bg-success-subtle bg-danger-subtle text-muted text-success text-danger')
                       .addClass('bg-warning-subtle text-warning')
                       .html('<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan draft...');
            } else if (status === 'saved') {
                const now = new Date();
                const timeStr = String(now.getHours()).padStart(2, '0') + ':' +
                                String(now.getMinutes()).padStart(2, '0') + ':' +
                                String(now.getSeconds()).padStart(2, '0');
                $badges.removeClass('bg-light bg-warning-subtle bg-danger-subtle text-muted text-warning text-danger')
                       .addClass('bg-success-subtle text-success')
                       .html(`<i class="ri-checkbox-circle-line me-1"></i> Draft tersimpan (${timeStr})`);
            } else if (status === 'error') {
                $badges.removeClass('bg-light bg-warning-subtle bg-success-subtle text-muted text-warning text-success')
                       .addClass('bg-danger-subtle text-danger')
                       .html('<i class="ri-error-warning-line me-1"></i> Gagal simpan draft');
            }
        }

        function triggerAutoSave() {
            clearTimeout(saveTimeout);
            setAutoSaveStatus('saving');
            saveTimeout = setTimeout(() => saveProgress(), 600);
        }

        function saveProgress() {
            let formData = $('#form-bongkar-wfg').serializeArray();
            formData = formData.filter(item => !item.name.startsWith('details['));

            items.forEach((item, index) => {
                formData.push({
                    name: `details[${index}][material_id]`,
                    value: item.material_id || ''
                });
                formData.push({
                    name: `details[${index}][batch_number]`,
                    value: item.batch || ''
                });
                formData.push({
                    name: `details[${index}][jenis]`,
                    value: item.jenis || 'P'
                });
                formData.push({
                    name: `details[${index}][uom]`,
                    value: item.uom || ''
                });
                formData.push({
                    name: `details[${index}][qty]`,
                    value: item.qty || 0
                });
                formData.push({
                    name: `details[${index}][keterangan]`,
                    value: item.keterangan || ''
                });
            });

            if (currentSaveRequest) {
                currentSaveRequest.abort();
            }

            setAutoSaveStatus('saving');

            currentSaveRequest = $.post("{{ route('wfg.bongkar_muat.save_draft_bongkar') }}", formData, function(res) {
                if (res.status) {
                    $('#jam').val(res.jam_muat || '');
                    if (res.driver_name) {
                        $('#driver_name').val(res.driver_name);
                        $('#driver-indicator-name').text(res.driver_name);
                        $('#driver-indicator').removeClass('d-none');
                    }
                    if (res.supplier_vendor && !$('#supplier_vendor').val()) {
                        $('#supplier_vendor').val(res.supplier_vendor);
                    }
                    setAutoSaveStatus('saved');
                } else {
                    setAutoSaveStatus('error');
                }
            }).fail(function(xhr) {
                if (xhr.statusText === 'abort') return;
                setAutoSaveStatus('error');
            }).always(function() {
                currentSaveRequest = null;
            });
        }

        function renderTable() {
            let html = '';
            items.forEach((item, index) => {
                html += `
                    <tr>
                        <td class="text-center fw-semibold text-muted">${index + 1}</td>
                        <td>
                            <select class="form-select form-select-sm table-material-select" data-index="${index}"></select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm table-input"
                                value="${escapeHtml(item.batch)}"
                                onchange="updateItemField(${index}, 'batch', this.value)" placeholder="Batch...">
                        </td>
                        <td>
                            <select class="form-select form-select-sm fw-semibold text-center"
                                onchange="updateItemJenis(${index}, this.value)">
                                <option value="P" ${item.jenis === 'P' ? 'selected' : ''}>P</option>
                                <option value="R" ${item.jenis === 'R' ? 'selected' : ''}>R</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm table-input text-center text-uppercase"
                                value="${escapeHtml(item.uom)}"
                                onchange="updateItemField(${index}, 'uom', this.value.toUpperCase())" placeholder="LSN/BOX">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm table-input text-center fw-bold"
                                value="${item.qty}" step="any" min="0" ${item.jenis === 'P' ? 'readonly' : ''}
                                onchange="updateItemQty(${index}, this.value)">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm table-input"
                                value="${escapeHtml(item.keterangan)}"
                                onchange="updateItemField(${index}, 'keterangan', this.value)" placeholder="Catatan...">
                        </td>
                        <td class="text-center text-nowrap">
                            <button type="button" class="btn btn-soft-primary btn-sm me-1" onclick="duplicateItem(${index})" title="Duplikat Baris (Row Below)">
                                <i class="ri-file-copy-line"></i>
                            </button>
                            <button type="button" class="btn btn-soft-danger btn-sm" onclick="removeItem(${index})" title="Hapus Baris">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            $('#table-items tbody').html(html);
            setTimeout(() => initTableMaterialSelects(), 0);

            if (items.length > 0) {
                $('#empty-state').addClass('d-none');
            } else {
                $('#empty-state').removeClass('d-none');
            }

            // Render Rekap Per MID & Turus Pallet
            const completed = items.filter(isRowComplete);
            if (completed.length === 0) {
                $('#summary-section').addClass('d-none');
                return;
            }

            $('#summary-section').removeClass('d-none');
            let grouped = {};
            let grandQty = 0;
            let grandPallet = 0;

            completed.forEach(it => {
                const key = it.material_id;
                if (!grouped[key]) {
                    grouped[key] = {
                        mid: it.mid || '-',
                        nama: it.nama_barang || '-',
                        uom: it.uom || 'BOX',
                        qty: 0,
                        pallets: 0,
                        notes: []
                    };
                }
                const q = parseFloat(it.qty) || 0;
                grouped[key].qty += q;
                grouped[key].pallets += 1;
                if (it.keterangan && !grouped[key].notes.includes(it.keterangan)) {
                    grouped[key].notes.push(it.keterangan);
                }
                grandQty += q;
                grandPallet += 1;
            });

            let rekapHtml = '';
            Object.keys(grouped).forEach((k, idx) => {
                const g = grouped[k];
                const notesStr = g.notes.length ? escapeHtml(g.notes.join(', ')) : '-';
                rekapHtml += `
                    <tr>
                        <td class="text-center">${idx + 1}.</td>
                        <td class="text-center fw-bold">${escapeHtml(g.mid)}</td>
                        <td>${escapeHtml(g.nama)}</td>
                        <td class="text-center">${escapeHtml(g.uom)}</td>
                        <td class="text-center fw-bold">${g.qty.toLocaleString('id-ID')}</td>
                        <td class="text-center fw-bold">${g.pallets} Pallet</td>
                        <td>${notesStr}</td>
                    </tr>
                `;
            });

            $('#rekap-mid-tbody').html(rekapHtml);
            $('#rekap-grand-qty').text(grandQty.toLocaleString('id-ID'));
            $('#rekap-grand-pallet').text(grandPallet + ' Pallet');
        }

        function materialLabel(item) {
            if (!item.material_id) return '';
            const mid = item.mid || item.material_id;
            const nama = item.nama_barang || '';
            return nama ? `[${mid}] ${nama}` : mid;
        }

        function initTableMaterialSelects() {
            $('.table-material-select').each(function() {
                const select = $(this);
                const index = parseInt(select.data('index'));
                const item = items[index];

                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }

                select.select2({
                    placeholder: 'Cari MID / Nama Barang...',
                    width: '100%',
                    minimumInputLength: 2,
                    ajax: {
                        url: "{{ route('wfg.bongkar_muat.search_materials') }}",
                        dataType: 'json',
                        data: params => ({
                            q: params.term
                        }),
                        processResults: data => ({
                            results: data
                        })
                    }
                });

                if (item && item.material_id) {
                    const option = new Option(materialLabel(item), item.material_id, true, true);
                    select.append(option).trigger('change');
                }
            });
        }

        window.addEmptyRow = function() {
            items.push({
                material_id: null,
                mid: '',
                nama_barang: '',
                batch: '',
                jenis: 'P',
                uom: 'BOX',
                qty: 0,
                qty_box: 0,
                keterangan: ''
            });
            renderTable();
            saveProgress();
        };

        window.removeItem = function(index) {
            items.splice(index, 1);
            renderTable();
            saveProgress();
        };

        window.duplicateItem = function(index) {
            if (items[index]) {
                const source = items[index];
                const clone = {
                    material_id: source.material_id,
                    mid: source.mid,
                    nama_barang: source.nama_barang,
                    batch: source.batch,
                    jenis: source.jenis,
                    uom: source.uom,
                    qty: source.qty,
                    qty_box: source.qty_box,
                    keterangan: source.keterangan
                };
                items.splice(index + 1, 0, clone);
                renderTable();
                saveProgress();
            }
        };

        window.updateItemField = function(index, field, value) {
            if (items[index]) {
                items[index][field] = value;
                renderTable();
                saveProgress();
            }
        };

        window.updateItemJenis = function(index, value) {
            if (items[index]) {
                items[index].jenis = value;
                if (value === 'P' && items[index].qty_box > 0) {
                    items[index].qty = items[index].qty_box;
                }
                renderTable();
                saveProgress();
            }
        };

        window.updateItemQty = function(index, value) {
            if (items[index]) {
                let val = parseFloat(value) || 0;
                let maxQty = parseFloat(items[index].qty_box) || 0;
                if (items[index].jenis === 'R' && maxQty > 0 && val > maxQty) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Qty Melebihi Full Pallet!',
                        text: `Qty Receh (R) tidak boleh melebihi kapasitas Full Pallet (${maxQty})`
                    });
                    val = maxQty;
                }
                items[index].qty = val;
                renderTable();
                saveProgress();
            }
        };

        window.showManualModal = function() {
            $('#manual-material-select').val(null).trigger('change');
            $('#manual-row-count').val(1);
            $('#manual-jenis').val('P');
            $('#manual-uom').val('BOX');
            $('#manual-qty').val('').prop('readonly', true);
            $('#manual-batch').val('');
            $('#manual-keterangan').val('');
            new bootstrap.Modal('#manualItemModal').show();
        };

        window.addManualItem = function() {
            const materialData = $('#manual-material-select').select2('data')[0];
            const rowCount = Math.max(1, parseInt($('#manual-row-count').val()) || 1);
            const jenis = $('#manual-jenis').val();
            const uom = ($('#manual-uom').val() || 'BOX').toUpperCase();
            let qty = parseFloat($('#manual-qty').val()) || 0;
            const batch = $('#manual-batch').val();
            const keterangan = $('#manual-keterangan').val();

            if (!materialData) {
                Swal.fire('Perhatian', 'Silakan pilih material terlebih dahulu.', 'warning');
                return;
            }

            const qtyBox = parseFloat(materialData.qty_box) || 0;
            if (jenis === 'P' && qtyBox > 0) {
                qty = qtyBox;
            } else if (jenis === 'R' && qtyBox > 0 && qty > qtyBox) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Qty Melebihi Full Pallet!',
                    text: `Qty Receh (R) tidak boleh melebihi kapasitas Full Pallet (${qtyBox})`
                });
                qty = qtyBox;
            }

            if (qty <= 0) {
                Swal.fire('Perhatian', 'Silakan isi Qty per Pallet lebih dari 0.', 'warning');
                return;
            }

            for (let i = 0; i < rowCount; i++) {
                items.push({
                    material_id: materialData.id,
                    mid: materialData.mid || materialData.mid_barang || '',
                    nama_barang: materialData.nama || materialData.nama_barang || '',
                    batch: batch,
                    jenis: jenis,
                    uom: uom,
                    qty: qty,
                    qty_box: qtyBox,
                    keterangan: keterangan
                });
            }

            renderTable();
            saveProgress();
            bootstrap.Modal.getInstance('#manualItemModal').hide();
        };

        $(document).ready(function() {
            renderTable();

            $('.select2').select2({
                width: '100%'
            });

            $('#no_mobil').select2({
                width: '100%',
                tags: true,
                placeholder: '-- Pilih Kendaraan WFG / Ketik Manual --',
                allowClear: true
            }).on('select2:select select2:unselect change', function() {
                const val = $(this).val();
                if (!val) {
                    $('#driver_name').val('');
                    $('#driver-indicator-name').text('');
                    $('#driver-indicator').addClass('d-none');
                    return;
                }
                const selected = $(this).find(':selected');
                const driver = selected.data('driver');
                const vendor = selected.data('vendor');
                const spb = selected.data('spb');
                const checkin = selected.data('checkin');

                if (driver) {
                    $('#driver_name').val(driver);
                    $('#driver-indicator-name').text(driver);
                    $('#driver-indicator').removeClass('d-none');
                }
                if (vendor) {
                    $('#supplier_vendor').val(vendor);
                }
                if (spb && !$('#no_spkb').val()) {
                    $('#no_spkb').val(spb);
                }
                triggerAutoSave();
            });

            // Live synchronization status gate terpakai (Muat & Bongkar)
            $('#gate').on('change select2:select', function() {
                triggerAutoSave();
            });

            $('#gate').on('select2:opening', function() {
                $.get("{{ route('wfg.bongkar_muat.booked_gates') }}", {
                    draft_id: '{{ $draft->id }}'
                }, function(res) {
                    if (res.status && res.booked_gates) {
                        const currentGate = $('#gate').val();
                        $('#gate option').each(function() {
                            const val = parseInt($(this).val());
                            if (!val) return;
                            const isBooked = res.booked_gates.includes(val) && val !==
                                parseInt(currentGate);
                            $(this).prop('disabled', isBooked);
                            $(this).text(`Gate ${val}${isBooked ? ' (Terpakai)' : ''}`);
                        });
                    }
                });
            });

            $('#form-bongkar-wfg').on('change input', 'input, select, textarea', function() {
                if ($(this).closest('#table-items').length) return;
                triggerAutoSave();
            });

            $('#manual-material-select').select2({
                dropdownParent: $('#manualItemModal'),
                placeholder: 'Cari MID / Nama Barang...',
                width: '100%',
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('wfg.bongkar_muat.search_materials') }}",
                    dataType: 'json',
                    data: params => ({
                        q: params.term
                    }),
                    processResults: data => ({
                        results: data
                    })
                }
            }).on('select2:select', function(e) {
                const data = e.params.data;
                if (data.uom) {
                    $('#manual-uom').val(data.uom);
                }
                const cleanQty = data.qty_box ? parseFloat(data.qty_box) : '';
                if ($('#manual-jenis').val() === 'P') {
                    $('#manual-qty').val(cleanQty).prop('readonly', true);
                } else {
                    $('#manual-qty').val(cleanQty).prop('readonly', false);
                }
            });

            $('#manual-jenis').on('change', function() {
                let j = $(this).val();
                let selData = $('#manual-material-select').select2('data')[0];
                let maxQty = selData ? (parseFloat(selData.qty_box) || 0) : 0;
                if (j === 'P') {
                    if (maxQty > 0) $('#manual-qty').val(maxQty);
                    $('#manual-qty').prop('readonly', true);
                } else {
                    $('#manual-qty').prop('readonly', false);
                }
            });

            $('#manual-qty').on('input change', function() {
                let j = $('#manual-jenis').val();
                let selData = $('#manual-material-select').select2('data')[0];
                let maxQty = selData ? (parseFloat(selData.qty_box) || 0) : 0;
                let val = parseFloat($(this).val()) || 0;
                if (j === 'R' && maxQty > 0 && val > maxQty) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Qty Melebihi Full Pallet!',
                        text: `Qty Receh (R) tidak boleh melebihi kapasitas Full Pallet (${maxQty})`
                    });
                    $(this).val(maxQty);
                }
            });

            $(document).on('select2:select', '.table-material-select', function(e) {
                const index = $(this).data('index');
                const d = e.params.data;
                if (!items[index]) return;

                items[index].material_id = d.id;
                items[index].mid = d.mid || d.mid_barang || '';
                items[index].nama_barang = d.nama || d.nama_barang || '';
                items[index].uom = d.uom || items[index].uom || 'BOX';
                items[index].qty_box = parseFloat(d.qty_box) || 0;
                if (items[index].jenis === 'P') {
                    items[index].qty = items[index].qty_box;
                } else if (!items[index].qty || items[index].qty <= 0) {
                    items[index].qty = items[index].qty_box || 1;
                } else if (items[index].qty > items[index].qty_box && items[index].qty_box > 0) {
                    items[index].qty = items[index].qty_box;
                }

                renderTable();
                saveProgress();
            });

            $('#form-bongkar-wfg').on('submit', function(e) {
                e.preventDefault();
                const completedItems = items.filter(isRowComplete);

                if (completedItems.length === 0) {
                    Swal.fire('Item Kosong!', 'Tambahkan minimal 1 item/pallet sebelum submit.', 'warning');
                    return;
                }
                if (completedItems.length !== items.length) {
                    Swal.fire('Baris Belum Lengkap',
                        'Masih ada baris item yang belum dipilih materialnya atau Qty 0.', 'warning');
                    return;
                }

                let overQtyError = null;
                completedItems.forEach((it, idx) => {
                    let maxQty = parseFloat(it.qty_box) || 0;
                    let q = parseFloat(it.qty) || 0;
                    if (it.jenis === 'R' && maxQty > 0 && q > maxQty) {
                        overQtyError = `Baris #${idx + 1}: Qty Receh (${q}) tidak boleh melebihi Full Pallet (${maxQty})`;
                    }
                });
                if (overQtyError) {
                    Swal.fire({ icon: 'warning', title: 'Validasi Qty', text: overQtyError });
                    return;
                }

                let formDataArr = $(this).serializeArray();
                formDataArr = formDataArr.filter(item => !item.name.startsWith('details['));

                completedItems.forEach((item, index) => {
                    formDataArr.push({
                        name: `details[${index}][material_id]`,
                        value: item.material_id
                    });
                    formDataArr.push({
                        name: `details[${index}][batch_number]`,
                        value: item.batch || ''
                    });
                    formDataArr.push({
                        name: `details[${index}][jenis]`,
                        value: item.jenis || 'P'
                    });
                    formDataArr.push({
                        name: `details[${index}][uom]`,
                        value: item.uom || 'BOX'
                    });
                    formDataArr.push({
                        name: `details[${index}][qty]`,
                        value: item.qty
                    });
                    formDataArr.push({
                        name: `details[${index}][keterangan]`,
                        value: item.keterangan || ''
                    });
                });

                const formData = $.param(formDataArr);

                Swal.fire({
                    title: 'Submit Form Bongkar?',
                    text: 'Data pemeriksaan dan item bongkar akan disimpan dan masuk ke tahap tanda tangan Pengawas & Sopir.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Submit!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post("{{ route('wfg.bongkar_muat.store_bongkar') }}", formData, function(
                            res) {
                            if (res.status) {
                                Swal.fire('Berhasil', res.message, 'success').then(() => {
                                    window.location.href = res.redirect;
                                });
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        }).fail(xhr => {
                            const msg = xhr.responseJSON ? xhr.responseJSON.message :
                                'Internal Server Error';
                            Swal.fire('Error', msg, 'error');
                        });
                    }
                });
            });

            $('#btnCancelDraft').click(function() {
                Swal.fire({
                    title: 'Batalkan Draft Bongkar?',
                    text: 'Semua data yang sudah diisi pada draft ini akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus Draft!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post("{{ route('wfg.bongkar_muat.cancel_draft') }}", {
                            _token: "{{ csrf_token() }}",
                            id: "{{ $draft->id }}"
                        }, function(res) {
                            if (res.status) {
                                window.location.href =
                                    "{{ route('wfg.bongkar_muat.form_bongkar') }}";
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
