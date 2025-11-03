<?php

namespace App\Http\Controllers\Wfg\stock_opname;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\SendWfgSopReportMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use App\Models\Wfg\stock_opname\WfgSopModel;
use App\Models\Wfg\stock_opname\BarangWfgModel;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Wfg\stock_opname\WfgSopTempModel;
use App\Models\Wfg\stock_opname\StockOnHandModel;
use App\Models\Wfg\stock_opname\WfgSopDetailModel;
use App\Models\Wfg\stock_opname\WfgSopStatusModel;
use App\Models\Wfg\stock_opname\WfgSopNewTempModel;
use App\Models\Wfg\stock_opname\WfgSopApprovalModel;
use App\Models\Wfg\stock_opname\WfgSopTempNoteModel;
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

    public function startOpname(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak terautentikasi'
            ], 401);
        }

        // Cek apakah user sudah memiliki opname aktif di tanggal hari ini
        $existing = WfgSopStatusModel::where('user_id', $user->id)
            ->whereDate('tgl_opname', now()->toDateString())
            ->first();

        if ($existing) {
            return response()->json([
                'status' => true,
                'message' => 'Opname sudah dimulai sebelumnya',
                'data' => $existing
            ]);
        }

        // Simpan status opname baru
        $status = WfgSopStatusModel::create([
            'user_id' => $user->id,
            'tgl_opname' => now()->toDateString(),
            'status' => 'started',
            'principal' => optional($user->principal)->principal ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Opname berhasil dimulai',
            'data' => $status
        ]);
    }

    public function getStatusOpname(Request $request)
    {
        $userId = $request->user()->id;

        $status = WfgSopStatusModel::where('user_id', $userId)
            ->whereDate('tgl_opname', now()->toDateString())
            ->first();

        if ($status) {
            return response()->json(['status' => $status->status]);
        }

        return response()->json(['status' => 'idle']);
    }


    public function saveTemp(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:qty,note,both',
            'soh_id' => 'required|exists:wfg_soh,id',
            'barang_id' => 'required|exists:wfg_barang,id',
            'qty_full' => 'nullable|integer',
            'qty_receh' => 'nullable|integer',
            'summary' => 'nullable|integer',
            'keterangan' => 'nullable|string|max:255'
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

        $keterangan = trim($request->keterangan ?? '');

        // ========================
        //  MODE QTY
        // ========================
        if ($request->mode === 'qty' || $request->mode === 'both') {
            $qtyFull = $request->qty_full ?? 0;
            $qtyReceh = $request->qty_receh ?? 0;
            $qtyBox = $barang->qty_box ?? 0;
            $summary = ($qtyFull * $qtyBox) + $qtyReceh;

            if (
                ($request->qty_full === null || $request->qty_full === '') &&
                ($request->qty_receh === null || $request->qty_receh === '')
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Isi minimal salah satu: Qty Full atau Qty Receh.',
                ], 422);
            }

            $temp = WfgSopTempModel::create([
                'soh_id' => $request->soh_id,
                'barang_id' => $request->barang_id,
                'qty_full' => $qtyFull,
                'qty_receh' => $qtyReceh,
                'summary' => $summary,
                'created_by' => Auth::id() ?? 1,
                'tgl_opname' => now(),
                'principal' => $principal,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data qty tersimpan sementara (histori baru dibuat).',
                'data' => $temp,
            ]);
        }

        // ========================
        //  MODE NOTE
        // ========================
        if ($request->mode === 'note' || $request->mode === 'both') {
            if ($keterangan === '') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Keterangan tidak boleh kosong pada mode note.',
                ], 422);
            }

            // Cek apakah sudah ada catatan di hari yang sama
            $tempNote = WfgSopTempNoteModel::where('soh_id', $request->soh_id)
                ->where('barang_id', $request->barang_id)
                ->whereDate('tgl_opname', now()->toDateString())
                ->first();

            if ($tempNote) {
                // Update catatan jika sudah ada
                $tempNote->update([
                    'catatan'    => $keterangan,
                    'updated_at' => now(),
                ]);

                $message = 'Catatan diperbarui (update data hari ini).';
            } else {
                // Create baru kalau belum ada
                $tempNote = WfgSopTempNoteModel::create([
                    'soh_id'     => $request->soh_id,
                    'barang_id'  => $request->barang_id,
                    'catatan'    => $keterangan,
                    'created_by' => Auth::id() ?? 1,
                    'tgl_opname' => now(),
                    'principal'  => $principal,
                ]);

                $message = 'Catatan tersimpan sementara (histori note baru dibuat).';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $tempNote,
            ]);
        }
    }

    public function saveTempNew(Request $request)
    {
        $request->validate([
            'mid_barang' => 'required|string|max:100',
            'nama_barang' => 'required|string|max:255',
            'uom' => 'required|string|max:255',
            'qty_box' => 'required|integer|min:1',
            'principal_barang' => 'required|string',
            'unrest' => 'required|integer|min:0',
            'qi' => 'nullable|integer|min:0',
            'blocked' => 'nullable|integer|min:0',
            'qty_full' => 'required|integer|min:0',
            'qty_receh' => 'required|integer|min:0',
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
                'principal' => $request->principal_barang,
                'uom' => $request->uom ?? 'PCS',
                'status' => 'aktif',
                'is_new' => true, // tandai barang baru
            ]
        );

        // 🔹 Hitung summary
        $summary = ($request->qty_full * $barang->qty_box) + $request->qty_receh;

        // 🔹 Simpan juga ke StockOnHand (jika belum ada)
        $soh = StockOnHandModel::firstOrCreate(
            [
                'barang_id' => $barang->id,
                'principal' => $principal
            ],
            [
                'user_id' => Auth::id(),
                'qty_soh' => $summary,
                'qty_unrest' => $request->unrest ?? 0,
                'qty_qi' => $request->qi ?? 0,
                'qty_block' => $request->blocked ?? 0,
                'last_updated' => now(),
            ]
        );

        // 🔹 Jika sudah ada, update data-nya
        if (!$soh->wasRecentlyCreated) {
            $soh->update([
                'qty_soh' => $summary,
                'qty_unrest' => $request->unrest ?? $soh->qty_unrest,
                'qty_qi' => $request->qi ?? $soh->qty_qi,
                'qty_block' => $request->blocked ?? $soh->qty_block,
                'last_updated' => now(),
            ]);
        }

        // 🔹 Simpan ke temp opname
        $temp = WfgSopTempModel::create([
            'barang_id' => $barang->id,
            'soh_id' => $soh->id, // karena belum ada di SOH
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
            'message' => 'Barang baru berhasil ditambahkan, disimpan sementara, dan dicatat di SOH.',
            'data' => [
                'barang' => $barang,
                'temp' => $temp,
                'soh' => $soh,
            ],
        ]);
    }

    public function processOpname(Request $request)
    {
        $request->validate([
            'tgl_opname' => 'required|date',
            'mode' => 'required|in:check,final',
        ]);

        $mode = $request->mode;
        $user = Auth::user();
        $tglOpname = $request->tgl_opname;
        $keteranganInput = $request->input('keterangan', []);

        // 🔹 Tentukan principal
        if ($user->jabatan === 'operator') {
            $principalFilter = optional($user->principal)->principal ?? null;
            if (empty($principalFilter)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun operator belum memiliki principal.',
                ], 403);
            }
        } else {
            $principalFilter = $request->input('principal');
            if (empty($principalFilter)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Principal harus dipilih.',
                ], 422);
            }
        }

        // 🔹 Ambil temp data
        $tempData = WfgSopTempModel::query()
            ->when(
                $user->jabatan === 'operator',
                fn($q) =>
                $q->where('created_by', $user->id)
            )
            ->where('tgl_opname', $tglOpname)
            ->where('principal', $principalFilter)
            ->get();

        if ($tempData->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data sementara masih kosong.',
            ]);
        }

        // 🔹 Ambil barang SOH hari ini
        $barangHariIni = StockOnHandModel::with('barang:id,mid_barang,nama_barang,principal')
            ->whereHas('barang', fn($q) => $q->where('principal', $principalFilter))
            ->whereDate('last_updated', Carbon::today())
            ->get();

        // 🔹 Cek yang belum di-opname
        $barangBelumOpname = [];
        foreach ($barangHariIni as $soh) {
            if (!$tempData->firstWhere('barang_id', $soh->barang->id)) {
                $barangBelumOpname[] = [
                    'mid_barang'  => $soh->barang->mid_barang,
                    'nama_barang' => $soh->barang->nama_barang,
                ];
            }
        }

        if ($mode === 'final' && count($barangBelumOpname) > 0) {
            return response()->json([
                'status'  => 'belum_opname',
                'message' => 'Masih ada barang yang belum di-opname.',
                'data'    => $barangBelumOpname,
            ]);
        }

        // 🔹 Cek selisih
        $grouped = [];
        foreach ($tempData as $temp) {
            $barangId = $temp->barang_id;
            $soh = $temp->soh_id ? StockOnHandModel::find($temp->soh_id) : null;

            if (!isset($grouped[$barangId])) {
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

        $tempNotes = WfgSopTempNoteModel::where('tgl_opname', $tglOpname)
            ->pluck('catatan', 'barang_id'); // hasil: [barang_id => keterangan]

        $selisihList = [];
        foreach ($grouped as $barangId => $g) {
            $selisih = $g['qty_fisik'] - $g['qty_sap'];
            $keterangan = $keteranganInput[$barangId]
                ?? ($tempNotes[$barangId] ?? null);

            if ($selisih != 0 && empty($keterangan)) {
                $g['selisih'] = $selisih;
                $selisihList[] = $g;
            }

            $grouped[$barangId]['keterangan'] = $keterangan;
        }

        // Kalau mode = check → stop di sini
        if ($mode === 'check') {
            return response()->json([
                'status' => 'success',
                'message' => count($selisihList) > 0
                    ? 'Ada selisih pada beberapa barang. Silakan periksa kembali sebelum finalisasi.'
                    : 'Semua data opname sudah lengkap & valid.',
                'data' => $selisihList,
            ]);
        }

        if ($mode === 'final') {
            $selisihTanpaKet = collect($grouped)->filter(function ($g) {
                $selisih = $g['qty_fisik'] - $g['qty_sap'];
                return $selisih != 0 && empty($g['keterangan']);
            })->values()->all();

            if (count($selisihTanpaKet) > 0) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Masih ada barang dengan selisih namun belum diberikan keterangan. Mohon isi keterangan terlebih dahulu sebelum finalisasi.',
                    'data'    => $selisihTanpaKet,
                ]);
            }
        }

        if ($mode === 'final' && $user->jabatan === 'operator') {
            $existingSop = WfgSopModel::whereDate('tgl_opname', $tglOpname)
                ->where('principal', $principalFilter)
                ->first();

            if ($existingSop) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah melakukan opname hari ini untuk principal ' . $principalFilter . '. Tidak dapat melakukan opname lebih dari sekali per hari. Hubungi Foreman!',
                ]);
            }
        }

        // Kalau mode = final → lanjut simpan SOP
        try {
            DB::beginTransaction();

            WfgSopStatusModel::query()->update([
                'status' => 'finished',
            ]);

            $sop = WfgSopModel::create([
                'tgl_opname' => $tglOpname,
                'user_id' => $user->id,
                'status' => 'draft',
                'principal' => $principalFilter,
            ]);

            foreach ($grouped as $g) {
                $selisih = $g['qty_fisik'] - $g['qty_sap'];
                $status = $selisih > 0 ? 'lebih' : ($selisih < 0 ? 'kurang' : 'match');

                WfgSopSummariesModel::create([
                    'sop_id' => $sop->id,
                    'barang_id' => $g['barang_id'],
                    'qty_fisik' => $g['qty_fisik'],
                    'qty_sistem' => $g['qty_sap'],
                    'selisih' => $selisih,
                    'status' => $status,
                    'keterangan' => $g['keterangan'] ?? null,
                ]);
            }

            foreach ($tempData as $temp) {
                WfgSopDetailModel::create([
                    'sop_id' => $sop->id,
                    'barang_id' => $temp->barang_id,
                    'qty_full' => $temp->qty_full,
                    'qty_receh' => $temp->qty_receh,
                ]);
            }

            WfgSopTempModel::where('tgl_opname', $tglOpname)
                ->where('principal', $principalFilter)
                ->when(
                    $user->jabatan === 'operator',
                    fn($q) =>
                    $q->where('created_by', $user->id)
                )
                ->delete();

            WfgSopTempNoteModel::where('tgl_opname', $tglOpname)
                ->where('principal', $principalFilter)
                ->when(
                    $user->jabatan === 'operator',
                    fn($q) =>
                    $q->where('created_by', $user->id)
                )
                ->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data opname berhasil disimpan final.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal simpan data: ' . $th->getMessage(),
            ], 500);
        }
    }


    /**
     * Edit Opname
     */
    public function editOpname($sop_id)
    {
        $user = Auth::user();

        // Cek role
        if ($user->jabatan === 'operator') {
            return response()->json([
                'status' => 'error',
                'message' => 'Operator tidak diizinkan melakukan edit opname.'
            ], 403);
        }

        // Ambil SOP beserta relasi
        $sop = WfgSopModel::with(['details', 'summaries'])->findOrFail($sop_id);

        // Bersihkan data temp lama
        WfgSopTempModel::where('principal', $sop->principal)
            ->where('tgl_opname', $sop->tgl_opname)
            ->delete();

        // Salin dari detail ke temp
        foreach ($sop->details as $detail) {
            $summary = $sop->summaries->firstWhere('barang_id', $detail->barang_id);
            WfgSopTempModel::create([
                'barang_id'   => $detail->barang_id,
                'qty_full'    => $detail->qty_full,
                'qty_receh'   => $detail->qty_receh,
                'summary'     => $detail->qty_full + $detail->qty_receh,
                'tgl_opname'  => $sop->tgl_opname,
                'principal'   => $sop->principal,
                'created_by'  => $user->id,
                'source_sop_id' => $sop->id,
                'soh_id'      => null, // bisa diisi kalau kamu simpan relasi SOH
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data opname berhasil dimuat ke mode edit.',
            'redirect' => route('opname.form', [
                'principal' => $sop->principal,
                'tgl' => $sop->tgl_opname,
                'edit' => 1,
                'sop_id' => $sop->id
            ])
        ]);
    }

    public function updateOpname(Request $request)
    {
        $request->validate([
            'sop_id' => 'required|exists:wfg_sop,id',
            'tgl_opname' => 'required|date',
        ]);

        $user = Auth::user();

        if ($user->jabatan === 'operator') {
            return response()->json([
                'status' => 'error',
                'message' => 'Operator tidak diizinkan melakukan edit opname.'
            ], 403);
        }

        $sop = WfgSopModel::findOrFail($request->sop_id);
        $tglOpname = $sop->tgl_opname;
        $principalFilter = $sop->principal;
        $keteranganInput = $request->input('keterangan', []);

        $tempData = WfgSopTempModel::where('tgl_opname', $tglOpname)
            ->where('principal', $principalFilter)
            ->get();

        if ($tempData->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data sementara masih kosong, isi terlebih dahulu.'
            ]);
        }

        // Grouping untuk summary (mirip finalize)
        $grouped = [];
        foreach ($tempData as $temp) {
            $barangId = $temp->barang_id;

            if (!isset($grouped[$barangId])) {
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

            $grouped[$barangId]['qty_fisik'] += (int)$temp->summary;
        }

        // Tambahkan keterangan
        foreach ($grouped as $barangId => $g) {
            $grouped[$barangId]['keterangan'] = $keteranganInput[$barangId] ?? null;
        }

        try {
            DB::beginTransaction();

            // Update header SOP (opsional: ubah status, user revisi, dsb)
            $sop->update([
                'updated_by' => $user->id,
                'status' => 'revised',
            ]);

            // Hapus detail & summary lama
            WfgSopDetailModel::where('sop_id', $sop->id)->delete();
            WfgSopSummariesModel::where('sop_id', $sop->id)->delete();

            // Insert ulang detail
            foreach ($tempData as $temp) {
                WfgSopDetailModel::create([
                    'sop_id' => $sop->id,
                    'barang_id' => $temp->barang_id,
                    'qty_full' => $temp->qty_full,
                    'qty_receh' => $temp->qty_receh,
                ]);
            }

            // Insert ulang summaries
            foreach ($grouped as $barangId => $g) {
                $selisih = $g['qty_fisik'] - $g['qty_sap'];
                $statusSelisih = $selisih > 0 ? 'lebih' : ($selisih < 0 ? 'kurang' : 'match');

                WfgSopSummariesModel::create([
                    'sop_id' => $sop->id,
                    'barang_id' => $barangId,
                    'qty_fisik' => $g['qty_fisik'],
                    'qty_sistem' => $g['qty_sap'],
                    'selisih' => $selisih,
                    'status' => $statusSelisih,
                    'keterangan' => $g['keterangan'] ?? null,
                ]);
            }

            // Bersihkan temp setelah selesai
            WfgSopTempModel::where('tgl_opname', $tglOpname)
                ->where('principal', $principalFilter)
                ->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data opname berhasil diperbarui.',
                'sop_id' => $sop->id,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan perubahan: ' . $th->getMessage(),
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

        if ($user->jabatan === 'operator') {
            $activePrincipal = optional($user->principal)->principal;
            if (empty($activePrincipal)) {
                // Operator tanpa principal: tidak boleh lanjut
                return response()->json([
                    'approval_status' => 'draft',
                    'approval_note' => null,
                    'approver_tracking' => [],
                    'message' => 'Principal tidak ditemukan pada akun operator. Hubungi foreman.',
                ], 422);
            }
        } else {
            // non-operator: boleh melihat semua principal jika tidak menyertakan filter
            $activePrincipal = $principalFilter ?: null;
        }

        // Jika ada filter tanggal, coba resolve SOP berdasarkan tanggal + principal (jika ada)
        if ($tanggalFilter) {
            $sopQuery = WfgSopModel::whereDate('tgl_opname', $tanggalFilter);

            if ($activePrincipal) {
                $sopQuery->where('principal', $activePrincipal);
            }
            // ambil yang paling baru jika ada beberapa
            $sop = $sopQuery->latest('id')->first();

            if (!$sop) {
                return response()->json([
                    'approval_status' => 'draft',
                    'approval_note' => null,
                    'approver_tracking' => [],
                ]);
            }

            $id = $sop->id; // override id arg dengan sop yang ditemukan
        } else {
            // jika tidak ada tanggal filter, pastikan SOP id dari param valid
            $sop = WfgSopModel::find($id);
            if (!$sop) {
                return response()->json([
                    'approval_status' => 'draft',
                    'approval_note' => null,
                    'approver_tracking' => [],
                ]);
            }

            // Jika user operator pastikan sop princial match user principal (safety)
            if ($user->jabatan === 'operator' && $sop->principal !== $activePrincipal) {
                return response()->json([
                    'approval_status' => 'draft',
                    'approval_note' => null,
                    'approver_tracking' => [],
                ], 403);
            }
        }

        // Ambil semua approval untuk SOP ini (kita pakai sop_id sebagai sumber kebenaran)
        $approvals = WfgSopApprovalModel::where('sop_id', $id)
            // optional: kalau kamu menyimpan principal di tabel approvals, uncomment berikut:
            // ->where('principal', $sop->principal)
            ->with('approver:id,username,jabatan')
            ->get();

        // Map untuk tracking (selalu tampilkan semua approver yang terdaftar)
        $approverTracking = $approvals->map(function ($a) {
            return [
                'nama' => $a->approver->username ?? '-',
                'jabatan' => $a->approver->jabatan ?? '-',
                'status' => ucfirst($a->status),
                'catatan' => $a->catatan,
            ];
        })->values();

        // Cek apakah user saat ini adalah approver untuk SOP ini (per sop_id)
        $approvalForUser = $approvals->firstWhere('approver_id', $userId);

        if ($approvalForUser) {
            return response()->json([
                'approval_status' => $approvalForUser->status,
                'approval_note' => $approvalForUser->catatan,
                'is_approver' => true,
                'approver_tracking' => $approverTracking,
            ]);
        }

        // Jika bukan approver, kita hitung status global:
        // aturan: jika ada rejected -> rejected
        // else if ada pending/read -> pending
        // else approved
        if ($approvals->isEmpty()) {
            return response()->json([
                'approval_status' => 'draft',
                'approval_note' => null,
                'is_approver' => false,
                'approver_tracking' => $approverTracking,
            ]);
        }

        // normalisasi status ke lowercase agar pengecekan konsisten
        $statuses = $approvals->pluck('status')->map(fn($s) => strtolower($s))->all();

        if (in_array('rejected', $statuses, true)) {
            $rejected = $approvals->firstWhere('status', 'rejected');
            return response()->json([
                'approval_status' => 'rejected',
                'approval_note' => $rejected->catatan,
                'is_approver' => false,
                'approver_tracking' => $approverTracking,
            ]);
        }

        if (collect($statuses)->contains(fn($st) => in_array($st, ['pending', 'read'], true))) {
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
        $perPage = 150;
        $today = now()->toDateString();

        // Tentukan principal filter
        // $principalToFilter = $user->jabatan === 'operator'
        //     ? $user->principal?->principal
        //     : $principalFilter;

        $userPrincipal = $user->principal?->principal;

        // 🔹 Subquery: total summary per SOH (hari ini)
        $tempSummarySubquery = DB::table('wfg_sop_temp')
            ->select(
                'soh_id',
                DB::raw('SUM(summary) AS total_summary')
            )
            ->whereDate('tgl_opname', $today)
            ->groupBy('soh_id');

        // Ambil data SOH + summary opname
        $finalQuery = DB::table('wfg_soh')
            ->join('wfg_barang', 'wfg_soh.barang_id', '=', 'wfg_barang.id')
            ->leftJoinSub($tempSummarySubquery, 'temp_sum', 'wfg_soh.id', '=', 'temp_sum.soh_id')
            ->select(
                'wfg_soh.id AS soh_id',
                'wfg_soh.barang_id',
                'wfg_soh.qty_soh',
                'wfg_soh.last_updated',
                'wfg_barang.mid_barang',
                'wfg_barang.nama_barang',
                'wfg_barang.qty_box',
                'wfg_barang.principal',
                DB::raw("COALESCE(temp_sum.total_summary, 0) AS total_summary"),
                DB::raw("(COALESCE(temp_sum.total_summary, 0) - COALESCE(wfg_soh.qty_soh, 0)) AS selisih"),
                DB::raw("CASE 
                    WHEN temp_sum.total_summary IS NOT NULL 
                         AND wfg_soh.qty_soh != temp_sum.total_summary 
                    THEN 1 ELSE 0 
                 END AS has_diff")
            )
            ->whereDate('wfg_soh.last_updated', $today);

        // Filter principal
        if ($user->jabatan === 'operator') {
            if ($userPrincipal === 'SMU') {
                // 🔹 Operator SMU bisa pilih tab principal
                if ($principalFilter) {
                    $finalQuery->where('wfg_barang.principal', $principalFilter);
                } else {
                    // default: semua principal kecuali BAS
                    $finalQuery->where('wfg_barang.principal', '!=', 'BAS');
                }
            } elseif ($userPrincipal) {
                // 🔹 Operator lain hanya lihat principal-nya sendiri
                $finalQuery->where('wfg_barang.principal', $userPrincipal);
            } else {
                $finalQuery->whereRaw('1 = 0');
            }
        } else {
            // 🔹 Non-operator (admin dsb) bisa filter manual via tab
            if ($principalFilter) {
                $finalQuery->where('wfg_barang.principal', $principalFilter);
            }
        }


        // Filter pencarian
        if ($searchTerm) {
            $finalQuery->where(function ($q) use ($searchTerm) {
                $q->where('wfg_barang.mid_barang', 'like', "%{$searchTerm}%")
                    ->orWhere('wfg_barang.nama_barang', 'like', "%{$searchTerm}%");
            });
        }

        // Urutkan: data yang punya selisih di atas
        $finalQuery
            ->orderByDesc('has_diff')
            ->orderByDesc(DB::raw('ABS(COALESCE(temp_sum.total_summary, 0) - COALESCE(wfg_soh.qty_soh, 0))')) // urutkan berdasarkan besar selisih
            ->orderBy('wfg_barang.mid_barang', 'asc');

        // Pagination
        $total = $finalQuery->count();
        $currentPage = Paginator::resolveCurrentPage();
        $items = $finalQuery->skip(($currentPage - 1) * $perPage)->take($perPage)->get();

        $mappedItems = $items->map(function ($item) {
            $selisih = (int) $item->selisih;

            // Kalau belum ada data temp sama sekali (total_summary = 0 dan belum diinput), kosongin status diff
            $status = null;
            if ($item->total_summary !== null && $item->total_summary != 0) {
                if ($selisih > 0) {
                    $status = 'lebih';
                } elseif ($selisih < 0) {
                    $status = 'kurang';
                } else {
                    $status = 'match';
                }
            }

            return (object) [
                'id' => $item->soh_id,
                'soh_id' => $item->soh_id,
                'barang_id' => $item->barang_id,
                'qty_soh' => (int) $item->qty_soh,
                'selisih' => (int) $item->selisih,
                'diff_status' => $status,
                'last_updated' => $item->last_updated,
                'mid_barang' => $item->mid_barang,
                'nama_barang' => $item->nama_barang,
                'qty_box' => $item->qty_box,
                'principal' => $item->principal,
            ];
        });

        $paginator = new LengthAwarePaginator(
            $mappedItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
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
        $today = now()->toDateString();
        $sohIds = array_map('intval', $request->input('soh_ids', []));
        $barangIds = array_map('intval', $request->input('barang_ids', []));
        $principalFilter = $request->input('principal');

        // 🔹 Tentukan principal filter
        if ($user->jabatan === 'operator') {
            $principalToFilter = $user->principal?->principal;

            // 🔸 Jika operator SMU, ambil semua principal kecuali BAS
            if ($principalToFilter === 'SMU') {
                $principalToFilter = '!= BAS';
            }
        } else {
            // Untuk non-operator (supervisor/admin)
            $principalToFilter = $principalFilter;
        }

        // ======================
        // 🔹 Ambil data QTY
        // ======================
        $qtyQuery = WfgSopTempModel::with([
            'soh.barang:id,principal,mid_barang,nama_barang',
            'barang:id,principal,mid_barang,nama_barang'
        ])
            ->whereDate('tgl_opname', $today)
            ->when(!empty($sohIds), fn($q) => $q->whereIn('soh_id', $sohIds))
            ->when(!empty($barangIds), fn($q) => $q->orWhereIn('barang_id', $barangIds)->whereNull('soh_id'));

        // 🔹 Filter principal
        $qtyQuery->where(function ($q) use ($principalToFilter) {
            if ($principalToFilter === '!= BAS') {
                // Kasus SMU → ambil semua kecuali BAS
                $q->whereHas('barang', fn($sub) => $sub->where('principal', '!=', 'BAS'))
                    ->orWhereHas('soh.barang', fn($sub) => $sub->where('principal', '!=', 'BAS'));
            } elseif ($principalToFilter) {
                // Kasus biasa
                $q->whereHas('barang', fn($sub) => $sub->where('principal', $principalToFilter))
                    ->orWhereHas('soh.barang', fn($sub) => $sub->where('principal', $principalToFilter));
            }
        });

        $qtyRecords = $qtyQuery->get()->map(function ($rec) {
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
                'mode'        => 'qty',
                'created_at'  => $rec->created_at,
                'updated_at'  => $rec->updated_at,
            ];
        })->filter();


        // ======================
        // 🔹 Ambil data NOTE
        // ======================
        $noteQuery = WfgSopTempNoteModel::with([
            'soh.barang:id,principal,mid_barang,nama_barang',
            'barang:id,principal,mid_barang,nama_barang'
        ])
            ->whereDate('tgl_opname', $today)
            ->when(!empty($sohIds), fn($q) => $q->whereIn('soh_id', $sohIds))
            ->when(!empty($barangIds), fn($q) => $q->orWhereIn('barang_id', $barangIds)->whereNull('soh_id'));

        $noteQuery->where(function ($q) use ($principalToFilter) {
            if ($principalToFilter === '!= BAS') {
                $q->whereHas('barang', fn($sub) => $sub->where('principal', '!=', 'BAS'))
                    ->orWhereHas('soh.barang', fn($sub) => $sub->where('principal', '!=', 'BAS'));
            } elseif ($principalToFilter) {
                $q->whereHas('barang', fn($sub) => $sub->where('principal', $principalToFilter))
                    ->orWhereHas('soh.barang', fn($sub) => $sub->where('principal', $principalToFilter));
            }
        });

        $noteRecords = $noteQuery->get()->map(function ($rec) {
            $barang = $rec->barang ?? optional(optional($rec->soh)->barang);
            if (!$barang) return null;

            return [
                'id'          => $rec->id,
                'soh_id'      => $rec->soh_id,
                'barang_id'   => $rec->barang_id,
                'mid_barang'  => $barang->mid_barang,
                'nama_barang' => $barang->nama_barang,
                'qty_full'    => null,
                'qty_receh'   => null,
                'summary'     => null,
                'keterangan'  => $rec->catatan,
                'principal'   => $barang->principal,
                'mode'        => 'note',
                'created_at'  => $rec->created_at,
                'updated_at'  => $rec->updated_at,
            ];
        })->filter();


        // ======================
        // 🔹 Gabungkan hasil keduanya
        // ======================
        $result = $qtyRecords->merge($noteRecords)->values();

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    public function getDataTempEdit(String $id)
    {
        $dataQty = WfgSopTempModel::with('barang:id,mid_barang,nama_barang,qty_box')
            ->where('barang_id', $id)
            ->whereDate('tgl_opname', now()->toDateString()) // optional: hanya hari ini
            ->orderBy('updated_at', 'asc')
            ->get();

        // Ambil catatan terbaru (kalau ada)
        $dataNote = WfgSopTempNoteModel::where('barang_id', $id)
            ->whereDate('tgl_opname', now()->toDateString()) // optional: hanya hari ini
            ->latest('updated_at')
            ->first();

        if ($dataQty->isEmpty() && !$dataNote) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data temp tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data_qty' => $dataQty,
            'data_note' => $dataNote
        ]);
    }

    public function getDataReport(Request $request)
    {
        try {
            $tanggalFilter = $request->filled('tanggal') ? $request->tanggal : now()->toDateString();
            $principalFilter = $request->input('principal');
            $search = trim($request->input('search', ''));
            $user = Auth::user();

            // Tentukan principal aktif
            if ($user->jabatan === 'operator') {
                $activePrincipal = optional($user->principal)->principal;
            } else {
                $activePrincipal = $principalFilter;
            }

            if (empty($activePrincipal)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Principal tidak ditemukan. Pastikan principal sudah diatur atau dipilih, Hubungi Foreman.',
                ], 422);
            }

            // 🔹 Filter berdasarkan tanggal + principal aktif
            $sop = WfgSopModel::with('user:id,username')
                ->whereDate('tgl_opname', $tanggalFilter)
                ->where('principal', $activePrincipal)
                ->first();

            if (!$sop) {
                return response()->json([
                    'status' => 'success',
                    'data' => [],
                    'message' => 'Belum ada SOP final untuk tanggal ini.'
                ]);
            }

            // 🔹 Ambil summaries dan details hanya berdasarkan sop_id
            $summariesQuery = WfgSopSummariesModel::with('barang:id,mid_barang,nama_barang,qty_box,uom,principal')
                ->where('sop_id', $sop->id);

            $detailsQuery = WfgSopDetailModel::with('barang:id,mid_barang,nama_barang,qty_box,uom,principal')
                ->where('sop_id', $sop->id);

            // Filter search jika ada
            if (!empty($search)) {
                $summariesQuery->whereHas('barang', function ($q) use ($search) {
                    $q->where('mid_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                });

                $detailsQuery->whereHas('barang', function ($q) use ($search) {
                    $q->where('mid_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                });
            }

            $summaries = $summariesQuery->get();
            $details   = $detailsQuery->get();

            return response()->json([
                'status' => 'success',
                'sop' => [
                    'id' => $sop->id,
                    'tgl_opname' => $sop->tgl_opname,
                    'status' => $sop->status,
                    'principal' => $sop->principal,
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

    public function getDataDetailEdit($barangId, Request $request)
    {
        $tanggal = $request->input('tanggal'); // ambil dari query param

        $query = WfgSopDetailModel::with('barang:id,mid_barang,nama_barang,qty_box')
            ->where('barang_id', $barangId)
            ->whereHas('sop', function ($q) use ($tanggal) {
                if ($tanggal) {
                    $q->whereDate('tgl_opname', $tanggal);
                }
            });

        $details = $query->get();

        if ($details->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan untuk barang ini pada tanggal tersebut.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $details
        ]);
    }

    public function getPrincipalList(Request $request)
    {
        $user = Auth::user();

        // Default kosong
        $principals = collect();

        // Jika user operator SMU → ambil semua principal kecuali BAS
        if ($user->jabatan === 'operator' && optional($user->principal)->principal === 'SMU') {
            $principals = BarangWfgModel::select('principal')
                ->whereNotNull('principal')
                ->where('principal', '!=', 'BAS')
                ->distinct()
                ->orderBy('principal', 'asc')
                ->pluck('principal')
                ->map(fn($p) => ['principal' => $p])
                ->values();
        }

        // Jika operator non-SMU → hanya principal dia sendiri
        elseif ($user->jabatan === 'operator') {
            $principalName = optional($user->principal)->principal;
            if ($principalName) {
                $principals = collect([
                    ['principal' => $principalName]
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'is_smu' => ($user->principal?->principal === 'SMU'),
            'principals' => $principals,
        ]);
    }



    /**
     * Update the specified resource in storage.
     */
    public function updateTempBatch(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',        // id dari wfg_sop_temp
            'items.*.qty_full' => 'nullable|integer|min:0',
            'items.*.qty_receh' => 'nullable|integer|min:0',
            'catatan' => 'nullable|string|max:1000',   // catatan global dari modal
        ]);

        $items = $validated['items'];
        $catatan = $validated['catatan'] ?? null;

        DB::beginTransaction();
        try {
            $processedPairs = []; // untuk menyimpan pasangan (soh_id, barang_id) yang perlu di-handle note

            foreach ($items as $it) {
                $temp = WfgSopTempModel::with('barang', 'soh')->find($it['id']);
                if (!$temp || !$temp->barang) continue;

                $qtyFull = isset($it['qty_full']) ? (int) $it['qty_full'] : 0;
                $qtyReceh = isset($it['qty_receh']) ? (int) $it['qty_receh'] : 0;
                $qtyBox = $temp->barang->qty_box ?? 0;

                $summary = ($qtyFull * $qtyBox) + $qtyReceh;

                $temp->qty_full = $qtyFull;
                $temp->qty_receh = $qtyReceh;
                $temp->summary = $summary;
                $temp->save();

                // simpan pasangan untuk catatan (gunakan soh_id & barang_id dari $temp, dan tanggal jika dikirim pada item)
                $tglOpname = $it['tanggal'] ?? null;
                // prefer tanggal yang dikirim, kalau tidak ada gunakan tgl_opname pada record temp (format date)
                if (!$tglOpname && $temp->tgl_opname) {
                    try {
                        $tglOpname = \Carbon\Carbon::parse($temp->tgl_opname)->toDateString();
                    } catch (\Throwable $e) {
                        $tglOpname = now()->toDateString();
                    }
                } elseif (!$tglOpname) {
                    $tglOpname = now()->toDateString();
                }

                $pairKey = ($temp->soh_id ?? 'null') . '|' . $temp->barang_id . '|' . $tglOpname;
                $processedPairs[$pairKey] = [
                    'soh_id' => $temp->soh_id,
                    'barang_id' => $temp->barang_id,
                    'tgl_opname' => $tglOpname,
                    'principal' => $temp->principal ?? null,
                ];
            }

            // Jika ada catatan yang dikirim (satu textarea untuk semua item di modal),
            // lakukan updateOrCreate untuk masing-masing pasangan (soh_id, barang_id, tanggal)
            if (!empty($catatan)) {
                foreach ($processedPairs as $pair) {
                    // jika soh_id null, kita tetap simpan berdasarkan barang_id & tanggal
                    $query = WfgSopTempNoteModel::where('barang_id', $pair['barang_id'])
                        ->whereDate('tgl_opname', $pair['tgl_opname']);

                    if (!is_null($pair['soh_id'])) {
                        $query->where('soh_id', $pair['soh_id']);
                    } else {
                        $query->whereNull('soh_id');
                    }

                    $existing = $query->first();

                    if ($existing) {
                        $existing->update([
                            'catatan' => $catatan,
                            'updated_at' => now(),
                        ]);
                    } else {
                        WfgSopTempNoteModel::create([
                            'soh_id' => $pair['soh_id'],
                            'barang_id' => $pair['barang_id'],
                            'catatan' => $catatan,
                            'created_by' => Auth::id() ?? 1,
                            'tgl_opname' => $pair['tgl_opname'],
                            'principal' => $pair['principal'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diperbarui.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
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

    public function updateEditData(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer', // id dari wfg_sop_detail
            'items.*.qty_full' => 'required|numeric',
            'items.*.qty_receh' => 'required|numeric',
            'items.*.tanggal' => 'required|date',
        ]);

        try {
            DB::beginTransaction();
            foreach ($validated['items'] as $item) {
                $detail = WfgSopDetailModel::with(['barang', 'sop'])
                    ->where('id', $item['id'])
                    ->whereHas('sop', function ($q) use ($item) {
                        $q->whereDate('tgl_opname', $item['tanggal']);
                    })
                    ->first();

                if (!$detail) {
                    continue; // skip kalau gak ketemu
                }

                $detail->qty_full = $item['qty_full'];
                $detail->qty_receh = $item['qty_receh'];
                $detail->save();
            }

            // Ambil tanggal opname dari item pertama
            $tanggal = $validated['items'][0]['tanggal'];

            // Ambil semua barang_id unik dari detail yang diupdate
            $barangIds = collect($validated['items'])->pluck('id')->unique();

            foreach ($barangIds as $detailId) {
                $detailSample = WfgSopDetailModel::find($detailId);
                if (!$detailSample) continue;

                $barangId = $detailSample->barang_id;

                // Ambil semua detail dengan barang_id & tanggal opname sama
                $details = WfgSopDetailModel::with(['barang', 'sop'])
                    ->where('barang_id', $barangId)
                    ->whereHas('sop', function ($q) use ($tanggal) {
                        $q->whereDate('tgl_opname', $tanggal);
                    })
                    ->get();

                // Hitung total qty fisik dari semua detail
                $totalFisik = 0;
                foreach ($details as $det) {
                    $qtyBox = $det->barang->qty_box ?? 0;
                    $totalFisik += ($det->qty_full * $qtyBox) + $det->qty_receh;
                }

                // Update summary
                $summary = WfgSopSummariesModel::where('sop_id', $detailSample->sop_id)
                    ->where('barang_id', $barangId)
                    ->first();

                if ($summary) {
                    $summary->qty_fisik = $totalFisik;
                    $summary->selisih = $summary->qty_fisik - $summary->qty_sistem;
                    if ($summary->selisih == 0) {
                        $summary->status = 'match';
                    } elseif ($summary->selisih > 0) {
                        $summary->status = 'lebih';
                    } else {
                        $summary->status = 'kurang';
                    }
                    $summary->save();
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Semua data berhasil diperbarui.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
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

    public function destroyTemp($id, Request $request)
    {
        try {
            $type = $request->input('tipe', 'qty'); // default 'qty'

            if ($type === 'note') {
                $deleted = WfgSopTempNoteModel::where('barang_id', $id)->delete();

                if (!$deleted) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Catatan tidak ditemukan.'
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Catatan berhasil dihapus.'
                ]);
            } else {
                // Hapus data qty sementara
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
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()
            ]);
        }
    }


    public function destroyEditData($id)
    {
        try {
            $detail = WfgSopDetailModel::find($id);

            if (!$detail) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $barangId = $detail->barang_id;
            $sopId = $detail->sop_id;
            $detail->delete();

            // Update summary setelah hapus
            $totalFull = WfgSopDetailModel::where('barang_id', $barangId)
                ->where('sop_id', $sopId)
                ->sum('qty_full');

            $totalReceh = WfgSopDetailModel::where('barang_id', $barangId)
                ->where('sop_id', $sopId)
                ->sum('qty_receh');

            $barang = BarangWfgModel::find($barangId);
            $qtyBox = $barang ? $barang->qty_box : 1;

            $qtyFisik = ($totalFull * $qtyBox) + $totalReceh;

            $summary = WfgSopSummariesModel::where('barang_id', $barangId)
                ->where('sop_id', $sopId)
                ->first();

            if ($summary) {
                $summary->qty_fisik = $qtyFisik;
                $summary->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Export SOP Report dengan pengecekan approval
    public function exportPdfSOPWFG(Request $request, $asContent = false)
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
            $sopQuery = WfgSopModel::with(['user:id,username'])
                ->whereDate('tgl_opname', $tanggal);

            // Kondisi 1: dipanggil lewat UI (user login)
            if ($user && $user->jabatan === 'operator' && optional($user->principal)->principal) {
                $userPrincipal = $user->principal->principal;
                $sopQuery->where('principal', $userPrincipal);
            }
            // Kondisi 2: dipanggil otomatis (auto-send) → tidak ada Auth, pakai principal dari request
            elseif (!empty($principalFilter)) {
                $sopQuery->where('principal', $principalFilter);
            }

            $sop = $sopQuery->first();

            if ($request->has('check')) {
                if (!$sop) {
                    return response()->json(['message' => "SOP tidak ditemukan untuk tanggal {$tanggal}."], 404);
                }
                return response()->json(['message' => 'Data siap diunduh.'], 200);
            }

            if (!$sop) {
                if ($asContent) {
                    throw new \Exception("SOP tidak ditemukan untuk principal {$principalFilter} pada tanggal {$tanggal}");
                }
                return redirect()->back()->with('error', "SOP tidak ditemukan untuk principal Anda pada tanggal {$tanggal}.");
            }

            if (!$sop) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Tidak ada SOP untuk tanggal $tanggal",
                ], 404);
            }

            $filteredSummaries = WfgSopSummariesModel::with(['barang'])
                ->where('sop_id', $sop->id)
                ->get();

            $filteredDetails = WfgSopDetailModel::with(['barang'])
                ->where('sop_id', $sop->id)
                ->get();

            // $filteredDetails    = $detailsQuery->get();

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

            $approvers = [];

            // Helper function untuk ambil path tanda tangan user
            $getSignaturePath = function ($user, $status = null) {
                // $dummy = public_path('storage/images/ttd/dummy.jpg');
                $dummy = '';

                if ($status !== 'approved') {
                    return $dummy;
                }

                if (!$user) return $dummy;

                if (isset($user->signature) && !empty($user->signature->signature)) {
                    $signaturePath = public_path($user->signature->signature);
                    if (File::exists($signaturePath)) {
                        return $signaturePath;
                    }
                }

                $usernameFile = 'uploads/signatures/signature_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $user->username) . '.png';
                $filePath = public_path($usernameFile);
                if (File::exists($filePath)) {
                    return $filePath;
                }

                return $dummy;
            };

            $operatorApproval = $sop->user ?? null;
            $approvers[] = [
                'nama' => $operatorApproval?->username ?? '-',
                'status' => 'approved', // Operator dianggap otomatis approve
                'ttd' => $getSignaturePath($operatorApproval, 'approved'),
                'catatan' => '',
            ];

            // === Foreman ===
            $foremanApproval = $approvals->first(fn($a) => $a->approver && $a->approver->jabatan === 'foreman');
            $approvers[] = [
                'nama' => $foremanApproval?->approver?->username ?? '-',
                'status' => $foremanApproval?->status ?? '-',
                'ttd' => $getSignaturePath($foremanApproval?->approver, $foremanApproval?->status),
                'catatan' => $foremanApproval?->catatan ?? '',
            ];

            // === Supervisor / Dept Head ===
            $supervisorApproval = $approvals->first(fn($a) => $a->approver && in_array($a->approver->jabatan, ['supervisor', 'dept_head']));
            $approvers[] = [
                'nama' => $supervisorApproval?->approver?->username ?? '-',
                'status' => $supervisorApproval?->status ?? '-',
                'ttd' => $getSignaturePath($supervisorApproval?->approver, $supervisorApproval?->status),
                'catatan' => $supervisorApproval?->catatan ?? '',
            ];

            $activePrincipal = $principalFilter ?? ($user->principal->principal ?? null);

            switch (strtoupper($activePrincipal)) {
                case 'SMU':
                    $logoPath = public_path('assets/images/logo/wings.png');
                    $logoWidth = 90;
                    break;

                case 'BAS':
                    $logoPath = public_path('assets/images/logo/logo.png');
                    $logoWidth = 170;
                    break;

                default:
                    $logoPath = public_path('assets/images/logo/logo.png');
                    $logoWidth = 170;
                    break;
            }

            if (!File::exists($logoPath)) {
                $logoPath = public_path('assets/images/logo/logo.png');
                $logoWidth = 170;
            }

            function bulanRomawi($bulan)
            {
                $romawi = [
                    1 => 'I',
                    2 => 'II',
                    3 => 'III',
                    4 => 'IV',
                    5 => 'V',
                    6 => 'VI',
                    7 => 'VII',
                    8 => 'VIII',
                    9 => 'IX',
                    10 => 'X',
                    11 => 'XI',
                    12 => 'XII'
                ];
                return $romawi[intval($bulan)] ?? '';
            }

            $tanggalCarbon = \Carbon\Carbon::parse($tanggal);
            $day   = str_pad($tanggalCarbon->day, 3, '0', STR_PAD_LEFT); // 005
            $bulanRomawi = bulanRomawi($tanggalCarbon->month);
            $tahun = $tanggalCarbon->year;

            $nomorDokumen = "{$day}/WFG/{$bulanRomawi}/{$tahun}";

            $pdf = Pdf::loadView('pdf.sop_wfg_report', [
                'data'       => $sop,
                'tanggal'    => $tanggal,
                'summaries'  => $filteredSummaries,
                'details'    => $filteredDetails,
                'approvers'  => $approvers,
                'principal'  => $activePrincipal,
                'logoPath'   => $logoPath,
                'logoWidth'   => $logoWidth,
                'nomorDokumen'  => $nomorDokumen,
            ]);

            if (empty($principalFilter) && $user->jabatan === 'operator') {
                $principalFilter = $user->principal?->principal;
            }

            $fileName = "SOP_WFG_REPORT_{$tanggal}" . ($principalFilter ? "_{$principalFilter}" : "") . ".pdf";

            if ($asContent) {
                return $pdf->output(); // kembalikan byte content PDF
            }
            return $pdf->stream($fileName);
        } catch (\Throwable $e) {
            if ($asContent) {
                throw $e;
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengekspor data: ' . $e->getMessage(),
            ], 500);
        }
    }

    // reset temp all
    public function resetTemp(Request $request)
    {
        $request->validate([
            'tgl_opname' => 'required|date',
        ]);

        try {
            $userId = Auth::id();
            $tglOpname = $request->tgl_opname;

            DB::beginTransaction();

            // Hapus data sementara (qty)
            $deletedTemp = WfgSopTempModel::where('created_by', $userId)
                ->whereDate('tgl_opname', $tglOpname)
                ->delete();

            // Hapus data catatan (note)
            $deletedNote = WfgSopTempNoteModel::where('created_by', $userId)
                ->whereDate('tgl_opname', $tglOpname)
                ->delete();

            DB::commit();

            if ($deletedTemp > 0 || $deletedNote > 0) {
                $msg = [];
                if ($deletedTemp > 0) $msg[] = "$deletedTemp data opname sementara";
                if ($deletedNote > 0) $msg[] = "$deletedNote catatan sementara";

                return response()->json([
                    'status' => 'success',
                    'message' => 'Berhasil menghapus ' . implode(' dan ', $msg) . " untuk tanggal $tglOpname."
                ]);
            }

            return response()->json([
                'status' => 'info',
                'message' => "Tidak ada data sementara ditemukan untuk tanggal $tglOpname."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data sementara.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // reset temp row
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

        DB::beginTransaction();
        try {
            // Build query for WfgSopTempModel
            $tempQuery = WfgSopTempModel::whereDate('tgl_opname', $today);
            if ($sohId) {
                $tempQuery->where('soh_id', $sohId);
            } else {
                $tempQuery->where('barang_id', $barangId)->whereNull('soh_id');
            }
            $deletedTemp = $tempQuery->delete();

            // Build query for WfgSopTempNoteModel (catatan)
            $noteQuery = WfgSopTempNoteModel::whereDate('tgl_opname', $today);
            if ($sohId) {
                $noteQuery->where('soh_id', $sohId);
            } else {
                $noteQuery->where('barang_id', $barangId)->whereNull('soh_id');
            }
            $deletedNote = $noteQuery->delete();

            DB::commit();

            $messageParts = [];
            if ($deletedTemp > 0) $messageParts[] = "{$deletedTemp} data temp dihapus";
            if ($deletedNote > 0) $messageParts[] = "{$deletedNote} catatan dihapus";

            $message = $messageParts ? implode(' dan ', $messageParts) . '.' : 'Tidak ada data yang dihapus.';

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'deleted' => [
                    'temp' => $deletedTemp,
                    'note' => $deletedNote,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mereset data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Approval
    public function sendApproval(Request $request)
    {
        $request->validate([
            'sop_id' => 'required|exists:wfg_sop,id',
            'foreman_id' => 'required|exists:users,id',
            'supervisor_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();

        // Ambil principal dari user login
        $userPrincipal = optional($user->principal)->principal;

        if (!$userPrincipal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Principal tidak ditemukan pada akun user. Hubungi admin atau foreman.',
            ], 422);
        }

        $sopId = $request->sop_id;

        // 🔹 Pastikan SOP sesuai dengan principal login
        $sop = WfgSopModel::where('id', $sopId)
            ->where('principal', $userPrincipal)
            ->first();

        if (!$sop) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data SOP tidak ditemukan untuk principal Anda.',
            ], 404);
        }

        // Update/Create approval per approver
        $approvers = [$request->foreman_id, $request->supervisor_id];

        foreach ($approvers as $userId) {
            WfgSopApprovalModel::updateOrCreate(
                [
                    'sop_id' => $sop->id,
                    'approver_id' => $userId,
                ],
                [
                    'status' => 'pending',
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => "Approval berhasil dikirim ke semua approver untuk principal {$userPrincipal}."
        ]);
    }


    public function getDataApproval()
    {
        $foremen = User::where('jabatan', 'foreman')->get(['id', 'username', 'jabatan']);
        $supervisors = User::where('jabatan', 'supervisor')->get(['id', 'username', 'jabatan']);
        $managers = User::where('jabatan', 'dept_head')->get(['id', 'username', 'jabatan']);

        return response()->json([
            'foremen' => $foremen,
            'supervisors' => $supervisors,
            'managers' => $managers
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

        WfgSopModel::where('id', $request->sop_id)->update([
            'status' => $finalStatus
        ]);

        // Jika semua approved, otomatis kirim laporan
        if ($finalStatus === 'approved') {
            $this->sendReportAuto($request->sop_id);
        }

        return response()->json([
            'message' => $request->status === 'approved' ? 'SOP berhasil disetujui.' : 'SOP telah ditolak.',
            'data' => $approval
        ]);
    }

    // Send report
    protected function sendReportAuto($sop_id)
    {
        try {
            // Ambil data SOP lengkap dengan relasi user dan approvals
            $sop = WfgSopModel::with(['user', 'approvals.approver'])->findOrFail($sop_id);
            $approvers = $sop->approvals;

            $tanggal = Carbon::parse($sop->tgl_opname)->format('Y-m-d');

            // Ambil principal langsung dari tabel SOP
            $principal = strtoupper($sop->principal);

            // === Generate PDF ===
            $fakeRequest = new \Illuminate\Http\Request([
                'sop_id' => $sop_id,
                'tanggal' => $tanggal,
                'principal' => $principal,
            ]);

            $pdfBinary = $this->exportPdfSOPWFG($fakeRequest, true);
            if ($pdfBinary instanceof \Illuminate\Http\Response) {
                $pdfBinary = $pdfBinary->getContent();
            }

            Log::info('PDF GENERATE RESULT', [
                'type' => gettype($pdfBinary),
                'class' => is_object($pdfBinary) ? get_class($pdfBinary) : null,
            ]);

            if (!is_string($pdfBinary) || empty($pdfBinary)) {
                throw new \Exception('PDF gagal dibuat atau kosong.');
            }

            $dir = 'public/reports';
            Storage::makeDirectory($dir);

            $fileName = "SOP_WFG_REPORT_{$tanggal}_{$principal}.pdf";
            $relativePath = "{$dir}/{$fileName}";
            $absolutePath = Storage::path($relativePath);

            Storage::put($relativePath, $pdfBinary);

            // === Ambil email approver berdasarkan sop_id ===
            $approverEmails = $approvers
                ->pluck('approver.email')
                ->filter()
                ->unique()
                ->values();

            // === Cari operator berdasarkan principal ===
            $operator = User::whereHas('principal', function ($q) use ($principal) {
                $q->where('principal', $principal);
            })->first();

            if ($operator && $operator->email) {
                $approverEmails->push($operator->email);
            }

            $approverEmails = $approverEmails->unique()->values();

            if ($approverEmails->isEmpty()) {
                throw new \Exception('Tidak ada penerima email terdaftar.');
            }

            // === Kirim email ke masing-masing penerima ===
            foreach ($approverEmails as $email) {
                Mail::to($email)->send(
                    new SendWfgSopReportMail($sop, $absolutePath, $tanggal, $principal)
                );
            }

            // (Opsional) kirim juga ke manager dept_head
            $manager = User::where('jabatan', 'dept_head')->first();
            if ($manager && $manager->email) {
                Mail::to($manager->email)->send(
                    new SendWfgSopReportMail($sop, $absolutePath, $tanggal, $principal)
                );
            }

            // Hapus file setelah semua email terkirim
            Storage::delete($relativePath);

            Log::info('AUTO SEND REPORT SUCCESS', [
                'sop_id' => $sop_id,
                'emails' => $approverEmails,
                'manager' => optional($manager)->email,
            ]);
        } catch (\Exception $e) {
            Log::error('AUTO SEND REPORT FAILED', [
                'sop_id' => $sop_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
