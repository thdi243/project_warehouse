<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tkbm\bps\TkbmModel;
use App\Models\Tkbm\bps\TkbmFeeModel;

class BpsDashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.bps_tkbm');
    }

    public function getStats(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Monthly Stats
        $monthlyData = TkbmModel::whereBetween('date', [$startOfMonth, $endOfMonth])->get();

        $totalTerpal = $monthlyData->sum('qty_terpal');
        $totalSlipsheet = $monthlyData->sum('qty_slipsheet');
        $totalPallet = $monthlyData->sum('qty_pallet');
        $totalProduk = $totalTerpal + $totalPallet + $totalSlipsheet;
        $totalQty = $monthlyData->sum('total_qty');
        $totalFee = $monthlyData->sum('total_fee');

        // Latest fee to calculate PPN/PPH
        $latestFee = TkbmFeeModel::latest()->first();
        $ppnRate = $latestFee ? $latestFee->ppn : 0;
        $pphRate = $latestFee ? $latestFee->pph : 0;

        $totalPpn = ($ppnRate / 100) * $totalFee;
        $totalPph = ($pphRate / 100) * $totalFee;
        $grandTotal = ($totalQty + $totalFee + $totalPpn) - $totalPph;

        // Last Month Comparison
        $startOfLastMonth = (clone $startOfMonth)->subMonth();
        $endOfLastMonth = (clone $startOfMonth)->subMonth()->endOfMonth();

        $lastMonthData = TkbmModel::whereBetween('date', [$startOfLastMonth, $endOfLastMonth])->get();
        $lastMonthQty = $lastMonthData->sum('total_qty');

        $diffQty = $totalQty - $lastMonthQty;
        $percChange = $lastMonthQty > 0 ? ($diffQty / $lastMonthQty) * 100 : 0;

        // Daily Chart Data
        $dailyTrendRaw = TkbmModel::selectRaw('DATE(date) as tanggal, SUM(qty_terpal) as total_terpal, SUM(qty_slipsheet) as total_slipsheet, SUM(qty_pallet) as total_pallet, SUM(jml_tkbm) as total_tkbm')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get()
            ->keyBy('tanggal');

        $dailyTrend = [];
        $currentDate = clone $startOfMonth;
        while ($currentDate <= $endOfMonth) {
            $dateString = $currentDate->toDateString();
            $data = $dailyTrendRaw->get($dateString);

            $dailyTrend[] = [
                'date' => $dateString,
                'total_terpal' => $data ? (float)$data->total_terpal : 0,
                'total_slipsheet' => $data ? (float)$data->total_slipsheet : 0,
                'total_pallet' => $data ? (float)$data->total_pallet : 0,
                'total_tkbm' => $data ? (float)$data->total_tkbm : 0,
            ];
            $currentDate->addDay();
        }

        // Latest fee to calculate PPN/PPH for trends
        $feeConfig = TkbmFeeModel::latest()->first();
        $ppnRate = $feeConfig ? $feeConfig->ppn : 0;
        $pphRate = $feeConfig ? $feeConfig->pph : 0;

        $monthlyTrend = TkbmModel::selectRaw('YEAR(date) as year, MONTH(date) as month, SUM(total_qty) as total_qty, SUM(total_fee) as total_fee')
            ->where('date', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) use ($ppnRate, $pphRate) {
                $item->month_name = Carbon::createFromDate($item->year, $item->month, 1)->format('M Y');
                $total_ppn = ($ppnRate / 100) * $item->total_fee;
                $total_pph = ($pphRate / 100) * $item->total_fee;
                $item->grand_total = ($item->total_qty + $item->total_fee + $total_ppn) - $total_pph;
                return $item;
            });

        return response()->json([
            'status' => 'success',
            'period' => $startOfMonth->translatedFormat('F Y'),
            'stats' => [
                'total_qty' => [
                    'value' => number_format($totalQty, 0, ',', '.'),
                    'diff' => number_format($diffQty, 0, ',', '.'),
                    'percent' => round($percChange, 1),
                    'trend' => $percChange >= 0 ? 'up' : 'down'
                ],
                'total_produk' => number_format($totalProduk, 0, ',', '.'),
                'total_terpal' => number_format($totalTerpal, 0, ',', '.'),
                'total_slipsheet' => number_format($totalSlipsheet, 0, ',', '.'),
                'total_pallet' => number_format($totalPallet, 0, ',', '.'),
                'total_fee' => number_format($totalFee, 0, ',', '.'),
                'ppn' => number_format($totalPpn, 0, ',', '.'),
                'pph' => number_format($totalPph, 0, ',', '.'),
                'grand_total' => number_format($grandTotal, 0, ',', '.'),
                'avg_daily' => number_format($totalQty / $startOfMonth->daysInMonth, 1, ',', '.'),
            ],
            'charts' => [
                'daily' => $dailyTrend,
                'monthly' => $monthlyTrend,
            ],
            'recent_entries' => TkbmModel::with('fee')->orderBy('date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $ppnRate = $item->fee ? $item->fee->ppn : 0;
                    $pphRate = $item->fee ? $item->fee->pph : 0;
                    $total_ppn = ($ppnRate / 100) * $item->total_fee;
                    $total_pph = ($pphRate / 100) * $item->total_fee;
                    $item->calculated_grand_total = ($item->total_qty + $item->total_fee + $total_ppn) - $total_pph;
                    return $item;
                })
        ]);
    }
}
