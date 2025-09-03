<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Tkbm\TkbmModel;
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
}
