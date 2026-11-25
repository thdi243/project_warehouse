@extends('layouts.app')

@section('title', '| Master Organisasi (Departemen, Bagian, Jabatan)')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Master Data Organisasi</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Data Master</a></li>
                                <li class="breadcrumb-item active">Organisasi</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nav Tabs -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom-0 pb-0">
                            <ul class="nav nav-tabs nav-tabs-custom card-header-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active fw-semibold" data-bs-toggle="tab" href="#tabDepartemen"
                                        role="tab">
                                        <i class="mdi mdi-domain me-1 text-primary"></i> Master Departemen
                                        <span class="badge bg-primary-subtle text-primary ms-1"
                                            id="badgeCountDept">{{ count($departemens) }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-semibold" data-bs-toggle="tab" href="#tabBagian" role="tab">
                                        <i class="mdi mdi-sitemap me-1 text-info"></i> Master Bagian
                                        <span class="badge bg-info-subtle text-info ms-1"
                                            id="badgeCountBagian">{{ count($bagians) }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-semibold" data-bs-toggle="tab" href="#tabJabatan" role="tab">
                                        <i class="mdi mdi-badge-account-horizontal-outline me-1 text-success"></i> Master
                                        Jabatan
                                        <span class="badge bg-success-subtle text-success ms-1"
                                            id="badgeCountJabatan">{{ count($jabatans) }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body p-4">
                            <div class="tab-content">
                                <!-- TAB 1: DEPARTEMEN -->
                                <div class="tab-pane active" id="tabDepartemen" role="tabpanel">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                                        <div>
                                            <h6 class="fw-bold mb-1">Daftar Departemen Perusahaan</h6>
                                            <p class="text-muted small mb-0">Kode departemen digunakan sebagai kode dokumen
                                                otomatis (misal: WRH, ENG, PRD).</p>
                                        </div>
                                        <button class="btn btn-primary btn-sm" id="btnOpenAddDept">
                                            <i class="mdi mdi-plus-circle me-1"></i> Tambah Departemen
                                        </button>
                                    </div>

                                    <!-- Client Toolbar: Entries & Search -->
                                    <div class="row mb-3 align-items-center g-2">
                                        <div class="col-sm-6 d-flex align-items-center">
                                            <span class="text-muted small me-2">Tampilkan</span>
                                            <select id="perPageDept" class="form-select form-select-sm"
                                                style="width: 75px;">
                                                <option value="5">5</option>
                                                <option value="10" selected>10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                            </select>
                                            <span class="text-muted small ms-2">data</span>
                                        </div>
                                        <div class="col-sm-6 d-flex justify-content-sm-end">
                                            <div class="input-group input-group-sm" style="max-width: 260px;">
                                                <span class="input-group-text bg-white border-end-0"><i
                                                        class="mdi mdi-magnify text-muted"></i></span>
                                                <input type="text" id="searchDept" class="form-control border-start-0"
                                                    placeholder="Cari departemen...">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    id="btnClearSearchDept" title="Reset cari" style="display:none;">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabel Standar Bootstrap (Tanpa Datatables Library) -->
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped align-middle mb-0">
                                            <thead class="table-light text-center small">
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th>Kode Singkatan</th>
                                                    <th>Nama Departemen</th>
                                                    <th>Jumlah Bagian</th>
                                                    <th>Deskripsi</th>
                                                    <th>Status</th>
                                                    <th width="12%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyDept">
                                                <tr>
                                                    <td colspan="7" class="text-center py-4 text-muted">
                                                        <span class="spinner-border spinner-border-sm me-2"></span>Memuat
                                                        data departemen...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination Footer -->
                                    <div class="row align-items-center mt-3 g-2">
                                        <div class="col-sm-6 text-muted small" id="infoDept">
                                            Menampilkan 0 data
                                        </div>
                                        <div class="col-sm-6 d-flex justify-content-sm-end">
                                            <ul class="pagination pagination-sm mb-0" id="paginationDept"></ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 2: BAGIAN -->
                                <div class="tab-pane" id="tabBagian" role="tabpanel">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                                        <div>
                                            <h6 class="fw-bold mb-1">Daftar Bagian (Sub-Departemen)</h6>
                                            <p class="text-muted small mb-0">Aturan SPV dan Matrix Approval dapat
                                                disesuaikan hingga level bagian ini.</p>
                                        </div>
                                        <button class="btn btn-info btn-sm text-white" id="btnOpenAddBagian">
                                            <i class="mdi mdi-plus-circle me-1"></i> Tambah Bagian Baru
                                        </button>
                                    </div>

                                    <!-- Client Toolbar: Entries, Filter Dept, & Search -->
                                    <div class="row mb-3 align-items-center g-2">
                                        <div class="col-md-7 d-flex flex-wrap align-items-center gap-2">
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted small me-2">Tampilkan</span>
                                                <select id="perPageBagian" class="form-select form-select-sm"
                                                    style="width: 75px;">
                                                    <option value="5">5</option>
                                                    <option value="10" selected>10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                </select>
                                                <span class="text-muted small ms-2">data</span>
                                            </div>
                                            <div class="d-flex align-items-center ms-md-2">
                                                <span class="text-muted small me-2">Filter Dept:</span>
                                                <select id="filterDeptForBagian" class="form-select form-select-sm"
                                                    style="min-width: 170px;">
                                                    <option value="">Semua Departemen</option>
                                                    @foreach ($departemens as $d)
                                                        <option value="{{ $d->id }}">{{ $d->nama }}
                                                            ({{ $d->kode }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5 d-flex justify-content-md-end">
                                            <div class="input-group input-group-sm" style="max-width: 260px;">
                                                <span class="input-group-text bg-white border-end-0"><i
                                                        class="mdi mdi-magnify text-muted"></i></span>
                                                <input type="text" id="searchBagian"
                                                    class="form-control border-start-0" placeholder="Cari bagian...">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    id="btnClearSearchBagian" title="Reset cari" style="display:none;">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabel Standar Bootstrap (Tanpa Datatables Library) -->
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped align-middle mb-0">
                                            <thead class="table-light text-center small">
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th class="text-start">Departemen Induk</th>
                                                    <th>Kode Bagian</th>
                                                    <th>Nama Bagian</th>
                                                    <th>Deskripsi</th>
                                                    <th>Status</th>
                                                    <th width="12%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyBagian">
                                                <tr>
                                                    <td colspan="7" class="text-center py-4 text-muted">
                                                        <span class="spinner-border spinner-border-sm me-2"></span>Memuat
                                                        data bagian...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination Footer -->
                                    <div class="row align-items-center mt-3 g-2">
                                        <div class="col-sm-6 text-muted small" id="infoBagian">
                                            Menampilkan 0 data
                                        </div>
                                        <div class="col-sm-6 d-flex justify-content-sm-end">
                                            <ul class="pagination pagination-sm mb-0" id="paginationBagian"></ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 3: JABATAN -->
                                <div class="tab-pane" id="tabJabatan" role="tabpanel">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                                        <div>
                                            <h6 class="fw-bold mb-1">Daftar Jabatan & Hierarki</h6>
                                            <p class="text-muted small mb-0">Tingkat hierarki jabatan menentukan hak
                                                persetujuan approval dokumen.</p>
                                        </div>
                                        <button class="btn btn-success btn-sm" id="btnOpenAddJabatan">
                                            <i class="mdi mdi-plus-circle me-1"></i> Tambah Jabatan Baru
                                        </button>
                                    </div>

                                    <!-- Client Toolbar: Entries & Search -->
                                    <div class="row mb-3 align-items-center g-2">
                                        <div class="col-sm-6 d-flex align-items-center">
                                            <span class="text-muted small me-2">Tampilkan</span>
                                            <select id="perPageJabatan" class="form-select form-select-sm"
                                                style="width: 75px;">
                                                <option value="5">5</option>
                                                <option value="10" selected>10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                            </select>
                                            <span class="text-muted small ms-2">data</span>
                                        </div>
                                        <div class="col-sm-6 d-flex justify-content-sm-end">
                                            <div class="input-group input-group-sm" style="max-width: 260px;">
                                                <span class="input-group-text bg-white border-end-0"><i
                                                        class="mdi mdi-magnify text-muted"></i></span>
                                                <input type="text" id="searchJabatan"
                                                    class="form-control border-start-0" placeholder="Cari jabatan...">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    id="btnClearSearchJabatan" title="Reset cari" style="display:none;">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabel Standar Bootstrap (Tanpa Datatables Library) -->
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped align-middle mb-0">
                                            <thead class="table-light text-center small">
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th>Kode</th>
                                                    <th>Nama Jabatan</th>
                                                    <th>Deskripsi</th>
                                                    <th>Status</th>
                                                    <th width="12%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyJabatan">
                                                <tr>
                                                    <td colspan="6" class="text-center py-4 text-muted">
                                                        <span class="spinner-border spinner-border-sm me-2"></span>Memuat
                                                        data jabatan...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination Footer -->
                                    <div class="row align-items-center mt-3 g-2">
                                        <div class="col-sm-6 text-muted small" id="infoJabatan">
                                            Menampilkan 0 data
                                        </div>
                                        <div class="col-sm-6 d-flex justify-content-sm-end">
                                            <ul class="pagination pagination-sm mb-0" id="paginationJabatan"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DEPARTEMEN -->
    <div class="modal fade" id="modalDept" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalDeptTitle">Tambah Departemen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formDept">
                    @csrf
                    <input type="hidden" name="dept_id" id="deptId">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kode Singkatan <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="kode" id="deptKode" class="form-control font-monospace"
                                placeholder="Contoh: ENG, WRH, PRD" required maxlength="20">
                            <small class="text-muted" style="font-size: 11px;">*Digunakan pada penomoran dokumen
                                resmi.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Departemen <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="nama" id="deptNama" class="form-control"
                                placeholder="Contoh: Engineering" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Deskripsi / Keterangan</label>
                            <textarea name="deskripsi" id="deptDeskripsi" class="form-control" rows="2"
                                placeholder="Keterangan fungsi departemen..."></textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="deptActive"
                                value="1" checked>
                            <label class="form-check-label small fw-semibold" for="deptActive">Status Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">Simpan Departemen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL BAGIAN -->
    <div class="modal fade" id="modalBagian" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalBagianTitle">Tambah Bagian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formBagian">
                    @csrf
                    <input type="hidden" name="bagian_id" id="bagianId">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Departemen Induk <span
                                    class="text-danger">*</span></label>
                            <select name="departemen_id" id="bagianDeptId" class="form-select" required>
                                <option value="">Pilih Departemen...</option>
                                @foreach ($departemens as $d)
                                    <option value="{{ $d->id }}">{{ $d->nama }} ({{ $d->kode }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kode Bagian</label>
                            <input type="text" name="kode" id="bagianKode" class="form-control font-monospace"
                                placeholder="Contoh: UTL, WSP" maxlength="30">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Bagian <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="nama" id="bagianNama" class="form-control"
                                placeholder="Contoh: Engineering Utility" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Deskripsi</label>
                            <textarea name="deskripsi" id="bagianDeskripsi" class="form-control" rows="2"
                                placeholder="Keterangan fungsi bagian..."></textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="bagianActive"
                                value="1" checked>
                            <label class="form-check-label small fw-semibold" for="bagianActive">Status Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info btn-sm text-white">Simpan Bagian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL JABATAN -->
    <div class="modal fade" id="modalJabatan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalJabatanTitle">Tambah Jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formJabatan">
                    @csrf
                    <input type="hidden" name="jabatan_id" id="jabatanId">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kode Jabatan <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="kode" id="jabatanKode" class="form-control font-monospace"
                                placeholder="Contoh: SPV, DH, FRM" required maxlength="20">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Jabatan <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="nama" id="jabatanNama" class="form-control"
                                placeholder="Contoh: Supervisor" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Deskripsi</label>
                            <textarea name="deskripsi" id="jabatanDeskripsi" class="form-control" rows="2"
                                placeholder="Keterangan tugas jabatan..."></textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="jabatanActive"
                                value="1" checked>
                            <label class="form-check-label small fw-semibold" for="jabatanActive">Status Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm">Simpan Jabatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Data Awal dari Server (Bisa direfresh via AJAX tanpa reload halaman)
            let orgData = {
                departemens: @json($departemens),
                bagians: @json($bagians),
                jabatans: @json($jabatans)
            };

            // ========================================================
            // KOMPONEN CLIENT-SIDE PAGINATION TABLE (TANPA DATATABLES)
            // ========================================================
            function initClientPagination(config) {
                let rawData = config.initialData || [];
                let currentPage = 1;
                let perPage = parseInt($(config.perPageSelector).val()) || 10;
                let searchQuery = '';
                let extraFilter = null;

                function getFilteredData() {
                    let result = rawData;
                    if (config.customFilter) {
                        result = config.customFilter(result, extraFilter);
                    }
                    if (searchQuery.trim() !== '') {
                        const q = searchQuery.toLowerCase().trim();
                        result = result.filter(item => config.searchFn(item, q));
                    }
                    return result;
                }

                function render() {
                    const filtered = getFilteredData();
                    const total = filtered.length;
                    const totalPages = Math.max(1, Math.ceil(total / perPage));

                    if (currentPage > totalPages) currentPage = totalPages;
                    if (currentPage < 1) currentPage = 1;

                    const startIdx = (currentPage - 1) * perPage;
                    const endIdx = startIdx + perPage;
                    const pageItems = filtered.slice(startIdx, endIdx);

                    // Render Table Body
                    if (pageItems.length === 0) {
                        $(config.tbodySelector).html(`
                        <tr>
                            <td colspan="${config.columnsCount || 7}" class="text-center py-4 text-muted">
                                <i class="mdi mdi-information-outline fs-5 d-block mb-1"></i>
                                ${config.emptyText || 'Tidak ada data ditemukan.'}
                            </td>
                        </tr>
                    `);
                    } else {
                        const rowsHtml = pageItems.map((item, idx) => config.rowRenderer(item, startIdx + idx + 1))
                            .join('');
                        $(config.tbodySelector).html(rowsHtml);
                    }

                    // Render Info Text
                    const displayedStart = total === 0 ? 0 : startIdx + 1;
                    const displayedEnd = Math.min(endIdx, total);
                    $(config.infoSelector).html(
                        `Menampilkan <b>${displayedStart}</b> sampai <b>${displayedEnd}</b> dari <b>${total}</b> data`
                    );

                    // Render Pagination Controls
                    renderPaginationButtons(totalPages, total);
                }

                function renderPaginationButtons(totalPages, total) {
                    if (total === 0 || totalPages <= 1) {
                        $(config.paginationSelector).html('');
                        return;
                    }

                    let html = '';

                    // Tombol Previous
                    html += `
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${currentPage - 1}">
                            <i class="mdi mdi-chevron-left"></i>
                        </a>
                    </li>
                `;

                    // Smart Window Pagination
                    const maxPagesToShow = 5;
                    let startPage = Math.max(1, currentPage - 2);
                    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
                    if (endPage - startPage < maxPagesToShow - 1) {
                        startPage = Math.max(1, endPage - maxPagesToShow + 1);
                    }

                    if (startPage > 1) {
                        html +=
                            `<li class="page-item"><a class="page-link" href="javascript:void(0);" data-page="1">1</a></li>`;
                        if (startPage > 2) {
                            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                        }
                    }

                    for (let p = startPage; p <= endPage; p++) {
                        html += `
                        <li class="page-item ${p === currentPage ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0);" data-page="${p}">${p}</a>
                        </li>
                    `;
                    }

                    if (endPage < totalPages) {
                        if (endPage < totalPages - 1) {
                            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                        }
                        html +=
                            `<li class="page-item"><a class="page-link" href="javascript:void(0);" data-page="${totalPages}">${totalPages}</a></li>`;
                    }

                    // Tombol Next
                    html += `
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${currentPage + 1}">
                            <i class="mdi mdi-chevron-right"></i>
                        </a>
                    </li>
                `;

                    $(config.paginationSelector).html(html);
                }

                // Event Listeners
                $(config.paginationSelector).on('click', 'a.page-link', function(e) {
                    e.preventDefault();
                    const p = parseInt($(this).data('page'));
                    if (p && p !== currentPage) {
                        currentPage = p;
                        render();
                    }
                });

                $(config.searchInput).on('input', function() {
                    searchQuery = $(this).val();
                    if (config.clearBtn) {
                        $(config.clearBtn).toggle(searchQuery.length > 0);
                    }
                    currentPage = 1;
                    render();
                });

                if (config.clearBtn) {
                    $(config.clearBtn).on('click', function() {
                        $(config.searchInput).val('');
                        searchQuery = '';
                        $(this).hide();
                        currentPage = 1;
                        render();
                    });
                }

                $(config.perPageSelector).on('change', function() {
                    perPage = parseInt($(this).val()) || 10;
                    currentPage = 1;
                    render();
                });

                return {
                    render: render,
                    setData: function(newData) {
                        rawData = newData || [];
                        render();
                    },
                    setExtraFilter: function(val) {
                        extraFilter = val;
                        currentPage = 1;
                        render();
                    }
                };
            }

            // ==========================================
            // 1. TABEL DEPARTEMEN (CLIENT SIDE)
            // ==========================================
            const tableDept = initClientPagination({
                initialData: orgData.departemens,
                tbodySelector: '#tbodyDept',
                infoSelector: '#infoDept',
                paginationSelector: '#paginationDept',
                searchInput: '#searchDept',
                clearBtn: '#btnClearSearchDept',
                perPageSelector: '#perPageDept',
                columnsCount: 7,
                emptyText: 'Belum ada data departemen yang cocok.',
                searchFn: (d, q) => {
                    return (d.kode || '').toLowerCase().includes(q) ||
                        (d.nama || '').toLowerCase().includes(q) ||
                        (d.deskripsi || '').toLowerCase().includes(q);
                },
                rowRenderer: (d, no) => {
                    const statusBadge = d.is_active ?
                        '<span class="badge bg-success-subtle text-success border border-success">Aktif</span>' :
                        '<span class="badge bg-secondary-subtle text-secondary border">Non-Aktif</span>';

                    return `
                    <tr>
                        <td class="text-center">${no}</td>
                        <td class="text-center"><span class="badge bg-primary font-monospace fs-6 px-2 py-1">${d.kode}</span></td>
                        <td><strong>${d.nama}</strong></td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-info">${d.bagians_count !== undefined ? d.bagians_count : (d.bagians ? d.bagians.length : 0)} Bagian</span>
                        </td>
                        <td class="small text-muted">${d.deskripsi || '-'}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-warning btnEditDept me-1" 
                                data-id="${d.id}" 
                                data-kode="${d.kode}" 
                                data-nama="${d.nama}" 
                                data-deskripsi="${d.deskripsi || ''}" 
                                data-active="${d.is_active ? 1 : 0}" 
                                title="Edit Departemen">
                                <i class="mdi mdi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btnDeleteDept" data-id="${d.id}" data-nama="${d.nama}" title="Hapus Departemen">
                                <i class="mdi mdi-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                `;
                }
            });
            tableDept.render();

            // ==========================================
            // 2. TABEL BAGIAN (CLIENT SIDE)
            // ==========================================
            const tableBagian = initClientPagination({
                initialData: orgData.bagians,
                tbodySelector: '#tbodyBagian',
                infoSelector: '#infoBagian',
                paginationSelector: '#paginationBagian',
                searchInput: '#searchBagian',
                clearBtn: '#btnClearSearchBagian',
                perPageSelector: '#perPageBagian',
                columnsCount: 7,
                emptyText: 'Belum ada data bagian yang cocok.',
                customFilter: (data, deptId) => {
                    if (!deptId) return data;
                    return data.filter(b => String(b.departemen_id) === String(deptId));
                },
                searchFn: (b, q) => {
                    const deptName = b.departemen ? b.departemen.nama : '';
                    return (b.nama || '').toLowerCase().includes(q) ||
                        (b.kode || '').toLowerCase().includes(q) ||
                        (deptName || '').toLowerCase().includes(q) ||
                        (b.deskripsi || '').toLowerCase().includes(q);
                },
                rowRenderer: (b, no) => {
                    const deptName = b.departemen ? b.departemen.nama : '-';
                    const statusBadge = b.is_active ?
                        '<span class="badge bg-success-subtle text-success border border-success">Aktif</span>' :
                        '<span class="badge bg-secondary-subtle text-secondary border">Non-Aktif</span>';

                    return `
                    <tr>
                        <td class="text-center">${no}</td>
                        <td>
                            <span class="badge badge-soft-primary border border-primary px-2 py-1">
                                ${deptName}
                            </span>
                        </td>
                        <td class="text-center font-monospace fw-bold">${b.kode || '-'}</td>
                        <td><strong>${b.nama}</strong></td>
                        <td class="small text-muted">${b.deskripsi || '-'}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-warning btnEditBagian me-1" 
                                data-id="${b.id}" 
                                data-dept="${b.departemen_id}" 
                                data-kode="${b.kode || ''}" 
                                data-nama="${b.nama}" 
                                data-deskripsi="${b.deskripsi || ''}" 
                                data-active="${b.is_active ? 1 : 0}" 
                                title="Edit Bagian">
                                <i class="mdi mdi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btnDeleteBagian" data-id="${b.id}" data-nama="${b.nama}" title="Hapus Bagian">
                                <i class="mdi mdi-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                `;
                }
            });
            tableBagian.render();

            // Filter Bagian Berdasarkan Departemen
            $('#filterDeptForBagian').on('change', function() {
                tableBagian.setExtraFilter($(this).val());
            });

            // ==========================================
            // 3. TABEL JABATAN (CLIENT SIDE)
            // ==========================================
            const tableJabatan = initClientPagination({
                initialData: orgData.jabatans,
                tbodySelector: '#tbodyJabatan',
                infoSelector: '#infoJabatan',
                paginationSelector: '#paginationJabatan',
                searchInput: '#searchJabatan',
                clearBtn: '#btnClearSearchJabatan',
                perPageSelector: '#perPageJabatan',
                columnsCount: 6,
                emptyText: 'Belum ada data jabatan yang cocok.',
                searchFn: (j, q) => {
                    return (j.kode || '').toLowerCase().includes(q) ||
                        (j.nama || '').toLowerCase().includes(q) ||
                        (j.deskripsi || '').toLowerCase().includes(q);
                },
                rowRenderer: (j, no) => {
                    const statusBadge = j.is_active ?
                        '<span class="badge bg-success-subtle text-success border border-success">Aktif</span>' :
                        '<span class="badge bg-secondary-subtle text-secondary border">Non-Aktif</span>';

                    return `
                    <tr>
                        <td class="text-center">${no}</td>
                        <td class="text-center font-monospace fw-bold">${j.kode}</td>
                        <td><strong>${j.nama}</strong></td>
                        <td class="small text-muted">${j.deskripsi || '-'}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-warning btnEditJabatan me-1" 
                                data-id="${j.id}" 
                                data-kode="${j.kode}" 
                                data-nama="${j.nama}" 
                                data-deskripsi="${j.deskripsi || ''}" 
                                data-active="${j.is_active ? 1 : 0}" 
                                title="Edit Jabatan">
                                <i class="mdi mdi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btnDeleteJabatan" data-id="${j.id}" data-nama="${j.nama}" title="Hapus Jabatan">
                                <i class="mdi mdi-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                `;
                }
            });
            tableJabatan.render();

            // ==========================================================
            // RELOAD DATA BY AJAX (SEAMLESS UPDATE TANPA RELOAD HALAMAN)
            // ==========================================================
            function loadAllData(callback) {
                $.get("{{ route('master.organization.data') }}", function(res) {
                    if (res.success) {
                        orgData.departemens = res.departemens;
                        orgData.bagians = res.bagians;
                        orgData.jabatans = res.jabatans;

                        // Update Tabel & Pagination
                        tableDept.setData(orgData.departemens);
                        tableBagian.setData(orgData.bagians);
                        tableJabatan.setData(orgData.jabatans);

                        // Update Badge Counters
                        $('#badgeCountDept').text(orgData.departemens.length);
                        $('#badgeCountBagian').text(orgData.bagians.length);
                        $('#badgeCountJabatan').text(orgData.jabatans.length);

                        // Update Dropdown Departemen di Modal Bagian & Filter
                        let deptOptions = '<option value="">Pilih Departemen...</option>';
                        let filterDeptOptions = '<option value="">Semua Departemen</option>';
                        orgData.departemens.forEach(d => {
                            deptOptions += `<option value="${d.id}">${d.nama} (${d.kode})</option>`;
                            filterDeptOptions +=
                                `<option value="${d.id}">${d.nama} (${d.kode})</option>`;
                        });
                        $('#bagianDeptId').html(deptOptions);

                        const curFilter = $('#filterDeptForBagian').val();
                        $('#filterDeptForBagian').html(filterDeptOptions).val(curFilter);

                        if (callback) callback();
                    }
                });
            }

            // ==========================================
            // DEPARTEMEN CRUD (AJAX)
            // ==========================================
            $('#btnOpenAddDept').on('click', function() {
                $('#formDept')[0].reset();
                $('#deptId').val('');
                $('#deptActive').prop('checked', true);
                $('#modalDeptTitle').text('Tambah Departemen Baru');
                $('#modalDept').modal('show');
            });

            $(document).on('click', '.btnEditDept', function() {
                const btn = $(this);
                $('#deptId').val(btn.data('id'));
                $('#deptKode').val(btn.data('kode'));
                $('#deptNama').val(btn.data('nama'));
                $('#deptDeskripsi').val(btn.data('deskripsi'));
                $('#deptActive').prop('checked', btn.data('active') == 1);
                $('#modalDeptTitle').text('Edit Departemen');
                $('#modalDept').modal('show');
            });

            $('#formDept').on('submit', function(e) {
                e.preventDefault();
                const id = $('#deptId').val();
                const url = id ? `{{ url('master/organization/departemen/update') }}/${id}` :
                    `{{ route('master.organization.departemen.store') }}`;
                const method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#modalDept').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success');
                        loadAllData();
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message ||
                            'Terjadi kesalahan sistem.', 'error');
                    }
                });
            });

            $(document).on('click', '.btnDeleteDept', function() {
                const id = $(this).data('id');
                const nama = $(this).data('nama');
                Swal.fire({
                    title: `Hapus Departemen ${nama}?`,
                    text: 'Bagian yang terhubung dengan departemen ini juga akan terhapus!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({
                            url: `{{ url('master/organization/departemen/delete') }}/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(r) {
                                Swal.fire('Terhapus', r.message, 'success');
                                loadAllData();
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal', xhr.responseJSON?.message ||
                                    'Gagal menghapus data.', 'error');
                            }
                        });
                    }
                });
            });

            // ==========================================
            // BAGIAN CRUD (AJAX)
            // ==========================================
            $('#btnOpenAddBagian').on('click', function() {
                $('#formBagian')[0].reset();
                $('#bagianId').val('');
                $('#bagianActive').prop('checked', true);
                $('#modalBagianTitle').text('Tambah Bagian Baru');
                $('#modalBagian').modal('show');
            });

            $(document).on('click', '.btnEditBagian', function() {
                const btn = $(this);
                $('#bagianId').val(btn.data('id'));
                $('#bagianDeptId').val(btn.data('dept'));
                $('#bagianKode').val(btn.data('kode'));
                $('#bagianNama').val(btn.data('nama'));
                $('#bagianDeskripsi').val(btn.data('deskripsi'));
                $('#bagianActive').prop('checked', btn.data('active') == 1);
                $('#modalBagianTitle').text('Edit Bagian');
                $('#modalBagian').modal('show');
            });

            $('#formBagian').on('submit', function(e) {
                e.preventDefault();
                const id = $('#bagianId').val();
                const url = id ? `{{ url('master/organization/bagian/update') }}/${id}` :
                    `{{ route('master.organization.bagian.store') }}`;
                const method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#modalBagian').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success');
                        loadAllData();
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message ||
                            'Terjadi kesalahan sistem.', 'error');
                    }
                });
            });

            $(document).on('click', '.btnDeleteBagian', function() {
                const id = $(this).data('id');
                const nama = $(this).data('nama');
                Swal.fire({
                    title: `Hapus Bagian ${nama}?`,
                    text: 'Data bagian ini akan dihapus dari sistem.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({
                            url: `{{ url('master/organization/bagian/delete') }}/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(r) {
                                Swal.fire('Terhapus', r.message, 'success');
                                loadAllData();
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal', xhr.responseJSON?.message ||
                                    'Gagal menghapus data.', 'error');
                            }
                        });
                    }
                });
            });

            // ==========================================
            // JABATAN CRUD (AJAX)
            // ==========================================
            $('#btnOpenAddJabatan').on('click', function() {
                $('#formJabatan')[0].reset();
                $('#jabatanId').val('');
                $('#jabatanActive').prop('checked', true);
                $('#modalJabatanTitle').text('Tambah Jabatan Baru');
                $('#modalJabatan').modal('show');
            });

            $(document).on('click', '.btnEditJabatan', function() {
                const btn = $(this);
                $('#jabatanId').val(btn.data('id'));
                $('#jabatanKode').val(btn.data('kode'));
                $('#jabatanNama').val(btn.data('nama'));
                $('#jabatanDeskripsi').val(btn.data('deskripsi'));
                $('#jabatanActive').prop('checked', btn.data('active') == 1);
                $('#modalJabatanTitle').text('Edit Jabatan');
                $('#modalJabatan').modal('show');
            });

            $('#formJabatan').on('submit', function(e) {
                e.preventDefault();
                const id = $('#jabatanId').val();
                const url = id ? `{{ url('master/organization/jabatan/update') }}/${id}` :
                    `{{ route('master.organization.jabatan.store') }}`;
                const method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#modalJabatan').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success');
                        loadAllData();
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message ||
                            'Terjadi kesalahan sistem.', 'error');
                    }
                });
            });

            $(document).on('click', '.btnDeleteJabatan', function() {
                const id = $(this).data('id');
                const nama = $(this).data('nama');
                Swal.fire({
                    title: `Hapus Jabatan ${nama}?`,
                    text: 'Data jabatan ini akan dihapus dari sistem.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({
                            url: `{{ url('master/organization/jabatan/delete') }}/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(r) {
                                Swal.fire('Terhapus', r.message, 'success');
                                loadAllData();
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal', xhr.responseJSON?.message ||
                                    'Gagal menghapus data.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
