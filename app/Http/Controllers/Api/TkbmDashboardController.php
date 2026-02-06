<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Tkbm\bps\TkbmModel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tkbm\bps\TkbmFeeModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tkbm\bps\TotalsTkbmModel;

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

    public function tkbmAllProduk(Request $request)
    {
        // Ambil tahun dari request, default: tahun sekarang
        $year = $request->get('year', Carbon::now()->year);

        // Query data hanya untuk tahun yang dipilih
        $produk = TkbmModel::selectRaw('
            YEAR(date) as year,
            MONTH(date) as month,
            SUM(qty_terpal) as total_terpal,
            SUM(qty_slipsheet) as total_slipsheet,
            SUM(qty_pallet) as total_pallet
        ')
            ->whereYear('date', $year)
            ->groupBy('year', 'month')
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy('month'); // key per bulan biar gampang lookup

        $result = [];

        // Loop fix 12 bulan
        for ($m = 1; $m <= 12; $m++) {
            $data = $produk->get($m);

            $result[] = [
                'bulan'            => Carbon::createFromDate($year, $m, 1)->format('F Y'),
                'year'             => $year,
                'total_terpal'     => (int) ($data->total_terpal ?? 0),
                'total_slipsheet'  => (int) ($data->total_slipsheet ?? 0),
                'total_pallet'     => (int) ($data->total_pallet ?? 0),
            ];
        }

        return response()->json([
            'year' => $year,
            'data' => $result
        ]);
    }

    public function qtyTerpalDay(Request $request)
    {
        $bulanInput = $request->input('bulan', now()->format('Y-m'));
        [$tahun, $bulan] = explode('-', $bulanInput);

        $data = TkbmModel::selectRaw('
                DATE(date) as tanggal, 
                SUM(qty_terpal) as total_terpal,
                SUM(jml_tkbm) as total_tkbm,
                MAX(fee_id) as fee_id,
                MAX(harga_id) as harga_id
            ')
            ->with([
                'fee:id,fee',
                'harga:id,harga_terpal'
            ])
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

        $data = TkbmModel::selectRaw('
                DATE(date) as tanggal, 
                SUM(qty_slipsheet) as total_slipsheet,
                SUM(jml_tkbm) as total_tkbm,
                MAX(fee_id) as fee_id,
                MAX(harga_id) as harga_id
            ')
            ->with([
                'fee:id,fee',
                'harga:id,harga_slipsheet'
            ])
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

        $data = TkbmModel::selectRaw('
                DATE(date) as tanggal, 
                SUM(qty_pallet) as total_pallet,
                SUM(jml_tkbm) as total_tkbm,
                MAX(fee_id) as fee_id,
                MAX(harga_id) as harga_id
            ')
            ->with([
                'fee:id,fee',
                'harga:id,harga_pallet'
            ])
            ->whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        return response()->json($data);
    }

    public function tkbmDashboardGrandTotal(Request $request)
    {
        // Tahun dari request, default: tahun sekarang
        $year = $request->get('year', Carbon::now()->year);

        // Ambil data hanya untuk tahun tersebut
        $grandTotals = TotalsTkbmModel::select(
            'month',
            'total_produk',
            'total_fee',
            'total_ppn',
            'total_pph',
            'grand_total'
        )
            ->where('year', $year)
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy('month');

        $result = [];

        // Loop fix 12 bulan
        for ($m = 1; $m <= 12; $m++) {
            $data = $grandTotals->get($m);

            $result[] = [
                'bulan'        => Carbon::createFromDate($year, $m, 1)->format('F Y'),
                'year'         => $year,
                'total_produk' => (float) ($data->total_produk ?? 0),
                'total_fee'    => (float) ($data->total_fee ?? 0),
                'total_ppn'    => (float) ($data->total_ppn ?? 0),
                'total_pph'    => (float) ($data->total_pph ?? 0),
                'grand_total'  => (float) ($data->grand_total ?? 0),
            ];
        }

        return response()->json([
            'year' => $year,
            'data' => $result
        ]);
    }

    public function dataWidget()
    {
        $date = now(); // otomatis pakai timezone Laravel

        // Ambil data bulan ini
        $data = TkbmModel::whereMonth('date', $date->month)
            ->whereYear('date', $date->year)
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'Tidak ada data bulan ini.',
                'terpal' => 0,
                'slipsheet' => 0,
                'pallet' => 0,
                'grand_total' => 0,
            ]);
        }

        $sum_terpal = 0;
        $sum_slipsheet = 0;
        $sum_pallet = 0;
        $sum_total_qty = 0;
        $sum_total_fee = 0;

        foreach ($data as $d) {
            $sum_terpal     += $d->qty_terpal;
            $sum_slipsheet  += $d->qty_slipsheet;
            $sum_pallet     += $d->qty_pallet;
            $sum_total_qty  += $d->total_qty;
            $sum_total_fee  += $d->total_fee;
        }

        // Ambil fee terbaru
        $latestFee = TkbmFeeModel::latest()->first();

        $ppn = $latestFee ? ($latestFee->ppn / 100) * $sum_total_fee : 0;
        $pph = $latestFee ? ($latestFee->pph / 100) * $sum_total_fee : 0;

        // Hitung Grand Total
        $grand_total = $sum_total_qty + $sum_total_fee + $ppn - $pph;

        return response()->json([
            'status'     => true,
            'month'      => $date->month,
            'year'       => $date->year,
            'terpal'     => $sum_terpal,
            'slipsheet'  => $sum_slipsheet,
            'pallet'     => $sum_pallet,
            'grand_total'      => $grand_total,
        ]);
    }
}
