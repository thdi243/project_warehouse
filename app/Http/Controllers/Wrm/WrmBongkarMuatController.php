<?php

namespace App\Http\Controllers\Wrm;

use App\Events\VehicleStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Vehicle\VehicleTrackingController;
use App\Models\NotificationsModel;
use App\Models\P2h\UserForkliftAssignmentModel;
use App\Models\User;
use App\Models\Vehicle\Location;
use App\Models\Vehicle\VehicleTracking;
use App\Models\Vehicle\VehicleTransaction;
use App\Models\Wfg\BongkarMuat;
use App\Models\Wpm\WpmBongkarMuat;
use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\WrmBongkarMuat;
use App\Models\Wrm\WrmBongkarMuatDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WrmBongkarMuatController extends Controller
{
    public function index()
    {
        return view('wrm.bongkar_muat.index');
    }

    public function data(Request $request)
    {
        $perPage = 10;
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');
        $tipe = $request->input('tipe');

        $query = WrmBongkarMuat::with(['forkliftDriver', 'checker', 'qcUser', 'details.material', 'verificator'])
            ->whereNotNull('jam_muat');

        if ($tipe) {
            $query->where('tipe', $tipe);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('no_dokumen', 'like', "%{$search}%")
                    ->orWhere('no_spkb', 'like', "%{$search}%")
                    ->orWhere('no_ecp', 'like', "%{$search}%")
                    ->orWhere('supplier_vendor', 'like', "%{$search}%")
                    ->orWhere('no_mobil', 'like', "%{$search}%");
            });
        }

        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $paginated
        ]);
    }

    public function approval()
    {
        return view('wrm.bongkar_muat.approval');
    }

    public function approvalData(Request $request)
    {
        $perPage = 10;
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = WrmBongkarMuat::with(['forkliftDriver', 'checker', 'qcUser', 'details.material'])
            ->where('status', 'finished');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('no_dokumen', 'like', "%{$search}%")
                    ->orWhere('no_spkb', 'like', "%{$search}%")
                    ->orWhere('no_ecp', 'like', "%{$search}%")
                    ->orWhere('supplier_vendor', 'like', "%{$search}%")
                    ->orWhere('no_mobil', 'like', "%{$search}%");
            });
        }

        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $paginated
        ]);
    }

    public function create(Request $request)
    {
        if ($request->query('create_new')) {
            $draft = WrmBongkarMuat::create([
                'tipe' => 'bongkar',
                'created_by' => Auth::id(),
                'status' => 'draft',
                'tanggal' => date('Y-m-d'),
                'sloc' => 'B006',
            ]);
            return redirect()->route('wrm.bongkar_muat.form', ['draft_id' => $draft->id]);
        }

        $draftId = $request->query('draft_id');

        if (!$draftId) {
            $latestActive = WrmBongkarMuat::where('created_by', Auth::id())
                ->whereIn('status', ['draft', 'submitted', 'approved'])
                ->latest()
                ->first();

            if ($latestActive) {
                if ($latestActive->status === 'draft') {
                    return redirect()->route('wrm.bongkar_muat.form', ['draft_id' => $latestActive->id]);
                } else {
                    return redirect()->route('wrm.bongkar_muat.show', $latestActive->id);
                }
            }

            $newDraft = WrmBongkarMuat::create([
                'tipe' => 'bongkar',
                'created_by' => Auth::id(),
                'status' => 'draft',
                'tanggal' => date('Y-m-d'),
                'sloc' => 'B006',
            ]);

            return redirect()->route('wrm.bongkar_muat.form', ['draft_id' => $newDraft->id]);
        }

        $draft = WrmBongkarMuat::with('details.material')->find($draftId);

        if (!$draft || $draft->created_by != Auth::id()) {
            return redirect()->route('wrm.bongkar_muat.form')
                ->with('error', 'Draft tidak ditemukan atau bukan milik Anda.');
        }

        if ($draft->status !== 'draft') {
            return redirect()->route('wrm.bongkar_muat.show', $draft->id);
        }

        $hasItems = $draft->details->contains(fn($d) => !empty($d->material_id) || !empty($d->mid));
        if (!$hasItems) {
            $draft->update(['tanggal' => date('Y-m-d')]);
        }

        $allDrafts = WrmBongkarMuat::where('created_by', Auth::id())
            ->whereIn('status', ['draft', 'submitted', 'approved'])
            ->latest()
            ->get();

        $forkliftDrivers = UserForkliftAssignmentModel::with('user')
            ->where('is_active', true)
            ->get()
            ->pluck('user')
            ->unique('id');

        $checkers = User::role('checker')->get();

        $bookedGates = $this->queryAllBookedGates($draftId);

        // Kendaraan aktif di area WRM jenis bongkaran
        $wrmVehicles = VehicleTransaction::with(['vehicle', 'targetLocation'])
            ->where(function ($q) {
                $q->whereIn('status', ['wrm_bongkar', 'wrm'])
                    ->orWhere(function ($sub) {
                        $sub->whereNotIn('status', ['completed', 'timbangan_out'])
                            ->whereHas('targetLocation', function ($tl) {
                                $tl->whereIn('s_loc', ['B006'])
                                    ->orWhere('name', 'like', '%WRM%');
                            });
                    });
            })
            ->where(function ($q) {
                $q->where('jenis', 'bongkaran')
                    ->orWhere('jenis', 'like', '%bongkar%');
            })
            ->get()
            ->filter(fn($tx) => $tx->vehicle && !empty($tx->vehicle->no_pol))
            ->unique(fn($tx) => strtoupper(str_replace([' ', '-', '.', '_'], '', $tx->vehicle->no_pol)))
            ->values();

        if (!empty($draft->no_mobil)) {
            [$resolvedNoMobil, $resolvedDriver, $resolvedVendor, $resolvedJamDoc, $resolvedJamRel] = $this->resolveVehicleInfo($draft->no_mobil, $draft->driver_name, $draft->supplier_vendor);
            $updateDraft = [];
            if ($resolvedNoMobil && $draft->no_mobil !== $resolvedNoMobil) {
                $updateDraft['no_mobil'] = $resolvedNoMobil;
                $draft->no_mobil = $resolvedNoMobil;
            }
            if ($resolvedDriver && empty($draft->driver_name)) {
                $updateDraft['driver_name'] = $resolvedDriver;
                $draft->driver_name = $resolvedDriver;
            }
            if ($resolvedVendor && empty($draft->supplier_vendor)) {
                $updateDraft['supplier_vendor'] = $resolvedVendor;
                $draft->supplier_vendor = $resolvedVendor;
            }
            if ($resolvedJamDoc && empty($draft->jam_serah_terima_qc)) {
                $updateDraft['jam_serah_terima_qc'] = $resolvedJamDoc;
                $draft->jam_serah_terima_qc = $resolvedJamDoc;
            }
            if ($resolvedJamRel && empty($draft->jam_release_qc)) {
                $updateDraft['jam_release_qc'] = $resolvedJamRel;
                $draft->jam_release_qc = $resolvedJamRel;
            }
            if (!empty($updateDraft)) {
                $draft->update($updateDraft);
            }
        }

        return view('wrm.bongkar_muat.form', compact(
            'forkliftDrivers',
            'checkers',
            'draft',
            'bookedGates',
            'allDrafts',
            'wrmVehicles'
        ));
    }

    private function queryAllBookedGates($excludeDraftId = null): array
    {
        return WrmBongkarMuat::whereIn('status', ['draft', 'submitted', 'approved'])
            ->when($excludeDraftId, fn($q) => $q->where('id', '!=', $excludeDraftId))
            ->whereNotNull('gate')
            ->pluck('gate')
            ->filter()
            ->map(fn($g) => (int) $g)
            ->unique()
            ->values()
            ->toArray();
    }

    public function getBookedGates(Request $request)
    {
        $excludeId = $request->input('draft_id');
        $booked = $this->queryAllBookedGates($excludeId);

        return response()->json([
            'status' => true,
            'booked_gates' => $booked
        ]);
    }

    private function defaultInspectionData(): array
    {
        return [
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
        ];
    }

    private function extractInspectionData(Request $request, ?array $existing = null): array
    {
        $base = array_merge($this->defaultInspectionData(), $existing ?? []);
        $in = $request->input('inspection', []);
        if (!is_array($in)) {
            $in = [];
        }

        return [
            'doc_coa' => $in['doc_coa'] ?? $base['doc_coa'],
            'doc_kartu_checkpoint' => $in['doc_kartu_checkpoint'] ?? $base['doc_kartu_checkpoint'],
            'kondisi_lantai' => $in['kondisi_lantai'] ?? $base['kondisi_lantai'],
            'kondisi_dinding' => $in['kondisi_dinding'] ?? $base['kondisi_dinding'],
            'kondisi_sealed' => $in['kondisi_sealed'] ?? $base['kondisi_sealed'],
            'kondisi_pintu' => $in['kondisi_pintu'] ?? $base['kondisi_pintu'],
            'kondisi_kebersihan' => $in['kondisi_kebersihan'] ?? $base['kondisi_kebersihan'],
            'kondisi_bau_tajam' => $in['kondisi_bau_tajam'] ?? $base['kondisi_bau_tajam'],
            'foto_plat_nomor' => filter_var($in['foto_plat_nomor'] ?? $base['foto_plat_nomor'], FILTER_VALIDATE_BOOLEAN),
            'foto_no_container' => filter_var($in['foto_no_container'] ?? $base['foto_no_container'], FILTER_VALIDATE_BOOLEAN),
            'foto_sealed_container' => filter_var($in['foto_sealed_container'] ?? $base['foto_sealed_container'], FILTER_VALIDATE_BOOLEAN),
            'foto_absorber_fumigan' => filter_var($in['foto_absorber_fumigan'] ?? $base['foto_absorber_fumigan'], FILTER_VALIDATE_BOOLEAN),
            'foto_material_bermasalah' => filter_var($in['foto_material_bermasalah'] ?? $base['foto_material_bermasalah'], FILTER_VALIDATE_BOOLEAN),
            'foto_container_berlubang' => filter_var($in['foto_container_berlubang'] ?? $base['foto_container_berlubang'], FILTER_VALIDATE_BOOLEAN),
            'jumlah_absorber' => $in['jumlah_absorber'] ?? $base['jumlah_absorber'],
            'jumlah_fumigan' => $in['jumlah_fumigan'] ?? $base['jumlah_fumigan'],
            'catatan_penyimpangan' => $in['catatan_penyimpangan'] ?? $base['catatan_penyimpangan'],
        ];
    }

    public function saveDraft(Request $request)
    {
        $draftId = $request->input('id');

        try {
            DB::beginTransaction();

            $order = null;
            if ($draftId) {
                $order = WrmBongkarMuat::where('id', $draftId)
                    ->where('created_by', Auth::id())
                    ->where('status', 'draft')
                    ->first();
            }

            if (!$order) {
                $order = WrmBongkarMuat::create([
                    'tipe' => 'bongkar',
                    'created_by' => Auth::id(),
                    'status' => 'draft',
                    'tanggal' => $request->filled('tanggal') ? $request->tanggal : date('Y-m-d'),
                    'sloc' => 'B006',
                ]);
            }

            $currentNoMobil = $this->cleanNull($request->no_mobil) ?? $order->no_mobil;
            $currentDriver = $this->cleanNull($request->driver_name) ?? $order->driver_name;
            $currentVendor = $this->cleanNull($request->supplier_vendor) ?? $order->supplier_vendor;

            [$resolvedNoMobil, $resolvedDriver, $resolvedVendor] = $this->resolveVehicleInfo($currentNoMobil, $currentDriver, $currentVendor);

            $order->update([
                'tipe' => 'bongkar',
                'tanggal' => $request->filled('tanggal') ? $request->tanggal : ($order->tanggal ?: date('Y-m-d')),
                'no_dokumen' => $this->cleanNull($request->no_dokumen) ?? $order->no_dokumen,
                'no_spkb' => $this->cleanNull($request->no_spkb) ?? $order->no_spkb,
                'no_ecp' => $this->cleanNull($request->no_ecp) ?? $order->no_ecp,
                'jumlah_tkbm' => $this->cleanNull($request->jumlah_tkbm) ?? $order->jumlah_tkbm,
                'sloc' => $this->cleanNull($request->sloc) ?? ($order->sloc ?: 'B006'),
                'supplier_vendor' => $resolvedVendor ?: ($this->cleanNull($request->supplier_vendor) ?? $order->supplier_vendor),
                'no_segel_bas' => $this->cleanNull($request->no_segel_bas) ?? $order->no_segel_bas,
                'jenis_pengiriman' => $this->cleanNull($request->jenis_pengiriman) ?? ($order->jenis_pengiriman ?: 'Lokal'),
                'no_mobil' => $resolvedNoMobil,
                'driver_name' => $resolvedDriver,
                'gate' => $this->cleanNull($request->gate) ?? $order->gate,
                'no_kontainer' => $this->cleanNull($request->no_kontainer) ?? $order->no_kontainer,
                'jam_serah_terima_qc' => $request->has('jam_serah_terima_qc') ? $this->cleanNull($request->jam_serah_terima_qc) : $order->jam_serah_terima_qc,
                'jam_release_qc' => $request->has('jam_release_qc') ? $this->cleanNull($request->jam_release_qc) : $order->jam_release_qc,
                'forklift_driver_id' => is_numeric($request->forklift_driver_id) ? $request->forklift_driver_id : $order->forklift_driver_id,
                'checker_id' => $order->checker_id ?? Auth::id(),
                'inspection_data' => $this->extractInspectionData($request, $order->inspection_data),
                'catatan' => $this->cleanNull($request->catatan) ?? $order->catatan,
            ]);

            if ($request->has('details') && is_array($request->details)) {
                $order->details()->delete();
                foreach ($request->details as $detail) {
                    $matId = $this->cleanNull($detail['material_id'] ?? null);
                    $mid = $this->cleanNull($detail['mid'] ?? null);
                    $namaBarang = $this->cleanNull($detail['nama_barang'] ?? null);
                    $uomVal = $this->cleanNull($detail['uom'] ?? null);

                    if ($matId && (empty($namaBarang) || empty($mid))) {
                        $mat = MasterBarangModel::find($matId);
                        if ($mat) {
                            $mid = $mid ?: $mat->mid;
                            $namaBarang = $namaBarang ?: $mat->nama_barang;
                            $uomVal = $uomVal ?: ($mat->uom ?? 'KG');
                        }
                    }

                    WrmBongkarMuatDetail::create([
                        'wrm_bongkar_muat_id' => $order->id,
                        'material_id' => $matId,
                        'mid' => $mid,
                        'nama_barang' => $namaBarang,
                        'uom' => $uomVal ?: 'KG',
                        'qty' => $detail['qty'] ?? 0,
                        'batch_number' => $this->cleanNull($detail['batch_number'] ?? null),
                        'jenis' => $this->cleanNull($detail['jenis'] ?? null) ?: 'P',
                        'keterangan' => $this->cleanNull($detail['keterangan'] ?? null),
                    ]);
                }
            }

            DB::commit();

            if (!empty($order->no_mobil)) {
                $this->syncVehicleDraftLoading($order->no_mobil, 'WRM');
            }

            $formattedTanggal = $order->tanggal instanceof \DateTimeInterface
                ? $order->tanggal->format('Y-m-d')
                : substr((string) $order->tanggal, 0, 10);

            return response()->json([
                'status' => true,
                'message' => 'Progress draft WRM disimpan.',
                'jam_muat' => $order->jam_muat,
                'tanggal' => $formattedTanggal,
                'driver_name' => $order->driver_name,
                'no_mobil' => $order->no_mobil,
                'supplier_vendor' => $order->supplier_vendor,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'no_mobil' => 'required',
            'details' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $draftId = $request->input('id');
        if ($request->filled('gate')) {
            $bookedGates = $this->queryAllBookedGates($draftId);
            if (in_array((int) $request->gate, $bookedGates)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gate ' . $request->gate . ' sudah digunakan oleh proses bongkar muat lain di area WRM.'
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $order = null;
            if ($draftId) {
                $order = WrmBongkarMuat::where('id', $draftId)
                    ->where('created_by', Auth::id())
                    ->where('status', 'draft')
                    ->first();
            }

            if (!$order) {
                return response()->json(['status' => false, 'message' => 'Draft tidak ditemukan atau bukan milik Anda.'], 404);
            }

            $noDok = ($order->no_dokumen === null || $order->no_dokumen === '')
                ? $this->generateNoDokumen()
                : $order->no_dokumen;

            [$resolvedNoMobil, $driverName, $resolvedVendor] = $this->resolveVehicleInfo($request->no_mobil, $request->driver_name, $request->supplier_vendor);

            $order->update([
                'tipe' => 'bongkar',
                'tanggal' => $request->tanggal,
                'no_dokumen' => $noDok,
                'no_spkb' => $this->cleanNull($request->no_spkb),
                'no_ecp' => $this->cleanNull($request->no_ecp),
                'jumlah_tkbm' => $this->cleanNull($request->jumlah_tkbm),
                'sloc' => $this->cleanNull($request->sloc) ?? 'B006',
                'supplier_vendor' => $resolvedVendor ?: $this->cleanNull($request->supplier_vendor),
                'no_segel_bas' => $this->cleanNull($request->no_segel_bas),
                'jenis_pengiriman' => $this->cleanNull($request->jenis_pengiriman) ?? 'Lokal',
                'no_mobil' => $resolvedNoMobil,
                'driver_name' => $driverName,
                'gate' => $this->cleanNull($request->gate),
                'no_kontainer' => $this->cleanNull($request->no_kontainer),
                'jam_serah_terima_qc' => $this->cleanNull($request->jam_serah_terima_qc),
                'jam_release_qc' => $this->cleanNull($request->jam_release_qc),
                'forklift_driver_id' => is_numeric($request->forklift_driver_id) ? $request->forklift_driver_id : null,
                'checker_id' => $order->checker_id ?? Auth::id(),
                'jam_muat' => $order->jam_muat ?: ($request->jam_muat ?: Carbon::now()->format('H:i:s')),
                'jam_selesai' => Carbon::now()->format('H:i:s'),
                'inspection_data' => $this->extractInspectionData($request, $order->inspection_data),
                'catatan' => $this->cleanNull($request->catatan),
                'status' => 'submitted',
                'created_by' => Auth::id(),
            ]);

            $order->details()->delete();
            foreach ($request->details as $detail) {
                $matId = $this->cleanNull($detail['material_id'] ?? null);
                $mid = $this->cleanNull($detail['mid'] ?? null);
                $namaBarang = $this->cleanNull($detail['nama_barang'] ?? null);
                $uomVal = $this->cleanNull($detail['uom'] ?? null);

                if ($matId && (empty($namaBarang) || empty($mid))) {
                    $mat = MasterBarangModel::find($matId);
                    if ($mat) {
                        $mid = $mid ?: $mat->mid;
                        $namaBarang = $namaBarang ?: $mat->nama_barang;
                        $uomVal = $uomVal ?: ($mat->uom ?? 'KG');
                    }
                }

                WrmBongkarMuatDetail::create([
                    'wrm_bongkar_muat_id' => $order->id,
                    'material_id' => $matId,
                    'mid' => $mid,
                    'nama_barang' => $namaBarang,
                    'uom' => $uomVal ?: 'KG',
                    'qty' => $detail['qty'] ?? 0,
                    'batch_number' => $this->cleanNull($detail['batch_number'] ?? null),
                    'jenis' => $this->cleanNull($detail['jenis'] ?? null) ?: 'P',
                    'keterangan' => $this->cleanNull($detail['keterangan'] ?? null),
                ]);
            }

            DB::commit();

            if (!empty($order->no_mobil)) {
                $this->syncVehicleDraftLoading($order->no_mobil, 'WRM');
            }

            return response()->json([
                'status' => true,
                'message' => 'Form Bongkar WRM berhasil disubmit.',
                'redirect' => route('wrm.bongkar_muat.show', $order->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $order = WrmBongkarMuat::with(['details.material', 'forkliftDriver', 'checker', 'qcUser', 'verificator'])->findOrFail($id);

        if (empty($order->driver_name) && !empty($order->no_mobil)) {
            [$resolvedNoMobil, $resolvedDriver, $resolvedVendor] = $this->resolveVehicleInfo($order->no_mobil, $order->driver_name, $order->supplier_vendor);
            if ($resolvedDriver && empty($order->driver_name)) {
                $order->driver_name = $resolvedDriver;
                $order->saveQuietly();
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => true, 'order' => $order]);
        }

        return view('wrm.bongkar_muat.show', compact('order'));
    }

    public function cancelDraft(Request $request)
    {
        try {
            DB::beginTransaction();
            $draftId = $request->input('id');
            $draft = WrmBongkarMuat::where('id', $draftId)
                ->where('created_by', Auth::id())
                ->where('status', 'draft')
                ->firstOrFail();

            $draft->details()->delete();
            $draft->delete();
            DB::commit();

            return response()->json(['status' => true, 'message' => 'Draft WRM berhasil dibatalkan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Gagal membatalkan draft: ' . $e->getMessage()], 500);
        }
    }

    public function approveChecker(Request $request, $id)
    {
        $request->validate(['signature' => 'required|string']);
        $order = WrmBongkarMuat::findOrFail($id);

        $signaturePath = $this->saveSignature($request->signature, '/wrm/checker_' . $id);
        $order->update([
            'checker_signature' => $signaturePath,
            'approved_at' => Carbon::now(),
            'status' => 'approved',
        ]);

        return back()->with('success', 'Tanda tangan Pengawas berhasil disimpan.');
    }

    public function approveQc(Request $request, $id)
    {
        $request->validate(['signature' => 'required|string']);
        $order = WrmBongkarMuat::findOrFail($id);

        $signaturePath = $this->saveSignature($request->signature, '/wrm/qc_' . $id);
        $order->update([
            'qc_signature' => $signaturePath,
            'qc_user_id' => Auth::id(),
            'qc_approved_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Tanda tangan Quality Control (QC) berhasil disimpan.');
    }

    public function approveDriver(Request $request, $id)
    {
        $request->validate([
            'driver_name' => 'required|string|max:255',
            'signature' => 'required|string',
        ]);

        $order = WrmBongkarMuat::findOrFail($id);
        $signaturePath = $this->saveSignature($request->signature, '/wrm/driver_' . $id);

        $order->update([
            'driver_name' => $request->driver_name,
            'driver_signature' => $signaturePath,
            'driver_approved_at' => Carbon::now(),
            'status' => 'finished',
        ]);

        return back()->with('success', 'Tanda tangan Sopir berhasil disimpan.');
    }

    public function validateOrder(Request $request, $id)
    {
        $order = WrmBongkarMuat::findOrFail($id);

        $signaturePath = null;
        if ($request->boolean('use_stored_signature') && auth()->user()->signature) {
            $signaturePath = auth()->user()->signature->signature;
        } else {
            $request->validate(['signature' => 'required|string']);
            $signaturePath = $this->saveSignature($request->signature, '/wrm/verificator_' . $id);
        }

        $order->update([
            'status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => Carbon::now(),
            'verified_note' => $request->note,
            'verified_signature' => $signaturePath,
        ]);

        return back()->with('success', 'Bongkar WRM berhasil diverifikasi.');
    }

    public function searchMaterials(Request $request)
    {
        $q = $request->input('q');
        $query = MasterBarangModel::query();

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('mid', 'like', "%{$q}%")
                    ->orWhere('nama_barang', 'like', "%{$q}%");
            });
        }

        $materials = $query->limit(30)->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'text' => "[{$m->mid}] {$m->nama_barang}",
                'mid' => $m->mid,
                'nama' => $m->nama_barang,
                'uom' => $m->uom ?? 'KG',
                'qty_pallet' => $m->qty_kg !== null ? (float) $m->qty_kg : 0,
            ];
        });

        return response()->json($materials);
    }

    public function download($id)
    {
        $order = WrmBongkarMuat::with(['details.material', 'forkliftDriver', 'checker', 'qcUser', 'verificator'])->findOrFail($id);

        $groupedBongkar = [];
        $totalAllQty = 0;
        $totalAllPallet = 0;

        foreach ($order->details->groupBy(fn($d) => $d->mid ?: $d->material_id) as $key => $details) {
            $first = $details->first();
            $namaBarang = $first->nama_barang ?: ($first->material->nama_barang ?? '-');
            $mid = $first->mid ?: ($first->material->mid ?? '-');
            $uom = $first->uom ?: ($first->material->uom ?? 'KG');
            $sumQty = $details->sum('qty');
            $palletCount = $details->count();
            $notes = $details->pluck('keterangan')->filter()->unique()->implode(', ');

            $groupedBongkar[] = [
                'mid' => $mid,
                'nama_barang' => $namaBarang,
                'uom' => $uom,
                'qty' => $sumQty,
                'pallet_count' => $palletCount,
                'keterangan' => $notes,
            ];
            $totalAllQty += $sumQty;
            $totalAllPallet += $palletCount;
        }

        $pdf = Pdf::loadView('pdf.wrm_bongkar', compact('order', 'groupedBongkar', 'totalAllQty', 'totalAllPallet'));
        $filename = 'BONGKAR_WRM_' . preg_replace('/[\/\\\\]/', '-', $order->no_dokumen ?? $order->id) . '.pdf';
        return $pdf->stream($filename);
    }

    private function resolveVehicleInfo(?string $noMobil, ?string $driverName, ?string $vendor = null): array
    {
        if (empty($noMobil)) {
            return [null, null, null, null, null];
        }

        $cleanNoMobil = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $noMobil));
        $noMobilSuffix = preg_replace('/^[A-Z]{1,2}/', '', $cleanNoMobil);

        $matchedTx = VehicleTransaction::with('vehicle')->whereHas('vehicle', function ($q) use ($cleanNoMobil) {
            $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_pol, ' ', ''), '-', ''), '.', ''), '_', '') = ?", [$cleanNoMobil]);
        })->whereNotIn('status', ['completed', 'timbangan_out'])->latest()->first();

        if (!$matchedTx && !empty($noMobilSuffix) && strlen($noMobilSuffix) >= 3) {
            $matchedTx = VehicleTransaction::with('vehicle')->whereHas('vehicle', function ($q) use ($noMobilSuffix) {
                $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_pol, ' ', ''), '-', ''), '.', ''), '_', '') LIKE ?", ['%' . $noMobilSuffix]);
            })->whereNotIn('status', ['completed', 'timbangan_out'])->latest()->first();
        }

        $resolvedNoMobil = $noMobil;
        $resolvedDriver = $driverName;
        $resolvedVendor = $vendor;
        $jamDocQc = null;
        $jamReleaseQc = null;

        if ($matchedTx) {
            if ($matchedTx->vehicle && !empty($matchedTx->vehicle->no_pol)) {
                $resolvedNoMobil = $matchedTx->vehicle->no_pol;
            }
            if (empty($resolvedDriver) && !empty($matchedTx->nama_driver)) {
                $resolvedDriver = $matchedTx->nama_driver;
            }
            if (empty($resolvedVendor) && !empty($matchedTx->vendor)) {
                $resolvedVendor = $matchedTx->vendor;
            }
            if ($matchedTx->queue_taken_time) {
                $jamDocQc = $matchedTx->queue_taken_time->format('H:i');
            } elseif ($matchedTx->notif_qc_doc_at) {
                $jamDocQc = $matchedTx->notif_qc_doc_at->format('H:i');
            }
            if ($matchedTx->finish_sampling_time) {
                $jamReleaseQc = $matchedTx->finish_sampling_time->format('H:i');
            } elseif ($matchedTx->notif_qc_release_at) {
                $jamReleaseQc = $matchedTx->notif_qc_release_at->format('H:i');
            }
        }

        return [$resolvedNoMobil, $resolvedDriver, $resolvedVendor, $jamDocQc, $jamReleaseQc];
    }

    private function generateNoDokumen()
    {
        $bulanRomawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $now = now();
        $currentYear = $now->year;
        $romanMonth = $bulanRomawi[$now->month] ?? 'I';

        // Running number reset setiap tahun baru mulai dari 1 lagi (format: 00001/WRM/XI/2026)
        $lastNumber = WrmBongkarMuat::whereNotNull('no_dokumen')
            ->where(function ($q) use ($currentYear) {
                $q->where('no_dokumen', 'like', "%/{$currentYear}")
                  ->orWhere(function ($sub) use ($currentYear) {
                      $sub->whereYear('tanggal', $currentYear)
                          ->where('no_dokumen', 'like', '%/WRM/%');
                  });
            })
            ->selectRaw('MAX(CAST(SUBSTRING_INDEX(no_dokumen, "/", 1) AS UNSIGNED)) as max_no')
            ->value('max_no');

        $nextNumber = ((int) $lastNumber) + 1;
        $formattedNumber = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        return "{$formattedNumber}/WRM/{$romanMonth}/{$currentYear}";
    }

    private function syncVehicleDraftLoading($noMobil, $area = 'WRM')
    {
        try {
            if (!$noMobil) return;
            $cleanNoMobil = strtoupper(str_replace([' ', '-', '.', '_'], '', $noMobil));

            $transaction = VehicleTransaction::whereNotIn('status', ['completed', 'timbangan_out'])
                ->whereHas('vehicle', function ($q) use ($cleanNoMobil) {
                    $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_pol, ' ', ''), '-', ''), '.', ''), '_', '') = ?", [$cleanNoMobil]);
                })
                ->where('unloading_status', '!=', 'completed')
                ->first();

            if ($transaction && ($transaction->unloading_status !== 'process' || empty($transaction->no_antrian))) {
                DB::beginTransaction();
                $now = Carbon::now();
                $assignedAntrian = $transaction->no_antrian ?: app(VehicleTrackingController::class)->generateNextQueueNumber($transaction);

                $transaction->update([
                    'status' => 'wrm_bongkar',
                    'no_antrian' => $assignedAntrian,
                    'queue_taken_time' => $transaction->queue_taken_time ?? $now,
                    'queue_taken_by' => $transaction->queue_taken_by ?? Auth::id(),
                    'unloading_status' => 'process',
                    'start_loading_time' => $transaction->start_loading_time ?? $now,
                    'start_loading_by' => $transaction->start_loading_by ?? Auth::id(),
                    'updated_by' => Auth::id()
                ]);

                $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : $noMobil;

                event(new VehicleStatusUpdated([
                    'transaction_id' => $transaction->id,
                    'no_pol' => $noPol,
                    'current_location' => 'B006',
                    'status' => 'wrm_bongkar',
                    'no_antrian' => $assignedAntrian,
                    'message' => "Truk {$noPol} mulai proses Bongkar di {$area} (Antrian #{$assignedAntrian} - Form Bongkar Draft).",
                    'time' => $now->format('H:i:s')
                ]));

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::warning("Gagal sinkronisasi draft WRM ke Vehicle Monitoring untuk {$noMobil}: " . $e->getMessage());
        }
    }

    private function saveSignature($base64Data, $prefix)
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $type = strtolower($type[1]);
                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) throw new \Exception('invalid image type');
                $base64Data = base64_decode($base64Data);
                if ($base64Data === false) throw new \Exception('base64_decode failed');
            } else {
                throw new \Exception('did not match data URI with image data');
            }

            $fileName = $prefix . '_' . time() . '.png';
            $path = 'signatures/' . ltrim($fileName, '/');
            Storage::disk('public')->put($path, $base64Data);
            return 'storage/' . $path;
        } catch (\Exception $e) {
            Log::error('Signature save error: ' . $e->getMessage());
            return null;
        }
    }

    private function cleanNull($value)
    {
        return in_array($value, [null, '', 'null', 'undefined'], true) ? null : $value;
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'no_mobil' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:100',
            'supplier_vendor' => 'nullable|string|max:150',
            'no_spkb' => 'nullable|string|max:100',
            'no_ecp' => 'nullable|string|max:100',
            'sloc' => 'nullable|string|max:50',
            'gate' => 'nullable|string|max:50',
            'no_kontainer' => 'nullable|string|max:50',
            'no_segel_bas' => 'nullable|string|max:50',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Gate collision check within WRM
        if ($request->filled('gate')) {
            $gateCollision = WrmBongkarMuat::whereIn('status', ['draft', 'submitted', 'approved'])
                ->where('id', '!=', $id)
                ->where('gate', $request->gate)
                ->exists();

            if ($gateCollision) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gate ' . $request->gate . ' sudah digunakan oleh draft/order lain di area WRM.'
                ], 422);
            }
        }

        try {
            $order = WrmBongkarMuat::findOrFail($id);
            $order->update($request->only([
                'tanggal',
                'no_mobil',
                'driver_name',
                'supplier_vendor',
                'no_spkb',
                'no_ecp',
                'sloc',
                'gate',
                'no_kontainer',
                'no_segel_bas',
                'catatan',
            ]));

            return response()->json(['status' => true, 'message' => 'Data Bongkar Muat WRM berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $order = WrmBongkarMuat::findOrFail($id);

        if (in_array($order->status, ['draft', 'submitted', 'rejected', 'approved', 'finished'])) {
            NotificationsModel::where('notifiable_type', WrmBongkarMuat::class)
                ->where('notifiable_id', $order->id)
                ->delete();

            $order->details()->delete();
            $order->delete();
            return response()->json(['status' => true, 'message' => 'Data Bongkar Muat WRM berhasil dihapus.']);
        }

        return response()->json(['status' => false, 'message' => 'Tidak dapat menghapus data dengan status ' . $order->status], 403);
    }

    public function followUpChecker($id)
    {
        $order = WrmBongkarMuat::with('checker')->findOrFail($id);

        if (!in_array($order->status, ['submitted', 'draft', 'approved'])) {
            return response()->json([
                'status' => false,
                'message' => 'Follow up hanya bisa dikirim untuk status draft, submitted, atau approved.'
            ], 422);
        }

        if (!$order->checker_id) {
            return response()->json([
                'status' => false,
                'message' => 'Checker belum ditentukan untuk Bongkar Muat ini.'
            ], 422);
        }

        $url = $order->status === 'draft'
            ? route('wrm.bongkar_muat.form', ['draft_id' => $order->id])
            : route('wrm.bongkar_muat.show', $order->id);

        $message = $order->status === 'draft'
            ? 'Form Bongkar Muat WRM Anda belum disubmit, tolong segera disubmit.'
            : 'Ada form Bongkar Muat WRM yang menunggu approval Anda.';

        NotificationsModel::create([
            'user_id' => $order->checker_id,
            'title' => 'Follow Up Bongkar Muat WRM',
            'message' => $message,
            'url' => $url,
            'is_read' => 0,
            'notifiable_type' => WrmBongkarMuat::class,
            'notifiable_id' => $order->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Notifikasi follow up berhasil dikirim ke checker.'
        ]);
    }
}
