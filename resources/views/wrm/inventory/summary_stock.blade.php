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
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#summary-inbound-monthly-tab"
                                        role="tab" aria-selected="false">
                                        Inbound Monthly
                                    </a>
                                </li>
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
                                                                        @if(in_array($m->mid, ['20000812', '20000860', '20001270'])) checked @endif>
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
                                                                        @if(in_array($val, $defaultMonths)) checked @endif>
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
                                                                        @if(in_array($yr, $defaultYears)) checked @endif>
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
    <script>
        $(document).ready(function() {
            const formatNumber = {
                display: function(data) {
                    if (data === null || data === undefined || data === '') {
                        return '-';
                    }
                    const number = parseFloat(data);
                    // jika desimal = 0
                    if (number % 1 === 0) {
                        return number.toLocaleString('id-ID', {
                            maximumFractionDigits: 0
                        });
                    }
                    // jika ada desimal
                    return number.toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    });
                }
            };

            function calculatePageTotalsPerUom(data) {
                const totals = {};
                data.forEach(function(row) {
                    const uom = row.uom || '-';
                    if (!totals[uom]) {
                        totals[uom] = {
                            uom: uom,
                            unrest: 0,
                            qi: 0,
                            blocked: 0,
                            all: 0
                        };
                    }
                    const unrest = parseFloat(row.qty_unrest || 0);
                    const qi = parseFloat(row.qty_qi || 0);
                    const blocked = parseFloat(row.qty_blocked || 0);
                    totals[uom].unrest += unrest;
                    totals[uom].qi += qi;
                    totals[uom].blocked += blocked;
                    totals[uom].all += (unrest + qi + blocked);
                });
                return Object.values(totals);
            }

            function calculateGroupPageTotalsPerUom(data, activeGroups, hasNoGroup) {
                const totals = {};
                data.forEach(function(row) {
                    const uom = row.uom || '-';
                    if (!totals[uom]) {
                        totals[uom] = {
                            uom: uom,
                            total_qty: 0
                        };
                        activeGroups.forEach(function(g) {
                            const alias = 'group_' + g.replace(/[^a-zA-Z0-9_]/g, '_');
                            totals[uom][alias] = 0;
                        });
                        if (hasNoGroup) {
                            totals[uom]['group_none'] = 0;
                        }
                    }

                    activeGroups.forEach(function(g) {
                        const alias = 'group_' + g.replace(/[^a-zA-Z0-9_]/g, '_');
                        totals[uom][alias] += parseFloat(row[alias] || 0);
                    });

                    if (hasNoGroup) {
                        totals[uom]['group_none'] += parseFloat(row['group_none'] || 0);
                    }

                    totals[uom].total_qty += parseFloat(row.total_qty || 0);
                });
                return Object.values(totals);
            }

            function renderPageFooter(selector, pageTotals, totalColspan) {
                let footerHtml = '';
                const totalRows = pageTotals.length;

                pageTotals.forEach(function(item, index) {
                    footerHtml += `<tr>`;

                    if (index === 0) {
                        footerHtml += `
                            <td colspan="${totalColspan}"
                                rowspan="${totalRows}"
                                class="text-center align-middle fw-bold">
                                Total (This Page)
                            </td>
                        `;
                    }

                    footerHtml += `
                            <td class="text-start fw-bold">${item.uom}</td>
                            <td class="text-end fw-bold">${formatNumber.display(item.unrest)}</td>
                            <td class="text-end fw-bold">${formatNumber.display(item.qi)}</td>
                            <td class="text-end fw-bold">${formatNumber.display(item.blocked)}</td>
                            <td class="text-end fw-bold text-success">${formatNumber.display(item.all)}</td>
                        </tr>
                    `;
                });

                $(selector).html(footerHtml);
            }

            function renderGroupPageFooter(selector, pageTotals, activeGroups, hasNoGroup) {
                let footerHtml = '';
                const totalRows = pageTotals.length;

                pageTotals.forEach(function(item, index) {
                    footerHtml += `<tr>`;

                    if (index === 0) {
                        footerHtml += `
                            <td colspan="2"
                                rowspan="${totalRows}"
                                class="text-center align-middle fw-bold">
                                Total (This Page)
                            </td>
                        `;
                    }

                    footerHtml += `<td class="text-start fw-bold">${item.uom}</td>`;

                    activeGroups.forEach(function(g) {
                        const alias = 'group_' + g.replace(/[^a-zA-Z0-9_]/g, '_');
                        const val = item[alias] || 0;
                        footerHtml +=
                            `<td class="text-end fw-bold">${formatNumber.display(val)}</td>`;
                    });

                    if (hasNoGroup) {
                        const val = item['group_none'] || 0;
                        footerHtml += `<td class="text-end fw-bold">${formatNumber.display(val)}</td>`;
                    }

                    footerHtml +=
                        `<td class="text-end fw-bold text-success">${formatNumber.display(item.total_qty || 0)}</td>`;
                    footerHtml += `</tr>`;
                });

                $(selector).html(footerHtml);
            }

            function calculateInboundMonthlyPageTotalsPerUom(data, activeMonths) {
                const totals = {};
                data.forEach(function(row) {
                    const uom = row.uom || '-';
                    if (!totals[uom]) {
                        totals[uom] = {
                            uom: uom,
                            total_qty: 0
                        };
                        activeMonths.forEach(function(my) {
                            const alias = 'ym_' + my.year + '_' + ('0' + my.month).slice(-2);
                            totals[uom][alias] = 0;
                        });
                    }
                    activeMonths.forEach(function(my) {
                        const alias = 'ym_' + my.year + '_' + ('0' + my.month).slice(-2);
                        totals[uom][alias] += parseFloat(row[alias] || 0);
                    });
                    totals[uom].total_qty += parseFloat(row.total_qty || 0);
                });
                return Object.values(totals);
            }

            function renderInboundMonthlyPageFooter(selector, pageTotals, activeMonths) {
                let footerHtml = '';
                const totalRows = pageTotals.length;

                pageTotals.forEach(function(item, index) {
                    footerHtml += `<tr>`;

                    if (index === 0) {
                        footerHtml += `
                            <td colspan="2"
                                rowspan="${totalRows}"
                                class="text-center align-middle fw-bold">
                                Total (This Page)
                            </td>
                        `;
                    }

                    footerHtml += `<td class="text-start fw-bold">${item.uom}</td>`;

                    activeMonths.forEach(function(my) {
                        const alias = 'ym_' + my.year + '_' + ('0' + my.month).slice(-2);
                        const val = item[alias] || 0;
                        footerHtml += `<td class="text-end fw-bold">${formatNumber.display(val)}</td>`;
                    });

                    footerHtml += `<td class="text-end fw-bold text-success">${formatNumber.display(item.total_qty || 0)}</td>`;
                    footerHtml += `</tr>`;
                });

                $(selector).html(footerHtml);
            }

            function renderPagination(containerSelector, recordsTotal, start, length, onPageChange) {
                const container = $(containerSelector);
                container.empty();

                if (recordsTotal === 0) {
                    return;
                }

                const currentPage = Math.floor(start / length) + 1;
                const totalPages = Math.ceil(recordsTotal / length);

                const from = start + 1;
                const textLimit = start + length;
                const to = Math.min(textLimit, recordsTotal);
                const infoText = `Showing ${from} to ${to} of ${recordsTotal} entries`;

                let paginationHtml = `
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3">
                        <div class="text-muted small">${infoText}</div>
                        <nav>
                            <ul class="pagination pagination-rounded mb-0">
                `;

                const prevDisabled = currentPage === 1 ? 'disabled' : '';
                paginationHtml += `
                    <li class="page-item ${prevDisabled}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                    </li>
                `;

                const maxVisiblePages = 5;
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
                if (endPage - startPage + 1 < maxVisiblePages) {
                    startPage = Math.max(1, endPage - maxVisiblePages + 1);
                }

                if (startPage > 1) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="1">1</a>
                        </li>
                    `;
                    if (startPage > 2) {
                        paginationHtml += `
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        `;
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    const activeClass = i === currentPage ? 'active' : '';
                    paginationHtml += `
                        <li class="page-item ${activeClass}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        paginationHtml += `
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        `;
                    }
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
                        </li>
                    `;
                }

                const nextDisabled = currentPage === totalPages ? 'disabled' : '';
                paginationHtml += `
                    <li class="page-item ${nextDisabled}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                    </li>
                `;

                paginationHtml += `
                            </ul>
                        </nav>
                    </div>
                `;

                container.html(paginationHtml);

                container.find('.page-link').on('click', function(e) {
                    e.preventDefault();
                    const parent = $(this).parent();
                    if (parent.hasClass('disabled') || parent.hasClass('active')) {
                        return;
                    }
                    const pageNum = parseInt($(this).data('page'));
                    if (!isNaN(pageNum)) {
                        const newStart = (pageNum - 1) * length;
                        onPageChange(newStart);
                    }
                });
            }

            // Custom dropdown filter helper
            function initCustomDropdown(id, placeholder, onChange) {
                const $dropdown = $('#' + id);
                const $button = $dropdown.find('.dropdown-toggle');
                const $placeholderSpan = $button.find('.dropdown-placeholder');
                const $badge = $button.find('.selected-count');
                const $searchInput = $dropdown.find('.search-options');
                const $optionsList = $dropdown.find('.options-list');
                const $checkboxes = $optionsList.find('.option-checkbox');

                // Cache initial checked state
                $checkboxes.each(function() {
                    $(this).data('initial-checked', $(this).prop('checked'));
                });

                function updateLabel(triggerCallback = true) {
                    const selected = [];
                    $checkboxes.filter(':checked').each(function() {
                        selected.push($(this).val());
                    });

                    if (selected.length === 0) {
                        $placeholderSpan.text(placeholder);
                    } else {
                        $placeholderSpan.text(`${selected.length} Terpilih`);
                    }

                    if (triggerCallback && onChange && !isResetting) {
                        onChange(selected);
                    }
                }

                // Search options
                $searchInput.on('input', function() {
                    const query = $(this).val().toLowerCase();
                    $dropdown.find('.option-item').each(function() {
                        const text = $(this).data('text').toString().toLowerCase();
                        const val = $(this).data('value').toString().toLowerCase();
                        if (text.indexOf(query) > -1 || val.indexOf(query) > -1) {
                            $(this).removeClass('d-none');
                        } else {
                            $(this).addClass('d-none');
                        }
                    });
                });

                // Checkbox changes
                $checkboxes.on('change', function() {
                    updateLabel(true);
                });

                // Select All
                $dropdown.find('.select-all-options').on('click', function(e) {
                    e.preventDefault();
                    $optionsList.find('.option-item:not(.d-none) .option-checkbox').prop('checked', true);
                    updateLabel(true);
                });

                // Clear All
                $dropdown.find('.clear-all-options').on('click', function(e) {
                    e.preventDefault();
                    $checkboxes.prop('checked', false);
                    updateLabel(true);
                });

                // Attach methods to the DOM element
                $dropdown.data('getValues', function() {
                    const selected = [];
                    $checkboxes.filter(':checked').each(function() {
                        selected.push($(this).val());
                    });
                    return selected;
                });

                $dropdown.data('reset', function() {
                    $checkboxes.each(function() {
                        $(this).prop('checked', $(this).data('initial-checked') || false);
                    });
                    $searchInput.val('').trigger('input');
                    updateLabel(false);
                });

                // Set initial label
                updateLabel(false);
            }

            // State management
            let itemStart = 0;
            const itemLength = 15;

            let spbStart = 0;
            const spbLength = 15;

            let groupStart = 0;
            const groupLength = 15;
            let activeGroupsGlobal = [];
            let hasNoGroupGlobal = false;

            let maStart = 0;
            const maLength = 15;

            let inboundMonthlyStart = 0;
            const inboundMonthlyLength = 15;
            let activeMonthsGlobal = [];

            let isResetting = false;

            // Initialize all custom dropdown filters
            initCustomDropdown('dropdown-mid', 'Pilih MID...', function() {
                loadItemTable(0);
            });

            initCustomDropdown('dropdown-no-spb', 'Pilih No SPB...', function() {
                loadSpbTable(0);
            });

            initCustomDropdown('dropdown-mid-spb', 'Pilih MID...', function() {
                loadSpbTable(0);
            });

            initCustomDropdown('dropdown-group-group', 'Pilih Group...', function() {
                loadGroupTable(0, true);
            });

            initCustomDropdown('dropdown-mid-group', 'Pilih MID...', function() {
                loadGroupTable(0, true);
            });

            initCustomDropdown('dropdown-mid-ma', 'Pilih MID...', function() {
                loadMaTable(0);
            });

            initCustomDropdown('dropdown-mid-inbound', 'Pilih MID...', function() {
                loadInboundMonthlyTable(0, true);
            });

            initCustomDropdown('dropdown-month-inbound', 'Pilih Bulan...', function() {
                loadInboundMonthlyTable(0, true);
            });

            initCustomDropdown('dropdown-year-inbound', 'Pilih Tahun...', function() {
                loadInboundMonthlyTable(0, true);
            });

            // Table loaders
            function loadItemTable(start = 0) {
                itemStart = start;
                const mids = $('#dropdown-mid').data('getValues')();

                const $tbody = $('#table-summary-item tbody');
                $tbody.html(
                    '<tr><td colspan="7" class="text-center py-4 text-muted"><i class="ri-loader-4-line ri-spin me-2 fs-5"></i>Loading data...</td></tr>'
                );

                $.ajax({
                    url: "{{ route('wrm.inventory.monitoring.summary-stock.item-data') }}",
                    type: 'GET',
                    data: {
                        draw: 1,
                        start: itemStart,
                        length: itemLength,
                        mids: mids
                    },
                    dataType: 'json',
                    success: function(response) {
                        $tbody.empty();
                        const data = response.data || [];

                        if (data.length === 0) {
                            $tbody.html(
                                '<tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data.</td></tr>'
                            );
                            $('#table-item-footer').empty();
                            $('#table-item-pagination').empty();
                            return;
                        }

                        let html = '';
                        data.forEach(function(row) {
                            const total = parseFloat(row.qty_unrest || 0) + parseFloat(row
                                .qty_qi || 0) + parseFloat(row.qty_blocked || 0);
                            html += `
                                <tr>
                                    <td>${row.mid || '-'}</td>
                                    <td>${row.nama_barang || '-'}</td>
                                    <td>${row.uom || '-'}</td>
                                    <td class="text-end">${formatNumber.display(row.qty_unrest)}</td>
                                    <td class="text-end">${formatNumber.display(row.qty_qi)}</td>
                                    <td class="text-end">${formatNumber.display(row.qty_blocked)}</td>
                                    <td class="text-end fw-bold">${formatNumber.display(total)}</td>
                                </tr>
                            `;
                        });
                        $tbody.html(html);

                        // Render Footer (Page Totals)
                        const pageTotals = calculatePageTotalsPerUom(data);
                        renderPageFooter('#table-item-footer', pageTotals, 2);

                        // Render Pagination
                        renderPagination('#table-item-pagination', response.recordsTotal, itemStart,
                            itemLength,
                            function(newStart) {
                                loadItemTable(newStart);
                            });
                    },
                    error: function(xhr, status, error) {
                        $tbody.html(
                            `<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat data: ${error}</td></tr>`
                        );
                    }
                });
            }

            function loadSpbTable(start = 0) {
                spbStart = start;
                const no_spbs = $('#dropdown-no-spb').data('getValues')();
                const mids = $('#dropdown-mid-spb').data('getValues')();

                const $tbody = $('#table-summary-spb tbody');
                $tbody.html(
                    '<tr><td colspan="6" class="text-center py-4 text-muted"><i class="ri-loader-4-line ri-spin me-2 fs-5"></i>Loading data...</td></tr>'
                );

                $.ajax({
                    url: "{{ route('wrm.inventory.monitoring.summary-stock.spb-data') }}",
                    type: 'GET',
                    data: {
                        draw: 1,
                        start: spbStart,
                        length: spbLength,
                        no_spbs: no_spbs,
                        mids: mids
                    },
                    dataType: 'json',
                    success: function(response) {
                        $tbody.empty();
                        const data = response.data || [];

                        if (data.length === 0) {
                            $tbody.html(
                                '<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data.</td></tr>'
                            );
                            $('#table-spb-footer').empty();
                            $('#table-spb-pagination').empty();
                            return;
                        }

                        let html = '';
                        data.forEach(function(row) {
                            const total = parseFloat(row.qty_unrest || 0) + parseFloat(row
                                .qty_qi || 0) + parseFloat(row.qty_blocked || 0);
                            const noSpbLink = row.no_spb ?
                                `<a href="#" class="fw-bold text-primary show-spb-detail" data-spb="${row.no_spb}">${row.no_spb}</a>` :
                                '-';
                            html += `
                                <tr>
                                    <td>${noSpbLink}</td>
                                    <td>${row.uom || '-'}</td>
                                    <td class="text-end">${formatNumber.display(row.qty_unrest)}</td>
                                    <td class="text-end">${formatNumber.display(row.qty_qi)}</td>
                                    <td class="text-end">${formatNumber.display(row.qty_blocked)}</td>
                                    <td class="text-end fw-bold">${formatNumber.display(total)}</td>
                                </tr>
                            `;
                        });
                        $tbody.html(html);

                        // Render Footer (Page Totals)
                        const pageTotals = calculatePageTotalsPerUom(data);
                        renderPageFooter('#table-spb-footer', pageTotals, 1);

                        // Render Pagination
                        renderPagination('#table-spb-pagination', response.recordsTotal, spbStart,
                            spbLength,
                            function(newStart) {
                                loadSpbTable(newStart);
                            });
                    },
                    error: function(xhr, status, error) {
                        $tbody.html(
                            `<tr><td colspan="6" class="text-center text-danger py-4">Gagal memuat data: ${error}</td></tr>`
                        );
                    }
                });
            }

            function loadGroupTable(start = 0, forceRebuildHeader = false) {
                groupStart = start;
                const mids = $('#dropdown-mid-group').data('getValues')();
                const groups = $('#dropdown-group-group').data('getValues')();

                const $table = $('#table-summary-group');

                if (forceRebuildHeader || activeGroupsGlobal.length === 0) {
                    $table.html(
                        '<thead><tr><th colspan="4" class="text-center py-4 text-muted"><i class="ri-loader-4-line ri-spin me-2 fs-5"></i>Loading meta...</th></tr></thead>'
                    );

                    $.ajax({
                        url: "{{ route('wrm.inventory.monitoring.summary-stock.group-meta') }}",
                        type: 'GET',
                        data: {
                            mids: mids,
                            groups: groups
                        },
                        success: function(response) {
                            activeGroupsGlobal = response.active_groups || [];
                            hasNoGroupGlobal = response.has_no_group || false;

                            let theadHtml = '<tr>';
                            theadHtml += '<th>MID</th>';
                            theadHtml += '<th>Nama Barang</th>';
                            theadHtml += '<th>UoM</th>';

                            activeGroupsGlobal.forEach(function(g) {
                                theadHtml += `<th class="text-end">${g}</th>`;
                            });

                            if (hasNoGroupGlobal) {
                                theadHtml += '<th class="text-end">No Group</th>';
                            }

                            theadHtml += '<th class="text-end">Total Qty</th>';
                            theadHtml += '</tr>';

                            $table.empty().append(
                                `<thead class="table-light">${theadHtml}</thead>` +
                                `<tbody></tbody>` +
                                `<tfoot class="table-light fw-semibold" id="table-group-footer"></tfoot>`
                            );

                            fetchGroupData();
                        },
                        error: function(xhr, status, error) {
                            $table.html(
                                `<thead><tr><th class="text-danger py-4 text-center">Gagal memuat meta data: ${error}</th></tr></thead>`
                            );
                        }
                    });
                } else {
                    fetchGroupData();
                }

                function fetchGroupData() {
                    const $tbody = $table.find('tbody');
                    $tbody.html(
                        `<tr><td colspan="${4 + activeGroupsGlobal.length + (hasNoGroupGlobal ? 1 : 0)}" class="text-center py-4 text-muted"><i class="ri-loader-4-line ri-spin me-2 fs-5"></i>Loading data...</td></tr>`
                    );

                    $.ajax({
                        url: "{{ route('wrm.inventory.monitoring.summary-stock.group-data') }}",
                        type: 'GET',
                        data: {
                            draw: 1,
                            start: groupStart,
                            length: groupLength,
                            mids: mids,
                            groups: groups
                        },
                        dataType: 'json',
                        success: function(response) {
                            $tbody.empty();
                            const data = response.data || [];

                            if (data.length === 0) {
                                $tbody.html(
                                    `<tr><td colspan="${4 + activeGroupsGlobal.length + (hasNoGroupGlobal ? 1 : 0)}" class="text-center py-4 text-muted">Tidak ada data.</td></tr>`
                                );
                                $('#table-group-footer').empty();
                                $('#table-group-pagination').empty();
                                return;
                            }

                            let html = '';
                            data.forEach(function(row) {
                                html += '<tr>';
                                html += `<td>${row.mid || '-'}</td>`;
                                html += `<td>${row.nama_barang || '-'}</td>`;
                                html += `<td>${row.uom || '-'}</td>`;

                                activeGroupsGlobal.forEach(function(g) {
                                    const alias = 'group_' + g.replace(/[^a-zA-Z0-9_]/g,
                                        '_');
                                    const val = parseFloat(row[alias] || 0);
                                    html +=
                                        `<td class="text-end">${formatNumber.display(val)}</td>`;
                                });

                                if (hasNoGroupGlobal) {
                                    const val = parseFloat(row.group_none || 0);
                                    html +=
                                        `<td class="text-end">${formatNumber.display(val)}</td>`;
                                }

                                html +=
                                    `<td class="text-end fw-bold">${formatNumber.display(row.total_qty || 0)}</td>`;
                                html += '</tr>';
                            });
                            $tbody.html(html);

                            // Render Footer
                            const pageTotals = calculateGroupPageTotalsPerUom(data, activeGroupsGlobal,
                                hasNoGroupGlobal);
                            renderGroupPageFooter('#table-group-footer', pageTotals, activeGroupsGlobal,
                                hasNoGroupGlobal);

                            // Render Pagination
                            renderPagination('#table-group-pagination', response.recordsTotal,
                                groupStart, groupLength,
                                function(newStart) {
                                    loadGroupTable(newStart, false);
                                });
                        },
                        error: function(xhr, status, error) {
                            $tbody.html(
                                `<tr><td colspan="${4 + activeGroupsGlobal.length + (hasNoGroupGlobal ? 1 : 0)}" class="text-center text-danger py-4">Gagal memuat data: ${error}</td></tr>`
                            );
                        }
                    });
                }
            }

            function loadMaTable(start = 0) {
                maStart = start;
                const mids = $('#dropdown-mid-ma').data('getValues')();
                const days = $('#filter-days-ma').val();

                const $tbody = $('#table-summary-ma tbody');
                $tbody.html(
                    '<tr><td colspan="8" class="text-center py-4 text-muted"><i class="ri-loader-4-line ri-spin me-2 fs-5"></i>Loading data...</td></tr>'
                );

                $.ajax({
                    url: "{{ route('wrm.inventory.monitoring.moving-average.data') }}",
                    type: 'GET',
                    data: {
                        draw: 1,
                        start: maStart,
                        length: maLength,
                        mids: mids,
                        days: days
                    },
                    dataType: 'json',
                    success: function(response) {
                        $tbody.empty();
                        const data = response.data || [];

                        if (data.length === 0) {
                            $tbody.html(
                                '<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data.</td></tr>'
                            );
                            $('#table-ma-pagination').empty();
                            return;
                        }

                        let html = '';
                        data.forEach(function(row) {
                            html += `
                                <tr>
                                    <td>${row.mid || '-'}</td>
                                    <td>${row.nama_barang || '-'}</td>
                                    <td>${row.uom || '-'}</td>
                                    <td class="text-end">${formatNumber.display(row.total_used)}</td>
                                    <td class="text-end">${formatNumber.display(row.avg_daily)}</td>
                                    <td class="text-end">${formatNumber.display(row.available)}</td>
                                    <td class="text-center fw-semibold">${row.cover_days || '-'}</td>
                                    <td class="text-center">${row.status_label || '-'}</td>
                                </tr>
                            `;
                        });
                        $tbody.html(html);

                        // Render Pagination
                        renderPagination('#table-ma-pagination', response.recordsTotal, maStart,
                            maLength,
                            function(newStart) {
                                loadMaTable(newStart);
                            });
                    },
                    error: function(xhr, status, error) {
                        $tbody.html(
                            `<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data: ${error}</td></tr>`
                        );
                    }
                });
            }

            function loadInboundMonthlyTable(start = 0, forceRebuildHeader = false) {
                inboundMonthlyStart = start;
                const mids = $('#dropdown-mid-inbound').data('getValues')();
                const months = $('#dropdown-month-inbound').data('getValues')();
                const years = $('#dropdown-year-inbound').data('getValues')();

                const $table = $('#table-summary-inbound-monthly');

                if (forceRebuildHeader || activeMonthsGlobal.length === 0) {
                    $table.html(
                        '<thead><tr><th colspan="4" class="text-center py-4 text-muted"><i class="ri-loader-4-line ri-spin me-2 fs-5"></i>Loading meta...</th></tr></thead>'
                    );

                    $.ajax({
                        url: "{{ route('wrm.inventory.monitoring.summary-stock.inbound-monthly-meta') }}",
                        type: 'GET',
                        data: {
                            mids: mids,
                            months: months,
                            years: years
                        },
                        success: function(response) {
                            activeMonthsGlobal = response.active_month_years || [];

                            let theadHtml = '<tr>';
                            theadHtml += '<th>MID</th>';
                            theadHtml += '<th>Nama Barang</th>';
                            theadHtml += '<th>UoM</th>';

                            activeMonthsGlobal.forEach(function(my) {
                                theadHtml += `<th class="text-end">${my.label}</th>`;
                            });

                            theadHtml += '<th class="text-end">Total Qty</th>';
                            theadHtml += '</tr>';

                            $table.empty().append(
                                `<thead class="table-light">${theadHtml}</thead>` +
                                `<tbody></tbody>` +
                                `<tfoot class="table-light fw-semibold" id="table-inbound-monthly-footer"></tfoot>`
                            );

                            fetchInboundMonthlyData();
                        },
                        error: function(xhr, status, error) {
                            $table.html(
                                `<thead><tr><th class="text-danger py-4 text-center">Gagal memuat meta data: ${error}</th></tr></thead>`
                            );
                        }
                    });
                } else {
                    fetchInboundMonthlyData();
                }

                function fetchInboundMonthlyData() {
                    const $tbody = $table.find('tbody');
                    $tbody.html(
                        `<tr><td colspan="${4 + activeMonthsGlobal.length}" class="text-center py-4 text-muted"><i class="ri-loader-4-line ri-spin me-2 fs-5"></i>Loading data...</td></tr>`
                    );

                    $.ajax({
                        url: "{{ route('wrm.inventory.monitoring.summary-stock.inbound-monthly-data') }}",
                        type: 'GET',
                        data: {
                            draw: 1,
                            start: inboundMonthlyStart,
                            length: inboundMonthlyLength,
                            mids: mids,
                            months: months,
                            years: years
                        },
                        dataType: 'json',
                        success: function(response) {
                            $tbody.empty();
                            const data = response.data || [];

                            if (data.length === 0) {
                                $tbody.html(
                                    `<tr><td colspan="${4 + activeMonthsGlobal.length}" class="text-center py-4 text-muted">Tidak ada data.</td></tr>`
                                );
                                $('#table-inbound-monthly-footer').empty();
                                $('#table-inbound-monthly-pagination').empty();
                                return;
                            }

                            let html = '';
                            data.forEach(function(row) {
                                html += '<tr>';
                                html += `<td>${row.mid || '-'}</td>`;
                                html += `<td>${row.nama_barang || '-'}</td>`;
                                html += `<td>${row.uom || '-'}</td>`;

                                activeMonthsGlobal.forEach(function(my) {
                                    const alias = 'ym_' + my.year + '_' + ('0' + my.month).slice(-2);
                                    const val = parseFloat(row[alias] || 0);
                                    html += `<td class="text-end">${formatNumber.display(val)}</td>`;
                                });

                                html += `<td class="text-end fw-bold">${formatNumber.display(row.total_qty || 0)}</td>`;
                                html += '</tr>';
                            });
                            $tbody.html(html);

                            // Render Footer
                            const pageTotals = calculateInboundMonthlyPageTotalsPerUom(data, activeMonthsGlobal);
                            renderInboundMonthlyPageFooter('#table-inbound-monthly-footer', pageTotals, activeMonthsGlobal);

                            // Render Pagination
                            renderPagination('#table-inbound-monthly-pagination', response.recordsTotal,
                                inboundMonthlyStart, inboundMonthlyLength,
                                function(newStart) {
                                    loadInboundMonthlyTable(newStart, false);
                                });
                        },
                        error: function(xhr, status, error) {
                            $tbody.html(
                                `<tr><td colspan="${4 + activeMonthsGlobal.length}" class="text-center text-danger py-4">Gagal memuat data: ${error}</td></tr>`
                            );
                        }
                    });
                }
            }

            // Load initial tab data
            loadItemTable(0);

            // Tab switching handler
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).attr('href');
                if (target === '#summary-item-tab') {
                    $('#summary-item-table-tab').addClass('active show');
                    $('#summary-spb-table-tab').removeClass('active show');
                    $('#summary-group-table-tab').removeClass('active show');
                    $('#summary-moving-average-table-tab').removeClass('active show');
                    $('#summary-inbound-monthly-table-tab').removeClass('active show');
                    loadItemTable(0);
                }

                if (target === '#summary-spb-tab') {
                    $('#summary-spb-table-tab').addClass('active show');
                    $('#summary-item-table-tab').removeClass('active show');
                    $('#summary-group-table-tab').removeClass('active show');
                    $('#summary-moving-average-table-tab').removeClass('active show');
                    $('#summary-inbound-monthly-table-tab').removeClass('active show');
                    loadSpbTable(0);
                }

                if (target === '#summary-group-tab') {
                    $('#summary-group-table-tab').addClass('active show');
                    $('#summary-item-table-tab').removeClass('active show');
                    $('#summary-spb-table-tab').removeClass('active show');
                    $('#summary-moving-average-table-tab').removeClass('active show');
                    $('#summary-inbound-monthly-table-tab').removeClass('active show');
                    loadGroupTable(0, true);
                }

                if (target === '#summary-moving-average-tab') {
                    $('#summary-moving-average-table-tab').addClass('active show');
                    $('#summary-item-table-tab').removeClass('active show');
                    $('#summary-spb-table-tab').removeClass('active show');
                    $('#summary-group-table-tab').removeClass('active show');
                    $('#summary-inbound-monthly-table-tab').removeClass('active show');
                    loadMaTable(0);
                }

                if (target === '#summary-inbound-monthly-tab') {
                    $('#summary-inbound-monthly-table-tab').addClass('active show');
                    $('#summary-item-table-tab').removeClass('active show');
                    $('#summary-spb-table-tab').removeClass('active show');
                    $('#summary-group-table-tab').removeClass('active show');
                    $('#summary-moving-average-table-tab').removeClass('active show');
                    loadInboundMonthlyTable(0, true);
                }
            });

            // Filter button handlers
            $('#btn-filter-item').on('click', function() {
                loadItemTable(0);
            });

            $('#btn-filter-spb').on('click', function() {
                loadSpbTable(0);
            });

            $('#btn-filter-group').on('click', function() {
                loadGroupTable(0, true);
            });

            $('#btn-filter-ma').on('click', function() {
                loadMaTable(0);
            });

            $('#btn-filter-inbound-monthly').on('click', function() {
                loadInboundMonthlyTable(0, true);
            });

            // Auto-reload on standard select change
            $('#filter-days-ma').on('change', function() {
                if (!isResetting) loadMaTable(0);
            });

            // Reset button handlers
            $('#btnReset').on('click', function() {
                isResetting = true;
                $('#dropdown-mid').data('reset')();
                isResetting = false;
                loadItemTable(0);
            });

            // Reset button handlers for SPB
            $('#btnResetSpb').on('click', function() {
                isResetting = true;
                $('#dropdown-no-spb').data('reset')();
                $('#dropdown-mid-spb').data('reset')();
                isResetting = false;
                loadSpbTable(0);
            });

            // Reset button handlers for Group
            $('#btnResetGroup').on('click', function() {
                isResetting = true;
                $('#dropdown-group-group').data('reset')();
                $('#dropdown-mid-group').data('reset')();
                isResetting = false;
                loadGroupTable(0, true);
            });

            // Reset button handlers for Moving Average
            $('#btnResetMa').on('click', function() {
                isResetting = true;
                $('#dropdown-mid-ma').data('reset')();
                $('#filter-days-ma').val('30');
                isResetting = false;
                loadMaTable(0);
            });

            // Reset button handlers for Inbound Monthly
            $('#btnResetInboundMonthly').on('click', function() {
                isResetting = true;
                $('#dropdown-mid-inbound').data('reset')();
                $('#dropdown-month-inbound').data('reset')();
                $('#dropdown-year-inbound').data('reset')();
                isResetting = false;
                loadInboundMonthlyTable(0, true);
            });

            // Handle clicking SPB detail link
            $(document).on('click', '.show-spb-detail', function(e) {
                e.preventDefault();
                const spbNumber = $(this).data('spb');

                $('#spbDetailNumber').text(spbNumber);
                const $tbody = $('#tableSpbDetail tbody');
                $tbody.html(
                    '<tr><td colspan="9" class="text-center py-4 text-muted"><i class="ri-loader-4-line ri-spin me-2"></i>Loading details...</td></tr>'
                );

                const myModal = new bootstrap.Modal(document.getElementById('modalSpbDetail'));
                myModal.show();

                $.ajax({
                    url: "{{ route('wrm.inventory.monitoring.spb-detail.data') }}",
                    type: 'GET',
                    data: {
                        no_spb: spbNumber
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status && res.data) {
                            $tbody.empty();
                            if (res.data.length === 0) {
                                $tbody.append(
                                    '<tr><td colspan="9" class="text-center py-4 text-muted">No stock items found for this SPB.</td></tr>'
                                );
                            } else {
                                let html = '';
                                res.data.forEach((item, index) => {
                                    let locStr = '-';
                                    if (item.bin && item.bin.location) {
                                        let l = item.bin.location;
                                        locStr =
                                            `${l.plant} - ${l.gudang} - ${l.bin} (${item.bin.kolom}.${item.bin.level})`;
                                    }
                                    let incomingDateStr = item.incoming_date ? item
                                        .incoming_date.substring(0, 10) : '-';
                                    html += `
                                        <tr>
                                            <td class="text-center">${index + 1}</td>
                                            <td><b class="text-primary">${item.pallet_id ?? '-'}</b></td>
                                            <td>${item.barang ? item.barang.mid : '-'}</td>
                                            <td>${item.barang ? item.barang.nama_barang : '-'}</td>
                                            <td class="text-end fw-bold">${formatNumber.display(item.qty)}</td>
                                            <td><span class="badge bg-soft-info text-info">${item.status}</span></td>
                                            <td class="small">${locStr}</td>
                                            <td>${item.supplier ?? '-'}</td>
                                            <td>${incomingDateStr}</td>
                                        </tr>
                                    `;
                                });
                                $tbody.html(html);
                            }
                        } else {
                            $tbody.html(
                                '<tr><td colspan="9" class="text-center text-danger py-4">Gagal memuat detail data.</td></tr>'
                            );
                        }
                    },
                    error: function() {
                        $tbody.html(
                            '<tr><td colspan="9" class="text-center text-danger py-4">Terjadi kesalahan koneksi.</td></tr>'
                        );
                    }
                });
            });
        });
    </script>
@endsection
