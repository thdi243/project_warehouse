$(document).ready(function () {
    const formatNumber = {
        display: function (data) {
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
        data.forEach(function (row) {
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
        data.forEach(function (row) {
            const uom = row.uom || '-';
            if (!totals[uom]) {
                totals[uom] = {
                    uom: uom,
                    total_qty: 0
                };
                activeGroups.forEach(function (g) {
                    const alias = 'group_' + g.replace(/[^a-zA-Z0-9_]/g, '_');
                    totals[uom][alias] = 0;
                });
                if (hasNoGroup) {
                    totals[uom]['group_none'] = 0;
                }
            }

            activeGroups.forEach(function (g) {
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

        pageTotals.forEach(function (item, index) {
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

        pageTotals.forEach(function (item, index) {
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

            activeGroups.forEach(function (g) {
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
        data.forEach(function (row) {
            const uom = row.uom || '-';
            if (!totals[uom]) {
                totals[uom] = {
                    uom: uom,
                    total_qty: 0
                };
                activeMonths.forEach(function (my) {
                    const alias = 'ym_' + my.year + '_' + ('0' + my.month).slice(-2);
                    totals[uom][alias] = 0;
                });
            }
            activeMonths.forEach(function (my) {
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

        pageTotals.forEach(function (item, index) {
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

            activeMonths.forEach(function (my) {
                const alias = 'ym_' + my.year + '_' + ('0' + my.month).slice(-2);
                const val = item[alias] || 0;
                footerHtml +=
                    `<td class="text-end fw-bold">${formatNumber.display(val)}</td>`;
            });

            footerHtml +=
                `<td class="text-end fw-bold text-success">${formatNumber.display(item.total_qty || 0)}</td>`;
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

        container.find('.page-link').on('click', function (e) {
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
        $checkboxes.each(function () {
            $(this).data('initial-checked', $(this).prop('checked'));
        });

        function updateLabel(triggerCallback = true) {
            const selected = [];
            $checkboxes.filter(':checked').each(function () {
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
        $searchInput.on('input', function () {
            const query = $(this).val().toLowerCase();
            $dropdown.find('.option-item').each(function () {
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
        $checkboxes.on('change', function () {
            updateLabel(true);
        });

        // Select All
        $dropdown.find('.select-all-options').on('click', function (e) {
            e.preventDefault();
            $optionsList.find('.option-item:not(.d-none) .option-checkbox').prop('checked', true);
            updateLabel(true);
        });

        // Clear All
        $dropdown.find('.clear-all-options').on('click', function (e) {
            e.preventDefault();
            $checkboxes.prop('checked', false);
            updateLabel(true);
        });

        // Attach methods to the DOM element
        $dropdown.data('getValues', function () {
            const selected = [];
            $checkboxes.filter(':checked').each(function () {
                selected.push($(this).val());
            });
            return selected;
        });

        $dropdown.data('reset', function () {
            $checkboxes.each(function () {
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
    initCustomDropdown('dropdown-mid', 'Pilih MID...', function () {
        loadItemTable(0);
    });

    initCustomDropdown('dropdown-no-spb', 'Pilih No SPB...', function () {
        loadSpbTable(0);
    });

    initCustomDropdown('dropdown-mid-spb', 'Pilih MID...', function () {
        loadSpbTable(0);
    });

    initCustomDropdown('dropdown-group-group', 'Pilih Group...', function () {
        loadGroupTable(0, true);
    });

    initCustomDropdown('dropdown-mid-group', 'Pilih MID...', function () {
        loadGroupTable(0, true);
    });

    initCustomDropdown('dropdown-mid-ma', 'Pilih MID...', function () {
        loadMaTable(0);
    });

    initCustomDropdown('dropdown-mid-inbound', 'Pilih MID...', function () {
        loadInboundMonthlyTable(0, true);
    });

    initCustomDropdown('dropdown-month-inbound', 'Pilih Bulan...', function () {
        loadInboundMonthlyTable(0, true);
    });

    initCustomDropdown('dropdown-year-inbound', 'Pilih Tahun...', function () {
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
            url: "/wrm/inventory/monitoring/data/summary-stock/item",
            type: 'GET',
            data: {
                draw: 1,
                start: itemStart,
                length: itemLength,
                mids: mids
            },
            dataType: 'json',
            success: function (response) {
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
                data.forEach(function (row) {
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
                    function (newStart) {
                        loadItemTable(newStart);
                    });
            },
            error: function (xhr, status, error) {
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
            url: "/wrm/inventory/monitoring/data/summary-stock/spb",
            type: 'GET',
            data: {
                draw: 1,
                start: spbStart,
                length: spbLength,
                no_spbs: no_spbs,
                mids: mids
            },
            dataType: 'json',
            success: function (response) {
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
                data.forEach(function (row) {
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
                    function (newStart) {
                        loadSpbTable(newStart);
                    });
            },
            error: function (xhr, status, error) {
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
                url: "/wrm/inventory/monitoring/data/summary-stock/group-meta",
                type: 'GET',
                data: {
                    mids: mids,
                    groups: groups
                },
                success: function (response) {
                    activeGroupsGlobal = response.active_groups || [];
                    hasNoGroupGlobal = response.has_no_group || false;

                    let theadHtml = '<tr>';
                    theadHtml += '<th>MID</th>';
                    theadHtml += '<th>Nama Barang</th>';
                    theadHtml += '<th>UoM</th>';

                    activeGroupsGlobal.forEach(function (g) {
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
                error: function (xhr, status, error) {
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
                url: "/wrm/inventory/monitoring/data/summary-stock/group",
                type: 'GET',
                data: {
                    draw: 1,
                    start: groupStart,
                    length: groupLength,
                    mids: mids,
                    groups: groups
                },
                dataType: 'json',
                success: function (response) {
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
                    data.forEach(function (row) {
                        html += '<tr>';
                        html += `<td>${row.mid || '-'}</td>`;
                        html += `<td>${row.nama_barang || '-'}</td>`;
                        html += `<td>${row.uom || '-'}</td>`;

                        activeGroupsGlobal.forEach(function (g) {
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
                        function (newStart) {
                            loadGroupTable(newStart, false);
                        });
                },
                error: function (xhr, status, error) {
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
            url: "/wrm/inventory/monitoring/data/moving-average",
            type: 'GET',
            data: {
                draw: 1,
                start: maStart,
                length: maLength,
                mids: mids,
                days: days
            },
            dataType: 'json',
            success: function (response) {
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
                data.forEach(function (row) {
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
                    function (newStart) {
                        loadMaTable(newStart);
                    });
            },
            error: function (xhr, status, error) {
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
                url: "/wrm/inventory/monitoring/data/summary-stock/inbound-monthly-meta",
                type: 'GET',
                data: {
                    mids: mids,
                    months: months,
                    years: years
                },
                success: function (response) {
                    activeMonthsGlobal = response.active_month_years || [];

                    let theadHtml = '<tr>';
                    theadHtml += '<th>MID</th>';
                    theadHtml += '<th>Nama Barang</th>';
                    theadHtml += '<th>UoM</th>';

                    activeMonthsGlobal.forEach(function (my) {
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
                error: function (xhr, status, error) {
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
                url: "/wrm/inventory/monitoring/data/summary-stock/inbound-monthly",
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
                success: function (response) {
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
                    data.forEach(function (row) {
                        html += '<tr>';
                        html += `<td>${row.mid || '-'}</td>`;
                        html += `<td>${row.nama_barang || '-'}</td>`;
                        html += `<td>${row.uom || '-'}</td>`;

                        activeMonthsGlobal.forEach(function (my) {
                            const alias = 'ym_' + my.year + '_' + ('0' + my
                                .month).slice(-2);
                            const val = parseFloat(row[alias] || 0);
                            html +=
                                `<td class="text-end">${formatNumber.display(val)}</td>`;
                        });

                        html +=
                            `<td class="text-end fw-bold">${formatNumber.display(row.total_qty || 0)}</td>`;
                        html += '</tr>';
                    });
                    $tbody.html(html);

                    // Render Footer
                    const pageTotals = calculateInboundMonthlyPageTotalsPerUom(data,
                        activeMonthsGlobal);
                    renderInboundMonthlyPageFooter('#table-inbound-monthly-footer', pageTotals,
                        activeMonthsGlobal);

                    // Render Pagination
                    renderPagination('#table-inbound-monthly-pagination', response.recordsTotal,
                        inboundMonthlyStart, inboundMonthlyLength,
                        function (newStart) {
                            loadInboundMonthlyTable(newStart, false);
                        });
                },
                error: function (xhr, status, error) {
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
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
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
    $('#btn-filter-item').on('click', function () {
        loadItemTable(0);
    });

    $('#btn-filter-spb').on('click', function () {
        loadSpbTable(0);
    });

    $('#btn-filter-group').on('click', function () {
        loadGroupTable(0, true);
    });

    $('#btn-filter-ma').on('click', function () {
        loadMaTable(0);
    });

    $('#btn-filter-inbound-monthly').on('click', function () {
        loadInboundMonthlyTable(0, true);
    });

    // Auto-reload on standard select change
    $('#filter-days-ma').on('change', function () {
        if (!isResetting) loadMaTable(0);
    });

    // Reset button handlers
    $('#btnReset').on('click', function () {
        isResetting = true;
        $('#dropdown-mid').data('reset')();
        isResetting = false;
        loadItemTable(0);
    });

    // Reset button handlers for SPB
    $('#btnResetSpb').on('click', function () {
        isResetting = true;
        $('#dropdown-no-spb').data('reset')();
        $('#dropdown-mid-spb').data('reset')();
        isResetting = false;
        loadSpbTable(0);
    });

    // Reset button handlers for Group
    $('#btnResetGroup').on('click', function () {
        isResetting = true;
        $('#dropdown-group-group').data('reset')();
        $('#dropdown-mid-group').data('reset')();
        isResetting = false;
        loadGroupTable(0, true);
    });

    // Reset button handlers for Moving Average
    $('#btnResetMa').on('click', function () {
        isResetting = true;
        $('#dropdown-mid-ma').data('reset')();
        $('#filter-days-ma').val('30');
        isResetting = false;
        loadMaTable(0);
    });

    // Reset button handlers for Inbound Monthly
    $('#btnResetInboundMonthly').on('click', function () {
        isResetting = true;
        $('#dropdown-mid-inbound').data('reset')();
        $('#dropdown-month-inbound').data('reset')();
        $('#dropdown-year-inbound').data('reset')();
        isResetting = false;
        loadInboundMonthlyTable(0, true);
    });

    // Handle clicking SPB detail link
    $(document).on('click', '.show-spb-detail', function (e) {
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
            url: "/wrm/inventory/monitoring/data/spb-detail",
            type: 'GET',
            data: {
                no_spb: spbNumber
            },
            dataType: 'json',
            success: function (res) {
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
            error: function () {
                $tbody.html(
                    '<tr><td colspan="9" class="text-center text-danger py-4">Terjadi kesalahan koneksi.</td></tr>'
                );
            }
        });
    });
});