<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Vehicle\Location;
use App\Models\User;
use App\Models\Vehicle\Vehicle;
use App\Models\Vehicle\VehicleItem;
use App\Models\Vehicle\VehicleTracking;
use App\Models\Vehicle\VehicleTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleAnalyticsController extends Controller
{
    /**
     * Display the Dedicated Vehicle Intelligence & Analytics Dashboard.
     */
    public function index()
    {
        $vendors = VehicleTransaction::whereNotNull('vendor')
            ->where('vendor', '<>', '')
            ->distinct()
            ->orderBy('vendor', 'asc')
            ->pluck('vendor');

        $locations = Location::where('s_loc', '!=', 'TMB')
            ->orderBy('name', 'asc')
            ->get();

        $items = VehicleItem::orderBy('name', 'asc')->get();

        return view('dashboard.vehicle_analytics', compact('vendors', 'locations', 'items'));
    }

    /**
     * Helper duration formatter.
     */
    private function formatDuration($minutes)
    {
        if ($minutes <= 0) return '0 mnt';
        if ($minutes < 60) return round($minutes) . ' mnt';
        $h = floor($minutes / 60);
        $m = round($minutes % 60);
        return $h . 'j ' . ($m > 0 ? $m . 'm' : '');
    }

    /**
     * Parse date range from request.
     */
    private function parseDateRange(Request $request)
    {
        $period = $request->input('period', '30days');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

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
                case '30days':
                default:
                    $start = Carbon::now()->subDays(29)->startOfDay();
                    $end = Carbon::now()->endOfDay();
                    break;
            }
        }

        return [$start, $end, $period];
    }

    /**
     * Helper to unify SMU and WFG areas into a single entity.
     */
    private function getNormalizedAreaName($loc)
    {
        if (!$loc) return 'Gudang Utama / Dock';
        $sLocUpper = strtoupper(trim($loc->s_loc ?? ''));
        $nameUpper = strtoupper(trim($loc->name ?? ''));
        if (in_array($loc->id, [4, 5]) || in_array($sLocUpper, ['A001', 'A002']) || str_contains($nameUpper, 'SMU') || str_contains($nameUpper, 'WFG')) {
            return 'SMU & WFG Area';
        }
        return $loc->name . ' (' . $loc->s_loc . ')';
    }

    /**
     * Build filtered base query.
     */
    private function buildBaseQuery(Request $request, Carbon $start, Carbon $end, array $with = [])
    {
        $query = VehicleTransaction::whereBetween('check_in_time', [$start, $end]);

        if (!empty($with)) {
            $query->with($with);
        }

        if ($request->filled('location_id') && $request->location_id !== 'all') {
            $locId = strtolower($request->location_id);
            if (in_array($locId, ['smu_wfg', '4,5', '4', '5'])) {
                $smuWfgIds = Location::where(function ($q) {
                    $q->whereIn('s_loc', ['A001', 'A002'])
                        ->orWhere('name', 'LIKE', '%SMU%')
                        ->orWhere('name', 'LIKE', '%WFG%')
                        ->orWhereIn('id', [4, 5]);
                })->pluck('id')->toArray();

                $query->whereIn('target_location_id', !empty($smuWfgIds) ? $smuWfgIds : [4, 5]);
            } else {
                $query->where('target_location_id', $request->location_id);
            }
        }

        if ($request->filled('jenis') && $request->jenis !== 'all') {
            $jFilter = strtolower($request->jenis);
            if ($jFilter === 'muatan') {
                $query->whereIn('jenis', ['curah', 'slipsheet', 'muatan']);
            } else {
                $query->where('jenis', $request->jenis);
            }
        }

        if ($request->filled('vendor') && $request->vendor !== 'all') {
            $query->where('vendor', $request->vendor);
        }

        if ($request->filled('item_id') && $request->item_id !== 'all') {
            $query->where('item_id', $request->item_id);
        }

        return $query;
    }

    /**
     * Aggregate data for the 6 Analytic Clusters (Supports Async Section Loading).
     */
    public function data(Request $request)
    {
        [$start, $end, $period] = $this->parseDateRange($request);
        $slaThreshold = intval($request->input('sla_limit', 450)); // default 450 minutes (7.5 jam)
        $section = $request->input('section');

        switch ($section) {
            case 'kpi_trends':
                return $this->getKpiTrends($request, $start, $end, $slaThreshold);
            case 'operations':
                return $this->getOperations($request, $start, $end, $slaThreshold);
            case 'vendor_capacity':
                return $this->getVendorCapacity($request, $start, $end, $slaThreshold);
            case 'operators':
                return $this->getOperators($request, $start, $end);
            default:
                return $this->getFullAnalyticsData($request, $start, $end, $slaThreshold, $period);
        }
    }

    /**
     * 1. Section: Top KPIs & Daily Check-in Trends (Pos 1 & Timbangan)
     */
    private function getKpiTrends(Request $request, Carbon $start, Carbon $end, int $slaThreshold)
    {
        $transactions = $this->buildBaseQuery($request, $start, $end)
            ->select([
                'id', 'check_in_time', 'checkin_pos1', 'timbangan_out_time', 'check_out_time',
                'start_sampling_time', 'finish_sampling_time', 'status', 'unloading_status', 'updated_at', 'qty_spb',
                'jenis', 'created_at'
            ])
            ->orderBy('check_in_time', 'asc')
            ->get();

        $hourlyBucketsPos1 = [];
        for ($h = 1; $h <= 24; $h++) {
            $hourlyBucketsPos1[$h] = [
                'hour' => $h,
                'label' => 'Jam ' . $h,
                'short_label' => sprintf('%02d:00', $h === 24 ? 0 : $h),
                'count' => 0,
            ];
        }

        $dailyBucketsTmb = [];
        $dailyBucketsCancelled = [];
        $cursorDate = $start->copy()->startOfDay();
        $endDate = $end->copy()->startOfDay();

        while ($cursorDate->lte($endDate)) {
            $dateKey = $cursorDate->format('Y-m-d');
            $dailyBucketsTmb[$dateKey] = [
                'date' => $dateKey,
                'label' => $cursorDate->format('d/m'),
                'full_label' => $cursorDate->format('d M Y'),
                'count' => 0,
            ];
            $dailyBucketsCancelled[$dateKey] = [
                'date' => $dateKey,
                'label' => $cursorDate->format('d/m'),
                'full_label' => $cursorDate->format('d M Y'),
                'batal_bongkar' => 0,
                'batal_muat' => 0,
                'total' => 0,
            ];
            $cursorDate->addDay();
        }

        $totalTATMinutes = 0;
        $tatCount = 0;
        $overSlaCount = 0;
        $cancelledCount = 0;
        $cancelledBongkarCount = 0;
        $cancelledMuatCount = 0;
        $totalTonAll = 0;

        foreach ($transactions as $tx) {
            $pos1 = $tx->checkin_pos1;
            $tmbIn = $tx->check_in_time;
            $tmbOut = $tx->timbangan_out_time;
            $checkOut = $tx->check_out_time;

            // Check-in Pos 1 jam tepat (Jam 1 s/d 24) dengan pembulatan terdekat
            $pos1Time = $pos1 ?: $tmbIn;
            if ($pos1Time) {
                $hRaw = (int) $pos1Time->format('G');
                $mRaw = (int) $pos1Time->format('i');
                $roundedH = ($mRaw >= 30) ? $hRaw + 1 : $hRaw;
                if ($roundedH === 0) {
                    $roundedH = 24;
                } elseif ($roundedH > 24) {
                    $roundedH = 24;
                }
                $hourlyBucketsPos1[$roundedH]['count']++;
            }

            if ($tmbIn) {
                $dKeyTmb = $tmbIn->format('Y-m-d');
                if (isset($dailyBucketsTmb[$dKeyTmb])) {
                    $dailyBucketsTmb[$dKeyTmb]['count']++;
                }
            }

            $inEffective = $pos1 ?: $tmbIn;
            $outEffective = $tmbOut ?: ($checkOut ?: ($tx->status === 'completed' ? $tx->updated_at : null));
            $samplingMin = ($tx->start_sampling_time && $tx->finish_sampling_time && $tx->finish_sampling_time->gte($tx->start_sampling_time))
                ? $tx->start_sampling_time->diffInMinutes($tx->finish_sampling_time)
                : 0;

            $truckDuration = 0;
            if ($inEffective && $outEffective && $outEffective->gte($inEffective)) {
                $grossDwell = $inEffective->diffInMinutes($outEffective);
                $truckDuration = max(1, $grossDwell - $samplingMin);
            }

            if ($truckDuration > 0) {
                $totalTATMinutes += $truckDuration;
                $tatCount++;
                if ($truckDuration > $slaThreshold) {
                    $overSlaCount++;
                }
            }

            // Truk Batal Bongkar / Muat (unloading_status = cancelled or status = cancelled)
            $isCancelled = (strtolower(trim($tx->unloading_status ?? '')) === 'cancelled' || strtolower(trim($tx->status ?? '')) === 'cancelled');
            if ($isCancelled) {
                $cancelledCount++;
                $jenisLower = strtolower(trim($tx->jenis ?? ''));
                $isBongkar = ($jenisLower === 'bongkaran' || str_contains($jenisLower, 'bongkar') || !in_array($jenisLower, ['curah', 'slipsheet', 'muatan']));
                if ($isBongkar) {
                    $cancelledBongkarCount++;
                } else {
                    $cancelledMuatCount++;
                }

                $cancelDate = $tmbIn ?: ($pos1 ?: $tx->created_at);
                if ($cancelDate) {
                    $dKeyCancel = $cancelDate->format('Y-m-d');
                    if (isset($dailyBucketsCancelled[$dKeyCancel])) {
                        if ($isBongkar) {
                            $dailyBucketsCancelled[$dKeyCancel]['batal_bongkar']++;
                        } else {
                            $dailyBucketsCancelled[$dKeyCancel]['batal_muat']++;
                        }
                        $dailyBucketsCancelled[$dKeyCancel]['total']++;
                    }
                }
            }

            $rawQty = floatval($tx->qty_spb);
            $ton = ($rawQty >= 100 ? $rawQty / 1000 : $rawQty);
            $totalTonAll += $ton;
        }

        $hourlyListPos1 = array_values($hourlyBucketsPos1);
        $totalCheckinsPos1 = array_sum(array_column($hourlyListPos1, 'count'));
        $weightedHourSum = 0;
        $maxPos1Count = 0;
        $peakHourPos1 = null;

        foreach ($hourlyListPos1 as $hb) {
            $weightedHourSum += ($hb['hour'] * $hb['count']);
            if ($hb['count'] > $maxPos1Count) {
                $maxPos1Count = $hb['count'];
                $peakHourPos1 = $hb['hour'];
            }
        }

        $avgHourPos1Decimal = $totalCheckinsPos1 > 0 ? round($weightedHourSum / $totalCheckinsPos1, 1) : 0;
        $avgHourPos1Rounded = round($avgHourPos1Decimal);
        $avgPerHourPos1 = round($totalCheckinsPos1 / 24, 1);

        $dailyListTmb = array_values($dailyBucketsTmb);
        $totalDailyDays = max(1, count($dailyListTmb));
        $totalCheckinsTmb = array_sum(array_column($dailyListTmb, 'count'));
        $avgDailyCheckinTmb = round($totalCheckinsTmb / $totalDailyDays, 1);

        $dailyListCancelled = array_values($dailyBucketsCancelled);

        $totalVehicles = $transactions->count();
        $avgTatMin = $tatCount > 0 ? round($totalTATMinutes / $tatCount) : 0;
        $overSlaRate = $tatCount > 0 ? round(($overSlaCount / $tatCount) * 100, 1) : 0;
        $cancelledRate = $totalVehicles > 0 ? round(($cancelledCount / $totalVehicles) * 100, 1) : 0;
        $healthScore = max(0, min(100, round(100 - $overSlaRate)));

        return response()->json([
            'success' => true,
            'section' => 'kpi_trends',
            'kpi' => [
                'total_vehicles' => $totalVehicles,
                'avg_tat_min' => $avgTatMin,
                'avg_tat_formatted' => $this->formatDuration($avgTatMin),
                'over_sla_count' => $overSlaCount,
                'over_sla_rate' => $overSlaRate,
                'cancelled_count' => $cancelledCount,
                'cancelled_bongkar_count' => $cancelledBongkarCount,
                'cancelled_muat_count' => $cancelledMuatCount,
                'cancelled_rate' => $cancelledRate,
                'health_score' => $healthScore,
                'total_tonnage' => round($totalTonAll, 2),
                'avg_daily_checkin' => $avgDailyCheckinTmb,
                'avg_daily_checkin_timbangan' => $avgDailyCheckinTmb,
                'avg_daily_checkin_pos1' => $avgHourPos1Rounded > 0 ? "Jam $avgHourPos1Rounded" : '-',
                'avg_hour_pos1' => $avgHourPos1Rounded > 0 ? "Jam $avgHourPos1Rounded" : '-',
                'peak_hour_pos1' => $peakHourPos1 ? "Jam $peakHourPos1" : '-',
                'total_checkins' => $totalCheckinsTmb,
                'total_checkins_timbangan' => $totalCheckinsTmb,
                'total_checkins_pos1' => $totalCheckinsPos1,
            ],
            'daily_checkin_pos1' => [
                'mode' => 'hourly',
                'series' => $hourlyListPos1,
                'total_checkins' => $totalCheckinsPos1,
                'avg_hour' => $avgHourPos1Rounded > 0 ? "Jam $avgHourPos1Rounded" : '-',
                'avg_hour_decimal' => $avgHourPos1Decimal,
                'peak_hour' => $peakHourPos1 ? "Jam $peakHourPos1" : '-',
                'peak_count' => $maxPos1Count,
                'avg_per_hour' => $avgPerHourPos1,
                'avg_per_day' => $avgHourPos1Rounded > 0 ? "Jam $avgHourPos1Rounded" : '-',
                'total_days' => 24,
            ],
            'daily_checkin_timbangan' => [
                'series' => $dailyListTmb,
                'avg_per_day' => $avgDailyCheckinTmb,
                'total_days' => $totalDailyDays,
                'total_checkins' => $totalCheckinsTmb,
            ],
            'daily_checkin' => [
                'series' => $dailyListTmb,
                'avg_per_day' => $avgDailyCheckinTmb,
                'total_days' => $totalDailyDays,
                'total_checkins' => $totalCheckinsTmb,
            ],
            'daily_cancelled' => [
                'series' => $dailyListCancelled,
                'total_cancelled' => $cancelledCount,
                'total_batal_bongkar' => $cancelledBongkarCount,
                'total_batal_muat' => $cancelledMuatCount,
                'avg_per_day' => round($cancelledCount / $totalDailyDays, 1),
                'total_days' => $totalDailyDays,
            ],
        ]);
    }

    /**
     * 2. Section: Operations (Stage Durations, Real Loading Bay Time, Frequent Trucks)
     */
    private function getOperations(Request $request, Carbon $start, Carbon $end, int $slaThreshold)
    {
        $transactions = $this->buildBaseQuery($request, $start, $end, ['vehicle', 'targetLocation'])
            ->orderBy('check_in_time', 'asc')
            ->get();

        $stageDefs = [
            'pos1_ke_timbangan' => ['name' => 'POS 1 KE TIMBANGAN', 'desc' => 'Pos 1 Security ke Check-in Timbangan Masuk', 'durations' => []],
            'antrian_dock' => ['name' => 'ANTRIAN MENUJU DOCK', 'desc' => 'Check-in Timbangan ke Ambil Antrian / Menuju Dock', 'durations' => []],
            'bongkar_muat' => ['name' => 'BONGKAR / MUAT', 'desc' => 'Mulai Bongkar/Muat hingga Selesai di Dock', 'durations' => []],
            'checkout_timbangan' => ['name' => 'CHECK-OUT TIMBANGAN', 'desc' => 'Timbangan Keluar ke Check-Out Timbangan', 'durations' => []],
        ];

        $truckVisits = [];
        $loadingBayTimes = [];
        $allLoadingMinutes = [];

        foreach ($transactions as $tx) {
            $pos1 = $tx->checkin_pos1;
            $tmbIn = $tx->check_in_time;
            $queueTime = $tx->queue_taken_time;
            $startLoad = $tx->start_loading_time;
            $finishLoad = $tx->finish_loading_time;
            $tmbOut = $tx->timbangan_out_time;
            $checkOut = $tx->check_out_time;

            if ($pos1 && $tmbIn && $tmbIn->gte($pos1)) {
                $stageDefs['pos1_ke_timbangan']['durations'][] = $pos1->diffInMinutes($tmbIn);
            }

            $nextAfterTmb = $queueTime ?: $startLoad;
            if ($tmbIn && $nextAfterTmb && $nextAfterTmb->gte($tmbIn)) {
                $stageDefs['antrian_dock']['durations'][] = $tmbIn->diffInMinutes($nextAfterTmb);
            }

            if ($startLoad && $finishLoad && $finishLoad->gte($startLoad)) {
                $stageDefs['bongkar_muat']['durations'][] = $startLoad->diffInMinutes($finishLoad);
            }

            if ($tmbOut && $checkOut && $checkOut->gte($tmbOut)) {
                $stageDefs['checkout_timbangan']['durations'][] = $tmbOut->diffInMinutes($checkOut);
            }

            // Real Loading Time
            if ($startLoad && $finishLoad && $finishLoad->gte($startLoad)) {
                $loadMin = $startLoad->diffInMinutes($finishLoad);
                $allLoadingMinutes[] = $loadMin;

                $dockName = $this->getNormalizedAreaName($tx->targetLocation);

                if (!isset($loadingBayTimes[$dockName])) {
                    $loadingBayTimes[$dockName] = [
                        'dock_name' => $dockName,
                        'total_min' => 0,
                        'sample_count' => 0,
                        'min_min' => $loadMin,
                        'max_min' => $loadMin,
                    ];
                }
                $loadingBayTimes[$dockName]['total_min'] += $loadMin;
                $loadingBayTimes[$dockName]['sample_count']++;
                if ($loadMin < $loadingBayTimes[$dockName]['min_min']) $loadingBayTimes[$dockName]['min_min'] = $loadMin;
                if ($loadMin > $loadingBayTimes[$dockName]['max_min']) $loadingBayTimes[$dockName]['max_min'] = $loadMin;
            }

            // Truck Duration for Frequent Trucks
            $inEffective = $pos1 ?: $tmbIn;
            $outEffective = $tmbOut ?: ($checkOut ?: ($tx->status === 'completed' ? $tx->updated_at : null));
            $samplingMin = ($tx->start_sampling_time && $tx->finish_sampling_time && $tx->finish_sampling_time->gte($tx->start_sampling_time))
                ? $tx->start_sampling_time->diffInMinutes($tx->finish_sampling_time)
                : 0;

            $truckDuration = 0;
            if ($inEffective && $outEffective && $outEffective->gte($inEffective)) {
                $grossDwell = $inEffective->diffInMinutes($outEffective);
                $truckDuration = max(1, $grossDwell - $samplingMin);
            }

            $noPol = $tx->vehicle ? strtoupper(trim($tx->vehicle->no_pol)) : null;
            if (!$noPol || $noPol === '-') {
                $noPol = $tx->nama_driver ? ('DRIVER: ' . strtoupper($tx->nama_driver)) : 'PLAT TIDAK TERCATAT';
            }
            $vVendor = strtoupper(trim($tx->vendor ?: 'LAIN-LAIN / UNKNOWN'));
            $rawQty = floatval($tx->qty_spb);
            $tonnage = ($rawQty >= 100 ? $rawQty / 1000 : $rawQty);

            if (!isset($truckVisits[$noPol])) {
                $truckVisits[$noPol] = [
                    'no_pol' => $noPol,
                    'vendor' => $vVendor,
                    'trips' => 0,
                    'total_tat_min' => 0,
                    'tat_samples' => 0,
                    'total_tonnage' => 0,
                ];
            }
            $truckVisits[$noPol]['trips']++;
            $truckVisits[$noPol]['total_tonnage'] += $tonnage;
            if ($truckDuration > 0) {
                $truckVisits[$noPol]['total_tat_min'] += $truckDuration;
                $truckVisits[$noPol]['tat_samples']++;
            }
        }

        $stageResults = [];
        $primaryBottleneckStage = null;
        $maxStageAvg = 0;

        foreach ($stageDefs as $k => $s) {
            $vals = $s['durations'];
            $cnt = count($vals);
            $avg = $cnt > 0 ? round(array_sum($vals) / $cnt) : 0;
            $min = $cnt > 0 ? min($vals) : 0;
            $max = $cnt > 0 ? max($vals) : 0;

            if ($avg > $maxStageAvg) {
                $maxStageAvg = $avg;
                $primaryBottleneckStage = $s['name'];
            }

            $stageResults[$k] = [
                'name' => $s['name'],
                'desc' => $s['desc'],
                'avg_min' => $avg,
                'min_min' => $min,
                'max_min' => $max,
                'avg_formatted' => $this->formatDuration($avg),
                'sample_count' => $cnt,
            ];
        }

        uasort($truckVisits, fn($a, $b) => $b['trips'] <=> $a['trips']);
        $topFrequentTrucks = array_values(array_slice(array_map(function ($t) {
            $avgTat = $t['tat_samples'] > 0 ? round($t['total_tat_min'] / $t['tat_samples']) : 0;
            return [
                'no_pol' => $t['no_pol'],
                'vendor' => $t['vendor'],
                'trips' => $t['trips'],
                'avg_tat_min' => $avgTat,
                'avg_tat_formatted' => $avgTat > 0 ? $this->formatDuration($avgTat) : '-',
                'total_tonnage' => round($t['total_tonnage'], 2),
            ];
        }, $truckVisits), 0, 8));

        uasort($loadingBayTimes, fn($a, $b) => $b['sample_count'] <=> $a['sample_count']);
        $loadingBayList = array_values(array_map(function ($b) {
            $avg = $b['sample_count'] > 0 ? round($b['total_min'] / $b['sample_count']) : 0;
            return [
                'dock_name' => $b['dock_name'],
                'sample_count' => $b['sample_count'],
                'avg_duration_min' => $avg,
                'avg_duration_formatted' => $this->formatDuration($avg),
                'min_formatted' => $this->formatDuration($b['min_min']),
                'max_formatted' => $this->formatDuration($b['max_min']),
            ];
        }, $loadingBayTimes));

        $totalLoadingSamples = count($allLoadingMinutes);
        $overallAvgLoadingMin = $totalLoadingSamples > 0 ? round(array_sum($allLoadingMinutes) / $totalLoadingSamples) : 0;

        return response()->json([
            'success' => true,
            'section' => 'operations',
            'stages' => $stageResults,
            'primary_bottleneck' => $primaryBottleneckStage ?: 'N/A',
            'max_stage_avg_formatted' => $this->formatDuration($maxStageAvg),
            'loading_time' => [
                'bay_list' => $loadingBayList,
                'total_samples' => $totalLoadingSamples,
                'overall_avg_min' => $overallAvgLoadingMin,
                'overall_avg_formatted' => $this->formatDuration($overallAvgLoadingMin),
            ],
            'frequent_trucks' => $topFrequentTrucks,
            'cluster_1' => [
                'stages' => $stageResults,
                'primary_bottleneck' => $primaryBottleneckStage ?: 'N/A',
                'max_stage_avg_formatted' => $this->formatDuration($maxStageAvg),
            ],
        ]);
    }

    /**
     * 3. Section: Vendor & Yard Capacity (Bay Throughput, Fleet Types, Vendor Over Duration, Weekly Duration)
     */
    private function getVendorCapacity(Request $request, Carbon $start, Carbon $end, int $slaThreshold)
    {
        $transactions = $this->buildBaseQuery($request, $start, $end, ['targetLocation'])
            ->orderBy('check_in_time', 'asc')
            ->get();

        $bayThroughput = [];
        $totalTonAll = 0;
        $totalLoadingHoursAll = 0;

        $vendorOverDuration = [];
        $fleetTypes = [
            'Bongkaran' => ['jenis' => 'Bongkaran', 'count' => 0, 'total_min' => 0],
            'Muatan' => ['jenis' => 'Muatan', 'count' => 0, 'total_min' => 0],
            'Retur' => ['jenis' => 'Retur', 'count' => 0, 'total_min' => 0],
        ];

        $dailyFleetBuckets = [];
        $cursorDate = $start->copy()->startOfDay();
        $endDate = $end->copy()->startOfDay();

        while ($cursorDate->lte($endDate)) {
            $dateKey = $cursorDate->format('Y-m-d');
            $dailyFleetBuckets[$dateKey] = [
                'date' => $dateKey,
                'label' => $cursorDate->format('d/m'),
                'full_label' => $cursorDate->format('d M Y'),
                'bongkaran' => 0,
                'muatan' => 0,
                'retur' => 0,
                'total' => 0,
            ];
            $cursorDate->addDay();
        }

        $weeklyBuckets = [];
        $weekCursor = $start->copy()->startOfWeek();
        $weekEnd = $end->copy()->endOfWeek();
        $indMonths = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        while ($weekCursor->lte($weekEnd)) {
            $wKey = $weekCursor->format('Y-\WW');
            $wStart = $weekCursor->copy();
            $wEndDay = $weekCursor->copy()->endOfWeek();

            $sM = $indMonths[$wStart->month] ?? $wStart->format('M');
            $eM = $indMonths[$wEndDay->month] ?? $wEndDay->format('M');
            $sYr = $wStart->format('y');
            $eYr = $wEndDay->format('y');

            if ($wStart->month === $wEndDay->month) {
                $weekLabel = $wStart->format('j') . '-' . $wEndDay->format('j') . ' ' . $sM . ' ' . $sYr;
            } else {
                $weekLabel = $wStart->format('j') . ' ' . $sM . ' - ' . $wEndDay->format('j') . ' ' . $eM . ' ' . $eYr;
            }

            $weeklyBuckets[$wKey] = [
                'week_key' => $wKey,
                'label' => $weekLabel,
                'week_label' => $weekLabel,
                'short_label' => $wStart->format('j') . '-' . $wEndDay->format('j') . '/' . $wEndDay->format('m'),
                'durations' => [],
                'total_trucks' => 0,
                'over_duration_count' => 0,
            ];
            $weekCursor->addWeek();
        }

        foreach ($transactions as $tx) {
            $pos1 = $tx->checkin_pos1;
            $tmbIn = $tx->check_in_time;
            $tmbOut = $tx->timbangan_out_time;
            $checkOut = $tx->check_out_time;

            $inEffective = $pos1 ?: $tmbIn;
            $outEffective = $tmbOut ?: ($checkOut ?: ($tx->status === 'completed' ? $tx->updated_at : null));
            $samplingMin = ($tx->start_sampling_time && $tx->finish_sampling_time && $tx->finish_sampling_time->gte($tx->start_sampling_time))
                ? $tx->start_sampling_time->diffInMinutes($tx->finish_sampling_time)
                : 0;

            $truckDuration = 0;
            if ($inEffective && $outEffective && $outEffective->gte($inEffective)) {
                $grossDwell = $inEffective->diffInMinutes($outEffective);
                $truckDuration = max(1, $grossDwell - $samplingMin);
            }

            // Bay Throughput
            $locKey = $this->getNormalizedAreaName($tx->targetLocation);

            if (!isset($bayThroughput[$locKey])) {
                $bayThroughput[$locKey] = [
                    'location_name' => $locKey,
                    'total_tonnage' => 0,
                    'trucks_handled' => 0,
                    'total_load_minutes' => 0,
                    'load_samples' => 0,
                ];
            }

            $rawQ = floatval($tx->qty_spb);
            $ton = ($rawQ >= 100 ? $rawQ / 1000 : $rawQ);
            $bayThroughput[$locKey]['total_tonnage'] += $ton;
            $totalTonAll += $ton;

            if ($tx->check_out_time || $tx->status === 'completed') {
                $bayThroughput[$locKey]['trucks_handled']++;
            }

            if ($tx->start_loading_time && $tx->finish_loading_time && $tx->finish_loading_time->gte($tx->start_loading_time)) {
                $loadMin = $tx->start_loading_time->diffInMinutes($tx->finish_loading_time);
                $bayThroughput[$locKey]['total_load_minutes'] += $loadMin;
                $bayThroughput[$locKey]['load_samples']++;
                $totalLoadingHoursAll += ($loadMin / 60);
            }

            // Vendor Over Duration
            $vVendor = strtoupper(trim($tx->vendor ?: 'LAIN-LAIN / UNKNOWN'));
            if (!isset($vendorOverDuration[$vVendor])) {
                $vendorOverDuration[$vVendor] = [
                    'vendor' => $vVendor,
                    'total_trips' => 0,
                    'over_count' => 0,
                    'total_duration_min' => 0,
                    'total_over_duration_min' => 0,
                ];
            }
            $vendorOverDuration[$vVendor]['total_trips']++;
            $vendorOverDuration[$vVendor]['total_duration_min'] += $truckDuration;
            if ($truckDuration > $slaThreshold) {
                $vendorOverDuration[$vVendor]['over_count']++;
                $vendorOverDuration[$vVendor]['total_over_duration_min'] += $truckDuration;
            }

            // Fleet Types & Daily Trend
            $rawJenis = strtolower(trim($tx->jenis ?: 'bongkaran'));
            if (in_array($rawJenis, ['curah', 'slipsheet', 'muatan'])) {
                $jKey = 'Muatan';
                $trendKey = 'muatan';
            } elseif ($rawJenis === 'retur') {
                $jKey = 'Retur';
                $trendKey = 'retur';
            } else {
                $jKey = 'Bongkaran';
                $trendKey = 'bongkaran';
            }
            $fleetTypes[$jKey]['count']++;
            $fleetTypes[$jKey]['total_min'] += $truckDuration;

            $fleetDate = $pos1 ?: ($tmbIn ?: $tx->created_at);
            if ($fleetDate) {
                $dKeyFleet = $fleetDate->format('Y-m-d');
                if (isset($dailyFleetBuckets[$dKeyFleet])) {
                    $dailyFleetBuckets[$dKeyFleet][$trendKey]++;
                    $dailyFleetBuckets[$dKeyFleet]['total']++;
                }
            }

            // Weekly Duration
            if ($inEffective) {
                $wKey = $inEffective->format('Y-\WW');
                if (isset($weeklyBuckets[$wKey])) {
                    $weeklyBuckets[$wKey]['total_trucks']++;
                    if ($truckDuration > 0) {
                        $weeklyBuckets[$wKey]['durations'][] = $truckDuration;
                    }
                    if ($truckDuration > $slaThreshold) {
                        $weeklyBuckets[$wKey]['over_duration_count']++;
                    }
                }
            }
        }

        $overallProductivityTonPerHour = ($totalLoadingHoursAll > 0) ? round($totalTonAll / $totalLoadingHoursAll, 1) : 0;

        uasort($bayThroughput, fn($a, $b) => $b['total_tonnage'] <=> $a['total_tonnage']);
        $bayList = array_values(array_map(function ($b) {
            $avgLoad = $b['load_samples'] > 0 ? round($b['total_load_minutes'] / $b['load_samples']) : 0;
            return [
                'location_name' => $b['location_name'],
                'total_tonnage' => round($b['total_tonnage'], 2),
                'trucks_handled' => $b['trucks_handled'],
                'avg_load_formatted' => $this->formatDuration($avgLoad),
            ];
        }, $bayThroughput));

        uasort($vendorOverDuration, function ($a, $b) {
            if ($b['over_count'] !== $a['over_count']) {
                return $b['over_count'] <=> $a['over_count'];
            }
            return $b['total_trips'] <=> $a['total_trips'];
        });

        $vendorOverDurationList = array_values(array_map(function ($v) {
            $avgAll = $v['total_trips'] > 0 ? round($v['total_duration_min'] / $v['total_trips']) : 0;
            $avgOver = $v['over_count'] > 0 ? round($v['total_over_duration_min'] / $v['over_count']) : 0;
            $overRate = $v['total_trips'] > 0 ? round(($v['over_count'] / $v['total_trips']) * 100, 1) : 0;

            $status = 'Aman (≤ 7.5j)';
            if ($overRate >= 50) {
                $status = 'Kritis (> 50%)';
            } elseif ($v['over_count'] > 0) {
                $status = 'Waspada';
            }

            return [
                'vendor' => $v['vendor'],
                'total_trips' => $v['total_trips'],
                'over_count' => $v['over_count'],
                'over_rate' => $overRate,
                'avg_duration_formatted' => $this->formatDuration($avgAll),
                'avg_over_duration_formatted' => $avgOver > 0 ? $this->formatDuration($avgOver) : '-',
                'status' => $status,
            ];
        }, $vendorOverDuration));

        $fleetDistribution = array_values(array_map(function ($f) {
            $avg = $f['count'] > 0 ? round($f['total_min'] / $f['count']) : 0;
            return [
                'jenis' => $f['jenis'],
                'count' => $f['count'],
                'avg_duration_formatted' => $this->formatDuration($avg),
            ];
        }, $fleetTypes));

        $dailyFleetList = array_values($dailyFleetBuckets);
        $totalDaysFleet = max(1, count($dailyFleetList));
        $totalBongkaran = array_sum(array_column($dailyFleetList, 'bongkaran'));
        $totalMuatan = array_sum(array_column($dailyFleetList, 'muatan'));
        $totalRetur = array_sum(array_column($dailyFleetList, 'retur'));
        $totalFleetAll = array_sum(array_column($dailyFleetList, 'total'));

        $fleetTrend = [
            'series' => $dailyFleetList,
            'total_bongkaran' => $totalBongkaran,
            'total_muatan' => $totalMuatan,
            'total_retur' => $totalRetur,
            'total_all' => $totalFleetAll,
            'avg_per_day' => round($totalFleetAll / $totalDaysFleet, 1),
            'total_days' => $totalDaysFleet,
        ];

        $weeklyDurationList = array_values(array_filter(array_map(function ($w) {
            $cnt = count($w['durations']);
            $avg = $cnt > 0 ? round(array_sum($w['durations']) / $cnt) : 0;
            $overRate = $w['total_trucks'] > 0 ? round(($w['over_duration_count'] / $w['total_trucks']) * 100, 1) : 0;
            return [
                'week_key' => $w['week_key'],
                'label' => $w['label'],
                'week_label' => $w['label'],
                'short_label' => $w['short_label'],
                'total_trucks' => $w['total_trucks'],
                'avg_duration_min' => $avg,
                'avg_duration_hours' => round($avg / 60, 1),
                'avg_duration_formatted' => $this->formatDuration($avg),
                'over_duration_count' => $w['over_duration_count'],
                'over_sla_count' => $w['over_duration_count'],
                'over_duration_rate' => $overRate,
                'over_sla_rate' => $overRate,
            ];
        }, $weeklyBuckets), fn($w) => $w['total_trucks'] > 0));

        return response()->json([
            'success' => true,
            'section' => 'vendor_capacity',
            'capacity_throughput' => [
                'bay_throughput' => $bayList,
                'productivity_ton_per_hour' => $overallProductivityTonPerHour,
                'total_tonnage' => round($totalTonAll, 2),
            ],
            'vendor_over_duration' => $vendorOverDurationList,
            'vendor_fleet' => [
                'vendor_tat' => $vendorOverDurationList,
                'fleet_distribution' => $fleetDistribution,
                'fleet_trend' => $fleetTrend,
            ],
            'fleet_trend' => $fleetTrend,
            'weekly_duration' => $weeklyDurationList,
        ]);
    }

    /**
     * 4. Section: Operators Productivity Audit
     */
    private function getOperators(Request $request, Carbon $start, Carbon $end)
    {
        $transactions = $this->buildBaseQuery($request, $start, $end, [
            'creator', 'queueTakenBy', 'startLoadingBy', 'finishLoadingBy', 'timbanganOutBy', 'checkOutBy'
        ])->get();

        $operatorWork = [];

        $recordOperatorAction = function ($user, $pos, $durationMin = null) use (&$operatorWork) {
            if (!$user) return;
            $uId = $user->id;
            $namaLengkap = trim($user->nama_lengkap ?? '');
            $username = trim($user->username ?? '');
            $displayName = $namaLengkap ?: ($username ?: ('Petugas #' . $uId));
            $key = $uId . '_' . $pos;

            if (!isset($operatorWork[$key])) {
                $operatorWork[$key] = [
                    'user_id' => $uId,
                    'user_name' => $displayName,
                    'nama_lengkap' => $namaLengkap,
                    'username' => $username,
                    'pos' => $pos,
                    'total_tickets' => 0,
                    'total_min' => 0,
                    'sample_count' => 0,
                ];
            }

            $operatorWork[$key]['total_tickets']++;
            if ($durationMin !== null && $durationMin > 0) {
                $operatorWork[$key]['total_min'] += $durationMin;
                $operatorWork[$key]['sample_count']++;
            }
        };

        foreach ($transactions as $tx) {
            if ($tx->creator) {
                $recordOperatorAction($tx->creator, 'Check-in Timbangan');
            }
            if ($tx->queueTakenBy) {
                $recordOperatorAction($tx->queueTakenBy, 'Pos Antrian');
            }
            if ($tx->finishLoadingBy || $tx->startLoadingBy) {
                $dur = ($tx->start_loading_time && $tx->finish_loading_time) ? $tx->start_loading_time->diffInMinutes($tx->finish_loading_time) : null;
                $recordOperatorAction($tx->finishLoadingBy ?: $tx->startLoadingBy, 'Loading Bay / Gudang', $dur);
            }
            if ($tx->timbanganOutBy) {
                $recordOperatorAction($tx->timbanganOutBy, 'Petugas Timbangan');
            }
            if ($tx->checkOutBy) {
                $recordOperatorAction($tx->checkOutBy, 'Pos Security Keluar');
            }
        }

        $txIds = $transactions->pluck('id')->toArray();
        $trackingLogs = !empty($txIds)
            ? VehicleTracking::with(['creator', 'location'])
                ->whereIn('vehicle_transaction_id', $txIds)
                ->whereNotNull('created_by')
                ->get()
            : collect();

        foreach ($trackingLogs as $log) {
            if ($log->creator) {
                $pos = $log->location ? $log->location->name : 'Operasional Lapangan';
                if (stripos($pos, 'qc') !== false || stripos($pos, 'sample') !== false || stripos($pos, 'sampling') !== false) {
                    continue;
                }
                $durMin = $log->duration_seconds ? round(abs($log->duration_seconds) / 60) : null;
                $recordOperatorAction($log->creator, $pos, $durMin);
            }
        }

        uasort($operatorWork, fn($a, $b) => $b['total_tickets'] <=> $a['total_tickets']);

        $operatorList = array_values(array_slice(array_map(function ($op) {
            $avg = $op['sample_count'] > 0 ? round($op['total_min'] / $op['sample_count']) : 0;
            return [
                'user_name' => $op['user_name'],
                'nama_lengkap' => $op['nama_lengkap'],
                'username' => $op['username'],
                'pos' => $op['pos'],
                'total_tickets' => $op['total_tickets'],
                'avg_duration_formatted' => $avg > 0 ? $this->formatDuration($avg) : 'Instant / Log',
            ];
        }, $operatorWork), 0, 15));

        return response()->json([
            'success' => true,
            'section' => 'operators',
            'operators' => [
                'operator_list' => $operatorList,
            ],
            'operator_list' => $operatorList,
            'cluster_6' => [
                'operators' => $operatorList,
            ],
        ]);
    }

    /**
     * Fallback full dataset generator (Backward compatibility).
     */
    private function getFullAnalyticsData(Request $request, Carbon $start, Carbon $end, int $slaThreshold, string $period)
    {
        $transactions = $this->buildBaseQuery($request, $start, $end, [
            'vehicle',
            'item',
            'targetLocation',
            'currentLocation',
            'creator',
            'queueTakenBy',
            'startLoadingBy',
            'finishLoadingBy',
            'timbanganOutBy',
            'checkOutBy',
        ])->orderBy('check_in_time', 'asc')->get();

        $formatDur = fn($min) => $this->formatDuration($min);

        // =========================================================================
        // 1. ANALISA WAKTU & BOTTLENECK (STAGE DURATIONS - TANPA SAMPLING QC)
        // =========================================================================
        $stageDefs = [
            'pos1_ke_timbangan' => [
                'name' => 'POS 1 KE TIMBANGAN',
                'desc' => 'Pos 1 Security ke Check-in Timbangan Masuk',
                'durations' => []
            ],
            'antrian_dock' => [
                'name' => 'ANTRIAN MENUJU DOCK',
                'desc' => 'Check-in Timbangan ke Ambil Antrian / Menuju Dock',
                'durations' => []
            ],
            'bongkar_muat' => [
                'name' => 'BONGKAR / MUAT',
                'desc' => 'Mulai Bongkar/Muat hingga Selesai di Dock',
                'durations' => []
            ],
            'checkout_timbangan' => [
                'name' => 'CHECK-OUT TIMBANGAN',
                'desc' => 'Timbangan Keluar ke Check-Out Timbangan',
                'durations' => []
            ],
        ];

        // Hourly Check-ins Series for POS 1 (Jam 1 s/d 24)
        $hourlyBucketsPos1 = [];
        for ($h = 1; $h <= 24; $h++) {
            $hourlyBucketsPos1[$h] = [
                'hour' => $h,
                'label' => 'Jam ' . $h,
                'short_label' => sprintf('%02d:00', $h === 24 ? 0 : $h),
                'count' => 0,
            ];
        }

        $dailyBucketsTmb = [];
        $dailyBucketsCancelled = [];
        $cursorDate = $start->copy()->startOfDay();
        $endDate = $end->copy()->startOfDay();

        while ($cursorDate->lte($endDate)) {
            $dateKey = $cursorDate->format('Y-m-d');
            $bTemplate = [
                'date' => $dateKey,
                'label' => $cursorDate->format('d/m'),
                'full_label' => $cursorDate->format('d M Y'),
                'count' => 0,
            ];
            $dailyBucketsPos1[$dateKey] = $bTemplate;
            $dailyBucketsTmb[$dateKey] = $bTemplate;
            $dailyBucketsCancelled[$dateKey] = [
                'date' => $dateKey,
                'label' => $cursorDate->format('d/m'),
                'full_label' => $cursorDate->format('d M Y'),
                'batal_bongkar' => 0,
                'batal_muat' => 0,
                'total' => 0,
            ];
            $cursorDate->addDay();
        }

        // Frequent Trucks Map
        $truckVisits = [];

        // Real Loading / Unloading Times
        $loadingBayTimes = [];
        $allLoadingMinutes = [];

        // Vendor Over Duration (> 7.5 jam) Map
        $vendorOverDuration = [];

        // Fleet Types Distribution (Bongkaran, Muatan, Retur)
        $fleetTypes = [
            'Bongkaran' => ['jenis' => 'Bongkaran', 'count' => 0, 'total_min' => 0],
            'Muatan' => ['jenis' => 'Muatan', 'count' => 0, 'total_min' => 0],
            'Retur' => ['jenis' => 'Retur', 'count' => 0, 'total_min' => 0],
        ];

        $dailyFleetBuckets = [];
        $cursorDate = $start->copy()->startOfDay();
        $endDate = $end->copy()->startOfDay();

        while ($cursorDate->lte($endDate)) {
            $dateKey = $cursorDate->format('Y-m-d');
            $dailyFleetBuckets[$dateKey] = [
                'date' => $dateKey,
                'label' => $cursorDate->format('d/m'),
                'full_label' => $cursorDate->format('d M Y'),
                'bongkaran' => 0,
                'muatan' => 0,
                'retur' => 0,
                'total' => 0,
            ];
            $cursorDate->addDay();
        }

        // Weekly Truck Duration Buckets
        $weeklyBuckets = [];
        $weekCursor = $start->copy()->startOfWeek();
        $weekEnd = $end->copy()->endOfWeek();
        $indMonths = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        while ($weekCursor->lte($weekEnd)) {
            $wKey = $weekCursor->format('Y-\WW');
            $wStart = $weekCursor->copy();
            $wEndDay = $weekCursor->copy()->endOfWeek();

            $sM = $indMonths[$wStart->month] ?? $wStart->format('M');
            $eM = $indMonths[$wEndDay->month] ?? $wEndDay->format('M');
            $sYr = $wStart->format('y');
            $eYr = $wEndDay->format('y');

            if ($wStart->month === $wEndDay->month) {
                $weekLabel = $wStart->format('j') . '-' . $wEndDay->format('j') . ' ' . $sM . ' ' . $sYr;
            } else {
                $weekLabel = $wStart->format('j') . ' ' . $sM . ' - ' . $wEndDay->format('j') . ' ' . $eM . ' ' . $eYr;
            }

            $weeklyBuckets[$wKey] = [
                'week_key' => $wKey,
                'label' => $weekLabel,
                'week_label' => $weekLabel,
                'short_label' => $wStart->format('j') . '-' . $wEndDay->format('j') . '/' . $wEndDay->format('m'),
                'durations' => [],
                'total_trucks' => 0,
                'over_duration_count' => 0,
            ];
            $weekCursor->addWeek();
        }

        $totalTATMinutes = 0;
        $tatCount = 0;
        $overSlaCount = 0;
        $cancelledCount = 0;
        $cancelledBongkarCount = 0;
        $cancelledMuatCount = 0;

        foreach ($transactions as $tx) {
            $pos1 = $tx->checkin_pos1;
            $tmbIn = $tx->check_in_time;
            $queueTime = $tx->queue_taken_time;
            $startLoad = $tx->start_loading_time;
            $finishLoad = $tx->finish_loading_time;
            $tmbOut = $tx->timbangan_out_time;
            $checkOut = $tx->check_out_time;

            // 1. Pos 1 ke Timbangan Masuk: checkin_pos1 -> check_in_time
            if ($pos1 && $tmbIn && $tmbIn->gte($pos1)) {
                $stageDefs['pos1_ke_timbangan']['durations'][] = $pos1->diffInMinutes($tmbIn);
            }

            // 2. Antrian Menuju Dock: check_in_time -> queue_taken_time (fallback ke start_loading, tanpa sampling)
            $nextAfterTmb = $queueTime ?: $startLoad;
            if ($tmbIn && $nextAfterTmb && $nextAfterTmb->gte($tmbIn)) {
                $stageDefs['antrian_dock']['durations'][] = $tmbIn->diffInMinutes($nextAfterTmb);
            }

            // 3. Bongkar/Muat: start_loading_time -> finish_loading_time
            if ($startLoad && $finishLoad && $finishLoad->gte($startLoad)) {
                $stageDefs['bongkar_muat']['durations'][] = $startLoad->diffInMinutes($finishLoad);
            }

            // 4. Check-Out Timbangan: timbangan_out_time -> check_out_time
            if ($tmbOut && $checkOut && $checkOut->gte($tmbOut)) {
                $stageDefs['checkout_timbangan']['durations'][] = $tmbOut->diffInMinutes($checkOut);
            }

            // Check-in Pos 1 jam tepat (Jam 1 s/d 24) dengan pembulatan terdekat
            $pos1Time = $pos1 ?: $tmbIn;
            if ($pos1Time) {
                $hRaw = (int) $pos1Time->format('G');
                $mRaw = (int) $pos1Time->format('i');
                $roundedH = ($mRaw >= 30) ? $hRaw + 1 : $hRaw;
                if ($roundedH === 0) {
                    $roundedH = 24;
                } elseif ($roundedH > 24) {
                    $roundedH = 24;
                }
                $hourlyBucketsPos1[$roundedH]['count']++;
            }

            // Daily Check-in aggregation for Timbangan
            if ($tmbIn) {
                $dKeyTmb = $tmbIn->format('Y-m-d');
                if (isset($dailyBucketsTmb[$dKeyTmb])) {
                    $dailyBucketsTmb[$dKeyTmb]['count']++;
                }
            }

            // Truck Duration: checkin pos1 sampai timbangan out, tanpa waktu sampling
            $inEffective = $pos1 ?: $tmbIn;
            $outEffective = $tmbOut ?: ($checkOut ?: ($tx->status === 'completed' ? $tx->updated_at : null));
            $samplingMin = ($tx->start_sampling_time && $tx->finish_sampling_time && $tx->finish_sampling_time->gte($tx->start_sampling_time))
                ? $tx->start_sampling_time->diffInMinutes($tx->finish_sampling_time)
                : 0;

            $truckDuration = 0;
            if ($inEffective && $outEffective && $outEffective->gte($inEffective)) {
                $grossDwell = $inEffective->diffInMinutes($outEffective);
                $truckDuration = max(1, $grossDwell - $samplingMin);
            }

            if ($truckDuration > 0) {
                $totalTATMinutes += $truckDuration;
                $tatCount++;
                if ($truckDuration > $slaThreshold) {
                    $overSlaCount++;
                }
            }

            // Truk Batal Bongkar / Muat (unloading_status = cancelled or status = cancelled)
            $isCancelled = (strtolower(trim($tx->unloading_status ?? '')) === 'cancelled' || strtolower(trim($tx->status ?? '')) === 'cancelled');
            if ($isCancelled) {
                $cancelledCount++;
                $jenisLower = strtolower(trim($tx->jenis ?? ''));
                $isBongkar = ($jenisLower === 'bongkaran' || str_contains($jenisLower, 'bongkar') || !in_array($jenisLower, ['curah', 'slipsheet', 'muatan']));
                if ($isBongkar) {
                    $cancelledBongkarCount++;
                } else {
                    $cancelledMuatCount++;
                }

                $cancelDate = $tmbIn ?: ($pos1 ?: $tx->created_at);
                if ($cancelDate) {
                    $dKeyCancel = $cancelDate->format('Y-m-d');
                    if (isset($dailyBucketsCancelled[$dKeyCancel])) {
                        if ($isBongkar) {
                            $dailyBucketsCancelled[$dKeyCancel]['batal_bongkar']++;
                        } else {
                            $dailyBucketsCancelled[$dKeyCancel]['batal_muat']++;
                        }
                        $dailyBucketsCancelled[$dKeyCancel]['total']++;
                    }
                }
            }

            // Frequent Trucks aggregation
            $noPol = $tx->vehicle ? strtoupper(trim($tx->vehicle->no_pol)) : null;
            if (!$noPol || $noPol === '-') {
                $noPol = $tx->nama_driver ? ('DRIVER: ' . strtoupper($tx->nama_driver)) : 'PLAT TIDAK TERCATAT';
            }
            $vVendor = strtoupper(trim($tx->vendor ?: 'LAIN-LAIN / UNKNOWN'));
            $rawQty = floatval($tx->qty_spb);
            $tonnage = ($rawQty >= 100 ? $rawQty / 1000 : $rawQty);

            if (!isset($truckVisits[$noPol])) {
                $truckVisits[$noPol] = [
                    'no_pol' => $noPol,
                    'vendor' => $vVendor,
                    'trips' => 0,
                    'total_tat_min' => 0,
                    'tat_samples' => 0,
                    'total_tonnage' => 0,
                ];
            }
            $truckVisits[$noPol]['trips']++;
            $truckVisits[$noPol]['total_tonnage'] += $tonnage;
            if ($truckDuration > 0) {
                $truckVisits[$noPol]['total_tat_min'] += $truckDuration;
                $truckVisits[$noPol]['tat_samples']++;
            }

            // Real Loading Time Calculation
            if ($startLoad && $finishLoad && $finishLoad->gte($startLoad)) {
                $loadMin = $startLoad->diffInMinutes($finishLoad);
                $allLoadingMinutes[] = $loadMin;

                $dockName = $this->getNormalizedAreaName($tx->targetLocation);

                if (!isset($loadingBayTimes[$dockName])) {
                    $loadingBayTimes[$dockName] = [
                        'dock_name' => $dockName,
                        'total_min' => 0,
                        'sample_count' => 0,
                        'min_min' => $loadMin,
                        'max_min' => $loadMin,
                    ];
                }
                $loadingBayTimes[$dockName]['total_min'] += $loadMin;
                $loadingBayTimes[$dockName]['sample_count']++;
                if ($loadMin < $loadingBayTimes[$dockName]['min_min']) $loadingBayTimes[$dockName]['min_min'] = $loadMin;
                if ($loadMin > $loadingBayTimes[$dockName]['max_min']) $loadingBayTimes[$dockName]['max_min'] = $loadMin;
            }

            // Vendor Over Duration Aggregation (> 7.5 jam)
            if (!isset($vendorOverDuration[$vVendor])) {
                $vendorOverDuration[$vVendor] = [
                    'vendor' => $vVendor,
                    'total_trips' => 0,
                    'over_count' => 0,
                    'total_duration_min' => 0,
                    'total_over_duration_min' => 0,
                ];
            }
            $vendorOverDuration[$vVendor]['total_trips']++;
            $vendorOverDuration[$vVendor]['total_duration_min'] += $truckDuration;
            if ($truckDuration > $slaThreshold) {
                $vendorOverDuration[$vVendor]['over_count']++;
                $vendorOverDuration[$vVendor]['total_over_duration_min'] += $truckDuration;
            }

            // Fleet Types Distribution (Curah & Slipsheet disatukan jadi 'Muatan')
            $rawJenis = strtolower(trim($tx->jenis ?: 'bongkaran'));
            if (in_array($rawJenis, ['curah', 'slipsheet', 'muatan'])) {
                $jKey = 'Muatan';
                $trendKey = 'muatan';
            } elseif ($rawJenis === 'retur') {
                $jKey = 'Retur';
                $trendKey = 'retur';
            } else {
                $jKey = 'Bongkaran';
                $trendKey = 'bongkaran';
            }
            $fleetTypes[$jKey]['count']++;
            $fleetTypes[$jKey]['total_min'] += $truckDuration;

            $fleetDate = $pos1 ?: ($tmbIn ?: $tx->created_at);
            if ($fleetDate) {
                $dKeyFleet = $fleetDate->format('Y-m-d');
                if (isset($dailyFleetBuckets[$dKeyFleet])) {
                    $dailyFleetBuckets[$dKeyFleet][$trendKey]++;
                    $dailyFleetBuckets[$dKeyFleet]['total']++;
                }
            }

            // Weekly Duration Buckets
            if ($inEffective) {
                $wKey = $inEffective->format('Y-\WW');
                if (isset($weeklyBuckets[$wKey])) {
                    $weeklyBuckets[$wKey]['total_trucks']++;
                    if ($truckDuration > 0) {
                        $weeklyBuckets[$wKey]['durations'][] = $truckDuration;
                    }
                    if ($truckDuration > $slaThreshold) {
                        $weeklyBuckets[$wKey]['over_duration_count']++;
                    }
                }
            }
        }

        // Calculate stage aggregates
        $stageResults = [];
        $primaryBottleneckStage = null;
        $maxStageAvg = 0;

        foreach ($stageDefs as $k => $s) {
            $vals = $s['durations'];
            $cnt = count($vals);
            $avg = $cnt > 0 ? round(array_sum($vals) / $cnt) : 0;
            $min = $cnt > 0 ? min($vals) : 0;
            $max = $cnt > 0 ? max($vals) : 0;

            if ($avg > $maxStageAvg) {
                $maxStageAvg = $avg;
                $primaryBottleneckStage = $s['name'];
            }

            $stageResults[$k] = [
                'name' => $s['name'],
                'desc' => $s['desc'],
                'avg_min' => $avg,
                'min_min' => $min,
                'max_min' => $max,
                'avg_formatted' => $formatDur($avg),
                'sample_count' => $cnt,
            ];
        }

        // Hourly Check-ins Aggregates for Pos 1 (Jam 1 s/d 24)
        $hourlyListPos1 = array_values($hourlyBucketsPos1);
        $totalCheckinsPos1 = array_sum(array_column($hourlyListPos1, 'count'));
        $weightedHourSum = 0;
        $maxPos1Count = 0;
        $peakHourPos1 = null;

        foreach ($hourlyListPos1 as $hb) {
            $weightedHourSum += ($hb['hour'] * $hb['count']);
            if ($hb['count'] > $maxPos1Count) {
                $maxPos1Count = $hb['count'];
                $peakHourPos1 = $hb['hour'];
            }
        }

        $avgHourPos1Decimal = $totalCheckinsPos1 > 0 ? round($weightedHourSum / $totalCheckinsPos1, 1) : 0;
        $avgHourPos1Rounded = round($avgHourPos1Decimal);
        $avgPerHourPos1 = round($totalCheckinsPos1 / 24, 1);

        // Daily Check-ins Aggregates for Timbangan
        $dailyListTmb = array_values($dailyBucketsTmb);
        $totalDailyDays = max(1, count($dailyListTmb));
        $totalCheckinsTmb = array_sum(array_column($dailyListTmb, 'count'));
        $avgDailyCheckinTmb = round($totalCheckinsTmb / $totalDailyDays, 1);

        // Daily Cancellation Aggregates
        $dailyListCancelled = array_values($dailyBucketsCancelled);

        // Frequent Trucks Top 8
        uasort($truckVisits, fn($a, $b) => $b['trips'] <=> $a['trips']);
        $topFrequentTrucks = array_values(array_slice(array_map(function ($t) use ($formatDur) {
            $avgTat = $t['tat_samples'] > 0 ? round($t['total_tat_min'] / $t['tat_samples']) : 0;
            return [
                'no_pol' => $t['no_pol'],
                'vendor' => $t['vendor'],
                'trips' => $t['trips'],
                'avg_tat_min' => $avgTat,
                'avg_tat_formatted' => $avgTat > 0 ? $formatDur($avgTat) : '-',
                'total_tonnage' => round($t['total_tonnage'], 2),
            ];
        }, $truckVisits), 0, 8));

        // Loading Bay Durations Aggregates
        uasort($loadingBayTimes, fn($a, $b) => $b['sample_count'] <=> $a['sample_count']);
        $loadingBayList = array_values(array_map(function ($b) use ($formatDur) {
            $avg = $b['sample_count'] > 0 ? round($b['total_min'] / $b['sample_count']) : 0;
            return [
                'dock_name' => $b['dock_name'],
                'sample_count' => $b['sample_count'],
                'avg_duration_min' => $avg,
                'avg_duration_formatted' => $formatDur($avg),
                'min_formatted' => $formatDur($b['min_min']),
                'max_formatted' => $formatDur($b['max_min']),
            ];
        }, $loadingBayTimes));

        $totalLoadingSamples = count($allLoadingMinutes);
        $overallAvgLoadingMin = $totalLoadingSamples > 0 ? round(array_sum($allLoadingMinutes) / $totalLoadingSamples) : 0;

        // Yard & Loading Bay Throughput
        $bayThroughput = [];
        $totalTonAll = 0;
        $totalLoadingHoursAll = 0;

        foreach ($transactions as $tx) {
            $locKey = $this->getNormalizedAreaName($tx->targetLocation);

            if (!isset($bayThroughput[$locKey])) {
                $bayThroughput[$locKey] = [
                    'location_name' => $locKey,
                    'total_tonnage' => 0,
                    'trucks_handled' => 0,
                    'total_load_minutes' => 0,
                    'load_samples' => 0,
                ];
            }

            $rawQ = floatval($tx->qty_spb);
            $ton = ($rawQ >= 100 ? $rawQ / 1000 : $rawQ);
            $bayThroughput[$locKey]['total_tonnage'] += $ton;
            $totalTonAll += $ton;

            if ($tx->check_out_time || $tx->status === 'completed') {
                $bayThroughput[$locKey]['trucks_handled']++;
            }

            if ($tx->start_loading_time && $tx->finish_loading_time && $tx->finish_loading_time->gte($tx->start_loading_time)) {
                $loadMin = $tx->start_loading_time->diffInMinutes($tx->finish_loading_time);
                $bayThroughput[$locKey]['total_load_minutes'] += $loadMin;
                $bayThroughput[$locKey]['load_samples']++;
                $totalLoadingHoursAll += ($loadMin / 60);
            }
        }

        $overallProductivityTonPerHour = ($totalLoadingHoursAll > 0) ? round($totalTonAll / $totalLoadingHoursAll, 1) : 0;

        uasort($bayThroughput, fn($a, $b) => $b['total_tonnage'] <=> $a['total_tonnage']);
        $bayList = array_values(array_map(function ($b) use ($formatDur) {
            $avgLoad = $b['load_samples'] > 0 ? round($b['total_load_minutes'] / $b['load_samples']) : 0;
            return [
                'location_name' => $b['location_name'],
                'total_tonnage' => round($b['total_tonnage'], 2),
                'trucks_handled' => $b['trucks_handled'],
                'avg_load_formatted' => $formatDur($avgLoad),
            ];
        }, $bayThroughput));

        // Detail data Vendor dari total truck yang melebihi Truck Duration (7.5 jam / $slaThreshold)
        uasort($vendorOverDuration, function ($a, $b) {
            if ($b['over_count'] !== $a['over_count']) {
                return $b['over_count'] <=> $a['over_count'];
            }
            return $b['total_trips'] <=> $a['total_trips'];
        });

        $vendorOverDurationList = array_values(array_map(function ($v) use ($formatDur) {
            $avgAll = $v['total_trips'] > 0 ? round($v['total_duration_min'] / $v['total_trips']) : 0;
            $avgOver = $v['over_count'] > 0 ? round($v['total_over_duration_min'] / $v['over_count']) : 0;
            $overRate = $v['total_trips'] > 0 ? round(($v['over_count'] / $v['total_trips']) * 100, 1) : 0;

            $status = 'Aman (≤ 7.5j)';
            if ($overRate >= 50) {
                $status = 'Kritis (> 50%)';
            } elseif ($v['over_count'] > 0) {
                $status = 'Waspada';
            }

            return [
                'vendor' => $v['vendor'],
                'total_trips' => $v['total_trips'],
                'over_count' => $v['over_count'],
                'over_rate' => $overRate,
                'avg_duration_formatted' => $formatDur($avgAll),
                'avg_over_duration_formatted' => $avgOver > 0 ? $formatDur($avgOver) : '-',
                'status' => $status,
            ];
        }, $vendorOverDuration));

        // Fleet Types Distribution
        $fleetDistribution = array_values(array_map(function ($f) use ($formatDur) {
            $avg = $f['count'] > 0 ? round($f['total_min'] / $f['count']) : 0;
            return [
                'jenis' => $f['jenis'],
                'count' => $f['count'],
                'avg_duration_formatted' => $formatDur($avg),
            ];
        }, $fleetTypes));

        $dailyFleetList = array_values($dailyFleetBuckets);
        $totalDaysFleet = max(1, count($dailyFleetList));
        $totalBongkaran = array_sum(array_column($dailyFleetList, 'bongkaran'));
        $totalMuatan = array_sum(array_column($dailyFleetList, 'muatan'));
        $totalRetur = array_sum(array_column($dailyFleetList, 'retur'));
        $totalFleetAll = array_sum(array_column($dailyFleetList, 'total'));

        $fleetTrend = [
            'series' => $dailyFleetList,
            'total_bongkaran' => $totalBongkaran,
            'total_muatan' => $totalMuatan,
            'total_retur' => $totalRetur,
            'total_all' => $totalFleetAll,
            'avg_per_day' => round($totalFleetAll / $totalDaysFleet, 1),
            'total_days' => $totalDaysFleet,
        ];

        // Weekly Truck Duration List
        $weeklyDurationList = array_values(array_filter(array_map(function ($w) use ($formatDur) {
            $cnt = count($w['durations']);
            $avg = $cnt > 0 ? round(array_sum($w['durations']) / $cnt) : 0;
            $overRate = $w['total_trucks'] > 0 ? round(($w['over_duration_count'] / $w['total_trucks']) * 100, 1) : 0;
            return [
                'week_key' => $w['week_key'],
                'label' => $w['label'],
                'week_label' => $w['label'],
                'short_label' => $w['short_label'],
                'total_trucks' => $w['total_trucks'],
                'avg_duration_min' => $avg,
                'avg_duration_hours' => round($avg / 60, 1),
                'avg_duration_formatted' => $formatDur($avg),
                'over_duration_count' => $w['over_duration_count'],
                'over_sla_count' => $w['over_duration_count'],
                'over_duration_rate' => $overRate,
                'over_sla_rate' => $overRate,
            ];
        }, $weeklyBuckets), fn($w) => $w['total_trucks'] > 0));

        // =========================================================================
        // AUDIT KINERJA OPERATOR (Nama Lengkap / Username)
        // =========================================================================
        $operatorWork = [];

        $recordOperatorAction = function ($user, $pos, $durationMin = null) use (&$operatorWork) {
            if (!$user) return;
            $uId = $user->id;
            $namaLengkap = trim($user->nama_lengkap ?? '');
            $username = trim($user->username ?? '');
            $displayName = $namaLengkap ?: ($username ?: ('Petugas #' . $uId));
            $key = $uId . '_' . $pos;

            if (!isset($operatorWork[$key])) {
                $operatorWork[$key] = [
                    'user_id' => $uId,
                    'user_name' => $displayName,
                    'nama_lengkap' => $namaLengkap,
                    'username' => $username,
                    'pos' => $pos,
                    'total_tickets' => 0,
                    'total_min' => 0,
                    'sample_count' => 0,
                ];
            }

            $operatorWork[$key]['total_tickets']++;
            if ($durationMin !== null && $durationMin > 0) {
                $operatorWork[$key]['total_min'] += $durationMin;
                $operatorWork[$key]['sample_count']++;
            }
        };

        foreach ($transactions as $tx) {
            // Check-in Timbangan
            if ($tx->creator) {
                $recordOperatorAction($tx->creator, 'Check-in Timbangan');
            }
            // Pos Antrian
            if ($tx->queueTakenBy) {
                $recordOperatorAction($tx->queueTakenBy, 'Pos Antrian');
            }
            // Loading Bay Operator
            if ($tx->finishLoadingBy || $tx->startLoadingBy) {
                $dur = ($tx->start_loading_time && $tx->finish_loading_time) ? $tx->start_loading_time->diffInMinutes($tx->finish_loading_time) : null;
                $recordOperatorAction($tx->finishLoadingBy ?: $tx->startLoadingBy, 'Loading Bay / Gudang', $dur);
            }
            // Timbangan Out
            if ($tx->timbanganOutBy) {
                $recordOperatorAction($tx->timbanganOutBy, 'Petugas Timbangan');
            }
            // Pos Keluar
            if ($tx->checkOutBy) {
                $recordOperatorAction($tx->checkOutBy, 'Pos Security Keluar');
            }
        }

        $txIds = $transactions->pluck('id')->toArray();
        $trackingLogs = !empty($txIds)
            ? VehicleTracking::with(['creator', 'location'])
                ->whereIn('vehicle_transaction_id', $txIds)
                ->whereNotNull('created_by')
                ->get()
            : collect();

        foreach ($trackingLogs as $log) {
            if ($log->creator) {
                $pos = $log->location ? $log->location->name : 'Operasional Lapangan';
                if (stripos($pos, 'qc') !== false || stripos($pos, 'sample') !== false || stripos($pos, 'sampling') !== false) {
                    continue;
                }
                $durMin = $log->duration_seconds ? round(abs($log->duration_seconds) / 60) : null;
                $recordOperatorAction($log->creator, $pos, $durMin);
            }
        }

        uasort($operatorWork, fn($a, $b) => $b['total_tickets'] <=> $a['total_tickets']);

        $operatorList = array_values(array_slice(array_map(function ($op) use ($formatDur) {
            $avg = $op['sample_count'] > 0 ? round($op['total_min'] / $op['sample_count']) : 0;
            return [
                'user_name' => $op['user_name'],
                'nama_lengkap' => $op['nama_lengkap'],
                'username' => $op['username'],
                'pos' => $op['pos'],
                'total_tickets' => $op['total_tickets'],
                'avg_duration_formatted' => $avg > 0 ? $formatDur($avg) : 'Instant / Log',
            ];
        }, $operatorWork), 0, 15));

        // Overall Executive KPIs
        $totalVehicles = $transactions->count();
        $avgTatMin = $tatCount > 0 ? round($totalTATMinutes / $tatCount) : 0;
        $overSlaRate = $tatCount > 0 ? round(($overSlaCount / $tatCount) * 100, 1) : 0;
        $cancelledRate = $totalVehicles > 0 ? round(($cancelledCount / $totalVehicles) * 100, 1) : 0;
        $healthScore = max(0, min(100, round(100 - $overSlaRate)));

        return response()->json([
            'success' => true,
            'meta' => [
                'period' => $period,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'sla_threshold_minutes' => $slaThreshold,
            ],
            'kpi' => [
                'total_vehicles' => $totalVehicles,
                'avg_tat_min' => $avgTatMin,
                'avg_tat_formatted' => $formatDur($avgTatMin),
                'over_sla_count' => $overSlaCount,
                'over_sla_rate' => $overSlaRate,
                'cancelled_count' => $cancelledCount,
                'cancelled_bongkar_count' => $cancelledBongkarCount,
                'cancelled_muat_count' => $cancelledMuatCount,
                'cancelled_rate' => $cancelledRate,
                'health_score' => $healthScore,
                'total_tonnage' => round($totalTonAll, 2),
                'primary_bottleneck_stage' => $primaryBottleneckStage ?: 'N/A',
                'overall_productivity' => $overallProductivityTonPerHour,
                'avg_daily_checkin' => $avgDailyCheckinTmb,
                'avg_daily_checkin_timbangan' => $avgDailyCheckinTmb,
                'avg_daily_checkin_pos1' => $avgHourPos1Rounded > 0 ? "Jam $avgHourPos1Rounded" : '-',
                'avg_hour_pos1' => $avgHourPos1Rounded > 0 ? "Jam $avgHourPos1Rounded" : '-',
                'peak_hour_pos1' => $peakHourPos1 ? "Jam $peakHourPos1" : '-',
                'total_checkins' => $totalCheckinsTmb,
                'total_checkins_timbangan' => $totalCheckinsTmb,
                'total_checkins_pos1' => $totalCheckinsPos1,
                'total_loading_samples' => $totalLoadingSamples,
                'overall_avg_loading_formatted' => $formatDur($overallAvgLoadingMin),
            ],
            // 1. Analisa Waktu & Bottleneck
            'stages' => $stageResults,
            'primary_bottleneck' => $primaryBottleneckStage ?: 'N/A',
            'max_stage_avg_formatted' => $formatDur($maxStageAvg),

            // 2. Daily Check-in POS 1, TIMBANGAN & PEMBATALAN
            'daily_checkin_pos1' => [
                'mode' => 'hourly',
                'series' => $hourlyListPos1,
                'total_checkins' => $totalCheckinsPos1,
                'avg_hour' => $avgHourPos1Rounded > 0 ? "Jam $avgHourPos1Rounded" : '-',
                'avg_hour_decimal' => $avgHourPos1Decimal,
                'peak_hour' => $peakHourPos1 ? "Jam $peakHourPos1" : '-',
                'peak_count' => $maxPos1Count,
                'avg_per_hour' => $avgPerHourPos1,
                'avg_per_day' => $avgHourPos1Rounded > 0 ? "Jam $avgHourPos1Rounded" : '-',
                'total_days' => 24,
            ],
            'daily_checkin_timbangan' => [
                'series' => $dailyListTmb,
                'avg_per_day' => $avgDailyCheckinTmb,
                'total_days' => $totalDailyDays,
                'total_checkins' => $totalCheckinsTmb,
            ],
            'daily_checkin' => [
                'series' => $dailyListTmb,
                'avg_per_day' => $avgDailyCheckinTmb,
                'total_days' => $totalDailyDays,
                'total_checkins' => $totalCheckinsTmb,
            ],
            'daily_cancelled' => [
                'series' => $dailyListCancelled,
                'total_cancelled' => $cancelledCount,
                'total_batal_bongkar' => $cancelledBongkarCount,
                'total_batal_muat' => $cancelledMuatCount,
                'avg_per_day' => round($cancelledCount / $totalDailyDays, 1),
                'total_days' => $totalDailyDays,
            ],

            // 3. Durasi Bongkar / Muat Riil
            'loading_time' => [
                'bay_list' => $loadingBayList,
                'total_samples' => $totalLoadingSamples,
                'overall_avg_min' => $overallAvgLoadingMin,
                'overall_avg_formatted' => $formatDur($overallAvgLoadingMin),
            ],

            // 4. Frequent Trucks
            'frequent_trucks' => $topFrequentTrucks,

            // 5. Yard & Loading Bay Throughput
            'capacity_throughput' => [
                'bay_throughput' => $bayList,
                'productivity_ton_per_hour' => $overallProductivityTonPerHour,
                'total_tonnage' => round($totalTonAll, 2),
            ],

            // 6. Vendor Detail Data Over Duration (> 7.5 jam) & Armada
            'vendor_over_duration' => $vendorOverDurationList,
            'vendor_fleet' => [
                'vendor_tat' => $vendorOverDurationList,
                'fleet_distribution' => $fleetDistribution,
                'fleet_trend' => $fleetTrend,
            ],
            'fleet_trend' => $fleetTrend,

            // 7. Weekly Truck Duration
            'weekly_duration' => $weeklyDurationList,

            // 8. Kinerja Operator
            'operators' => [
                'operator_list' => $operatorList,
            ],
            'operator_list' => $operatorList,

            // Backward Compatibility Aliases
            'cluster_1' => [
                'stages' => $stageResults,
                'primary_bottleneck' => $primaryBottleneckStage ?: 'N/A',
                'max_stage_avg_formatted' => $formatDur($maxStageAvg),
                'daily_checkin' => [
                    'series' => $dailyListTmb,
                    'avg_per_day' => $avgDailyCheckinTmb,
                ],
            ],
            'cluster_3' => [
                'bay_throughput' => $bayList,
                'loading_time' => $loadingBayList,
                'productivity_ton_per_hour' => $overallProductivityTonPerHour,
                'total_tonnage' => round($totalTonAll, 2),
            ],
            'cluster_4' => [
                'vendor_tat' => $vendorOverDurationList,
                'fleet_distribution' => $fleetDistribution,
                'frequent_trucks' => $topFrequentTrucks,
            ],
            'cluster_6' => [
                'operators' => $operatorList,
            ],
        ]);
    }
}
