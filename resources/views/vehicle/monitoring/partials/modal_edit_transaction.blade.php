<!-- Edit Transaction Modal (Foreman & Timbangan) -->
<div class="modal fade" id="editModal" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="ri-edit-line me-2 align-middle text-warning"></i>Edit Data Transaksi Kendaraan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_no_pol" class="form-label">No. Polisi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_no_pol" name="no_pol" required style="text-transform: uppercase;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_vendor" class="form-label">Nama Vendor</label>
                            <select class="form-select" id="edit_vendor" name="vendor">
                                <option value="" selected disabled>Pilih Vendor</option>
                                @foreach ($vendors ?? [] as $vendor)
                                    <option value="{{ $vendor->name }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_nama_driver" class="form-label">Nama Driver</label>
                            <input type="text" class="form-control" id="edit_nama_driver" name="nama_driver" placeholder="Masukkan nama driver">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_no_hp_driver" class="form-label">No. HP Driver</label>
                            <input type="text" class="form-control" id="edit_no_hp_driver" name="no_hp_driver" placeholder="Contoh: 08123456789">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_jenis" class="form-label">Jenis <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_jenis" name="jenis" required>
                                <option value="bongkaran">Bongkaran</option>
                                <option value="slipsheet">Slipsheet</option>
                                <option value="curah">Curah</option>
                                <option value="retur">Retur</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_target_location_id" class="form-label">Area (Sloc) <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_target_location_id" name="target_location_id" required>
                                @foreach ($targetLocations ?? [] as $loc)
                                    <option value="{{ $loc->id }}" data-sloc="{{ $loc->s_loc }}">
                                        {{ $loc->s_loc }} - {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="edit_item_id" class="form-label">Item <span class="text-danger">*</span></label>
                            <select class="form-select select2-edit" id="edit_item_id" name="item_id" required>
                                @foreach (($allItems ?? $items ?? []) as $item)
                                    <option value="{{ $item->id }}" data-location-id="{{ $item->location_id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_no_spb" class="form-label">No. SPB</label>
                            <input type="text" class="form-control" id="edit_no_spb" name="no_spb" placeholder="Nomor Surat Perintah Bongkar">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_qty_spb" class="form-label">Qty SPB</label>
                            <input type="number" class="form-control" id="edit_qty_spb" name="qty_spb" step="any" placeholder="10000">
                        </div>
                    </div>
                    <input type="hidden" id="edit_checkin_pos1" name="checkin_pos1">
                    <input type="hidden" id="edit_trnvisitorid" name="trnvisitorid">
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jQuery === 'undefined') return;

        // Inisialisasi Select2 di dalam Edit Modal
        if ($('#edit_item_id').length) {
            $('#edit_item_id').select2({
                dropdownParent: $('#editModal'),
                allowClear: true,
                width: '100%'
            });
        }

        if ($('#edit_vendor').length) {
            $('#edit_vendor').select2({
                dropdownParent: $('#editModal'),
                tags: true,
                placeholder: 'Pilih atau Ketik Vendor Baru',
                allowClear: true,
                width: '100%'
            });
        }

        const allItemsListForEdit = @json($allItems ?? $items ?? []);

        function filterTargetLocationsForEdit() {
            const jenis = $('#edit_jenis').val();
            const targetSelect = $('#edit_target_location_id');

            targetSelect.find('option').each(function() {
                const option = $(this);
                const sloc = option.data('sloc');
                if (!sloc) return;

                if (jenis === 'slipsheet' || jenis === 'curah') {
                    if (sloc === 'A001') {
                        option.prop('disabled', false).show();
                    } else {
                        option.prop('disabled', true).hide();
                    }
                } else if (jenis === 'bongkaran' || jenis === 'retur') {
                    if (sloc === 'B006' || sloc === 'C001' || sloc === 'A002' || sloc === 'SMU') {
                        option.prop('disabled', false).show();
                    } else {
                        option.prop('disabled', true).hide();
                    }
                } else {
                    option.prop('disabled', false).show();
                }
            });

            const currentSelected = targetSelect.find('option:selected');
            if (currentSelected.prop('disabled')) {
                const firstValid = targetSelect.find('option:not(:disabled):first');
                if (firstValid.length) {
                    targetSelect.val(firstValid.val());
                }
            }
        }

        function filterItemsByLocationForEdit() {
            const targetLocId = $('#edit_target_location_id').val();
            const itemSelect = $('#edit_item_id');
            const currentVal = itemSelect.val();

            itemSelect.empty();
            itemSelect.append('<option value="" selected disabled>Pilih Item</option>');

            let matchedItems = allItemsListForEdit;
            if (targetLocId) {
                matchedItems = allItemsListForEdit.filter(function(item) {
                    return !item.location_id || String(item.location_id) === String(targetLocId);
                });
            }

            matchedItems.forEach(function(item) {
                const selected = String(item.id) === String(currentVal) ? 'selected' : '';
                itemSelect.append(`<option value="${item.id}" data-location-id="${item.location_id || ''}" ${selected}>${item.name}</option>`);
            });

            itemSelect.trigger('change.select2');
        }

        $('#edit_jenis').on('change', function() {
            filterTargetLocationsForEdit();
            filterItemsByLocationForEdit();
        });

        $('#edit_target_location_id').on('change', function() {
            filterItemsByLocationForEdit();
        });

        // Sinkronisasi data-id ke tombol edit di dalam Modal Detail saat No. Polisi diklik
        $(document).on('click', '.btn-detail-truck', function() {
            const id = $(this).data('id');
            if (id) {
                $('.btn-edit-from-detail').attr('data-id', id).data('id', id);
            }
        });

        // Tutup Modal Detail ketika tombol Edit dari Modal Detail diklik
        $(document).on('click', '.btn-edit-from-detail', function() {
            const modalDetailEl = document.getElementById('modalDetailTruck');
            if (modalDetailEl) {
                const modalDetail = bootstrap.Modal.getInstance(modalDetailEl);
                if (modalDetail) modalDetail.hide();
            }
        });

        // Click Handler Edit Button
        $(document).on('click', '.btn-edit-ajax', function() {
            const id = $(this).data('id');
            const btn = $(this);
            btn.prop('disabled', true);

            $.ajax({
                url: `{{ url('vehicle-monitoring/timbangan/show') }}/${id}`,
                type: 'GET',
                success: function(response) {
                    btn.prop('disabled', false);

                    if (response.success && response.data) {
                        const tx = response.data;

                        $('#edit_no_pol').val(tx.no_pol);
                        $('#edit_jenis').val(tx.jenis.toLowerCase());
                        filterTargetLocationsForEdit();
                        $('#edit_target_location_id').val(tx.target_loc);
                        filterItemsByLocationForEdit();
                        $('#edit_item_id').val(tx.item_id).trigger('change');

                        if (tx.vendor) {
                            if ($("#edit_vendor").find("option[value='" + tx.vendor + "']").length === 0) {
                                var newOption = new Option(tx.vendor, tx.vendor, true, true);
                                $("#edit_vendor").append(newOption).trigger('change');
                            } else {
                                $("#edit_vendor").val(tx.vendor).trigger('change');
                            }
                        } else {
                            $("#edit_vendor").val(null).trigger('change');
                        }

                        $('#edit_no_spb').val(tx.no_spb === '-' ? '' : tx.no_spb);
                        $('#edit_qty_spb').val(tx.qty_spb === '-' ? '' : tx.qty_spb);
                        $('#edit_nama_driver').val(tx.nama_driver || '');
                        $('#edit_no_hp_driver').val(tx.no_hp_driver || '');
                        $('#edit_checkin_pos1').val(tx.checkin_pos1 || '');
                        $('#edit_trnvisitorid').val(tx.trnvisitorid || '');

                        const actionUrl = `{{ url('vehicle-monitoring/timbangan/update') }}/${id}`;
                        $('#editForm').attr('action', actionUrl);

                        var myModal = new bootstrap.Modal(document.getElementById('editModal'));
                        myModal.show();
                    } else {
                        Swal.fire('Error!', response.message || 'Gagal memuat data transaksi.', 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Gagal mengambil data dari server.', 'error');
                }
            });
        });

        // Intercept Edit Form Submit
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const actionUrl = form.attr('action');

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    const modalEl = document.getElementById('editModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    Swal.fire('Berhasil!', response.message || 'Data transaksi berhasil diperbarui.', 'success');

                    // Reload data area terkait
                    if (typeof loadWfgData === 'function') loadWfgData();
                    if (typeof loadWpmData === 'function') loadWpmData();
                    if (typeof loadWrmData === 'function') loadWrmData();
                    if (typeof loadSmuData === 'function') loadSmuData();
                    if (typeof fetchTransactions === 'function') fetchTransactions();
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Gagal memperbarui transaksi.', 'error');
                }
            });
        });
    });
</script>
