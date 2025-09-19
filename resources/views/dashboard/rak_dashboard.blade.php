@extends('layouts.app')

@section('styles')
    <style>
        .rack-level {
            height: 30px;
            border: 1px solid #949494;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 4px;
            margin: 1px 0;
        }

        .rack-level:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .empty {
            background-color: #ebebeb;
            color: #6c757d;
        }

        .full {
            background-color: #f5a2a2;
            color: #600c0c;
            border-color: #f8baba;
        }

        .occupied {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .rack-card {
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 1.2px solid #d46300;
        }

        .rack-col {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .flour {
            background-color: #e9ecef;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .loading {
            text-align: center;
            padding: 20px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .badge-status {
            font-size: 10px;
            position: absolute;
            top: -5px;
            right: -5px;
        }

        .rack-level {
            position: relative;
        }

        .item-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }

        .highlight-search {
            background-color: #4adbfc !important;
            /* kuning muda */
            transition: background-color 0.3s ease;
        }

        /* kotak suggestion */
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            border-radius: 8px;
            background: #fff;
            border: 1px solid #ddd;
            font-size: 14px;
            z-index: 2000 !important;
            /* biar gak ketutup elemen lain */
        }

        /* setiap item */
        .ui-menu-item-wrapper {
            padding: 8px 12px;
            cursor: pointer;
        }

        /* hover / fokus */
        .ui-state-active,
        .ui-menu-item-wrapper.ui-state-active {
            background: linear-gradient(90deg, #198754, #20c997);
            color: #fff !important;
            border: none;
        }

        .bg-wh {
            background: linear-gradient(135deg, #4b38b3, #7d6cfa);
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            {{-- Header --}}
            <div class="row mb-2">
                <div class="col-12 text-center">
                    <h1 class="fw-bold mdi"> Warehouse Rack Dashboard</h1>
                    <h5 class="text-muted">Real-time Inventory Tracking</h5>
                </div>
            </div>

            <div class="row">
                {{-- Left: Warehouse Layout --}}
                <div class="col-lg-12">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-wh text-white fw-bold">
                            Warehouse Rack Layout
                        </div>
                        <div class="card-body">
                            {{-- Controls --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-2">
                                    <label class="form-label">Filter by Rack Name</label>
                                    <select class="form-select" id="rackFilter">
                                        <option value="all">All Racks</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Filter by Status</label>
                                    <select class="form-select" id="statusFilter">
                                        <option value="all">All Status</option>
                                        <option value="empty">Empty</option>
                                        <option value="full">Full</option>
                                        <option value="occupied">Occupied</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Search Item</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="itemSearch"
                                            placeholder="Search by mid or item name...">
                                        <button class="btn btn-primary" id="showItemSearch" style="">
                                            <i class="mdi mdi-eye me-2"></i> Tampilkan
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-outline-danger w-100" id="refreshData"><i
                                            class="mdi mdi-refresh me-3"></i>Refresh</button>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12 text-start">
                                    <div class="d-inline-block">
                                        <span class="badge empty px-3 py-2 me-2">Empty</span>
                                        <span class="badge full px-3 py-2 me-2">Full</span>
                                        <span class="badge occupied px-3 py-2">Occupied</span>
                                    </div>
                                </div>
                            </div>

                            <div id="loadingIndicator" class="loading">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p>Loading warehouse data...</p>
                            </div>

                            <div id="errorContainer"></div>

                            {{-- Warehouse Layout --}}
                            <div id="warehouseLayout" class="row g-3">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk detail rak -->
    <div class="modal fade" id="rackModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Detail Lokasi Rak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-pills" id="rackTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tabDetailBtn" data-bs-toggle="tab"
                                data-bs-target="#tabDetail" type="button" role="tab">Detail</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabOpnameBtn" data-bs-toggle="tab" data-bs-target="#tabOpname"
                                type="button" role="tab">Opname</button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content mt-3">

                        <!-- Tab Detail -->
                        <div class="tab-pane fade show active" id="tabDetail" role="tabpanel">
                            <div id="rackDetailContent">
                                <!-- konten detail rak bakal diinject via js -->
                            </div>
                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>

                        <!-- Tab Opname -->
                        <div class="tab-pane fade" id="tabOpname" role="tabpanel">
                            <form id="opnameForm">
                                <div class="table-responsive">
                                    <table id="opnameTable" class="table table-sm table-bordered align-middle text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Mid Barang</th>
                                                <th>Nama Barang</th>
                                                {{-- <th>Stock Sistem</th> --}}
                                                <th>Stock Fisik</th>
                                                <th>Selisih</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-success">Simpan Perubaha</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let warehouseData = {};

            function fetchWarehouseData() {
                $("#loadingIndicator").show();
                $("#errorContainer").empty();

                $.ajax({
                    url: `{{ url('api/dashboard/wsp/rak') }}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status && response.data.length > 0) {
                            // reset option biar gak dobel
                            $("#rackFilter").empty().append('<option value="all">All Racks</option>');

                            // loop data rak
                            let racks = [...new Set(response.data.map(item => item.nama_rak))];
                            racks.sort((a, b) => a.localeCompare(b));
                            racks.forEach(function(nama_rak) {
                                $("#rackFilter").append(
                                    `<option value="${nama_rak}">${nama_rak}</option>`
                                );
                            });

                            // render data warehouse
                            processAPIData(response.data);
                            renderWarehouse();
                        } else {
                            showError("Data tidak ditemukan");
                        }
                    },
                    error: function() {
                        showError("Terjadi kesalahan koneksi");
                    },
                    complete: function() {
                        $("#loadingIndicator").hide();
                    }
                });
            }

            function processAPIData(data) {
                warehouseData = {};

                data.forEach(item => {
                    const kodeRak = item.kode_rak;
                    const namaRak = item.nama_rak;
                    const kolom = item.kolom_rak;
                    const level = item.level_rak;
                    const box = item.box_rak;

                    // Inisialisasi struktur data jika belum ada
                    if (!warehouseData[kodeRak]) {
                        warehouseData[kodeRak] = {};
                    }
                    if (!warehouseData[kodeRak][namaRak]) {
                        warehouseData[kodeRak][namaRak] = {};
                    }
                    if (!warehouseData[kodeRak][namaRak][kolom]) {
                        warehouseData[kodeRak][namaRak][kolom] = {};
                    }
                    if (!warehouseData[kodeRak][namaRak][kolom][level]) {
                        warehouseData[kodeRak][namaRak][kolom][level] = [];
                    }

                    // Tambahkan item ke lokasi yang sesuai
                    warehouseData[kodeRak][namaRak][kolom][level].push({
                        ...item,
                        rak_id: item.rak_id // pastikan ada di API
                    });
                });
            }

            function renderWarehouse() {
                let $layout = $("#warehouseLayout");
                $layout.empty();

                // Urutkan kodeRak secara natural (misal FLR01, FLR02, FLR03, dst)
                Object.keys(warehouseData).sort((a, b) => {
                    // Ambil angka setelah FLR, fallback ke string compare jika tidak ada angka
                    const numA = parseInt(a.replace(/\D/g, '')) || 0;
                    const numB = parseInt(b.replace(/\D/g, '')) || 0;
                    if (numA !== numB) return numA - numB;
                    return a.localeCompare(b);
                }).forEach(kodeRak => {
                    $layout.append(
                        `<div class="flour col-12 text-center text-muted py-3 fw-bold">${kodeRak}</div>`
                    );

                    // Loop berdasarkan nama_rak
                    Object.keys(warehouseData[kodeRak]).sort((a, b) => a.localeCompare(b)).forEach(
                        namaRak => {
                            let rackHtml = `
                            <div class="col-md-4 col-lg-3">
                                <div class="card rack-card" data-rack="${namaRak}">
                                    <div class="card-header text-center fw-bold">Rack ${namaRak}</div>
                                    <div class="card-body p-2">
                                        <div class="row">`;

                            // Tentukan batas kolom berdasarkan kodeRak
                            let maxKolom;
                            if (kodeRak === "FL1") {
                                maxKolom = 2;
                            } else if (kodeRak === "FL2" || kodeRak === "FL3") {
                                maxKolom = 4;
                            } else {
                                // Untuk floor lain, ambil kolom yang ada di data
                                let availableKolom = Object.keys(warehouseData[kodeRak][namaRak])
                                    .map(k => parseInt(k))
                                    .sort((a, b) => a - b);
                                maxKolom = availableKolom.length;
                            }

                            // Generate array kolom berdasarkan aturan (1, 2, 3, dst.)
                            let kolomKeys = [];
                            for (let i = 1; i <= maxKolom; i++) {
                                kolomKeys.push(i);
                            }

                            // Tentukan CSS class berdasarkan jumlah kolom
                            let colClass;
                            if (kolomKeys.length === 1) colClass = 'col-12';
                            else if (kolomKeys.length === 2) colClass = 'col-6';
                            else if (kolomKeys.length === 3) colClass = 'col-4';
                            else colClass = 'col-3';

                            // Loop kolom (misal 1, 2, 3, dst.)
                            kolomKeys.forEach(kolom => {
                                rackHtml += `
                                <div class="${colClass}">
                                    <h6 class="text-center rack-col">${namaRak}${kolom}</h6>
                                    <div class="d-flex flex-column-reverse gap-1">`;

                                // Tentukan level maksimal
                                const kolomData = warehouseData[kodeRak][namaRak][kolom] || {};
                                const maxLevel = Math.max(...Object.keys(kolomData).map(l =>
                                    parseInt(l)), 6);

                                for (let level = 1; level <= maxLevel; level++) {
                                    const items = kolomData[level] || [];
                                    const itemCount = items.length;
                                    let status = 'empty';

                                    if (itemCount > 0) {
                                        if (itemCount >= 3) status = 'full';
                                        else if (itemCount >= 1) status = 'occupied';
                                    }

                                    rackHtml += `
                                    <div class="rack-level ${status}" 
                                        data-rak-id="${items.length > 0 ? items[0].rak_id : ''}"
                                        data-kode-rak="${kodeRak}"
                                        data-nama-rak="${namaRak}" 
                                        data-kolom="${kolom}" 
                                        data-level="${level}"
                                        data-items='${JSON.stringify(items)}'>
                                        L${level}
                                        ${itemCount > 0 ? `<span class="item-count">${itemCount}</span>` : ''}
                                    </div>`;
                                }

                                rackHtml += `</div></div>`;
                            });

                            rackHtml += `
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                            $layout.append(rackHtml);
                        });
                });
            }

            // Event handler untuk klik level rak 
            $(document).on("click", ".rack-level", function() {
                const kodeRak = $(this).data("kode-rak");
                const namaRak = $(this).data("nama-rak");
                const kolom = $(this).data("kolom");
                const level = $(this).data("level");
                const items = $(this).data("items");

                // Build konten detail
                let detailContent = `
                <div class="row mb-3">
                    <div class="col-md-5">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 40%">Kode Rak</th>
                                    <td>${kodeRak}</td>
                                </tr>
                                <tr>
                                    <th>Nama Rak</th>
                                    <td>${namaRak}</td>
                                </tr>
                                <tr>
                                    <th>Kolom</th>
                                    <td>${kolom}</td>
                                </tr>
                                <tr>
                                    <th>Level</th>
                                    <td>${level}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-5">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 40%">Status</th>
                                    <td>
                                        <span class="badge bg-${
                                            getStatusBadgeClass(
                                                $(this).hasClass('empty')
                                                    ? 'empty'
                                                    : $(this).hasClass('full')
                                                        ? 'full'
                                                        : 'occupied'
                                            )
                                        }">
                                        ${getStatusText($(this))}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Jumlah Item</th>
                                    <td>${items.length}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>`;

                if (items.length > 0) {
                    detailContent += `
                    <h6>Daftar Barang:</h6>
                    <div class="table-responsive">
                        <table id="barangTable" class="table table-sm table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>MID</th>
                                    <th>Box</th>
                                    <th>Username</th>
                                    <th>Image</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>`;
                } else {
                    detailContent += `<div class="alert alert-info">Tidak ada barang di lokasi ini</div>`;
                }

                // Inject ke tab Detail
                $("#rackDetailContent").html(detailContent);

                // Set judul modal
                $("#modalTitle").text(`Detail Lokasi: ${kodeRak} - ${namaRak}${kolom} - L${level}`);
                $("#rackModal").modal("show");

                // Inisialisasi DataTable setelah modal tampil
                $("#rackModal").on("shown.bs.modal", function() {
                    if (items.length > 0) {
                        $("#barangTable").DataTable({
                            data: items,
                            destroy: true,
                            scrollX: true,
                            columns: [{
                                    data: null,
                                    render: (d, t, r, i) => i.row + 1
                                },
                                {
                                    data: "nama_barang"
                                },
                                {
                                    data: "mid_barang"
                                },
                                {
                                    data: "box_rak",
                                    render: d =>
                                        `<span class="badge bg-info text-white px-3 py-1">${d}</span>`
                                },
                                {
                                    data: "username"
                                },
                                {
                                    data: "image",
                                    render: d => d ? `<img src="${d}" width="60">` : "-"
                                }
                            ],
                            order: [
                                [0, "asc"]
                            ],
                            language: {
                                lengthMenu: "Show _MENU_ entries"
                            }
                        });
                    }
                });

                // Reset form opname setiap kali modal dibuka
                $("#opnameForm")[0].reset();

                // Load data ke tabel opname
                loadOpnameTable(items);

                function loadOpnameTable(items) {
                    $("#opnameTable").DataTable({
                        data: items,
                        destroy: true,
                        paging: false,
                        info: false,
                        columns: [{
                                data: null,
                                render: (d, t, r, i) => i.row + 1
                            },
                            {
                                data: "mid_barang"
                            },
                            {
                                data: "nama_barang"
                            },
                            {
                                data: "qty",
                                className: "text-center",
                                render: function(d, t, r) {
                                    return `<input type="number" class="form-control form-control-sm stock-fisik" 
                                data-barang-id="${r.barang_id}" 
                                data-rak-id="${r.rak_id}" 
                                value="${d}" min="0">`;
                                }
                            },
                            {
                                data: null,
                                className: "text-center selisih",
                                render: () => "0"
                            },
                            {
                                data: null,
                                render: function(d) {
                                    return `
                                        <input type="text" class="form-control keterangan" value="">
                                    `;
                                }
                            }
                        ]
                    });
                }
            });

            // Klik tombol tampilkan
            function searchItem() {
                const keyword = $("#itemSearch").val().trim().toLowerCase();

                if (!keyword) {
                    showError("Masukkan MID atau Nama Barang dulu!");
                    return;
                }

                // Flatten semua items dari warehouseData
                let allItems = [];
                Object.values(warehouseData).forEach(rakObj => {
                    Object.values(rakObj).forEach(namaRakObj => {
                        Object.values(namaRakObj).forEach(kolomObj => {
                            Object.values(kolomObj).forEach(levelArr => {
                                allItems = allItems.concat(levelArr);
                            });
                        });
                    });
                });

                // Filter berdasarkan input
                const filtered = allItems.filter(item =>
                    item.mid_barang.toLowerCase().includes(keyword) ||
                    item.nama_barang.toLowerCase().includes(keyword)
                );

                // Build konten modal
                let detailContent = "";
                if (filtered.length > 0) {
                    detailContent = `
                    <div class="table-responsive">
                        <table id="searchTable" class="table table-sm table-striped align-middle text-nowrap">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>MID</th>
                                    <th>Kode Rak</th>
                                    <th>Nama Rak</th>
                                    <th>Kolom Rak</th>
                                    <th>Level Rak</th>
                                    <th>Box</th>
                                    <th>Username</th>
                                    <th>Image</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                `;
                } else {
                    detailContent = `<div class="alert alert-warning">Barang tidak ditemukan</div>`;
                }

                $("#rackDetailContent").html(detailContent);
                $("#modalTitle").text(`Detail Pencarian: ${keyword}`);
                $("#rackModal").modal("show");

                // Inisialisasi DataTable setelah modal tampil
                $("#rackModal").on("shown.bs.modal", function() {
                    if (filtered.length > 0) {
                        $("#searchTable").DataTable({
                            data: filtered,
                            destroy: true,
                            scrollX: true,
                            columns: [{
                                    data: null,
                                    render: (d, t, r, i) => i.row + 1
                                },
                                {
                                    data: "nama_barang"
                                },
                                {
                                    data: "mid_barang"
                                },
                                {
                                    data: "kode_rak"
                                },
                                {
                                    data: "nama_rak"
                                },
                                {
                                    data: "kolom_rak"
                                },
                                {
                                    data: "level_rak"
                                },
                                {
                                    data: "box_rak",
                                    render: d =>
                                        `<span class="badge bg-info text-white px-3 py-1">${d}</span>`
                                },
                                {
                                    data: "username"
                                },
                                {
                                    data: "image",
                                    render: d => (d ? `<img src="${d}" width="60">` : "-")
                                }
                            ],
                            language: {
                                lengthMenu: "Show _MENU_ entries"
                            }
                        });
                    }
                });

                // Reset form opname setiap kali modal dibuka
                $("#opnameForm")[0].reset();

                // Load data ke tabel opname
                loadOpnameTable(filtered);

                function loadOpnameTable(items) {
                    $("#opnameTable").DataTable({
                        data: items,
                        destroy: true,
                        paging: false,
                        info: false,
                        scrollX: true,
                        columns: [{
                                data: null,
                                render: (d, t, r, i) => i.row + 1
                            },
                            {
                                data: "mid_barang"
                            },
                            {
                                data: "nama_barang"
                            },
                            {
                                data: "qty",
                                className: "text-center",
                                render: function(d, t, r) {
                                    return `<input type="number" class="form-control form-control-sm stock-fisik" 
                                data-barang-id="${r.barang_id}" 
                                data-rak-id="${r.rak_id}" 
                                value="${d}" min="0">`;
                                }
                            },
                            {
                                data: null,
                                className: "text-center selisih",
                                render: () => "0"
                            },
                            {
                                data: null,
                                render: function(d) {
                                    return `
                                        <input type="text" class="form-control keterangan" value="">
                                    `;
                                }
                            }
                        ]
                    });
                }
            }

            // Trigger lewat klik tombol
            $("#showItemSearch").on("click", function() {
                searchItem();
            });

            // Trigger tampilkan search lewat enter di input
            $("#itemSearch").on("keypress", function(e) {
                if (e.which === 13) { // 13 = Enter
                    e.preventDefault(); // supaya tidak submit form
                    searchItem();
                }
            });

            // Hitung selisih kalau input berubah
            $(document).on("input", ".stock-fisik", function() {
                const row = $(this).closest("tr");
                const stockSistem = parseInt(row.find("td:eq(2)").text()) || 0;
                const stockFisik = parseInt($(this).val()) || 0;
                const selisih = stockFisik - stockSistem;
                row.find(".selisih").text(selisih);
            });

            // simpan opname
            $("#opnameForm").on("submit", function(e) {
                e.preventDefault();

                let data = [];
                $("#opnameTable tbody tr").each(function() {
                    const barangId = $(this).find(".stock-fisik").data("barang-id");
                    const rakId = $(this).find(".stock-fisik").data("rak-id");
                    const stockSistem = parseInt($(this).find("td:eq(2)").text()) || 0;
                    const stockFisik = parseInt($(this).find(".stock-fisik").val()) || 0;
                    const selisih = stockFisik - stockSistem;
                    const keterangan = $(this).find(".keterangan").val() || "";

                    console.log(rakId);

                    data.push({
                        rak_id: rakId,
                        barang_id: barangId,
                        stock_sistem: stockSistem,
                        stock_fisik: stockFisik,
                        selisih: selisih,
                        keterangan: keterangan
                    });
                });

                $.ajax({
                    url: "{{ route('wsp.rak.store.opname') }}", // endpoint opname bulk
                    method: "POST",
                    data: JSON.stringify({
                        items: data
                    }),
                    contentType: "application/json",
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            message: 'Data Opname Berhasil Disimpan'
                        })
                        $("#rackModal").modal("hide");
                        fetchWarehouseData();
                    },
                    error: function(xhr) {
                        showError("Data gagal disimpan!")
                    }
                });
            });

            function showError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                });
            }

            function getStatusBadgeClass(status) {
                switch (status) {
                    case 'empty':
                        return 'secondary';
                    case 'full':
                        return 'danger';
                    case 'occupied':
                        return 'success';
                    default:
                        return 'secondary';
                }
            }

            function getStatusText($element) {
                if ($element.hasClass('empty')) return 'Kosong';
                if ($element.hasClass('full')) return 'Penuh';
                if ($element.hasClass('occupied')) return 'Berisi';
                return 'Tidak Diketahui';
            }

            // Event handler untuk refresh data
            $("#refreshData").click(function() {
                fetchWarehouseData();

                $("#rackFilter").val("all");
                $("#statusFilter").val("all");
                $("#itemSearch").val("");
            });

            // filter 
            $("#rackFilter, #statusFilter").change(function() {
                const selectedRack = $("#rackFilter").val();
                const selectedStatus = $("#statusFilter").val().toLowerCase();
                const searchTerm = $("#itemSearch").val().toLowerCase();

                $(".rack-card").each(function() {
                    const rackName = $(this).data("rack");
                    let showRack = (selectedRack === "all" || rackName === selectedRack);

                    if (showRack && selectedStatus !== "all") {
                        showRack = false;
                        $(this).find(".rack-level").each(function() {
                            if ($(this).hasClass(selectedStatus)) {
                                showRack = true;
                                return false; // break loop
                            }
                        });
                    }

                    if (showRack && searchTerm) {
                        showRack = false;
                        $(this).find(".rack-level").each(function() {
                            const items = $(this).data("items");
                            for (let item of items) {
                                if (item.nama_barang.toLowerCase().includes(searchTerm) ||
                                    item.mid_barang.toLowerCase().includes(searchTerm)) {
                                    showRack = true;
                                    return false; // break loop
                                }
                            }
                        });
                    }

                    if (showRack) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Autocomplete search
            $("#itemSearch").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "{{ url('/api/wsp/items/search') }}",
                        data: {
                            q: request.term
                        },
                        success: function(data) {
                            response(data.map(item => ({
                                label: `<strong>${item.mid_barang}</strong> - ${item.nama_barang}`,
                                value: item.mid_barang
                            })));
                        }
                    });
                },
                minLength: 2
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li>")
                    .append("<div>" + item.label + "</div>")
                    .appendTo(ul);
            };

            // item search
            $("#itemSearch").on("keyup", function() {
                const searchTerm = $(this).val().toLowerCase();
                const selectedRack = $("#rackFilter").val();
                const selectedStatus = $("#statusFilter").val().toLowerCase();

                $(".rack-card").each(function() {
                    const rackName = $(this).data("rack");
                    let showRack = (selectedRack === "all" || rackName === selectedRack);

                    // reset highlight sebelum search baru
                    $(this).find(".rack-level").removeClass("highlight-search");

                    if (showRack && selectedStatus !== "all") {
                        showRack = false;
                        $(this).find(".rack-level").each(function() {
                            if ($(this).hasClass(selectedStatus)) {
                                showRack = true;
                                return false; // break loop
                            }
                        });
                    }

                    if (showRack && searchTerm) {
                        showRack = false;
                        $(this).find(".rack-level").each(function() {
                            const items = $(this).data("items");
                            for (let item of items) {
                                if (item.nama_barang.toLowerCase().includes(searchTerm) ||
                                    item.mid_barang.toLowerCase().includes(searchTerm)) {

                                    // highlight rak level yang kena match
                                    $(this).addClass("highlight-search");

                                    showRack = true;
                                    return false; // break loop
                                }
                            }
                        });
                    }

                    if (showRack) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Load data pertama kali
            fetchWarehouseData();
        });
    </script>
@endsection
