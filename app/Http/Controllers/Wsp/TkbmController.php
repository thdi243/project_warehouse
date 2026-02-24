<?php

namespace App\Http\Controllers\Wsp;

use Illuminate\Http\Request;
use App\Models\Tkbm\bps\TkbmModel;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tkbm\bps\TkbmFeeModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Tkbm\bps\TotalsTkbmModel;
use App\Models\Tkbm\bps\TkbmHargaProdukModel;

class TkbmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = TkbmModel::orderBy('date', 'desc')->get();

        // ambil ppn pph terakhir
        $feeMaster = TkbmFeeModel::orderBy('created_at', 'desc')->first();
        $fee = $feeMaster ? $feeMaster->fee : 0;
        $ppn = $feeMaster ? $feeMaster->ppn : 0;
        $pph = $feeMaster ? $feeMaster->pph : 0;

        return view('tkbm.data_tkbm', compact('data', 'fee', 'ppn', 'pph'));
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
        // Validasi data yang diterima dari request
        $validated = $request->validate([
            'date' => 'required|date',
            'petugas' => 'required|string|max:255',
            'shift' => 'required',
            'qtyTerpal' => 'integer|min:0',
            'qtySlipsheet' => 'integer|min:0',
            'qtyPallet' => 'integer|min:0',
            'jml_tkbm' => 'required|integer|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // cek data duplikat berdasarkan tanggal dan shift
        $exist = TkbmModel::where('date', $request->date)
            ->where('shift', $request->shift)
            ->first();

        if ($exist) {
            return response()->json([
                'ok' => false,
                'message' => 'Data untuk tanggal ' . $validated['date'] . ' dan shift ' . $validated['shift'] . ' sudah ada',
            ], 422);
        }

        // Harga Produk
        $lastFeeData = TkbmFeeModel::latest()->first();
        $lastHarga   = TkbmHargaProdukModel::latest()->first();

        if (!$lastHarga) {
            return response()->json([
                'ok' => false,
                'message' => 'Data harga belum tersedia.'
            ], 422);
        }

        if (!$lastFeeData) {
            return response()->json([
                'ok' => false,
                'message' => 'Data Fees & Taxes belum tersedia.'
            ], 422);
        }

        // hitung total qty berdasarkan harga terbaru
        $totalProduk =
            (($request->qtyTerpal ?? 0) * $lastHarga['harga_terpal']) +
            (($request->qtySlipsheet ?? 0) * $lastHarga['harga_slipsheet']) +
            (($request->qtyPallet ?? 0) * $lastHarga['harga_pallet']);

        $hargaId = $lastHarga->id;
        $feeId   = $lastFeeData->id;

        // Hitung total fee
        $feePersen = $lastFeeData->fee; // misal 6.5%
        $totalFee  = ($feePersen / 100) * $totalProduk;

        // Pajak
        $totalPpn = ($lastFeeData->ppn / 100) * $totalFee;
        $totalPph = ($lastFeeData->pph / 100) * $totalFee;
        $grandTotal = $totalProduk + $totalFee + $totalPpn - $totalPph;

        // 5. Simpan ke tabel tkbm
        $tkbm = TkbmModel::create([
            'date' => $request->date,
            'petugas' => $request->petugas,
            'shift' => $request->shift,
            'qty_terpal' => $request->qtyTerpal ?? 0,
            'qty_slipsheet' => $request->qtySlipsheet ?? 0,
            'qty_pallet' => $request->qtyPallet ?? 0,
            'jml_tkbm' => $request->jml_tkbm ?? 0,
            'keterangan' => $request->keterangan ?? null,
            'total_qty' => $totalProduk,
            'total_fee' => $totalFee,
            'fee_id' => $feeId,
            'harga_id' => $hargaId,
        ]);

        // ambil bulan & tahun dari request date
        $month = date('m', strtotime($request->date));
        $year  = date('Y', strtotime($request->date));

        // dd($request->date);
        // cek apakah sudah ada total untuk bulan & tahun tsb
        $totals = TotalsTkbmModel::where('month', $month)
            ->where('year', $year)
            ->first();

        if ($totals) {
            // update totals (increment)
            $totals->update([
                'total_terpal'    => $totals->total_terpal + ($request->qtyTerpal ?? 0),
                'total_slipsheet' => $totals->total_slipsheet + ($request->qtySlipsheet ?? 0),
                'total_pallet'    => $totals->total_pallet + ($request->qtyPallet ?? 0),
                'total_produk'    => $totals->total_produk + $totalProduk,
                'total_fee'       => $totals->total_fee + $totalFee,
                'total_ppn'       => $totals->total_ppn + $totalPpn,
                'total_pph'       => $totals->total_pph + $totalPph,
                'grand_total'     => $totals->grand_total + $grandTotal,
            ]);
        } else {
            // buat record baru
            $totals = TotalsTkbmModel::create([
                'month'           => $month,
                'year'            => $year,
                'total_terpal'    => $request->qtyTerpal ?? 0,
                'total_slipsheet' => $request->qtySlipsheet ?? 0,
                'total_pallet'    => $request->qtyPallet ?? 0,
                'total_produk'    => $totalProduk,
                'total_fee'       => $totalFee,
                'total_ppn'       => $totalPpn,
                'total_pph'       => $totalPph, // total pph selalu negatif
                'grand_total'     => $grandTotal,
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Data TKBM berhasil disimpan!',
            'data' => $tkbm,
            'rekap' => $totals
        ], 200);
    }

    public function syncTotalsTkbm()
    {
        $data = TkbmModel::selectRaw("
            MONTH(`date`) as month,
            YEAR(`date`) as year,
            SUM(qty_terpal) as total_terpal,
            SUM(qty_slipsheet) as total_slipsheet,
            SUM(qty_pallet) as total_pallet,
            SUM(total_qty) as total_produk,
            SUM(total_fee) as total_fee
        ")
            ->groupByRaw('YEAR(`date`), MONTH(`date`)')
            ->get();

        foreach ($data as $row) {
            $feeData = TkbmFeeModel::latest()->first();

            $totalFee = $row->total_qty * ($feeData->fee / 100);

            $totalPpn = ($feeData->ppn / 100) * $totalFee;
            $totalPph = ($feeData->pph / 100) * $totalFee;
            $grandTotal = $row->total_produk + $totalFee + $totalPpn - $totalPph;

            TotalsTkbmModel::updateOrCreate(
                ['month' => $row->month, 'year' => $row->year],
                [
                    'total_terpal' => $row->total_terpal,
                    'total_slipsheet' => $row->total_slipsheet,
                    'total_pallet' => $row->total_pallet,
                    'total_produk' => $row->total_produk,
                    'total_fee' => $totalFee,
                    'total_ppn' => $totalPpn,
                    'total_pph' => $totalPph,
                    'grand_total' => $grandTotal,
                ]
            );
        }

        return "Sync selesai 🚀";
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id = null)
    {
        $start = request()->query('start_date'); // format: YYYY-MM-DD
        $end   = request()->query('end_date');   // format: YYYY-MM-DD

        $query = TkbmModel::query();

        if ($start && $end) {
            // validasi format tanggal
            $startDate = date_create_from_format('Y-m-d', $start);
            $endDate   = date_create_from_format('Y-m-d', $end);

            if (!$startDate || !$endDate) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Format tanggal tidak valid. Gunakan YYYY-MM-DD.'
                ], 400);
            }

            // Pastikan end >= start
            if ($end < $start) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Tanggal akhir harus lebih besar atau sama dengan tanggal awal.'
                ], 400);
            }

            $query->whereBetween('date', [$start, $end]);
        } else {
            // default ke bulan & tahun sekarang
            $year = (int) date('Y');
            $month = (int) date('m');

            $query->whereYear('date', $year)
                ->whereMonth('date', $month);
        }

        $latestFee = TkbmFeeModel::orderBy('created_at', 'desc')->first();

        $data = $query->orderBy('date', 'asc')->get();

        $data->transform(function ($item) use ($latestFee) {
            $item->fee_value = $latestFee?->fee ?? 0;
            return $item;
        });

        // $data = $query->orderBy('date', 'desc')->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'ok' => true,
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'ok' => false,
                'message' => 'Data tidak ditemukan.'
            ], 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = TkbmModel::find($id);

        if ($data) {
            return response()->json([
                'ok' => true,
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'ok' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validasi data yang diterima dari request
        $validated = $request->validate([
            'date' => 'required|date',
            'petugas' => 'required|string|max:255',
            'shift' => 'required',
            'qty_terpal' => 'integer|min:0',
            'qty_slipsheet' => 'integer|min:0',
            'qty_pallet' => 'integer|min:0',
            'jml_tkbm' => 'integer|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $dataTkbm = TkbmModel::find($id);

        if (!$dataTkbm) {
            return response()->json([
                'ok' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        // Cek data duplikat (Sudah benar)
        $exist = TkbmModel::where('date', $request->date)
            ->where('shift', $request->shift)
            ->where('id', '!=', $id)
            ->first();

        if ($exist) {
            return response()->json([
                'ok' => false,
                'message' => 'Data untuk tanggal ' . $validated['date'] . ' dan shift ' . $validated['shift'] . ' sudah ada',
            ], 422);
        }

        // --- 1. AMBIL NILAI LAMA (UNTUK ROLLBACK) ---
        // Simpan nilai lama sebelum di-update, termasuk bulan/tahun lama
        $oldMonth = date('m', strtotime($dataTkbm->date));
        $oldYear = date('Y', strtotime($dataTkbm->date));

        $oldTotalQty = $dataTkbm->total_qty;
        $oldTotalFee = $dataTkbm->total_fee;

        // Untuk perhitungan PPN/PPH lama, kita harus hitung ulang dari fee_id lama
        $oldFeeData = TkbmFeeModel::find($dataTkbm->fee_id);
        $oldTotalPpn = ($oldFeeData && $oldFeeData->ppn) ? ($oldFeeData->ppn / 100) * $oldTotalFee : 0;
        $oldTotalPph = ($oldFeeData && $oldFeeData->pph) ? ($oldFeeData->pph / 100) * $oldTotalFee : 0;
        $oldGrandTotal = $oldTotalQty + $oldTotalFee + $oldTotalPpn - $oldTotalPph;


        // --- 2. HITUNG NILAI BARU (SAMA SEPERTI DI STORE) ---

        $lastFeeData = TkbmFeeModel::latest()->first();
        $lastHarga = TkbmHargaProdukModel::latest()->first();

        if (!$lastHarga || !$lastFeeData) {
            return response()->json(['ok' => false, 'message' => 'Data Harga atau Fees & Taxes belum tersedia.'], 422);
        }

        // Hitung total produk (total_qty) berdasarkan harga terbaru
        $totalProdukBaru = (($request->qty_terpal ?? 0) * $lastHarga['harga_terpal']) +
            (($request->qty_slipsheet ?? 0) * $lastHarga['harga_slipsheet']) +
            (($request->qty_pallet ?? 0) * $lastHarga['harga_pallet']);

        $hargaIdBaru = $lastHarga->id;
        $feeIdBaru = $lastFeeData->id;

        // Hitung total fee baru
        $feePersenBaru = $lastFeeData->fee;
        $totalFeeBaru = ($feePersenBaru / 100) * $totalProdukBaru;

        // Hitung Pajak baru
        $totalPpnBaru = ($lastFeeData->ppn / 100) * $totalFeeBaru;
        $totalPphBaru = ($lastFeeData->pph / 100) * $totalFeeBaru;
        $grandTotalBaru = $totalProdukBaru + $totalFeeBaru + $totalPpnBaru - $totalPphBaru;

        // Ambil bulan & tahun baru (mungkin berubah)
        $newMonth = date('m', strtotime($request->date));
        $newYear = date('Y', strtotime($request->date));


        try {
            DB::beginTransaction();

            // --- 3. ROLLBACK DARI TOTALS LAMA ---
            $totalsLama = TotalsTkbmModel::where('month', $oldMonth)
                ->where('year', $oldYear)
                ->first();

            if ($totalsLama) {
                // Kurangi totals dengan nilai lama
                $totalsLama->total_terpal    -= $dataTkbm->qty_terpal;
                $totalsLama->total_slipsheet -= $dataTkbm->qty_slipsheet;
                $totalsLama->total_pallet    -= $dataTkbm->qty_pallet;
                $totalsLama->total_produk    -= $oldTotalQty;
                $totalsLama->total_fee       -= $oldTotalFee;
                $totalsLama->total_ppn       -= $oldTotalPpn;
                $totalsLama->total_pph       -= $oldTotalPph;
                $totalsLama->grand_total     -= $oldGrandTotal;
                $totalsLama->save();
            }

            // --- 4. UPDATE DATA TKBM ---
            $dataTkbm->update([
                'date' => $request->date,
                'petugas' => $request->petugas,
                'shift' => $request->shift,
                'qty_terpal' => $request->qty_terpal ?? 0,
                'qty_slipsheet' => $request->qty_slipsheet ?? 0,
                'qty_pallet' => $request->qty_pallet ?? 0,
                'jml_tkbm' => $request->jml_tkbm ?? 0,
                'keterangan' => $request->keterangan ?? null,
                'total_qty' => $totalProdukBaru,
                'total_fee' => $totalFeeBaru,
                'fee_id' => $feeIdBaru,       // Simpan ID, BUKAN persentase fee
                'harga_id' => $hargaIdBaru,   // Simpan ID harga
            ]);

            // --- 5. APLIKASIKAN KE TOTALS BARU ---

            // Cek apakah bulan/tahun totals yang dituju sudah ada
            $totalsBaru = TotalsTkbmModel::firstOrNew([
                'month' => $newMonth,
                'year' => $newYear,
            ]);

            // Jika data baru atau data lama tapi di bulan yang sama
            if ($totalsBaru->exists) {
                // Update totals (increment)
                $totalsBaru->update([
                    'total_terpal'    => $totalsBaru->total_terpal + ($request->qty_terpal ?? 0),
                    'total_slipsheet' => $totalsBaru->total_slipsheet + ($request->qty_slipsheet ?? 0),
                    'total_pallet'    => $totalsBaru->total_pallet + ($request->qty_pallet ?? 0),
                    'total_produk'    => $totalsBaru->total_produk + $totalProdukBaru,
                    'total_fee'       => $totalsBaru->total_fee + $totalFeeBaru,
                    'total_ppn'       => $totalsBaru->total_ppn + $totalPpnBaru,
                    'total_pph'       => $totalsBaru->total_pph + $totalPphBaru,
                    'grand_total'     => $totalsBaru->grand_total + $grandTotalBaru,
                ]);
            } else {
                // Buat record baru (jika bulan/tahun berubah dan totals belum ada)
                $totalsBaru->fill([
                    'total_terpal'    => $request->qty_terpal ?? 0,
                    'total_slipsheet' => $request->qty_slipsheet ?? 0,
                    'total_pallet'    => $request->qty_pallet ?? 0,
                    'total_produk'    => $totalProdukBaru,
                    'total_fee'       => $totalFeeBaru,
                    'total_ppn'       => $totalPpnBaru,
                    'total_pph'       => $totalPphBaru,
                    'grand_total'     => $grandTotalBaru,
                ])->save();
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Data berhasil diupdate dan total bulanan telah diperbarui',
                'data' => $dataTkbm,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengupdate data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dataTkbm = TkbmModel::find($id);

        if (!$dataTkbm) {
            return response()->json([
                'ok' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        try {
            DB::beginTransaction();
            $date = \Carbon\Carbon::parse($dataTkbm->date);
            $month = $date->month;
            $year = $date->year;

            // 2. Cari record totals_tkbm yang sesuai
            $dataTotals = TotalsTkbmModel::where('month', $month)
                ->where('year', $year)
                ->first();

            // Cek jika record totals_tkbm ditemukan
            if ($dataTotals) {
                $dataTotals->total_terpal    -= $dataTkbm->qty_terpal;
                $dataTotals->total_slipsheet -= $dataTkbm->qty_slipsheet;
                $dataTotals->total_pallet    -= $dataTkbm->qty_pallet;
                $dataTotals->total_produk    -= $dataTkbm->total_qty;
                $dataTotals->total_fee       -= $dataTkbm->total_fee;

                $relevantFeeData = TkbmFeeModel::find($dataTkbm->fee_id);

                if ($relevantFeeData) {
                    $ppnPersen = $relevantFeeData->ppn;
                    $pphPersen = $relevantFeeData->pph;

                    $newTotalPpn = ($ppnPersen / 100) * $dataTotals->total_fee;
                    $newTotalPph = ($pphPersen / 100) * $dataTotals->total_fee;

                    // Hitung ulang Grand Total Bulanan
                    $newGrandTotal = $dataTotals->total_produk + $dataTotals->total_fee + $newTotalPpn - $newTotalPph;

                    // Update kolom di totals_tkbm
                    $dataTotals->total_ppn   = $newTotalPpn;
                    $dataTotals->total_pph   = $newTotalPph;
                    $dataTotals->grand_total = $newGrandTotal;

                    // 4. Simpan perubahan pada totals_tkbm
                    $dataTotals->save();
                }
            }

            $dataTkbm->delete();

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Data berhasil dihapus dan total bulanan telah diperbarui',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            // Penanganan error
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menghapus data dan memperbarui total: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export data to Excel based on the selected month.
     */
    public function export(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        // Validasi input
        if (!$startDate || !$endDate) {
            return redirect()->back()->with('error', 'Tanggal awal dan akhir wajib diisi.');
        }

        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end   = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Format tanggal tidak valid.');
        }

        if ($end->lt($start)) {
            return redirect()->back()->with('error', 'Tanggal akhir harus lebih besar atau sama dengan tanggal awal.');
        }

        // Ambil data dari database berdasarkan rentang tanggal
        $data = TkbmModel::with(['fee', 'harga'])
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'asc')
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada data pada rentang tanggal tersebut.'
            ], 200);
        }

        // Load template Excel
        $templatePath = public_path('assets/templates/excel/template_excel_tkbm.xlsx');
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Template Excel tidak ditemukan di: ' . $templatePath);
        }

        try {
            // Log::info('Starting Excel export', ['start_date' => $startDate, 'end_date' => $endDate]);

            // Baca file template
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($templatePath);
            $templateSheet = $spreadsheet->getActiveSheet();
            $cleanTemplate = clone $templateSheet;

            // Generate data untuk header
            $noDok = $this->generateNoDokFromRange($startDate, $endDate);
            $periodeText = Carbon::now()->format('j F Y');

            $dataBySheet = collect();

            foreach ($data as $item) {
                $day = Carbon::parse($item->date)->day;
                $month = Carbon::parse($item->date)->month;
                $year = Carbon::parse($item->date)->year;

                // Tentukan range day
                $range = $day <= 15 ? '1-15' : '16-31';

                // Gunakan kombinasi tahun-bulan-range sebagai key sheet
                $sheetKey = $year . '-' . $month . '-' . $range;

                if (!isset($dataBySheet[$sheetKey])) {
                    $dataBySheet[$sheetKey] = collect();
                }

                $dataBySheet[$sheetKey]->push($item);
            }

            // Proses setiap sheet
            $sheetIndex = 0;
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($templatePath);
            $templateSheet = $spreadsheet->getActiveSheet();
            $cleanTemplate = clone $templateSheet;

            foreach ($dataBySheet as $sheetKey => $items) {
                $currentSheet = $sheetIndex == 0 ? $templateSheet : clone $cleanTemplate;
                if ($sheetIndex > 0) {
                    $spreadsheet->addSheet($currentSheet);
                }

                // Set judul sheet berdasarkan range tanggal
                $firstDate = Carbon::parse($items->first()->date);
                $day = $firstDate->day;
                $periode = $day <= 15 ? 'Periode I' : 'Periode II';
                $sheetName = $firstDate->format('M Y') . ' ' . $periode;
                $currentSheet->setTitle(substr($sheetName, 0, 31));

                // Gunakan tanggal pertama di item sebagai periode
                $periodeText = Carbon::parse($items->first()->date)->format('j F Y');
                $noDok = $this->generateNoDokFromRange($items->first()->date, $items->last()->date);

                $this->processSheet($currentSheet, $items, $noDok, $periodeText, $sheetIndex + 1);
                $sheetIndex++;
            }

            $fileName = 'Data_TKBM_' . $startDate . '_to_' . $endDate . '.xlsx';

            // Save ke temporary file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $tempPath = tempnam(sys_get_temp_dir(), 'tkbm_export_');
            $writer->save($tempPath);

            // Log::info('File saved successfully', ['temp_path' => $tempPath, 'filename' => $fileName]);

            // Cleanup memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return response()->download($tempPath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            // Log::error('Excel export failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Gagal membuat file Excel: ' . $e->getMessage());
        }
    }

    // Export Pdf
    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        // $noDok = $request->query('no_dok');

        $noDok = $this->generateNoDokFromRange($startDate, $endDate);


        // Validasi input
        if (!$startDate || !$endDate) {
            return redirect()->back()->with('error', 'Tanggal awal dan akhir wajib diisi.');
        }

        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end   = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Format tanggal tidak valid.');
        }

        if ($end->lt($start)) {
            return redirect()->back()->with('error', 'Tanggal akhir harus lebih besar atau sama dengan tanggal awal.');
        }

        // Ambil data dari database berdasarkan rentang tanggal
        $data = TkbmModel::whereBetween('date', [$start, $end])
            ->orderBy('date', 'asc')
            ->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data pada rentang tanggal tersebut.');
        }

        $sum_terpal = 0;
        $sum_slipsheet = 0;
        $sum_pallet = 0;
        $sum_total_qty = 0;
        $sum_total_fee = 0;

        foreach ($data as $d) {
            $sum_terpal += $d['qty_terpal'];
            $sum_slipsheet += $d['qty_slipsheet'];
            $sum_pallet += $d['qty_pallet'];
            $sum_total_qty += $d['total_qty'];
            $sum_total_fee += $d['total_fee'];
        }

        // Ambil fee terbaru
        $latestFee = TkbmFeeModel::latest()->first();

        // Hitung PPn & PPh berdasarkan fee terbaru
        $ppn = ($latestFee->ppn / 100) * $sum_total_fee;
        $pph = ($latestFee->pph / 100) * $sum_total_fee;
        $grand_total = $sum_total_qty + $sum_total_fee + $ppn - $pph;

        // return response()->json([
        //     'status' => true,
        //     'message' => 'Data berhasil ditemukan',
        //     'data' => $data,
        //     "summary" => [
        //         "sum_terpal" => $sum_terpal,
        //         "sum_slipsheet" => $sum_slipsheet,
        //         "sum_pallet" => $sum_pallet,
        //         "sum_total_qty" => $sum_total_qty,
        //         "sum_total_fee" => $sum_total_fee,
        //         "ppn" => $ppn,
        //         "pph" => $pph,
        //         "grand_total" => $grand_total
        //     ]
        // ]);

        $summary = (object)[
            "sum_terpal" => $sum_terpal,
            "sum_slipsheet" => $sum_slipsheet,
            "sum_pallet" => $sum_pallet,
            "sum_total_qty" => $sum_total_qty,
            "sum_total_fee" => $sum_total_fee,
            "ppn" => $ppn,
            "pph" => $pph,
            "grand_total" => $grand_total,
            "no_dok" => $noDok
        ];

        $pdf = Pdf::loadView('pdf.tkbm_report', [
            "data" => $data,
            "summary" => $summary,
            "latestFee" => $latestFee
        ])->setPaper('a4', 'portrait');

        // // kalau mau langsung preview di browser:
        $filename = 'report-tkbm-' . time() . '.pdf';
        return $pdf->stream($filename);
    }

    private function processSheet($sheet, $dataChunk, $noDok, $periodeText, $sheetNumber)
    {
        // Log::info('Processing sheet', ['sheet_number' => $sheetNumber, 'data_count' => count($dataChunk)]);

        // Isi header
        $this->fillSheetHeader($sheet, $noDok, $periodeText, $dataChunk);

        // Isi data
        $startRow = 9;
        $currentRow = $startRow;

        foreach ($dataChunk as $item) {
            $sheet->setCellValue('A' . $currentRow, $item->date ? Carbon::parse($item->date)->format('d/m/Y') : '');
            $sheet->setCellValue('F' . $currentRow, $item->qty_terpal ?? 0);
            $sheet->setCellValue('J' . $currentRow, $item->qty_slipsheet ?? 0);
            $sheet->setCellValue('N' . $currentRow, $item->qty_pallet ?? 0);
            $sheet->setCellValue('R' . $currentRow, $item->total_qty ?? 0);
            // $sheet->setCellValue('W' . $currentRow, $item->total_fee ?? 0);
            $fee = $item->fee;        // relasi dari Tkbm -> FeeModel
            $harga = $item->harga;    // relasi dari Tkbm -> HargaModel

            $sheet->setCellValue('W' . $currentRow, $item->total_fee ?? 0);
            $currentRow++;
        }

        // Isi total
        $endRow = $currentRow - 1;
        $this->fillSheetTotals($sheet, $startRow, $endRow, $dataChunk);

        // Log::info('Sheet processing completed', ['sheet_number' => $sheetNumber, 'end_row' => $endRow]);
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
        return sprintf("%s/WCP/%s/%s", $nomor, $romawi[$month], $year);
    }

    private function fillSheetHeader($sheet, $noDok, $periodeText, $dataChunk)
    {
        $sheet->setCellValue('U1', $noDok);
        $sheet->setCellValue('U2', 0);
        $sheet->setCellValue('U4', '1 of 1');
        $sheet->setCellValue('U3', $periodeText);

        // Isi keterangan fee
        $firstItem = $dataChunk->first();
        $feeRate = $firstItem && $firstItem->fee ? $firstItem->fee->fee : 0;
        $ppnRate = $firstItem && $firstItem->fee ? $firstItem->fee->ppn : 0;
        $pphRate = $firstItem && $firstItem->fee ? $firstItem->fee->pph : 0;

        $sheet->setCellValue(
            'W7',
            "Keterangan\n(Fee " . $feeRate . "%)"
        );
        // $sheet->setCellValue(
        //     'W7',
        //     "Keterangan\n(Fee " . ($lastFeeData->fee ?? 0) . "%)"
        // );

        // Isi label PPn dan PPh
        $sheet->setCellValue('A30', "PPn " . ($ppnRate ?? 0) . "%");
        $sheet->setCellValue('A32', "PPh " . ($pphRate ?? 0) . "%");
    }

    private function fillSheetTotals($sheet, $startRow, $endRow, $dataChunk)
    {
        $startRowTotal = 28;

        // Isi formula total hanya jika ada data
        if ($endRow >= $startRow) {
            $sheet->setCellValue('F' . $startRowTotal, '=SUM(F' . $startRow . ':F' . $endRow . ')');
            $sheet->setCellValue('J' . $startRowTotal, '=SUM(J' . $startRow . ':J' . $endRow . ')');
            $sheet->setCellValue('N' . $startRowTotal, '=SUM(N' . $startRow . ':N' . $endRow . ')');
            $sheet->setCellValue('R' . $startRowTotal, '=SUM(R' . $startRow . ':R' . $endRow . ')');
            $sheet->setCellValue('W' . $startRowTotal, '=SUM(W' . $startRow . ':W' . $endRow . ')');
        } else {
            // Jika tidak ada data, isi dengan 0
            $sheet->setCellValue('F' . $startRowTotal, 0);
            $sheet->setCellValue('J' . $startRowTotal, 0);
            $sheet->setCellValue('N' . $startRowTotal, 0);
            $sheet->setCellValue('R' . $startRowTotal, 0);
            $sheet->setCellValue('W' . $startRowTotal, 0);
        }

        // Format qty columns
        $qtyFormat = '_-* #,##0_-;-* #,##0_-;_-* "-"??_-;_-@_-';
        $sheet->getStyle('F' . $startRow . ':F' . $startRowTotal)->getNumberFormat()->setFormatCode($qtyFormat);
        $sheet->getStyle('J' . $startRow . ':J' . $startRowTotal)->getNumberFormat()->setFormatCode($qtyFormat);
        $sheet->getStyle('N' . $startRow . ':N' . $startRowTotal)->getNumberFormat()->setFormatCode($qtyFormat);

        // Format Rupiah
        $rupiahFormat = '_-"Rp"* #,##0_-;-"Rp"* #,##0_-;_-"Rp"* "-"_-;_-@_-';
        $sheet->getStyle('W' . $startRow . ':W' . $startRowTotal)->getNumberFormat()->setFormatCode($rupiahFormat);
        $sheet->getStyle('R' . $startRow . ':R' . $startRowTotal)->getNumberFormat()->setFormatCode($rupiahFormat);

        // Perhitungan PPn, PPh, dan Grand Total
        $startRowPpn = 30;
        $startRowPph = 32;
        $startRowGrandTotal = 34;

        $ppnTotal = 0;
        $pphTotal = 0;

        foreach ($dataChunk as $item) {
            if ($item->fee) {
                $ppnTotal += ($item->total_fee ?? 0) * ($item->fee->ppn / 100);
                $pphTotal += ($item->total_fee ?? 0) * ($item->fee->pph / 100);
            }
        }

        $sheet->setCellValue('R' . $startRowPpn, $ppnTotal);
        $sheet->setCellValue('R' . $startRowPph, $pphTotal);
        $sheet->setCellValue('R' . $startRowGrandTotal, '=R' . $startRowTotal . '+W' . $startRowTotal . '+R' . $startRowPpn . '-R' . $startRowPph);

        // Format currency untuk perhitungan
        $currencyFormat = '_("Rp"* #,##0_);_("Rp"* (#,##0);_("Rp"* "-"_);_(@_)';
        $sheet->getStyle('R' . $startRowPpn)->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle('R' . $startRowPph)->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle('R' . $startRowGrandTotal)->getNumberFormat()->setFormatCode($currencyFormat);
    }


    /**
     * Handle Master Fees & Harga TKBM store
     */
    public function simpanFeeTkbm(Request $request)
    {
        $request->validate([
            'fee' => 'numeric',
            'ppn' => 'numeric',
            'pph' => 'numeric',
        ]);

        // Ambil data terakhir untuk fallback
        $lastData = TkbmFeeModel::orderBy('created_at', 'desc')->first();

        // Tentukan nilai yang akan disimpan, menggunakan nilai terakhir sebagai fallback jika input 0/null
        $newFee = ($request->fee !== null && $request->fee != 0) ? $request->fee : ($lastData->fee ?? 0);
        $newPpn = ($request->ppn !== null && $request->ppn != 0) ? $request->ppn : ($lastData->ppn ?? 0);
        $newPph = ($request->pph !== null && $request->pph != 0) ? $request->pph : ($lastData->pph ?? 0);

        try {
            DB::beginTransaction();

            $save = TkbmFeeModel::create([
                'fee' => $newFee,
                'ppn' => $newPpn,
                'pph' => $newPph,
            ]);

            // Cek apakah PPN atau PPH benar-benar berubah dari nilai sebelumnya
            $isPpnChanged = ($lastData ? $lastData->ppn : 0) != $newPpn;
            $isPphChanged = ($lastData ? $lastData->pph : 0) != $newPph;

            if ($isPpnChanged || $isPphChanged) {

                // Ambil semua record totals_tkbm yang ada
                $allTotals = TotalsTkbmModel::all();

                foreach ($allTotals as $totals) {
                    // Total Fee bulanan tidak berubah
                    $totalFeeBulanan = $totals->total_fee;

                    // Hitung ulang PPN & PPH bulanan menggunakan rate BARU
                    $newTotalPpn = ($newPpn / 100) * $totalFeeBulanan;
                    $newTotalPph = ($newPph / 100) * $totalFeeBulanan;

                    $newGrandTotal = $totals->total_produk + $totals->total_fee + $newTotalPpn - $newTotalPph;

                    // Update totals_tkbm
                    $totals->update([
                        'total_ppn' => $newTotalPpn,
                        'total_pph' => $newTotalPph,
                        'grand_total' => $newGrandTotal,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Data Fee TKBM berhasil disimpan dan Total PPN/PPH bulanan telah diperbarui!',
                'data' => $save,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menyimpan data Master Fee dan memperbarui total: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function simpanHargaProduk(Request $request)
    {
        $request->validate([
            'terpal' => 'numeric',
            'slipsheet' => 'numeric',
            'pallet' => 'numeric',
        ]);

        // Ambil data terakhir dari database
        $lastData = TkbmHargaProdukModel::orderBy('created_at', 'desc')->first();

        // Jika harga produk bernilai 0 atau null, gunakan nilai dari data terakhir (jika ada)
        $terpal = ($request->terpal !== null && $request->terpal != 0) ? $request->terpal : ($lastData->terpal ?? 0);
        $slipsheet = ($request->slipsheet !== null && $request->slipsheet != 0) ? $request->slipsheet : ($lastData->slipsheet ?? 0);
        $pallet = ($request->pallet !== null && $request->pallet != 0) ? $request->pallet : ($lastData->pallet ?? 0);

        // Simpan data ke database
        $save = TkbmHargaProdukModel::create([
            'harga_terpal' => $terpal,
            'harga_slipsheet' => $slipsheet,
            'harga_pallet' => $pallet,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Data Fee TKBM berhasil disimpan!',
            'data' => $save,
        ], 200);
    }

    /**
     * Handle history fee TKBM
     */
    public function historyFeeTkbm()
    {
        $data = TkbmFeeModel::orderBy('created_at', 'desc')->get();

        return response()->json([
            'ok' => true,
            'data' => $data,
        ], 200);
    }

    public function historyProductPrice()
    {
        $data = TkbmHargaProdukModel::orderBy('created_at', 'desc')->get();

        return response()->json([
            'ok' => true,
            'data' => $data,
        ], 200);
    }
}
