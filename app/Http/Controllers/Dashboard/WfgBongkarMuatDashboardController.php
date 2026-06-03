<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Wfg\BongkarMuat;
use App\Models\Wfg\BongkarMuatDetail;
use App\Models\Wfg\MasterDestinasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WfgBongkarMuatDashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.wfg_bongkar_muat_dashboard');
    }

    // --- 1. KPI Cards ---
    public function getKpi(Request $request)
    {
        $query = BongkarMuatDetail::join('wfg_bongkar_muats', 'wfg_bongkar_muat_details.bongkar_muat_id', '=', 'wfg_bongkar_muats.id');

        // Date filter
        if ($request->start_date) {
            $query->whereDate('wfg_bongkar_muats.tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('wfg_bongkar_muats.tanggal', '<=', $request->end_date);
        }

        // Exclude draft orders from qty summary (only count submitted+)
        $qtyQuery = (clone $query)->whereNotIn('wfg_bongkar_muats.status', ['draft']);

        $totalQtyFull  = (clone $qtyQuery)->where('wfg_bongkar_muat_details.jenis', 'P')->sum('wfg_bongkar_muat_details.qty');
        $totalQtyReceh = (clone $qtyQuery)->where('wfg_bongkar_muat_details.jenis', 'R')->sum('wfg_bongkar_muat_details.qty');
        $totalQtyBox   = $totalQtyFull + $totalQtyReceh;

        // Count of orders per status (all dates if no filter)
        $orderQuery = BongkarMuat::query();
        if ($request->start_date) {
            $orderQuery->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $orderQuery->whereDate('tanggal', '<=', $request->end_date);
        }

        $countByStatus = (clone $orderQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusList = ['draft', 'submitted', 'approved', 'loaded', 'verified', 'rejected'];
        $statusCounts = [];
        foreach ($statusList as $s) {
            $statusCounts[$s] = $countByStatus[$s] ?? 0;
        }

        // Today's loading orders
        $todayCount = BongkarMuat::whereDate('tanggal', Carbon::today())->count();
        $todayQtyFull  = BongkarMuatDetail::join('wfg_bongkar_muats', 'wfg_bongkar_muat_details.bongkar_muat_id', '=', 'wfg_bongkar_muats.id')
            ->whereDate('wfg_bongkar_muats.tanggal', Carbon::today())
            ->whereNotIn('wfg_bongkar_muats.status', ['draft'])
            ->where('wfg_bongkar_muat_details.jenis', 'P')
            ->sum('wfg_bongkar_muat_details.qty');

        // Outbound BAS & SMU calculation
        $outboundQuery = BongkarMuatDetail::join('wfg_bongkar_muats', 'wfg_bongkar_muat_details.bongkar_muat_id', '=', 'wfg_bongkar_muats.id')
            ->join('wfg_barang', 'wfg_bongkar_muat_details.material_id', '=', 'wfg_barang.id')
            ->whereNotIn('wfg_bongkar_muats.status', ['draft']);

        if ($request->start_date) {
            $outboundQuery->whereDate('wfg_bongkar_muats.tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $outboundQuery->whereDate('wfg_bongkar_muats.tanggal', '<=', $request->end_date);
        }

        $outboundBAS = (clone $outboundQuery)->where('wfg_barang.principal', 'BAS')->count();
        $outboundSMU = (clone $outboundQuery)->where('wfg_barang.principal', '!=', 'BAS')->count();
        $totalOutbound = $outboundBAS + $outboundSMU;

        // Truck logic
        $truckQuery = BongkarMuat::whereNotIn('status', ['draft', 'rejected']);
        if ($request->start_date) {
            $truckQuery->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $truckQuery->whereDate('tanggal', '<=', $request->end_date);
        }

        $truckSlipsheet = (clone $truckQuery)->where('jumlah_slipsheet', '>', 0)->count();
        $truckCurah = (clone $truckQuery)->where(function ($q) {
            $q->whereNull('jumlah_slipsheet')->orWhere('jumlah_slipsheet', 0);
        })->count();
        $truckFinish = $truckSlipsheet + $truckCurah;

        return response()->json([
            'status' => true,
            'data' => [
                'total_qty_box'   => (int) $totalQtyBox,
                'total_qty_full'  => (int) $totalQtyFull,
                'total_qty_receh' => (int) $totalQtyReceh,
                'status_counts'   => $statusCounts,
                'today_count'     => $todayCount,
                'today_qty_full'  => (int) $todayQtyFull,
                'truck_finish'    => $truckFinish,
                'truck_slipsheet' => $truckSlipsheet,
                'truck_curah'     => $truckCurah,
                'outbound_bas'    => $outboundBAS,
                'outbound_smu'    => $outboundSMU,
                'total_outbound'  => $totalOutbound,
            ]
        ]);
    }

    // --- 2. Wavepick list by status ---
    public function getWavepickByStatus(Request $request)
    {
        $status = $request->status ?? null; // null = all

        $query = BongkarMuat::with([
            'forkliftDriver:id,nama_lengkap',
            'checker:id,nama_lengkap',
            'destinasi:id,destinasi',
        ])
            ->whereNotIn('status', ['draft', 'verified', 'rejected'])
            ->withCount('details')
            ->withSum(['details as total_qty_full' => function ($q) {
                $q->where('jenis', 'P');
            }], 'qty')
            ->withSum(['details as total_qty_receh' => function ($q) {
                $q->where('jenis', 'R');
            }], 'qty');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->start_date) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        $orders = $query->latest('tanggal')->latest('id')->limit(100)->get()->map(function ($o) {
            return [
                'id'              => $o->id,
                'tanggal'         => $o->tanggal ? Carbon::parse($o->tanggal)->format('d M Y') : '-',
                'wavepick_smu'    => $o->wavepick_smu ?? '-',
                'wavepick_bas'    => $o->wavepick_bas ?? '-',
                'shipment_smu'    => $o->shipment_smu ?? '-',
                'destinasi'       => $o->destinasi?->destinasi ?? '-',
                'driver'          => $o->forkliftDriver?->nama_lengkap ?? '-',
                'checker'         => $o->checker?->nama_lengkap ?? '-',
                'gate'            => $o->gate ?? '-',
                'no_mobil'        => $o->no_mobil ?? '-',
                'status'          => $o->status,
                'details_count'   => $o->details_count,
                'total_qty_full'  => (int) $o->total_qty_full,
                'total_qty_receh' => (int) $o->total_qty_receh,
                'total_qty_box'   => (int) ($o->total_qty_full + $o->total_qty_receh),
                'jam_muat'        => $o->jam_muat ?? '-',
                'approved_at'     => $o->approved_at ? Carbon::parse($o->approved_at)->format('d M Y H:i') : '-',
            ];
        });

        return response()->json(['status' => true, 'data' => $orders]);
    }

    // --- 3. Chart Trend: Loading Orders per day ---
    public function getChartTrend(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(29)->startOfDay();
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()     : Carbon::now()->endOfDay();

        $dailyData = BongkarMuat::whereBetween('tanggal', [$startDate, $endDate])
            ->whereNotIn('status', ['draft'])
            ->selectRaw('DATE(tanggal) as date, SUM(1) as order_count')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $qtyDaily = BongkarMuatDetail::join('wfg_bongkar_muats', 'wfg_bongkar_muat_details.bongkar_muat_id', '=', 'wfg_bongkar_muats.id')
            ->whereBetween('wfg_bongkar_muats.tanggal', [$startDate, $endDate])
            ->whereNotIn('wfg_bongkar_muats.status', ['draft'])
            ->selectRaw("DATE(wfg_bongkar_muats.tanggal) as date, SUM(CASE WHEN wfg_bongkar_muat_details.jenis = 'P' THEN wfg_bongkar_muat_details.qty ELSE 0 END) as total_full, SUM(CASE WHEN wfg_bongkar_muat_details.jenis = 'R' THEN wfg_bongkar_muat_details.qty ELSE 0 END) as total_receh")
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $categories  = [];
        $seriesOrder = [];
        $seriesFull  = [];
        $seriesReceh = [];

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $categories[]  = $date->format('d M');
            $seriesOrder[]  = isset($dailyData[$dateStr]) ? (int) $dailyData[$dateStr]->order_count : 0;
            $seriesFull[]   = isset($qtyDaily[$dateStr]) ? (int) $qtyDaily[$dateStr]->total_full : 0;
            $seriesReceh[]  = isset($qtyDaily[$dateStr]) ? (int) $qtyDaily[$dateStr]->total_receh : 0;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'categories' => $categories,
                'series' => [
                    ['name' => 'Bongkar Muat', 'data' => $seriesOrder, 'color' => '#6366f1', 'type' => 'spline', 'yAxis' => 1],
                    ['name' => 'QTY Full (Box)',  'data' => $seriesFull,  'color' => '#22c55e', 'type' => 'column', 'yAxis' => 0],
                    ['name' => 'QTY Receh (Pcs)', 'data' => $seriesReceh, 'color' => '#f59e0b', 'type' => 'column', 'yAxis' => 0],
                ]
            ]
        ]);
    }

    // --- 4. Donut Chart: Status Distribution ---
    public function getChartStatus(Request $request)
    {
        $query = BongkarMuat::query();

        if ($request->start_date) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        $data = $query->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $colorMap = [
            'draft'     => '#94a3b8',
            'submitted' => '#3b82f6',
            'approved'  => '#a855f7',
            'loaded'    => '#f59e0b',
            'verified'  => '#16a34a',
            'rejected'  => '#dc2626',
        ];

        $chartData = $data->map(fn($d) => [
            'name'  => ucfirst($d->status),
            'y'     => (int) $d->total,
            'color' => $colorMap[$d->status] ?? '#64748b',
        ])->values()->toArray();

        return response()->json(['status' => true, 'data' => $chartData]);
    }

    // --- 5. Bar Chart: Top Destinations ---
    public function getChartDestination(Request $request)
    {
        $query = BongkarMuat::join('wfg_master_destinasi', 'wfg_bongkar_muats.destinasi_id', '=', 'wfg_master_destinasi.id')
            ->whereNotIn('wfg_bongkar_muats.status', ['draft', 'rejected']);

        if ($request->start_date) {
            $query->whereDate('wfg_bongkar_muats.tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('wfg_bongkar_muats.tanggal', '<=', $request->end_date);
        }

        $results = $query->select('wfg_master_destinasi.destinasi', DB::raw('count(*) as total'))
            ->groupBy('wfg_master_destinasi.id', 'wfg_master_destinasi.destinasi')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $categories = $results->pluck('destinasi')->toArray();
        $data       = $results->map(fn($r) => (int) $r->total)->toArray();

        return response()->json([
            'status' => true,
            'data' => [
                'categories' => $categories,
                'series' => [
                    ['name' => 'Bongkar Muat', 'data' => $data, 'color' => '#06b6d4', 'type' => 'bar']
                ]
            ]
        ]);
    }

    // --- 6. Loading Visual: Draft Orders ---
    public function getLoadingVisual(Request $request)
    {
        $query = BongkarMuat::with([
            'checker:id,nama_lengkap,username',
            'destinasi:id,destinasi',
            'details.material:id,mid_barang,nama_barang',
            'forkliftDriver:id,nama_lengkap,username',
        ])
            ->where('status', 'draft');

        if ($request->start_date) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        $orders = $query->latest('id')->limit(15)->get()->map(function ($o) {
            $totalFull  = $o->details->where('jenis', 'P')->sum('qty');
            $totalReceh = $o->details->where('jenis', 'R')->sum('qty');

            return [
                'id'              => $o->id,
                'wavepick'        => $o->wavepick_smu ?: ($o->wavepick_bas ?: '-'),
                'destinasi'       => $o->destinasi?->destinasi ?? '-',
                'checker'         => $o->checker?->nama_lengkap ?? $o->checker?->username,
                'forklift_driver' => $o->forkliftDriver?->nama_lengkap ?? $o->forkliftDriver?->username,
                'gate'            => $o->gate ?? '-',
                'no_mobil'        => $o->no_mobil ?? '-',
                'total_full'      => (int) $totalFull,
                'total_receh'     => (int) $totalReceh,
                'total_qty'       => (int) ($totalFull + $totalReceh),
                'total_items'     => $o->details->count(),
                'items' => $o->details
                    ->sortByDesc('id')
                    ->map(fn($d) => [
                        'material' => $d->material?->mid_barang ?? '-',
                        'qty'      => $d->qty,
                        'jenis'    => $d->jenis
                    ])
                    ->values()
            ];
        });

        return response()->json(['status' => true, 'data' => $orders]);
    }
}
