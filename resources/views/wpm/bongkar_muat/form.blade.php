@extends('layouts.app')

@section('title', '| Form Bongkar WPM')

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
                                <h4 class="mb-sm-0">Form Perintah & Pemeriksaan Bongkar (WPM)</h4>
                                <small class="text-muted">FRM/WPM/01/000/004-01 • Aktivitas: <span
                                        class="badge bg-warning text-dark">BONGKAR</span></small>
                            </div>
                            <span id="autosave-status" class="badge bg-light text-muted border px-3 py-2 fw-medium ms-md-2">
                                <i class="ri-checkbox-circle-line text-success me-1"></i> Draft tersimpan otomatis
                            </span>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">WPM</a></li>
                                <li class="breadcrumb-item active">Form Bongkar</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs for Active WPM Bongkar Drafts --}}
            <div class="row mb-3">
                <div class="col-12">
                    <ul class="nav nav-tabs nav-tabs-custom nav-warning" role="tablist">
                        @foreach ($allDrafts as $d)
                            @php
                                $isCurrent = $draft && $draft->id == $d->id;
                                $tabUrl =
                                    $d->status === 'draft'
                                        ? route('wpm.bongkar_muat.form', ['draft_id' => $d->id])
                                        : route('wpm.bongkar_muat.show', $d->id);

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
                                    <span>Draft WPM #{{ $d->id }}</span>
                                    @if ($d->no_mobil)
                                        <span class="badge bg-info-subtle text-info rounded-pill">{{ $d->no_mobil }}</span>
                                    @endif
                                    {!! $statusBadge !!}
                                </a>
                            </li>
                        @endforeach
                        <li class="nav-item ms-auto">
                            <a class="nav-link fw-medium bg-warning text-dark"
                                href="{{ route('wpm.bongkar_muat.form', ['create_new' => 1]) }}">
                                <i class="ri-add-line me-1"></i> Draft Bongkar Baru
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <form id="form-bongkar-wpm">
                @csrf
                <input type="hidden" name="id" value="{{ $draft->id }}">
                <input type="hidden" name="tipe" value="bongkar">

                <div class="row g-3">
                    {{-- Kolom Kiri: Informasi Dokumen & Pengiriman --}}
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-primary bg-gradient text-white">
                                <h5 class="card-title mb-0 text-white">
                                    <i class="ri-file-list-3-line me-2"></i>Informasi Dokumen & Pengiriman (WPM)
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
                                        <input type="text" name="sloc" class="form-control" placeholder="C001"
                                            value="{{ $draft->sloc ?? 'C001' }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Supplier / Vendor</label>
                                        <input type="text" name="supplier_vendor" id="supplier_vendor"
                                            class="form-control" placeholder="Nama Supplier / Vendor..."
                                            value="{{ $draft->supplier_vendor ?? '' }}">
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
                                    <div class="col-md-6">
                                        <label class="form-label text-primary fw-bold">
                                            <i class="ri-time-line me-1"></i> Jam Serah Dokumen ke QC <small class="text-muted fw-normal">(WIB / 24 Jam)</small>
                                        </label>
                                        <div class="input-group">
                                            <input type="text" name="jam_serah_terima_qc" id="jam_serah_terima_qc"
                                                class="form-control border-primary timepicker-24"
                                                placeholder="Contoh: 14:42"
                                                value="{{ !empty($draft->jam_serah_terima_qc) ? substr($draft->jam_serah_terima_qc, 0, 5) : '' }}">
                                            <span class="input-group-text bg-primary-subtle text-primary"><i class="ri-time-line"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-success fw-bold">
                                            <i class="ri-checkbox-circle-line me-1"></i> Jam Release / Unrelease QC <small class="text-muted fw-normal">(WIB / 24 Jam)</small>
                                        </label>
                                        <div class="input-group">
                                            <input type="text" name="jam_release_qc" id="jam_release_qc"
                                                class="form-control border-success timepicker-24"
                                                placeholder="Contoh: 15:30"
                                                value="{{ !empty($draft->jam_release_qc) ? substr($draft->jam_release_qc, 0, 5) : '' }}">
                                            <span class="input-group-text bg-success-subtle text-success"><i class="ri-checkbox-circle-line"></i></span>
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

                                            $matchedWpmTx = $wpmVehicles->first(
                                                fn($v) => $matchVehicle($v->vehicle->no_pol ?? ''),
                                            );
                                            $isDraftInWpm = !empty($matchedWpmTx);
                                            $driverNameVal = $draft->driver_name ?? ($matchedWpmTx->nama_driver ?? '');
                                        @endphp
                                        <select name="no_mobil" id="no_mobil" class="form-select select2-tags" required>
                                            <option value="" @selected(empty($draft->no_mobil))></option>
                                            @foreach ($wpmVehicles as $vTx)
                                                @php
                                                    $vNoPol = $vTx->vehicle->no_pol;
                                                    $vDriver = $vTx->nama_driver ?? '';
                                                    $vVendor = $vTx->vendor ?? '';
                                                    $vSpb = $vTx->no_spb ?? '';
                                                    $jamDoc = $vTx->queue_taken_time ? $vTx->queue_taken_time->format('H:i') : ($vTx->notif_qc_doc_at ? $vTx->notif_qc_doc_at->format('H:i') : '');
                                                    $jamRel = $vTx->finish_sampling_time ? $vTx->finish_sampling_time->format('H:i') : ($vTx->notif_qc_release_at ? $vTx->notif_qc_release_at->format('H:i') : '');
                                                    $isSelected = $matchVehicle($vNoPol);
                                                @endphp
                                                <option value="{{ $vNoPol }}" data-driver="{{ $vDriver }}"
                                                    data-vendor="{{ $vVendor }}" data-spb="{{ $vSpb }}"
                                                    data-jam-doc-qc="{{ $jamDoc }}"
                                                    data-jam-release-qc="{{ $jamRel }}"
                                                    @selected($isSelected)>
                                                    {{ $vNoPol }}{{ !empty($vDriver) ? ' - ' . $vDriver : '' }}{{ !empty($vVendor) ? ' [' . $vVendor . ']' : '' }}
                                                    (WPM)
                                                </option>
                                            @endforeach
                                            @if (!empty($draft->no_mobil) && !$isDraftInWpm)
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

                {{-- Tabel Detail Item Bongkar WPM (Per Pallet / Row) --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0">
                                    <i class="ri-list-check me-2"></i>Detail Item Bongkar WPM (Per Pallet)
                                </h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-list">
                                        <i class="ri-play-list-add-line me-1"></i> Add List (Multi Pallet)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary" id="btn-add-row">
                                        <i class="ri-add-line me-1"></i> Add 1 Row
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0" id="table-details">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 50px;">No</th>
                                                <th style="min-width: 200px;">MID / Nama Barang</th>
                                                <th style="width: 130px;">Batch / Lot</th>
                                                <th style="width: 80px;" class="text-center">Jenis</th>
                                                <th style="width: 80px;" class="text-center">UoM</th>
                                                <th style="width: 110px;" class="text-end">Jumlah</th>
                                                <th style="width: 180px;">Keterangan</th>
                                                <th class="text-center" style="width: 90px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $rowIndex = 0; @endphp
                                            @if ($draft && $draft->details && $draft->details->count() > 0)
                                                @foreach ($draft->details as $idx => $detail)
                                                    @php
                                                        $rowIndex++;
                                                        $mat = $detail->material;
                                                        $mid = $detail->mid ?? ($mat->mid ?? '');
                                                        $nama = $detail->nama_barang ?? ($mat->nama_barang ?? '');
                                                        $uom = $detail->uom ?? ($mat->uom ?? 'BOX');
                                                        $jenisVal = $detail->jenis ?? 'P';
                                                        $qtyPallet = floatval($mat->qty_pallet ?? ($mat->qty_box ?? 0));
                                                    @endphp
                                                    <tr data-index="{{ $rowIndex }}">
                                                        <td class="text-center fw-medium row-number">{{ $rowIndex }}
                                                        </td>
                                                        <td>
                                                            <input type="hidden"
                                                                name="details[{{ $rowIndex }}][material_id]"
                                                                class="detail-material-id"
                                                                value="{{ $detail->material_id }}">
                                                            <input type="hidden"
                                                                name="details[{{ $rowIndex }}][mid]"
                                                                class="detail-mid"
                                                                value="{{ $mid }}">
                                                            <input type="hidden"
                                                                name="details[{{ $rowIndex }}][nama_barang]"
                                                                class="detail-nama" value="{{ $nama }}">
                                                            <input type="hidden"
                                                                class="detail-qty-pallet"
                                                                value="{{ $qtyPallet }}">
                                                            <select class="form-select form-select-sm material-select"
                                                                style="width: 100%;">
                                                                @if ($detail->material_id || $mid)
                                                                    <option value="{{ $detail->material_id }}"
                                                                        data-mid="{{ $mid }}"
                                                                        data-nama="{{ $nama }}"
                                                                        data-uom="{{ $uom }}"
                                                                        data-qty-pallet="{{ $qtyPallet }}" selected>
                                                                        [{{ $mid }}] {{ $nama }}
                                                                    </option>
                                                                @endif
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                name="details[{{ $rowIndex }}][batch_number]"
                                                                class="form-control form-control-sm table-input text-center"
                                                                value="{{ $detail->batch_number ?? '' }}"
                                                                placeholder="Batch...">
                                                        </td>
                                                        <td>
                                                            <select name="details[{{ $rowIndex }}][jenis]"
                                                                class="form-select form-select-sm fw-bold text-center detail-jenis">
                                                                <option value="P" @selected($jenisVal === 'P')>P
                                                                </option>
                                                                <option value="R" @selected($jenisVal === 'R')>R
                                                                </option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                name="details[{{ $rowIndex }}][uom]"
                                                                class="form-control form-control-sm table-input detail-uom text-center"
                                                                value="{{ $uom }}" placeholder="BOX">
                                                        </td>
                                                        <td>
                                                            <input type="number" step="any"
                                                                name="details[{{ $rowIndex }}][qty]"
                                                                class="form-control form-control-sm table-input detail-qty text-end fw-bold"
                                                                value="{{ floatval($detail->qty ?? 0) > 0 ? (float) $detail->qty : '' }}"
                                                                placeholder="0"
                                                                {{ $jenisVal === 'P' ? 'readonly' : '' }} required>
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                name="details[{{ $rowIndex }}][keterangan]"
                                                                class="form-control form-control-sm table-input detail-keterangan"
                                                                value="{{ $detail->keterangan ?? '' }}"
                                                                placeholder="Keterangan...">
                                                        </td>
                                                        <td class="text-center text-nowrap">
                                                            <button type="button" class="btn btn-sm btn-soft-primary btn-duplicate-row me-1" title="Duplikat Baris (Row Below)">
                                                                <i class="ri-file-copy-line"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-soft-danger btn-delete-row" title="Hapus Baris">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr class="fw-bold">
                                                <td colspan="5" class="text-end">Total:</td>
                                                <td class="text-end" id="grand-total-qty">0</td>
                                                <td><span id="grand-total-pallet">0</span> Pallet</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Live Summary / Rekap per MID --}}
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-soft-secondary">
                                <h6 class="card-title mb-0">
                                    <i class="ri-bar-chart-grouped-line me-1"></i> Ringkasan Rekap Bongkar WPM
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0" id="table-summary">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 140px;">MID</th>
                                                <th>Nama Barang</th>
                                                <th style="width: 90px;" class="text-center">UoM</th>
                                                <th style="width: 120px;" class="text-end">Total Qty</th>
                                                <th style="width: 120px;" class="text-center">Total Pallet</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted small py-3">Belum ada
                                                    baris item</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pemeriksaan Kondisi, Foto, & Catatan Penyimpangan --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="ri-shield-check-line me-2"></i>Pemeriksaan Kondisi & Kelengkapan Dokumen /
                                    Foto
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    {{-- Kelengkapan Dokumen --}}
                                    <div class="col-md-4 border-end">
                                        <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">Kelengkapan Dokumen</h6>
                                        <div class="mb-3">
                                            <label class="form-label d-block">COA</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inspection[doc_coa]"
                                                    id="coa_ada" value="Ada" @checked(($insp['doc_coa'] ?? 'Ada') === 'Ada')>
                                                <label class="form-check-label" for="coa_ada">Ada</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inspection[doc_coa]"
                                                    id="coa_tidak" value="Tidak ada" @checked(($insp['doc_coa'] ?? '') === 'Tidak ada')>
                                                <label class="form-check-label" for="coa_tidak">Tidak ada</label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label d-block">Kartu Checkpoint</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio"
                                                    name="inspection[doc_kartu_checkpoint]" id="cp_ada" value="Ada"
                                                    @checked(($insp['doc_kartu_checkpoint'] ?? 'Ada') === 'Ada')>
                                                <label class="form-check-label" for="cp_ada">Ada</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio"
                                                    name="inspection[doc_kartu_checkpoint]" id="cp_tidak"
                                                    value="Tidak ada" @checked(($insp['doc_kartu_checkpoint'] ?? '') === 'Tidak ada')>
                                                <label class="form-check-label" for="cp_tidak">Tidak ada</label>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-3">* Diisi oleh Pengawas</small>
                                    </div>

                                    {{-- Kondisi Kendaraan --}}
                                    <div class="col-md-4 border-end">
                                        <h6 class="fw-bold mb-3 text-info border-bottom pb-2">Kondisi Kendaraan</h6>
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
                                                'kondisi_pintu' => [
                                                    'label' => 'Pintu Container',
                                                    'opts' => ['Baik', 'Rusak'],
                                                ],
                                                'kondisi_kebersihan' => [
                                                    'label' => 'Kebersihan Kendaraan',
                                                    'opts' => ['Bersih', 'Tidak Bersih'],
                                                ],
                                                'kondisi_bau_tajam' => [
                                                    'label' => 'Berbau Tajam',
                                                    'opts' => ['Ada', 'Tidak'],
                                                ],
                                            ];
                                        @endphp
                                        @foreach ($vehicleChecks as $fieldKey => $cfg)
                                            <div
                                                class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <span class="small fw-semibold">{{ $cfg['label'] }}</span>
                                                <div class="d-flex gap-3">
                                                    @foreach ($cfg['opts'] as $optIdx => $optVal)
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="radio"
                                                                name="inspection[{{ $fieldKey }}]"
                                                                id="wpm_{{ $fieldKey }}_{{ $optIdx }}"
                                                                value="{{ $optVal }}" @checked(($insp[$fieldKey] ?? ($fieldKey === 'kondisi_bau_tajam' ? 'Tidak' : $cfg['opts'][0])) === $optVal)>
                                                            <label class="form-check-label small"
                                                                for="wpm_{{ $fieldKey }}_{{ $optIdx }}">{{ $optVal }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Kelengkapan Foto --}}
                                    <div class="col-md-4">
                                        <h6 class="fw-bold mb-3 text-warning border-bottom pb-2">Kelengkapan Foto</h6>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="inspection[foto_plat_nomor]" id="f_plat" value="1"
                                                        @checked(!empty($insp['foto_plat_nomor']))>
                                                    <label class="form-check-label small" for="f_plat">Foto Plat
                                                        Nomor</label>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="inspection[foto_no_container]" id="f_kont"
                                                        value="1" @checked(!empty($insp['foto_no_container']))>
                                                    <label class="form-check-label small" for="f_kont">Foto No
                                                        Container</label>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="inspection[foto_sealed_container]" id="f_seal"
                                                        value="1" @checked(!empty($insp['foto_sealed_container']))>
                                                    <label class="form-check-label small" for="f_seal">Foto Sealed
                                                        Container</label>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="inspection[foto_absorber_fumigan]" id="f_abs"
                                                        value="1" @checked(!empty($insp['foto_absorber_fumigan']))>
                                                    <label class="form-check-label small" for="f_abs">Foto Absorber /
                                                        Fumigan</label>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="inspection[foto_material_bermasalah]" id="f_mat"
                                                        value="1" @checked(!empty($insp['foto_material_bermasalah']))>
                                                    <label class="form-check-label small" for="f_mat">Foto Material
                                                        Bermasalah</label>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="inspection[foto_container_berlubang]" id="f_lub"
                                                        value="1" @checked(!empty($insp['foto_container_berlubang']))>
                                                    <label class="form-check-label small" for="f_lub">Foto Container
                                                        Berlubang</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-2 mt-2">
                                            <div class="col-6">
                                                <label class="form-label small mb-1">Jumlah Absorber</label>
                                                <input type="text" name="inspection[jumlah_absorber]"
                                                    class="form-control form-control-sm"
                                                    value="{{ $insp['jumlah_absorber'] ?? '-' }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small mb-1">Jumlah Fumigan</label>
                                                <input type="text" name="inspection[jumlah_fumigan]"
                                                    class="form-control form-control-sm"
                                                    value="{{ $insp['jumlah_fumigan'] ?? '-' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Catatan Penyimpangan & Catatan Umum --}}
                                <div class="row g-3 mt-3 border-top pt-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-danger">Catatan Bila Ditemukan
                                            Penyimpangan</label>
                                        <textarea name="inspection[catatan_penyimpangan]" class="form-control" rows="2"
                                            placeholder="Catat penyimpangan jika ada...">{{ $insp['catatan_penyimpangan'] ?? '' }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Catatan Umum / Lain-lain</label>
                                        <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan tambahan...">{{ $draft->catatan ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="row mt-4 mb-5">
                    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                        <button type="button" class="btn btn-outline-danger" id="btn-cancel-draft">
                            <i class="ri-delete-bin-line me-1"></i> Batalkan Draft
                        </button>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="autosave-status-bottom badge bg-light text-muted border px-3 py-2 fw-medium">
                                <i class="ri-checkbox-circle-line text-success me-1"></i> Draft tersimpan otomatis
                            </span>
                            <button type="button" class="btn btn-soft-secondary" id="btn-save-draft">
                                <i class="ri-save-line me-1"></i> Simpan Manual
                            </button>
                            <button type="submit" class="btn btn-success px-4" id="btn-submit">
                                <i class="ri-check-line me-1"></i> Submit & Lanjut Approval
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Add List (Multi-Pallet) --}}
    <div class="modal fade" id="modalAddList" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add List Multi Pallet (WPM)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Material (WPM) <span class="text-danger">*</span></label>
                        <select id="modal-material-select" class="form-select" style="width: 100%;"></select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">MID</label>
                            <input type="text" id="modal-mid" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">UoM</label>
                            <input type="text" id="modal-uom" class="form-control bg-light" readonly>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label">Jumlah Pallet <span class="text-danger">*</span></label>
                            <input type="number" id="modal-pallet-count" class="form-control" min="1"
                                max="100" value="1">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Jenis <span class="text-danger">*</span></label>
                            <select id="modal-jenis" class="form-select">
                                <option value="P" selected>P (Full Pallet)</option>
                                <option value="R">R (Receh)</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Qty per Pallet <span class="text-danger">*</span></label>
                            <input type="number" step="any" id="modal-qty-pallet" class="form-control"
                                placeholder="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Batch / Lot (Opsional)</label>
                        <input type="text" id="modal-batch" class="form-control" placeholder="Batch number...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan (Opsional)</label>
                        <input type="text" id="modal-keterangan" class="form-control" placeholder="Keterangan...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-confirm-add-list">Tambahkan ke Tabel</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let rowCounter = {{ $rowIndex ?? 0 }};

            $('.select2').select2({
                placeholder: '-- Pilih --',
                allowClear: true
            });
            $('.select2-tags').select2({
                placeholder: '-- Pilih / Ketik --',
                allowClear: true,
                tags: true
            });

            function initMaterialSelect($element) {
                $element.select2({
                    placeholder: 'Cari Material WPM...',
                    allowClear: true,
                    ajax: {
                        url: '{{ route('wpm.bongkar_muat.search_materials') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.map(function(item) {
                                    return {
                                        id: item.id,
                                        text: item.text,
                                        mid: item.mid,
                                        nama: item.nama,
                                        uom: item.uom,
                                        qty_pallet: item.qty_pallet
                                    };
                                })
                            };
                        },
                        cache: true
                    }
                }).on('select2:select', function(e) {
                    let data = e.params.data;
                    let $row = $(this).closest('tr');
                    $row.find('.detail-material-id').val(data.id);
                    $row.find('.detail-mid').val(data.mid);
                    $row.find('.detail-nama').val(data.nama);
                    $row.find('.detail-uom').val(data.uom);
                    let cleanQty = (data.qty_pallet !== undefined && data.qty_pallet !== null && data.qty_pallet !== '') ? parseFloat(data.qty_pallet) : 0;
                    $row.find('.detail-qty-pallet').val(cleanQty);

                    let j = $row.find('.detail-jenis').val();
                    let $qtyInput = $row.find('.detail-qty');
                    if (j === 'P') {
                        $qtyInput.val(cleanQty > 0 ? cleanQty : '').prop('readonly', true);
                    } else {
                        $qtyInput.prop('readonly', false);
                        if (!$qtyInput.val() || $qtyInput.val() == '0') {
                            $qtyInput.val(cleanQty > 0 ? cleanQty : '');
                        }
                    }
                    setJamMuatIfEmpty();
                    recalculateSummary();
                    triggerAutoSave();
                });
            }

            $('.material-select').each(function() {
                initMaterialSelect($(this));
            });

            function setJamMuatIfEmpty() {
                let $jam = $('#jam');
                if (!$jam.val()) {
                    let now = new Date();
                    let hh = String(now.getHours()).padStart(2, '0');
                    let mm = String(now.getMinutes()).padStart(2, '0');
                    let ss = String(now.getSeconds()).padStart(2, '0');
                    $jam.val(hh + ':' + mm + ':' + ss);
                }
            }

            $('#btn-add-row').on('click', function() {
                rowCounter++;
                let newRow = `
                    <tr data-index="${rowCounter}">
                        <td class="text-center fw-medium row-number"></td>
                        <td>
                            <input type="hidden" name="details[${rowCounter}][material_id]" class="detail-material-id">
                            <input type="hidden" name="details[${rowCounter}][mid]" class="detail-mid">
                            <input type="hidden" name="details[${rowCounter}][nama_barang]" class="detail-nama">
                            <input type="hidden" class="detail-qty-pallet" value="0">
                            <select class="form-select form-select-sm material-select" style="width: 100%;"></select>
                        </td>
                        <td>
                            <input type="text" name="details[${rowCounter}][batch_number]" class="form-control form-control-sm table-input text-center" placeholder="Batch...">
                        </td>
                        <td>
                            <select name="details[${rowCounter}][jenis]" class="form-select form-select-sm fw-bold text-center detail-jenis">
                                <option value="P" selected>P</option>
                                <option value="R">R</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="details[${rowCounter}][uom]" class="form-control form-control-sm table-input detail-uom text-center" value="BOX" placeholder="BOX">
                        </td>
                        <td>
                            <input type="number" step="any" name="details[${rowCounter}][qty]" class="form-control form-control-sm table-input detail-qty text-end fw-bold" placeholder="0" readonly required>
                        </td>
                        <td>
                            <input type="text" name="details[${rowCounter}][keterangan]" class="form-control form-control-sm table-input detail-keterangan" placeholder="Keterangan...">
                        </td>
                        <td class="text-center text-nowrap">
                            <button type="button" class="btn btn-sm btn-soft-primary btn-duplicate-row me-1" title="Duplikat Baris (Row Below)"><i class="ri-file-copy-line"></i></button>
                            <button type="button" class="btn btn-sm btn-soft-danger btn-delete-row" title="Hapus Baris"><i class="ri-delete-bin-line"></i></button>
                        </td>
                    </tr>
                `;
                $('#table-details tbody').append(newRow);
                let $lastRow = $('#table-details tbody tr').last();
                initMaterialSelect($lastRow.find('.material-select'));
                updateRowNumbers();
                recalculateSummary();
                triggerAutoSave();
            });

            $(document).on('click', '.btn-duplicate-row', function() {
                let $currentRow = $(this).closest('tr');
                rowCounter++;

                let materialId = $currentRow.find('.detail-material-id').val() || '';
                let mid = $currentRow.find('.detail-mid').val() || '';
                let nama = $currentRow.find('.detail-nama').val() || '';
                let qtyPallet = $currentRow.find('.detail-qty-pallet').val() || 0;
                let batch = $currentRow.find('input[name*="[batch_number]"]').val() || '';
                let jenis = $currentRow.find('.detail-jenis').val() || 'P';
                let uom = $currentRow.find('.detail-uom').val() || 'BOX';
                let qty = $currentRow.find('.detail-qty').val() || '';
                let keterangan = $currentRow.find('.detail-keterangan').val() || '';

                let optionHtml = '';
                if (materialId || mid) {
                    optionHtml = `<option value="${materialId}" data-mid="${mid}" data-nama="${nama}" data-uom="${uom}" data-qty-pallet="${qtyPallet}" selected>[${mid}] ${nama}</option>`;
                }

                let newRow = `
                    <tr data-index="${rowCounter}">
                        <td class="text-center fw-medium row-number"></td>
                        <td>
                            <input type="hidden" name="details[${rowCounter}][material_id]" class="detail-material-id" value="${materialId}">
                            <input type="hidden" name="details[${rowCounter}][mid]" class="detail-mid" value="${mid}">
                            <input type="hidden" name="details[${rowCounter}][nama_barang]" class="detail-nama" value="${nama}">
                            <input type="hidden" class="detail-qty-pallet" value="${qtyPallet}">
                            <select class="form-select form-select-sm material-select" style="width: 100%;">
                                ${optionHtml}
                            </select>
                        </td>
                        <td>
                            <input type="text" name="details[${rowCounter}][batch_number]" class="form-control form-control-sm table-input text-center" value="${batch}" placeholder="Batch...">
                        </td>
                        <td>
                            <select name="details[${rowCounter}][jenis]" class="form-select form-select-sm fw-bold text-center detail-jenis">
                                <option value="P" ${jenis === 'P' ? 'selected' : ''}>P</option>
                                <option value="R" ${jenis === 'R' ? 'selected' : ''}>R</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="details[${rowCounter}][uom]" class="form-control form-control-sm table-input detail-uom text-center" value="${uom}" placeholder="BOX">
                        </td>
                        <td>
                            <input type="number" step="any" name="details[${rowCounter}][qty]" class="form-control form-control-sm table-input detail-qty text-end fw-bold" value="${qty}" ${jenis === 'P' ? 'readonly' : ''} placeholder="0" required>
                        </td>
                        <td>
                            <input type="text" name="details[${rowCounter}][keterangan]" class="form-control form-control-sm table-input detail-keterangan" value="${keterangan}" placeholder="Keterangan...">
                        </td>
                        <td class="text-center text-nowrap">
                            <button type="button" class="btn btn-sm btn-soft-primary btn-duplicate-row me-1" title="Duplikat Baris (Row Below)"><i class="ri-file-copy-line"></i></button>
                            <button type="button" class="btn btn-sm btn-soft-danger btn-delete-row" title="Hapus Baris"><i class="ri-delete-bin-line"></i></button>
                        </td>
                    </tr>
                `;

                $currentRow.after(newRow);
                let $newRowEl = $currentRow.next('tr');
                initMaterialSelect($newRowEl.find('.material-select'));
                updateRowNumbers();
                recalculateSummary();
                triggerAutoSave();
            });

            $(document).on('click', '.btn-delete-row', function() {
                $(this).closest('tr').remove();
                updateRowNumbers();
                recalculateSummary();
                triggerAutoSave();
            });

            function updateRowNumbers() {
                $('#table-details tbody tr').each(function(idx) {
                    $(this).find('.row-number').text(idx + 1);
                });
            }

            $(document).on('change', '.detail-jenis', function() {
                let $row = $(this).closest('tr');
                let j = $(this).val();
                let maxQty = parseFloat($row.find('.detail-qty-pallet').val()) || 0;
                let $qtyInput = $row.find('.detail-qty');

                if (j === 'P') {
                    if (maxQty > 0) {
                        $qtyInput.val(maxQty);
                    }
                    $qtyInput.prop('readonly', true);
                } else {
                    $qtyInput.prop('readonly', false);
                }
                recalculateSummary();
                triggerAutoSave();
            });

            $(document).on('input change', '.detail-qty', function() {
                let $row = $(this).closest('tr');
                let j = $row.find('.detail-jenis').val();
                let maxQty = parseFloat($row.find('.detail-qty-pallet').val()) || 0;
                let val = parseFloat($(this).val()) || 0;

                if (j === 'R' && maxQty > 0 && val > maxQty) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Qty Melebihi Full Pallet!',
                        text: `Qty Receh (R) tidak boleh melebihi kapasitas Full Pallet (${maxQty})`
                    });
                    $(this).val(maxQty);
                }
                setJamMuatIfEmpty();
                recalculateSummary();
                triggerAutoSave();
            });

            $(document).on('input change',
                '.detail-keterangan, .detail-mid, .detail-uom',
                function() {
                    setJamMuatIfEmpty();
                    recalculateSummary();
                    triggerAutoSave();
                });

            function recalculateSummary() {
                let grandTotalQty = 0;
                let grandTotalPallet = 0;
                let groups = {};

                $('#table-details tbody tr').each(function() {
                    let $r = $(this);
                    let mid = $r.find('.detail-mid').val() || '-';
                    let nama = $r.find('.detail-nama').val() || '-';
                    let uom = $r.find('.detail-uom').val() || 'BOX';
                    let qty = parseFloat($r.find('.detail-qty').val()) || 0;
                    let ket = $r.find('.detail-keterangan').val() || '';

                    if (mid !== '-') {
                        grandTotalQty += qty;
                        grandTotalPallet += 1;

                        if (!groups[mid]) {
                            groups[mid] = {
                                mid: mid,
                                nama: nama,
                                uom: uom,
                                totalQty: 0,
                                count: 0,
                                notes: []
                            };
                        }
                        groups[mid].totalQty += qty;
                        groups[mid].count += 1;
                        if (ket && !groups[mid].notes.includes(ket)) {
                            groups[mid].notes.push(ket);
                        }
                    }
                });

                $('#grand-total-qty').text(grandTotalQty.toLocaleString('id-ID'));
                $('#grand-total-pallet').text(grandTotalPallet);

                let $tbody = $('#table-summary tbody');
                $tbody.empty();

                let keys = Object.keys(groups);
                if (keys.length === 0) {
                    $tbody.html(
                        '<tr><td colspan="6" class="text-center text-muted small py-3">Belum ada baris item</td></tr>'
                        );
                    return;
                }

                keys.forEach(function(k) {
                    let g = groups[k];
                    let notesStr = g.notes.length > 0 ? g.notes.join(', ') : '-';
                    $tbody.append(`
                        <tr>
                            <td class="fw-bold text-center">${g.mid}</td>
                            <td>${g.nama}</td>
                            <td class="text-center">${g.uom}</td>
                            <td class="text-end fw-bold">${g.totalQty.toLocaleString('id-ID')}</td>
                            <td class="text-center fw-bold">${g.count} Pallet</td>
                            <td>${notesStr}</td>
                        </tr>
                    `);
                });
            }

            function renderTally(count) {
                let fives = Math.floor(count / 5);
                let rem = count % 5;
                let out = '';
                for (let i = 0; i < fives; i++) {
                    out += '<span class="tally-group tally-five">||||</span> ';
                }
                if (rem > 0) {
                    out += '<span class="tally-group">' + '|'.repeat(rem) + '</span>';
                }
                return out;
            }

            // Modal Multi Pallet
            $('#btn-add-list').on('click', function() {
                $('#modalAddList').modal('show');
            });

            $('#modal-material-select').select2({
                dropdownParent: $('#modalAddList'),
                placeholder: 'Cari Material WPM...',
                allowClear: true,
                ajax: {
                    url: '{{ route('wpm.bongkar_muat.search_materials') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.text,
                                    mid: item.mid,
                                    nama: item.nama,
                                    uom: item.uom,
                                    qty_pallet: item.qty_pallet
                                };
                            })
                        };
                    },
                    cache: true
                }
            }).on('select2:select', function(e) {
                let d = e.params.data;
                $('#modal-mid').val(d.mid);
                $('#modal-uom').val(d.uom);
                let cleanModalQty = (d.qty_pallet !== undefined && d.qty_pallet !== null && d.qty_pallet !== '') ? parseFloat(d.qty_pallet) : '';
                $('#modal-qty-pallet').val(!isNaN(cleanModalQty) && cleanModalQty > 0 ? cleanModalQty : '');
                if ($('#modal-jenis').val() === 'P') {
                    $('#modal-qty-pallet').prop('readonly', true);
                } else {
                    $('#modal-qty-pallet').prop('readonly', false);
                }
            });

            $('#modal-jenis').on('change', function() {
                let j = $(this).val();
                let selData = $('#modal-material-select').select2('data')[0];
                let maxQty = selData ? (parseFloat(selData.qty_pallet) || 0) : 0;
                if (j === 'P') {
                    if (maxQty > 0) $('#modal-qty-pallet').val(maxQty);
                    $('#modal-qty-pallet').prop('readonly', true);
                } else {
                    $('#modal-qty-pallet').prop('readonly', false);
                }
            });

            $('#modal-qty-pallet').on('input change', function() {
                let j = $('#modal-jenis').val();
                let selData = $('#modal-material-select').select2('data')[0];
                let maxQty = selData ? (parseFloat(selData.qty_pallet) || 0) : 0;
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

            $('#btn-confirm-add-list').on('click', function() {
                let selData = $('#modal-material-select').select2('data')[0];
                if (!selData) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih Material terlebih dahulu!'
                    });
                    return;
                }
                let palletCount = parseInt($('#modal-pallet-count').val()) || 1;
                let jenisVal = $('#modal-jenis').val() || 'P';
                let maxPalletQty = parseFloat(selData.qty_pallet) || 0;
                let qtyVal = parseFloat($('#modal-qty-pallet').val()) || (maxPalletQty > 0 ? maxPalletQty : 0);
                if (jenisVal === 'P' && maxPalletQty > 0) {
                    qtyVal = maxPalletQty;
                } else if (jenisVal === 'R' && maxPalletQty > 0 && qtyVal > maxPalletQty) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Qty Melebihi Full Pallet!',
                        text: `Qty Receh (R) tidak boleh melebihi kapasitas Full Pallet (${maxPalletQty})`
                    });
                    qtyVal = maxPalletQty;
                }
                let batchVal = $('#modal-batch').val() || '';
                let ketVal = $('#modal-keterangan').val() || '';

                for (let i = 0; i < palletCount; i++) {
                    rowCounter++;
                    let newRow = `
                        <tr data-index="${rowCounter}">
                            <td class="text-center fw-medium row-number"></td>
                            <td>
                                <input type="hidden" name="details[${rowCounter}][material_id]" class="detail-material-id" value="${selData.id}">
                                <input type="hidden" name="details[${rowCounter}][mid]" class="detail-mid" value="${selData.mid}">
                                <input type="hidden" name="details[${rowCounter}][nama_barang]" class="detail-nama" value="${selData.nama}">
                                <input type="hidden" class="detail-qty-pallet" value="${maxPalletQty}">
                                <select class="form-select form-select-sm material-select" style="width: 100%;">
                                    <option value="${selData.id}" selected>[${selData.mid}] ${selData.nama}</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="details[${rowCounter}][batch_number]" class="form-control form-control-sm table-input text-center" value="${batchVal}">
                            </td>
                            <td>
                                <select name="details[${rowCounter}][jenis]" class="form-select form-select-sm fw-bold text-center detail-jenis">
                                    <option value="P" ${jenisVal === 'P' ? 'selected' : ''}>P</option>
                                    <option value="R" ${jenisVal === 'R' ? 'selected' : ''}>R</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="details[${rowCounter}][uom]" class="form-control form-control-sm table-input detail-uom text-center" value="${selData.uom || 'BOX'}">
                            </td>
                            <td>
                                <input type="number" step="any" name="details[${rowCounter}][qty]" class="form-control form-control-sm table-input detail-qty text-end fw-bold" value="${qtyVal}" ${jenisVal === 'P' ? 'readonly' : ''} required>
                            </td>
                            <td>
                                <input type="text" name="details[${rowCounter}][keterangan]" class="form-control form-control-sm table-input detail-keterangan" value="${ketVal}">
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-sm btn-soft-primary btn-duplicate-row me-1" title="Duplikat Baris (Row Below)"><i class="ri-file-copy-line"></i></button>
                                <button type="button" class="btn btn-sm btn-soft-danger btn-delete-row" title="Hapus Baris"><i class="ri-delete-bin-line"></i></button>
                            </td>
                        </tr>
                    `;
                    $('#table-details tbody').append(newRow);
                    let $r = $('#table-details tbody tr').last();
                    initMaterialSelect($r.find('.material-select'));
                }

                setJamMuatIfEmpty();
                updateRowNumbers();
                recalculateSummary();
                triggerAutoSave();
                $('#modalAddList').modal('hide');
            });

            // Init 24-hour timepicker for QC (Format 24 Jam WIB tanpa AM/PM)
            if (typeof flatpickr !== 'undefined') {
                $('.timepicker-24').flatpickr({
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    allowInput: true,
                    minuteIncrement: 1,
                    onClose: function(selectedDates, dateStr, instance) {
                        $(instance.element).trigger('change');
                    }
                });
            }

            // Normalisasi input manual jam (misal diketik 14.42 -> 14:42, 9:5 -> 09:05, 1442 -> 14:42)
            $(document).on('blur', '.timepicker-24', function() {
                let val = $(this).val().trim();
                if (val) {
                    val = val.replace('.', ':');
                    if (/^\d{4}$/.test(val)) {
                        val = val.substring(0, 2) + ':' + val.substring(2);
                    } else if (/^\d{1,2}:\d{1,2}$/.test(val)) {
                        let parts = val.split(':');
                        val = String(parts[0]).padStart(2, '0') + ':' + String(parts[1]).padStart(2, '0');
                    }
                    $(this).val(val);
                    if (this._flatpickr) {
                        this._flatpickr.setDate(val, false);
                    }
                    triggerAutoSave();
                }
            });

            // Vehicle on change
            $('#no_mobil').on('change select2:select', function() {
                let $opt = $(this).find('option:selected');
                let driver = $opt.data('driver') || '';
                let vendor = $opt.data('vendor') || '';
                let spb = $opt.data('spb') || '';
                let jamDocQc = $opt.data('jam-doc-qc') || '';
                let jamReleaseQc = $opt.data('jam-release-qc') || '';

                if (driver) {
                    $('#driver_name').val(driver);
                    $('#driver-indicator-name').text(driver);
                    $('#driver-indicator').removeClass('d-none');
                }
                if (vendor && !$('#supplier_vendor').val()) {
                    $('#supplier_vendor').val(vendor);
                }
                if (spb && !$('#no_spkb').val()) {
                    $('#no_spkb').val(spb);
                }
                if (jamDocQc) {
                    $('#jam_serah_terima_qc').val(jamDocQc);
                    if ($('#jam_serah_terima_qc')[0]?._flatpickr) {
                        $('#jam_serah_terima_qc')[0]._flatpickr.setDate(jamDocQc, false);
                    }
                }
                if (jamReleaseQc) {
                    $('#jam_release_qc').val(jamReleaseQc);
                    if ($('#jam_release_qc')[0]?._flatpickr) {
                        $('#jam_release_qc')[0]._flatpickr.setDate(jamReleaseQc, false);
                    }
                }
                triggerAutoSave();
            });

            // Gate & select change
            $('#gate').on('change select2:select', function() {
                triggerAutoSave();
            });

            // Global change listener for header inputs, textareas, and radios/checkboxes
            $('#form-bongkar-wpm').on('input change', 'input, select, textarea', function(e) {
                // If it's a detail row input, it's already handled with detail delegation
                if ($(this).closest('#table-details').length) return;
                triggerAutoSave();
            });

            // Auto Save Draft Progress & Indicator
            let autoSaveTimer = null;
            let currentAutoSaveRequest = null;

            function setAutoSaveStatus(status) {
                const $badges = $('#autosave-status, .autosave-status-bottom');
                if (!$badges.length) return;

                if (status === 'saving') {
                    $badges.removeClass(
                            'bg-light bg-success-subtle bg-danger-subtle text-muted text-success text-danger')
                        .addClass('bg-warning-subtle text-warning')
                        .html('<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan draft...');
                } else if (status === 'saved') {
                    const now = new Date();
                    const timeStr = String(now.getHours()).padStart(2, '0') + ':' +
                        String(now.getMinutes()).padStart(2, '0') + ':' +
                        String(now.getSeconds()).padStart(2, '0');
                    $badges.removeClass(
                            'bg-light bg-warning-subtle bg-danger-subtle text-muted text-warning text-danger')
                        .addClass('bg-success-subtle text-success')
                        .html(`<i class="ri-checkbox-circle-line me-1"></i> Draft tersimpan (${timeStr})`);
                } else if (status === 'error') {
                    $badges.removeClass(
                            'bg-light bg-warning-subtle bg-success-subtle text-muted text-warning text-success')
                        .addClass('bg-danger-subtle text-danger')
                        .html('<i class="ri-error-warning-line me-1"></i> Gagal simpan draft');
                }
            }

            function triggerAutoSave() {
                clearTimeout(autoSaveTimer);
                setAutoSaveStatus('saving');
                autoSaveTimer = setTimeout(function() {
                    autoSaveDraft(false);
                }, 600);
            }

            function autoSaveDraft(isManual = false) {
                let formData = $('#form-bongkar-wpm').serialize();

                if (currentAutoSaveRequest) {
                    currentAutoSaveRequest.abort();
                }

                if (isManual) {
                    Swal.showLoading();
                } else {
                    setAutoSaveStatus('saving');
                }

                currentAutoSaveRequest = $.ajax({
                    url: '{{ route('wpm.bongkar_muat.save_draft') }}',
                    type: 'POST',
                    data: formData,
                    success: function(res) {
                        if (res.status) {
                            if (res.jam_muat) {
                                $('#jam').val(res.jam_muat);
                            }
                            if (res.driver_name) {
                                $('#driver_name').val(res.driver_name);
                                $('#driver-indicator-name').text(res.driver_name);
                                $('#driver-indicator').removeClass('d-none');
                            }
                            if (res.supplier_vendor && !$('#supplier_vendor').val()) {
                                $('#supplier_vendor').val(res.supplier_vendor);
                            }
                            setAutoSaveStatus('saved');
                            if (isManual) {
                                Swal.close();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        } else {
                            setAutoSaveStatus('error');
                            if (isManual) {
                                Swal.close();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.message
                                });
                            }
                        }
                    },
                    error: function(xhr) {
                        if (xhr.statusText === 'abort') return;
                        setAutoSaveStatus('error');
                        if (isManual) {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem'
                            });
                        }
                    },
                    complete: function() {
                        currentAutoSaveRequest = null;
                    }
                });
            }

            $('#btn-save-draft').on('click', function() {
                autoSaveDraft(true);
            });

            // Submit Form
            $('#form-bongkar-wpm').on('submit', function(e) {
                e.preventDefault();
                let rowCount = $('#table-details tbody tr').length;
                if (rowCount === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tabel Barang Kosong',
                        text: 'Tambahkan minimal 1 baris barang sebelum submit!'
                    });
                    return;
                }

                let overQtyError = null;
                $('#table-details tbody tr').each(function(idx) {
                    let j = $(this).find('.detail-jenis').val();
                    let qty = parseFloat($(this).find('.detail-qty').val()) || 0;
                    let maxQty = parseFloat($(this).find('.detail-qty-pallet').val()) || 0;
                    if (j === 'R' && maxQty > 0 && qty > maxQty) {
                        overQtyError = `Baris #${idx + 1}: Qty Receh (${qty}) tidak boleh melebihi Full Pallet (${maxQty})`;
                        return false;
                    }
                });
                if (overQtyError) {
                    Swal.fire({ icon: 'warning', title: 'Validasi Qty', text: overQtyError });
                    return;
                }

                let formData = $(this).serialize();
                $.ajax({
                    url: '{{ route('wpm.bongkar_muat.store') }}',
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        Swal.showLoading();
                    },
                    success: function(res) {
                        Swal.close();
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Bongkar WPM Disubmit!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = res.redirect;
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.message
                            });
                        }
                    },
                    error: function(err) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: err.responseJSON?.message ||
                                'Terjadi kesalahan sistem'
                        });
                    }
                });
            });

            // Cancel Draft
            $('#btn-cancel-draft').on('click', function() {
                Swal.fire({
                    title: 'Batalkan Draft WPM ini?',
                    text: 'Data draft akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Batalkan!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('wpm.bongkar_muat.cancel_draft') }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: $('input[name="id"]').val()
                            },
                            success: function(res) {
                                if (res.status) {
                                    window.location.href =
                                        '{{ route('wpm.bongkar_muat.index') }}';
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: res.message
                                    });
                                }
                            }
                        });
                    }
                });
            });

            // Initial calculation
            updateRowNumbers();
            recalculateSummary();

            // Auto fill QC time on initial load if empty and vehicle option has it
            let $selectedMobilOpt = $('#no_mobil').find('option:selected');
            if ($selectedMobilOpt.length) {
                let initJamDoc = $selectedMobilOpt.data('jam-doc-qc') || '';
                let initJamRel = $selectedMobilOpt.data('jam-release-qc') || '';
                if (initJamDoc && !$('#jam_serah_terima_qc').val()) {
                    $('#jam_serah_terima_qc').val(initJamDoc);
                    if ($('#jam_serah_terima_qc')[0]?._flatpickr) {
                        $('#jam_serah_terima_qc')[0]._flatpickr.setDate(initJamDoc, false);
                    }
                }
                if (initJamRel && !$('#jam_release_qc').val()) {
                    $('#jam_release_qc').val(initJamRel);
                    if ($('#jam_release_qc')[0]?._flatpickr) {
                        $('#jam_release_qc')[0]._flatpickr.setDate(initJamRel, false);
                    }
                }
            }
        });
    </script>
@endsection
