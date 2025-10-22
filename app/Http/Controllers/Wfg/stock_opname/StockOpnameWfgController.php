<?php

namespace App\Http\Controllers\Wfg\stock_opname;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\Wfg\stock_opname\WfgSopModel;
use App\Models\Wfg\stock_opname\BarangWfgModel;
use App\Models\Wfg\stock_opname\WfgSopTempModel;
use App\Models\Wfg\stock_opname\StockOnHandModel;
use App\Models\Wfg\stock_opname\WfgSopDetailModel;
use App\Models\Wfg\stock_opname\WfgSopNewTempModel;
use App\Models\Wfg\stock_opname\WfgSopApprovalModel;
use App\Models\Wfg\stock_opname\WfgSopSummariesModel;

class StockOpnameWfgController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'mid' => 'required|exists:wfg_barang,mid_barang',
    //         'tgl_opname' => 'required|date',
    //         'qty_full' => 'required|array',
    //         'qty_receh' => 'required|array',
    //         // 'keterangan' => 'nullable|string|max:255',
    //     ], [
    //         'tgl_opname.required' => 'Tanggal opname wajib diisi.',
    //         'mid.required' => 'MID barang wajib dipilih.',
    //     ]);

    //     $qtyFull = $request->input('qty_full', []);
    //     $qtyReceh = $request->input('qty_receh', []);

    //     $hasQty = false;
    //     foreach ($qtyFull as $index => $full) {
    //         $fullVal = $full ?? null;
    //         $recehVal = $qtyReceh[$index] ?? null;

    //         if (!empty($fullVal) || !empty($recehVal)) {
    //             $hasQty = true;
    //             break;
    //         }
    //     }

    //     if (!$hasQty) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Minimal isi salah satu Qty Full atau Qty Receh sebelum menyimpan.'
    //         ], 422);
    //     }

    //     try {
    //         $barang = BarangWfgModel::where('mid_barang', $request->mid)->firstOrFail();

    //         // Cek existing data (kode tetap sama)
    //         $existing = WfgSopSummariesModel::whereHas('sop', function ($q) use ($request) {
    //             $q->where('tgl_opname', $request->tgl_opname);
    //         })->where('barang_id', $barang->id)->first();

    //         if ($existing) {
    //             return response()->json([
    //                 'status' => 'warning',
    //                 'message' => 'Stock opname sudah ada. Lanjutkan update?',
    //                 'sop_id' => $existing->sop_id,
    //             ], 200);
    //         }

    //         // $soh = StockOnHandModel::where('barang_id', $barang->id)->first();
    //         // $qty_sistem = $soh ? $soh->qty_soh : 0;

    //         $entriesCount = count($request->qty_full);
    //         $totalFisik = 0;
    //         for ($i = 0; $i < $entriesCount; $i++) {
    //             $qty_full = $request->qty_full[$i] ?? 0;
    //             $qty_receh = $request->qty_receh[$i] ?? 0;
    //             $qty_fisik = ($qty_full * (float)($barang->qty_box ?? 1)) + $qty_receh;
    //             $totalFisik += $qty_fisik;
    //         }

    //         // // Hitung Selisih
    //         // $selisih = $totalFisik - $qty_sistem;

    //         // if (abs($selisih) > 0 && empty($request->keterangan)) {
    //         //     return response()->json([
    //         //         'status' => 'selisih',
    //         //         'message' => "Terdapat selisih (Fisik: {$totalFisik}, Sistem: {$qty_sistem}). Keterangan wajib diisi."
    //         //     ]);
    //         // }

    //         // $status = '';

    //         // if ($selisih === 0) {
    //         //     $status = 'sesuai';
    //         // } else if ($selisih > 0) {
    //         //     $status = 'lebih';
    //         // } else {
    //         //     $status = 'kurang';
    //         // };

    //         DB::beginTransaction();

    //         // 4. Proses Simpan SOP (Kode selanjutnya sama seperti sebelumnya, tapi menggunakan $totalFisik dan $selisih yang sudah dihitung)
    //         $sop = WfgSopModel::create([
    //             'tgl_opname' => $request->tgl_opname,
    //             'user_id' => Auth::id() ?? 1,
    //         ]);

    //         // Simpan Detail
    //         for ($i = 0; $i < $entriesCount; $i++) {
    //             $qty_full = $request->qty_full[$i] ?? 0;
    //             $qty_receh = $request->qty_receh[$i] ?? 0;

    //             WfgSopDetailModel::create([
    //                 'sop_id' => $sop->id,
    //                 'barang_id' => $barang->id,
    //                 'qty_full' => $qty_full,
    //                 'qty_receh' => $qty_receh,
    //             ]);
    //         }

    //         // Simpan summary
    //         // WfgSopSummariesModel::create([
    //         //     'sop_id' => $sop->id,
    //         //     'barang_id' => $barang->id,
    //         //     'qty_fisik' => $totalFisik ?? 0,
    //         //     'qty_sistem' => $qty_sistem ?? 0,
    //         //     'selisih' => $selisih ?? 0,
    //         //     'status' => $status ?? '',
    //         //     'keterangan' => null,
    //         //     // 'keterangan' => $request->keterangan ?? null,
    //         // ]);

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Stock opname berhasil disimpan',
    //             'sop_id' => $sop->id
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Gagal menyimpan stock opname: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        // dd($request);

        $request->validate([
            'mid' => 'required|exists:wfg_barang,mid_barang',
            'tgl_opname' => 'required|date',
            'qty_full' => 'required|array',
            'qty_receh' => 'required|array',
            // 'keterangan' => 'nullable|string|max:255',
        ], [
            'tgl_opname.required' => 'Tanggal opname wajib diisi.',
            'mid.required' => 'MID barang wajib dipilih.',
        ]);

        $qtyFull = $request->input('qty_full', []);
        $qtyReceh = $request->input('qty_receh', []);

        $hasQty = false;
        foreach ($qtyFull as $index => $full) {
            $fullVal = $full ?? null;
            $recehVal = $qtyReceh[$index] ?? null;

            if (!empty($fullVal) || !empty($recehVal)) {
                $hasQty = true;
                break;
            }
        }

        if (!$hasQty) {
            return response()->json([
                'status' => 'error',
                'message' => 'Minimal isi salah satu Qty Full atau Qty Receh sebelum menyimpan.'
            ], 422);
        }

        try {
            $barang = BarangWfgModel::where('mid_barang', $request->mid)->firstOrFail();

            // Cek existing data (Cek apakah SOP sudah ada untuk tanggal dan barang ini)
            $existing = WfgSopSummariesModel::whereHas('sop', function ($q) use ($request) {
                $q->where('tgl_opname', $request->tgl_opname);
            })->where('barang_id', $barang->id)->first();

            if ($existing) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Stock opname sudah ada. Lanjutkan update?',
                    'sop_id' => $existing->sop_id,
                ], 200);
            }

            // --- HAPUS LOGIKA PERHITUNGAN DAN SOH/SUMMARY DI SINI ---

            // Hanya hitung total fisik untuk tujuan validasi (minimal terisi), tetapi tidak perlu $totalFisik
            // Logika $totalFisik tetap dipertahankan hanya untuk memastikan validasi 'hasQty' bekerja
            $entriesCount = count($request->qty_full);
            /* // Baris kode perhitungan $totalFisik, $qty_sistem, dan $selisih dihilangkan 
        // karena akan dilakukan di fungsi import SOH.
        */

            DB::beginTransaction();

            // 4. Proses Simpan SOP
            $sop = WfgSopModel::create([
                'tgl_opname' => $request->tgl_opname,
                'user_id' => Auth::id() ?? 1,
            ]);

            // Simpan Detail
            for ($i = 0; $i < $entriesCount; $i++) {
                $qty_full = $request->qty_full[$i] ?? 0;
                $qty_receh = $request->qty_receh[$i] ?? 0;

                WfgSopDetailModel::create([
                    'sop_id' => $sop->id,
                    'barang_id' => $barang->id,
                    'qty_full' => $qty_full,
                    'qty_receh' => $qty_receh,
                ]);
            }

            // Simpan summary (HAPUS/DIHILANGKAN)
            /*
        // WfgSopSummariesModel::create(...) 
        // Logika ini dihilangkan karena akan dilakukan di fungsi import SOH.
        */

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Stock opname berhasil disimpan',
                'sop_id' => $sop->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan stock opname: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function saveTemp(Request $request)
    {
        $request->validate([
            'soh_id' => 'required|exists:wfg_soh,id',
            'barang_id' => 'required|exists:wfg_barang,id',
            'qty_full' => 'required|integer|min:0',
            'qty_receh' => 'required|integer|min:0',
            'summary' => 'required|integer|min:0',
        ]);

        $barang = BarangWfgModel::find($request->barang_id);
        if (!$barang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak ditemukan.'
            ]);
        }

        $user = Auth::user();

        // 🔍 Tentukan principal sesuai role
        if ($user->jabatan === 'operator') {
            $principal = $user->principal?->principal ?? null;

            if (empty($principal)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Akun operator belum memiliki principal. Hubungi Foreman untuk melengkapi data user.',
                ], 422);
            }
        } else {
            $principal = $request->input('principal', $user->principal ?? null);
        }
        // if (!$principal) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Principal tidak ditemukan untuk user ini.',
        //     ]);
        // }

        $qtyFull = $request->qty_full;
        $qtyReceh = $request->qty_receh;
        $qtyBox = $barang->qty_box ?? 0;

        $summary = ($qtyFull * $qtyBox) + $qtyReceh;

        $temp = WfgSopTempModel::create([
            'soh_id' => $request->soh_id,
            'barang_id' => $request->barang_id,
            'qty_full' => $request->qty_full,
            'qty_receh' => $request->qty_receh,
            'summary' => $summary,
            'created_by' => Auth::id() ?? 1,
            'tgl_opname' => now(),
            'principal' => $principal,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data sementara tersimpan.',
            'data' => $temp,
        ]);
    }

    public function saveTempNew(Request $request)
    {
        $request->validate([
            'mid_barang' => 'required|string|max:100',
            'nama_barang' => 'required|string|max:255',
            'uom' => 'required|string|max:255',
            'qty_box' => 'nullable|integer|min:1',
            'qty_full' => 'required|integer|min:0',
            'qty_receh' => 'required|integer|min:0',
            'summary' => 'required|integer|min:0',
        ]);

        $user = Auth::user();

        // 🔍 Tentukan principal
        if ($user->jabatan === 'operator') {
            $principal = $user->principal?->principal ?? null;
            if (empty($principal)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Akun operator belum memiliki principal. Hubungi Foreman untuk melengkapi data user.',
                ], 422);
            }
        } else {
            $principal = $request->input('principal', $user->principal ?? null);
        }

        // 🔹 Simpan barang baru ke wfg_barang (jika belum ada)
        $barang = BarangWfgModel::firstOrCreate(
            ['mid_barang' => $request->mid_barang],
            [
                'nama_barang' => $request->nama_barang,
                'qty_box' => $request->qty_box ?? 1,
                'principal' => $principal,
                'uom' => $request->uom ?? 'PCS',
                'status' => 'aktif',
                'is_new' => true, // tandai barang baru
            ]
        );

        // 🔹 Simpan ke temp opname
        $summary = ($request->qty_full * $barang->qty_box) + $request->qty_receh;

        $temp = WfgSopTempModel::create([
            'barang_id' => $barang->id,
            'soh_id' => null, // karena tidak ada di SOH
            'qty_full' => $request->qty_full,
            'qty_receh' => $request->qty_receh,
            'summary' => $summary,
            'principal' => $principal,
            'created_by' => Auth::id(),
            'tgl_opname' => $request->input('tgl_opname', now()->toDateString()),
            'status' => 'waiting',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Barang baru berhasil ditambahkan dan disimpan sementara.',
            'data' => [
                'barang' => $barang,
                'temp' => $temp,
            ],
        ]);
    }

    public function finalizeOpname(Request $request)
    {
        $request->validate([
            'tgl_opname' => 'required|date',
        ]);

        $user = Auth::user();

        $userId = $user->id;
        $tglOpname = $request->tgl_opname;
        $keteranganInput = $request->input('keterangan', []);

        // Tentukan principal active:
        if ($user->jabatan === 'operator') {
            // ambil nilai principal dari relasi user (sesuaikan nama field di UserPrincipalModel)
            $principalFilter = optional($user->principal)->principal ?? null;

            if (empty($principalFilter)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun operator belum memiliki principal. Hubungi foreman untuk melengkapi data user.',
                ], 403);
            }
        } else {
            // non-operator: wajib mengirim principal (sesuai permintaan)
            $principalFilter = $request->input('principal');
            if (empty($principalFilter)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Principal harus dipilih sebelum menyimpan SOP final.',
                ], 422);
            }
        }

        if ($user->jabatan === 'operator') {
            $existingSop = WfgSopModel::whereDate('tgl_opname', $tglOpname)->first();

            if ($existingSop) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah melakukan opname hari ini. Tidak dapat melakukan opname lebih dari sekali per hari. Hubungi Foreman!',
                ]);
            }
        }

        // Ambil barang sesuai principal dan tanggal (SOH hari ini yang punya principal tersebut)
        $barangHariIni = StockOnHandModel::with('barang:id,mid_barang,nama_barang,principal')
            ->whereHas('barang', function ($q) use ($principalFilter) {
                $q->where('principal', $principalFilter);
            })
            ->whereDate('last_updated', Carbon::today())
            ->get()
            ->pluck('barang');

        // Ambil data temp sesuai scope:
        if ($user->jabatan === 'operator') {
            $tempData = WfgSopTempModel::where('created_by', $userId)
                ->where('tgl_opname', $tglOpname)
                ->where('principal', $principalFilter)
                ->get();
        } else {
            $tempData = WfgSopTempModel::where('tgl_opname', $tglOpname)
                ->where('principal', $principalFilter)
                ->get();
        }

        if ($tempData->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data sementara masih kosong, isi terlebih dahulu.',
            ]);
        }

        // Cek apakah ada barang yang belum di opname — hanya dengan barang yang di SOH untuk principal ini
        $barangBelumOpname = [];
        foreach ($barangHariIni as $barang) {
            $found = $tempData->firstWhere('barang_id', $barang->id);
            if (!$found) {
                $barangBelumOpname[] = [
                    'mid_barang' => $barang->mid_barang,
                    'nama_barang' => $barang->nama_barang,
                ];
            }
        }

        if (count($barangBelumOpname) > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Masih ada barang yang belum di opname untuk principal ini.',
                'data' => $barangBelumOpname,
            ]);
        }

        // 🔍 Cek selisih terlebih dahulu (grouped by barang_id)
        $grouped = [];
        foreach ($tempData as $temp) {
            $barangId = $temp->barang_id;
            if (!isset($grouped[$barangId])) {
                // cari SOH terkait (mungkin null jika manual add tanpa soh_id)
                $soh = null;
                if (!empty($temp->soh_id)) {
                    $soh = StockOnHandModel::find($temp->soh_id);
                }

                $grouped[$barangId] = [
                    'barang_id' => $barangId,
                    'mid_barang' => optional($temp->barang)->mid_barang,
                    'nama_barang' => optional($temp->barang)->nama_barang,
                    'qty_sap' => $soh ? $soh->qty_soh : 0,
                    'qty_fisik' => 0,
                ];
            }
            $grouped[$barangId]['qty_fisik'] += (int) $temp->summary;
        }

        // Hitung selisih dan cek keterangan
        $selisihList = [];
        foreach ($grouped as $barangId => $g) {
            $selisih = $g['qty_fisik'] - $g['qty_sap'];
            $keterangan = $keteranganInput[$barangId] ?? null;

            if ($selisih != 0 && empty($keterangan)) {
                $g['selisih'] = $selisih;
                $selisihList[] = $g;
            }

            // Simpan keterangan di grouped untuk nanti disimpan ke summaries
            $grouped[$barangId]['keterangan'] = $keterangan;
        }
        // Operator hanya boleh submit 1x per hari



        if (count($selisihList) > 0) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Terdapat selisih antara Qty Fisik dan SAP, harap isi keterangan!',
                'data' => $selisihList,
            ]);
        }

        // Simpan final SOP
        try {
            DB::beginTransaction();

            $sop = WfgSopModel::create([
                'tgl_opname' => $tglOpname,
                'user_id' => $userId,
                'status' => 'draft',
                'principal' => $principalFilter,
            ]);

            // summaries
            foreach ($grouped as $barangId => $g) {
                $selisih = $g['qty_fisik'] - $g['qty_sap'];

                if ($selisih > 0) $status = 'lebih';
                elseif ($selisih < 0) $status = 'kurang';
                else $status = 'match';

                WfgSopSummariesModel::create([
                    'sop_id' => $sop->id,
                    'barang_id' => $barangId,
                    'qty_fisik' => $g['qty_fisik'],
                    'qty_sistem' => $g['qty_sap'],
                    'selisih' => $selisih,
                    'status' => $status,
                    'keterangan' => $g['keterangan'] ?? null,
                ]);
            }

            // details
            foreach ($tempData as $temp) {
                WfgSopDetailModel::create([
                    'sop_id' => $sop->id,
                    'barang_id' => $temp->barang_id,
                    'qty_full' => $temp->qty_full,
                    'qty_receh' => $temp->qty_receh,
                ]);
            }

            // Hapus temp: 
            // - operator: hapus hanya yang dibuat oleh operator itu untuk tanggal/principal
            // - non-operator: hapus semua temp untuk tanggal/principal (karena admin finalize group)
            if ($user->jabatan === 'operator') {
                WfgSopTempModel::where('created_by', $userId)
                    ->where('tgl_opname', $tglOpname)
                    ->where('principal', $principalFilter)
                    ->delete();
            } else {
                WfgSopTempModel::where('tgl_opname', $tglOpname)
                    ->where('principal', $principalFilter)
                    ->delete();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data opname berhasil disimpan sebagai final.',
                'sop_id' => $sop->id,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data final: ' . $th->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = Auth::user();
        $userId = $user->id;
        $tanggalFilter = $request->input('tanggal');
        $principalFilter = $request->input('principal');

        if ($tanggalFilter) {
            $sopQuery = WfgSopModel::whereDate('tgl_opname', $tanggalFilter);

            // Filter berdasarkan principal kalau ada
            if (!empty($principalFilter)) {
                $sopQuery->where('principal', $principalFilter);
            }

            $sop = $sopQuery->first();

            if (!$sop) {
                return response()->json([
                    'approval_status' => 'draft',
                    'approval_note' => null,
                    'approver_tracking' => [],
                ]);
            }

            $id = $sop->id;
        }

        // 🔹 ambil tracking approval untuk semua response
        $approverTracking = WfgSopApprovalModel::where('sop_id', $id)
            ->with('approver:id,username,jabatan')
            ->get()
            ->map(fn($a) => [
                'nama' => $a->approver->username ?? '-',
                'jabatan' => $a->approver->jabatan ?? '-',
                'status' => ucfirst($a->status),
                'catatan' => $a->catatan,
            ]);

        // 🔹 cek apakah user ini approver
        $approval = WfgSopApprovalModel::where('sop_id', $id)
            ->where('approver_id', $userId)
            ->first();

        if ($approval) {
            return response()->json([
                'approval_status' => $approval->status,
                'approval_note' => $approval->catatan,
                'is_approver' => true,
                'approver_tracking' => $approverTracking, // ✅ ditambah sini
            ]);
        }

        // 🔹 operator logic
        if ($user->jabatan === 'operator') {
            $approvals = WfgSopApprovalModel::where('sop_id', $id)->get();

            if ($approvals->isEmpty()) {
                return response()->json([
                    'approval_status' => 'draft',
                    'approval_note' => null,
                    'is_approver' => false,
                    'approver_tracking' => $approverTracking,
                ]);
            }

            if ($approvals->contains('status', 'rejected')) {
                $rejected = $approvals->firstWhere('status', 'rejected');
                return response()->json([
                    'approval_status' => 'rejected',
                    'approval_note' => $rejected->catatan,
                    'is_approver' => false,
                    'approver_tracking' => $approverTracking,
                ]);
            }

            if ($approvals->contains(fn($a) => in_array($a->status, ['pending', 'read']))) {
                return response()->json([
                    'approval_status' => 'pending',
                    'approval_note' => null,
                    'is_approver' => false,
                    'approver_tracking' => $approverTracking,
                ]);
            }

            return response()->json([
                'approval_status' => 'approved',
                'approval_note' => null,
                'is_approver' => false,
                'approver_tracking' => $approverTracking,
            ]);
        }

        // 🔹 untuk user lain
        $approvals = WfgSopApprovalModel::where('sop_id', $id)->get();

        if ($approvals->isEmpty()) {
            return response()->json([
                'approval_status' => 'draft',
                'approval_note' => null,
                'is_approver' => false,
                'approver_tracking' => $approverTracking,
            ]);
        }

        if ($approvals->contains('status', 'rejected')) {
            $rejected = $approvals->firstWhere('status', 'rejected');
            return response()->json([
                'approval_status' => 'rejected',
                'approval_note' => $rejected->catatan,
                'is_approver' => false,
                'approver_tracking' => $approverTracking,
            ]);
        }

        if ($approvals->contains(fn($a) => in_array($a->status, ['pending', 'read']))) {
            return response()->json([
                'approval_status' => 'pending',
                'approval_note' => null,
                'is_approver' => false,
                'approver_tracking' => $approverTracking,
            ]);
        }

        return response()->json([
            'approval_status' => 'approved',
            'approval_note' => null,
            'is_approver' => false,
            'approver_tracking' => $approverTracking,
        ]);
    }

    public function getData(Request $request)
    {
        $user = Auth::user();
        $searchTerm = $request->input('search');
        $principalFilter = $request->input('principal');
        $perPage = 25;
        $today = now()->toDateString();

        // Tentukan principal filter
        if ($user->jabatan === 'operator') {
            $principalToFilter = $user->principal?->principal;
        } else {
            $principalToFilter = $principalFilter;
        }

        // Ambil data SOH yang belum ada opname hari ini
        $sohPendingQuery = StockOnHandModel::select([
            'wfg_soh.id AS soh_id',
            'wfg_soh.barang_id',
            'wfg_soh.qty_soh',
            DB::raw("NULL AS temp_id"),
            DB::raw("wfg_soh.last_updated AS last_updated"),
        ])
            ->leftJoin('wfg_barang', 'wfg_soh.barang_id', '=', 'wfg_barang.id');

        // Ambil data barang baru (belum punya SOH)
        $sopTempQuery = WfgSopTempModel::select([
            DB::raw("NULL AS soh_id"),
            'wfg_sop_temp.barang_id',
            DB::raw("NULL AS qty_soh"),
            'wfg_sop_temp.id AS temp_id',
            DB::raw("wfg_sop_temp.updated_at AS last_updated"),
        ])
            ->whereNull('wfg_sop_temp.soh_id')
            ->whereDate('wfg_sop_temp.tgl_opname', $today)
            ->leftJoin('wfg_barang', 'wfg_sop_temp.barang_id', '=', 'wfg_barang.id');

        // Gabungkan dua sumber data
        $dataQuery = $sohPendingQuery->union($sopTempQuery);

        $bindings = $dataQuery->getBindings();
        $sql = $dataQuery->toSql();

        // 🔹 Join ulang untuk ambil detail barang & filter
        $finalQuery = DB::table(DB::raw("({$sql}) AS union_result"))
            ->setBindings($bindings)
            ->join('wfg_barang', 'union_result.barang_id', '=', 'wfg_barang.id')
            ->select(
                'union_result.soh_id',
                'union_result.temp_id',
                'union_result.barang_id',
                'union_result.qty_soh',
                'union_result.last_updated',
                'wfg_barang.mid_barang',
                'wfg_barang.nama_barang',
                'wfg_barang.qty_box',
                'wfg_barang.principal'
            );

        // Filter principal
        if ($principalToFilter) {
            $finalQuery->where('wfg_barang.principal', $principalToFilter);
        }

        // Filter pencarian
        if ($searchTerm) {
            $finalQuery->where(function ($q) use ($searchTerm) {
                $q->where('wfg_barang.mid_barang', 'like', "%{$searchTerm}%")
                    ->orWhere('wfg_barang.nama_barang', 'like', "%{$searchTerm}%");
            });
        }

        // Urutkan biar data SOH muncul duluan
        $finalQuery->orderByRaw('CASE WHEN union_result.temp_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('wfg_barang.mid_barang', 'asc');

        // Pagination
        $total = $finalQuery->count();
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $items = $finalQuery->skip(($currentPage - 1) * $perPage)->take($perPage)->get();

        // Map data biar konsisten
        $mappedItems = $items->map(function ($item) {
            return (object) [
                'id' => $item->soh_id ?? $item->temp_id,
                'soh_id' => $item->soh_id,
                'barang_id' => $item->barang_id,
                'qty_soh' => $item->qty_soh,
                'last_updated' => $item->last_updated,
                'mid_barang' => $item->mid_barang,
                'nama_barang' => $item->nama_barang,
                'qty_box' => $item->qty_box,
                'principal' => $item->principal,
            ];
        });

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $mappedItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return response()->json([
            'status' => 'success',
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }

    public function getDataTempBatch(Request $request)
    {
        $user = Auth::user();
        $sohIds = $request->input('soh_ids', []);
        $barangIds = $request->input('barang_ids', []);

        if (!is_array($sohIds) || !is_array($barangIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter soh_ids dan barang_ids harus berupa array'
            ], 400);
        }

        $sohIds = array_map('intval', array_filter($sohIds));
        $barangIds = array_map('intval', array_filter($barangIds));

        $recordsQuery = WfgSopTempModel::with([
            'soh.barang:id,principal,mid_barang,nama_barang',
            'barang:id,principal,mid_barang,nama_barang'
        ])
            ->where(function ($q) use ($sohIds, $barangIds) {
                if (!empty($sohIds)) {
                    $q->whereIn('soh_id', $sohIds);
                }
                if (!empty($barangIds)) {
                    $q->orWhereIn('barang_id', $barangIds)->whereNull('soh_id');
                }
            })
            ->whereDate('tgl_opname', now()->toDateString())
            ->orderByDesc('updated_at');

        if ($user->jabatan === 'operator') {
            $userPrincipal = $user->principal?->principal;
            if (!$userPrincipal) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun operator belum memiliki principal. Hubungi admin.',
                    'data' => []
                ], 403);
            }

            $recordsQuery->where(function ($q) use ($userPrincipal) {
                $q->whereHas('barang', fn($sub) => $sub->where('principal', $userPrincipal))
                    ->orWhereHas('soh.barang', fn($sub) => $sub->where('principal', $userPrincipal));
            });
        }

        $records = $recordsQuery->get();

        $result = $records->map(function ($rec) {
            $barang = $rec->barang ?? optional(optional($rec->soh)->barang);
            if (!$barang) return null;

            return [
                'id'          => $rec->id,
                'soh_id'      => $rec->soh_id,
                'barang_id'   => $rec->barang_id,
                'mid_barang'  => $barang->mid_barang,
                'nama_barang' => $barang->nama_barang,
                'qty_full'    => $rec->qty_full,
                'qty_receh'   => $rec->qty_receh,
                'summary'     => (int) $rec->summary,
                'principal'   => $barang->principal,
                'created_at'  => $rec->created_at,
                'updated_at'  => $rec->updated_at,
            ];
        })->filter()->values();

        return response()->json([
            'status' => 'success',
            'data'   => $result
        ]);
    }

    public function getDataTempEdit(String $id)
    {
        $data = WfgSopTempModel::with('barang:id,mid_barang,nama_barang,qty_box')
            ->where('barang_id', $id)
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function getDataReport(Request $request)
    {
        try {
            $tanggalFilter = $request->filled('tanggal') ? $request->tanggal : now()->toDateString();
            $principalFilter = $request->input('principal');
            $user = Auth::user();

            // 1. Cari SOP final sesuai tanggal
            $sop = WfgSopModel::with('user:id,username')
                ->whereDate('tgl_opname', $tanggalFilter)
                ->first();

            if (!$sop) {
                return response()->json([
                    'status' => 'success',
                    'data' => [],
                    'message' => 'Belum ada SOP final untuk tanggal ini.'
                ]);
            }

            // 2. Ambil summaries (unik per barang)
            $summariesQuery = WfgSopSummariesModel::with('barang:id,mid_barang,nama_barang,qty_box,uom,principal')
                ->where('sop_id', $sop->id);

            // 3. Ambil details (bisa ada beberapa per barang)
            $detailsQuery = WfgSopDetailModel::with('barang:id,mid_barang,nama_barang,qty_box,principal')
                ->where('sop_id', $sop->id);

            // 🔹 Terapkan filter principal seperti di getData()
            if ($user->jabatan === 'operator') {
                $userPrincipal = $user->principal?->principal;
                if ($userPrincipal) {
                    $summariesQuery->whereHas('barang', fn($q) => $q->where('principal', $userPrincipal));
                    $detailsQuery->whereHas('barang', fn($q) => $q->where('principal', $userPrincipal));
                } else {
                    $summariesQuery->whereRaw('1 = 0');
                    $detailsQuery->whereRaw('1 = 0');
                }
            } elseif (!empty($principalFilter)) {
                $summariesQuery->whereHas('barang', fn($q) => $q->where('principal', $principalFilter));
                $detailsQuery->whereHas('barang', fn($q) => $q->where('principal', $principalFilter));
            }

            $summaries = $summariesQuery->get();
            $details = $detailsQuery->get();

            return response()->json([
                'status' => 'success',
                'sop' => [
                    'id' => $sop->id,
                    'tgl_opname' => $sop->tgl_opname,
                    'username' => $sop->user->username,
                ],
                'summaries' => $summaries,
                'details' => $details,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data report SOP: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data report.',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateTempBatch(Request $request)
    {
        $updates = $request->input('updates', []);

        foreach ($updates as $u) {
            $temp = WfgSopTempModel::with('barang')->find($u['id']);
            if (!$temp || !$temp->barang) continue;

            $qtyFull = $u['qty_full'] ?? 0;
            $qtyReceh = $u['qty_receh'] ?? 0;
            $qtyBox = $temp->barang->qty_box ?? 0;

            $summary = ($qtyFull * $qtyBox) + $qtyReceh;

            $temp->update([
                'qty_full' => $qtyFull,
                'qty_receh' => $qtyReceh,
                'summary' => $summary,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Semua data berhasil diperbarui'
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'qty_full'   => 'required|array',
            'qty_receh'  => 'required|array',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // cek minimal ada satu qty (full/receh) berisi > 0
        $qtyFull = $request->input('qty_full', []);
        $qtyReceh = $request->input('qty_receh', []);
        $hasQty = false;
        $maxLen = max(count($qtyFull), count($qtyReceh));
        for ($i = 0; $i < $maxLen; $i++) {
            // Menggunakan isset untuk menghindari error jika array tidak seragam
            $f = isset($qtyFull[$i]) ? (float) str_replace(',', '.', $qtyFull[$i]) : 0;
            $r = isset($qtyReceh[$i]) ? (float) str_replace(',', '.', $qtyReceh[$i]) : 0;
            if ($f > 0 || $r > 0) {
                $hasQty = true;
                break;
            }
        }

        if (!$hasQty) {
            return response()->json([
                'status' => 'error',
                'message' => 'Minimal isi salah satu Qty Full atau Qty Receh sebelum menyimpan.'
            ], 422);
        }

        try {
            DB::beginTransaction(); // Mulai transaksi di sini

            $sop = WfgSopModel::findOrFail($id);

            // 1. Tentukan barang_id (ambil dari summary lama atau detail pertama)
            $barang_id = WfgSopSummariesModel::where('sop_id', $sop->id)->value('barang_id');
            if (!$barang_id) {
                $firstDetail = WfgSopDetailModel::where('sop_id', $sop->id)->first();
                $barang_id = $firstDetail ? $firstDetail->barang_id : null;
            }

            if (!$barang_id) {
                throw new \Exception('Tidak dapat menentukan barang terkait SOP ini.');
            }

            // 2. Ambil info barang (qty_box)
            $barang = BarangWfgModel::findOrFail($barang_id);
            $barang_qty_box = $barang->qty_box ?? 1;

            // 3. Hitung TOTAL FISIK BARU
            $totalFisik = 0;
            for ($i = 0; $i < $maxLen; $i++) {
                $qty_full = isset($qtyFull[$i]) ? (float) str_replace(',', '.', $qtyFull[$i]) : 0;
                $qty_receh = isset($qtyReceh[$i]) ? (float) str_replace(',', '.', $qtyReceh[$i]) : 0;
                $qty_fisik = ($qty_full * $barang_qty_box) + $qty_receh;
                $totalFisik += $qty_fisik;
            }

            // 4. Ambil QTY SISTEM (SOH)
            $soh = StockOnHandModel::where('barang_id', $barang_id)->first();
            $qty_sistem = $soh ? $soh->qty_soh : 0;

            // 5. Hitung Selisih
            $selisih = $totalFisik - $qty_sistem;

            // Validasi selisih & keterangan
            if (abs($selisih) > 0 && empty($request->keterangan)) {
                return response()->json([
                    'status' => 'selisih',
                    'message' => "Terdapat selisih (Fisik: {$totalFisik}, Sistem: {$qty_sistem}). Keterangan wajib diisi."
                ]);
            }

            // 7. Hapus detail & summary lama
            WfgSopDetailModel::where('sop_id', $sop->id)->delete();
            WfgSopSummariesModel::where('sop_id', $sop->id)->delete();

            // 8. Insert ulang detail baru
            for ($i = 0; $i < $maxLen; $i++) {
                $qty_full = isset($qtyFull[$i]) ? (float) str_replace(',', '.', $qtyFull[$i]) : 0;
                $qty_receh = isset($qtyReceh[$i]) ? (float) str_replace(',', '.', $qtyReceh[$i]) : 0;

                // Hanya simpan detail yang memiliki kuantitas > 0 untuk menjaga kebersihan data
                if ($qty_full == 0 && $qty_receh == 0) {
                    continue;
                }

                WfgSopDetailModel::create([
                    'sop_id'    => $sop->id,
                    'barang_id' => $barang_id,
                    'qty_full'  => $qty_full,
                    'qty_receh' => $qty_receh,
                ]);
            }

            $status = '';

            if ($selisih === 0) {
                $status = 'sesuai';
            } else if ($selisih > 0) {
                $status = 'lebih';
            } else {
                $status = 'kurang';
            };

            // 9. Update/Create summary
            WfgSopSummariesModel::updateOrCreate(
                ['sop_id' => $sop->id, 'barang_id' => $barang_id],
                [
                    'qty_fisik'  => $totalFisik ?? 0,
                    'qty_sistem' => $qty_sistem ?? 0,
                    'selisih'    => $selisih ?? 0,
                    'status'    => $status ?? '',
                    'keterangan' => $request->keterangan ?? null, // Simpan keterangan baru
                ]
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Stock opname berhasil diperbarui.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui stock opname: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateKeterangan(Request $request, $id)
    {
        // Cari data summary berdasarkan ID
        $summary = WfgSopSummariesModel::find($id);

        if (!$summary) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        $keterangan = $request->input('keterangan');

        // Opsional: Lakukan validasi sisi server
        if ($summary->selisih !== 0 && empty($keterangan)) {
            return response()->json(['message' => 'Keterangan wajib diisi karena ada selisih.'], 422);
        }

        // Update dan Simpan
        $summary->keterangan = $keterangan;
        $summary->save();

        return response()->json(['message' => 'Keterangan berhasil diperbarui.', 'data' => $summary]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $sop = WfgSopModel::findOrFail($id);
            $sop->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Stock opname berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus stock opname: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyTemp($id)
    {
        try {
            $temp = WfgSopTempModel::find($id);

            if (!$temp) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data sementara tidak ditemukan.'
                ]);
            }

            $temp->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data sementara berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data.'
            ]);
        }
    }

    // Export SOP Report dengan pengecekan approval
    public function exportPdfSOPWFG(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
        ], [
            'tanggal.required' => 'Tanggal wajib diisi untuk ekspor.',
        ]);

        $tanggal = $request->tanggal;
        $principalFilter = $request->input('principal');
        $user = Auth::user();

        try {
            $sop = WfgSopModel::with(['user:id,username'])
                ->whereDate('tgl_opname', $tanggal)
                ->first();

            if (!$sop) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Tidak ada SOP untuk tanggal $tanggal",
                ], 404);
            }

            $summariesQuery = WfgSopSummariesModel::with([
                'barang:id,mid_barang,nama_barang,qty_box,uom,principal,is_new'
            ])->where('sop_id', $sop->id);

            $detailsQuery = WfgSopDetailModel::with([
                'barang:id,mid_barang,nama_barang,qty_box,principal,is_new'
            ])->where('sop_id', $sop->id);

            // Jika user operator → filter otomatis principal miliknya
            if ($user->jabatan === 'operator') {
                $userPrincipal = $user->principal?->principal;
                if (!$userPrincipal) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Akun operator tidak memiliki principal yang terdaftar.',
                    ], 403);
                }

                $summariesQuery->whereHas('barang', fn($q) => $q->where('principal', $userPrincipal));
                $detailsQuery->whereHas('barang', fn($q) => $q->where('principal', $userPrincipal));
            } elseif (!empty($principalFilter)) {
                $summariesQuery->whereHas('barang', fn($q) => $q->where('principal', $principalFilter));
                $detailsQuery->whereHas('barang', fn($q) => $q->where('principal', $principalFilter));
            }

            $filteredSummaries  = $summariesQuery->get();
            $filteredDetails    = $detailsQuery->get();

            // Validasi: Apakah ada barang yang masih is_new = 1?
            $barangBaru = collect([$filteredSummaries, $filteredDetails])
                ->flatten(1)
                ->filter(fn($item) => $item->barang && $item->barang->is_new == 1)
                ->pluck('barang.mid_barang')
                ->unique()
                ->values();

            if ($barangBaru->isNotEmpty()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Terdapat barang baru yang belum disetujui: ' . $barangBaru->implode(', ') . '. Silakan hubungi Foreman untuk approval terlebih dahulu.',
                ], 403);
            }

            // Lanjutkan jika tidak ada barang pending approval
            $approvals = WfgSopApprovalModel::with('approver')
                ->where('sop_id', $sop->id)
                ->get();

            $pendingOrRead = $approvals->whereIn('status', ['pending', 'read']);

            if ($approvals->isEmpty() || $pendingOrRead->isNotEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "SOP tanggal $tanggal belum disetujui semua approver.",
                ], 403);
            }

            $approvers = [];

            // Helper function untuk ambil path tanda tangan user
            $getSignaturePath = function ($user) {
                // Fallback default
                $dummy = asset('storage/images/ttd/dummy.jpg');
                if (!$user) return $dummy;

                // Cek apakah user punya relasi signature di DB
                if (isset($user->signature) && !empty($user->signature->signature)) {
                    $signaturePath = public_path($user->signature->signature);
                    if (File::exists($signaturePath)) {
                        return asset($user->signature->signature);
                    }
                }

                // Jika tidak ada relasi, cek file berdasarkan username (hasil dari update user)
                $usernameFile = 'uploads/signatures/signature_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $user->username) . '.png';
                $filePath = public_path($usernameFile);
                if (File::exists($filePath)) {
                    return asset($usernameFile);
                }

                return $dummy;
            };


            // === Operator (pembuat SOP) ===
            $operatorApproval = $sop->user;
            $approvers[] = $operatorApproval ? [
                'nama'   => $operatorApproval->username ?? 'Unknown',
                'ttd'    => $getSignaturePath($operatorApproval),
                'status' => 'approved',
            ] : [
                'nama'   => '-',
                'ttd'    => asset('storage/images/ttd/dummy.jpg'),
                'status' => null,
            ];


            // === Foreman ===
            $foremanApproval = $approvals->firstWhere(fn($a) => $a->approver->jabatan === 'foreman');
            $approvers[] = $foremanApproval ? [
                'nama'   => $foremanApproval->approver->username ?? 'Unknown',
                'ttd'    => $getSignaturePath($foremanApproval->approver),
                'status' => $foremanApproval->status,
            ] : [
                'nama'   => '-',
                'ttd'    => asset('storage/images/ttd/dummy.jpg'),
                'status' => null,
            ];

            // === Supervisor / Dept Head ===
            $supervisorApproval = $approvals->firstWhere(fn($a) => in_array($a->approver->jabatan, ['supervisor', 'dept_head']));
            $approvers[] = $supervisorApproval ? [
                'nama'   => $supervisorApproval->approver->username ?? 'Unknown',
                'ttd'    => $getSignaturePath($supervisorApproval->approver),
                'status' => $supervisorApproval->status,
            ] : [
                'nama'   => '-',
                'ttd'    => asset('storage/images/ttd/dummy.jpg'),
                'status' => null,
            ];

            $pdf = Pdf::loadView('pdf.sop_wfg_report', [
                'data'       => $sop,
                'tanggal'    => $tanggal,
                'summaries'  => $filteredSummaries,
                'details'    => $filteredDetails,
                'approvers'  => $approvers,
                'principal'  => $principalFilter ?? ($user->principal->principal ?? null),
            ]);

            if (empty($principalFilter) && $user->jabatan === 'operator') {
                $principalFilter = $user->principal?->principal;
            }

            $fileName = "SOP_WFG_REPORT_{$tanggal}" . ($principalFilter ? "_{$principalFilter}" : "") . ".pdf";

            return $pdf->stream($fileName);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengekspor data: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Reset Temp All
    public function resetTemp(Request $request)
    {
        $request->validate([
            'tgl_opname' => 'required|date',
        ]);

        try {
            $userId = Auth::id();
            $tglOpname = $request->tgl_opname;

            // Hapus semua data sementara milik user untuk tanggal opname yang diminta
            $deletedCount = WfgSopTempModel::where('created_by', $userId)
                ->whereDate('tgl_opname', $tglOpname)
                ->delete();

            if ($deletedCount > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Berhasil menghapus $deletedCount data sementara untuk tanggal $tglOpname."
                ]);
            }

            return response()->json([
                'status' => 'info',
                'message' => "Tidak ada data sementara ditemukan untuk tanggal $tglOpname."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data sementara.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Reset temp row
    public function resetTempRow(Request $request)
    {
        $sohId = $request->input('soh_id');
        $barangId = $request->input('barang_id');
        $today = now()->toDateString();

        if (!$sohId && !$barangId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter soh_id atau barang_id harus diisi.'
            ], 400);
        }

        $query = WfgSopTempModel::whereDate('tgl_opname', $today);

        if ($sohId) {
            $query->where('soh_id', $sohId);
        } else {
            $query->where('barang_id', $barangId)->whereNull('soh_id');
        }

        $deleted = $query->delete();

        return response()->json([
            'status' => 'success',
            'message' => $deleted > 0
                ? 'Data opname barang berhasil direset.'
                : 'Tidak ada data yang dihapus.'
        ]);
    }


    // **
    // Approval
    public function sendApproval(Request $request)
    {
        $request->validate([
            'sop_id' => 'required|exists:wfg_sop,id',
            'foreman_id' => 'required|exists:users,id',
            'supervisor_id' => 'required|exists:users,id',
        ]);

        $sopId = $request->sop_id;
        $approvers = [$request->foreman_id, $request->supervisor_id];

        foreach ($approvers as $userId) {
            WfgSopApprovalModel::updateOrCreate(
                ['sop_id' => $sopId, 'approver_id' => $userId],
                ['status' => 'pending']
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Approval berhasil dikirim ke semua approver.'
        ]);
    }

    public function getDataApproval()
    {
        $foremen = User::where('jabatan', 'foreman')->get(['id', 'username', 'jabatan']);
        $supervisors = User::where('jabatan', 'supervisor')->get(['id', 'username', 'jabatan']);

        return response()->json([
            'foremen' => $foremen,
            'supervisors' => $supervisors
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'sop_id' => 'required|exists:wfg_sop,id',
            'status' => 'required|in:approved,rejected',
            'catatan' => $request->status === 'rejected' ? 'required|string' : 'nullable|string',
        ]);

        $user = Auth::user();

        $approval = WfgSopApprovalModel::where('sop_id', $request->sop_id)
            ->where('approver_id', $user->id)
            ->first();

        if (!$approval) {
            return response()->json([
                'message' => 'Anda tidak terdaftar sebagai approver untuk SOP ini.'
            ], 403);
        }

        // Jika approval ditemukan, update status dan catatan
        $approval->update([
            'status' => $request->status,
            'catatan' => $request->catatan,
        ]);

        $approvals = WfgSopApprovalModel::where('sop_id', $request->sop_id)->get();

        $allApproved = $approvals->every(fn($a) => $a->status === 'approved');
        $anyRejected = $approvals->contains(fn($a) => $a->status === 'rejected');

        if ($anyRejected) {
            $finalStatus = 'rejected';
        } elseif ($allApproved) {
            $finalStatus = 'approved';
        } else {
            $finalStatus = 'waiting';
        }

        // Update status SOP utama
        WfgSopModel::where('id', $request->sop_id)->update([
            'status' => $finalStatus
        ]);

        return response()->json([
            'message' => $request->status === 'approved' ? 'SOP berhasil disetujui.' : 'SOP telah ditolak.',
            'data' => $approval
        ]);
    }
}
