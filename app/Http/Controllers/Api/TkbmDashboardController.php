<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Tkbm\TkbmModel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tkbm\TkbmFeeModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TkbmDashboardController extends Controller
{
    protected $allMonths = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    ];

    public function userDashboard()
    {
        $userCount = User::count();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditemukan',
            'data' => $userCount
        ]);
    }

    public function tkbmDashboard()
    {
        $dataDb = DB::table('tkbm')
            ->selectRaw('MONTHNAME(date) as bulan, COUNT(*) as banyak_data')
            ->groupBy('bulan')
            ->orderByRaw('MIN(date)')
            ->pluck('banyak_data', 'bulan')
            ->toArray();

        $result = [];
        foreach ($this->allMonths as $bulan) {
            $result[] = [
                'bulan' => $bulan,
                'banyak_data' => $dataDb[$bulan] ?? 0
            ];
        }

        return response()->json($result);
    }

    public function tkbmDashboardProduk()
    {
        $bulanInput = request()->query('bulan');

        if ($bulanInput) {
            // jika format "YYYY-MM", parse dengan Carbon
            $date = Carbon::createFromFormat('Y-m', $bulanInput);
            $bulan = $date->month; // angka 1-12
            $tahun = $date->year;
        } else {
            $date = Carbon::now();
            $bulan = $date->month;
            $tahun = $date->year;
        }

        // ambil data sesuai bulan
        $data = TkbmModel::selectRaw('
            MONTH(date) as bulan,
            SUM(qty_terpal) as total_terpal,
            SUM(qty_slipsheet) as total_slipsheet,
            SUM(qty_pallet) as total_pallet
        ')
            ->whereMonth('date', $bulan)
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get();

        return response()->json($data);
    }

    public function qtyTerpalDay(Request $request)
    {
        $bulanInput = $request->input('bulan', now()->format('Y-m'));
        [$tahun, $bulan] = explode('-', $bulanInput);

        $data = TkbmModel::selectRaw('DATE(date) as tanggal, SUM(qty_terpal) as total_terpal')
            ->whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        return response()->json($data);
    }

    public function qtySlipsheetDay(Request $request)
    {
        $bulanInput = $request->input('bulan', now()->format('Y-m'));
        [$tahun, $bulan] = explode('-', $bulanInput);

        $data = TkbmModel::selectRaw('DATE(date) as tanggal, SUM(qty_slipsheet) as total_slipsheet')
            ->whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        return response()->json($data);
    }

    public function qtyPalletDay(Request $request)
    {
        $bulanInput = $request->input('bulan', now()->format('Y-m'));
        [$tahun, $bulan] = explode('-', $bulanInput);

        $data = TkbmModel::selectRaw('DATE(date) as tanggal, SUM(qty_pallet) as total_pallet')
            ->whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        return response()->json($data);
    }

    public function tkbmDashboardGrandTotal()
    {
        // Ambil persentase PPN & PPh dari tabel fee
        $feeConfig = TkbmFeeModel::latest()->first();
        $ppnPersen = $feeConfig->ppn ?? 0;
        $pphPersen = $feeConfig->pph ?? 0;

        $data = TkbmModel::selectRaw('
            MONTH(date) as bulan,
            SUM(total_fee) as total_fee,
            SUM(total_qty) as total_qty
        ')
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get()
            ->map(function ($item) use ($ppnPersen, $pphPersen) {
                $totalFee = (float) $item->total_fee;
                $totalQty = (int) $item->total_qty;

                $totalPpn = $totalFee * ($ppnPersen / 100);
                $totalPph = $totalFee * ($pphPersen / 100);
                $grandTotal = $totalFee + $totalPpn + $totalPph + $totalQty;

                return [
                    'bulan'        => $item->bulan,
                    'bulan_nama'   => $this->allMonths[$item->bulan - 1] ?? '',
                    'total_fee'    => $totalFee,
                    'total_qty'    => $totalQty,
                    'total_ppn'    => $totalPpn,
                    'total_pph'    => $totalPph,
                    'grand_total'  => $grandTotal
                ];
            })
            ->keyBy('bulan_nama');

        // Susun data sesuai urutan $allMonths
        $data = collect($this->allMonths)->map(function ($bulan) use ($data) {
            return $data[$bulan] ?? [
                'bulan'        => array_search($bulan, $this->allMonths) + 1,
                'bulan_nama'   => $bulan,
                'total_fee'    => 0,
                'total_qty'    => 0,
                'total_ppn'    => 0,
                'total_pph'    => 0,
                'grand_total'  => 0
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditemukan',
            'data' => $data
        ]);
    }




    // Export Pdf
    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $noDok = $request->query('no_dok');

        // if (!$startDate) {
        //     $startDate = now()->startOfMonth()->format('Y-m-d'); // tanggal 1 bulan ini
        // }
        // if (!$endDate) {
        //     $endDate = now()->startOfMonth()->addDays(14)->format('Y-m-d'); // tanggal 15 bulan ini
        // }

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
        $grand_total = $sum_total_qty + $sum_total_fee + $ppn + $pph;

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
}
