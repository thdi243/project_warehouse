<?php

namespace App\Http\Controllers\Tkbm\ikat_terpal;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tkbm\ikat_terpal\IkatTerpal;
use App\Models\Tkbm\ikat_terpal\FeeIkatTerpal;
use App\Models\Tkbm\ikat_terpal\ProdukIkatTerpal;

class IkatTerpalController extends Controller
{
    public function index()
    {
        return view('tkbm.ikat_terpal.index');
    }

    public function report()
    {
        return view('tkbm.ikat_terpal.report');
    }

    public function store(Request $request)
    {
        // Validation input dasar
        $request->validate([
            'tanggal'    => 'required|date',
            'qty_pallet' => 'required|numeric|min:0',
            'jml_buruh'  => 'nullable|numeric|min:0',
            'catatan'    => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // 1. Cek produk aktif
            $produkAktif = ProdukIkatTerpal::where('aktif', true)->first();
            if (!$produkAktif) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak ada harga produk aktif. Silakan atur master harga produk terlebih dahulu.'
                ], 422);
            }

            // 2. Cek fee aktif
            $feeAktif = FeeIkatTerpal::where('aktif', true)->first();
            if (!$feeAktif) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak ada fee aktif. Silakan atur master fee terlebih dahulu.'
                ], 422);
            }

            // 3. Cek apakah sudah ada entri untuk tanggal yang sama
            $tanggalInput = $request->tanggal;
            $dataExist = IkatTerpal::whereDate('tanggal', $tanggalInput)->exists();

            if ($dataExist) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sudah ada data Ikat Terpal untuk tanggal ' . \Carbon\Carbon::parse($tanggalInput)->translatedFormat('j F Y') . '. Silahkan hubungin foreman untuk mengedit.'
                ], 422);
            }

            // Ambil nilai yang dibutuhkan
            $produkId       = $produkAktif->id;
            $feeId          = $feeAktif->id;
            $hargaPallet    = $produkAktif->harga_pallet;
            $feePersen      = $feeAktif->fee;

            // Hitung
            $subTotalBarang = $request->qty_pallet * $hargaPallet;
            $totalFee       = ($feePersen / 100) * $subTotalBarang;

            // Simpan data
            $data = IkatTerpal::create([
                'tanggal'         => $tanggalInput,
                'produk_id'       => $produkId,
                'fee_id'          => $feeId,
                'user_id'         => Auth::id() ?? 1,
                'qty_pallet'      => $request->qty_pallet,
                'jml_buruh'       => $request->jml_buruh,
                'subtotal_barang' => $subTotalBarang,
                'total_fee'       => $totalFee,
                'catatan'         => $request->catatan,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data Ikat Terpal berhasil disimpan',
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDataReport(Request $request)
    {
        $query = IkatTerpal::with(['produk:id,harga_pallet', 'fee:id,fee,ppn,pph', 'user:id,nama_lengkap,username']);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        $data = $query->orderBy('tanggal', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function exportPdf(Request $request)
    {
        $query = IkatTerpal::with(['produk:id,harga_pallet', 'fee:id,fee,ppn,pph']);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        $data = $query->orderBy('tanggal', 'asc')->get();

        $noDok = $this->generateNoDokFromRange($request->start_date, $request->end_date);

        // Ambil persentase dari record pertama (asumsi fee sama di seluruh periode)
        $fee_percent = 0;
        $ppn_percent = 0;
        $pph_percent = 0;

        if ($data->isNotEmpty() && $data->first()->fee) {
            $fee = $data->first()->fee;
            $fee_percent = $fee->fee ?? 0;
            $ppn_percent = $fee->ppn ?? 0;
            $pph_percent = $fee->pph ?? 0;
        }

        // Hitung summary
        $total_qty_pallet = $data->sum('qty_pallet');
        $total_subtotal   = $data->sum('subtotal_barang');
        $total_fee        = $data->sum('total_fee');

        $ppn_rate = $ppn_percent / 100;
        $pph_rate = $pph_percent / 100;

        $total_ppn = $total_fee * $ppn_rate;
        $total_pph = $total_fee * $pph_rate;

        $grand_total = ($total_subtotal + $total_fee + $total_ppn) - $total_pph;

        // Format untuk tampilan
        $summary = [
            'total_qty_pallet' => $total_qty_pallet,
            'total_subtotal'   => $total_subtotal,
            'total_fee'        => $total_fee,
            'total_ppn'        => $total_ppn,
            'total_pph'        => $total_pph,
            'grand_total'      => $grand_total,
        ];

        $pdf = Pdf::loadView('pdf.tkbm_ikat_terpal', [
            'data'         => $data,
            'summary'      => $summary,
            'fee_percent'  => $fee_percent,
            'ppn_percent'  => $ppn_percent,
            'pph_percent'  => $pph_percent,
            'noDok'  => $noDok,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('report-ikat-terpal-' . now()->format('Ymd-His') . '.pdf');
    }

    private function generateNoDokFromRange($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $month = $start->month;
        $year = $start->year;

        // mapping bulan ke romawi 
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

        $nomor = $start->day <= 15 ? '001' : '002';
        return sprintf("%s/WRM/%s/%s", $nomor, $romawi[$month], $year);
    }

    public function show($id)
    {
        $data = IkatTerpal::with(['produk', 'fee'])->find($id);

        if (!$data) {
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

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'qty_pallet' => 'required|numeric|min:0',
            'jml_buruh'  => 'nullable|numeric|min:0',
            'catatan'    => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            $data = IkatTerpal::findOrFail($id);

            $tanggalInput = $request->tanggal;
            $dataExist = IkatTerpal::whereDate('tanggal', $tanggalInput)->exists();

            if ($dataExist) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sudah ada data Ikat Terpal untuk tanggal ' . \Carbon\Carbon::parse($tanggalInput)->translatedFormat('j F Y')
                ], 422);
            }

            // Hitung ulang subtotal & fee (karena qty bisa berubah)
            $hargaPallet = $data->produk->harga_pallet;
            $feePersen   = $data->fee->fee;

            $subtotalBarang = $request->qty_pallet * $hargaPallet;
            $totalFee       = ($feePersen / 100) * $subtotalBarang;

            $data->update([
                'tanggal'         => $request->tanggal,
                'qty_pallet'      => $request->qty_pallet,
                'jml_buruh'       => $request->jml_buruh,
                'subtotal_barang' => $subtotalBarang,
                'total_fee'       => $totalFee,
                'catatan'         => $request->catatan,
                'user_id'         => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data Ikat Terpal berhasil diupdate',
                'data'    => $data
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $data = IkatTerpal::find($id);

            if (!$data) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data Ikat Terpal tidak ditemukan'
                ], 404);
            }

            $data->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data Ikat Terpal berhasil dihapus',
                'id'      => $id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}
