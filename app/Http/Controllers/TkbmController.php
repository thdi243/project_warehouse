<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tkbm\TkbmModel;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tkbm\TkbmFeeModel;
use App\Models\Tkbm\TkbmHargaProdukModel;

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
            'jml_tkbm' => 'integer|min:0',
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
        $hargaTerbaru = TkbmHargaProdukModel::orderBy('created_at', 'desc')->first();

        if (!$hargaTerbaru) {
            return response()->json([
                'ok' => false,
                'message' => 'Data harga belum tersedia.'
            ]);
        }

        // hitung total qty berdasarkan harga terbaru
        $totalQty = (($request->qtyTerpal ?? 0) * $hargaTerbaru['harga_terpal']) +
            (($request->qtySlipsheet ?? 0) * $hargaTerbaru['harga_slipsheet']) +
            (($request->qtyPallet ?? 0) * $hargaTerbaru['harga_pallet']);

        // ambil data fee terakhir
        $lastFeeData = TkbmFeeModel::orderBy('created_at', 'desc')->first();

        if (!$lastFeeData) {
            return response()->json([
                'ok' => false,
                'message' => 'Data Fees & Taxes belum tersedia.'
            ]);
        }

        $fee = $lastFeeData ? $lastFeeData->fee : 0;
        // $ppn = $lastFeeData ? $lastFeeData->ppn : 0;
        // $pph = $lastFeeData ? $lastFeeData->pph : 0;
        $feeAct = ($fee / 100) * $totalQty;

        // Simpan data ke database (fee simpan nilai fee, bukan id)
        $save = TkbmModel::create([
            'date' => $request->date,
            'petugas' => $request->petugas,
            'shift' => $request->shift,
            'qty_terpal' => $request->qtyTerpal ?? 0,
            'qty_slipsheet' => $request->qtySlipsheet ?? 0,
            'qty_pallet' => $request->qtyPallet ?? 0,
            'jml_tkbm' => $request->jml_tkbm ?? 0,
            'keterangan' => $request->keterangan ?? null,
            'total_qty' => $totalQty,
            'total_fee' => $feeAct,
            'fee_id' => $fee,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Data TKBM berhasil disimpan!',
            'data' => $save,
        ], 200);
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

        $data = $query->orderBy('date', 'asc')->get();

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

        $data = TkbmModel::find($id);

        if (!$data) {
            return response()->json([
                'ok' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        // cek data duplikat berdasarkan tanggal dan shift, kecuali data yang sedang diupdate
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

        // hitung total qty
        $totalQty = (($request->qty_terpal ?? 0) * 770) +
            (($request->qty_slipsheet ?? 0) * 440) +
            (($request->qty_pallet ?? 0) * 1100);

        // ambil data fee terakhir
        $lastFeeData = TkbmFeeModel::orderBy('created_at', 'desc')->first();
        $fee = $lastFeeData ? $lastFeeData->fee : 0;
        $feeAct = ($fee / 100) * $totalQty;

        try {
            // Update data di database (fee simpan nilai fee, bukan id)
            $data->update([
                'date' => $request->date,
                'petugas' => $request->petugas,
                'shift' => $request->shift,
                'qty_terpal' => $request->qty_terpal ?? 0,
                'qty_slipsheet' => $request->qty_slipsheet ?? 0,
                'qty_pallet' => $request->qty_pallet ?? 0,
                'jml_tkbm' => $request->jml_tkbm ?? 0,
                'keterangan' => $request->keterangan ?? null,
                'total_qty' => $totalQty,
                'total_fee' => $feeAct,
                'fee_id' => $fee,
            ]);
            return response()->json([
                'ok' => true,
                'message' => 'Data berhasil diupdate',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
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
        $data = TkbmModel::find($id);

        if (!$data) {
            return response()->json([
                'ok' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        try {
            $data->delete();

            return response()->json([
                'ok' => true,
                'message' => 'Data berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
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
        $data = TkbmModel::whereBetween('date', [$start, $end])
            ->orderBy('date', 'asc')
            ->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data pada rentang tanggal tersebut.');
        }

        // Load template Excel
        $templatePath = public_path('assets/template/excel/template_excel_tkbm.xlsx');
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Template Excel tidak ditemukan di: ' . $templatePath);
        }

        try {
            // Baca file template
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $templateSpreadsheet = $reader->load($templatePath);

            // Clone spreadsheet agar template asli tidak berubah
            $spreadsheet = clone $templateSpreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            // Isi Rev
            $sheet->setCellValue('V2', 0);
            $sheet->setCellValue('V4', '1 of 1');

            // Atur judul periode di sheet
            $periodeText = Carbon::now()->format('j F Y');
            $sheet->setCellValue('V3', $periodeText);

            // Mulai menulis data dari baris 9
            $startRow = 9;
            $currentRow = $startRow;

            // Salin style dari template row untuk consistency
            $templateRowRange = 'A' . $startRow . ':AC' . $startRow;

            foreach ($data as $index => $item) {
                // Mapping data ke kolom Excel
                $sheet->setCellValue('A' . $currentRow, $item->date ? Carbon::parse($item->date)->format('d/m/Y') : '');
                $sheet->setCellValue('G' . $currentRow, $item->qty_terpal ?? 0);
                $sheet->setCellValue('K' . $currentRow, $item->qty_slipsheet ?? 0);
                $sheet->setCellValue('O' . $currentRow, $item->qty_pallet ?? 0);
                $sheet->setCellValue('S' . $currentRow, $item->total_qty ?? 0);
                $sheet->setCellValue('X' . $currentRow, $item->total_fee ?? 0); // Perbaikan: kolom F bukan E lagi

                // Salin style dari template row jika ada
                try {
                    $sheet->duplicateStyle(
                        $sheet->getStyle($templateRowRange),
                        'A' . $currentRow . ':AC' . $currentRow
                    );
                } catch (\Exception $e) {
                    // Jika gagal copy style, lanjutkan tanpa style
                }

                // Jadikan teks di baris ini bold
                $sheet->getStyle('S' . $currentRow . ':AC' . $currentRow)
                    ->getFont()
                    ->setBold(true);

                $currentRow++;
            }

            // Isi total di baris terakhir
            $startRowTotal = 28;
            // $sheet->setCellValue('A' . $startRowTotal, 'TOTAL');
            $sheet->setCellValue('G' . $startRowTotal, '=SUM(G' . $startRow . ':G' . ($startRowTotal - 1) . ')');
            $sheet->setCellValue('K' . $startRowTotal, '=SUM(K' . $startRow . ':K' . ($startRowTotal - 1) . ')');
            $sheet->setCellValue('O' . $startRowTotal, '=SUM(O' . $startRow . ':O' . ($startRowTotal - 1) . ')');
            $sheet->setCellValue('S' . $startRowTotal, '=SUM(S' . $startRow . ':S' . ($startRowTotal - 1) . ')');
            $sheet->setCellValue('X' . $startRowTotal, '=SUM(X' . $startRow . ':X' . ($startRowTotal - 1) . ')');

            // style qty
            $sheet->getStyle('G' . $startRow . ':G' . $startRowTotal)
                ->getNumberFormat()
                ->setFormatCode('_-* #,##0_-;-* #,##0_-;_-* "-"??_-;_-@_-');
            $sheet->getStyle('K' . $startRow . ':K' . $startRowTotal)
                ->getNumberFormat()
                ->setFormatCode('_-* #,##0_-;-* #,##0_-;_-* "-"??_-;_-@_-');
            $sheet->getStyle('O' . $startRow . ':O' . $startRowTotal)
                ->getNumberFormat()
                ->setFormatCode('_-* #,##0_-;-* #,##0_-;_-* "-"??_-;_-@_-');

            // Format kolom fee (X) dan total fee (X) sebagai Rupiah sesuai format custom
            $rupiahFormat = '_-"Rp"* #,##0_-;-"Rp"* #,##0_-;_-"Rp"* "-"_-;_-@_-';
            $sheet->getStyle('X' . $startRow . ':X' . $startRowTotal)
                ->getNumberFormat()
                ->setFormatCode($rupiahFormat);
            $sheet->getStyle('X' . $startRowTotal)
                ->getNumberFormat()
                ->setFormatCode($rupiahFormat);

            // Format kolom total_qty (S) dan total total_qty (S) sebagai Rupiah sesuai format custom
            $sheet->getStyle('S' . $startRow . ':S' . $startRowTotal)
                ->getNumberFormat()
                ->setFormatCode($rupiahFormat);
            $sheet->getStyle('S' . $startRowTotal)
                ->getNumberFormat()
                ->setFormatCode($rupiahFormat);

            // Ambil data fee, ppn, pph terakhir untuk perhitungan di bawah
            $lastFeeData = TkbmFeeModel::orderBy('created_at', 'desc')->first();

            $sheet->setCellValue(
                'X7',
                "Keterangan\n(Fee " . ($lastFeeData->fee ?? 0) . "%)"
            );

            $sheet->setCellValue(
                'A30',
                "PPn " . ($lastFeeData->ppn ?? 0) . "%"
            );

            $sheet->setCellValue(
                'A32',
                "PPh " . ($lastFeeData->pph ?? 0) . "%"
            );


            $startRowPpn = 30;
            $sheet->setCellValue('S' . $startRowPpn, '=X' . $startRowTotal . '*(' . ($lastFeeData ? $lastFeeData->ppn : 0) . '/100)');
            $sheet->getStyle('X' . $startRowPpn)
                ->getNumberFormat()
                ->setFormatCode('_("Rp"* #,##0_);_("Rp"* (#,##0);_("Rp"* "-"_);_(@_)');

            $startRowPph = 32;
            $sheet->setCellValue('S' . $startRowPph, '=-X' . $startRowTotal . '*(' . ($lastFeeData ? $lastFeeData->pph : 0) . '/100)');
            $sheet->getStyle('X' . $startRowPph)
                ->getNumberFormat()
                ->setFormatCode('_("Rp"* #,##0_);_("Rp"* (#,##0);_("Rp"* "-"_);_(@_)');

            $startRowGrandTotal = 34;
            // Grand total = total_qty (S) + total fee (X) + ppn (S42) + pph (S44)
            $sheet->setCellValue('S' . $startRowGrandTotal, '=S' . $startRowTotal . '+X' . $startRowTotal . '+S' . $startRowPpn . '+S' . $startRowPph);
            $sheet->getStyle('S' . $startRowGrandTotal)
                ->getNumberFormat()
                ->setFormatCode('_("Rp"* #,##0_);_("Rp"* (#,##0);_("Rp"* "-"_);_(@_)');

            // Generate filename
            $fileName = 'Data_TKBM_' . $startDate . '-' . str_pad($endDate, 2, '0', STR_PAD_LEFT) . '.xlsx';

            // Save ke temporary file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $tempPath = tempnam(sys_get_temp_dir(), 'tkbm_export_');
            $writer->save($tempPath);

            // Cleanup memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return response()->download($tempPath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Handle Master Fees & Harga TKBM store
     */
    public function simpanFeeTkbm(Request $request)
    {
        $validated = $request->validate([
            'fee' => 'numeric',
            'ppn' => 'numeric',
            'pph' => 'numeric',
        ]);

        // Ambil data terakhir dari database
        $lastData = TkbmFeeModel::orderBy('created_at', 'desc')->first();

        // Jika fee, ppn, atau pph bernilai 0 atau null, gunakan nilai dari data terakhir (jika ada)
        $fee = ($request->fee !== null && $request->fee != 0) ? $request->fee : ($lastData->fee ?? 0);
        $ppn = ($request->ppn !== null && $request->ppn != 0) ? $request->ppn : ($lastData->ppn ?? 0);
        $pph = ($request->pph !== null && $request->pph != 0) ? $request->pph : ($lastData->pph ?? 0);

        // Simpan data ke database
        $save = TkbmFeeModel::create([
            'fee' => $fee,
            'ppn' => $ppn,
            'pph' => $pph,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Data Fee TKBM berhasil disimpan!',
            'data' => $save,
        ], 200);
    }

    public function simpanHargaProduk(Request $request)
    {
        $validated = $request->validate([
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
