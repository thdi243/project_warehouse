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
     * Aggregate data for the 6 Analytic Clusters.
     */
    public function data(Request $request)
    {
        $period = $request->input('period', '30days');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $slaThreshold = intval($request->input('sla_limit', 120)); // in minutes

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
                    $start = Carbon::create(2020, 1, 1);
                    $end = Carbon::now()->endOfDay();
                    break;
                case '30days':
                default:
                    $start = Carbon::now()->subDays(29)->startOfDay();
                    $end = Carbon::now()->endOfDay();
                    break;
            }
        }

        // Base Query
        $query = VehicleTransaction::with([
            'vehicle',
            'item',
            'targetLocation',
            'currentLocation',
            'creator',
            'queueTakenBy',
            'startSamplingBy',
            'finishSamplingBy',
            'startLoadingBy',
            'finishLoadingBy',
            'timbanganOutBy',
            'checkOutBy',
        ])->whereBetween('check_in_time', [$start, $end]);

        if ($request->filled('location_id') && $request->location_id !== 'all') {
            $query->where('target_location_id', $request->location_id);
        }

        if ($request->filled('jenis') && $request->jenis !== 'all') {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('vendor') && $request->vendor !== 'all') {
            $query->where('vendor', $request->vendor);
        }

        if ($request->filled('item_id') && $request->item_id !== 'all') {
            $query->where('item_id', $request->item_id);
        }

        $transactions = $query->orderBy('check_in_time', 'asc')->get();

        // Helper format duration
        $formatDur = function ($minutes) {
            if ($minutes <= 0) return '0 mnt';
            if ($minutes < 60) return round($minutes) . ' mnt';
            $h = floor($minutes / 60);
            $m = round($minutes % 60);
            return $h . 'j ' . ($m > 0 ? $m . 'm' : '');
        };

        // =========================================================================
        // 1. ANALISA WAKTU & BOTTLENECK (STAGE DURATIONS)
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
            'sampling_lab' => [
                'name' => 'SAMPLING QC',
                'desc' => 'Mulai Sampling hingga Selesai Pengujian Lab',
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

        // Daily Check-ins Series & Average Line
        $dailyBuckets = [];
        $cursorDate = $start->copy()->startOfDay();
        $endDate = $end->copy()->startOfDay();

        while ($cursorDate->lte($endDate)) {
            $dateKey = $cursorDate->format('Y-m-d');
            $dailyBuckets[$dateKey] = [
                'date' => $dateKey,
                'label' => $cursorDate->format('d/m'),
                'full_label' => $cursorDate->format('d M Y'),
                'count' => 0,
            ];
            $cursorDate->addDay();
        }

        // Frequent Trucks Map
        $truckVisits = [];

        // Real Loading / Unloading Times (hanya yang memiliki data waktu bongkar muat lengkap)
        $loadingBayTimes = [];
        $allLoadingMinutes = [];

        $totalTATMinutes = 0;
        $tatCount = 0;
        $overSlaCount = 0;

        foreach ($transactions as $tx) {
            $pos1 = $tx->checkin_pos1;
            $tmbIn = $tx->check_in_time;
            $queueTime = $tx->queue_taken_time;
            $startSamp = $tx->start_sampling_time;
            $finishSamp = $tx->finish_sampling_time;
            $startLoad = $tx->start_loading_time;
            $finishLoad = $tx->finish_loading_time;
            $tmbOut = $tx->timbangan_out_time;
            $checkOut = $tx->check_out_time;

            // 1. Pos 1 ke Timbangan Masuk: checkin_pos1 -> check_in_time
            if ($pos1 && $tmbIn && $tmbIn->gte($pos1)) {
                $stageDefs['pos1_ke_timbangan']['durations'][] = $pos1->diffInMinutes($tmbIn);
            }

            // 2. Antrian Menuju Dock: check_in_time -> queue_taken_time (fallback ke start_sampling / start_loading)
            $nextAfterTmb = $queueTime ?: ($startSamp ?: $startLoad);
            if ($tmbIn && $nextAfterTmb && $nextAfterTmb->gte($tmbIn)) {
                $stageDefs['antrian_dock']['durations'][] = $tmbIn->diffInMinutes($nextAfterTmb);
            }

            // 3. Sampling Lab QC: start_sampling_time -> finish_sampling_time
            if ($startSamp && $finishSamp && $finishSamp->gte($startSamp)) {
                $stageDefs['sampling_lab']['durations'][] = $startSamp->diffInMinutes($finishSamp);
            }

            // 4. Bongkar/Muat: start_loading_time -> finish_loading_time
            if ($startLoad && $finishLoad && $finishLoad->gte($startLoad)) {
                $stageDefs['bongkar_muat']['durations'][] = $startLoad->diffInMinutes($finishLoad);
            }

            // 5. Check-Out Timbangan: timbangan_out_time -> check_out_time
            if ($tmbOut && $checkOut && $checkOut->gte($tmbOut)) {
                $stageDefs['checkout_timbangan']['durations'][] = $tmbOut->diffInMinutes($checkOut);
            }

            // Daily Check-in aggregation
            $inStamp = $tmbIn ?: $pos1;
            if ($inStamp) {
                $dKey = $inStamp->format('Y-m-d');
                if (isset($dailyBuckets[$dKey])) {
                    $dailyBuckets[$dKey]['count']++;
                }
            }

            // Frequent Trucks aggregation
            $noPol = $tx->vehicle ? strtoupper(trim($tx->vehicle->no_pol)) : null;
            if (!$noPol || $noPol === '-') {
                $noPol = $tx->nama_driver ? ('DRIVER: ' . strtoupper($tx->nama_driver)) : 'PLAT TIDAK TERCATAT';
            }
            $vVendor = strtoupper(trim($tx->vendor ?: 'LAIN-LAIN'));
            $inEffective = $pos1 ?: $tmbIn;
            $outEffective = $checkOut ?: ($tmbOut ?: ($tx->status === 'completed' ? $tx->updated_at : null));
            $truckDwell = ($inEffective && $outEffective && $outEffective->gte($inEffective)) ? $inEffective->diffInMinutes($outEffective) : 0;
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
            if ($truckDwell > 0) {
                $truckVisits[$noPol]['total_tat_min'] += $truckDwell;
                $truckVisits[$noPol]['tat_samples']++;
            }

            // Real Loading Time Calculation (hanya yang ada time loading lengkap)
            if ($startLoad && $finishLoad && $finishLoad->gte($startLoad)) {
                $loadMin = $startLoad->diffInMinutes($finishLoad);
                $allLoadingMinutes[] = $loadMin;

                $targetLoc = $tx->targetLocation;
                $dockName = $targetLoc ? ($targetLoc->name . ' (' . $targetLoc->s_loc . ')') : 'Gudang Utama / Dock';

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

            // Turnaround Time
            if ($inEffective) {
                $effectiveOut = $checkOut ?: ($tmbOut ?: ($tx->status === 'completed' ? $tx->updated_at : Carbon::now()));
                $dwell = max(1, round($inEffective->diffInMinutes($effectiveOut)));
                $totalTATMinutes += $dwell;
                $tatCount++;
                if ($dwell > $slaThreshold) {
                    $overSlaCount++;
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

        // Daily Check-ins Aggregates
        $dailyList = array_values($dailyBuckets);
        $totalDailyDays = max(1, count($dailyList));
        $totalCheckins = array_sum(array_column($dailyList, 'count'));
        $avgDailyCheckin = round($totalCheckins / $totalDailyDays, 1);

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

        // =========================================================================
        // 2. ANALISA MUTU & KUALITAS MATERIAL (QC & COMPLIANCE - NO HOLD)
        // =========================================================================
        $qcCounts = [
            'PASS' => 0,
            'REJECT' => 0,
            'WAITING' => 0,
            'NOT_REQUIRED' => 0,
        ];

        $vendorRejections = [];
        $materialDurations = [];

        foreach ($transactions as $tx) {
            $qc = strtolower(trim($tx->qc_status ?? ''));
            $isReject = false;

            if (in_array($qc, ['released', 'pass', 'passed', 'completed'])) {
                $qcCounts['PASS']++;
            } elseif (in_array($qc, ['rejected', 'reject'])) {
                $qcCounts['REJECT']++;
                $isReject = true;
            } elseif (in_array($qc, ['not_required', 'none'])) {
                $qcCounts['NOT_REQUIRED']++;
            } else {
                // pending, on_check, waiting_dokumen, or null/empty
                $qcCounts['WAITING']++;
            }

            // Vendor Rejection Aggregation
            $vName = strtoupper(trim($tx->vendor ?: 'LAIN-LAIN / UNKNOWN'));
            if (!isset($vendorRejections[$vName])) {
                $vendorRejections[$vName] = [
                    'vendor' => $vName,
                    'total_trips' => 0,
                    'reject_count' => 0,
                    'reject_qty' => 0,
                    'reasons' => [],
                ];
            }
            $vendorRejections[$vName]['total_trips']++;
            if ($isReject) {
                $vendorRejections[$vName]['reject_count']++;
                $rawQ = floatval($tx->qty_spb);
                $vendorRejections[$vName]['reject_qty'] += ($rawQ >= 100 ? $rawQ / 1000 : $rawQ);
                if ($tx->follow_up_notes) {
                    $vendorRejections[$vName]['reasons'][] = $tx->follow_up_notes;
                }
            }

            // Material Lab Duration
            if ($tx->start_sampling_time && $tx->finish_sampling_time && $tx->finish_sampling_time->gte($tx->start_sampling_time)) {
                $iName = $tx->item ? $tx->item->name : 'Item #' . ($tx->item_id ?? 'N/A');
                $sampMin = $tx->start_sampling_time->diffInMinutes($tx->finish_sampling_time);
                if (!isset($materialDurations[$iName])) {
                    $materialDurations[$iName] = [
                        'item_name' => $iName,
                        'total_min' => 0,
                        'sample_count' => 0,
                    ];
                }
                $materialDurations[$iName]['total_min'] += $sampMin;
                $materialDurations[$iName]['sample_count']++;
            }
        }

        // Top 5 Vendors by Rejection Rate
        uasort($vendorRejections, function ($a, $b) {
            $rateA = $a['total_trips'] > 0 ? ($a['reject_count'] / $a['total_trips']) : 0;
            $rateB = $b['total_trips'] > 0 ? ($b['reject_count'] / $b['total_trips']) : 0;
            if ($rateB == $rateA) {
                return $b['reject_count'] <=> $a['reject_count'];
            }
            return $rateB <=> $rateA;
        });

        $top5RejectVendors = array_values(array_slice(array_map(function ($v) {
            $rate = $v['total_trips'] > 0 ? round(($v['reject_count'] / $v['total_trips']) * 100, 1) : 0;
            $reasons = count($v['reasons']) > 0 ? implode('; ', array_unique($v['reasons'])) : ($v['reject_count'] > 0 ? 'Kualitas Tidak Sesuai Spek Lab' : '-');
            return [
                'vendor' => $v['vendor'],
                'total_trips' => $v['total_trips'],
                'reject_count' => $v['reject_count'],
                'reject_qty_ton' => round($v['reject_qty'], 2),
                'reject_rate' => $rate,
                'common_reason' => $reasons,
            ];
        }, $vendorRejections), 0, 5));

        // Material Lab Testing Durations
        uasort($materialDurations, function ($a, $b) {
            $avgA = $a['sample_count'] > 0 ? $a['total_min'] / $a['sample_count'] : 0;
            $avgB = $b['sample_count'] > 0 ? $b['total_min'] / $b['sample_count'] : 0;
            return $avgB <=> $avgA;
        });

        $materialList = array_values(array_map(function ($m) use ($formatDur) {
            $avg = $m['sample_count'] > 0 ? round($m['total_min'] / $m['sample_count']) : 0;
            return [
                'item_name' => $m['item_name'],
                'sample_count' => $m['sample_count'],
                'avg_duration_min' => $avg,
                'avg_duration_formatted' => $formatDur($avg),
            ];
        }, $materialDurations));

        // =========================================================================
        // KLUSTER 3: ANALISA KAPASITAS & BONGKAR MUAT (YARD & LOADING BAY)
        // =========================================================================
        $bayThroughput = [];
        $totalTonAll = 0;
        $totalLoadingHoursAll = 0;

        foreach ($transactions as $tx) {
            $loc = $tx->targetLocation;
            $locKey = $loc ? ($loc->name . ' (' . $loc->s_loc . ')') : 'Gudang Utama / Dock';

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

        // Productivity Gauge: Ton per Hour
        $overallProductivityTonPerHour = ($totalLoadingHoursAll > 0) ? round($totalTonAll / $totalLoadingHoursAll, 1) : 0;

        // Sort Bay Throughput by tonnage
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

        // =========================================================================
        // KLUSTER 4: ANALISA VENDOR & ARMADA (VENDOR & FLEET PERFORMANCE)
        // =========================================================================
        $vendorFleet = [];
        $fleetTypes = [];

        foreach ($transactions as $tx) {
            $vName = strtoupper(trim($tx->vendor ?: 'LAIN-LAIN / UNKNOWN'));
            $inTime = $tx->checkin_pos1 ?: $tx->check_in_time;
            $outTime = $tx->check_out_time ?: ($tx->timbangan_out_time ?: ($tx->status === 'completed' ? $tx->updated_at : Carbon::now()));
            $dwell = ($inTime && $outTime) ? max(1, round($inTime->diffInMinutes($outTime))) : 0;

            if (!isset($vendorFleet[$vName])) {
                $vendorFleet[$vName] = [
                    'vendor' => $vName,
                    'trips' => 0,
                    'total_tat_min' => 0,
                    'on_time_count' => 0,
                ];
            }
            $vendorFleet[$vName]['trips']++;
            $vendorFleet[$vName]['total_tat_min'] += $dwell;
            if ($dwell <= $slaThreshold) {
                $vendorFleet[$vName]['on_time_count']++;
            }

            // Fleet Type (jenis: Bongkaran, Curah, Slipsheet, Retur)
            $j = ucfirst(strtolower($tx->jenis ?: 'bongkaran'));
            if (!isset($fleetTypes[$j])) {
                $fleetTypes[$j] = [
                    'jenis' => $j,
                    'count' => 0,
                    'total_min' => 0,
                ];
            }
            $fleetTypes[$j]['count']++;
            $fleetTypes[$j]['total_min'] += $dwell;
        }

        // Vendor Table Ranking by Turnaround Time
        uasort($vendorFleet, function ($a, $b) {
            $avgA = $a['trips'] > 0 ? $a['total_tat_min'] / $a['trips'] : 0;
            $avgB = $b['trips'] > 0 ? $b['total_tat_min'] / $b['trips'] : 0;
            return $avgA <=> $avgB; // faster TAT first
        });

        $vendorTatList = array_values(array_map(function ($v) use ($formatDur) {
            $avg = $v['trips'] > 0 ? round($v['total_tat_min'] / $v['trips']) : 0;
            $onTimeRate = $v['trips'] > 0 ? round(($v['on_time_count'] / $v['trips']) * 100, 1) : 0;
            $status = 'Tepat Waktu';
            if ($onTimeRate < 50) $status = 'Sering Terlambat';
            elseif ($onTimeRate < 80) $status = 'Waspada';

            return [
                'vendor' => $v['vendor'],
                'trips' => $v['trips'],
                'avg_tat_min' => $avg,
                'avg_tat_formatted' => $formatDur($avg),
                'on_time_rate' => $onTimeRate,
                'status' => $status,
            ];
        }, $vendorFleet));

        // Fleet Types Distribution
        $fleetDistribution = array_values(array_map(function ($f) use ($formatDur) {
            $avg = $f['count'] > 0 ? round($f['total_min'] / $f['count']) : 0;
            return [
                'jenis' => $f['jenis'],
                'count' => $f['count'],
                'avg_duration_formatted' => $formatDur($avg),
            ];
        }, $fleetTypes));

        // =========================================================================
        // KLUSTER 5: KENDALA & LOG FOLLOW-UP (INCIDENT & EXCEPTIONS)
        // =========================================================================
        $incidentsQuery = VehicleTransaction::with(['vehicle', 'targetLocation'])
            ->where(function ($q) {
                $q->whereNotNull('follow_up_time')
                    ->orWhereNotNull('follow_up_notes')
                    ->orWhereNotNull('follow_up_target');
            })
            ->whereBetween('check_in_time', [$start, $end]);

        $incidents = $incidentsQuery->orderBy('follow_up_time', 'desc')->get();
        $totalIncidents = $incidents->count();

        $activePendingIssues = $incidents->filter(function ($tx) {
            return is_null($tx->check_out_time) && $tx->status !== 'completed';
        })->count();

        $resolvedIncidents = $totalIncidents - $activePendingIssues;

        $incidentList = $incidents->take(15)->map(function ($tx) {
            return [
                'no_antrian' => $tx->no_antrian ?: $tx->no_transaction,
                'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                'vendor' => $tx->vendor ?: '-',
                'target_unit' => $tx->follow_up_target ?: 'PIC Area',
                'notes' => $tx->follow_up_notes ?: 'Pemeriksaan Lanjutan / Tindak Lanjut',
                'incident_time' => $tx->follow_up_time ? $tx->follow_up_time->format('d/m/Y H:i') : ($tx->updated_at ? $tx->updated_at->format('d/m/Y H:i') : '-'),
                'status' => strtoupper($tx->status ?: 'PENDING'),
            ];
        })->values();

        // =========================================================================
        // KLUSTER 6: AUDIT KINERJA OPERATOR (PIC PRODUCTIVITY)
        // =========================================================================
        $operatorWork = [];

        $recordOperatorAction = function ($user, $pos, $durationMin = null) use (&$operatorWork) {
            if (!$user) return;
            $uId = $user->id;
            $uName = $user->name ?: ('User #' . $uId);
            $key = $uId . '_' . $pos;

            if (!isset($operatorWork[$key])) {
                $operatorWork[$key] = [
                    'user_id' => $uId,
                    'user_name' => $uName,
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
            // Pos Antrian
            if ($tx->queueTakenBy) {
                $recordOperatorAction($tx->queueTakenBy, 'Pos Antrian');
            }
            // Sampling QC
            if ($tx->finishSamplingBy || $tx->startSamplingBy) {
                $dur = ($tx->start_sampling_time && $tx->finish_sampling_time) ? $tx->start_sampling_time->diffInMinutes($tx->finish_sampling_time) : null;
                $recordOperatorAction($tx->finishSamplingBy ?: $tx->startSamplingBy, 'QC Lab Sampling', $dur);
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

        // Tracking logs fallback for operators if direct stamps are sparse
        $trackingLogs = VehicleTracking::with(['creator', 'location'])
            ->whereHas('transaction', function ($q) use ($start, $end) {
                $q->whereBetween('check_in_time', [$start, $end]);
            })
            ->whereNotNull('created_by')
            ->get();

        foreach ($trackingLogs as $log) {
            if ($log->creator) {
                $pos = $log->location ? $log->location->name : 'Operasional Lapangan';
                $durMin = $log->duration_seconds ? round(abs($log->duration_seconds) / 60) : null;
                $recordOperatorAction($log->creator, $pos, $durMin);
            }
        }

        uasort($operatorWork, fn($a, $b) => $b['total_tickets'] <=> $a['total_tickets']);

        $operatorList = array_values(array_slice(array_map(function ($op) use ($formatDur) {
            $avg = $op['sample_count'] > 0 ? round($op['total_min'] / $op['sample_count']) : 0;
            return [
                'user_name' => $op['user_name'],
                'pos' => $op['pos'],
                'total_tickets' => $op['total_tickets'],
                'avg_duration_formatted' => $avg > 0 ? $formatDur($avg) : 'Instant / Log',
            ];
        }, $operatorWork), 0, 15));

        // Overall Executive KPIs
        $totalVehicles = $transactions->count();
        $avgTatMin = $tatCount > 0 ? round($totalTATMinutes / $tatCount) : 0;
        $overSlaRate = $tatCount > 0 ? round(($overSlaCount / $tatCount) * 100, 1) : 0;
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
                'health_score' => $healthScore,
                'total_tonnage' => round($totalTonAll, 2),
                'primary_bottleneck_stage' => $primaryBottleneckStage ?: 'N/A',
                'overall_productivity' => $overallProductivityTonPerHour,
                'avg_daily_checkin' => $avgDailyCheckin,
                'total_checkins' => $totalCheckins,
                'total_loading_samples' => $totalLoadingSamples,
                'overall_avg_loading_formatted' => $formatDur($overallAvgLoadingMin),
            ],
            // 1. Analisa Waktu & Bottleneck
            'stages' => $stageResults,
            'primary_bottleneck' => $primaryBottleneckStage ?: 'N/A',
            'max_stage_avg_formatted' => $formatDur($maxStageAvg),

            // 2. Daily Check-in & Rata-rata
            'daily_checkin' => [
                'series' => $dailyList,
                'avg_per_day' => $avgDailyCheckin,
                'total_days' => $totalDailyDays,
                'total_checkins' => $totalCheckins,
            ],

            // 3. Durasi Bongkar / Muat Riil (hanya data dengan timestamp start & finish loading)
            'loading_time' => [
                'bay_list' => $loadingBayList,
                'total_samples' => $totalLoadingSamples,
                'overall_avg_min' => $overallAvgLoadingMin,
                'overall_avg_formatted' => $formatDur($overallAvgLoadingMin),
            ],

            // 4. Frequent Trucks (Armada Paling Sering Berkunjung)
            'frequent_trucks' => $topFrequentTrucks,

            // 5. Analisa Mutu & QC (No HOLD)
            'qc_compliance' => [
                'qc_distribution' => $qcCounts,
                'top_rejections' => $top5RejectVendors,
                'material_durations' => $materialList,
            ],

            // 6. Yard & Loading Bay Throughput
            'capacity_throughput' => [
                'bay_throughput' => $bayList,
                'productivity_ton_per_hour' => $overallProductivityTonPerHour,
                'total_tonnage' => round($totalTonAll, 2),
            ],

            // 7. Vendor TAT & Armada
            'vendor_fleet' => [
                'vendor_tat' => array_slice($vendorTatList, 0, 15),
                'fleet_distribution' => $fleetDistribution,
            ],

            // 8. Kendala & Insiden
            'incidents' => [
                'pending_issues_count' => $activePendingIssues,
                'total_incidents' => $totalIncidents,
                'resolved_incidents' => $resolvedIncidents,
                'incident_list' => $incidentList,
            ],

            // 9. Kinerja Operator
            'operators' => [
                'operator_list' => $operatorList,
            ],

            // Backward Compatibility Aliases
            'cluster_1' => [
                'stages' => $stageResults,
                'primary_bottleneck' => $primaryBottleneckStage ?: 'N/A',
                'max_stage_avg_formatted' => $formatDur($maxStageAvg),
                'daily_checkin' => [
                    'series' => $dailyList,
                    'avg_per_day' => $avgDailyCheckin,
                ],
            ],
            'cluster_2' => [
                'qc_distribution' => $qcCounts,
                'top_rejections' => $top5RejectVendors,
                'material_durations' => $materialList,
            ],
            'cluster_3' => [
                'bay_throughput' => $bayList,
                'loading_time' => $loadingBayList,
                'productivity_ton_per_hour' => $overallProductivityTonPerHour,
                'total_tonnage' => round($totalTonAll, 2),
            ],
            'cluster_4' => [
                'vendor_tat' => array_slice($vendorTatList, 0, 15),
                'fleet_distribution' => $fleetDistribution,
                'frequent_trucks' => $topFrequentTrucks,
            ],
            'cluster_5' => [
                'pending_issues_count' => $activePendingIssues,
                'total_incidents' => $totalIncidents,
                'resolved_incidents' => $resolvedIncidents,
                'incident_list' => $incidentList,
            ],
            'cluster_6' => [
                'operators' => $operatorList,
            ],
        ]);
    }
}
