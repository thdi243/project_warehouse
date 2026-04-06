@extends('layouts.app')

@section('styles')
<style>
    .clickable {
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .clickable:hover {
        transform: scale(1.03);
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.15);
    }

    .forklift-card:hover {
        background-color: #ffe5e5;
        border: 1px solid #dc3545;
    }

    .pallet-card:hover {
        background-color: #e0f0ff;
        border: 1px solid #0d6efd;
    }

    .radio-label {
        padding: 5px 10px;
        border-radius: 6px;
        border: 1px solid transparent;
        display: inline-block;
    }

    .radio-label.ok-selected {
        background-color: #d1f7d6;
        color: #0f5132;
        border-color: #198754;
    }

    .radio-label.nok-selected {
        background-color: #f8d7da;
        color: #842029;
        border-color: #dc3545;
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- Header -->
        <div class="row">
            <div class="col-xxl-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="row align-items-end">
                            <div class="col-sm-10">
                                <div class="p-3">
                                    <h1>Data P2H Online</h1>
                                    <p class="fs-16 lh-base">Periksa Forklift Anda dengan Teliti</p>
                                </div>
                            </div>
                            <div class="col-sm-2 text-end">
                                <img src="{{ asset('assets/images/gudang.png') }}" class="img-fluid" alt=""
                                    style="max-height: 100px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Unit -->
        <div class="row">
            <div class="col-md-6">
                <div class="card clickable card-p2h forklift-card" data-unit="Forklift">
                    <div class="card-body text-center">
                        <h4 class="card-title">Forklift</h4>
                        <img src="{{ asset('assets/images/forklift.svg') }}" alt="gambar" height="150"
                            style="border-radius: 20px;">
                        <p class="text-muted">Klik untuk pemeriksaan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card clickable card-p2h pallet-card" data-unit="Pallet Mover">
                    <div class="card-body text-center">
                        <h4 class="card-title">Pallet Mover</h4>
                        <img src="{{ asset('assets/images/pallet_mover.svg') }}" alt="gambar" height="150"
                            style="border-radius: 20px;">
                        <p class="text-muted">Klik untuk pemeriksaan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data P2H -->
        <div id="table-container" style="display: none;">
            <!-- Filter Controls -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari Nomor Unit ">
                </div>
                <div class="col-md-4">
                    <input type="date" id="filterDate" class="form-control">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary w-100" id="resetFilter">Reset</button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 id="table-title" class="mb-3">Data P2H</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="p2hTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nomor Unit</th>
                                    <th>Jenis P2H</th>
                                    <th>Section</th>
                                    <th>Shift Tersedia</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="p2hTableBody">
                                <!-- Data akan dimasukkan di sini oleh JavaScript -->
                            </tbody>
                        </table>
                        <div id="pagination" class="mt-3 d-flex justify-content-center"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Detail Shift -->
<div class="modal fade" id="modalDetailP2H" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title" id="detailModalLabel">Detail Pemeriksaan Shift</h5><br>


                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="modalDetailBody">
                <!-- Konten detail akan diisi via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="downloadPDF">Download PDF</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="editP2HModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="editP2HForm">
                @csrf
                <input type="hidden" name="tanggal" id="editTanggal">
                <input type="hidden" name="nomor_unit" id="editNomorUnit">
                <input type="hidden" name="jenis_p2h" id="editJenisP2H">

                <div class="modal-header">
                    <h5 class="modal-title">Edit P2H - <span id="editUnitTitle"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" id="editTanggalDisplay"
                                readonly>
                        </div>
                        <div class="col-md-4">
                            <label>Nomor Unit</label>
                            <input type="text" class="form-control" name="nomor_unit" id="editNomorUnitDisplay"
                                readonly>
                        </div>
                        <div class="col-md-4">
                            <label>Jenis</label>
                            <input type="text" class="form-control" name="janis_p2h" id="editJenisDisplay"
                                readonly>
                        </div>
                    </div>

                    <!-- Tab Dinamis -->
                    <ul class="nav nav-tabs" id="shiftTabs" role="tablist"></ul>

                    <div class="tab-content pt-3" id="shiftTabContent">
                        <!-- Content akan di-append di sini -->
                    </div>

                    <!-- Tombol Tambah Shift Baru -->
                    {{-- <div class="mt-3 text-center">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addNewShiftBtn">
                                <i class="mdi mdi-plus"></i> Tambah Shift Baru
                            </button>
                        </div> --}}
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan Semua Shift</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- <script>
        window.authJabatan = '{{ Auth::user()->jabatan }}';
</script> --}}
<script>
    $(document).ready(function() {
        let currentData = [];
        let palletData = [];
        let filteredData = [];
        const rowsPerPage = 10;
        let currentPage = 1;
        let currentUnitType = '';

        // Fungsi render tabel dengan pagination
        function renderTable(data, page = 1) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const paginatedData = data.slice(start, end);
            console.log(paginatedData);

            $('#p2hTableBody').empty();

            paginatedData.forEach((item, index) => {
                const shiftKeys = Object.keys(item.shifts || {}).sort().join(', ') || '-';

                // Buat unique key dari data item
                const uniqueKey = `${item.tanggal}|${item.nomor_unit}|${item.jenis_p2h}`;
                const globalIndex = start + index;
                let editButton = '';
                @can('permission', 'p2h-form-plus')
                editButton = `
                            <button 
                                class="btn btn-sm btn-warning me-2 btn-edit" 
                                data-key="${uniqueKey}"
                                title="Edit">
                                <i class="mdi mdi-pencil"></i> Edit
                            </button>
                        `;
                @endcan

                const detailButton = `
                       <button 
                            class="btn btn-sm btn-primary btn-detail" 
                            data-index="${globalIndex}"
                            title="Detail">
                            <i class="mdi mdi-eye"></i> Detail
                        </button>
                    `;

                $('#p2hTableBody').append(`
                        <tr>
                            <td>${item.tanggal}</td>
                            <td>${item.nomor_unit}</td>
                            <td>${item.jenis_p2h}</td>
                            <td>${item.section}</td>
                            <td>${shiftKeys}</td>
                            <td class="text-center">
                                ${editButton}
                                ${detailButton}
                            </td>
                        </tr>
                    `);
            });

            renderPagination(data.length, page);
        }

        // Fungsi render tombol pagination
        function renderPagination(totalItems, currentPage) {
            const totalPages = Math.ceil(totalItems / rowsPerPage);
            if (totalPages <= 1) {
                $('#pagination').empty();
                return;
            }

            let html = '';

            // Tombol Previous
            html += `
                    <button class="btn btn-sm ${currentPage === 1 ? 'btn-secondary disabled' : 'btn-outline-primary'} mx-1 prev-btn" 
                            ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">
                        Prev
                    </button>
                `;

            const maxVisible = 7; // Ubah angka ini kalau mau lebih/sedikit tombol angka yang keliatan
            let startPage, endPage;

            if (totalPages <= maxVisible) {
                // Kalau total halaman sedikit, tampilkan semua
                startPage = 1;
                endPage = totalPages;
            } else {
                // Hitung range di sekitar current page
                const half = Math.floor(maxVisible / 2);
                if (currentPage <= half + 1) {
                    startPage = 1;
                    endPage = maxVisible - 1; // sisakan tempat untuk last page
                } else if (currentPage >= totalPages - half) {
                    startPage = totalPages - maxVisible + 2; // sisakan tempat untuk page 1
                    endPage = totalPages;
                } else {
                    startPage = currentPage - half + 1;
                    endPage = currentPage + half - 1;
                }
            }

            // Page 1
            if (startPage > 1) {
                html += `
                        <button class="btn btn-sm ${1 === currentPage ? 'btn-primary' : 'btn-outline-primary'} mx-1 page-btn" data-page="1">
                            1
                        </button>
                    `;
                if (startPage > 2) {
                    html += `<span class="mx-1 align-middle">...</span>`;
                }
            }

            // Halaman tengah
            for (let i = startPage; i <= endPage; i++) {
                html += `
                        <button class="btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline-primary'} mx-1 page-btn" data-page="${i}">
                            ${i}
                        </button>
                    `;
            }

            // Last page + ellipsis
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<span class="mx-1 align-middle">...</span>`;
                }
                html += `
                        <button class="btn btn-sm ${totalPages === currentPage ? 'btn-primary' : 'btn-outline-primary'} mx-1 page-btn" data-page="${totalPages}">
                            ${totalPages}
                        </button>
                    `;
            }

            // Tombol Next
            html += `
                    <button class="btn btn-sm ${currentPage === totalPages ? 'btn-secondary disabled' : 'btn-outline-primary'} mx-1 next-btn" 
                            ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}">
                        Next
                    </button>
                `;

            $('#pagination').html(html);
        }

        $(document).on('click', '.page-btn', function() {
            const page = parseInt($(this).data('page'));
            renderTable(filteredData || getSourceData(), page); // sesuaikan getSourceData()
        });

        $(document).on('click', '.prev-btn:not(.disabled)', function() {
            const page = parseInt($(this).data('page'));
            renderTable(filteredData || getSourceData(), page);
        });

        $(document).on('click', '.next-btn:not(.disabled)', function() {
            const page = parseInt($(this).data('page'));
            renderTable(filteredData || getSourceData(), page);
        });

        // Fungsi filter berdasarkan keyword dan tanggal
        function applyFilter() {
            const keyword = $('#searchInput').val().toLowerCase();
            const selectedDate = $('#filterDate').val();
            const sourceData = currentData || []; // selalu pakai currentData

            filteredData = sourceData.filter(item => {
                const unit = (item.nomor_unit || '').toLowerCase();
                const jenis = (item.jenis_p2h || '').toLowerCase();
                const tanggal = item.tanggal || '';

                const matchKeyword = unit.includes(keyword) || jenis.includes(keyword);
                const matchDate = !selectedDate || tanggal === selectedDate;

                return matchKeyword && matchDate;
            });

            currentPage = 1;
            renderTable(filteredData, currentPage);
        }

        // Event listener filter dan reset
        $('#searchInput').on('input', applyFilter);
        $('#filterDate').on('change', applyFilter);
        $('#resetFilter').on('click', function() {
            $('#searchInput').val('');
            $('#filterDate').val('');
            applyFilter();
        });

        // Event listener klik unit
        $('.card-p2h').on('click', function() {
            const unit = $(this).data('unit');
            currentUnitType = unit;

            const isPallet = unit === 'Pallet Mover';

            const fetchUrl = isPallet ?
                "{{ url('api/p2h/data/pallet-mover') }}" :
                "{{ url('api/p2h/data/forklift-data') }}";

            $.ajax({
                url: fetchUrl,
                method: "GET",
                success: function(response) {
                    // PERBAIKAN UTAMA: currentData selalu diisi dengan response
                    currentData = response;
                    filteredData = response;

                    $('#table-title').text(`Data P2H - ${unit}`);
                    $('#table-container').slideDown();
                    $('#searchInput').val('');
                    $('#filterDate').val('');
                    currentPage = 1;
                    renderTable(filteredData, currentPage);
                },
                error: function() {
                    Swal.fire('Gagal', 'Gagal mengambil data P2H.', 'error');
                }
            });
        });

        // Event listener tombol Detail
        $(document).on('click', '.btn-detail', function() {
            const index = $(this).data('index');
            const type = $(this).data('type');

            const displayedData = filteredData || ($('#table-title').text().includes('Pallet') ?
                palletData : currentData);
            const item = displayedData[index];

            if (!item) {
                alert('Data tidak ditemukan!');
                return;
            }

            let html = '';

            Object.entries(item.shifts).forEach(([shift, detail]) => {
                const time = new Date(detail.created_at).toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                });

                html += `
                        <div class="mb-4">
                            <h5 class="mb-2">Shift ${shift}</h5>
                            <p>
                                <i class="bi bi-person-circle me-1"></i><strong>Operator:</strong> ${detail.operator_name || '-'}
                                <i class="bi bi-clock ms-3 me-1"></i><strong>Jam Input:</strong> ${time} WIB
                            </p>
                            <div class="row">
                    `;

                for (const [key, value] of Object.entries(detail)) {
                    if (['id', 'created_at', 'updated_at', 'jenis_p2h', 'operator_name',
                            'p2h_model_id', 'shift'
                        ].includes(key)) continue;

                    let label = key === 'jam_operasional' ? 'Hours Meter' : key.replace(/_/g,
                        ' ').replace(/\b\w/g, l => l.toUpperCase());
                    let badge = '';

                    if (key === 'jam_operasional') {

                        const formatted = Math.round(Number(value || 0))
                            .toLocaleString('id-ID');

                        badge = `<span class="text-muted">${formatted}</span>`;

                    } else if (value === 1 || value === '1') {

                        badge = `<span class="badge bg-success">OK</span>`;

                    } else if (value === 0 || value === '0') {

                        badge = `<span class="badge bg-danger">NOK</span>`;

                    } else {

                        badge = `<span class="text-muted">${value || '-'}</span>`;
                    }

                    html += `
                            <div class="col-md-4 mb-2">
                                <strong>${label}</strong><br>${badge}
                            </div>
                        `;
                }

                html += `</div></div>`;
            });

            $('#modalDetailBody').html(html);
            $('#modalDetailP2H').modal('show');
        });

        // PDF download
        $('#downloadPDF').on('click', function() {
            // Ambil header dan body modal
            const header = document.querySelector('#modalDetailP2H .modal-header').cloneNode(true);
            const body = document.querySelector('#modalDetailBody').cloneNode(true);

            // Bungkus jadi 1 div untuk di-export
            const exportContainer = document.createElement('div');
            exportContainer.appendChild(header);
            exportContainer.appendChild(body);

            // Hilangkan scroll & batas tinggi
            exportContainer.style.maxHeight = 'unset';
            exportContainer.style.overflow = 'visible';

            const opt = {
                margin: 0.5,
                filename: 'detail_p2h_shift.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    scrollY: 0
                },
                jsPDF: {
                    unit: 'in',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    mode: ['css', 'legacy']
                } // auto page break
            };

            html2pdf()
                .set(opt)
                .from(exportContainer)
                .save();
        });

        // Edit P2H
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        $(document).on('click', '.btn-edit', function() {
            const uniqueKey = $(this).data('key');

            const [tanggal, nomorUnit, jenisP2h] = uniqueKey.split('|');

            const item = currentData.find(d =>
                d.tanggal === tanggal &&
                d.nomor_unit === nomorUnit &&
                d.jenis_p2h === jenisP2h
            );

            if (!item) {
                Swal.fire('Error', 'Data tidak ditemukan! Pastikan data sudah dimuat.', 'error');
                return;
            }

            // Header modal
            $('#editUnitTitle').text(`${item.nomor_unit} - ${item.tanggal}`);
            // $('#editId').val(item.id);
            $('#editTanggal').val(item.tanggal);
            $('#editTanggalDisplay').val(item.tanggal);
            $('#editNomorUnit').val(item.nomor_unit);
            $('#editNomorUnitDisplay').val(item.nomor_unit);
            $('#editJenisDisplay').val(item.jenis_p2h);
            $('#editJenisP2H').val(item.jenis_p2h);

            // Kosongkan tab dan content
            $('#shiftTabs').empty();
            $('#shiftTabContent').empty();

            // === DETEKSI JENIS UNIT ===
            const isPalletMover = item.jenis_p2h === 'Pallet Mover';

            // === CHECKLIST & LABEL KHUSUS PER JENIS ===
            let checklistItems, labelMap;

            if (isPalletMover) {
                checklistItems = [
                    'check_air_accu', 'check_battery', 'check_body_unit', 'check_klakson',
                    'check_roda', 'check_sistem_kemudi', 'check_kebersihan_unit',
                    'check_kunci_pm', 'check_hydraulic'
                ];

                labelMap = {
                    check_air_accu: 'Air Accu',
                    check_battery: 'Battery',
                    check_body_unit: 'Body Unit',
                    check_klakson: 'Klakson',
                    check_roda: 'Roda',
                    check_sistem_kemudi: 'Sistem Kemudi',
                    check_kebersihan_unit: 'Kebersihan Unit',
                    check_kunci_pm: 'Kunci PM',
                    check_hydraulic: 'Hydraulic'
                };
            } else {
                // Default: Forklift
                checklistItems = [
                    'cek_baterai', 'air_aki', 'kondisi_ban', 'rantai_lift', 'sistem_hidrolik',
                    'sistem_kemudi', 'fungsi_rem', 'klakson', 'buzzer_mundur', 'lampu_kiri',
                    'lampu_kanan', 'lampu_sorot', 'lampu_sign_depan_kiri', 'lampu_sign_depan_kanan',
                    'kaca_spion', 'kipas_belakang', 'panel_display', 'kondisi_axle',
                    'kondisi_body_kebersihan', 'cek_fork'
                ];

                labelMap = {
                    cek_baterai: 'Cek Baterai',
                    air_aki: 'Air Aki',
                    kondisi_ban: 'Kondisi Ban',
                    rantai_lift: 'Rantai Lift',
                    sistem_hidrolik: 'Sistem Hidrolik',
                    sistem_kemudi: 'Sistem Kemudi',
                    fungsi_rem: 'Fungsi Rem',
                    klakson: 'Klakson',
                    buzzer_mundur: 'Buzzer Mundur',
                    lampu_kiri: 'Lampu Kiri',
                    lampu_kanan: 'Lampu Kanan',
                    lampu_sorot: 'Lampu Sorot',
                    lampu_sign_depan_kiri: 'Lampu Sign Depan Kiri',
                    lampu_sign_depan_kanan: 'Lampu Sign Depan Kanan',
                    kaca_spion: 'Kaca Spion',
                    kipas_belakang: 'Kipas Belakang',
                    panel_display: 'Panel Display',
                    kondisi_axle: 'Kondisi Axle',
                    kondisi_body_kebersihan: 'Kondisi Body & Kebersihan',
                    cek_fork: 'Cek Fork'
                };
            }

            let hasAnyShift = false;
            let firstShift = true;

            [1, 2, 3].forEach(shift => {
                const shiftData = item.shifts?.[shift] || {};
                const hasData = shiftData.operator_name ||
                    shiftData.jam_operasional ||
                    Object.keys(shiftData).some(key => checklistItems.includes(key) &&
                        shiftData[key] == 1);

                if (!hasData) return;

                hasAnyShift = true;

                const isActive = firstShift;
                firstShift = false;

                const activeClass = isActive ? 'active' : '';
                const showClass = isActive ? 'show active' : '';

                $('#shiftTabs').append(`
                        <li class="nav-item" role="presentation">
                            <button class="nav-link ${activeClass}" data-bs-toggle="tab" 
                                    data-bs-target="#shift${shift}Tab" type="button">
                                Shift ${shift} ${shiftData.operator_name ? '(' + shiftData.operator_name + ')' : ''}
                            </button>
                        </li>
                    `);

                $('#shiftTabContent').append(`
                        <div class="tab-pane fade ${showClass}" id="shift${shift}Tab">
                            <div class="pt-3" id="shift${shift}Content"></div>
                        </div>
                    `);

                // Generate form checklist
                // Di dalam loop shift
                let headerHtml = `
                        <input type="hidden" name="shifts[${shift}][id]" value="${shiftData.id || ''}">
                        <div class="row mb-4">
                            <div class="col-md-${item.jenis_p2h !== 'Pallet Mover' ? '6' : '12'}">
                                <label>Operator</label>
                                <input type="text" class="form-control" name="shifts[${shift}][operator_name]" 
                                    value="${shiftData.operator_name || ''}" placeholder="Nama operator">
                            </div>
                    `;

                if (item.jenis_p2h !== 'Pallet Mover') {
                    headerHtml += `
                            <div class="col-md-6">
                                <label>Hours Meter</label>
                                <input type="number" class="form-control" name="shifts[${shift}][jam_operasional]" 
                                    value="${shiftData.jam_operasional || ''}" placeholder="Jam">
                            </div>
                        `;
                }

                headerHtml += `</div>`; // tutup row

                let html = headerHtml + `
                        <h6 class="mt-3">Checklist</h6>
                        <div class="row">
                    `;

                let generalCatatan = shiftData.catatan || '';

                checklistItems.forEach(field => {
                    let value = shiftData[field] ?? 0;
                    let checked = value == 1 ? 'checked' : '';
                    let noteValue = shiftData[`${field}_note`] || '';

                    // === LOGIC BARU: Ekstrak dari catatan umum kalau note per item kosong ===
                    // if (value == 0 && !noteValue && generalCatatan) {
                    //     const label = labelMap[field];
                    //     const regex = new RegExp(
                    //         `${label}\\s*[:\\-\\–]\\s*(.+?)(?=\\s*\\|\\s*|$)`, 'i');
                    //     const match = generalCatatan.match(regex);

                    //     if (match && match[1].trim()) {
                    //         noteValue = match[1].trim();
                    //         // HAPUS bagian yang diekstrak dari generalCatatan
                    //         generalCatatan = generalCatatan.replace(match[0], '')
                    //             .trim();
                    //         generalCatatan = generalCatatan.replace(/^\|\s*|\s*\|$/,
                    //             ''); // Bersihkan pipe di ujung
                    //         generalCatatan = generalCatatan.replace(/\s*\|\s*\|/,
                    //             ' | '); // Bersihkan pipe ganda
                    //     }
                    // }

                    if (value == 0 && !noteValue && generalCatatan) {

                        const label = labelMap[field];
                        const newFormat = generalCatatan.match(
                            new RegExp(
                                `${escapeRegExp(label)}\\s*[:\\-]\\s*(.+?)(?=\\s*\\||$)`,
                                'i'
                            )
                        );

                        if (newFormat && newFormat[1]) {
                            noteValue = newFormat[1].trim();
                        }

                        if (!noteValue) {
                            noteValue = extractLegacyNote(generalCatatan, field);
                        }
                    }


                    const showNote = value == 0 ? '' : 'd-none';

                    html += `
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="hidden" name="shifts[${shift}][${field}]" value="0">
                                <input class="form-check-input checklist-item" type="checkbox" 
                                    name="shifts[${shift}][${field}]" value="1" ${checked}>
                                <label class="form-check-label">${labelMap[field] || field}</label>
                            </div>
                            <div class="note-container mt-2 ${showNote}">
                                <label class="form-label text-danger">
                                    <small>Keterangan (wajib isi kenapa NOK)</small>
                                </label>
                                <input type="text" class="form-control form-control-sm note-input" 
                                    name="shifts[${shift}][${field}_note]" value="${noteValue}"
                                    placeholder="Jelaskan kerusakan...">
                            </div>
                        </div>
                    `;
                });



                $(`#shift${shift}Content`).html(html);
            });

            // Jika tidak ada shift sama sekali
            if (!hasAnyShift) {
                $('#shiftTabs').html(
                    '<li class="nav-item"><span class="nav-link text-muted">Belum ada data P2H untuk hari ini</span></li>'
                );
                $('#shiftTabContent').html(`
                        <div class="text-center py-5 text-muted">
                            <i class="mdi mdi-clipboard-off mdi-48px"></i>
                            <h5>Belum ada checklist diisi</h5>
                        </div>
                    `);
            }

            $('#editP2HModal').modal('show');
        });

        function extractLegacyNote(catatan, fieldKey) {
            const legacyLabelAliases = {
                kondisi_body_kebersihan: [
                    'body unit dan kebersihan',
                    'kondisi body dan kebersihan',
                    'body unit',
                ],
                sistem_hidrolik: [
                    'hydraulic system + selang',
                    'hydraulic system',
                    'sistem hidrolik',
                    'hydraulic'
                ],
                sistem_kemudi: [
                    'sistem kemudi'
                ],
                kondisi_ban: [
                    'ban',
                    'kondisi ban'
                ],
                fungsi_rem: [
                    'fungsi rem',
                    'rem'
                ],
                cek_fork: [
                    'pengecekan fork',
                    'fork'
                ],
                cek_baterai: [
                    'baterai',
                    'cek baterai'
                ],
                klakson: [
                    'klakson',
                    'horn',
                    'klakson / horn'
                ],
                air_aki: [
                    'air aki',
                    'level air aki'
                ],
                rantai_lift: [
                    'rantai lift',
                    'lift chains',
                    'chain lift'
                ],
                buzzer_mundur: [
                    'buzzer mundur',
                    'buzzer back'
                ],
                lampu_kiri: [
                    'lampu kiri',
                    'kombinasi lampu kiri'
                ],
                lampu_kanan: [
                    'lampu kanan',
                    'kombinasi lampu kanan'
                ],
                lampu_sorot: [
                    'lampu sorot',
                    'headlamp'
                ],
                lampu_sign_depan_kiri: [
                    'lampu sign depan kiri',
                    'left signal light'
                ],
                lampu_sign_depan_kanan: [
                    'lampu sign depan kanan',
                    'right signal light'
                ],
                kaca_spion: [
                    'kaca spion',
                    'rearview mirror'
                ],
                panel_display: [
                    'panel display'
                ],
                kondisi_axle: [
                    'kondisi axle',
                    'axle condition'
                ],

                // Pallet Mover
                check_air_accu: [
                    'air accu',
                    'Air Accu'
                ],
                check_battery: [
                    'battery',
                    'kondisi battery'
                ],
                check_body_unit: [
                    'body unit'
                ],
                check_klakson: [
                    'klakson',
                    'horn',
                    'klakson / horn'
                ],
                check_roda: [
                    'roda',
                ],
                check_sistem_kemudi: [
                    'sistem kemudi',
                ],
                check_kebersihan_unit: [
                    'kebersihan unit',
                    'unit cleanliness'
                ],
                check_kunci_pm: [
                    'kunci pm',
                    'pm key'
                ],
                check_hydraulic: [
                    'hydraulic',
                    'sistem hydraulic'
                ]

            };

            if (!catatan || !legacyLabelAliases[fieldKey]) return '';

            const text = catatan.toLowerCase();

            for (const alias of legacyLabelAliases[fieldKey]) {
                const escaped = escapeRegExp(alias);

                const regex = new RegExp(
                    `${escaped}\\s+([^,]+)`,
                    'i'
                );

                const match = text.match(regex);
                if (match && match[1]) {
                    return match[1].trim()
                        .replace(/^./, c => c.toUpperCase());
                }
            }
            return '';
        }

        $(document).on('change', '.checklist-item', function() {
            const $checkbox = $(this);
            const checked = $checkbox.is(':checked');
            const $container = $checkbox.closest('.form-check').siblings('.note-container');
            const $input = $container.find('.note-input');
            const $label = $container.find('label small');

            if (checked) {
                // OK → sembunyikan keterangan + hapus required + ubah teks jadi opsional
                $container.addClass('d-none');
                $input.removeAttr('required');
                $label.text('Keterangan opsional (kenapa NOK?)');
            } else {
                // NOK → tampilkan + required + teks wajib
                $container.removeClass('d-none');
                $input.attr('required', 'required');
                $label.text('Keterangan wajib (kenapa NOK?)');
            }
        });

        $('#editP2HForm').on('submit', function(e) {
            e.preventDefault();

            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...');

            const formData = new FormData(this);

            // Ambil jenis unit dari hidden input di modal (paling akurat)
            const jenisP2H = $('#editJenisP2H').val();
            const isPalletMover = jenisP2H === 'Pallet Mover';

            // Tentukan route update sesuai jenis unit
            const updateUrl = isPalletMover ?
                "{{ url('/p2h/update/multi-pallet') }}" // Route untuk Pallet Mover
                :
                "{{ url('/p2h/update/multi') }}"; // Route untuk Forklift

            $.ajax({
                url: updateUrl,
                type: 'POST', // Lebih tepat pakai PUT untuk update
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire('Berhasil!', res.message || 'Data berhasil diperbarui!',
                        'success');

                    const fetchUrl = isPalletMover ?
                        "{{ url('api/p2h/data/pallet-mover') }}" :
                        "{{ url('api/p2h/data/forklift-data') }}";

                    $.get(fetchUrl, function(newData) {
                        currentData = newData;
                        applyFilter();
                        renderTable(filteredData, currentPage);
                    });

                    $('#editP2HModal').modal('hide');
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan';
                    if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON?.errors) {
                        const errors = xhr.responseJSON.errors;
                        msg = Object.values(errors).flat().join('<br>• ');
                    }
                    Swal.fire('Gagal', msg, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Simpan Perubahan');
                }
            });
        });
    });
</script>
@endsection