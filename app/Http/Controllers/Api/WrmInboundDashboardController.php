<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WrmInboundDashboardController extends Controller
{
    private function getFilter(Request $request): array
    {
        return [
            'periode'   => $request->input('periode', 'bulanan'),
            'tgl_mulai' => $request->input('tgl_mulai'),
            'tgl_akhir' => $request->input('tgl_akhir'),
        ];
    }

    private function applyDateFilter($query, array $filter)
    {
        if ($filter['tgl_mulai'] && $filter['tgl_akhir']) {
            $query->whereBetween('incoming_date', [$filter['tgl_mulai'], $filter['tgl_akhir']]);
        }
        return $query;
    }

    private function getPeriodeLabel(string $periode, string $date): string
    {
        return match ($periode) {
            'harian'   => \Carbon\Carbon::parse($date)->format('d M Y'),
            'mingguan' => 'Minggu ' . \Carbon\Carbon::parse($date)->weekOfMonth . ' ' . \Carbon\Carbon::parse($date)->format('M Y'),
            'tahunan'  => \Carbon\Carbon::parse($date)->format('Y'),
            default    => \Carbon\Carbon::parse($date)->format('M Y'),
        };
    }

    private function getGroupByPeriode(string $periode): string
    {
        return match ($periode) {
            'harian'   => "DATE(incoming_date)",
            'mingguan' => "YEARWEEK(incoming_date, 1)",
            'tahunan'  => "YEAR(incoming_date)",
            default    => "DATE_FORMAT(incoming_date, '%Y-%m')",
        };
    }

    public function widget(Request $request)
    {
        $filter = $this->getFilter($request);

        $query = DB::table('wrm_stock_gula');
        $this->applyDateFilter($query, $filter);

        $totalQty    = (clone $query)->sum('qty');
        $totalPallet = (clone $query)->count();
        $totalBatch  = (clone $query)->distinct('no_spb')->count('no_spb');
        $totalAlert  = (clone $query)->whereIn('status', ['QI', 'LELEH'])->count();

        return response()->json([
            'total_qty'    => $totalQty,
            'total_pallet' => $totalPallet,
            'total_batch'  => $totalBatch,
            'total_alert'  => $totalAlert,
        ]);
    }

    public function perPeriode(Request $request)
    {
        $filter  = $this->getFilter($request);
        $groupBy = $this->getGroupByPeriode($filter['periode']);

        $query = DB::table('wrm_stock_gula')
            ->select(
                DB::raw("{$groupBy} as periode_key"),
                DB::raw('MIN(incoming_date) as sample_date'),
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('COUNT(*) as total_pallet'),
                DB::raw('COUNT(DISTINCT no_spb) as total_batch')
            )
            ->whereNotNull('incoming_date')
            ->groupBy(DB::raw($groupBy))
            ->orderBy(DB::raw($groupBy));

        $this->applyDateFilter($query, $filter);

        $data = $query->get()->map(function ($item) use ($filter) {
            return [
                'label'        => $this->getPeriodeLabel($filter['periode'], $item->sample_date),
                'total_qty'    => $item->total_qty,
                'total_pallet' => $item->total_pallet,
                'total_batch'  => $item->total_batch,
            ];
        });

        return response()->json($data);
    }

    public function stokPerBarang(Request $request)
    {
        $filter = $this->getFilter($request);

        $query = DB::table('wrm_stock_gula')
            ->join('wrm_master_barang', 'wrm_stock_gula.barang_id', '=', 'wrm_master_barang.id')
            ->select(
                'wrm_master_barang.nama_barang',
                DB::raw('SUM(wrm_stock_gula.qty) as total_qty'),
                DB::raw('COUNT(*) as total_pallet'),
                DB::raw('COUNT(DISTINCT wrm_stock_gula.no_spb) as total_batch')
            )
            ->groupBy('wrm_master_barang.id', 'wrm_master_barang.nama_barang')
            ->orderByDesc('total_qty');

        $this->applyDateFilter($query, $filter);

        return response()->json($query->get());
    }

    public function distribusiStatus(Request $request)
    {
        $filter = $this->getFilter($request);

        $query = DB::table('wrm_stock_gula')
            ->select(
                'status',
                DB::raw('COUNT(*) as total_pallet'),
                DB::raw('SUM(qty) as total_qty')
            )
            ->groupBy('status')
            ->orderBy('status');

        $this->applyDateFilter($query, $filter);

        return response()->json($query->get());
    }

    public function stokPerGudang(Request $request)
    {
        $filter = $this->getFilter($request);

        $query = DB::table('wrm_stock_gula')
            ->select(
                'gudang',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('COUNT(*) as total_pallet'),
                DB::raw('COUNT(DISTINCT no_spb) as total_batch')
            )
            ->whereNotNull('gudang')
            ->groupBy('gudang')
            ->orderBy('gudang');

        $this->applyDateFilter($query, $filter);

        return response()->json($query->get());
    }
}
