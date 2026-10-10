<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Wsp\BarangModel;
use App\Models\Wsp\RakModel;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionModel;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionApprovalModel;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionItemsModel;
use App\Models\Wsp\purchase_requesition\WspStockReservations;
use App\Models\Wsp\stock_manage\StockOnHandWspModel;
use App\Models\Wsp\stock_move\WspIncomingModel;
use App\Models\Wsp\stock_move\WspOutgoingModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WspDashboardController extends Controller
{
    /**
     * Display the WSP Analytics & Intelligence Dashboard.
     */
    public function index()
    {
        $departments = WspPurchaseRequesitionModel::whereNotNull('department')
            ->where('department', '<>', '')
            ->distinct()
            ->orderBy('department', 'asc')
            ->pluck('department');

        $jenisList = WspPurchaseRequesitionModel::whereNotNull('jenis')
            ->where('jenis', '<>', '')
            ->distinct()
            ->orderBy('jenis', 'asc')
            ->pluck('jenis');

        $raks = RakModel::orderBy('detail_loc', 'asc')->get();

        return view('dashboard.wsp_analytics', compact('departments', 'jenisList', 'raks'));
    }

    /**
     * Aggregate data for WSP Analytic Clusters.
     */
    public function data(Request $request)
    {
        $period = $request->input('period', '30days');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $departmentFilter = $request->input('department');
        $jenisFilter = $request->input('jenis');
        $rakFilter = $request->input('rak_id');

        // Date Range Calculation
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } else {
            switch ($period) {
                case 'today':
                    $start = Carbon::today()->startOfDay();
                    $end = Carbon::today()->endOfDay();
                    break;
                case '7days':
                    $start = Carbon::now()->subDays(6)->startOfDay();
                    $end = Carbon::now()->endOfDay();
                    break;
                case 'month':
                    $start = Carbon::now()->startOfMonth();
                    $end = Carbon::now()->endOfDay();
                    break;
                case 'all':
                    $start = Carbon::create(2020, 1, 1)->startOfDay();
                    $end = Carbon::now()->endOfDay();
                    break;
                case '30days':
                default:
                    $start = Carbon::now()->subDays(29)->startOfDay();
                    $end = Carbon::now()->endOfDay();
                    break;
            }
        }

        // ==========================================
        // 1. SOH (STOCK ON HAND) INTELLIGENCE
        // ==========================================
        $sohQuery = StockOnHandWspModel::query();
        if ($rakFilter && $rakFilter !== 'all') {
            $sohQuery->whereHas('barang.activeStockLocation', function ($q) use ($rakFilter) {
                $q->where('rak_id', $rakFilter);
            });
        }

        $sohStats = DB::table('wsp_stock_on_hand')
            ->selectRaw('
                COUNT(*) as total_records,
                COUNT(DISTINCT barang_id) as total_skus,
                COALESCE(SUM(qty_soh), 0) as total_qty,
                COALESCE(SUM(unrest), 0) as total_unrest,
                COALESCE(SUM(qual_insp), 0) as total_qi,
                COALESCE(SUM(blocked), 0) as total_blocked,
                COALESCE(SUM(transf), 0) as total_transf,
                COUNT(CASE WHEN qty_soh = 0 THEN 1 END) as zero_stock_count,
                COUNT(CASE WHEN qty_soh > 0 THEN 1 END) as in_stock_count
            ')->first();

        // Top 10 High Inventory Spareparts
        $topStockItems = DB::table('wsp_stock_on_hand')
            ->join('wsp_barang', 'wsp_stock_on_hand.barang_id', '=', 'wsp_barang.id')
            ->select(
                'wsp_barang.mid_barang',
                'wsp_barang.nama_barang',
                'wsp_barang.uom',
                DB::raw('SUM(wsp_stock_on_hand.qty_soh) as total_qty'),
                DB::raw('SUM(wsp_stock_on_hand.unrest) as total_unrest'),
                DB::raw('SUM(wsp_stock_on_hand.blocked) as total_blocked')
            )
            ->groupBy('wsp_barang.id', 'wsp_barang.mid_barang', 'wsp_barang.nama_barang', 'wsp_barang.uom')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Critical / Zero Stock Samples (Top 10 Zero Stock for Attention)
        $zeroStockItems = DB::table('wsp_stock_on_hand')
            ->join('wsp_barang', 'wsp_stock_on_hand.barang_id', '=', 'wsp_barang.id')
            ->where('wsp_stock_on_hand.qty_soh', '=', 0)
            ->select('wsp_barang.mid_barang', 'wsp_barang.nama_barang', 'wsp_barang.uom', 'wsp_stock_on_hand.last_update')
            ->orderBy('wsp_stock_on_hand.last_update', 'desc')
            ->limit(10)
            ->get();

        // Storage Distribution (SOH by Rak)
        $rakDistribution = DB::table('wsp_stock_location')
            ->join('wsp_rak', 'wsp_stock_location.rak_id', '=', 'wsp_rak.id')
            ->join('wsp_stock_on_hand', 'wsp_stock_location.barang_id', '=', 'wsp_stock_on_hand.barang_id')
            ->select(
                'wsp_rak.detail_loc',
                'wsp_rak.s_loc',
                DB::raw('COUNT(DISTINCT wsp_stock_location.barang_id) as sku_count'),
                DB::raw('SUM(wsp_stock_on_hand.qty_soh) as total_qty')
            )
            ->groupBy('wsp_rak.id', 'wsp_rak.detail_loc', 'wsp_rak.s_loc')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->get();

        // ==========================================
        // 2. PURCHASE REQUISITION (PR) INTELLIGENCE
        // ==========================================
        $prQuery = WspPurchaseRequesitionModel::whereBetween('created_at', [$start, $end]);

        if ($departmentFilter && $departmentFilter !== 'all') {
            $prQuery->where('department', $departmentFilter);
        }

        if ($jenisFilter && $jenisFilter !== 'all') {
            $prQuery->where('jenis', $jenisFilter);
        }

        $allFilteredPrs = $prQuery->get();
        $prIds = $allFilteredPrs->pluck('id');

        $totalPrCount = $allFilteredPrs->count();
        $prApprovedCount = $allFilteredPrs->where('status', 'approved')->count();
        $prPendingCount = $allFilteredPrs->where('status', 'pending')->count();
        $prRejectedCount = $allFilteredPrs->where('status', 'rejected')->count();

        $prApprovalRate = $totalPrCount > 0 ? round(($prApprovedCount / $totalPrCount) * 100, 1) : 0;

        // Total PR Items Requested
        $prItemsStats = WspPurchaseRequesitionItemsModel::whereIn('pr_id', $prIds)
            ->selectRaw('COUNT(*) as total_items, COALESCE(SUM(qty), 0) as total_qty')
            ->first();

        // PR Inflow Trend Over Time
        $prTrendRaw = WspPurchaseRequesitionModel::whereBetween('created_at', [$start, $end])
            ->when($departmentFilter && $departmentFilter !== 'all', fn($q) => $q->where('department', $departmentFilter))
            ->when($jenisFilter && $jenisFilter !== 'all', fn($q) => $q->where('jenis', $jenisFilter))
            ->selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
            ->groupBy(DB::raw('DATE(created_at)'), 'status')
            ->orderBy('date', 'asc')
            ->get();

        $trendMap = [];
        foreach ($prTrendRaw as $row) {
            $d = $row->date;
            if (!isset($trendMap[$d])) {
                $trendMap[$d] = ['approved' => 0, 'pending' => 0, 'rejected' => 0];
            }
            $trendMap[$d][$row->status] = (int) $row->count;
        }

        $prTrendCategories = [];
        $prTrendApproved = [];
        $prTrendPending = [];
        $prTrendRejected = [];

        // Generate full daily series for smoother line/bar chart if <= 31 days
        $daysDiff = $start->diffInDays($end);
        if ($daysDiff <= 31) {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $dStr = $cursor->format('Y-m-d');
                $prTrendCategories[] = $cursor->format('d M');
                $prTrendApproved[] = $trendMap[$dStr]['approved'] ?? 0;
                $prTrendPending[] = $trendMap[$dStr]['pending'] ?? 0;
                $prTrendRejected[] = $trendMap[$dStr]['rejected'] ?? 0;
                $cursor->addDay();
            }
        } else {
            // Group by Month if larger range
            $prTrendGrouped = WspPurchaseRequesitionModel::whereBetween('created_at', [$start, $end])
                ->when($departmentFilter && $departmentFilter !== 'all', fn($q) => $q->where('department', $departmentFilter))
                ->when($jenisFilter && $jenisFilter !== 'all', fn($q) => $q->where('jenis', $jenisFilter))
                ->selectRaw("DATE_FORMAT(created_at, '%b %Y') as m_label, status, COUNT(*) as count")
                ->groupBy('m_label', 'status')
                ->get();

            $mMap = [];
            foreach ($prTrendGrouped as $row) {
                $lbl = $row->m_label;
                if (!isset($mMap[$lbl])) {
                    $mMap[$lbl] = ['approved' => 0, 'pending' => 0, 'rejected' => 0];
                }
                $mMap[$lbl][$row->status] = (int) $row->count;
            }

            foreach ($mMap as $mLabel => $counts) {
                $prTrendCategories[] = $mLabel;
                $prTrendApproved[] = $counts['approved'];
                $prTrendPending[] = $counts['pending'];
                $prTrendRejected[] = $counts['rejected'];
            }
        }

        // PR by Department
        $prByDeptRaw = WspPurchaseRequesitionModel::whereBetween('created_at', [$start, $end])
            ->when($jenisFilter && $jenisFilter !== 'all', fn($q) => $q->where('jenis', $jenisFilter))
            ->select('department', DB::raw('COUNT(*) as count'))
            ->groupBy('department')
            ->orderByDesc('count')
            ->get();

        $prByDept = $prByDeptRaw->map(function ($item) {
            return [
                'department' => strtoupper(str_replace('_', ' ', $item->department ?: 'N/A')),
                'count' => (int) $item->count,
            ];
        });

        // PR by Jenis & Detail Jenis
        $prByJenisRaw = WspPurchaseRequesitionModel::whereBetween('created_at', [$start, $end])
            ->when($departmentFilter && $departmentFilter !== 'all', fn($q) => $q->where('department', $departmentFilter))
            ->select('jenis', DB::raw('COUNT(*) as count'))
            ->groupBy('jenis')
            ->orderByDesc('count')
            ->get();

        $prByJenis = $prByJenisRaw->map(function ($item) {
            return [
                'jenis' => strtoupper($item->jenis ?: 'REGULER'),
                'count' => (int) $item->count,
            ];
        });

        // Top 10 Most Requested Items in PR
        $topRequestedItems = WspPurchaseRequesitionItemsModel::whereIn('pr_id', $prIds)
            ->leftJoin('wsp_barang', 'wsp_purchase_requesition_items.barang_id', '=', 'wsp_barang.id')
            ->select(
                DB::raw('COALESCE(wsp_barang.nama_barang, wsp_purchase_requesition_items.desc, "Item") as item_name'),
                DB::raw('COALESCE(wsp_barang.mid_barang, "-") as mid_code'),
                DB::raw('COUNT(*) as request_freq'),
                DB::raw('SUM(wsp_purchase_requesition_items.qty) as total_qty')
            )
            ->groupBy('item_name', 'mid_code')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // ==========================================
        // 3. APPROVAL WORKFLOW & BOTTLENECK ANALYSIS (LIKE VEHICLE STAGE CYCLE TIME)
        // ==========================================
        // Pending approvals per role
        $approvalBottlenecksRaw = DB::table('wsp_purchase_requesition_approval')
            ->join('wsp_purchase_requesition', 'wsp_purchase_requesition_approval.pr_id', '=', 'wsp_purchase_requesition.id')
            ->where('wsp_purchase_requesition.status', 'pending')
            ->where('wsp_purchase_requesition_approval.status', 'pending')
            ->select('wsp_purchase_requesition_approval.role', DB::raw('COUNT(*) as pending_count'))
            ->groupBy('wsp_purchase_requesition_approval.role')
            ->orderByDesc('pending_count')
            ->get();

        $approvalBottlenecks = $approvalBottlenecksRaw->map(function ($item) {
            return [
                'role' => ucwords(str_replace('_', ' ', $item->role)),
                'pending_count' => (int) $item->pending_count,
            ];
        });

        // Average Lead Time / Turnaround Time (TAT) in Hours for completed approvals
        $tatRaw = DB::table('wsp_purchase_requesition_approval')
            ->whereNotNull('action_at')
            ->where('status', 'approved')
            ->select(
                'role',
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, action_at)) as avg_minutes'),
                DB::raw('COUNT(*) as sample_count')
            )
            ->groupBy('role')
            ->orderBy('avg_minutes', 'asc')
            ->get();

        $stageTat = $tatRaw->map(function ($item) {
            $mins = round($item->avg_minutes);
            $hours = round($mins / 60, 1);
            $formatted = $hours >= 24 ? round($hours / 24, 1) . ' hr' : $hours . ' jam';
            return [
                'role' => ucwords(str_replace('_', ' ', $item->role)),
                'avg_minutes' => $mins,
                'avg_hours' => $hours,
                'formatted' => $formatted,
                'sample_count' => $item->sample_count,
            ];
        });

        // Overall Average PR Lead Time (Creation to Last Approval)
        $completedPrTat = DB::table('wsp_purchase_requesition')
            ->where('status', 'approved')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_lead_hours')
            ->first();
        $overallTatHours = round($completedPrTat->avg_lead_hours ?? 0, 1);
        $overallTatFormatted = $overallTatHours >= 24 ? round($overallTatHours / 24, 1) . ' Hari' : $overallTatHours . ' Jam';

        // Longest Pending PRs (Attention List / Bottlenecks)
        $longestPendingPrsRaw = WspPurchaseRequesitionModel::where('status', 'pending')
            ->with(['approval' => function ($q) {
                $q->where('status', 'pending')->orderBy('level', 'asc');
            }, 'items', 'user'])
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        $longestPendingPrs = $longestPendingPrsRaw->map(function ($pr) {
            $currentApproval = $pr->approval->first();
            $agingHours = round(Carbon::parse($pr->created_at)->diffInHours(now()));
            $agingFormatted = $agingHours >= 24 ? round($agingHours / 24) . 'h ' . ($agingHours % 24) . 'j' : $agingHours . ' jam';

            return [
                'id' => $pr->id,
                'no_doc' => $pr->no_doc ?: ('PR #' . $pr->id),
                'requested_by' => $pr->requested_by ?: ($pr->user->nama_lengkap ?? 'User'),
                'department' => strtoupper($pr->department ?: 'N/A'),
                'jenis' => strtoupper($pr->jenis ?: 'REGULER'),
                'created_at' => Carbon::parse($pr->created_at)->format('d M Y H:i'),
                'aging_hours' => $agingHours,
                'aging_formatted' => $agingFormatted,
                'current_role' => $currentApproval ? ucwords($currentApproval->role) : 'Pending Final',
                'item_count' => $pr->items->count(),
                'status' => $pr->status,
            ];
        });

        $pendingBottlenecksCount = WspPurchaseRequesitionModel::where('status', 'pending')
            ->where('created_at', '<=', now()->subHours(24))
            ->count();

        // ==========================================
        // 4. STOCK RESERVATIONS & MATERIAL MOVEMENT
        // ==========================================
        $activeReservations = WspStockReservations::where('status', 'active')->count();
        $totalReservations = WspStockReservations::count();
        $confirmedReservations = WspStockReservations::where('status', 'confirmed')->count();
        $expiredReservations = WspStockReservations::whereIn('status', ['expired', 'released'])->count();

        // Recent Inbound (Incoming) & Outbound Movement
        $incomingCount = WspIncomingModel::count();
        $outgoingCount = WspOutgoingModel::count();

        return response()->json([
            'success' => true,
            'period' => [
                'selected' => $period,
                'start' => $start->format('d M Y'),
                'end' => $end->format('d M Y'),
                'days' => $daysDiff + 1,
            ],
            'kpi' => [
                'total_soh_qty' => (int) $sohStats->total_qty,
                'total_skus' => (int) $sohStats->total_skus,
                'total_unrest_qty' => (int) $sohStats->total_unrest,
                'total_blocked_qty' => (int) $sohStats->total_blocked,
                'total_qi_qty' => (int) $sohStats->total_qi,
                'zero_stock_count' => (int) $sohStats->zero_stock_count,
                'in_stock_count' => (int) $sohStats->in_stock_count,
                'in_stock_rate' => $sohStats->total_skus > 0 ? round(($sohStats->in_stock_count / $sohStats->total_skus) * 100, 1) : 0,
                'total_pr_count' => $totalPrCount,
                'pr_approved_count' => $prApprovedCount,
                'pr_pending_count' => $prPendingCount,
                'pr_rejected_count' => $prRejectedCount,
                'pr_approval_rate' => $prApprovalRate,
                'total_items_requested' => (int) ($prItemsStats->total_items ?? 0),
                'total_items_qty' => (int) ($prItemsStats->total_qty ?? 0),
                'active_reservations' => $activeReservations,
                'pending_bottlenecks' => $pendingBottlenecksCount,
                'overall_tat_formatted' => $overallTatFormatted,
                'incoming_count' => $incomingCount,
                'outgoing_count' => $outgoingCount,
            ],
            'soh' => [
                'composition' => [
                    'unrestricted' => (int) $sohStats->total_unrest,
                    'qual_insp' => (int) $sohStats->total_qi,
                    'blocked' => (int) $sohStats->total_blocked,
                    'transf' => (int) $sohStats->total_transf,
                ],
                'top_items' => $topStockItems,
                'zero_stock_items' => $zeroStockItems,
                'rak_distribution' => $rakDistribution,
            ],
            'pr' => [
                'trend' => [
                    'categories' => $prTrendCategories,
                    'approved' => $prTrendApproved,
                    'pending' => $prTrendPending,
                    'rejected' => $prTrendRejected,
                ],
                'by_department' => $prByDept,
                'by_jenis' => $prByJenis,
                'top_requested_items' => $topRequestedItems,
            ],
            'workflow' => [
                'bottlenecks' => $approvalBottlenecks,
                'stage_tat' => $stageTat,
                'longest_pending_prs' => $longestPendingPrs,
            ],
            'reservations' => [
                'total' => $totalReservations,
                'active' => $activeReservations,
                'confirmed' => $confirmedReservations,
                'expired' => $expiredReservations,
            ],
        ]);
    }
}
