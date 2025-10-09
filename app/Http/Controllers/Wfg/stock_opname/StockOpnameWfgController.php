<?php

namespace App\Http\Controllers\Wfg\stock_opname;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Wfg\stock_opname\WfgSopModel;
use App\Models\Wfg\stock_opname\BarangWfgModel;
use App\Models\Wfg\stock_opname\StockOnHandModel;
use App\Models\Wfg\stock_opname\WfgSopDetailModel;
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
    public function store(Request $request)
    {
        $request->validate([
            'mid' => 'required|exists:wfg_barang,mid_barang',
            'tgl_opname' => 'required|date',
            'qty_full' => 'required|array',
            'qty_receh' => 'required|array',
            'keterangan' => 'nullable|string|max:255',
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

            // Cek existing data (kode tetap sama)
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

            $soh = StockOnHandModel::where('barang_id', $barang->id)->first();
            $qty_sistem = $soh ? $soh->qty_soh : 0;

            $entriesCount = count($request->qty_full);
            $totalFisik = 0;
            for ($i = 0; $i < $entriesCount; $i++) {
                $qty_full = $request->qty_full[$i] ?? 0;
                $qty_receh = $request->qty_receh[$i] ?? 0;
                $qty_fisik = ($qty_full * (float)($barang->qty_box ?? 1)) + $qty_receh;
                $totalFisik += $qty_fisik;
            }

            // Hitung Selisih
            $selisih = $totalFisik - $qty_sistem;

            if (abs($selisih) > 0 && empty($request->keterangan)) {
                return response()->json([
                    'status' => 'selisih',
                    'message' => "Terdapat selisih (Fisik: {$totalFisik}, Sistem: {$qty_sistem}). Keterangan wajib diisi."
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

            DB::beginTransaction();

            // 4. Proses Simpan SOP (Kode selanjutnya sama seperti sebelumnya, tapi menggunakan $totalFisik dan $selisih yang sudah dihitung)
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

            // Simpan summary
            WfgSopSummariesModel::create([
                'sop_id' => $sop->id,
                'barang_id' => $barang->id,
                'qty_fisik' => $totalFisik ?? 0,
                'qty_sistem' => $qty_sistem ?? 0,
                'selisih' => $selisih ?? 0,
                'status' => $status ?? '',
                'keterangan' => $request->keterangan ?? null,
            ]);

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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function getBarang(Request $request)
    {
        $query = BarangWfgModel::select('id', 'mid_barang', 'nama_barang', 'qty_box')
            ->with(['stockOnHand' => function ($q) {
                $q->select('id', 'barang_id', 'qty_soh')
                    ->latest()
                    ->take(1); // ambil hanya data SOH terbaru
            }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mid_barang', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%");
            });
        }

        $barang = $query->orderBy('nama_barang')->take(20)->get();

        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }


    public function getDataReport(Request $request)
    {
        try {
            $query = WfgSopModel::query();

            $query->whereHas('summaries', function ($q) {
                $q->whereHas('barang');
            });

            $query->with([
                'user:id,username',

                // Relasi Summaries - HANYA ambil summary yang barangnya masih AKTIF
                'summaries' => function ($q) {
                    // Filter utama: Hanya sertakan summary yang memiliki relasi 'barang' aktif
                    $q->whereHas('barang')
                        // Eager load relasi barang yang aktif
                        ->with('barang:id,mid_barang,nama_barang,qty_box,satuan');
                },

                // Relasi Details - HANYA ambil detail yang barangnya masih AKTIF
                'details' => function ($q) {
                    // Filter utama: Hanya sertakan detail yang memiliki relasi 'barang' aktif
                    $q->whereHas('barang')
                        // Eager load relasi barang yang aktif
                        ->with('barang:id,mid_barang,nama_barang,qty_box');
                }
            ]);

            // Filter optional (per tanggal)
            $tanggalFilter = $request->filled('tanggal') ? $request->tanggal : now()->toDateString();
            $query->whereDate('tgl_opname', $tanggalFilter);

            // Urutkan dari opname terbaru
            $data = $query->orderBy('tgl_opname', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            // Log error untuk debugging
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


    // Export SOP Report
    public function exportPdfSOPWFG(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
        ], [
            'tanggal.required' => 'Tanggal wajib diisi untuk ekspor.',
        ]);

        $tanggal = $request->tanggal;

        try {
            $query = WfgSopModel::query();

            $query->whereHas('summaries', function ($q) {
                $q->whereHas('barang');
            });

            $query->with([
                'user:id,username',

                'summaries' => function ($q) {
                    $q->whereHas('barang');

                    $q->with([
                        'barang' => function ($b) {
                            $b->select('id', 'mid_barang', 'nama_barang', 'qty_box', 'satuan')
                                ->with('stockOnHand:id,barang_id,qty_soh');
                        },
                    ]);
                },
            ]);

            $data = $query->whereDate('tgl_opname', $tanggal)
                ->orderBy('tgl_opname', 'desc')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data Stock Opname untuk tanggal yang dipilih: ' . $tanggal
                ], 404)->header('Content-Type', 'application/json'); // ✅ Pastikan header JSON
            }

            // Generate PDF
            $pdf = Pdf::loadView('pdf.sop_wfg_report', [
                'data' => $data,
                'tanggal' => $request->tanggal
            ]);

            $fileName = "SOP_WFG_REEPORT_{$tanggal}.pdf";
            return $pdf->stream($fileName);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengekspor data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
