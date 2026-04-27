<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tkbm\ikat_terpal\IkatTerpal;
use App\Models\Tkbm\ikat_terpal\FeeIkatTerpal;

class IkatTerpalDashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.ikat-terpal');
    }

    public function getStats(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Monthly Stats
        $monthlyData = IkatTerpal::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->get();

        $totalPallets = $monthlyData->sum('qty_pallet');
        $totalBuruh = $monthlyData->sum('jml_buruh');
        $totalSubtotal = $monthlyData->sum('subtotal_barang');
        $totalFee = $monthlyData->sum('total_fee');

        // Latest fee to calculate PPN/PPH
        $latestFee = FeeIkatTerpal::where('aktif', true)->first();
        $ppnRate = $latestFee ? $latestFee->ppn : 0;
        $pphRate = $latestFee ? $latestFee->pph : 0;

        $totalPpn = ($ppnRate / 100) * $totalFee;
        $totalPph = ($pphRate / 100) * $totalFee;
        $grandTotal = ($totalSubtotal + $totalFee + $totalPpn) - $totalPph;

        // Last Month Comparison
        $startOfLastMonth = (clone $startOfMonth)->subMonth();
        $endOfLastMonth = (clone $startOfMonth)->subMonth()->endOfMonth();

        $lastMonthData = IkatTerpal::whereBetween('tanggal', [$startOfLastMonth, $endOfLastMonth])->get();
        $lastMonthPallets = $lastMonthData->sum('qty_pallet');

        $diffPallets = $totalPallets - $lastMonthPallets;
        $percChange = $lastMonthPallets > 0 ? ($diffPallets / $lastMonthPallets) * 100 : 0;

        // Daily Chart Data
        $dailyTrendRaw = IkatTerpal::selectRaw('DATE(tanggal) as date, SUM(qty_pallet) as total_pallet, SUM(total_fee) as total_fee, SUM(jml_buruh) as total_buruh')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        $dailyTrend = [];
        $currentDate = clone $startOfMonth;
        while ($currentDate <= $endOfMonth) {
            $dateString = $currentDate->toDateString();
            $data = $dailyTrendRaw->get($dateString);

            $dailyTrend[] = [
                'date' => $dateString,
                'total_pallet' => $data ? (float)$data->total_pallet : 0,
                'total_fee' => $data ? (float)$data->total_fee : 0,
                'total_buruh' => $data ? (float)$data->total_buruh : 0,
            ];
            $currentDate->addDay();
        }

        // Monthly Trend (Last 12 Months)
        $monthlyTrend = IkatTerpal::selectRaw('YEAR(tanggal) as year, MONTH(tanggal) as month, SUM(qty_pallet) as total_pallet, SUM(subtotal_barang) as total_subtotal, SUM(total_fee) as total_fee')
            ->where('tanggal', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) use ($ppnRate, $pphRate) {
                $item->month_name = Carbon::createFromDate($item->year, $item->month, 1)->format('M Y');
                $total_ppn = ($ppnRate / 100) * $item->total_fee;
                $total_pph = ($pphRate / 100) * $item->total_fee;
                $item->grand_total = ($item->total_subtotal + $item->total_fee + $total_ppn) - $total_pph;
                return $item;
            });

        return response()->json([
            'status' => 'success',
            'period' => $startOfMonth->translatedFormat('F Y'),
            'stats' => [
                'total_pallets' => [
                    'value' => number_format($totalPallets, 0, ',', '.'),
                    'diff' => number_format($diffPallets, 0, ',', '.'),
                    'percent' => round($percChange, 1),
                    'trend' => $percChange >= 0 ? 'up' : 'down'
                ],
                'total_buruh' => number_format($totalBuruh, 0, ',', '.'),
                'total_subtotal' => number_format($totalSubtotal, 0, ',', '.'),
                'total_fee' => number_format($totalFee, 0, ',', '.'),
                'ppn' => number_format($totalPpn, 0, ',', '.'),
                'pph' => number_format($totalPph, 0, ',', '.'),
                'grand_total' => number_format($grandTotal, 0, ',', '.'),
                'avg_daily' => number_format($totalPallets / $startOfMonth->daysInMonth, 1, ',', '.'),
            ],
            'charts' => [
                'daily' => $dailyTrend,
                'monthly' => $monthlyTrend,
            ],
            'recent_entries' => IkatTerpal::with(['user:id,nama_lengkap', 'fee'])
                ->orderBy('tanggal', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $ppnRate = $item->fee ? $item->fee->ppn : 0;
                    $pphRate = $item->fee ? $item->fee->pph : 0;
                    $total_ppn = ($ppnRate / 100) * $item->total_fee;
                    $total_pph = ($pphRate / 100) * $item->total_fee;
                    $item->calculated_grand_total = ($item->subtotal_barang + $item->total_fee + $total_ppn) - $total_pph;
                    return $item;
                })
        ]);
    }
}
