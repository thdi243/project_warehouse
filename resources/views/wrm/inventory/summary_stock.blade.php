@extends('layouts.app')

@section('title', '- Summary Stock Inventory')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Summary Stock Inventory</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">WRM Inventory</a></li>
                                <li class="breadcrumb-item active">Summary Stock</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Stock Summary</h4>
                        </div>
                        <div class="card-body border border-dashed border-end-0 border-start-0">
                            <ul class="nav nav-tabs nav-justified nav-border-top mb-3 text-primary" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#summary-item-tab" role="tab"
                                        aria-selected="true">
                                        Per Item
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#summary-spb-tab" role="tab"
                                        aria-selected="false">
                                        Per No SPB
                                    </a>
                                </li>
                                @can('permission', 'wrm-summary-stock-by-group')
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" data-bs-toggle="tab" href="#summary-group-tab" role="tab"
                                            aria-selected="false">
                                            By Group
                                        </a>
                                    </li>
                                @endcan
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#summary-moving-average-tab"
                                        role="tab" aria-selected="false">
                                        Moving Average
                                    </a>
                                </li>
                                @can('permission', 'wrm-summary-stock-inbound-monthly')
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" data-bs-toggle="tab" href="#summary-inbound-monthly-tab"
                                            role="tab" aria-selected="false">
                                            Inbound Monthly
                                        </a>
                                    </li>
                                @endcan
                            </ul>

                            <div class="tab-content text-muted">
                                <div class="tab-pane active" id="summary-item-tab" role="tabpanel">
                                    <form id="filter-item-form">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-xxl-4 col-sm-6">
                                                <label class="form-label fw-semibold">Filter MID</label>
                                                <div class="dropdown custom-filter-dropdown" id="dropdown-mid">
                                                    <button
                                                        class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                                        type="button" data-bs-toggle="dropdown"
                                                        data-bs-auto-close="outside" aria-expanded="false">
                                                        <span class="dropdown-placeholder text-muted">Pilih MID...</span>
                                                        <span
                                                            class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                                    </button>
                                                    <div class="dropdown-menu p-3 shadow-lg border-0"
                                                        style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                                        <div class="mb-2">
                                                            <input type="text"
                                                                class="form-control form-control-sm search-options"
                                                                placeholder="Cari MID...">
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                                All</button>
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                                All</button>
                                                        </div>
                                                        <hr class="dropdown-divider my-2">
                                                        <div class="options-list"
                                                            style="max-height: 250px; overflow-y: auto;">
                                                            @foreach ($mids as $m)
                                                                <div class="form-check mb-2 option-item"
                                                                    data-value="{{ $m->mid }}"
                                                                    data-text="{{ $m->mid }} - {{ $m->nama_barang }}">
                                                                    <input class="form-check-input option-checkbox"
                                                                        type="checkbox" value="{{ $m->mid }}"
                                                                        id="chk-mid-item-{{ $m->mid }}">
                                                                    <label class="form-check-label text-truncate w-100"
                                                                        for="chk-mid-item-{{ $m->mid }}">
                                                                        {{ $m->mid }} - {{ $m->nama_barang }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-sm-6">
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-primary flex-fill"
                                                        id="btn-filter-item">
                                                        <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                                        Filter
                                                    </button>

                                                    <button type="button" class="btn btn-outline-danger flex-fill"
                                                        id="btnReset">
                                                        <i class="ri-refresh me-1 align-bottom"></i>
                                                        Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" id="summary-spb-tab" role="tabpanel">
                                    <form id="filter-spb-form">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-xxl-3 col-sm-4">
                                                <label class="form-label fw-semibold">Filter No SPB</label>
                                                <div class="dropdown custom-filter-dropdown" id="dropdown-no-spb">
                                                    <button
                                                        class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                                        type="button" data-bs-toggle="dropdown"
                                                        data-bs-auto-close="outside" aria-expanded="false">
                                                        <span class="dropdown-placeholder text-muted">Pilih No
                                                            SPB...</span>
                                                        <span
                                                            class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                                    </button>
                                                    <div class="dropdown-menu p-3 shadow-lg border-0"
                                                        style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                                        <div class="mb-2">
                                                            <input type="text"
                                                                class="form-control form-control-sm search-options"
                                                                placeholder="Cari No SPB...">
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                                All</button>
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                                All</button>
                                                        </div>
                                                        <hr class="dropdown-divider my-2">
                                                        <div class="options-list"
                                                            style="max-height: 250px; overflow-y: auto;">
                                                            @foreach ($spbs as $s)
                                                                <div class="form-check mb-2 option-item"
                                                                    data-value="{{ $s }}"
                                                                    data-text="{{ $s }}">
                                                                    <input class="form-check-input option-checkbox"
                                                                        type="checkbox" value="{{ $s }}"
                                                                        id="chk-spb-{{ $s }}">
                                                                    <label class="form-check-label text-truncate w-100"
                                                                        for="chk-spb-{{ $s }}">
                                                                        {{ $s }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-4 col-sm-4">
                                                <label class="form-label fw-semibold">Filter MID</label>
                                                <div class="dropdown custom-filter-dropdown" id="dropdown-mid-spb">
                                                    <button
                                                        class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                                        type="button" data-bs-toggle="dropdown"
                                                        data-bs-auto-close="outside" aria-expanded="false">
                                                        <span class="dropdown-placeholder text-muted">Pilih MID...</span>
                                                        <span
                                                            class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                                    </button>
                                                    <div class="dropdown-menu p-3 shadow-lg border-0"
                                                        style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                                        <div class="mb-2">
                                                            <input type="text"
                                                                class="form-control form-control-sm search-options"
                                                                placeholder="Cari MID...">
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                                All</button>
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                                All</button>
                                                        </div>
                                                        <hr class="dropdown-divider my-2">
                                                        <div class="options-list"
                                                            style="max-height: 250px; overflow-y: auto;">
                                                            @foreach ($mids as $m)
                                                                <div class="form-check mb-2 option-item"
                                                                    data-value="{{ $m->mid }}"
                                                                    data-text="{{ $m->mid }} - {{ $m->nama_barang }}">
                                                                    <input class="form-check-input option-checkbox"
                                                                        type="checkbox" value="{{ $m->mid }}"
                                                                        id="chk-mid-spb-{{ $m->mid }}">
                                                                    <label class="form-check-label text-truncate w-100"
                                                                        for="chk-mid-spb-{{ $m->mid }}">
                                                                        {{ $m->mid }} - {{ $m->nama_barang }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-sm-4">
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-primary flex-fill"
                                                        id="btn-filter-spb">
                                                        <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                                        Filter
                                                    </button>

                                                    <button type="button" class="btn btn-outline-danger flex-fill"
                                                        id="btnResetSpb">
                                                        <i class="ri-refresh me-1 align-bottom"></i>
                                                        Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" id="summary-group-tab" role="tabpanel">
                                    <form id="filter-group-form">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-xxl-3 col-sm-4">
                                                <label class="form-label fw-semibold">Filter Group</label>
                                                <div class="dropdown custom-filter-dropdown" id="dropdown-group-group">
                                                    <button
                                                        class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                                        type="button" data-bs-toggle="dropdown"
                                                        data-bs-auto-close="outside" aria-expanded="false">
                                                        <span class="dropdown-placeholder text-muted">Pilih Group...</span>
                                                        <span
                                                            class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                                    </button>
                                                    <div class="dropdown-menu p-3 shadow-lg border-0"
                                                        style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                                        <div class="mb-2">
                                                            <input type="text"
                                                                class="form-control form-control-sm search-options"
                                                                placeholder="Cari Group...">
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                                All</button>
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                                All</button>
                                                        </div>
                                                        <hr class="dropdown-divider my-2">
                                                        <div class="options-list"
                                                            style="max-height: 250px; overflow-y: auto;">
                                                            @foreach ($groups as $g)
                                                                <div class="form-check mb-2 option-item"
                                                                    data-value="{{ $g }}"
                                                                    data-text="{{ $g }}">
                                                                    <input class="form-check-input option-checkbox"
                                                                        type="checkbox" value="{{ $g }}"
                                                                        id="chk-group-{{ $g }}">
                                                                    <label class="form-check-label text-truncate w-100"
                                                                        for="chk-group-{{ $g }}">
                                                                        {{ $g }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-4 col-sm-4">
                                                <label class="form-label fw-semibold">Filter MID</label>
                                                <div class="dropdown custom-filter-dropdown" id="dropdown-mid-group">
                                                    <button
                                                        class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                                        type="button" data-bs-toggle="dropdown"
                                                        data-bs-auto-close="outside" aria-expanded="false">
                                                        <span class="dropdown-placeholder text-muted">Pilih MID...</span>
                                                        <span
                                                            class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                                    </button>
                                                    <div class="dropdown-menu p-3 shadow-lg border-0"
                                                        style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                                        <div class="mb-2">
                                                            <input type="text"
                                                                class="form-control form-control-sm search-options"
                                                                placeholder="Cari MID...">
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                                All</button>
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                                All</button>
                                                        </div>
                                                        <hr class="dropdown-divider my-2">
                                                        <div class="options-list"
                                                            style="max-height: 250px; overflow-y: auto;">
                                                            @foreach ($mids as $m)
                                                                <div class="form-check mb-2 option-item"
                                                                    data-value="{{ $m->mid }}"
                                                                    data-text="{{ $m->mid }} - {{ $m->nama_barang }}">
                                                                    <input class="form-check-input option-checkbox"
                                                                        type="checkbox" value="{{ $m->mid }}"
                                                                        id="chk-mid-group-{{ $m->mid }}"
                                                                        @if (in_array($m->mid, ['20000812', '20000860', '20001270'])) checked @endif>
                                                                    <label class="form-check-label text-truncate w-100"
                                                                        for="chk-mid-group-{{ $m->mid }}">
                                                                        {{ $m->mid }} - {{ $m->nama_barang }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-sm-4">
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-primary flex-fill"
                                                        id="btn-filter-group">
                                                        <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                                        Filter
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger flex-fill"
                                                        id="btnResetGroup">
                                                        <i class="ri-refresh me-1 align-bottom"></i>
                                                        Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" id="summary-moving-average-tab" role="tabpanel">
                                    <form id="filter-moving-average-form">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-xxl-4 col-sm-4">
                                                <label class="form-label fw-semibold">Filter MID</label>
                                                <div class="dropdown custom-filter-dropdown" id="dropdown-mid-ma">
                                                    <button
                                                        class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                                        type="button" data-bs-toggle="dropdown"
                                                        data-bs-auto-close="outside" aria-expanded="false">
                                                        <span class="dropdown-placeholder text-muted">Pilih MID...</span>
                                                        <span
                                                            class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                                    </button>
                                                    <div class="dropdown-menu p-3 shadow-lg border-0"
                                                        style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                                        <div class="mb-2">
                                                            <input type="text"
                                                                class="form-control form-control-sm search-options"
                                                                placeholder="Cari MID...">
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                                All</button>
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                                All</button>
                                                        </div>
                                                        <hr class="dropdown-divider my-2">
                                                        <div class="options-list"
                                                            style="max-height: 250px; overflow-y: auto;">
                                                            @foreach ($mids as $m)
                                                                <div class="form-check mb-2 option-item"
                                                                    data-value="{{ $m->mid }}"
                                                                    data-text="{{ $m->mid }} - {{ $m->nama_barang }}">
                                                                    <input class="form-check-input option-checkbox"
                                                                        type="checkbox" value="{{ $m->mid }}"
                                                                        id="chk-mid-ma-{{ $m->mid }}">
                                                                    <label class="form-check-label text-truncate w-100"
                                                                        for="chk-mid-ma-{{ $m->mid }}">
                                                                        {{ $m->mid }} - {{ $m->nama_barang }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-3 col-sm-4">
                                                <label class="form-label fw-semibold">Hari</label>
                                                <select class="form-select" id="filter-days-ma">
                                                    <option value="20">20 Hari</option>
                                                    <option value="30" selected>30 Hari</option>
                                                    <option value="40">40 Hari</option>
                                                </select>
                                            </div>
                                            <div class="col-xxl-2 col-sm-4">
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-primary flex-fill"
                                                        id="btn-filter-ma">
                                                        <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                                        Filter
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger flex-fill"
                                                        id="btnResetMa">
                                                        <i class="ri-refresh me-1 align-bottom"></i>
                                                        Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" id="summary-inbound-monthly-tab" role="tabpanel">
                                    <form id="filter-inbound-monthly-form">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-xxl-4 col-sm-6">
                                                <label class="form-label fw-semibold">Filter MID</label>
                                                <div class="dropdown custom-filter-dropdown" id="dropdown-mid-inbound">
                                                    <button
                                                        class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                                        type="button" data-bs-toggle="dropdown"
                                                        data-bs-auto-close="outside" aria-expanded="false">
                                                        <span class="dropdown-placeholder text-muted">Pilih MID...</span>
                                                        <span
                                                            class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                                    </button>
                                                    <div class="dropdown-menu p-3 shadow-lg border-0"
                                                        style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                                        <div class="mb-2">
                                                            <input type="text"
                                                                class="form-control form-control-sm search-options"
                                                                placeholder="Cari MID...">
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                                All</button>
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                                All</button>
                                                        </div>
                                                        <hr class="dropdown-divider my-2">
                                                        <div class="options-list"
                                                            style="max-height: 250px; overflow-y: auto;">
                                                            @foreach ($mids as $m)
                                                                <div class="form-check mb-2 option-item"
                                                                    data-value="{{ $m->mid }}"
                                                                    data-text="{{ $m->mid }} - {{ $m->nama_barang }}">
                                                                    <input class="form-check-input option-checkbox"
                                                                        type="checkbox" value="{{ $m->mid }}"
                                                                        id="chk-mid-inbound-{{ $m->mid }}"
                                                                        @if (in_array($m->mid, ['20000812', '20000860', '20001270'])) checked @endif>
                                                                    <label class="form-check-label text-truncate w-100"
                                                                        for="chk-mid-inbound-{{ $m->mid }}">
                                                                        {{ $m->mid }} - {{ $m->nama_barang }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-3 col-sm-6">
                                                <label class="form-label fw-semibold">Filter Bulan</label>
                                                <div class="dropdown custom-filter-dropdown" id="dropdown-month-inbound">
                                                    <button
                                                        class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                                        type="button" data-bs-toggle="dropdown"
                                                        data-bs-auto-close="outside" aria-expanded="false">
                                                        <span class="dropdown-placeholder text-muted">Pilih Bulan...</span>
                                                    </button>
                                                    <div class="dropdown-menu p-3 shadow-lg border-0"
                                                        style="min-width: 240px; max-height: 400px; overflow: hidden;">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                                All</button>
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                                All</button>
                                                        </div>
                                                        <hr class="dropdown-divider my-2">
                                                        <div class="options-list"
                                                            style="max-height: 250px; overflow-y: auto;">
                                                            @foreach ($inboundMonths as $val => $name)
                                                                <div class="form-check mb-2 option-item"
                                                                    data-value="{{ $val }}"
                                                                    data-text="{{ $name }}">
                                                                    <input class="form-check-input option-checkbox"
                                                                        type="checkbox" value="{{ $val }}"
                                                                        id="chk-month-inbound-{{ $val }}"
                                                                        @if (in_array($val, $defaultMonths)) checked @endif>
                                                                    <label class="form-check-label text-truncate w-100"
                                                                        for="chk-month-inbound-{{ $val }}">
                                                                        {{ $name }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-3 col-sm-6">
                                                <label class="form-label fw-semibold">Filter Tahun</label>
                                                <div class="dropdown custom-filter-dropdown" id="dropdown-year-inbound">
                                                    <button
                                                        class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                                        type="button" data-bs-toggle="dropdown"
                                                        data-bs-auto-close="outside" aria-expanded="false">
                                                        <span class="dropdown-placeholder text-muted">Pilih Tahun...</span>
                                                    </button>
                                                    <div class="dropdown-menu p-3 shadow-lg border-0"
                                                        style="min-width: 240px; max-height: 400px; overflow: hidden;">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                                All</button>
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                                All</button>
                                                        </div>
                                                        <hr class="dropdown-divider my-2">
                                                        <div class="options-list"
                                                            style="max-height: 250px; overflow-y: auto;">
                                                            @foreach ($inboundYears as $yr)
                                                                <div class="form-check mb-2 option-item"
                                                                    data-value="{{ $yr }}"
                                                                    data-text="{{ $yr }}">
                                                                    <input class="form-check-input option-checkbox"
                                                                        type="checkbox" value="{{ $yr }}"
                                                                        id="chk-year-inbound-{{ $yr }}"
                                                                        @if (in_array($yr, $defaultYears)) checked @endif>
                                                                    <label class="form-check-label text-truncate w-100"
                                                                        for="chk-year-inbound-{{ $yr }}">
                                                                        {{ $yr }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-sm-6">
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-primary flex-fill"
                                                        id="btn-filter-inbound-monthly">
                                                        <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                                        Filter
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger flex-fill"
                                                        id="btnResetInboundMonthly">
                                                        <i class="ri-refresh me-1 align-bottom"></i>
                                                        Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="tab-content text-muted px-4">
                                <div class="tab-pane active" id="summary-item-table-tab" role="tabpanel">
                                    <div class="table-responsive table-card mb-4">
                                        <table class="table table-stripped align-middle table-nowrap mb-0"
                                            id="table-summary-item">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>MID</th>
                                                    <th>Nama Barang</th>
                                                    <th>UoM</th>
                                                    <th class="text-end">Qty Unrest</th>
                                                    <th class="text-end">Qty QI</th>
                                                    <th class="text-end">Qty Blocked</th>
                                                    <th class="text-end">Total Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {{-- Data will be loaded via Ajax --}}
                                            </tbody>
                                            <tfoot class="table-light fw-semibold" id="table-item-footer">
                                                {{-- Footer rows will be generated dynamically --}}
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div id="table-item-pagination" class="px-3 pb-3"></div>
                                </div>

                                <div class="tab-pane" id="summary-spb-table-tab" role="tabpanel">
                                    <div class="table-responsive table-card mb-4">
                                        <table class="table align-middle table-nowrap mb-0" id="table-summary-spb">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>No SPB</th>
                                                    <th>UoM</th>
                                                    <th class="text-end">Qty Unrest</th>
                                                    <th class="text-end">Qty QI</th>
                                                    <th class="text-end">Qty Blocked</th>
                                                    <th class="text-end">Total Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {{-- Data will be loaded via Ajax --}}
                                            </tbody>
                                            <tfoot class="table-light fw-semibold" id="table-spb-footer">
                                                {{-- Footer rows will be generated dynamically --}}
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div id="table-spb-pagination" class="px-3 pb-3"></div>
                                </div>

                                <div class="tab-pane" id="summary-group-table-tab" role="tabpanel">
                                    <div class="table-responsive table-card mb-4">
                                        <table class="table table-striped align-middle table-nowrap mb-0"
                                            id="table-summary-group" style="width: 100%;">
                                            {{-- Will be generated dynamically via AJAX --}}
                                        </table>
                                    </div>
                                    <div id="table-group-pagination" class="px-3 pb-3"></div>
                                </div>

                                <div class="tab-pane" id="summary-moving-average-table-tab" role="tabpanel">
                                    <div class="table-responsive table-card mb-4">
                                        <table class="table align-middle table-nowrap mb-0" id="table-summary-ma"
                                            style="width:100%;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>MID</th>
                                                    <th>Nama Barang</th>
                                                    <th>UoM</th>
                                                    <th class="text-end">Stock Transfer</th>
                                                    <th class="text-end">Average</th>
                                                    <th class="text-end">Stock On Hand</th>
                                                    <th class="text-center">Cover Days</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {{-- Data will be loaded via Ajax --}}
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="table-ma-pagination" class="px-3 pb-3"></div>
                                </div>

                                <div class="tab-pane" id="summary-inbound-monthly-table-tab" role="tabpanel">
                                    <div class="table-responsive table-card mb-4">
                                        <table class="table table-striped align-middle table-nowrap mb-0"
                                            id="table-summary-inbound-monthly" style="width:100%;">
                                            {{-- Will be dynamically generated --}}
                                        </table>
                                    </div>
                                    <div id="table-inbound-monthly-pagination" class="px-3 pb-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SPB Detail Modal -->
    <div class="modal fade" id="modalSpbDetail" tabindex="-1" aria-labelledby="modalSpbDetailLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold" id="modalSpbDetailLabel">
                        <i class="ri-article-line me-2 text-primary"></i> Detail Stock SPB: <span id="spbDetailNumber"
                            class="text-primary"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="table-responsive rounded shadow-sm">
                        <table class="table table-bordered table-striped align-middle text-nowrap mb-0"
                            id="tableSpbDetail">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="50">No</th>
                                    <th>Pallet ID</th>
                                    <th>MID</th>
                                    <th>Nama Barang</th>
                                    <th class="text-end">Qty (KG)</th>
                                    <th>Status</th>
                                    <th>Lokasi</th>
                                    <th>Supplier</th>
                                    <th>Tanggal Masuk</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/wrm/inventory/summary_stock.js') }}"></script>
@endsection
