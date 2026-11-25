<?php

namespace App\Http\Controllers\Vehicle;

use App\Events\VehicleStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Vehicle\Location;
use App\Models\Vehicle\Vehicle;
use App\Models\Vehicle\VehicleItem;
use App\Models\Vehicle\VehicleTracking;
use App\Models\Vehicle\VehicleTransaction;
use App\Models\Vehicle\VehicleVendor;
use App\Models\User;
use App\Models\Wrm\MasterSupplierModel;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VehicleTrackingController extends Controller
{
    /**
     * Display the Real-time Monitoring Dashboard.
     */
    public function dashboard()
    {
        return view('dashboard.vehicle_dashboard');
    }

    /**
     * Display the Animated Visual Map Dashboard.
     */
    public function visualDashboard()
    {
        return view('dashboard.vehicle_visual');
    }

    /**
     * Display the Dedicated Kantong Parkir Dashboard.
     */
    public function parkirDashboard()
    {
        return view('dashboard.vehicle_parkir');
    }

    /**
     * Get real-time data for the dashboard via AJAX.
     */
    public function dashboardData()
    {
        // Catatan: Pengecekan SLA Overdue ditangani secara terpusat oleh Scheduler (routes/console.php)
        // agar tidak memicu notifikasi ganda akibat polling/refresh dashboard web.

        $locations = Location::all();

        // Get raw collection of active transactions
        $txCollection = VehicleTransaction::with(['vehicle', 'item', 'currentLocation', 'targetLocation', 'activeTracking'])
            ->where('status', '!=', 'completed')
            ->get();

        // Map active transactions for rendering
        $activeTransactions = $txCollection->map(function ($tx) {
            // Calculate current duration
            $currentTracking = $tx->activeTracking;

            $durationSeconds = 0;
            $isBottleneck = false;
            $limitMinutes = 0;

            if ($currentTracking) {
                $durationSeconds = abs(Carbon::now()->diffInSeconds($currentTracking->arrival_time, false));
            }

            return [
                'id' => $tx->id,
                'no_transaction' => $tx->no_transaction,
                'no_pol' => $tx->vehicle->no_pol,
                'vendor' => $tx->vendor,
                'nama_driver' => $tx->nama_driver,
                'no_hp_driver' => $tx->no_hp_driver,
                'jenis' => $tx->jenis,
                'item' => $tx->item ? $tx->item->name : 'N/A',
                'sku' => $tx->item ? $tx->item->sku : 'N/A',
                'no_spb' => $tx->no_spb,
                'qty_spb' => $tx->qty_spb,
                'current_location_code' => $tx->currentLocation->s_loc,
                'current_location_name' => $tx->currentLocation->name,
                'target_location_code' => $tx->targetLocation->s_loc,
                'target_location_name' => $tx->targetLocation->name,
                'status' => $tx->status,
                'qc_status' => $tx->qc_status,
                'unloading_status' => $tx->unloading_status,
                'no_antrian' => $tx->no_antrian,
                'queue_taken_time' => $tx->queue_taken_time ? $tx->queue_taken_time->format('Y-m-d H:i:s') : null,
                'start_sampling_time' => $tx->start_sampling_time ? $tx->start_sampling_time->format('Y-m-d H:i:s') : null,
                'finish_sampling_time' => $tx->finish_sampling_time ? $tx->finish_sampling_time->format('Y-m-d H:i:s') : null,
                'start_loading_time' => $tx->start_loading_time ? $tx->start_loading_time->format('Y-m-d H:i:s') : null,
                'finish_loading_time' => $tx->finish_loading_time ? $tx->finish_loading_time->format('Y-m-d H:i:s') : null,
                'timbangan_out_time' => $tx->timbangan_out_time ? $tx->timbangan_out_time->format('Y-m-d H:i:s') : null,
                'follow_up_time' => $tx->follow_up_time ? $tx->follow_up_time->format('Y-m-d H:i:s') : null,
                'check_in_time' => $tx->check_in_time ? $tx->check_in_time->format('Y-m-d H:i:s') : null,
                'arrival_time' => $currentTracking ? $currentTracking->arrival_time->format('Y-m-d H:i:s') : ($tx->check_in_time ? $tx->check_in_time->format('Y-m-d H:i:s') : null),
                'duration_seconds' => $durationSeconds,
                'limit_minutes' => $limitMinutes,
                'is_bottleneck' => $isBottleneck,
            ];
        });

        // Split into queues for the dashboard tables (exclude already completed & timbangan_out)
        $queues = [
            'WPM' => $activeTransactions->filter(function ($tx) {
                return ($tx['status'] === 'wpm' || $tx['target_location_code'] === 'C001')
                    && !in_array($tx['status'], ['timbangan_out', 'completed']);
            })->values(),
            'WRM' => $activeTransactions->filter(function ($tx) {
                return ($tx['status'] === 'wrm_bongkar' || $tx['target_location_code'] === 'B006')
                    && !in_array($tx['status'], ['timbangan_out', 'completed']);
            })->values(),
            'WFG' => $activeTransactions->filter(function ($tx) {
                return ($tx['status'] === 'wfg' || $tx['target_location_code'] === 'A001')
                    && !in_array($tx['status'], ['timbangan_out', 'completed']);
            })->values(),
            'SMU' => $activeTransactions->filter(function ($tx) {
                return ($tx['status'] === 'smu' || in_array($tx['target_location_code'], ['A002', 'SMU']))
                    && !in_array($tx['status'], ['timbangan_out', 'completed']);
            })->values(),
        ];

        // New KPIs Calculations for Gula & Import (Active transactions only)
        $itemKPIs = [
            'gula_tebu' => ['ton' => 0, 'truck' => 0],
            'gula_kelapa' => ['ton' => 0, 'truck' => 0],
            'gula_kelapa_grade_b' => ['ton' => 0, 'truck' => 0],
            'gula_pasir' => ['ton' => 0, 'truck' => 0],
            'import' => ['ton' => 0, 'truck' => 0],
        ];

        foreach ($txCollection as $tx) {
            if ($tx->status === 'completed' || $tx->check_out_time) {
                continue;
            }

            if ($tx->item) {
                $itemName = strtoupper(trim($tx->item->name));
                $rawQty = floatval($tx->qty_spb);
                // Konversi KG ke TON jika nilai >= 100 (misal 10000 kg -> 10 ton)
                $tonValue = $rawQty >= 100 ? ($rawQty / 1000) : $rawQty;

                if (str_contains($itemName, 'GULA KELAPA') && (str_contains($itemName, 'GRADE B') || str_ends_with($itemName, ' B'))) {
                    $itemKPIs['gula_kelapa_grade_b']['ton'] += $tonValue;
                    $itemKPIs['gula_kelapa_grade_b']['truck']++;
                } elseif (str_contains($itemName, 'GULA KELAPA')) {
                    $itemKPIs['gula_kelapa']['ton'] += $tonValue;
                    $itemKPIs['gula_kelapa']['truck']++;
                } elseif (str_contains($itemName, 'GULA TEBU')) {
                    $itemKPIs['gula_tebu']['ton'] += $tonValue;
                    $itemKPIs['gula_tebu']['truck']++;
                } elseif (str_contains($itemName, 'GULA PASIR')) {
                    $itemKPIs['gula_pasir']['ton'] += $tonValue;
                    $itemKPIs['gula_pasir']['truck']++;
                } elseif (str_contains($itemName, 'IMPORT')) {
                    $itemKPIs['import']['ton'] += $tonValue;
                    $itemKPIs['import']['truck']++;
                }
            }
        }

        // Completed transactions today (for "Out" counters)
        $todayCompletedTransactions = VehicleTransaction::with(['targetLocation', 'vehicle', 'item'])
            ->where(function ($q) {
                $q->where('status', 'completed')
                    ->orWhereNotNull('check_out_time');
            })
            ->where(function ($q) {
                $q->whereDate('check_out_time', Carbon::today())
                    ->orWhere(function ($sub) {
                        $sub->whereNull('check_out_time')
                            ->whereDate('updated_at', Carbon::today());
                    });
            })
            ->get();

        $slipsheetIn = $activeTransactions->filter(function ($tx) {
            return strtolower(trim($tx['jenis'] ?? '')) === 'slipsheet'
                && !in_array($tx['status'], ['completed', 'timbangan_out']);
        })->count();
        $slipsheetOut = $todayCompletedTransactions->filter(function ($tx) {
            return strtolower(trim($tx->jenis ?? '')) === 'slipsheet';
        })->count();

        $curahIn = $activeTransactions->filter(function ($tx) {
            return strtolower(trim($tx['jenis'] ?? '')) === 'curah'
                && !in_array($tx['status'], ['completed', 'timbangan_out']);
        })->count();
        $curahOut = $todayCompletedTransactions->filter(function ($tx) {
            return strtolower(trim($tx->jenis ?? '')) === 'curah';
        })->count();

        $smuIn = $queues['SMU']->count();
        $smuOut = $todayCompletedTransactions->filter(function ($tx) {
            $sLoc = $tx->targetLocation->s_loc ?? '';
            $name = strtoupper($tx->targetLocation->name ?? '');
            return in_array($sLoc, ['A002', 'SMU']) || str_contains($name, 'SMU');
        })->count();

        $wpmIn = $queues['WPM']->count();
        $wpmOut = $todayCompletedTransactions->filter(function ($tx) {
            $sLoc = $tx->targetLocation->s_loc ?? '';
            $name = strtoupper($tx->targetLocation->name ?? '');
            return $sLoc === 'C001' || str_contains($name, 'WPM');
        })->count();

        $wrmIn = $queues['WRM']->count();
        $wrmOut = $todayCompletedTransactions->filter(function ($tx) {
            $sLoc = $tx->targetLocation->s_loc ?? '';
            $name = strtoupper($tx->targetLocation->name ?? '');
            return $sLoc === 'B006' || str_contains($name, 'WRM');
        })->count();

        // Area counts
        $counts = [
            'total' => $activeTransactions->count(),
            'wpm' => $queues['WPM']->count(),
            'wrm' => $queues['WRM']->count(),
            'wfg' => $queues['WFG']->count(),
            'smu' => $queues['SMU']->count(),
            'bottlenecks' => 0,

            // New granular counters
            'item_kpis' => $itemKPIs,
            'slipsheet' => ['in' => $slipsheetIn, 'out' => $slipsheetOut],
            'curah' => ['in' => $curahIn, 'out' => $curahOut],
            'smu_details' => ['in' => $smuIn, 'out' => $smuOut],
            'wpm_details' => ['in' => $wpmIn, 'out' => $wpmOut],
            'wrm_details' => ['in' => $wrmIn, 'out' => $wrmOut],
        ];

        // Recent activity feed (last 10 movements)
        $recentActivities = VehicleTracking::with(['transaction.vehicle', 'location'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'no_pol' => $track->transaction->vehicle->no_pol,
                    'location_name' => $track->location->name,
                    'location_code' => $track->location->s_loc,
                    'arrival_time' => $track->arrival_time->format('H:i:s'),
                    'departure_time' => $track->departure_time ? $track->departure_time->format('H:i:s') : null,
                    'duration_seconds' => $track->duration_seconds,
                    'status_notes' => $track->status_notes,
                    'time_ago' => $track->arrival_time->diffForHumans(),
                ];
            });

        $recentCheckouts = $todayCompletedTransactions->sortByDesc('check_out_time')->take(5)->map(function ($tx) {
            return [
                'id' => $tx->id,
                'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : '-',
                'nama_driver' => $tx->nama_driver,
                'vendor' => $tx->vendor,
                'item' => $tx->item ? $tx->item->name : '-',
                'status' => 'completed',
                'check_out_time' => $tx->check_out_time ? $tx->check_out_time->format('Y-m-d H:i:s') : null,
            ];
        })->values();

        return response()->json([
            'queues' => $queues,
            'counts' => $counts,
            'activities' => $recentActivities,
            'transactions' => $activeTransactions,
            'recent_checkouts' => $recentCheckouts
        ]);
    }

    /**
     * Get real-time Parking Pocket (Kantong Parkir) and slot data directly from API.
     */
    public function kantongParkirData(Request $request)
    {
        try {
            $baseUrl = env('MYBAS_API_URL', 'http://127.0.0.1:8081');
            $response = Http::timeout(4)->get(rtrim($baseUrl, '/') . '/api/kantong-parkir');

            if ($response->successful()) {
                $payload = $response->json();
                if (!empty($payload['data']) && count($payload['data']) > 0) {
                    // Normalize slots so nomor_slot, no_pol, and status are consistently accessible
                    $occupiedPlates = [];
                    foreach ($payload['data'] as &$zone) {
                        if (!empty($zone['slots']) && is_array($zone['slots'])) {
                            foreach ($zone['slots'] as $idx => &$slot) {
                                if (!isset($slot['nomor_slot'])) {
                                    $slot['nomor_slot'] = $idx + 1;
                                }
                                if (!empty($slot['active_vehicle'])) {
                                    if (empty($slot['active_vehicle']['no_pol'])) {
                                        $slot['active_vehicle']['no_pol'] = $slot['active_vehicle']['no_polisi'] ?? null;
                                    }
                                    if (empty($slot['active_vehicle']['no_polisi'])) {
                                        $slot['active_vehicle']['no_polisi'] = $slot['active_vehicle']['no_pol'] ?? null;
                                    }
                                    if (!empty($slot['active_vehicle']['no_pol'])) {
                                        $occupiedPlates[] = strtoupper(str_replace(' ', '', $slot['active_vehicle']['no_pol']));
                                    }
                                }
                            }
                        }
                    }
                    unset($zone); // break reference

                    // Sinkronisasi: Pastikan truk di DB Warehouse yang sedang antri/menunggu bongkar-muat
                    // (start_loading_time masih NULL) TETAP muncul di slot parkir meskipun sudah ambil nomor antrian.
                    $waitingVehicles = VehicleTransaction::with(['vehicle', 'item', 'currentLocation', 'targetLocation'])
                        ->where('status', '!=', 'completed')
                        ->where(function ($q) {
                            $q->whereIn('status', ['check_in', 'antri_sampling', 'parkir', 'timbangan_in', 'sampling'])
                                ->orWhere(function ($sub) {
                                    $sub->whereIn('status', ['wfg', 'smu', 'wpm', 'wrm'])
                                        ->whereNull('start_loading_time');
                                });
                        })
                        ->orderBy('id', 'desc')
                        ->get();

                    foreach ($waitingVehicles as $tx) {
                        $plateRaw = $tx->vehicle ? $tx->vehicle->no_pol : null;
                        if (!$plateRaw) continue;
                        $plateClean = strtoupper(str_replace(' ', '', $plateRaw));
                        if (in_array($plateClean, $occupiedPlates)) continue;

                        $st = strtolower($tx->status ?? '');
                        $prefZone = 'BAS01';
                        if ($st === 'wfg' || $st === 'smu') $prefZone = 'BAS04';
                        elseif ($st === 'wrm') $prefZone = 'BAS02';
                        elseif ($st === 'wpm') $prefZone = 'BAS05';

                        $assigned = false;
                        // Coba tempatkan di zona preferensi
                        foreach ($payload['data'] as &$zone) {
                            if (($zone['kode_zona'] ?? '') === $prefZone && !empty($zone['slots'])) {
                                foreach ($zone['slots'] as &$slot) {
                                    if (empty($slot['active_vehicle']) && ($slot['status_slot'] ?? '') !== 'terisi') {
                                        $slot['status_slot'] = 'terisi';
                                        $slot['is_tersedia'] = false;
                                        $slot['active_vehicle'] = [
                                            'id' => $tx->id,
                                            'no_polisi' => $plateRaw,
                                            'no_pol' => $plateRaw,
                                            'nama_driver' => $tx->vehicle->nama_driver ?? 'Driver Logistik',
                                            'no_hp_driver' => $tx->vehicle->no_hp_driver ?? '-',
                                            'jenis_kendaraan' => $tx->vehicle->jenis_truk ?? 'Truk',
                                            'waktu_masuk' => $tx->created_at ? $tx->created_at->format('d-m-Y H:i:s') : Carbon::now()->format('d-m-Y H:i:s'),
                                            'durasi_parkir' => $tx->created_at ? $tx->created_at->diffForHumans() : 'Standby Parkir',
                                            'catatan' => 'Antrian ' . ($tx->no_antrian ?? '-') . ' - Menunggu Panggilan Dock'
                                        ];
                                        $occupiedPlates[] = $plateClean;
                                        $assigned = true;
                                        break;
                                    }
                                }
                            }
                            if ($assigned) break;
                        }
                        unset($zone);

                        // Fallback ke slot kosong manapun jika zona preferensi penuh
                        if (!$assigned) {
                            foreach ($payload['data'] as &$zone) {
                                if (!empty($zone['slots'])) {
                                    foreach ($zone['slots'] as &$slot) {
                                        if (empty($slot['active_vehicle']) && ($slot['status_slot'] ?? '') !== 'terisi') {
                                            $slot['status_slot'] = 'terisi';
                                            $slot['is_tersedia'] = false;
                                            $slot['active_vehicle'] = [
                                                'id' => $tx->id,
                                                'no_polisi' => $plateRaw,
                                                'no_pol' => $plateRaw,
                                                'nama_driver' => $tx->vehicle->nama_driver ?? 'Driver Logistik',
                                                'no_hp_driver' => $tx->vehicle->no_hp_driver ?? '-',
                                                'jenis_kendaraan' => $tx->vehicle->jenis_truk ?? 'Truk',
                                                'waktu_masuk' => $tx->created_at ? $tx->created_at->format('d-m-Y H:i:s') : Carbon::now()->format('d-m-Y H:i:s'),
                                                'durasi_parkir' => $tx->created_at ? $tx->created_at->diffForHumans() : 'Standby Parkir',
                                                'catatan' => 'Antrian ' . ($tx->no_antrian ?? '-') . ' - Menunggu Panggilan Dock'
                                            ];
                                            $occupiedPlates[] = $plateClean;
                                            $assigned = true;
                                            break;
                                        }
                                    }
                                }
                                if ($assigned) break;
                            }
                            unset($zone);
                        }
                    }

                    // Hitung ulang summary zona dan summary_all
                    $totalSlotsAll = 0;
                    $totalTerisiAll = 0;
                    foreach ($payload['data'] as &$zone) {
                        $cap = $zone['summary']['kapasitas_total'] ?? count($zone['slots'] ?? []);
                        $terisi = 0;
                        if (!empty($zone['slots'])) {
                            foreach ($zone['slots'] as $s) {
                                if (!empty($s['active_vehicle']) || ($s['status_slot'] ?? '') === 'terisi') {
                                    $terisi++;
                                }
                            }
                        }
                        $kosong = max(0, $cap - $terisi);
                        $zone['summary']['kapasitas_total'] = $cap;
                        $zone['summary']['slot_terisi'] = $terisi;
                        $zone['summary']['slot_kosong'] = $kosong;
                        $zone['summary']['occupancy_percentage'] = $cap > 0 ? round(($terisi / $cap) * 100, 1) : 0;
                        $totalSlotsAll += $cap;
                        $totalTerisiAll += $terisi;
                    }
                    unset($zone);

                    $payload['summary_all'] = [
                        'total_zona' => count($payload['data']),
                        'total_slot' => $totalSlotsAll,
                        'total_terisi' => $totalTerisiAll,
                        'total_kosong' => max(0, $totalSlotsAll - $totalTerisiAll),
                        'global_occupancy_percentage' => $totalSlotsAll > 0 ? round(($totalTerisiAll / $totalSlotsAll) * 100, 1) : 0
                    ];

                    return response()->json($payload);
                }
            }
        } catch (\Throwable $th) {
            // Log or pass down to fallback
        }

        // Fallback: Generate the 8 internal company parking zones as specified by user
        $fallback = $this->getDefaultKantongParkirData();
        return response()->json($fallback);
    }

    /**
     * Generate standard internal 8-zone parking layout with active vehicle mapping.
     */
    private function getDefaultKantongParkirData()
    {
        $zoneSpecs = [
            ['nama_zona' => 'zona bas01', 'code' => 'BAS01', 'capacity' => 17],
            ['nama_zona' => 'zona bas02', 'code' => 'BAS02', 'capacity' => 7],
            ['nama_zona' => 'zona bas03', 'code' => 'BAS03', 'capacity' => 4],
            ['nama_zona' => 'zona bas04', 'code' => 'BAS04', 'capacity' => 16],
            ['nama_zona' => 'zona bas05', 'code' => 'BAS05', 'capacity' => 5],
            ['nama_zona' => 'zona bas06', 'code' => 'BAS06', 'capacity' => 2],
            ['nama_zona' => 'zona bas07', 'code' => 'BAS07', 'capacity' => 6],
            ['nama_zona' => 'zona bas08', 'code' => 'BAS08', 'capacity' => 3],
        ];

        // Fetch active waiting/checking-in transactions to populate realistic occupied slots
        $waitingVehicles = VehicleTransaction::with(['vehicle', 'item', 'currentLocation', 'targetLocation'])
            ->where('status', '!=', 'completed')
            ->where(function ($q) {
                $q->whereIn('status', ['check_in', 'antri_sampling', 'parkir', 'timbangan_in', 'sampling'])
                    ->orWhere(function ($sub) {
                        $sub->whereIn('status', ['wfg', 'smu', 'wpm', 'wrm'])
                            ->whereNull('start_loading_time');
                    });
            })
            ->orderBy('id', 'desc')
            ->get();

        $vehicleIndex = 0;
        $totalVehicles = $waitingVehicles->count();
        $totalSlots = 0;
        $totalTerisi = 0;

        $zonesData = [];
        $slotGlobalId = 1;

        foreach ($zoneSpecs as $spec) {
            $slots = [];
            $zoneCapacity = $spec['capacity'];
            $totalSlots += $zoneCapacity;
            $zoneTerisi = 0;

            for ($i = 1; $i <= $zoneCapacity; $i++) {
                $slotCode = sprintf('%s-%02d', $spec['code'], $i);
                $isOccupied = false;
                $activeVehicle = null;

                // Assign waiting vehicle if available
                if ($vehicleIndex < $totalVehicles) {
                    $vTx = $waitingVehicles[$vehicleIndex];
                    $isOccupied = true;
                    $activeVehicle = [
                        'id' => $vTx->id,
                        'no_polisi' => $vTx->vehicle ? $vTx->vehicle->no_pol : 'B ' . rand(1000, 9999) . ' BAS',
                        'vendor' => $vTx->vendor ?? 'PT Mitra Logistik',
                        'nama_driver' => $vTx->nama_driver ?? 'Driver ' . $vTx->id,
                        'no_hp_driver' => $vTx->no_hp_driver ?? '-',
                        'item' => $vTx->item ? $vTx->item->name : 'Material Produksi',
                        'no_spb' => $vTx->no_spb ?? '-',
                        'status' => $vTx->status,
                        'target_location' => $vTx->targetLocation ? $vTx->targetLocation->name : 'Gedung Pabrik',
                        'waktu_masuk' => $vTx->check_in_time ? $vTx->check_in_time->format('Y-m-d H:i:s') : Carbon::now()->subMinutes(rand(10, 120))->format('Y-m-d H:i:s'),
                    ];
                    $vehicleIndex++;
                    $zoneTerisi++;
                    $totalTerisi++;
                }

                $slots[] = [
                    'id' => $slotGlobalId++,
                    'kode_slot' => $slotCode,
                    'nomor_slot' => $i,
                    'status_slot' => $isOccupied ? 'terisi' : 'kosong',
                    'active_vehicle' => $activeVehicle,
                ];
            }

            $zonesData[] = [
                'id' => $spec['code'],
                'nama_zona' => $spec['nama_zona'],
                'kode_zona' => $spec['code'],
                'kapasitas' => $zoneCapacity,
                'total_terisi' => $zoneTerisi,
                'total_kosong' => $zoneCapacity - $zoneTerisi,
                'slots' => $slots,
            ];
        }

        $totalKosong = $totalSlots - $totalTerisi;
        $occupancyPct = $totalSlots > 0 ? round(($totalTerisi / $totalSlots) * 100) : 0;

        return [
            'success' => true,
            'source' => 'internal_live_sync',
            'data' => $zonesData,
            'summary_all' => [
                'total_zona' => count($zonesData),
                'total_slot' => $totalSlots,
                'total_terisi' => $totalTerisi,
                'total_kosong' => $totalKosong,
                'global_occupancy_percentage' => $occupancyPct,
            ]
        ];
    }

    /**
     * Release a slot in Kantong Parkir via API proxy to MyBAS.
     */
    public function kantongParkirRelease(Request $request)
    {
        try {
            $baseUrl = env('MYBAS_API_URL', 'http://127.0.0.1:8081');
            $response = Http::timeout(5)->post(rtrim($baseUrl, '/') . '/api/kantong-parkir/release', [
                'no_polisi' => $request->input('no_polisi'),
                'slot_id' => $request->input('slot_id'),
                'keterangan' => $request->input('keterangan') ?? 'Release manual dari warehouse dashboard'
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke API MyBAS: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Internal helper to release vehicle from external Kantong Parkir slot in MyBAS.
     */
    private function releaseKantongParkirSlot($noPol, $keterangan = null)
    {
        try {
            if (!$noPol) return;
            $baseUrl = env('MYBAS_API_URL', 'http://127.0.0.1:8081');
            $url = rtrim($baseUrl, '/') . '/api/kantong-parkir/release';

            Http::timeout(3)->post($url, [
                'no_polisi' => $noPol,
                'keterangan' => $keterangan ?? 'Dipanggil ke dock / antrian warehouse'
            ]);
        } catch (\Throwable $th) {
            Log::warning("Gagal mengirim release parkir ke MyBAS untuk {$noPol}: " . $th->getMessage());
        }
    }

    public function timbanganIndex(Request $request)
    {
        $items = VehicleItem::orderBy('name')->get();
        $targetLocations = Location::where('s_loc', '!=', 'TMB')->get();
        $vendors = VehicleVendor::orderBy('name')->get();

        $todayTransactions = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])
            ->whereNull('check_out_time')
            ->latest()
            ->get();

        return view('vehicle.monitoring.timbangan', compact('items', 'targetLocations', 'vendors', 'todayTransactions'));
    }

    /**
     * Get Timbangan active transaction data via AJAX (not checked out).
     */
    public function timbanganData(Request $request)
    {
        // Self-heal: pastikan transaksi yang sudah memiliki check_out_time berstatus completed
        VehicleTransaction::whereNotNull('check_out_time')
            ->where('status', 'timbangan_out')
            ->update(['status' => 'completed']);

        $transactions = VehicleTransaction::with(['vehicle', 'item', 'targetLocation', 'currentLocation'])
            ->whereNull('check_out_time')
            ->latest()
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'no_transaction' => $tx->no_transaction,
                    'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                    'jenis' => ucfirst($tx->jenis),
                    'jenis_raw' => strtolower($tx->jenis),
                    'item_id' => $tx->item_id,
                    'item_name' => $tx->item ? $tx->item->name : '-',
                    'vendor' => $tx->vendor,
                    'nama_driver' => $tx->nama_driver,
                    'no_hp_driver' => $tx->no_hp_driver,
                    'trnvisitorid' => $tx->trnvisitorid ?? '-',
                    'checkin_pos1' => $tx->checkin_pos1 ? $tx->checkin_pos1->format('d-m-Y H:i') : '-',
                    'checkin_pos1_date' => $tx->checkin_pos1 ? $tx->checkin_pos1->format('d-m-Y') : '-',
                    'checkin_pos1_clock' => $tx->checkin_pos1 ? $tx->checkin_pos1->format('H:i') : '-',
                    'no_spb' => $tx->no_spb ?? '-',
                    'qty_spb' => $tx->qty_spb ? number_format($tx->qty_spb, 2) : '-',
                    'target_loc' => $tx->target_location_id,
                    'target_sloc' => $tx->targetLocation ? $tx->targetLocation->s_loc : '-',
                    'target_name' => $tx->targetLocation ? $tx->targetLocation->name : '-',
                    'current_loc_code' => $tx->currentLocation ? $tx->currentLocation->s_loc : '-',
                    'current_loc_name' => $tx->currentLocation ? $tx->currentLocation->name : '-',
                    'status' => $tx->status,
                    'qc_status' => $tx->qc_status,
                    'unloading_status' => $tx->unloading_status ?? 'pending',
                    'no_antrian' => $tx->no_antrian,
                    'check_in_time' => $tx->check_in_time ? $tx->check_in_time->format('d-m-Y H:i') : '-',
                    'check_out_time' => $tx->check_out_time ? $tx->check_out_time->format('d-m-Y H:i') : '-',
                    'check_in_date' => $tx->check_in_time ? $tx->check_in_time->format('d-m-Y') : '-',
                    'check_in_clock' => $tx->check_in_time ? $tx->check_in_time->format('H:i') : '-',
                    'check_out_date' => $tx->check_out_time ? $tx->check_out_time->format('d-m-Y') : '-',
                    'check_out_clock' => $tx->check_out_time ? $tx->check_out_time->format('H:i') : '-',
                    'start_loading_time' => $tx->start_loading_time ? $tx->start_loading_time->format('H:i') : null,
                    'finish_loading_time' => $tx->finish_loading_time ? $tx->finish_loading_time->format('H:i') : null,
                    'timbangan_out_time' => $tx->timbangan_out_time ? $tx->timbangan_out_time->format('d-m-Y H:i') : '-',
                    'timbangan_out_date' => $tx->timbangan_out_time ? $tx->timbangan_out_time->format('d-m-Y') : '-',
                    'timbangan_out_clock' => $tx->timbangan_out_time ? $tx->timbangan_out_time->format('H:i') : '-',
                    'start_sampling_time' => $tx->start_sampling_time ? $tx->start_sampling_time->format('H:i') : null,
                    'finish_sampling_time' => $tx->finish_sampling_time ? $tx->finish_sampling_time->format('H:i') : null,
                    'follow_up_time' => $tx->follow_up_time ? $tx->follow_up_time->format('H:i') : null,
                    'follow_up_timestamp' => $tx->follow_up_time ? $tx->follow_up_time->timestamp : null,
                    'follow_up_target' => $tx->follow_up_target,
                    'follow_up_notes' => $tx->follow_up_notes,
                ];
            });

        $pendingFollowups = Cache::get('unregistered_vehicle_followups', []);

        return response()->json([
            'transactions' => $transactions,
            'pending_followups' => array_values($pendingFollowups)
        ]);
    }

    /**
     * Show details of a single transaction for editing.
     */
    public function timbanganShow($id)
    {
        try {
            $transaction = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'no_pol' => $transaction->vehicle->no_pol,
                    'jenis' => $transaction->jenis,
                    'target_loc' => $transaction->target_location_id,
                    'item_id' => $transaction->item_id,
                    'vendor' => $transaction->vendor,
                    'nama_driver' => $transaction->nama_driver,
                    'no_hp_driver' => $transaction->no_hp_driver,
                    'trnvisitorid' => $transaction->trnvisitorid,
                    'checkin_pos1' => $transaction->checkin_pos1 ? $transaction->checkin_pos1->format('Y-m-d H:i:s') : null,
                    'no_spb' => $transaction->no_spb,
                    'qty_spb' => $transaction->qty_spb,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data transaksi tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Autocomplete for vehicle search.
     */
    public function autocompleteVehicle(Request $request)
    {
        $search = $request->get('term');
        $vehicles = Vehicle::where('no_pol', 'LIKE', '%' . $search . '%')
            ->take(10)
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'value' => $v->no_pol,
                    'vendor' => $v->vendor,
                ];
            });

        return response()->json($vehicles);
    }

    /**
     * Lookup driver info from previous transactions for a given plate number.
     */
    public function driverLookup(Request $request)
    {
        $nopol = strtoupper(str_replace(' ', '', $request->get('no_pol', '')));
        if (empty($nopol)) {
            return response()->json(['found' => false]);
        }

        $lastTx = VehicleTransaction::whereHas('vehicle', function ($q) use ($nopol) {
            $q->where(DB::raw("REPLACE(UPPER(no_pol), ' ', '')"), $nopol);
        })
            ->whereNotNull('nama_driver')
            ->where('nama_driver', '!=', '')
            ->latest('id')
            ->first();

        if ($lastTx) {
            return response()->json([
                'found' => true,
                'nama_driver' => $lastTx->nama_driver,
                'no_hp_driver' => $lastTx->no_hp_driver,
                'vendor' => $lastTx->vendor
            ]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * Get supplier data from external API.
     */
    public function getSupplierData(Request $request)
    {
        try {
            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->get(env('MYBAS_API_URL') . '/api/supplier-data');

            if ($response->successful()) {
                $payload = $response->json();

                if (isset($payload['success']) && $payload['success'] && isset($payload['data']) && is_array($payload['data'])) {
                    // Fetch plate numbers of vehicles currently in the yard (not completed)
                    $activeNopols = VehicleTransaction::where('status', '!=', 'completed')
                        ->with('vehicle')
                        ->get()
                        ->map(function ($tx) {
                            return $tx->vehicle ? strtoupper(str_replace(' ', '', $tx->vehicle->no_pol)) : null;
                        })
                        ->filter()
                        ->toArray();

                    // Filter out vehicles that are already active in the local tracking system
                    $filteredData = array_values(array_filter($payload['data'], function ($item) use ($activeNopols) {
                        if (!isset($item['nopol'])) {
                            return true;
                        }
                        $cleanNopol = strtoupper(str_replace(' ', '', $item['nopol']));
                        return !in_array($cleanNopol, $activeNopols);
                    }));

                    $payload['data'] = $filteredData;
                    if (isset($payload['count'])) {
                        $payload['count'] = count($filteredData);
                    }
                }

                return response()->json($payload);
            }

            return response()->json([
                'success' => false,
                'message' => 'API returned status code ' . $response->status(),
                'data' => []
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to supplier API: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Find supplier data by vehicle plate number from external API.
     */
    private function findSupplierByNopol(string $noPol): ?array
    {
        try {
            $cleanNoPol = strtoupper(str_replace(' ', '', $noPol));
            if (empty($cleanNoPol)) {
                return null;
            }

            $apiUrl = env('MYBAS_API_URL');
            if (!$apiUrl) {
                return null;
            }

            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->get($apiUrl . '/api/supplier-data');

            if ($response->successful()) {
                $payload = $response->json();
                if (isset($payload['data']) && is_array($payload['data'])) {
                    foreach ($payload['data'] as $item) {
                        if (isset($item['nopol'])) {
                            $itemNopol = strtoupper(str_replace(' ', '', $item['nopol']));
                            if ($itemNopol === $cleanNoPol) {
                                return $item;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to find supplier data by nopol from API: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Store Vehicle Check-In at scales.
     */
    public function timbanganCheckIn(Request $request)
    {
        $request->validate([
            'no_pol' => 'required|string|max:20',
            'vendor' => 'nullable|string|max:100',
            'nama_driver' => 'nullable|string|max:255',
            'no_hp_driver' => 'nullable|string|max:50',
            'checkin_pos1' => 'nullable|date',
            'trnvisitorid' => 'nullable|string|max:100',
            'jenis' => 'required|string|in:bongkaran,slipsheet,curah,retur',
            'item_id' => 'required|exists:vehicle_items,id',
            'no_spb' => 'nullable|string|max:50',
            'qty_spb' => 'nullable|numeric|min:0',
            'target_location_id' => 'required|exists:locations,id',
        ]);

        $noPol = strtoupper(str_replace(' ', '', $request->no_pol));
        $namaDriver = $request->nama_driver;
        $noHpDriver = $request->no_hp_driver;
        $checkinPos1 = $request->checkin_pos1;
        $trnvisitorid = $request->trnvisitorid;
        $vendor = $request->vendor;

        // Cek API supplier jika nama driver atau no hp driver belum terisi
        if (empty($namaDriver) || empty($noHpDriver)) {
            $matchedSupplier = $this->findSupplierByNopol($noPol);
            if ($matchedSupplier) {
                if (empty($namaDriver) && !empty($matchedSupplier['nama_driver'])) {
                    $namaDriver = $matchedSupplier['nama_driver'];
                }
                if (empty($noHpDriver) && !empty($matchedSupplier['no_hp_driver'])) {
                    $noHpDriver = $matchedSupplier['no_hp_driver'];
                }
                if (empty($trnvisitorid) && !empty($matchedSupplier['trnvisitorid'])) {
                    $trnvisitorid = $matchedSupplier['trnvisitorid'];
                }
                if (empty($checkinPos1) && !empty($matchedSupplier['checkin_pos1'])) {
                    $checkinPos1 = $matchedSupplier['checkin_pos1'];
                }
                if (empty($vendor) && !empty($matchedSupplier['nama_perusahaan'])) {
                    $cleanedVendor = trim($matchedSupplier['nama_perusahaan']);
                    if (str_starts_with($cleanedVendor, '- ')) {
                        $cleanedVendor = trim(substr($cleanedVendor, 2));
                    } elseif (str_starts_with($cleanedVendor, '-')) {
                        $cleanedVendor = trim(substr($cleanedVendor, 1));
                    }
                    $vendor = $cleanedVendor;
                }
            }

            // Fallback: cek riwayat transaksi lokal jika masih kosong
            if (empty($namaDriver) || empty($noHpDriver)) {
                $lastTx = VehicleTransaction::whereHas('vehicle', function ($q) use ($noPol) {
                    $q->where(DB::raw("REPLACE(UPPER(no_pol), ' ', '')"), $noPol);
                })
                    ->whereNotNull('nama_driver')
                    ->where('nama_driver', '!=', '')
                    ->latest('id')
                    ->first();

                if ($lastTx) {
                    if (empty($namaDriver)) $namaDriver = $lastTx->nama_driver;
                    if (empty($noHpDriver)) $noHpDriver = $lastTx->no_hp_driver;
                    if (empty($vendor)) $vendor = $lastTx->vendor;
                }
            }
        }

        try {
            DB::beginTransaction();

            // Validasi truk tidak boleh duplikasi jika masih ada transaksi aktif
            // $activeTransaction = VehicleTransaction::whereHas('vehicle', function ($q) use ($noPol) {
            //     $q->where(DB::raw("REPLACE(UPPER(no_pol), ' ', '')"), $noPol);
            // })
            //     ->where(function ($q) {
            //         $q->whereNull('check_out_time')
            //             ->orWhere('status', '!=', 'completed');
            //     })
            //     ->with(['currentLocation', 'targetLocation'])
            //     ->latest()
            //     ->first();

            // if ($activeTransaction) {
            //     $currentLocName = $activeTransaction->currentLocation ? $activeTransaction->currentLocation->name : ($activeTransaction->targetLocation ? $activeTransaction->targetLocation->name : 'Warehouse');
            //     throw new \Exception("Kendaraan dengan No. Polisi {$request->no_pol} masih aktif dalam sistem (No. Transaksi: {$activeTransaction->no_transaction}, Posisi: {$currentLocName}, Status: {$activeTransaction->status}). Harap selesaikan transaksi sebelumnya terlebih dahulu.");
            // }

            // Validate target area based on jenis
            $targetLoc = Location::findOrFail($request->target_location_id);
            $jenis = $request->jenis;
            if (in_array($jenis, ['slipsheet', 'curah'])) {
                if (!in_array($targetLoc->s_loc, ['A001', 'A002', 'B006'])) {
                    throw new \Exception('Untuk jenis slipsheet atau curah, hanya boleh memilih tujuan area WFG (A001), SMU, atau WRM (B006).');
                }
            } elseif ($jenis === 'retur') {
                if (!in_array($targetLoc->s_loc, ['B006', 'C001'])) {
                    throw new \Exception('Untuk jenis retur, hanya boleh memilih tujuan area WRM (B006) atau WPM (C001).');
                }
            }

            $vendorName = trim($vendor ?? '');
            if ($vendorName) {
                VehicleVendor::firstOrCreate(['name' => $vendorName]);
            }

            // Find or create vehicle
            $vehicle = Vehicle::updateOrCreate(
                ['no_pol' => $noPol],
                [
                    'vendor' => $vendorName ?: $vendor
                ]
            );

            // Generate transaction number: TRX-YYYYMMDD-XXXX
            $datePrefix = Carbon::now()->format('Ymd');
            $maxTransaction = VehicleTransaction::where('no_transaction', 'LIKE', 'VHC-' . $datePrefix . '-%')
                ->orderBy('no_transaction', 'desc')
                ->first();

            if ($maxTransaction) {
                $parts = explode('-', $maxTransaction->no_transaction);
                $lastSequence = intval(end($parts));
                $sequenceNum = $lastSequence + 1;
            } else {
                $sequenceNum = 1;
            }

            $sequence = str_pad($sequenceNum, 4, '0', STR_PAD_LEFT);
            $noTransaction = 'VHC-' . $datePrefix . '-' . $sequence;

            $timbanganLoc = Location::where('s_loc', 'TMB')->first();
            if (!$timbanganLoc) {
                throw new \Exception('Lokasi TIMBANGAN tidak ditemukan di database.');
            }

            // 1. Create Transaction at TIMBANGAN
            $transaction = VehicleTransaction::create([
                'no_transaction' => $noTransaction,
                'trnvisitorid' => $trnvisitorid,
                'vehicle_id' => $vehicle->id,
                'jenis' => $request->jenis,
                'vendor' => $vendorName ?: ($vendor ?? $vehicle->vendor),
                'nama_driver' => $namaDriver,
                'no_hp_driver' => $noHpDriver,
                'checkin_pos1' => $checkinPos1,
                'item_id' => $request->item_id,
                'no_spb' => $request->no_spb,
                'qty_spb' => $request->qty_spb,
                'target_location_id' => $request->target_location_id,
                'current_location_id' => $timbanganLoc->id,
                'status' => 'timbangan_in',
                'check_in_time' => Carbon::now(),
                'created_by' => Auth::id(),
            ]);

            // 2. Create Initial Tracking Log for Timbangan
            $timbanganTrack = VehicleTracking::create([
                'vehicle_transaction_id' => $transaction->id,
                'location_id' => $timbanganLoc->id,
                'arrival_time' => Carbon::now(),
                'created_by' => Auth::id(),
            ]);

            // Broadcast check-in
            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'TIMBANGAN',
                'status' => 'timbangan_in',
                'message' => "Truk {$noPol} telah Check-In di Timbangan.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            // 3. Immediately dispatch vehicle to target queue (departure from Timbangan)

            // Conclude Timbangan tracking
            $timbanganTrack->update([
                'departure_time' => Carbon::now(),
                'duration_seconds' => abs(Carbon::now()->diffInSeconds($timbanganTrack->arrival_time, false)),
                'status_notes' => 'Timbangan Masuk Selesai. Menuju ' . $targetLoc->name
            ]);

            // Map target location to transaction status
            $newStatus = 'smu';
            $initialQcStatus = 'not_required';
            $currentLocId = $targetLoc->id;

            if ($targetLoc->s_loc === 'C001') {
                $newStatus = 'wpm';
                $initialQcStatus = ($jenis === 'retur') ? 'not_required' : 'waiting_dokumen';
            } elseif ($targetLoc->s_loc === 'B006') {
                $newStatus = 'wrm_bongkar';
                $initialQcStatus = ($jenis === 'retur') ? 'not_required' : 'waiting_dokumen';
            } elseif ($targetLoc->s_loc === 'A001') {
                $newStatus = 'wfg';
                $initialQcStatus = 'not_required';
            }

            // Update transaction to target location
            $transaction->update([
                'current_location_id' => $currentLocId,
                'status' => $newStatus,
                'qc_status' => $initialQcStatus,
                'unloading_status' => 'pending'
            ]);

            // Create new tracking log for target location
            VehicleTracking::create([
                'vehicle_transaction_id' => $transaction->id,
                'location_id' => $currentLocId,
                'arrival_time' => Carbon::now(),
                'created_by' => Auth::id(),
            ]);

            $targetMsg = ($jenis !== 'retur' && in_array($targetLoc->s_loc, ['B006', 'C001']))
                ? "QC (" . $targetLoc->name . ")"
                : $targetLoc->name;

            // Broadcast movement
            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $targetLoc->s_loc,
                'status' => $newStatus,
                'message' => "Truk {$noPol} diarahkan dari Timbangan menuju {$targetMsg}.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            // Clear from pending follow up cache if exists
            $followUpList = Cache::get('unregistered_vehicle_followups', []);
            if (!empty($followUpList)) {
                $filteredFollowUps = array_values(array_filter($followUpList, function ($item) use ($noPol) {
                    return strtoupper(str_replace(' ', '', $item['no_pol'] ?? '')) !== $noPol;
                }));
                Cache::put('unregistered_vehicle_followups', $filteredFollowUps, 86400);
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Truk berhasil Check-In dan diarahkan ke ' . $targetLoc->name,
                    'vendors' => VehicleVendor::orderBy('name')->get()
                ]);
            }
            return redirect()->route('vehicle.monitoring.timbangan')->with('success', 'Truk berhasil Check-In dan diarahkan ke ' . $targetLoc->name);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal Check-In: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->withInput()->with('error', 'Gagal Check-In: ' . $e->getMessage());
        }
    }

    public function wpmIndex()
    {
        $wpmLoc = Location::where('s_loc', 'C001')->first();
        $items = VehicleItem::where(function ($q) use ($wpmLoc) {
            if ($wpmLoc) {
                $q->where('location_id', $wpmLoc->id);
            }
        })->orWhereNull('location_id')->orderBy('name')->get();

        $vendors = VehicleVendor::orderBy('name')->get();
        return view('vehicle.monitoring.wpm', compact('vendors', 'items'));
    }

    /**
     * Get JSON data for WPM Unloading Area.
     */
    public function wpmData()
    {
        $queue = VehicleTransaction::with(['vehicle', 'item', 'activeTracking', 'targetLocation'])
            ->where(function ($q) {
                $q->where('status', 'wpm')
                    ->orWhereHas('targetLocation', function ($tl) {
                        $tl->where('s_loc', 'C001');
                    });
            })
            ->whereNotIn('status', ['completed', 'timbangan_out'])
            ->orderBy('check_in_time', 'asc')
            ->get()
            ->map(function ($tx) {
                $tracking = $tx->activeTracking;
                $arrivalTime = $tracking ? $tracking->arrival_time : $tx->check_in_time;
                $actionName = $tx->jenis === 'bongkaran' ? 'Bongkar' : 'Muat';
                $isOvernight = $tx->check_in_time ? $tx->check_in_time->lt(Carbon::today()) : false;

                return [
                    'id' => $tx->id,
                    'is_overnight' => $isOvernight,
                    'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                    'vendor' => $tx->vendor,
                    'nama_driver' => $tx->nama_driver,
                    'no_hp_driver' => $tx->no_hp_driver,
                    'jenis' => $tx->jenis,
                    'action_label' => $actionName,
                    'item_name' => $tx->item ? $tx->item->name : 'N/A',
                    'no_spb' => $tx->no_spb ?? '-',
                    'qty_spb' => $tx->qty_spb ? number_format($tx->qty_spb, 2) : '-',
                    'qc_status' => $tx->qc_status,
                    'unloading_status' => $tx->unloading_status ?? 'pending',
                    'arrival_time' => $arrivalTime ? $arrivalTime->format('d-m-Y H:i') : '-',
                    'arrival_timestamp' => $arrivalTime ? $arrivalTime->timestamp : null,
                    'start_loading_time' => $tx->start_loading_time ? $tx->start_loading_time->format('H:i') : null,
                    'start_loading_timestamp' => $tx->start_loading_time ? $tx->start_loading_time->timestamp : null,
                    'finish_loading_time' => $tx->finish_loading_time ? $tx->finish_loading_time->format('H:i') : null,
                    'finish_loading_timestamp' => $tx->finish_loading_time ? $tx->finish_loading_time->timestamp : null,
                    'follow_up_time' => $tx->follow_up_time ? $tx->follow_up_time->format('H:i') : null,
                    'follow_up_timestamp' => $tx->follow_up_time ? $tx->follow_up_time->timestamp : null,
                    'follow_up_target' => $tx->follow_up_target,
                    'follow_up_notes' => $tx->follow_up_notes,
                    'target_sloc' => $tx->targetLocation ? $tx->targetLocation->s_loc : null,
                    'sloc' => $tx->targetLocation ? $tx->targetLocation->s_loc : null,
                ];
            });

        return response()->json([
            'queue' => $queue
        ]);
    }

    /**
     * Start WPM unloading/loading process.
     */
    public function wpmStartLoading(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));
            $actionName = $transaction->jenis === 'bongkaran' ? 'Bongkar' : 'Muat';
            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            $transaction->update([
                'unloading_status' => 'process',
                'start_loading_time' => $transaction->start_loading_time ?? Carbon::now(),
                'start_loading_by' => $transaction->start_loading_by ?? Auth::id(),
                'status' => 'wpm',
                'updated_by' => Auth::id()
            ]);

            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            if ($activeTrack) {
                $activeTrack->update([
                    'status_notes' => "Mulai Proses {$actionName} di WPM."
                ]);
            }

            // Pastikan ter-release dari slot parkir
            $this->releaseKantongParkirSlot($noPol, "Mulai proses {$actionName} di WPM");

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'C001',
                'status' => 'wpm',
                'message' => "Truk {$noPol} mulai proses {$actionName} di WPM.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Proses {$actionName} untuk truk {$noPol} berhasil dimulai."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memulai proses: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Complete WPM unloading/loading activity.
     */
    public function wpmComplete(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));
            $actionName = $transaction->jenis === 'bongkaran' ? 'Bongkar' : 'Muat';

            // Conclude WPM tracking
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            $now = Carbon::now();
            $duration = $activeTrack ? abs($now->diffInSeconds($activeTrack->arrival_time, false)) : 0;

            if ($activeTrack) {
                $activeTrack->update([
                    'departure_time' => $now,
                    'duration_seconds' => $duration,
                    'status_notes' => "Aktivitas {$actionName} WPM Selesai. Truk kembali ke Timbangan."
                ]);
            }

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            $timbanganLoc = Location::where('s_loc', 'TMB')->first();
            if (!$timbanganLoc) {
                throw new \Exception('Lokasi TIMBANGAN tidak ditemukan di database.');
            }

            // Update transaction to timbangan_out (hanya jika belum checkout/completed)
            $isAlreadyCompleted = ($transaction->status === 'completed' || !empty($transaction->check_out_time));

            $updateData = [
                'unloading_status' => 'completed',
                'finish_loading_time' => $transaction->finish_loading_time ?? $now,
                'finish_loading_by' => $transaction->finish_loading_by ?? Auth::id(),
                'updated_by' => Auth::id()
            ];

            if (!$isAlreadyCompleted) {
                $updateData['timbangan_out_time'] = $transaction->timbangan_out_time ?? $now;
                $updateData['timbangan_out_by'] = $transaction->timbangan_out_by ?? Auth::id();
                $updateData['current_location_id'] = $timbanganLoc->id;
                $updateData['status'] = 'timbangan_out';
            }

            $transaction->update($updateData);

            if (!$isAlreadyCompleted) {
                // Create new tracking log for Timbangan
                VehicleTracking::create([
                    'vehicle_transaction_id' => $transaction->id,
                    'location_id' => $timbanganLoc->id,
                    'arrival_time' => $now,
                    'created_by' => Auth::id(),
                ]);
            }

            $broadcastStatus = $isAlreadyCompleted ? 'completed' : 'timbangan_out';
            $broadcastLoc = $isAlreadyCompleted ? ($transaction->currentLocation ? $transaction->currentLocation->s_loc : 'TIMBANGAN') : 'TIMBANGAN';
            $broadcastMsg = $isAlreadyCompleted
                ? "Proses {$actionName} Truk {$noPol} di WPM telah selesai."
                : "Proses {$actionName} Truk {$noPol} di WPM telah selesai. Truk kembali ke Timbangan untuk Check-Out.";

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $broadcastLoc,
                'status' => $broadcastStatus,
                'message' => $broadcastMsg,
                'time' => $now->format('H:i:s')
            ]));

            DB::commit();

            // Notifikasi ke supir bahwa proses di WPM telah selesai
            $this->notifyDriverCompleted($transaction, 'WPM');

            // Pemanggilan otomatis truk antrian berikutnya ke dock WPM
            $this->notifyNextDriverToEnterDock($transaction, 'WPM');

            return response()->json(['success' => true, 'message' => 'Truk ' . $noPol . ' selesai di WPM. Diarahkan kembali ke Timbangan untuk Check-Out.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyelesaikan proses di WPM: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cancel WPM loading/unloading process.
     */
    public function wpmCancelLoading(Request $request, $id)
    {
        return $this->processCancelLoading($request, $id, 'C001', 'WPM');
    }

    /**
     * Helper to reorder and compact QC queue numbers (Deprecated: kini menggunakan model tiket tetap).
     */
    private function reorderQcQueue()
    {
        // No-op: Nomor antrian kini permanen (Model Tiket RS)
    }

    /**
     * QC Area View.
     */
    public function qcIndex()
    {
        return view('vehicle.monitoring.qc');
    }

    /**
     * Get JSON data for QC Area.
     */
    public function qcData()
    {
        $queue = VehicleTransaction::with(['vehicle', 'item', 'targetLocation', 'activeTracking'])
            ->whereIn('qc_status', ['waiting_dokumen', 'waiting_sampling', 'on_check'])
            ->orderByRaw('CASE WHEN no_antrian IS NULL THEN 1 ELSE 0 END, COALESCE(DATE(queue_taken_time), DATE(created_at)) ASC, CAST(no_antrian AS UNSIGNED) ASC, check_in_time ASC')
            ->get()
            ->map(function ($tx) {
                $tracking = $tx->activeTracking;
                $arrivalTime = $tracking ? $tracking->arrival_time : $tx->check_in_time;

                $statusLabel = match ($tx->status) {
                    'completed' => 'Selesai / Check-Out',
                    'timbangan_out' => 'Timbangan Out',
                    'wrm_bongkar' => 'WRM (Bongkar)',
                    'wpm' => 'WPM',
                    'wfg' => 'WFG',
                    'smu' => 'SMU',
                    'sampling' => 'Proses Sampling',
                    'antri_sampling' => 'Antri Sampling',
                    default => ucfirst(str_replace('_', ' ', $tx->status ?? '-'))
                };

                $isOvernight = $tx->queue_taken_time ? $tx->queue_taken_time->lt(Carbon::today()) : ($tx->created_at ? $tx->created_at->lt(Carbon::today()) : false);

                return [
                    'id' => $tx->id,
                    'no_antrian' => $tx->no_antrian,
                    'is_overnight' => $isOvernight,
                    'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                    'vendor' => $tx->vendor,
                    'nama_driver' => $tx->nama_driver,
                    'no_hp_driver' => $tx->no_hp_driver,
                    'lokasi_tujuan' => $tx->targetLocation ? $tx->targetLocation->s_loc : 'N/A',
                    'lokasi_tujuan_name' => $tx->targetLocation ? $tx->targetLocation->name : 'N/A',
                    'no_spb' => $tx->no_spb ?? '-',
                    'qty_spb' => $tx->qty_spb ? number_format($tx->qty_spb, 2) : '-',
                    'item_name' => $tx->item ? $tx->item->name : 'N/A',
                    'jenis' => $tx->jenis,
                    'qc_status' => $tx->qc_status,
                    'status' => $tx->status,
                    'status_label' => $statusLabel,
                    'unloading_status' => $tx->unloading_status ?? 'pending',
                    'start_loading_time' => $tx->start_loading_time ? $tx->start_loading_time->format('H:i') : null,
                    'finish_loading_time' => $tx->finish_loading_time ? $tx->finish_loading_time->format('H:i') : null,
                    'arrival_time' => $arrivalTime ? $arrivalTime->format('d-m-Y H:i') : '-',
                    'arrival_timestamp' => $arrivalTime ? $arrivalTime->timestamp : null,
                    'queue_taken_time' => $tx->queue_taken_time ? $tx->queue_taken_time->format('H:i') : null,
                    'queue_taken_timestamp' => $tx->queue_taken_time ? $tx->queue_taken_time->timestamp : null,
                    'start_sampling_time' => $tx->start_sampling_time ? $tx->start_sampling_time->format('H:i') : null,
                    'start_sampling_timestamp' => $tx->start_sampling_time ? $tx->start_sampling_time->timestamp : null,
                    'finish_sampling_time' => $tx->finish_sampling_time ? $tx->finish_sampling_time->format('H:i') : null,
                    'finish_sampling_timestamp' => $tx->finish_sampling_time ? $tx->finish_sampling_time->timestamp : null,
                    'follow_up_time' => $tx->follow_up_time ? $tx->follow_up_time->format('H:i') : null,
                    'follow_up_timestamp' => $tx->follow_up_time ? $tx->follow_up_time->timestamp : null,
                    'follow_up_target' => $tx->follow_up_target,
                    'follow_up_notes' => $tx->follow_up_notes,
                    'target_sloc' => $tx->targetLocation ? $tx->targetLocation->s_loc : null,
                    'sloc' => $tx->targetLocation ? $tx->targetLocation->s_loc : null,
                ];
            });

        return response()->json([
            'queue' => $queue,
            'antriSampling' => $queue->whereNull('no_antrian')->values(),
            'prosesSample' => $queue->whereNotNull('no_antrian')->values()
        ]);
    }

    /**
     * Update Queue Number for QC Area (Ambil Antrian).
     */
    public function qcUpdateQueueNumber(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));

            $today = Carbon::today();
            $maxAntrian = VehicleTransaction::whereNotNull('no_antrian')
                ->where(function ($q) use ($today) {
                    $q->whereDate('queue_taken_time', $today)
                        ->orWhere(function ($q2) use ($today) {
                            $q2->whereNull('queue_taken_time')
                                ->whereDate('created_at', $today);
                        });
                })
                ->where('id', '!=', $transaction->id)
                ->get()
                ->map(function ($tx) {
                    return (int)$tx->no_antrian;
                })
                ->max();

            $nextAntrian = $maxAntrian ? $maxAntrian + 1 : 1;
            $formattedAntrian = str_pad($nextAntrian, 2, '0', STR_PAD_LEFT);

            // Update transaction to waiting sampling status
            $transaction->update([
                'no_antrian' => $formattedAntrian,
                'queue_taken_time' => $transaction->queue_taken_time ?? Carbon::now(),
                'queue_taken_by' => $transaction->queue_taken_by ?? Auth::id(),
                'qc_status' => 'waiting_sampling',
                'updated_by' => Auth::id()
            ]);

            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            if ($activeTrack) {
                $activeTrack->update([
                    'status_notes' => 'Dokumen diterima. No Antrian QC: ' . $formattedAntrian . '. Menunggu Mulai Sampling.'
                ]);
            }

            // Broadcast movement/update
            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';
            $targetLoc = $transaction->targetLocation;
            $sLoc = $targetLoc ? $targetLoc->s_loc : 'QC';

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $sLoc,
                'status' => $transaction->status,
                'message' => "Truk {$noPol} dokumen telah diterima. No Antrian: {$formattedAntrian}. Menunggu Sampling QC.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();

            // Poin 4: Notifikasi untuk barang pengecualian ('Packaging', 'Import') langsung ke area dock terkait
            $this->notifyDriverDirectToDockIfException($transaction);

            return response()->json(['success' => true, 'message' => "No Antrian {$formattedAntrian} berhasil diset otomatis. Kendaraan masuk antrian sampling."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate No Antrian: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cancel Queue Number for QC Area.
     */
    public function qcCancelQueueNumber(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));

            // Validasi: jika proses sampling sudah dimulai, antrian tidak dapat dibatalkan
            if ($transaction->qc_status === 'on_check' || !empty($transaction->start_sampling_time)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Antrian tidak dapat dibatalkan karena proses sampling QC sudah berjalan.'
                ], 422);
            }

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';
            $oldAntrian = $transaction->no_antrian;

            // Reset antrian dan kembalikan qc_status ke waiting_dokumen
            $transaction->update([
                'no_antrian' => null,
                'queue_taken_time' => null,
                'queue_taken_by' => null,
                'qc_status' => 'waiting_dokumen',
                'updated_by' => Auth::id()
            ]);

            // Update status log catatan
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            if ($activeTrack) {
                $activeTrack->update([
                    'status_notes' => 'Nomor antrian QC (#' . $oldAntrian . ') dibatalkan. Menunggu antrian.'
                ]);
            }

            $targetLoc = $transaction->targetLocation;
            $sLoc = $targetLoc ? $targetLoc->s_loc : 'QC';

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $sLoc,
                'status' => $transaction->status,
                'message' => "Nomor antrian QC untuk Truk {$noPol} telah dibatalkan.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Nomor antrian QC untuk Truk {$noPol} berhasil dibatalkan."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan nomor antrian QC: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start QC Sampling (Mulai Sampling).
     */
    public function qcStartSampling(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));
            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            // QC hanya mengubah qc_status, tidak merubah status kendaraan
            $updateData = [
                'qc_status' => 'on_check',
                'start_sampling_time' => $transaction->start_sampling_time ?? Carbon::now(),
                'start_sampling_by' => $transaction->start_sampling_by ?? Auth::id(),
                'updated_by' => Auth::id()
            ];

            $transaction->update($updateData);

            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            if ($activeTrack) {
                $activeTrack->update([
                    'status_notes' => "Mulai Proses Sampling QC."
                ]);
            }

            // Rilis dari slot kantong parkir
            $this->releaseKantongParkirSlot($noPol, "Mulai proses sampling QC");

            $targetLoc = $transaction->targetLocation;
            $sLoc = $targetLoc ? $targetLoc->s_loc : 'QC';

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $sLoc,
                'status' => $transaction->status,
                'qc_status' => 'on_check',
                'message' => "Truk {$noPol} mulai proses sampling QC.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Proses sampling untuk truk {$noPol} berhasil dimulai."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memulai sampling: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update QC Sample Status in QC Area (Release / Reject).
     */
    public function qcUpdateQC(Request $request, $id)
    {
        $request->validate([
            'qc_status' => 'required|in:released,rejected',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));
            $now = Carbon::now();

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            // Release kantong parkir slot if any
            $actionLabel = $request->qc_status === 'released' ? 'QC Lolos (Released)' : 'QC Ditolak (Rejected)';
            $this->releaseKantongParkirSlot($noPol, $actionLabel);

            // Update QC lifecycle data only (tidak merubah status pergerakan fisik kendaraan)
            $updateData = [
                'qc_status' => $request->qc_status,
                'start_sampling_time' => $transaction->start_sampling_time ?? $now,
                'start_sampling_by' => $transaction->start_sampling_by ?? Auth::id(),
                'finish_sampling_time' => $transaction->finish_sampling_time ?? $now,
                'finish_sampling_by' => Auth::id(),
                'updated_by' => Auth::id()
            ];

            $transaction->update($updateData);

            // Update status note in active tracking log if exists
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            if ($activeTrack) {
                $qcResultLabel = strtoupper($request->qc_status);
                $noteText = "QC Hasil: {$qcResultLabel}." . ($request->notes ? " Catatan: {$request->notes}" : '');
                $activeTrack->update([
                    'status_notes' => $noteText
                ]);
            }

            $currentLocCode = $transaction->currentLocation ? $transaction->currentLocation->s_loc : 'QC';
            $msgLabel = ($request->qc_status === 'released') ? 'RELEASED' : 'REJECTED';

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $currentLocCode,
                'status' => $transaction->status,
                'qc_status' => $request->qc_status,
                'message' => "Hasil QC Truk {$noPol}: {$msgLabel}." . ($request->notes ? " Catatan: {$request->notes}" : ''),
                'time' => $now->format('H:i:s')
            ]));

            $msg = 'Status QC Truk ' . $noPol . ' diperbarui ke ' . $msgLabel . '.';

            DB::commit();

            // Poin 2 & 3: Notifikasi Supir jika QC Released (tidak berjalan jika Packaging/Import atau sudah mulai bongkar/checkout)
            if ($request->qc_status === 'released') {
                $this->notifyDriverQcReleased($transaction);
            }

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui QC: ' . $e->getMessage()], 500);
        }
    }

    /**
     * WRM Unloading View.
     */
    public function wrmIndex()
    {
        $wrmLoc = Location::where('s_loc', 'B006')->first();
        $items = VehicleItem::where(function ($q) use ($wrmLoc) {
            if ($wrmLoc) {
                $q->where('location_id', $wrmLoc->id);
            }
        })->orWhereNull('location_id')->orderBy('name')->get();

        $vendors = VehicleVendor::orderBy('name')->get();
        return view('vehicle.monitoring.wrm', compact('vendors', 'items'));
    }

    /**
     * Get JSON data for WRM Unloading Area.
     */
    public function wrmData()
    {
        $queue = VehicleTransaction::with(['vehicle', 'item', 'activeTracking', 'targetLocation'])
            ->where(function ($q) {
                $q->where('status', 'wrm_bongkar')
                    ->orWhereHas('targetLocation', function ($tl) {
                        $tl->where('s_loc', 'B006');
                    });
            })
            ->whereNotIn('status', ['completed', 'timbangan_out'])
            ->orderBy('check_in_time', 'asc')
            ->get()
            ->map(function ($tx) {
                $tracking = $tx->activeTracking;
                $arrivalTime = $tracking ? $tracking->arrival_time : $tx->check_in_time;
                $actionName = $tx->jenis === 'bongkaran' ? 'Bongkar' : 'Muat';
                $isOvernight = $tx->check_in_time ? $tx->check_in_time->lt(Carbon::today()) : false;

                return [
                    'id' => $tx->id,
                    'is_overnight' => $isOvernight,
                    'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                    'vendor' => $tx->vendor,
                    'nama_driver' => $tx->nama_driver,
                    'no_hp_driver' => $tx->no_hp_driver,
                    'jenis' => $tx->jenis,
                    'action_label' => $actionName,
                    'item_name' => $tx->item ? $tx->item->name : 'N/A',
                    'no_spb' => $tx->no_spb ?? '-',
                    'qty_spb' => $tx->qty_spb ? number_format($tx->qty_spb, 2) : '-',
                    'qc_status' => $tx->qc_status,
                    'unloading_status' => $tx->unloading_status ?? 'pending',
                    'arrival_time' => $arrivalTime ? $arrivalTime->format('d-m-Y H:i') : '-',
                    'arrival_timestamp' => $arrivalTime ? $arrivalTime->timestamp : null,
                    'start_loading_time' => $tx->start_loading_time ? $tx->start_loading_time->format('H:i') : null,
                    'start_loading_timestamp' => $tx->start_loading_time ? $tx->start_loading_time->timestamp : null,
                    'finish_loading_time' => $tx->finish_loading_time ? $tx->finish_loading_time->format('H:i') : null,
                    'finish_loading_timestamp' => $tx->finish_loading_time ? $tx->finish_loading_time->timestamp : null,
                    'follow_up_time' => $tx->follow_up_time ? $tx->follow_up_time->format('H:i') : null,
                    'follow_up_timestamp' => $tx->follow_up_time ? $tx->follow_up_time->timestamp : null,
                    'follow_up_target' => $tx->follow_up_target,
                    'follow_up_notes' => $tx->follow_up_notes,
                    'target_sloc' => $tx->targetLocation ? $tx->targetLocation->s_loc : null,
                    'sloc' => $tx->targetLocation ? $tx->targetLocation->s_loc : null,
                ];
            });

        return response()->json([
            'queue' => $queue
        ]);
    }

    /**
     * Start WRM loading/unloading process.
     */
    public function wrmStartLoading(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));
            $actionName = $transaction->jenis === 'bongkaran' ? 'Bongkar' : 'Muat';
            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            $transaction->update([
                'unloading_status' => 'process',
                'start_loading_time' => $transaction->start_loading_time ?? Carbon::now(),
                'start_loading_by' => $transaction->start_loading_by ?? Auth::id(),
                'status' => 'wrm_bongkar',
                'updated_by' => Auth::id()
            ]);

            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            if ($activeTrack) {
                $activeTrack->update([
                    'status_notes' => "Mulai Proses {$actionName} di WRM."
                ]);
            }

            // Pastikan ter-release dari slot parkir
            $this->releaseKantongParkirSlot($noPol, "Mulai proses {$actionName} di WRM");

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'B006',
                'status' => 'wrm_bongkar',
                'message' => "Truk {$noPol} mulai proses {$actionName} di WRM.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Proses {$actionName} untuk truk {$noPol} berhasil dimulai."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memulai proses: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Finish WRM Unloading/Loading.
     */
    public function wrmUpdateUnloading(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));
            $actionName = $transaction->jenis === 'bongkaran' ? 'Bongkar' : 'Muat';
            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            // Conclude WRM tracking
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            $now = Carbon::now();
            $duration = $activeTrack ? abs($now->diffInSeconds($activeTrack->arrival_time, false)) : 0;

            if ($activeTrack) {
                $activeTrack->update([
                    'departure_time' => $now,
                    'duration_seconds' => $duration,
                    'status_notes' => "Proses {$actionName} Selesai. Truk kembali ke Timbangan."
                ]);
            }

            $timbanganLoc = Location::where('s_loc', 'TMB')->first();
            if (!$timbanganLoc) {
                throw new \Exception('Lokasi TIMBANGAN tidak ditemukan di database.');
            }

            // Update transaction to timbangan_out (hanya jika belum checkout/completed)
            $isAlreadyCompleted = ($transaction->status === 'completed' || !empty($transaction->check_out_time));

            $updateData = [
                'unloading_status' => 'completed',
                'finish_loading_time' => $transaction->finish_loading_time ?? $now,
                'finish_loading_by' => $transaction->finish_loading_by ?? Auth::id(),
                'updated_by' => Auth::id()
            ];

            if (!$isAlreadyCompleted) {
                $updateData['timbangan_out_time'] = $transaction->timbangan_out_time ?? $now;
                $updateData['timbangan_out_by'] = $transaction->timbangan_out_by ?? Auth::id();
                $updateData['current_location_id'] = $timbanganLoc->id;
                $updateData['status'] = 'timbangan_out';
            }

            $transaction->update($updateData);

            if (!$isAlreadyCompleted) {
                // Create new tracking log for Timbangan
                VehicleTracking::create([
                    'vehicle_transaction_id' => $transaction->id,
                    'location_id' => $timbanganLoc->id,
                    'arrival_time' => $now,
                    'created_by' => Auth::id(),
                ]);
            }

            $broadcastStatus = $isAlreadyCompleted ? 'completed' : 'timbangan_out';
            $broadcastLoc = $isAlreadyCompleted ? ($transaction->currentLocation ? $transaction->currentLocation->s_loc : 'TIMBANGAN') : 'TIMBANGAN';
            $broadcastMsg = $isAlreadyCompleted
                ? "Proses {$actionName} Truk {$noPol} di WRM telah selesai."
                : "Proses {$actionName} Truk {$noPol} di WRM telah selesai. Truk kembali ke Timbangan untuk Check-Out.";

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $broadcastLoc,
                'status' => $broadcastStatus,
                'message' => $broadcastMsg,
                'time' => $now->format('H:i:s')
            ]));

            DB::commit();

            // Notifikasi ke supir bahwa proses di WRM telah selesai
            $this->notifyDriverCompleted($transaction, 'WRM');

            // Pemanggilan otomatis truk antrian berikutnya ke dock WRM
            $this->notifyNextDriverToEnterDock($transaction, 'WRM');

            return response()->json(['success' => true, 'message' => "Status {$actionName} truk {$noPol} diperbarui ke Selesai. Diarahkan kembali ke Timbangan untuk Check-Out."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyelesaikan proses: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cancel WRM loading/unloading process.
     */
    public function wrmCancelLoading(Request $request, $id)
    {
        return $this->processCancelLoading($request, $id, 'B006', 'WRM');
    }

    /**
     * WFG Loading/Unloading View.
     */
    public function wfgIndex()
    {
        $wfgLoc = Location::where('s_loc', 'A001')->first();
        $items = VehicleItem::where(function ($q) use ($wfgLoc) {
            if ($wfgLoc) {
                $q->where('location_id', $wfgLoc->id);
            }
        })->orWhereNull('location_id')->orderBy('name')->get();

        $vendors = VehicleVendor::orderBy('name')->get();
        return view('vehicle.monitoring.wfg', compact('vendors', 'items'));
    }

    /**
     * Get JSON data for WFG Loading/Unloading Area.
     */
    public function wfgData()
    {
        $queue = VehicleTransaction::with(['vehicle', 'item', 'targetLocation', 'activeTracking'])
            ->where('status', 'wfg')
            ->orderByRaw('CASE WHEN no_antrian IS NULL THEN 1 ELSE 0 END, COALESCE(DATE(queue_taken_time), DATE(created_at)) ASC, CAST(no_antrian AS UNSIGNED) ASC, check_in_time ASC')
            ->get()
            ->map(function ($tx) {
                $tracking = $tx->activeTracking;

                $arrivalTime = $tracking ? $tracking->arrival_time : $tx->check_in_time;
                $isOvernight = $tx->queue_taken_time ? $tx->queue_taken_time->lt(Carbon::today()) : ($tx->created_at ? $tx->created_at->lt(Carbon::today()) : false);

                return [
                    'id' => $tx->id,
                    'no_antrian' => $tx->no_antrian,
                    'is_overnight' => $isOvernight,
                    'no_pol' => $tx->vehicle->no_pol,
                    'vendor' => $tx->vendor,
                    'nama_driver' => $tx->nama_driver,
                    'no_hp_driver' => $tx->no_hp_driver,
                    'jenis' => $tx->jenis,
                    'item_name' => $tx->item ? $tx->item->name : 'N/A',
                    'no_spb' => $tx->no_spb ?? '-',
                    'qty_spb' => $tx->qty_spb ? number_format($tx->qty_spb, 2) : '-',
                    'unloading_status' => $tx->unloading_status,
                    'arrival_time' => $arrivalTime->format('d-m-Y H:i'),
                    'arrival_timestamp' => $arrivalTime->timestamp,
                    'queue_taken_time' => $tx->queue_taken_time ? $tx->queue_taken_time->format('H:i') : null,
                    'queue_taken_timestamp' => $tx->queue_taken_time ? $tx->queue_taken_time->timestamp : null,
                    'start_loading_time' => $tx->start_loading_time ? $tx->start_loading_time->format('H:i') : null,
                    'start_loading_timestamp' => $tx->start_loading_time ? $tx->start_loading_time->timestamp : null,
                    'finish_loading_time' => $tx->finish_loading_time ? $tx->finish_loading_time->format('H:i') : null,
                    'finish_loading_timestamp' => $tx->finish_loading_time ? $tx->finish_loading_time->timestamp : null,
                    'follow_up_time' => $tx->follow_up_time ? $tx->follow_up_time->format('H:i') : null,
                    'follow_up_timestamp' => $tx->follow_up_time ? $tx->follow_up_time->timestamp : null,
                    'follow_up_target' => $tx->follow_up_target,
                    'follow_up_notes' => $tx->follow_up_notes,
                    'target_sloc' => $tx->targetLocation ? $tx->targetLocation->s_loc : null,
                    'sloc' => $tx->targetLocation ? $tx->targetLocation->s_loc : null,
                ];
            });

        return response()->json([
            'queue' => $queue
        ]);
    }

    /**
     * Start WFG loading/unloading process.
     */
    public function wfgStartLoading(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));

            $transaction->update([
                'unloading_status' => 'process',
                'start_loading_time' => $transaction->start_loading_time ?? Carbon::now(),
                'start_loading_by' => $transaction->start_loading_by ?? Auth::id(),
                'updated_by' => Auth::id()
            ]);

            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            if ($activeTrack) {
                $activeTrack->update([
                    'status_notes' => 'Mulai Proses Bongkar/Muat di WFG.'
                ]);
            }

            $noPol = $transaction->vehicle->no_pol;

            // Pastikan ter-release dari slot parkir
            $this->releaseKantongParkirSlot($noPol, 'Mulai proses bongkar/muat di WFG');

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'A001',
                'status' => 'wfg',
                'message' => "Truk {$noPol} mulai proses bongkar/muat di WFG.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();

            // 3. Notifikasi ke supir antrian berikutnya untuk bersiap-siap (WFG - per jenis)
            $this->notifyNextDriverToPrepare($transaction, 'WFG');

            return response()->json(['success' => true, 'message' => 'Proses bongkar/muat berhasil dimulai.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memulai bongkar/muat: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Finish WFG Unloading/Loading.
     */
    public function wfgUpdateLoading(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));

            // Conclude WFG tracking
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            $now = Carbon::now();
            $duration = $activeTrack ? abs($now->diffInSeconds($activeTrack->arrival_time, false)) : 0;

            if ($activeTrack) {
                $activeTrack->update([
                    'departure_time' => $now,
                    'duration_seconds' => $duration,
                    'status_notes' => 'Proses Bongkar/Muat WFG Selesai. Truk kembali ke Timbangan.'
                ]);
            }

            $noPol = $transaction->vehicle->no_pol;

            $timbanganLoc = Location::where('s_loc', 'TMB')->first();
            if (!$timbanganLoc) {
                throw new \Exception('Lokasi TIMBANGAN tidak ditemukan di database.');
            }

            $completedAntrian = $transaction->no_antrian ? (int)$transaction->no_antrian : 0;
            $transactionJenis = strtolower(trim($transaction->jenis ?? ''));

            // Update transaction to timbangan_out (hanya jika belum checkout/completed)
            $isAlreadyCompleted = ($transaction->status === 'completed' || !empty($transaction->check_out_time));

            $updateData = [
                'unloading_status' => 'completed',
                'finish_loading_time' => $transaction->finish_loading_time ?? $now,
                'finish_loading_by' => $transaction->finish_loading_by ?? Auth::id(),
                'updated_by' => Auth::id()
            ];

            if (!$isAlreadyCompleted) {
                $updateData['timbangan_out_time'] = $transaction->timbangan_out_time ?? $now;
                $updateData['current_location_id'] = $timbanganLoc->id;
                $updateData['status'] = 'timbangan_out';
            }

            $transaction->update($updateData);

            if (!$isAlreadyCompleted) {
                // Create new tracking log for Timbangan (Menunggu Timbang Keluar)
                VehicleTracking::create([
                    'vehicle_transaction_id' => $transaction->id,
                    'location_id' => $timbanganLoc->id,
                    'arrival_time' => $now,
                    'status_notes' => 'Selesai dari WFG. Menunggu Timbang Keluar di Timbangan.',
                    'created_by' => Auth::id(),
                ]);
            }

            $broadcastStatus = $isAlreadyCompleted ? 'completed' : 'timbangan_out';
            $broadcastLoc = $isAlreadyCompleted ? ($transaction->currentLocation ? $transaction->currentLocation->s_loc : 'TIMBANGAN') : 'TIMBANGAN';
            $broadcastMsg = $isAlreadyCompleted
                ? "Truk {$noPol} selesai di WFG."
                : "Truk {$noPol} selesai di WFG dan diarahkan kembali ke Timbangan untuk Timbang Keluar.";

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $broadcastLoc,
                'status' => $broadcastStatus,
                'message' => $broadcastMsg,
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();

            // 4. Notifikasi ke supir bahwa proses muat/bongkar telah selesai (WFG)
            $this->notifyDriverCompleted($transaction, 'WFG');

            // 5. Pemanggilan otomatis truk antrian berikutnya jika kuota dock belum penuh
            $this->notifyNextDriverToEnterDockWfgOrSmu($transaction, 'WFG');

            return response()->json(['success' => true, 'message' => 'Proses Bongkar/Muat di WFG selesai. Truk diarahkan ke Timbangan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyelesaikan bongkar/muat: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cancel WFG loading/unloading process.
     */
    public function wfgCancelLoading(Request $request, $id)
    {
        return $this->processCancelLoading($request, $id, 'A001', 'WFG');
    }

    /**
     * Helper to reorder and compact SMU queue numbers (Deprecated: kini menggunakan model tiket tetap).
     */
    private function reorderSmuQueue()
    {
        // No-op: Nomor antrian kini permanen (Model Tiket RS)
    }

    /**
     * SMU Area View.
     */
    public function smuIndex()
    {
        $smuLoc = Location::whereIn('s_loc', ['A002', 'SMU'])->first();
        $items = VehicleItem::where(function ($q) use ($smuLoc) {
            if ($smuLoc) {
                $q->where('location_id', $smuLoc->id);
            }
        })->orWhereNull('location_id')->orderBy('name')->get();

        $vendors = VehicleVendor::orderBy('name')->get();
        return view('vehicle.monitoring.smu', compact('vendors', 'items'));
    }

    /**
     * Get JSON data for SMU Area.
     */
    public function smuData()
    {
        $queue = VehicleTransaction::with(['vehicle', 'item', 'activeTracking'])
            ->where('status', 'smu')
            ->orderByRaw('CASE WHEN no_antrian IS NULL THEN 1 ELSE 0 END, COALESCE(DATE(queue_taken_time), DATE(created_at)) ASC, CAST(no_antrian AS UNSIGNED) ASC, check_in_time ASC')
            ->get()
            ->map(function ($tx) {
                $tracking = $tx->activeTracking;

                $arrivalTime = $tracking ? $tracking->arrival_time : $tx->check_in_time;
                $isOvernight = $tx->queue_taken_time ? $tx->queue_taken_time->lt(Carbon::today()) : ($tx->created_at ? $tx->created_at->lt(Carbon::today()) : false);

                return [
                    'id' => $tx->id,
                    'no_antrian' => $tx->no_antrian,
                    'is_overnight' => $isOvernight,
                    'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                    'vendor' => $tx->vendor,
                    'nama_driver' => $tx->nama_driver,
                    'no_hp_driver' => $tx->no_hp_driver,
                    'jenis' => $tx->jenis,
                    'item_name' => $tx->item ? $tx->item->name : 'N/A',
                    'no_spb' => $tx->no_spb ?? '-',
                    'qty_spb' => $tx->qty_spb ? number_format($tx->qty_spb, 2) : '-',
                    'unloading_status' => $tx->unloading_status,
                    'arrival_time' => $arrivalTime ? $arrivalTime->format('H:i') : '-',
                    'arrival_timestamp' => $arrivalTime ? $arrivalTime->timestamp : null,
                    'queue_taken_time' => $tx->queue_taken_time ? $tx->queue_taken_time->format('H:i') : null,
                    'queue_taken_timestamp' => $tx->queue_taken_time ? $tx->queue_taken_time->timestamp : null,
                    'start_loading_time' => $tx->start_loading_time ? $tx->start_loading_time->format('H:i') : null,
                    'start_loading_timestamp' => $tx->start_loading_time ? $tx->start_loading_time->timestamp : null,
                    'finish_loading_time' => $tx->finish_loading_time ? $tx->finish_loading_time->format('H:i') : null,
                    'finish_loading_timestamp' => $tx->finish_loading_time ? $tx->finish_loading_time->timestamp : null,
                    'follow_up_time' => $tx->follow_up_time ? $tx->follow_up_time->format('H:i') : null,
                    'follow_up_timestamp' => $tx->follow_up_time ? $tx->follow_up_time->timestamp : null,
                    'follow_up_target' => $tx->follow_up_target,
                    'follow_up_notes' => $tx->follow_up_notes,
                    'target_sloc' => $tx->targetLocation ? $tx->targetLocation->s_loc : null,
                    'sloc' => $tx->targetLocation ? $tx->targetLocation->s_loc : null,
                ];
            });

        return response()->json([
            'queue' => $queue
        ]);
    }

    /**
     * Start SMU process.
     */
    public function smuStartLoading(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));

            // Auto-assign queue number if not yet set
            if (empty($transaction->no_antrian)) {
                $this->syncAndResetDailyQueues();
                $formattedAntrian = $this->generateNextQueueNumber($transaction);
            } else {
                $formattedAntrian = $transaction->no_antrian;
            }

            $transaction->update([
                'no_antrian' => $formattedAntrian,
                'queue_taken_time' => $transaction->queue_taken_time ?? Carbon::now(),
                'queue_taken_by' => $transaction->queue_taken_by ?? Auth::id(),
                'unloading_status' => 'process',
                'start_loading_time' => $transaction->start_loading_time ?? Carbon::now(),
                'start_loading_by' => $transaction->start_loading_by ?? Auth::id(),
                'updated_by' => Auth::id()
            ]);

            $this->reorderSmuQueue();
            $transaction->refresh();
            $formattedAntrian = $transaction->no_antrian ?? $formattedAntrian;

            $actionName = $transaction->jenis === 'slipsheet' ? 'Muat' : 'Bongkar';
            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            if ($activeTrack) {
                $activeTrack->update([
                    'status_notes' => "Mulai Proses {$actionName} di SMU. (Antrian #{$formattedAntrian})"
                ]);
            }

            // Pastikan ter-release dari slot parkir
            $this->releaseKantongParkirSlot($noPol, "Mulai proses {$actionName} di SMU");

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'SMU',
                'status' => 'smu',
                'message' => "Truk {$noPol} mulai proses {$actionName} di SMU (Antrian #{$formattedAntrian}).",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();

            // 3. Notifikasi ke supir antrian berikutnya untuk bersiap-siap (SMU)
            $this->notifyNextDriverToPrepare($transaction, 'SMU');

            return response()->json([
                'success' => true,
                'message' => "Proses {$actionName} untuk truk {$noPol} (Antrian #{$formattedAntrian}) berhasil dimulai."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memulai proses: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Complete SMU activity.
     */
    public function smuComplete(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));

            // Conclude SMU tracking
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            $now = Carbon::now();
            $duration = $activeTrack ? abs($now->diffInSeconds($activeTrack->arrival_time, false)) : 0;

            if ($activeTrack) {
                $activeTrack->update([
                    'departure_time' => $now,
                    'duration_seconds' => $duration,
                    'status_notes' => 'Aktivitas SMU Selesai. Truk kembali ke Timbangan.'
                ]);
            }

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            $timbanganLoc = Location::where('s_loc', 'TMB')->first();
            if (!$timbanganLoc) {
                throw new \Exception('Lokasi TIMBANGAN tidak ditemukan di database.');
            }

            // Update transaction to timbangan_out (hanya jika belum checkout/completed)
            $isAlreadyCompleted = ($transaction->status === 'completed' || !empty($transaction->check_out_time));

            $updateData = [
                'unloading_status' => 'completed',
                'finish_loading_time' => $transaction->finish_loading_time ?? $now,
                'finish_loading_by' => $transaction->finish_loading_by ?? Auth::id(),
                'updated_by' => Auth::id()
            ];

            if (!$isAlreadyCompleted) {
                $updateData['timbangan_out_time'] = $transaction->timbangan_out_time ?? $now;
                $updateData['timbangan_out_by'] = $transaction->timbangan_out_by ?? Auth::id();
                $updateData['current_location_id'] = $timbanganLoc->id;
                $updateData['status'] = 'timbangan_out';
            }

            $transaction->update($updateData);

            if (!$isAlreadyCompleted) {
                // Create new tracking log for Timbangan
                VehicleTracking::create([
                    'vehicle_transaction_id' => $transaction->id,
                    'location_id' => $timbanganLoc->id,
                    'arrival_time' => $now,
                    'created_by' => Auth::id(),
                ]);
            }

            $broadcastStatus = $isAlreadyCompleted ? 'completed' : 'timbangan_out';
            $broadcastLoc = $isAlreadyCompleted ? ($transaction->currentLocation ? $transaction->currentLocation->s_loc : 'TIMBANGAN') : 'TIMBANGAN';
            $broadcastMsg = $isAlreadyCompleted
                ? "Proses Truk {$noPol} di SMU selesai."
                : "Proses Truk {$noPol} di SMU selesai. Truk kembali ke Timbangan untuk Check-Out.";

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $broadcastLoc,
                'status' => $broadcastStatus,
                'message' => $broadcastMsg,
                'time' => $now->format('H:i:s')
            ]));

            DB::commit();

            // 4. Notifikasi ke supir bahwa proses muat/bongkar telah selesai (SMU)
            $this->notifyDriverCompleted($transaction, 'SMU');

            // 5. Pemanggilan otomatis truk antrian berikutnya jika kuota dock belum penuh
            $this->notifyNextDriverToEnterDockWfgOrSmu($transaction, 'SMU');

            return response()->json(['success' => true, 'message' => 'Truk ' . $noPol . ' selesai di SMU. Diarahkan kembali ke Timbangan untuk Check-Out.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memproses SMU: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cancel SMU loading/unloading process.
     */
    public function smuCancelLoading(Request $request, $id)
    {
        return $this->processCancelLoading($request, $id, 'SMU', 'SMU');
    }

    /**
     * Unified helper to cancel loading/unloading process across areas (WPM, WRM, WFG, SMU).
     */
    private function processCancelLoading(Request $request, $id, string $areaCode, string $areaName)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));
            $jenis = strtolower(trim($transaction->jenis ?? ''));

            // Tentukan nama aksi: Bongkar / Muat
            $actionName = ($jenis === 'bongkaran') ? 'Bongkar' : 'Muat';
            if ($areaCode === 'SMU') {
                $actionName = ($jenis === 'slipsheet' || $jenis === 'curah') ? 'Muat' : 'Bongkar';
            }

            $reason = trim($request->input('reason', ''));
            $reasonNote = !empty($reason) ? " Alasan: {$reason}." : "";

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';
            $oldAntrian = $transaction->no_antrian;

            // Conclude active tracking at current area
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            $now = Carbon::now();
            $duration = $activeTrack ? abs($now->diffInSeconds($activeTrack->arrival_time, false)) : 0;

            if ($activeTrack) {
                $activeTrack->update([
                    'departure_time' => $now,
                    'duration_seconds' => $duration,
                    'status_notes' => "Batal Proses {$actionName} di {$areaName}.{$reasonNote} Truk kembali ke Timbangan."
                ]);
            }

            // Release kantong parkir slot jika ada
            $this->releaseKantongParkirSlot($noPol, "Batal proses {$actionName} di {$areaName}");

            $timbanganLoc = Location::where('s_loc', 'TMB')->first();
            if (!$timbanganLoc) {
                throw new \Exception('Lokasi TIMBANGAN tidak ditemukan di database.');
            }

            $isAlreadyCompleted = ($transaction->status === 'completed' || !empty($transaction->check_out_time));

            $updateData = [
                'unloading_status' => 'cancelled',
                'finish_loading_time' => $transaction->finish_loading_time ?? $now,
                'finish_loading_by' => $transaction->finish_loading_by ?? Auth::id(),
                'no_antrian' => null,
                'follow_up_notes' => "Batal {$actionName} ({$areaName})" . (!empty($reason) ? ": {$reason}" : ""),
                'updated_by' => Auth::id()
            ];

            if (!$isAlreadyCompleted) {
                $updateData['timbangan_out_time'] = $transaction->timbangan_out_time ?? $now;
                $updateData['timbangan_out_by'] = $transaction->timbangan_out_by ?? Auth::id();
                $updateData['current_location_id'] = $timbanganLoc->id;
                $updateData['status'] = 'timbangan_out';
            }

            $transaction->update($updateData);

            if (!$isAlreadyCompleted) {
                // Create new tracking log for Timbangan
                VehicleTracking::create([
                    'vehicle_transaction_id' => $transaction->id,
                    'location_id' => $timbanganLoc->id,
                    'arrival_time' => $now,
                    'status_notes' => "Tiba di Timbangan untuk Check-Out (Batal {$actionName} di {$areaName}).",
                    'created_by' => Auth::id(),
                ]);
            }

            $broadcastStatus = $isAlreadyCompleted ? 'completed' : 'timbangan_out';
            $broadcastLoc = $isAlreadyCompleted ? ($transaction->currentLocation ? $transaction->currentLocation->s_loc : 'TIMBANGAN') : 'TIMBANGAN';
            $broadcastMsg = "Proses {$actionName} Truk {$noPol} di {$areaName} dibatalkan. Truk kembali ke Timbangan untuk Check-Out.";

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $broadcastLoc,
                'status' => $broadcastStatus,
                'message' => $broadcastMsg,
                'time' => $now->format('H:i:s')
            ]));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Proses {$actionName} truk {$noPol} di {$areaName} berhasil dibatalkan. Truk diarahkan kembali ke Timbangan untuk Check-Out."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan proses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check-Out vehicle at scales.
     */
    public function timbanganCheckOut(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));

            // Conclude active tracking (TIMBANGAN_OUT)
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            $now = Carbon::now();
            $duration = $activeTrack ? abs($now->diffInSeconds($activeTrack->arrival_time, false)) : 0;

            if ($activeTrack) {
                $activeTrack->update([
                    'departure_time' => $now,
                    'duration_seconds' => $duration,
                    'status_notes' => 'Timbang Keluar Selesai. Check-Out.'
                ]);
            }

            $noPol = $transaction->vehicle->no_pol;

            // Finalize transaction
            $transaction->update([
                'status' => 'completed',
                'check_out_time' => $now,
                'check_out_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'TIMBANGAN',
                'status' => 'completed',
                'message' => "Truk {$noPol} telah timbang keluar (Check-Out) dan meninggalkan area.",
                'time' => $now->format('H:i:s')
            ]));

            DB::commit();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Truk ' . $noPol . ' berhasil Timbang Keluar (Check-Out).'
                ]);
            }
            return redirect()->route('vehicle.monitoring.timbangan')->with('success', 'Truk ' . $noPol . ' berhasil Timbang Keluar (Check-Out).');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal Check-Out: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('vehicle.monitoring.timbangan')->with('error', 'Gagal Check-Out: ' . $e->getMessage());
        }
    }

    /**
     * Follow up transaction to area (QC, WFG, SMU, WRM, WPM, etc.).
     */
    public function timbanganFollowUpArea(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::with(['vehicle', 'currentLocation', 'targetLocation'])->findOrFail($id);

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';
            $targetArea = strtoupper($request->target_area ?? 'ALL');
            $notes = $request->notes ? trim($request->notes) : null;
            $now = Carbon::now();

            $transaction->update([
                'follow_up_time' => $now,
                'follow_up_target' => $targetArea,
                'follow_up_notes' => $notes,
                'follow_up_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            // Update status log catatan in active tracking if exists
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            if ($activeTrack) {
                $msgNote = "Follow up dari Timbangan ke Area {$targetArea}" . ($notes ? ": {$notes}" : ".");
                $activeTrack->update([
                    'status_notes' => $msgNote
                ]);
            }

            $operatorName = Auth::user()->name ?? 'Operator Timbangan';
            $targetSloc = $transaction->targetLocation ? $transaction->targetLocation->s_loc : '';

            // Cek apakah WhatsApp follow up sudah pernah dikirim sebelumnya untuk transaksi ini
            $alreadyFollowedUp = !empty($transaction->follow_up_time) || Cache::has("fu_timbangan_wa_sent_{$transaction->id}");

            // Broadcast Event for Realtime Alert (tetap dikirim setiap klik untuk notifikasi web real-time)
            event(new VehicleStatusUpdated([
                'type' => 'follow_up',
                'action' => 'follow_up',
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'no_spb' => $transaction->no_spb ?? '-',
                'target_area' => $targetArea,
                'target_sloc' => $targetSloc,
                'notes' => $notes,
                'sender' => $operatorName,
                'status' => $transaction->status,
                'qc_status' => $transaction->qc_status,
                'message' => "Peringatan Follow Up dari Timbangan: Truk {$noPol} menunggu konfirmasi selesai / keputusan QC di area {$targetArea}.",
                'time' => $now->format('H:i:s')
            ]));

            DB::commit();

            // Kirim notifikasi WhatsApp ke Foreman Area terkait (SMU/WFG -> Foreman WFG, WRM/WPM -> Foreman WRM)
            // WhatsApp HANYA dikirim sekali saja per transaksi (tidak di-spam per klik)
            if (!$alreadyFollowedUp) {
                $this->sendFollowUpForemanAlert($transaction, $targetArea, $notes);
                Cache::put("fu_timbangan_wa_sent_{$transaction->id}", true, 86400 * 3);
                $responseMsg = "Notifikasi follow up berhasil dikirim ke area {$targetArea} dan WhatsApp Foreman untuk Truk {$noPol}.";
            } else {
                Log::info("[VehicleTracking] Skip duplicate WA follow-up timbangan untuk truk {$noPol} (ID: {$transaction->id}) karena sudah pernah dikirim sebelumnya. Notifikasi real-time web tetap terkirim.");
                $responseMsg = "Notifikasi follow up real-time berhasil dikirim ke area {$targetArea} untuk Truk {$noPol}. (WhatsApp Foreman sudah pernah dikirim sebelumnya).";
            }

            return response()->json([
                'success' => true,
                'message' => $responseMsg
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim follow up: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Follow up from Area (WFG / SMU / etc.) to Timbangan for unregistered vehicles.
     */
    public function followUpTimbangan(Request $request)
    {
        $request->validate([
            'no_pol' => 'required|string|max:20',
            'vendor' => 'nullable|string|max:100',
            'jenis' => 'required|string|in:bongkaran,slipsheet,curah,retur',
            'item_id' => 'nullable|exists:vehicle_items,id',
            'no_spb' => 'nullable|string|max:100',
            'qty_spb' => 'nullable|numeric|min:0',
            'area' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $noPol = strtoupper(str_replace(' ', '', $request->no_pol));
            $vendor = trim($request->vendor ?? '');
            $jenis = strtolower(trim($request->jenis));
            $area = strtoupper(trim($request->area));
            $notes = trim($request->notes ?? '');
            $noSpb = trim($request->no_spb ?? '');
            $qtySpb = $request->filled('qty_spb') ? (float)$request->qty_spb : null;
            $itemId = $request->item_id;
            $item = $itemId ? VehicleItem::find($itemId) : null;
            $itemName = $item ? $item->name : null;
            $now = Carbon::now();

            if ($vendor) {
                VehicleVendor::firstOrCreate(['name' => $vendor]);
            }

            $senderName = Auth::user()->name ?? 'Operator ' . $area;

            $followUpPayload = [
                'type' => 'follow_up_timbangan',
                'action' => 'unregistered_vehicle',
                'no_pol' => $noPol,
                'vendor' => $vendor ?: '-',
                'jenis' => $jenis,
                'item_id' => $itemId,
                'item_name' => $itemName ?: '-',
                'no_spb' => $noSpb ?: null,
                'qty_spb' => $qtySpb,
                'source_area' => $area,
                'target_area' => 'TIMBANGAN',
                'notes' => $notes ?: null,
                'sender' => $senderName,
                'message' => "Peringatan Follow Up dari {$area}: Truk {$noPol} ({$vendor}" . ($itemName ? " - {$itemName}" : "") . " - " . ucfirst($jenis) . ") sudah berada di lokasi {$area} namun belum terdaftar di Timbangan.",
                'time' => $now->format('H:i:s'),
                'timestamp' => $now->timestamp
            ];

            // Simpan data follow up ke Cache agar tidak hilang jika operator merefresh atau sebelum event ditangkap
            $followUpList = Cache::get('unregistered_vehicle_followups', []);
            $followUpList = array_values(array_filter($followUpList, function ($item) use ($noPol) {
                return strtoupper(str_replace(' ', '', $item['no_pol'] ?? '')) !== $noPol;
            }));
            $followUpList[] = $followUpPayload;
            Cache::put('unregistered_vehicle_followups', $followUpList, 86400);

            // Broadcast Realtime Event
            event(new VehicleStatusUpdated($followUpPayload));

            return response()->json([
                'success' => true,
                'message' => "Pemberitahuan follow up untuk truk {$noPol} berhasil dikirim ke Timbangan."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim follow up ke Timbangan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dismiss / hapus data follow up kendaraan yang belum terdaftar dari Cache Timbangan.
     */
    public function dismissFollowUpTimbangan(Request $request)
    {
        $request->validate([
            'no_pol' => 'required|string|max:20',
        ]);

        try {
            $noPol = strtoupper(str_replace(' ', '', $request->no_pol));

            $followUpList = Cache::get('unregistered_vehicle_followups', []);
            $filteredFollowUps = array_values(array_filter($followUpList, function ($item) use ($noPol) {
                return strtoupper(str_replace(' ', '', $item['no_pol'] ?? '')) !== $noPol;
            }));

            Cache::put('unregistered_vehicle_followups', $filteredFollowUps, 86400);

            // Broadcast status update event
            event(new VehicleStatusUpdated([
                'type' => 'dismiss_follow_up_timbangan',
                'no_pol' => $noPol,
                'message' => "Follow up untuk truk {$noPol} telah dihapus/dibatalkan dari Timbangan.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            return response()->json([
                'success' => true,
                'message' => "Peringatan follow up untuk truk {$noPol} berhasil dihapus."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus follow up: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate running queue number (+1 auto increment) strictly scoped by dock pool:
     * 1. Slipsheet (WFG Slipsheet saja)
     * 2. Curah (WFG Curah saja)
     * 3. Bongkaran & SMU (Antrian gabungan untuk WFG Bongkaran + semua truk di Area SMU)
     */
    public function generateNextQueueNumber(VehicleTransaction $transaction): string
    {
        $category = $this->resolveDockCategory($transaction);
        $todayStr = Carbon::today()->toDateString();
        $txDateStr = $transaction->created_at ? $transaction->created_at->toDateString() : $todayStr;

        $query = VehicleTransaction::whereNotNull('no_antrian')
            ->where('id', '!=', $transaction->id)
            ->where(function ($q) use ($todayStr, $txDateStr) {
                $q->whereDate(DB::raw('COALESCE(queue_taken_time, created_at)'), $todayStr)
                    ->orWhereDate(DB::raw('COALESCE(queue_taken_time, created_at)'), $txDateStr);
            });

        if ($category === 'slipsheet') {
            // Group 1: WFG - slipsheet
            $query->where(function ($q) {
                $q->where('status', 'wfg')
                    ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
            })->where(function ($q) {
                $q->where('jenis', 'slipsheet')
                    ->orWhereNull('jenis')
                    ->orWhere('jenis', '');
            })->whereNotIn('status', ['smu', 'wrm', 'wpm'])
              ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU', 'B006', 'C001']));
        } elseif ($category === 'curah') {
            // Group 2: WFG - curah
            $query->where(function ($q) {
                $q->where('status', 'wfg')
                    ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
            })->where('jenis', 'curah')
              ->whereNotIn('status', ['smu', 'wrm', 'wpm'])
              ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU', 'B006', 'C001']));
        } else {
            // Group 3: WFG - bongkaran == SMU bongkaran
            $query->where(function ($q) {
                // 1. Area SMU
                $q->where(function ($smuQ) {
                    $smuQ->where('status', 'smu')
                        ->orWhereHas('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU']));
                })
                // 2. ATAU Area WFG khusus jenis bongkaran
                ->orWhere(function ($wfgQ) {
                    $wfgQ->where(function ($w) {
                        $w->where('status', 'wfg')
                            ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
                    })->where('jenis', 'bongkaran');
                });
            })
            ->whereNotIn('status', ['wrm', 'wpm'])
            ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['B006', 'C001']));
        }

        $maxAntrian = $query->get()
            ->map(function ($tx) {
                return (int)$tx->no_antrian;
            })
            ->max();

        $nextAntrian = $maxAntrian ? $maxAntrian + 1 : 1;
        return str_pad($nextAntrian, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Sinkronisasi dan reset antrian saat ganti hari:
     * - Khusus saat pergantian hari baru, antrian di-reset untuk hari baru.
     * - Jika masih ada truk yang outstanding (menginap/sisa hari kemarin yang belum selesai),
     *   truk-truk outstanding tersebut diurutkan ulang menjadi nomor 01, 02, ... n (ASC)
     *   dan tanggal antriannya dimajukan ke hari ini agar menjadi antrian terdepan hari ini.
     * - Truk baru yang ambil antrian hari ini akan melanjutkan urutan setelahnya (n+1, n+2, dst)
     *   secara running terus-menerus tanpa di-reset kembali.
     */
    public function syncAndResetDailyQueues(): int
    {
        $today = Carbon::today();
        $updatedCount = 0;
        $categories = ['slipsheet', 'curah', 'bongkaran', 'qc'];

        foreach ($categories as $cat) {
            $query = VehicleTransaction::with('vehicle')
                ->whereNotNull('no_antrian')
                ->whereNotIn('status', ['completed', 'timbangan_out'])
                ->whereNull('finish_loading_time')
                ->whereNull('check_out_time')
                ->whereNull('timbangan_out_time');

            if ($cat === 'slipsheet') {
                $query->where(function ($q) {
                    $q->where('status', 'wfg')
                        ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
                })->where(function ($q) {
                    $q->where('jenis', 'slipsheet')->orWhereNull('jenis')->orWhere('jenis', '');
                })->whereNotIn('status', ['smu', 'wrm', 'wpm'])
                  ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU', 'B006', 'C001']));
            } elseif ($cat === 'curah') {
                $query->where(function ($q) {
                    $q->where('status', 'wfg')
                        ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
                })->where('jenis', 'curah')
                  ->whereNotIn('status', ['smu', 'wrm', 'wpm'])
                  ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU', 'B006', 'C001']));
            } elseif ($cat === 'bongkaran') {
                // Group 3: WFG Bongkaran == SMU Bongkaran
                $query->where(function ($q) {
                    $q->where(function ($smuQ) {
                        $smuQ->where('status', 'smu')
                            ->orWhereHas('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU']));
                    })->orWhere(function ($wfgQ) {
                        $wfgQ->where(function ($w) {
                            $w->where('status', 'wfg')
                                ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
                        })->where('jenis', 'bongkaran');
                    });
                })->whereNotIn('status', ['wrm', 'wpm'])
                  ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['B006', 'C001']));
            } else { // QC
                $query->whereIn('qc_status', ['waiting_dokumen', 'waiting_sampling', 'on_check'])
                    ->whereNull('finish_sampling_time');
            }

            $activeTxs = $query->get();
            if ($activeTxs->isEmpty()) {
                continue;
            }

            // Cari transaksi yang MASIH berstatus outstanding dari hari kemarin (< today)
            $overnightTxs = $activeTxs->filter(function ($tx) use ($today) {
                $qDate = $tx->queue_taken_time ? Carbon::parse($tx->queue_taken_time) : Carbon::parse($tx->created_at);
                return $qDate->lt($today);
            });

            // Hanya proses jika ada transaksi outstanding yang tanggalnya masih hari kemarin
            if ($overnightTxs->isNotEmpty()) {
                // Urutkan truk outstanding hari kemarin berdasarkan waktu / no antrian lama ASC
                $sortedOvernight = $overnightTxs->sort(function ($a, $b) {
                    $dateA = $a->queue_taken_time ? Carbon::parse($a->queue_taken_time) : Carbon::parse($a->created_at);
                    $dateB = $b->queue_taken_time ? Carbon::parse($b->queue_taken_time) : Carbon::parse($b->created_at);

                    if ($dateA->ne($dateB)) {
                        return $dateA->lt($dateB) ? -1 : 1;
                    }
                    return (int)$a->no_antrian <=> (int)$b->no_antrian;
                })->values();

                $seq = 1;
                foreach ($sortedOvernight as $tx) {
                    $formattedSeq = str_pad($seq, 2, '0', STR_PAD_LEFT);
                    $tx->update([
                        'no_antrian' => $formattedSeq,
                        // Update queue_taken_time ke awal hari ini agar tercatat sebagai antrian hari ini
                        'queue_taken_time' => $today->copy()->startOfDay()->addMinutes($seq)
                    ]);
                    $seq++;
                    $updatedCount++;
                }

                // Jika sudah ada truk hari ini yang terbit sebelum sinkronisasi, lanjutkan urutannya setelah $seq
                $todayTxs = $activeTxs->filter(function ($tx) use ($today) {
                    $qDate = $tx->queue_taken_time ? Carbon::parse($tx->queue_taken_time) : Carbon::parse($tx->created_at);
                    return $qDate->gte($today);
                })->sort(function ($a, $b) {
                    return (int)$a->no_antrian <=> (int)$b->no_antrian;
                })->values();

                foreach ($todayTxs as $tx) {
                    $formattedSeq = str_pad($seq, 2, '0', STR_PAD_LEFT);
                    if ($tx->no_antrian !== $formattedSeq) {
                        $tx->update(['no_antrian' => $formattedSeq]);
                        $updatedCount++;
                    }
                    $seq++;
                }
            }
        }

        if ($updatedCount > 0) {
            Log::info("[VehicleTracking] syncAndResetDailyQueues: {$updatedCount} antrian outstanding hari kemarin disinkronkan menjadi antrian awal hari ini.");
            event(new VehicleStatusUpdated([
                'type' => 'queue_resynced',
                'action' => 'queue_resynced',
                'message' => "Antrian outstanding hari kemarin disinkronkan ({$updatedCount} kendaraan disesuaikan urutannya)"
            ]));
        }

        return $updatedCount;
    }

    /**
     * Update queue number for a transaction (generic).
     */
    public function updateQueueNumber(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));

            // 1. Sinkronkan antrian outstanding jika baru berganti hari
            $this->syncAndResetDailyQueues();

            // 2. Jika transaksi sudah memiliki nomor antrian, pertahankan nomor tersebut
            if (!empty($transaction->no_antrian)) {
                $formattedAntrian = $transaction->no_antrian;
            } else {
                $formattedAntrian = $this->generateNextQueueNumber($transaction);
                $transaction->update([
                    'no_antrian' => $formattedAntrian,
                    'queue_taken_time' => Carbon::now(),
                    'queue_taken_by' => $transaction->queue_taken_by ?? Auth::id(),
                    'updated_by' => Auth::id()
                ]);
            }

            // Kendaraan TETAP berada di slot kantong parkir saat mengambil antrian.
            // Release dari parkir HANYA dilakukan saat action Mulai Bongkar/Muat (start_loading_time).
            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';
            $currentLoc = $transaction->currentLocation ? $transaction->currentLocation->s_loc : 'N/A';
            $jenisLabel = ($status === 'wfg' && !empty($transaction->jenis)) ? ' (' . ucfirst($transaction->jenis) . ')' : '';

            // Broadcast change
            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $currentLoc,
                'status' => $transaction->status,
                'message' => "Nomor antrian Truk {$noPol}{$jenisLabel} diset otomatis menjadi {$formattedAntrian}.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();

            // 5. Notifikasi info nomor antrian ke supir (hanya di WFG dan SMU)
            $areaUpper = strtoupper($transaction->status);
            if (in_array($areaUpper, ['WFG', 'SMU'])) {
                $this->notifyDriverQueueTaken($transaction, $areaUpper);
            }

            return response()->json([
                'success' => true,
                'message' => "Nomor antrian Truk {$noPol}{$jenisLabel} berhasil diset ke {$formattedAntrian}."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui nomor antrian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel queue number for a transaction (generic: SMU, WFG, etc.).
     */
    public function cancelQueueNumber(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));

            // Jika transaksi berada di area QC
            if ($transaction->qc_status === 'waiting_sampling' || in_array($transaction->status, ['antri_sampling', 'sampling'])) {
                if ($transaction->qc_status === 'on_check' || !empty($transaction->start_sampling_time)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Antrian tidak dapat dibatalkan karena proses sampling QC sudah berjalan.'
                    ], 422);
                }
                $qcResetStatus = 'waiting_dokumen';
            } else {
                // Untuk SMU, WFG, dll.
                if ($transaction->unloading_status === 'process' || !empty($transaction->start_loading_time)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Antrian tidak dapat dibatalkan karena proses bongkar/muat sudah berjalan.'
                    ], 422);
                }
                $qcResetStatus = $transaction->qc_status;
            }

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';
            $oldAntrian = $transaction->no_antrian;
            $status = $transaction->status;
            $jenis = strtolower(trim($transaction->jenis ?? ''));

            $updateData = [
                'no_antrian' => null,
                'queue_taken_time' => null,
                'queue_taken_by' => null,
                'updated_by' => Auth::id()
            ];
            if (isset($qcResetStatus)) {
                $updateData['qc_status'] = $qcResetStatus;
            }

            $transaction->update($updateData);

            $currentLoc = $transaction->currentLocation ? $transaction->currentLocation->s_loc : 'N/A';

            // Update status log catatan
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            if ($activeTrack) {
                $activeTrack->update([
                    'status_notes' => 'Nomor antrian (' . $oldAntrian . ') dibatalkan. Menunggu antrian.'
                ]);
            }

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $currentLoc,
                'status' => $transaction->status,
                'message' => "Nomor antrian Truk {$noPol} telah dibatalkan.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Nomor antrian Truk {$noPol} berhasil dibatalkan."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan nomor antrian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Timbangan Transaction.
     */
    public function timbanganUpdate(Request $request, $id)
    {
        $request->validate([
            'no_pol' => 'required|string|max:20',
            'vendor' => 'nullable|string|max:100',
            'nama_driver' => 'nullable|string|max:255',
            'no_hp_driver' => 'nullable|string|max:50',
            'checkin_pos1' => 'nullable|date',
            'trnvisitorid' => 'nullable|string|max:100',
            'jenis' => 'required|string|in:bongkaran,slipsheet,curah,retur',
            'item_id' => 'required|exists:vehicle_items,id',
            'no_spb' => 'nullable|string|max:50',
            'qty_spb' => 'nullable|numeric|min:0',
            'target_location_id' => 'required|exists:locations,id',
        ]);

        $noPol = strtoupper(str_replace(' ', '', $request->no_pol));
        $namaDriver = $request->nama_driver;
        $noHpDriver = $request->no_hp_driver;
        $checkinPos1 = $request->checkin_pos1;
        $trnvisitorid = $request->trnvisitorid;

        // Cek API supplier jika nama driver atau no hp driver belum terisi
        if (empty($namaDriver) || empty($noHpDriver)) {
            $matchedSupplier = $this->findSupplierByNopol($noPol);
            if ($matchedSupplier) {
                if (empty($namaDriver) && !empty($matchedSupplier['nama_driver'])) {
                    $namaDriver = $matchedSupplier['nama_driver'];
                }
                if (empty($noHpDriver) && !empty($matchedSupplier['no_hp_driver'])) {
                    $noHpDriver = $matchedSupplier['no_hp_driver'];
                }
                if (empty($trnvisitorid) && !empty($matchedSupplier['trnvisitorid'])) {
                    $trnvisitorid = $matchedSupplier['trnvisitorid'];
                }
                if (empty($checkinPos1) && !empty($matchedSupplier['checkin_pos1'])) {
                    $checkinPos1 = $matchedSupplier['checkin_pos1'];
                }
            }
        }

        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));

            // Validate target area based on jenis
            $targetLoc = Location::findOrFail($request->target_location_id);
            $jenis = $request->jenis;
            if ($jenis === 'bongkaran') {
                if (!in_array($targetLoc->s_loc, ['A001', 'A002', 'SMU', 'B006', 'C001'])) {
                    throw new \Exception('Untuk jenis bongkaran, silakan pilih tujuan area WFG (A001), SMU, WRM (B006), atau WPM (C001).');
                }
            } elseif (in_array($jenis, ['slipsheet', 'curah'])) {
                if (!in_array($targetLoc->s_loc, ['A001', 'SMU', 'A002', 'B006'])) {
                    throw new \Exception('Untuk jenis slipsheet atau curah, hanya boleh memilih tujuan area WFG (A001), SMU, atau WRM (B006).');
                }
            } elseif ($jenis === 'retur') {
                if (!in_array($targetLoc->s_loc, ['B006', 'C001'])) {
                    throw new \Exception('Untuk jenis retur, hanya boleh memilih tujuan area WRM (B006) atau WPM (C001).');
                }
            }

            $vendorName = trim($request->vendor);
            if ($vendorName) {
                VehicleVendor::firstOrCreate(['name' => $vendorName]);
            }

            // Find or create vehicle
            $vehicle = Vehicle::updateOrCreate(
                ['no_pol' => $noPol],
                ['vendor' => $request->vendor]
            );

            $oldTargetId = $transaction->target_location_id;

            $updateData = [
                'vehicle_id' => $vehicle->id,
                'jenis' => $request->jenis,
                'vendor' => $request->vendor ?? $vehicle->vendor,
                'nama_driver' => $namaDriver,
                'no_hp_driver' => $noHpDriver,
                'item_id' => $request->item_id,
                'no_spb' => $request->no_spb,
                'qty_spb' => $request->qty_spb,
                'target_location_id' => $request->target_location_id,
                'updated_by' => Auth::id(),
            ];

            if ($request->jenis === 'retur') {
                $updateData['qc_status'] = 'not_required';
            }

            if (!empty($checkinPos1)) {
                $updateData['checkin_pos1'] = $checkinPos1;
            } elseif ($request->filled('checkin_pos1')) {
                $updateData['checkin_pos1'] = $request->checkin_pos1;
            }

            if (!empty($trnvisitorid)) {
                $updateData['trnvisitorid'] = $trnvisitorid;
            } elseif ($request->filled('trnvisitorid')) {
                $updateData['trnvisitorid'] = $request->trnvisitorid;
            }

            $transaction->update($updateData);

            // If target location changed, update the active tracking location to match the new destination
            if ($oldTargetId != $request->target_location_id && $transaction->status !== 'completed') {

                // Map target location to transaction status
                $newStatus = 'smu';
                $initialQcStatus = ($jenis === 'retur') ? 'not_required' : $transaction->qc_status;
                $currentLocId = $targetLoc->id;

                if ($targetLoc->s_loc === 'C001') {
                    $newStatus = ($jenis === 'retur') ? 'wpm' : 'antri_sampling';
                    $initialQcStatus = ($jenis === 'retur') ? 'not_required' : 'waiting_dokumen';
                    $currentLocId = $targetLoc->id;
                } elseif ($targetLoc->s_loc === 'B006') {
                    $newStatus = ($jenis === 'retur') ? 'wrm_bongkar' : 'antri_sampling';
                    $initialQcStatus = ($jenis === 'retur') ? 'not_required' : 'waiting_dokumen';
                    $currentLocId = $targetLoc->id;
                } elseif ($targetLoc->s_loc === 'A001') {
                    $newStatus = 'wfg';
                    $initialQcStatus = 'not_required';
                }

                // Conclude current target tracking log (if active is at target, or update the active tracking)
                $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                    ->whereNull('departure_time')
                    ->latest()
                    ->first();

                if ($activeTrack && $activeTrack->location_id == $oldTargetId) {
                    $activeTrack->update([
                        'location_id' => $currentLocId
                    ]);
                }

                $transaction->update([
                    'current_location_id' => $currentLocId,
                    'status' => $newStatus,
                    'qc_status' => $initialQcStatus
                ]);
            }

            // Broadcast change via Reverb
            $currentLocCode = $transaction->currentLocation ? $transaction->currentLocation->s_loc : 'TIMBANGAN';
            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $currentLocCode,
                'status' => $transaction->status,
                'message' => "Data transaksi Truk {$noPol} telah diperbarui di Timbangan.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data transaksi berhasil diperbarui.',
                    'vendors' => VehicleVendor::orderBy('name')->get()
                ]);
            }
            $redirectUrl = route('vehicle.monitoring.timbangan');
            if ($request->filled('date')) {
                $redirectUrl .= '?date=' . $request->date;
            }
            return redirect($redirectUrl)->with('success', 'Data transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui transaksi: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }

    public function timbanganDestroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $transaction = VehicleTransaction::findOrFail($id);
            $status = strtolower(trim($transaction->status ?? ''));
            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';
            $txId = $transaction->id;

            // Delete related tracking logs
            VehicleTracking::where('vehicle_transaction_id', $transaction->id)->delete();
            $transaction->delete();
            DB::commit();

            // Broadcast delete event via Reverb
            event(new VehicleStatusUpdated([
                'transaction_id' => $txId,
                'no_pol' => $noPol,
                'current_location' => 'TIMBANGAN',
                'status' => 'deleted',
                'message' => "Transaksi Truk {$noPol} telah dihapus dari sistem.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data transaksi berhasil dihapus.'
                ]);
            }

            $redirectUrl = route('vehicle.monitoring.timbangan');
            if ($request->filled('date')) {
                $redirectUrl .= '?date=' . $request->date;
            }
            return redirect($redirectUrl)->with('success', 'Data transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()
                ], 500);
            }
            $redirectUrl = route('vehicle.monitoring.timbangan');
            if ($request->filled('date')) {
                $redirectUrl .= '?date=' . $request->date;
            }
            return redirect($redirectUrl)->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Master Items View.
     */
    public function masterItemsIndex()
    {
        $items = VehicleItem::with('location')->orderBy('id')->get();
        $locations = Location::orderBy('id')->get();
        $vendors = VehicleVendor::orderBy('id')->get();
        return view('vehicle.monitoring.master_items', compact('items', 'locations', 'vendors'));
    }

    /**
     * Get all Master Data as JSON.
     */
    public function masterItemsData()
    {
        $items = VehicleItem::with('location')->orderBy('id')->get();
        $locations = Location::orderBy('id')->get();
        $vendors = VehicleVendor::orderBy('id')->get();
        return response()->json([
            'success' => true,
            'items' => $items,
            'locations' => $locations,
            'vendors' => $vendors
        ]);
    }

    /**
     * Store Master SKU.
     */
    public function masterItemsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        $item = VehicleItem::create([
            'name' => $request->name,
            'location_id' => $request->location_id,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item SKU berhasil ditambahkan.',
                'item' => $item
            ]);
        }

        return redirect()->route('vehicle.monitoring.master.items')->with('success', 'Item SKU berhasil ditambahkan.');
    }

    /**
     * Update Master SKU.
     */
    public function masterItemsUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        $item = VehicleItem::findOrFail($id);
        $item->update([
            'name' => $request->name,
            'location_id' => $request->location_id,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item SKU berhasil diperbarui.',
                'item' => $item
            ]);
        }

        return redirect()->route('vehicle.monitoring.master.items')->with('success', 'Item SKU berhasil diperbarui.');
    }

    /**
     * Delete Master SKU.
     */
    public function masterItemsDestroy($id)
    {
        try {
            $item = VehicleItem::findOrFail($id);
            $item->delete();
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item SKU berhasil dihapus.'
                ]);
            }
            return redirect()->route('vehicle.monitoring.master.items')->with('success', 'Item SKU berhasil dihapus.');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus Item SKU: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('vehicle.monitoring.master.items')->with('error', 'Gagal menghapus Item SKU: ' . $e->getMessage());
        }
    }

    /**
     * Store Master Sloc.
     */
    public function masterSlocStore(Request $request)
    {
        $request->validate([
            's_loc' => 'required|string|max:50|unique:locations,s_loc',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $location = Location::create([
            's_loc' => strtoupper($request->s_loc),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sloc berhasil ditambahkan.',
                'sloc' => $location
            ]);
        }

        return redirect()->route('vehicle.monitoring.master.items')
            ->with('success', 'Sloc berhasil ditambahkan.')
            ->with('tab', 'sloc');
    }

    /**
     * Update Master Sloc.
     */
    public function masterSlocUpdate(Request $request, $id)
    {
        $request->validate([
            's_loc' => 'required|string|max:50|unique:locations,s_loc,' . $id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $location = Location::findOrFail($id);
        $location->update([
            's_loc' => strtoupper($request->s_loc),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sloc berhasil diperbarui.',
                'sloc' => $location
            ]);
        }

        return redirect()->route('vehicle.monitoring.master.items')
            ->with('success', 'Sloc berhasil diperbarui.')
            ->with('tab', 'sloc');
    }

    /**
     * Delete Master Sloc.
     */
    public function masterSlocDestroy($id)
    {
        try {
            $location = Location::findOrFail($id);
            $location->delete();
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sloc berhasil dihapus.'
                ]);
            }
            return redirect()->route('vehicle.monitoring.master.items')
                ->with('success', 'Sloc berhasil dihapus.')
                ->with('tab', 'sloc');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus Sloc: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('vehicle.monitoring.master.items')
                ->with('error', 'Gagal menghapus Sloc: ' . $e->getMessage())
                ->with('tab', 'sloc');
        }
    }

    /**
     * Store Master Vendor.
     */
    public function masterVendorStore(Request $request)
    {
        $request->validate([
            'vendor_name' => 'required|string|max:100|unique:vehicle_vendors,name',
            'description' => 'nullable|string|max:255',
        ]);

        $vendor = VehicleVendor::create([
            'name' => $request->vendor_name,
            'description' => $request->description,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vendor berhasil ditambahkan.',
                'vendor' => $vendor
            ]);
        }

        return redirect()->route('vehicle.monitoring.master.items')
            ->with('success', 'Vendor berhasil ditambahkan.')
            ->with('tab', 'vendor');
    }

    /**
     * Update Master Vendor.
     */
    public function masterVendorUpdate(Request $request, $id)
    {
        $request->validate([
            'vendor_name' => 'required|string|max:100|unique:vehicle_vendors,name,' . $id,
            'description' => 'nullable|string|max:255',
        ]);

        $vendor = VehicleVendor::findOrFail($id);
        $vendor->update([
            'name' => $request->vendor_name,
            'description' => $request->description,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vendor berhasil diperbarui.',
                'vendor' => $vendor
            ]);
        }

        return redirect()->route('vehicle.monitoring.master.items')
            ->with('success', 'Vendor berhasil diperbarui.')
            ->with('tab', 'vendor');
    }

    /**
     * Delete Master Vendor.
     */
    public function masterVendorDestroy($id)
    {
        try {
            $vendor = VehicleVendor::findOrFail($id);
            $vendor->delete();
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vendor berhasil dihapus.'
                ]);
            }
            return redirect()->route('vehicle.monitoring.master.items')
                ->with('success', 'Vendor berhasil dihapus.')
                ->with('tab', 'vendor');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus Vendor: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('vehicle.monitoring.master.items')
                ->with('error', 'Gagal menghapus Vendor: ' . $e->getMessage())
                ->with('tab', 'vendor');
        }
    }

    /**
     * Display the standalone History Report view.
     */
    public function historyIndex()
    {
        $locations = Location::where('s_loc', '!=', 'TMB')->get();
        return view('vehicle.monitoring.history', compact('locations'));
    }

    /**
     * Get historical log data for reports with comprehensive duration & movement tracking.
     */
    public function historyData(Request $request)
    {
        // Self-heal: pastikan transaksi yang sudah memiliki check_out_time berstatus completed
        VehicleTransaction::whereNotNull('check_out_time')
            ->where('status', 'timbangan_out')
            ->update(['status' => 'completed']);

        $query = VehicleTransaction::with([
            'vehicle',
            'item',
            'targetLocation',
            'tracking.location',
            'tracking.creator',
            'creator',
            'queueTakenBy',
            'startSamplingBy',
            'finishSamplingBy',
            'startLoadingBy',
            'finishLoadingBy',
            'timbanganOutBy',
            'checkOutBy',
        ])->where(function ($q) {
            $q->where('status', 'completed')
                ->orWhereNotNull('check_out_time');
        });

        if ($request->filled('start_date')) {
            $query->whereDate('check_in_time', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('check_in_time', '<=', $request->end_date);
        }
        if ($request->filled('target_location_id')) {
            $query->where('target_location_id', $request->target_location_id);
        }
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_transaction', 'LIKE', "%{$search}%")
                    ->orWhere('no_spb', 'LIKE', "%{$search}%")
                    ->orWhere('vendor', 'LIKE', "%{$search}%")
                    ->orWhere('nama_driver', 'LIKE', "%{$search}%")
                    ->orWhereHas('vehicle', function ($vQ) use ($search) {
                        $vQ->where('no_pol', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('item', function ($iQ) use ($search) {
                        $iQ->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $formatDuration = function ($sec) {
            if ($sec === null || $sec <= 0) return '0d';
            $h = floor($sec / 3600);
            $m = floor(($sec % 3600) / 60);
            $s = $sec % 60;
            $res = '';
            if ($h > 0) $res .= $h . 'j ';
            if ($m > 0 || $h > 0) $res .= $m . 'm ';
            $res .= $s . 'd';
            return trim($res);
        };

        $getUserName = function ($user) {
            if (!$user) return '-';
            return $user->nama_lengkap ?: ($user->username ?: '-');
        };

        $perPage = $request->input('per_page', 10);
        if ($perPage === 'all') {
            $totalCount = (clone $query)->count();
            $perPage = $totalCount > 0 ? $totalCount : 10;
        } else {
            $perPage = max(1, (int) $perPage);
        }

        $paginated = $query->orderBy('check_in_time', 'desc')->paginate($perPage);

        $paginated->getCollection()->transform(function ($tx) use ($formatDuration, $getUserName) {
            $checkIn = $tx->check_in_time;
            $checkOut = $tx->check_out_time;

            // 1. Total Durasi Siklus (Check-In to Check-Out)
            $totalDurationSeconds = ($checkIn && $checkOut) ? abs($checkOut->diffInSeconds($checkIn, false)) : 0;

            // 2. Durasi Tunggu (Waktu sejak check-in sampai ambil nomor antrian atau mulai proses)
            $waitSeconds = 0;
            $hasWait = false;
            if ($tx->queue_taken_time && $checkIn) {
                $waitSeconds = abs($tx->queue_taken_time->diffInSeconds($checkIn, false));
                $hasWait = true;
            } elseif ($tx->start_loading_time && $checkIn) {
                $waitSeconds = abs($tx->start_loading_time->diffInSeconds($checkIn, false));
                $hasWait = true;
            } elseif ($tx->start_sampling_time && $checkIn) {
                $waitSeconds = abs($tx->start_sampling_time->diffInSeconds($checkIn, false));
                $hasWait = true;
            }

            // 3. Durasi Antri (Waktu sejak ambil antrian sampai mulai proses aksi)
            $queueSeconds = 0;
            $hasQueue = false;
            if ($tx->queue_taken_time) {
                if ($tx->start_loading_time) {
                    $queueSeconds = abs($tx->start_loading_time->diffInSeconds($tx->queue_taken_time, false));
                    $hasQueue = true;
                } elseif ($tx->start_sampling_time) {
                    $queueSeconds = abs($tx->start_sampling_time->diffInSeconds($tx->queue_taken_time, false));
                    $hasQueue = true;
                }
            }

            // 4. Durasi Aksi (QC Sampling & Bongkar/Muat)
            $actionSeconds = 0;
            $actionDetails = [];

            // Aksi QC Sampling
            if ($tx->start_sampling_time && $tx->finish_sampling_time) {
                $qcSec = abs($tx->finish_sampling_time->diffInSeconds($tx->start_sampling_time, false));
                $actionSeconds += $qcSec;
                $actionDetails[] = [
                    'label' => 'QC Sampling',
                    'duration_sec' => $qcSec,
                    'duration_label' => $formatDuration($qcSec),
                    'start' => $tx->start_sampling_time->format('H:i'),
                    'finish' => $tx->finish_sampling_time->format('H:i'),
                    'action_by' => $getUserName($tx->finishSamplingBy ?: $tx->startSamplingBy),
                ];
            }

            // Aksi Bongkar / Muat
            if ($tx->start_loading_time && $tx->finish_loading_time) {
                $loadingSec = abs($tx->finish_loading_time->diffInSeconds($tx->start_loading_time, false));
                $actionSeconds += $loadingSec;
                $loadLabel = $tx->jenis === 'bongkaran' ? 'Bongkar' : 'Muat';
                $actionDetails[] = [
                    'label' => $loadLabel,
                    'duration_sec' => $loadingSec,
                    'duration_label' => $formatDuration($loadingSec),
                    'start' => $tx->start_loading_time->format('H:i'),
                    'finish' => $tx->finish_loading_time->format('H:i'),
                    'action_by' => $getUserName($tx->finishLoadingBy ?: $tx->startLoadingBy),
                ];
            } elseif ($actionSeconds === 0) {
                // Fallback dari tracking logs jika timestamps belum tercatat
                foreach ($tx->tracking as $track) {
                    $sLoc = $track->location ? $track->location->s_loc : '';
                    if ($sLoc && $sLoc !== 'TMB' && $track->duration_seconds) {
                        $trackSec = abs($track->duration_seconds);
                        if ($trackSec > 0) {
                            $actionSeconds += $trackSec;
                            $actionDetails[] = [
                                'label' => ($track->location ? $track->location->name : $sLoc),
                                'duration_sec' => $trackSec,
                                'duration_label' => $formatDuration($trackSec),
                                'start' => $track->arrival_time ? $track->arrival_time->format('H:i') : '-',
                                'finish' => $track->departure_time ? $track->departure_time->format('H:i') : '-',
                                'action_by' => $getUserName($track->creator),
                            ];
                        }
                    }
                }
            }

            // 5. Tracking Steps (Perpindahan Lokasi & Catatan)
            $trackingSteps = $tx->tracking->map(function ($track) use ($formatDuration, $getUserName) {
                $locName = $track->location ? $track->location->name : 'N/A';
                $locCode = $track->location ? $track->location->s_loc : '-';

                $isQcTrack = false;
                if ($track->status_notes && (
                    str_contains($track->status_notes, 'QC Hasil') ||
                    str_contains($track->status_notes, 'Sampling') ||
                    str_contains($track->status_notes, 'Dokumen')
                )) {
                    $isQcTrack = true;
                }

                if ($isQcTrack) {
                    $locName = 'QC (Quality Control)';
                    $locCode = 'QC';
                }

                $durSec = $track->duration_seconds ? abs($track->duration_seconds) : 0;

                return [
                    'id' => $track->id,
                    'location_name' => $locName,
                    'location_code' => $locCode,
                    'arrival_time' => $track->arrival_time ? $track->arrival_time->format('H:i') : '-',
                    'departure_time' => $track->departure_time ? $track->departure_time->format('H:i') : '-',
                    'arrival_full' => $track->arrival_time ? $track->arrival_time->format('d-m-Y H:i:s') : '-',
                    'departure_full' => $track->departure_time ? $track->departure_time->format('d-m-Y H:i:s') : '-',
                    'duration_sec' => $durSec,
                    'duration_label' => $formatDuration($durSec),
                    'status_notes' => $track->status_notes ?? '-',
                    'action_by' => $getUserName($track->creator),
                ];
            });

            // Path rute ringkas
            $historyPath = $trackingSteps->map(function ($step) {
                return "{$step['location_code']} ({$step['duration_label']})";
            })->implode(' ➔ ');

            return [
                'id' => $tx->id,
                'no_transaction' => $tx->no_transaction,
                'trnvisitorid' => $tx->trnvisitorid ?? '-',
                'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                'vendor' => $tx->vendor ?? '-',
                'nama_driver' => $tx->nama_driver ?? '-',
                'no_hp_driver' => $tx->no_hp_driver ?? '-',
                'checkin_pos1' => $tx->checkin_pos1 ? $tx->checkin_pos1->format('d-m-Y H:i') : '-',
                'jenis' => $tx->jenis,
                'item_name' => $tx->item ? $tx->item->name : 'N/A',
                'no_spb' => $tx->no_spb ?? '-',
                'qty_spb' => $tx->qty_spb ? number_format($tx->qty_spb, 2) : '-',
                'target_name' => $tx->targetLocation ? $tx->targetLocation->name : '-',
                'target_code' => $tx->targetLocation ? $tx->targetLocation->s_loc : '-',
                'no_antrian' => $tx->no_antrian,
                'qc_status' => $tx->qc_status,
                'check_in' => $checkIn ? $checkIn->format('d-m-Y H:i') : '-',
                'check_out' => $checkOut ? $checkOut->format('d-m-Y H:i') : '-',

                // Durasi-durasi
                'durasi_tunggu_sec' => $waitSeconds,
                'durasi_tunggu_label' => $hasWait ? $formatDuration($waitSeconds) : '-',
                'durasi_antri_sec' => $queueSeconds,
                'durasi_antri_label' => $hasQueue ? $formatDuration($queueSeconds) : '-',
                'durasi_aksi_sec' => $actionSeconds,
                'durasi_aksi_label' => $actionSeconds > 0 ? $formatDuration($actionSeconds) : '-',
                'total_durasi_sec' => $totalDurationSeconds,
                'total_durasi_label' => $totalDurationSeconds > 0 ? $formatDuration($totalDurationSeconds) : '-',
                'timbangan_out_time' => $tx->timbangan_out_time ? $tx->timbangan_out_time->format('d-m-Y H:i') : '-',
                'durasi_timbangan_out_sec' => $tx->timbangan_out_time ? abs(($checkOut ?: Carbon::now())->diffInSeconds($tx->timbangan_out_time, false)) : 0,
                'durasi_timbangan_out_label' => $tx->timbangan_out_time ? $formatDuration(abs(($checkOut ?: Carbon::now())->diffInSeconds($tx->timbangan_out_time, false))) : '-',

                // Detail Aksi & Rute
                'action_details' => $actionDetails,
                'tracking_steps' => $trackingSteps,
                'history_path' => $historyPath,

                // Timestamps lengkap
                'timestamps' => [
                    'checkin_pos1' => $tx->checkin_pos1 ? $tx->checkin_pos1->format('d-m-Y H:i:s') : '-',
                    'check_in' => $checkIn ? $checkIn->format('d-m-Y H:i:s') : '-',
                    'check_in_by' => $getUserName($tx->creator),
                    'queue_taken' => $tx->queue_taken_time ? $tx->queue_taken_time->format('d-m-Y H:i:s') : '-',
                    'queue_taken_by' => $getUserName($tx->queueTakenBy),
                    'start_sampling' => $tx->start_sampling_time ? $tx->start_sampling_time->format('d-m-Y H:i:s') : '-',
                    'start_sampling_by' => $getUserName($tx->startSamplingBy),
                    'finish_sampling' => $tx->finish_sampling_time ? $tx->finish_sampling_time->format('d-m-Y H:i:s') : '-',
                    'finish_sampling_by' => $getUserName($tx->finishSamplingBy),
                    'start_loading' => $tx->start_loading_time ? $tx->start_loading_time->format('d-m-Y H:i:s') : '-',
                    'start_loading_by' => $getUserName($tx->startLoadingBy),
                    'finish_loading' => $tx->finish_loading_time ? $tx->finish_loading_time->format('d-m-Y H:i:s') : '-',
                    'finish_loading_by' => $getUserName($tx->finishLoadingBy),
                    'timbangan_out' => $tx->timbangan_out_time ? $tx->timbangan_out_time->format('d-m-Y H:i:s') : '-',
                    'timbangan_out_by' => $getUserName($tx->timbanganOutBy),
                    'check_out' => $checkOut ? $checkOut->format('d-m-Y H:i:s') : '-',
                    'check_out_by' => $getUserName($tx->checkOutBy),
                ],
            ];
        });

        return response()->json($paginated);
    }

    /**
     * Format driver's phone number into WhatsApp mention/tag format (@628xxxxxxxx).
     */
    protected function formatDriverWaTag(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (empty($clean) || strlen($clean) < 7) {
            return '';
        }

        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }

        return '@' . $clean;
    }

    /**
     * Dapatkan sapaan humanis ramah berdasarkan waktu setempat dan variasi spintax.
     */
    protected function humanGreeting(): string
    {
        $hour = (int)Carbon::now()->format('H');
        if ($hour >= 5 && $hour < 11) {
            $timeGreeting = '{Selamat pagi|Pagi|Halo selamat pagi|Salam pagi|Halo|Semangat pagi|Selamat pagi menjelang siang}';
        } elseif ($hour >= 11 && $hour < 15) {
            $timeGreeting = '{Selamat siang|Siang|Halo selamat siang|Salam siang|Halo|Semangat siang}';
        } elseif ($hour >= 15 && $hour < 18) {
            $timeGreeting = '{Selamat sore|Sore|Halo selamat sore|Salam sore|Halo|Semangat sore}';
        } else {
            $timeGreeting = '{Selamat malam|Malam|Halo selamat malam|Salam malam|Halo}';
        }

        return $this->parseSpintax("{$timeGreeting} {Pak|Bapak/Ibu|Bpk|Bapak}");
    }

    /**
     * Parse template spintax seperti "{Halo|Siang|Salam}" secara acak.
     * Mendukung nested spintax dengan pengulangan parsing sampai bersih.
     */
    protected function parseSpintax(string $text): string
    {
        $maxIterations = 5;
        while ($maxIterations-- > 0 && preg_match('/\{([^{}]+)\}/', $text)) {
            $text = preg_replace_callback('/\{([^{}]+)\}/', function ($matches) {
                $parts = explode('|', $matches[1]);
                return trim($parts[array_rand($parts)]);
            }, $text);
        }
        return $text;
    }

    /**
     * Buat dynamic footer dengan timestamp detik & random ref ID
     * agar hash checksum pesan tidak identik di algoritma anti-spam Meta/WhatsApp.
     */
    protected function dynamicMessageFooter(string $prefix = 'BAS'): string
    {
        $timeStr = Carbon::now()->format('H:i:s');
        $randomRef = strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 4));
        return "\n\n_Ref: #{$prefix}-{$timeStr}-{$randomRef}_";
    }

    /**
     * Resolve driver name & phone safely (auto-detects if phone & name were swapped in inputs/database).
     */
    public function resolveDriverInfo(?string $namaDriver, ?string $noHpDriver): array
    {
        $name = trim($namaDriver ?? '');
        $phone = trim($noHpDriver ?? '');

        $nameDigitsOnly = preg_replace('/[^0-9]/', '', $name);

        // Jika "nama_driver" berisi angka no HP (>= 8 digit) dan "no_hp_driver" berisi huruf (nama), maka tertukar!
        if (strlen($nameDigitsOnly) >= 8 && preg_match('/^[0-9+ \-]+$/', $name) && preg_match('/[a-zA-Z]/', $phone)) {
            $temp = $name;
            $name = $phone;
            $phone = $temp;
        } elseif (empty($phone) && strlen($nameDigitsOnly) >= 8 && preg_match('/^[0-9+ \-]+$/', $name)) {
            $phone = $name;
            $name = 'Supir';
        }

        $tag = $this->formatDriverWaTag($phone);
        $name = $name ?: 'Supir';
        $display = !empty($phone) ? "{$name} ({$phone})" : $name;

        return [
            'name' => $name,
            'phone' => $phone,
            'tag' => $tag,
            'display' => $display,
        ];
    }

    /**
     * Dapatkan kuota dock maksimum berdasarkan kategori muatan:
     * - Slipsheet : 2 mobil
     * - Curah     : 4 mobil
     * - Bongkaran : 2 mobil (mencakup bongkaran di WFG dan SMU)
     */
    public function getDockQuota(string $category): int
    {
        return match (strtolower(trim($category))) {
            'slipsheet' => 2,
            'curah' => 4,
            'bongkaran', 'smu' => 2,
            default => 2,
        };
    }

    /**
     * Tentukan kategori dock dari transaksi truk:
     * - 'bongkaran' (jika jenis bongkaran atau area SMU)
     * - 'slipsheet'
     * - 'curah'
     */
    public function resolveDockCategory(VehicleTransaction $transaction, ?string $area = null): string
    {
        $areaUpper = strtoupper($area ?? $transaction->status ?? '');
        $jenis = strtolower(trim($transaction->jenis ?? ''));
        $transaction->loadMissing('targetLocation');
        $targetSloc = $transaction->targetLocation ? strtoupper($transaction->targetLocation->s_loc) : '';

        // Group 3: Area SMU atau WFG khusus jenis Bongkaran
        $isSmu = ($areaUpper === 'SMU') || in_array($targetSloc, ['A002', 'SMU']);
        $isWfgBongkaran = ($areaUpper === 'WFG' || $targetSloc === 'A001') && ($jenis === 'bongkaran');

        if ($isSmu || $isWfgBongkaran) {
            return 'bongkaran';
        }

        // Group 2: WFG Curah
        if ($jenis === 'curah') {
            return 'curah';
        }

        // Group 1: WFG Slipsheet
        return 'slipsheet';
    }

    /**
     * Hitung jumlah truk yang sedang aktif di dock (unloading_status = 'process').
     */
    public function getActiveDockOccupancy(string $category, ?int $excludeTxId = null): int
    {
        $cat = strtolower(trim($category));
        $query = VehicleTransaction::where(function ($q) {
            $q->where('unloading_status', 'process')
                ->orWhere(function ($q2) {
                    $q2->whereNotNull('start_loading_time')
                        ->whereNull('finish_loading_time')
                        ->whereNotIn('unloading_status', ['completed', 'cancelled']);
                });
        })
            ->whereNull('finish_loading_time')
            ->whereNull('check_out_time')
            ->whereNull('timbangan_out_time')
            ->whereNotIn('status', ['completed', 'timbangan_out']);

        if ($excludeTxId) {
            $query->where('id', '!=', $excludeTxId);
        }

        if ($cat === 'slipsheet') {
            $query->where(function ($q) {
                $q->where('status', 'wfg')->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
            })->where(function ($q) {
                $q->where('jenis', 'slipsheet')->orWhereNull('jenis')->orWhere('jenis', '');
            })->where('status', '!=', 'smu')
              ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU', 'B006', 'C001']));
        } elseif ($cat === 'curah') {
            $query->where(function ($q) {
                $q->where('status', 'wfg')->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
            })->where('jenis', 'curah')
              ->where('status', '!=', 'smu')
              ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU', 'B006', 'C001']));
        } elseif (in_array($cat, ['bongkaran', 'smu'])) {
            // HANYA Area SMU ATAU Area WFG khusus jenis Bongkaran (slot 2/2)
            // TIDAK boleh menghitung truk di WRM (B006), WPM (C001), QC, dll.
            $query->where(function ($q) {
                $q->where(function ($smuQ) {
                    $smuQ->where('status', 'smu')
                        ->orWhereHas('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU']));
                })->orWhere(function ($wfgQ) {
                    $wfgQ->where(function ($w) {
                        $w->where('status', 'wfg')
                            ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
                    })->where('jenis', 'bongkaran');
                });
            })
            ->whereNotIn('status', ['wrm', 'wpm'])
            ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['B006', 'C001']));
        }

        return $query->count();
    }

    /**
     * Cek apakah dock untuk kategori tersebut masih ada kuota kosong.
     */
    public function isDockSlotAvailable(string $category, ?int $excludeTxId = null): bool
    {
        $quota = $this->getDockQuota($category);
        $active = $this->getActiveDockOccupancy($category, $excludeTxId);
        return $active < $quota;
    }

    /**
     * 1. Notifikasi Info Nomor Antrian Mobil ke Supir.
     * Dikirim saat admin mengambil nomor antrian (WFG & SMU).
     * Jika kuota dock untuk kategori muatan belum penuh, supir LANGSUNG dipanggil masuk dock!
     * Dilengkapi instruksi konfirmasi balas 'OKE' untuk mencegah deteksi spam.
     */
    public function notifyDriverQueueTaken(VehicleTransaction $transaction, string $area = 'WFG'): array
    {
        try {
            $areaUpper = strtoupper($area);
            if (!in_array($areaUpper, ['WFG', 'SMU'])) {
                return ['status' => false, 'message' => 'Notifikasi info antrian hanya aktif untuk WFG dan SMU.'];
            }

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';
            $antrian = $transaction->no_antrian;
            if (empty($antrian)) {
                return ['status' => false, 'message' => 'Nomor antrian belum tersedia.'];
            }

            // Anti-spam / Duplicate Lock via Database (kebal deploy / cache:clear)
            if (!empty($transaction->notif_queue_taken_at)) {
                Log::info("[VehicleTracking] Info antrian untuk truk {$noPol} (#{$antrian}) sudah pernah tercatat pada {$transaction->notif_queue_taken_at}. Skip duplicate WA.");
                return ['status' => false, 'message' => 'Info nomor antrian sudah pernah dikirim.'];
            }

            $driver = $this->resolveDriverInfo($transaction->nama_driver, $transaction->no_hp_driver);
            if (empty($driver['phone'])) {
                Log::warning("[VehicleTracking] Nomor HP supir tidak tersedia untuk truk {$noPol}");
                return ['status' => false, 'message' => "Nomor HP supir untuk truk {$noPol} belum terdaftar."];
            }

            $jenis = strtolower(trim($transaction->jenis ?? ''));
            $category = $this->resolveDockCategory($transaction, $areaUpper);
            $quota = $this->getDockQuota($category);
            $activeOccupancy = $this->getActiveDockOccupancy($category, $transaction->id);
            $availableSlots = max(0, $quota - $activeOccupancy);

            // Hitung antrian sebelumnya di pool yang sama yang MASIH MENUNGGU di kantong parkir
            $waitingAheadQuery = VehicleTransaction::where('id', '!=', $transaction->id)
                ->whereNotNull('no_antrian')
                ->where(function ($q) {
                    $q->whereNull('unloading_status')
                        ->orWhereIn('unloading_status', ['pending', 'waiting', 'call', 'dipanggil']);
                })
                ->whereNull('start_loading_time')
                ->whereNull('finish_loading_time')
                ->whereNull('check_out_time')
                ->whereNull('timbangan_out_time')
                ->whereNull('follow_up_time')
                ->whereNotIn('status', ['completed', 'timbangan_out']);

            if ($category === 'slipsheet') {
                // Group 1: WFG - slipsheet
                $waitingAheadQuery->where(function ($q) {
                    $q->where('status', 'wfg')
                        ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
                })->where(function ($q) {
                    $q->where('jenis', 'slipsheet')->orWhereNull('jenis')->orWhere('jenis', '');
                })->whereNotIn('status', ['smu', 'wrm', 'wpm'])
                  ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU', 'B006', 'C001']));
            } elseif ($category === 'curah') {
                // Group 2: WFG - curah
                $waitingAheadQuery->where(function ($q) {
                    $q->where('status', 'wfg')
                        ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
                })->where('jenis', 'curah')
                  ->whereNotIn('status', ['smu', 'wrm', 'wpm'])
                  ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU', 'B006', 'C001']));
            } else {
                // Group 3: WFG - bongkaran == SMU bongkaran
                $waitingAheadQuery->where(function ($q) {
                    $q->where(function ($smuQ) {
                        $smuQ->where('status', 'smu')
                            ->orWhereHas('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU']));
                    })->orWhere(function ($wfgQ) {
                        $wfgQ->where(function ($w) {
                            $w->where('status', 'wfg')
                                ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
                        })->where('jenis', 'bongkaran');
                    });
                })->whereNotIn('status', ['wrm', 'wpm'])
                  ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['B006', 'C001']));
            }

            $currentDate = $transaction->queue_taken_time ? $transaction->queue_taken_time->toDateString() : ($transaction->created_at ? $transaction->created_at->toDateString() : date('Y-m-d'));
            $currentAntrianNum = (int)$antrian;

            $waitingAheadQuery->where(function ($q) use ($currentDate, $currentAntrianNum) {
                $q->whereDate(DB::raw('COALESCE(queue_taken_time, created_at)'), '<', $currentDate)
                    ->orWhere(function ($q2) use ($currentDate, $currentAntrianNum) {
                        $q2->whereDate(DB::raw('COALESCE(queue_taken_time, created_at)'), '=', $currentDate)
                            ->whereRaw('CAST(no_antrian AS UNSIGNED) < ?', [$currentAntrianNum]);
                    });
            });

            $waitingAheadCount = $waitingAheadQuery->count();

            // Truk hanya boleh langsung dipanggil masuk dock jika slot dock tersedia DAN tidak ada antrian di depannya yang mengantri slot tersebut
            $isDockAvailable = ($availableSlots > 0) && ($waitingAheadCount < $availableSlots);

            $categoryLabel = match ($category) {
                'slipsheet' => 'Slipsheet',
                'curah' => 'Curah',
                'bongkaran' => 'Bongkar (SMU/WFG)',
                default => ucfirst($category),
            };

            $jenisLabel = ($areaUpper === 'WFG' && !empty($jenis)) ? ' (' . ucfirst($jenis) . ')' : '';
            $actionLabel = ($category === 'bongkaran') ? 'Bongkar' : 'Muat';
            $now = Carbon::now()->format('d/m/Y H:i');

            $greeting = $this->humanGreeting();
            $footer = $this->dynamicMessageFooter('ANTRIAN');

            if ($isDockAvailable) {
                // Dock masih ada slot kosong dan tidak ada antrian menunggu di depan, langsung panggil masuk dock!
                $rawMessage = "{$greeting} *{$driver['name']}*,\n\n"
                    . "{Nomor antrian untuk kendaraan|Nomor antrian kendaraan Anda|Antrian untuk mobil|Informasi antrian kendaraan} *{$noPol}* {sudah terbit|sudah diterbitkan|telah dibuat|telah siap}:\n"
                    . "• No. Antrian : *#{$antrian}* ({$categoryLabel})\n"
                    . "• Area Dock   : *{$areaUpper}*\n"
                    . "• Status Dock : *Tersedia Slot Kosong*\n\n"
                    . "👉 {Silakan|Dipersilakan|Bisa|Mohon} *{segera|langsung|bisa segera} {merapat dan langsung masuk|menuju|merapat} ke Loading Dock {$areaUpper}* untuk {proses|melakukan proses|memulai proses} {$actionLabel} ya pak.\n\n"
                    . "⚠️ *{Perhatian|Catatan|Info penting}:* {Harap segera merapat ke area dock yaa|Mohon segera memposisikan mobil|Pastikan segera standby di depan dock|Diharapkan langsung merapat ya pak}. {Jika datang terlambat atau berhalangan|Apabila terlambat tanpa konfirmasi|Kalau berhalangan atau lambat merapat}, giliran slot dock {akan dialihkan ke antrian kendaraan lain|terpaksa digantikan truk berikutnya|akan digeser ke antrian lain}.\n\n"
                    . "{Bisa tolong balas sebentar ya pak jika sudah siap merapat?|Tolong dikabari yaa pak kalau sudah meluncur ke dock|Bisa konfirmasi balas chat ini ya pak kalau mobil sudah standby?|Mohon konfirmasinya ya pak jika sudah bersiap|Bisa balas chat ini sebentar kalau sudah siap?} 🙏"
                    . $footer;
                $message = $this->parseSpintax($rawMessage);
            } else {
                // Dock sedang penuh terisi ATAU sisa slot kosong sudah menjadi hak antrian-antrian di depan
                $statusDockText = ($activeOccupancy >= $quota)
                    ? "Penuh ({$activeOccupancy}/{$quota} Terisi)"
                    : "Terisi {$activeOccupancy}/{$quota}" . ($waitingAheadCount > 0 ? " ({$waitingAheadCount} Antrian di Depan Anda)" : "");

                $rawMessage = "{$greeting} *{$driver['name']}*,\n\n"
                    . "{Nomor antrian untuk kendaraan|Nomor antrian kendaraan Anda|Antrian untuk mobil|Informasi antrian kendaraan} *{$noPol}* {sudah terbit|sudah diterbitkan|telah dibuat|telah siap}:\n"
                    . "• No. Antrian : *#{$antrian}* ({$categoryLabel})\n"
                    . "• Area Dock   : *{$areaUpper}*\n"
                    . "• Status Dock : *{$statusDockText}*\n\n"
                    . "{Saat ini|Untuk saat ini} Loading Dock {$categoryLabel} {sedang ada aktivitas|masih terisi|sedang berjalan proses}" . ($waitingAheadCount > 0 ? " dan masih ada {$waitingAheadCount} truk antrian di depan Anda" : "") . ". {Silakan|Mohon|Bisa|Dipersilakan} *{istirahat atau standby|standby|menunggu} di kantong parkir* terlebih dahulu ya pak. {Nanti giliran masuk akan kami infokan via WhatsApp & layar monitor segera setelah dock siap|Informasi giliran masuk dock akan kami kabari segera via WhatsApp setelah ada slot kosong|Begitu ada slot dock yang kosong, akan langsung kami panggil via pesan ini}.\n\n"
                    . "{Kalau ada kendala atau butuh bantuan silakan balas chat ini ya pak|Bisa dibalas yaaa pak kalau ada pertanyaan|Terima kasih banyak atas kesabarannya menunggu|Mohon ditunggu sebentar ya pak, terima kasih atas kerja samanya}. 🙏"
                    . $footer;
                $message = $this->parseSpintax($rawMessage);
            }

            $cleanPhone = preg_replace('/[^0-9]/', '', $driver['phone']);
            $fonnte = app(FonnteService::class);
            $res = $fonnte->queueMessage($cleanPhone, $message, 120, [], "Info Antrian #{$antrian} Truk {$noPol}");

            if (!empty($res['status'])) {
                $updateData = ['notif_queue_taken_at' => Carbon::now()];
                if ($isDockAvailable) {
                    $updateData['notif_prepare_at'] = Carbon::now();
                }
                $transaction->update($updateData);
                Log::info("[VehicleTracking] Info antrian #{$antrian} (" . ($isDockAvailable ? "Langsung Masuk Dock" : "Standby Parkir") . ") berhasil dijadwalkan ke supir {$noPol} ({$cleanPhone})");

                event(new VehicleStatusUpdated([
                    'type' => $isDockAvailable ? 'dock_call' : 'queue_assigned',
                    'transaction_id' => $transaction->id,
                    'no_pol' => $noPol,
                    'no_antrian' => $antrian,
                    'message' => $isDockAvailable ? "Panggilan Masuk Dock: Truk {$noPol} (Antrian #{$antrian})" : "Antrian Diterbitkan: Truk {$noPol} (Antrian #{$antrian})"
                ]));
            }

            return $res;
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim WA info antrian ({$area}): " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 2. Notifikasi Supir Antrian Berikutnya untuk BERSIAP-SIAP.
     * Dikirim ketika truk saat ini MULAI MUAT / BONGKAR (atau checker isi form draft bongkar muat).
     * Di WFG, antrian dipisahkan berdasarkan jenis (Bongkaran, Slipsheet, Curah). Di SMU tanpa jenis.
     * Tidak berjalan jika truk saat ini atau truk berikutnya ada followup dari timbangan.
     */
    public function notifyNextDriverToPrepare(VehicleTransaction $currentTx, string $area = 'WFG'): array
    {
        try {
            $areaUpper = strtoupper($area);

            // 1. Hanya di WFG dan SMU dulu (Point 1)
            if (!in_array($areaUpper, ['WFG', 'SMU'])) {
                return ['status' => false, 'message' => 'Notifikasi siap-siap supir hanya aktif untuk WFG dan SMU.'];
            }

            // 6. Tidak berjalan ketika ada followup dari timbangan (Point 6)
            if (!empty($currentTx->follow_up_time)) {
                Log::info("[VehicleTracking] Skip notif siap-siap: Truk {$currentTx->id} dalam status follow up dari timbangan.");
                return ['status' => false, 'message' => 'Dilewati: Truk saat ini memiliki status follow up dari timbangan.'];
            }

            $currentCategory = $this->resolveDockCategory($currentTx, $areaUpper);
            $currentJenis = strtolower(trim($currentTx->jenis ?? ''));

            $nextQuery = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])
                ->where('id', '!=', $currentTx->id)
                ->whereNotNull('no_antrian')
                ->where(function ($q) {
                    $q->whereNull('unloading_status')
                        ->orWhereNotIn('unloading_status', ['process', 'completed', 'complete']);
                })
                ->whereNull('start_loading_time')
                ->whereNull('finish_loading_time')
                ->whereNull('check_out_time')
                ->whereNull('timbangan_out_time')
                ->whereNull('follow_up_time'); // Point 6: Lewati jika truk berikutnya ada follow up timbangan

            // Pisahkan antrian secara ketat berdasarkan pool dock:
            if ($currentCategory === 'slipsheet') {
                // Group 1: WFG - slipsheet
                $nextQuery->where(function ($q) {
                    $q->where('status', 'wfg')->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
                })->where(function ($q) {
                    $q->where('jenis', 'slipsheet')->orWhereNull('jenis')->orWhere('jenis', '');
                })->whereNotIn('status', ['smu', 'wrm', 'wpm'])
                  ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU', 'B006', 'C001']));
            } elseif ($currentCategory === 'curah') {
                // Group 2: WFG - curah
                $nextQuery->where(function ($q) {
                    $q->where('status', 'wfg')->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
                })->where('jenis', 'curah')
                  ->whereNotIn('status', ['smu', 'wrm', 'wpm'])
                  ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU', 'B006', 'C001']));
            } else {
                // Group 3: WFG - bongkaran == SMU bongkaran
                $nextQuery->where(function ($q) {
                    $q->where(function ($smuQ) {
                        $smuQ->where('status', 'smu')
                            ->orWhereHas('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU']));
                    })->orWhere(function ($wfgQ) {
                        $wfgQ->where(function ($w) {
                            $w->where('status', 'wfg')
                                ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
                        })->where('jenis', 'bongkaran');
                    });
                })->whereNotIn('status', ['wrm', 'wpm'])
                  ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['B006', 'C001']));
            }

            // Utamakan antrian hari sebelumnya yang belum selesai (Carry-Over), lalu urutan antrian berikutnya
            $currentDate = $currentTx->queue_taken_time ? $currentTx->queue_taken_time->toDateString() : ($currentTx->created_at ? $currentTx->created_at->toDateString() : date('Y-m-d'));
            $currentAntrianNum = (int)$currentTx->no_antrian;
            $nextTx = null;

            if ($currentAntrianNum > 0) {
                $nextTx = (clone $nextQuery)
                    ->where(function ($q) use ($currentDate, $currentAntrianNum) {
                        $q->where(function ($q2) use ($currentDate, $currentAntrianNum) {
                            $q2->whereDate(DB::raw('COALESCE(queue_taken_time, created_at)'), '=', $currentDate)
                                ->whereRaw('CAST(no_antrian AS UNSIGNED) > ?', [$currentAntrianNum]);
                        })->orWhereDate(DB::raw('COALESCE(queue_taken_time, created_at)'), '>', $currentDate);
                    })
                    ->orderByRaw('COALESCE(DATE(queue_taken_time), DATE(created_at)) ASC, CAST(no_antrian AS UNSIGNED) ASC')
                    ->first();
            }

            if (!$nextTx) {
                $nextTx = $nextQuery
                    ->orderByRaw('COALESCE(DATE(queue_taken_time), DATE(created_at)) ASC, CAST(no_antrian AS UNSIGNED) ASC')
                    ->first();
            }

            if (!$nextTx) {
                Log::info("[VehicleTracking] Tidak ada truk antrian berikutnya di {$areaUpper}" . ($areaUpper === 'WFG' && $currentJenis ? " ({$currentJenis})" : '') . " yang perlu dipersiapkan.");
                return ['status' => false, 'message' => 'Tidak ada antrian berikutnya.'];
            }

            $currentNoPol = $currentTx->vehicle ? $currentTx->vehicle->no_pol : 'N/A';
            $nextNoPol = $nextTx->vehicle ? $nextTx->vehicle->no_pol : 'N/A';

            // Anti-spam / Duplicate Lock: Jika truk antrian berikutnya sudah pernah dikirimi pesan siap-siap atau dipanggil dock,
            // JANGAN kirim ulang ke truk ini, dan JANGAN loncat broadcast ke antrian nomor berikutnya (misal #5).
            // Langsung hentikan proses (notifikasi persiapan cukup sekali saja per transaksi antrian).
            $hasBeenPrepared = !empty($nextTx->notif_prepare_at)
                || Cache::has("driver_prepare_notif_{$nextTx->id}")
                || Cache::has("driver_dock_called_{$nextTx->id}");
            if ($hasBeenPrepared) {
                Log::info("[VehicleTracking] Truk antrian berikutnya {$nextNoPol} (#{$nextTx->no_antrian}) sudah pernah mendapatkan notif siap-siap/panggilan dock sebelumnya (DB: {$nextTx->notif_prepare_at}). Skip (tidak kirim ulang dan tidak broadcast ke antrian berikutnya).");
                return [
                    'status' => false,
                    'message' => "Truk {$nextNoPol} (#{$nextTx->no_antrian}) sudah pernah mendapatkan notifikasi siap-siap/panggilan dock sebelumnya."
                ];
            }

            $nextDriver = $this->resolveDriverInfo($nextTx->nama_driver, $nextTx->no_hp_driver);
            if (empty($nextDriver['phone'])) {
                Log::warning("[VehicleTracking] Nomor HP supir tidak tersedia untuk truk {$nextNoPol}");
                return ['status' => false, 'message' => "Nomor HP supir untuk truk {$nextNoPol} belum terdaftar."];
            }

            $jenisLabel = ($areaUpper === 'WFG' && !empty($nextTx->jenis)) ? ' (' . ucfirst($nextTx->jenis) . ')' : '';
            $actionLabel = ($nextTx->jenis === 'bongkaran') ? 'Bongkar' : 'Muat';

            $greeting = $this->humanGreeting();
            $footer = $this->dynamicMessageFooter('PERSIAPAN');

            $rawMessage = "{$greeting} *{$nextDriver['name']}*,\n\n"
                . "{Sekadar info|Pemberitahuan antrian|Informasi antrian|Info persiapan}, truk antrian sebelum Anda (*{$currentNoPol}*) saat ini {sudah|telah} *mulai proses {$actionLabel}* di Loading Dock {$areaUpper}.\n\n"
                . "• Truk Anda   : *{$nextNoPol}*\n"
                . "• No. Antrian : *#{$nextTx->no_antrian}* ({$areaUpper}{$jenisLabel})\n\n"
                . "👉 {Mohon|Dipersilakan|Bisa|Harap} *{mulai bersiap-siap dan merapat|bersiap-siap dan mendekat|mulai memposisikan mobil} ke dekat area dock* ya pak. {Begitu truk di depan selesai|Setelah proses truk sebelumnya rampung|Saat slot di depan sudah selesai}, giliran Anda akan {langsung dipanggil masuk|segera dipanggil|langsung masuk ke dock}.\n\n"
                . "⚠️ *{Perhatian|Catatan penting|Penting}:* {Jangan sampai terlambat ya pak|Pastikan mobil sudah dalam posisi siap|Harap standby di sekitar area dock yaa}. {Kalau pas dipanggil belum siap, urutan dock terpaksa dialihkan ke antrian berikutnya|Jika terlambat merapat, slot dock akan digantikan truk lain|Apabila belum siap saat dipanggil, nomor antrian dapat digeser ke antrian setelahnya}.\n\n"
                . "{Bisa tolong balas sebentar ya pak kalau sudah standby di dekat dock?|Tolong kabari ya pak kalau sudah posisi siap|Bisa konfirmasi balas pesan ini yaa kalau sudah bersiap?|Mohon konfirmasi singkatnya ya pak jika sudah standby|Bisa kabari lewat pesan ini kalau sudah siap?} 🙏"
                . $footer;
            $message = $this->parseSpintax($rawMessage);

            $cleanPhone = preg_replace('/[^0-9]/', '', $nextDriver['phone']);
            $fonnte = app(FonnteService::class);
            $res = $fonnte->queueMessage($cleanPhone, $message, 120, [], "Siap-siap Truk {$nextNoPol}");

            if (!empty($res['status'])) {
                $nextTx->update(['notif_prepare_at' => Carbon::now()]);
                Cache::put("driver_prepare_notif_{$nextTx->id}", true, 86400 * 2);
                Log::info("[VehicleTracking] Notif siap-siap berhasil dijadwalkan ke supir berikutnya {$nextNoPol} ({$cleanPhone}) untuk antrian {$areaUpper}{$jenisLabel}");
            }

            return $res;
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim WA siap-siap supir ({$area}): " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 3. Notifikasi Supir SELESAI Proses Muat/Bongkar.
     * Dikirim saat checker submit form atau admin klik Selesai Bongkar/Muat (WFG & SMU).
     * Tidak berjalan jika truk ada followup dari timbangan.
     */
    public function notifyDriverCompleted(VehicleTransaction $transaction, string $area = 'WFG'): array
    {
        try {
            $areaUpper = strtoupper($area);

            // Aktif untuk WFG, SMU, WRM, dan WPM
            if (!in_array($areaUpper, ['WFG', 'SMU', 'WRM', 'WPM'])) {
                return ['status' => false, 'message' => 'Notifikasi selesai hanya aktif untuk WFG, SMU, WRM, dan WPM.'];
            }

            // 6. Tidak berjalan ketika ada followup dari timbangan (Point 6)
            if (!empty($transaction->follow_up_time)) {
                Log::info("[VehicleTracking] Skip notif selesai supir: Truk {$transaction->id} memiliki follow up dari timbangan.");
                return ['status' => false, 'message' => 'Dilewati: Truk dalam status follow up dari timbangan.'];
            }

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            // Anti-spam / Duplicate Lock via Database (kebal deploy / cache:clear)
            if (!empty($transaction->notif_completed_at)) {
                Log::info("[VehicleTracking] Notifikasi selesai muat/bongkar untuk truk {$noPol} sudah pernah tercatat pada {$transaction->notif_completed_at}. Skip duplicate WA.");
                return ['status' => false, 'message' => "Notifikasi selesai untuk truk {$noPol} sudah pernah dikirim."];
            }

            $driver = $this->resolveDriverInfo($transaction->nama_driver, $transaction->no_hp_driver);
            if (empty($driver['phone'])) {
                Log::warning("[VehicleTracking] Nomor HP supir tidak tersedia untuk truk {$noPol}");
                return ['status' => false, 'message' => "Nomor HP supir untuk truk {$noPol} belum terdaftar."];
            }

            $jenisLabel = ($areaUpper === 'WFG' && !empty($transaction->jenis)) ? ' (' . ucfirst($transaction->jenis) . ')' : '';
            $actionLabel = ($transaction->jenis === 'bongkaran' || $areaUpper === 'WRM') ? 'Bongkar' : 'Muat';
            $now = Carbon::now()->format('d/m/Y H:i');

            $greeting = $this->humanGreeting();
            $footer = $this->dynamicMessageFooter('SELESAI');

            $rawMessage = "{$greeting} *{$driver['name']}*,\n\n"
                . "{Proses|Pekerjaan|Aktivitas} {$actionLabel} untuk truk *{$noPol}* di Dock *{$areaUpper}* {telah selesai|sudah selesai|telah rampung} pada pukul {$now}.\n\n"
                . "{Silakan|Dipersilakan|Bisa|Mohon} {menyelesaikan administrasi dokumen dan segera melanjutkan kendaraan|mengurus administrasi dokumen dan membawa kendaraan|melengkapi dokumen dan mengarahkan truk} ke *Timbangan Out* (Timbang Keluar) ya pak.\n\n"
                . "{Terima kasih atas kerja samanya|Matur nuwun|Semoga perjalanan lancar dan selamat sampai tujuan|Terima kasih banyak dan hati-hati di jalan ya pak|Terima kasih atas kerjasamanya dan tetap utamakan keselamatan}. 🙏"
                . $footer;
            $message = $this->parseSpintax($rawMessage);

            $cleanPhone = preg_replace('/[^0-9]/', '', $driver['phone']);
            $fonnte = app(FonnteService::class);
            $res = $fonnte->queueMessage($cleanPhone, $message, 120, [], "Selesai Muat/Bongkar Truk {$noPol}");

            if (!empty($res['status'])) {
                $transaction->update(['notif_completed_at' => Carbon::now()]);
                Log::info("[VehicleTracking] Notif selesai proses berhasil dijadwalkan ke supir {$noPol} ({$cleanPhone})");
            }

            return $res;
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim WA selesai proses ({$area}): " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Pemanggilan Otomatis Truk Antrian Berikutnya ke Dock (WRM & WPM).
     * Dipicu ketika salah satu truk selesai proses bongkar/muat di WRM / WPM.
     * Prioritas pemilihan antrian:
     * 1. Status hasil sampling QC sudah 'released' (urutkan id ASC)
     * 2. Item 'Import' untuk WRM / item 'Packaging' untuk WPM / muatan retur (urutkan id ASC)
     */
    public function notifyNextDriverToEnterDock(VehicleTransaction $completedTx, string $area = 'WRM'): array
    {
        try {
            $areaUpper = strtoupper($area);

            // Hanya aktif untuk WRM dan WPM
            if (!in_array($areaUpper, ['WRM', 'WPM'])) {
                return ['status' => false, 'message' => 'Pemanggilan otomatis saat ini khusus untuk WRM dan WPM.'];
            }

            // Query kandidat truk yang sedang menunggu di area terkait
            $candidatesQuery = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])
                ->where('id', '!=', $completedTx->id)
                ->where(function ($q) use ($areaUpper) {
                    if ($areaUpper === 'WRM') {
                        $q->where('status', 'wrm_bongkar')
                            ->orWhereHas('targetLocation', function ($tl) {
                                $tl->where('s_loc', 'B006');
                            });
                    } else {
                        $q->where('status', 'wpm')
                            ->orWhereHas('targetLocation', function ($tl) {
                                $tl->where('s_loc', 'C001');
                            });
                    }
                })
                ->whereNotIn('status', ['completed', 'timbangan_out'])
                ->where(function ($q) {
                    $q->whereNull('unloading_status')->orWhere('unloading_status', 'pending');
                })
                ->whereNull('start_loading_time')
                ->whereNull('finish_loading_time')
                ->whereNull('follow_up_time')
                ->whereNotNull('no_hp_driver');

            // Prioritas 1: Dahulukan yang status hasil sampling QC nya RELEASED (urut tanggal & id ASC)
            $nextTx = (clone $candidatesQuery)
                ->where('qc_status', 'released')
                ->orderByRaw('COALESCE(DATE(check_in_time), DATE(created_at)) ASC, id ASC')
                ->first();

            // Prioritas 2: Jika tidak ada yang released, dahulukan item 'Import' untuk WRM (urut tanggal & id ASC)
            if (!$nextTx) {
                if ($areaUpper === 'WRM') {
                    $nextTx = (clone $candidatesQuery)
                        ->where(function ($q) {
                            $q->whereHas('item', function ($iq) {
                                $iq->where('name', 'LIKE', '%IMPORT%');
                            })->orWhere('jenis', 'retur');
                        })
                        ->orderByRaw('COALESCE(DATE(check_in_time), DATE(created_at)) ASC, id ASC')
                        ->first();
                } else {
                    $nextTx = (clone $candidatesQuery)
                        ->where(function ($q) {
                            $q->whereHas('item', function ($iq) {
                                $iq->where('name', 'LIKE', '%PACKAGING%');
                            })->orWhere('jenis', 'retur');
                        })
                        ->orderByRaw('COALESCE(DATE(check_in_time), DATE(created_at)) ASC, id ASC')
                        ->first();
                }
            }

            if (!$nextTx) {
                Log::info("[VehicleTracking] Tidak ada kandidat truk antrian berikutnya di {$areaUpper} yang memenuhi syarat (Released / Import).");
                return ['status' => false, 'message' => 'Tidak ada antrian berikutnya yang siap dipanggil.'];
            }

            $nextNoPol = $nextTx->vehicle ? $nextTx->vehicle->no_pol : 'N/A';

            // Anti-spam / Duplicate Lock: jangan panggil supir yang sama jika sudah pernah dipanggil masuk dock
            if (Cache::has("driver_dock_called_{$nextTx->id}")) {
                Log::info("[VehicleTracking] Truk {$nextNoPol} sudah pernah dipanggil masuk dock sebelumnya. Skip duplicate WA.");
                return ['status' => false, 'message' => "Truk {$nextNoPol} baru saja dipanggil sebelumnya."];
            }

            $driver = $this->resolveDriverInfo($nextTx->nama_driver, $nextTx->no_hp_driver);
            if (empty($driver['phone'])) {
                Log::warning("[VehicleTracking] Nomor HP supir tidak tersedia untuk truk {$nextNoPol}");
                return ['status' => false, 'message' => "Nomor HP supir untuk truk {$nextNoPol} belum terdaftar."];
            }

            $actionLabel = ($nextTx->jenis === 'bongkaran' || $areaUpper === 'WRM') ? 'Bongkar' : 'Muat';

            $hour = (int)Carbon::now()->format('H');
            if ($hour >= 5 && $hour < 11) {
                $timeWord = 'pagi';
            } elseif ($hour >= 11 && $hour < 15) {
                $timeWord = 'siang';
            } elseif ($hour >= 15 && $hour < 18) {
                $timeWord = 'sore';
            } else {
                $timeWord = 'malam';
            }

            $driverName = $driver['name'] ?: 'Supir';
            $casualGreeting = "{Selamat {$timeWord}|{$timeWord}|Halo {$timeWord}|Halo} {pak|bapak} *{$driverName}*";
            $footer = $this->dynamicMessageFooter('DOCK');
            $antrianNote = !empty($nextTx->no_antrian) ? " (Antrian #{$nextTx->no_antrian})" : "";

            $rawMessage = "{$casualGreeting},\n\n"
                . "{kendaraan|truk|mobil} *{$nextNoPol}*{$antrianNote} {slot dock|dock} *{$areaUpper}* {sudah siap ya pak|sudah kosong ya pak|sudah ready ya pak}, {mohon segera merapat|bisa langsung merapat dan masuk|tolong segera menuju|bisa segera memposisikan mobil} ke dock untuk proses {$actionLabel} ya {pakk|pak|yaa pak}, dan {tolong konfirmasinya yaa|mohon konfirmasinya ya pak|bisa tolong konfirmasinya ya pak jika mobil sudah bergerak}. 🙏"
                . $footer;
            $message = $this->parseSpintax($rawMessage);

            $cleanPhone = preg_replace('/[^0-9]/', '', $driver['phone']);
            $fonnte = app(FonnteService::class);
            $res = $fonnte->queueMessage($cleanPhone, $message, 120, [], "Panggilan Masuk Dock Truk {$nextNoPol}");

            if (!empty($res['status'])) {
                $nextTx->update(['notif_prepare_at' => Carbon::now()]);
                Cache::put("driver_dock_called_{$nextTx->id}", true, 86400 * 2);
                Log::info("[VehicleTracking] Panggilan otomatis ke dock {$areaUpper} berhasil dijadwalkan ke supir {$nextNoPol} ({$cleanPhone})");
            }

            return $res;
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim WA panggilan dock ({$area}): " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Pemanggilan Otomatis Truk Antrian Berikutnya ke Loading Dock WFG / SMU.
     * Dipicu ketika salah satu truk selesai proses bongkar/muat di WFG / SMU.
     * Logika kuota dock:
     * - Slipsheet : 2 mobil
     * - Curah     : 4 mobil
     * - Bongkaran : 2 mobil (mencakup bongkaran di WFG dan SMU)
     */
    public function notifyNextDriverToEnterDockWfgOrSmu(VehicleTransaction $completedTx, string $area = 'WFG'): array
    {
        try {
            $areaUpper = strtoupper($area);
            if (!in_array($areaUpper, ['WFG', 'SMU'])) {
                return ['status' => false, 'message' => 'Hanya aktif untuk WFG dan SMU.'];
            }

            $category = $this->resolveDockCategory($completedTx, $areaUpper);
            $quota = $this->getDockQuota($category);
            $activeOccupancy = $this->getActiveDockOccupancy($category, $completedTx->id);

            // Jika dock masih terisi penuh sesuai kuota, jangan panggil dulu
            if ($activeOccupancy >= $quota) {
                Log::info("[VehicleTracking] Dock {$category} masih terisi ({$activeOccupancy}/{$quota}). Belum ada slot kosong untuk dipanggil.");
                return ['status' => false, 'message' => "Dock {$category} masih penuh."];
            }

            // Cari kandidat antrian berikutnya yang menunggu di kategori dock tersebut
            $query = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])
                ->where('id', '!=', $completedTx->id)
                ->whereNotNull('no_antrian')
                ->where(function ($q) {
                    $q->whereNull('unloading_status')
                        ->orWhereIn('unloading_status', ['pending', 'waiting']);
                })
                ->whereNull('start_loading_time')
                ->whereNull('finish_loading_time')
                ->whereNull('check_out_time')
                ->whereNull('timbangan_out_time')
                ->whereNull('follow_up_time')
                ->whereNotIn('status', ['completed', 'timbangan_out']);

            if ($category === 'slipsheet') {
                // Group 1: WFG - slipsheet
                $query->where(function ($q) {
                    $q->where('status', 'wfg')
                        ->orWhereHas('targetLocation', function ($tl) {
                            $tl->where('s_loc', 'A001');
                        });
                })->where(function ($q) {
                    $q->where('jenis', 'slipsheet')->orWhereNull('jenis')->orWhere('jenis', '');
                })->whereNotIn('status', ['smu', 'wrm', 'wpm'])
                  ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU', 'B006', 'C001']));
            } elseif ($category === 'curah') {
                // Group 2: WFG - curah
                $query->where(function ($q) {
                    $q->where('status', 'wfg')
                        ->orWhereHas('targetLocation', function ($tl) {
                            $tl->where('s_loc', 'A001');
                        });
                })->where('jenis', 'curah')
                  ->whereNotIn('status', ['smu', 'wrm', 'wpm'])
                  ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU', 'B006', 'C001']));
            } elseif ($category === 'bongkaran') {
                // Group 3: WFG - bongkaran == SMU bongkaran
                $query->where(function ($q) {
                    $q->where(function ($smuQ) {
                        $smuQ->where('status', 'smu')
                            ->orWhereHas('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['A002', 'SMU']));
                    })->orWhere(function ($wfgQ) {
                        $wfgQ->where(function ($w) {
                            $w->where('status', 'wfg')
                                ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'A001'));
                        })->where('jenis', 'bongkaran');
                    });
                })->whereNotIn('status', ['wrm', 'wpm'])
                  ->whereDoesntHave('targetLocation', fn($tl) => $tl->whereIn('s_loc', ['B006', 'C001']));
            }

            // Prioritaskan carry-over (hari kemarin) terlebih dahulu, lalu urutan no antrian ASC
            $nextTx = $query
                ->orderByRaw('CASE WHEN no_antrian IS NULL THEN 1 ELSE 0 END, COALESCE(DATE(queue_taken_time), DATE(created_at)) ASC, CAST(no_antrian AS UNSIGNED) ASC, id ASC')
                ->first();

            if (!$nextTx) {
                Log::info("[VehicleTracking] Tidak ada antrian berikutnya yang menunggu untuk dock {$category}.");
                return ['status' => false, 'message' => "Tidak ada antrian berikutnya untuk dock {$category}."];
            }

            $nextNoPol = $nextTx->vehicle ? $nextTx->vehicle->no_pol : 'N/A';

            // Anti-spam / Duplicate Lock: jangan panggil supir yang sama jika sudah pernah dipanggil masuk dock
            if (Cache::has("driver_dock_called_{$nextTx->id}")) {
                Log::info("[VehicleTracking] Truk {$nextNoPol} sudah pernah dipanggil masuk dock sebelumnya. Skip duplicate WA.");
                return ['status' => false, 'message' => "Truk {$nextNoPol} baru saja dipanggil sebelumnya."];
            }

            $driver = $this->resolveDriverInfo($nextTx->nama_driver, $nextTx->no_hp_driver);
            if (empty($driver['phone'])) {
                Log::warning("[VehicleTracking] Nomor HP supir tidak tersedia untuk truk {$nextNoPol}");
                return ['status' => false, 'message' => "Nomor HP supir untuk truk {$nextNoPol} belum terdaftar."];
            }

            $targetAreaUpper = strtoupper($nextTx->status ?? $areaUpper);
            $nextJenis = strtolower(trim($nextTx->jenis ?? ''));
            $categoryLabel = match ($category) {
                'slipsheet' => 'Slipsheet',
                'curah' => 'Curah',
                'bongkaran' => 'Bongkar (SMU/WFG)',
                default => ucfirst($category),
            };
            $jenisLabel = ($targetAreaUpper === 'WFG' && !empty($nextJenis)) ? ' (' . ucfirst($nextJenis) . ')' : '';
            $actionLabel = ($category === 'bongkaran') ? 'Bongkar' : 'Muat';

            $hour = (int)Carbon::now()->format('H');
            if ($hour >= 5 && $hour < 11) {
                $timeWord = 'pagi';
            } elseif ($hour >= 11 && $hour < 15) {
                $timeWord = 'siang';
            } elseif ($hour >= 15 && $hour < 18) {
                $timeWord = 'sore';
            } else {
                $timeWord = 'malam';
            }

            $driverName = $driver['name'] ?: 'Supir';
            $casualGreeting = "{Selamat {$timeWord}|{$timeWord}|Halo {$timeWord}|Halo} {pak|bapak} *{$driverName}*";
            $footer = $this->dynamicMessageFooter('DOCK');

            $antrianNote = !empty($nextTx->no_antrian) ? " (Antrian #{$nextTx->no_antrian})" : "";
            $slotLabel = $categoryLabel ? " {$categoryLabel}" : "";

            $rawMessage = "{$casualGreeting},\n\n"
                . "{kendaraan|truk|mobil} *{$nextNoPol}*{$antrianNote} slot dock *{$targetAreaUpper}{$slotLabel}* {sudah siap ya pak|sudah kosong ya pak|sudah ready ya pak}, {mohon segera merapat|bisa langsung merapat dan masuk|tolong segera menuju|bisa segera memposisikan mobil} ke dock untuk proses {$actionLabel} ya {pakk|pak|yaa pak}, dan {tolong konfirmasinya yaa|mohon konfirmasinya ya pak|bisa tolong konfirmasinya ya pak jika mobil sudah bergerak}. 🙏"
                . $footer;
            $message = $this->parseSpintax($rawMessage);

            $cleanPhone = preg_replace('/[^0-9]/', '', $driver['phone']);
            $fonnte = app(FonnteService::class);
            $res = $fonnte->queueMessage($cleanPhone, $message, 120, [], "Panggilan Masuk Dock {$targetAreaUpper} Truk {$nextNoPol}");

            if (!empty($res['status'])) {
                $nextTx->update(['notif_prepare_at' => Carbon::now()]);
                Cache::put("driver_dock_called_{$nextTx->id}", true, 86400 * 2);
                Log::info("[VehicleTracking] Panggilan otomatis masuk dock {$targetAreaUpper} ({$category}) berhasil dijadwalkan ke supir {$nextNoPol} ({$cleanPhone})");

                event(new VehicleStatusUpdated([
                    'type' => 'dock_call',
                    'action' => 'dock_call',
                    'transaction_id' => $nextTx->id,
                    'no_pol' => $nextNoPol,
                    'no_antrian' => $nextTx->no_antrian,
                    'target_area' => $targetAreaUpper,
                    'message' => "Panggilan Masuk Dock {$targetAreaUpper}: Truk {$nextNoPol} (Antrian #{$nextTx->no_antrian})"
                ]));
            }

            return $res;
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim WA panggilan masuk dock WFG/SMU: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Helper to detect QC Target Area (WRM or WPM).
     */
    protected function getQcTargetArea(VehicleTransaction $transaction): string
    {
        $transaction->loadMissing('targetLocation');
        if ($transaction->targetLocation) {
            if ($transaction->targetLocation->s_loc === 'C001') return 'WPM';
            if ($transaction->targetLocation->s_loc === 'B006') return 'WRM';
            if ($transaction->targetLocation->s_loc === 'A001') return 'WFG';
            if ($transaction->targetLocation->s_loc === 'A002') return 'SMU';
        }
        if (str_contains($transaction->status ?? '', 'wpm')) return 'WPM';
        if (str_contains($transaction->status ?? '', 'wrm')) return 'WRM';
        if (str_contains($transaction->status ?? '', 'wfg')) return 'WFG';
        if (str_contains($transaction->status ?? '', 'smu')) return 'SMU';
        return 'WRM';
    }

    /**
     * Helper to check if item is an exception item ('Packaging', 'Import') that doesn't need to wait for QC release.
     */
    protected function isQcExceptionItem(VehicleTransaction $transaction): bool
    {
        $transaction->loadMissing('item');
        if (!$transaction->item) {
            return false;
        }
        $itemName = strtoupper(trim($transaction->item->name ?? ''));
        return str_contains($itemName, 'PACKAGING') || str_contains($itemName, 'IMPORT');
    }

    /**
     * Poin 1: Notifikasi Serahkan Dokumen ke Lab QC untuk Sampling.
     * Dikirim saat truk check-in di timbangan dan memerlukan sampling QC (WRM & WPM).
     */
    public function notifyDriverQcDocument(VehicleTransaction $transaction, string $area = 'WRM'): array
    {
        try {
            $transaction->loadMissing(['vehicle', 'item', 'targetLocation']);
            $areaUpper = strtoupper($area);

            // Hanya aktif untuk WRM dan WPM
            if (!in_array($areaUpper, ['WRM', 'WPM'])) {
                return ['status' => false, 'message' => 'Notifikasi serahkan dokumen QC hanya aktif untuk WRM dan WPM.'];
            }

            // Jika retur atau QC tidak diperlukan
            if ($transaction->jenis === 'retur' || $transaction->qc_status === 'not_required') {
                return ['status' => false, 'message' => 'Tidak memerlukan sampling QC.'];
            }

            // Tidak berjalan jika ada follow-up dari timbangan
            if (!empty($transaction->follow_up_time)) {
                Log::info("[VehicleTracking] Skip notif dokumen QC: Truk {$transaction->id} dalam status follow up dari timbangan.");
                return ['status' => false, 'message' => 'Truk dalam status follow up dari timbangan.'];
            }

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            // Anti-spam / Duplicate Lock via Database (kebal deploy / cache:clear)
            if (!empty($transaction->notif_qc_doc_at)) {
                Log::info("[VehicleTracking] Notif dokumen QC untuk truk {$noPol} sudah pernah tercatat pada {$transaction->notif_qc_doc_at}. Skip duplicate WA.");
                return ['status' => false, 'message' => 'Notifikasi serahkan dokumen QC sudah pernah dikirim.'];
            }

            $driver = $this->resolveDriverInfo($transaction->nama_driver, $transaction->no_hp_driver);
            if (empty($driver['phone'])) {
                Log::warning("[VehicleTracking] Nomor HP supir tidak tersedia untuk truk {$noPol}");
                return ['status' => false, 'message' => "Nomor HP supir untuk truk {$noPol} belum terdaftar."];
            }

            $greeting = $this->humanGreeting();
            $footer = $this->dynamicMessageFooter('QC-DOC');

            $rawMessage = "{$greeting} *{$driver['name']}*,\n\n"
                . "Kendaraan *{$noPol}* (tujuan area *{$areaUpper}*) {sudah|telah} selesai proses timbang masuk ya pak.\n\n"
                . "{Silakan|Dipersilakan|Bisa|Mohon} *segera menuju ke Lab QC* untuk {menyerahkan kelengkapan dokumen|mengurus berkas dokumen} guna pengambilan antrian sampling QC.\n\n"
                . "{Tolong kabari yaaa pak kalau sudah di Lab QC|Bisa tolong balas chat ini ya pak kalau sudah serahkan dokumen?|Terima kasih banyak atas kerja samanya|Mohon konfirmasi singkatnya ya pak jika sudah di Lab QC}. 🙏"
                . $footer;
            $message = $this->parseSpintax($rawMessage);

            $cleanPhone = preg_replace('/[^0-9]/', '', $driver['phone']);
            $fonnte = app(FonnteService::class);
            $res = $fonnte->queueMessage($cleanPhone, $message, 120, [], "Serahkan Dokumen QC Truk {$noPol}");

            if (!empty($res['status'])) {
                $transaction->update(['notif_qc_doc_at' => Carbon::now()]);
                Log::info("[VehicleTracking] Notif serahkan dokumen QC berhasil dijadwalkan ke supir {$noPol} ({$cleanPhone})");
            }

            return $res;
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim WA serahkan dokumen QC: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Notifikasi Supir WFG / SMU untuk Mengurus Dokumen ke Admin WFG.
     * Dikirim saat truk check-in di timbangan dan diarahkan ke WFG atau SMU.
     */
    public function notifyDriverWfgDocument(VehicleTransaction $transaction, string $area = 'WFG'): array
    {
        try {
            $transaction->loadMissing(['vehicle', 'item', 'targetLocation']);
            $areaUpper = strtoupper($area);

            // Hanya aktif untuk WFG dan SMU
            if (!in_array($areaUpper, ['WFG', 'SMU'])) {
                return ['status' => false, 'message' => 'Notifikasi dokumen WFG hanya aktif untuk WFG dan SMU.'];
            }

            // Tidak berjalan jika ada follow-up dari timbangan
            if (!empty($transaction->follow_up_time)) {
                Log::info("[VehicleTracking] Skip notif dokumen WFG: Truk {$transaction->id} dalam status follow up dari timbangan.");
                return ['status' => false, 'message' => 'Truk dalam status follow up dari timbangan.'];
            }

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            // Anti-spam / Duplicate Lock via Database (kebal deploy / cache:clear)
            if (!empty($transaction->notif_wfg_doc_at)) {
                Log::info("[VehicleTracking] Notif dokumen WFG untuk truk {$noPol} sudah pernah tercatat pada {$transaction->notif_wfg_doc_at}. Skip duplicate WA.");
                return ['status' => false, 'message' => 'Notifikasi serahkan dokumen WFG sudah pernah dikirim.'];
            }

            $driver = $this->resolveDriverInfo($transaction->nama_driver, $transaction->no_hp_driver);
            if (empty($driver['phone'])) {
                Log::warning("[VehicleTracking] Nomor HP supir tidak tersedia untuk truk {$noPol}");
                return ['status' => false, 'message' => "Nomor HP supir untuk truk {$noPol} belum terdaftar."];
            }

            $tujuanLabel = ($areaUpper === 'SMU') ? 'SMU (Area WFG)' : 'WFG';

            $greeting = $this->humanGreeting();
            $footer = $this->dynamicMessageFooter('WFG-DOC');

            $rawMessage = "{$greeting} *{$driver['name']}*,\n\n"
                . "Kendaraan *{$noPol}* (tujuan *{$tujuanLabel}*) {sudah|telah} selesai proses timbang masuk ya pak.\n\n"
                . "{Silakan|Dipersilakan|Bisa|Mohon} *segera menuju ke loket Admin WFG* untuk {mengurus dan menyerahkan kelengkapan dokumen antrian|menyerahkan berkas dokumen antrian}.\n\n"
                . "{Tolong kabari yaa pak kalau sudah di loket|Bisa tolong balas sebentar ya pak kalau sudah serahkan dokumen?|Terima kasih banyak atas kerja samanya|Mohon konfirmasinya ya pak jika sudah di loket Admin WFG}. 🙏"
                . $footer;
            $message = $this->parseSpintax($rawMessage);

            $cleanPhone = preg_replace('/[^0-9]/', '', $driver['phone']);
            $fonnte = app(FonnteService::class);
            $res = $fonnte->queueMessage($cleanPhone, $message, 120, [], "Serahkan Dokumen WFG Truk {$noPol}");

            if (!empty($res['status'])) {
                $transaction->update(['notif_wfg_doc_at' => Carbon::now()]);
                Log::info("[VehicleTracking] Notif urus dokumen WFG berhasil dijadwalkan ke supir {$noPol} ({$cleanPhone})");
            }

            return $res;
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim WA urus dokumen WFG: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Notifikasi Supir Muatan RETUR untuk Langsung Menuju Area WRM / WPM.
     * Dikirim saat check-in di timbangan jika jenis muatan adalah retur (tidak ada sampling QC).
     */
    public function notifyDriverReturDirectToDock(VehicleTransaction $transaction, string $area = 'WRM'): array
    {
        try {
            $transaction->loadMissing(['vehicle', 'item', 'targetLocation']);
            $areaUpper = strtoupper($area);

            if (!in_array($areaUpper, ['WRM', 'WPM'])) {
                return ['status' => false, 'message' => 'Hanya aktif untuk WRM dan WPM.'];
            }

            if ($transaction->jenis !== 'retur') {
                return ['status' => false, 'message' => 'Bukan muatan retur.'];
            }

            // Tidak berjalan jika ada follow-up dari timbangan
            if (!empty($transaction->follow_up_time)) {
                Log::info("[VehicleTracking] Skip notif retur ke dock: Truk {$transaction->id} dalam status follow up dari timbangan.");
                return ['status' => false, 'message' => 'Truk dalam status follow up dari timbangan.'];
            }

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            // Anti-spam / Duplicate Lock via Database
            if (!empty($transaction->notif_qc_dock_at)) {
                Log::info("[VehicleTracking] Notif retur langsung ke dock untuk truk {$noPol} sudah pernah tercatat pada {$transaction->notif_qc_dock_at}. Skip duplicate WA.");
                return ['status' => false, 'message' => 'Notifikasi retur langsung ke dock sudah pernah dikirim.'];
            }

            $driver = $this->resolveDriverInfo($transaction->nama_driver, $transaction->no_hp_driver);
            if (empty($driver['phone'])) {
                Log::warning("[VehicleTracking] Nomor HP supir tidak tersedia untuk truk {$noPol}");
                return ['status' => false, 'message' => "Nomor HP supir untuk truk {$noPol} belum terdaftar."];
            }

            $greeting = $this->humanGreeting();
            $footer = $this->dynamicMessageFooter('RETUR');

            $rawMessage = "{$greeting} *{$driver['name']}*,\n\n"
                . "Kendaraan *{$noPol}* muatan *Retur* {telah|sudah} selesai proses timbang masuk.\n\n"
                . "{Silakan|Dipersilakan|Bisa|Mohon} *langsung {menuju|merapat ke} area {$areaUpper}* untuk proses bongkar ya pak (muatan retur tidak memerlukan sampling QC).\n\n"
                . "{Terima kasih banyak|Terima kasih atas kerja samanya|Matur nuwun|Tetap utamakan keselamatan ya pak}. 🙏"
                . $footer;
            $message = $this->parseSpintax($rawMessage);

            $cleanPhone = preg_replace('/[^0-9]/', '', $driver['phone']);
            $fonnte = app(FonnteService::class);
            $res = $fonnte->queueMessage($cleanPhone, $message, 120, [], "Retur Langsung ke Dock Truk {$noPol}");

            if (!empty($res['status'])) {
                $transaction->update(['notif_qc_dock_at' => Carbon::now()]);
                Log::info("[VehicleTracking] Notif retur langsung ke dock {$areaUpper} berhasil dijadwalkan ke supir {$noPol} ({$cleanPhone})");
            }

            return $res;
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim WA retur langsung ke dock: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Poin 4: Notifikasi untuk Barang Pengecualian ('Packaging', 'Import').
     * Setelah supir menyerahkan dokumen & admin QC menerbitkan nomor antrian sampling,
     * supir langsung diarahkan ke area terkait (WRM / WPM) tanpa perlu menunggu hasil sampling QC.
     */
    public function notifyDriverDirectToDockIfException(VehicleTransaction $transaction): array
    {
        try {
            $transaction->loadMissing(['vehicle', 'item', 'targetLocation']);
            $area = $this->getQcTargetArea($transaction);

            // Hanya aktif untuk WRM dan WPM
            if (!in_array($area, ['WRM', 'WPM'])) {
                return ['status' => false, 'message' => 'Hanya aktif untuk WRM dan WPM.'];
            }

            // Cek apakah item adalah pengecualian ('Packaging' atau 'Import')
            if (!$this->isQcExceptionItem($transaction)) {
                return ['status' => false, 'message' => 'Bukan item pengecualian Packaging/Import.'];
            }

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';
            $antrian = $transaction->no_antrian ?? '-';

            // Pengecekan apakah sudah mulai bongkar atau selesai
            if (!empty($transaction->start_loading_time) || in_array($transaction->unloading_status, ['process', 'completed'])) {
                return ['status' => false, 'message' => 'Truk sudah melakukan proses bongkar/muat.'];
            }

            // Tidak berjalan jika ada follow-up dari timbangan
            if (!empty($transaction->follow_up_time)) {
                Log::info("[VehicleTracking] Skip notif langsung ke dock: Truk {$noPol} dalam status follow up dari timbangan.");
                return ['status' => false, 'message' => 'Truk dalam status follow up dari timbangan.'];
            }

            // Anti-spam / Duplicate Lock via Database (kebal deploy / cache:clear)
            if (!empty($transaction->notif_qc_dock_at)) {
                Log::info("[VehicleTracking] Notif langsung ke dock untuk truk {$noPol} sudah pernah tercatat pada {$transaction->notif_qc_dock_at}. Skip duplicate WA.");
                return ['status' => false, 'message' => 'Notifikasi langsung ke dock sudah pernah dikirim.'];
            }

            $driver = $this->resolveDriverInfo($transaction->nama_driver, $transaction->no_hp_driver);
            if (empty($driver['phone'])) {
                Log::warning("[VehicleTracking] Nomor HP supir tidak tersedia untuk truk {$noPol}");
                return ['status' => false, 'message' => "Nomor HP supir untuk truk {$noPol} belum terdaftar."];
            }

            $itemName = $transaction->item ? $transaction->item->name : 'Packaging / Import';

            $greeting = $this->humanGreeting();
            $footer = $this->dynamicMessageFooter('QC-DIRECT');

            $rawMessage = "{$greeting} *{$driver['name']}*,\n\n"
                . "Dokumen kendaraan *{$noPol}* telah diterima di QC (No. Antrian QC: *#{$antrian}*).\n\n"
                . "Untuk muatan *{$itemName}*, {silakan|dipersilakan|Anda bisa|bisa langsung} *langsung {menuju|merapat ke} area {$area}* untuk persiapan proses bongkar/muat tanpa perlu menunggu hasil sampling QC ya pak.\n\n"
                . "{Terima kasih banyak|Matur nuwun|Terima kasih atas kerja samanya|Tetap semangat dan utamakan keselamatan}. 🙏"
                . $footer;
            $message = $this->parseSpintax($rawMessage);

            $cleanPhone = preg_replace('/[^0-9]/', '', $driver['phone']);
            $fonnte = app(FonnteService::class);
            $res = $fonnte->queueMessage($cleanPhone, $message, 120, [], "Langsung ke Dock {$area} Truk {$noPol}");

            if (!empty($res['status'])) {
                $transaction->update(['notif_qc_dock_at' => Carbon::now()]);
                Log::info("[VehicleTracking] Notif langsung ke dock {$area} berhasil dijadwalkan ke supir {$noPol} ({$cleanPhone})");
            }

            return $res;
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim WA langsung ke dock: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Poin 2 & Poin 3: Notifikasi QC Sampling SELESAI (RELEASED).
     * Dikirim ketika QC menyatakan status 'released'.
     * Pengecualian: Tidak berjalan jika truk sudah melakukan bongkar, sudah checkout timbangan,
     * atau muatan merupakan pengecualian ('Packaging'/'Import') yang sudah langsung ke dock.
     */
    public function notifyDriverQcReleased(VehicleTransaction $transaction): array
    {
        try {
            $transaction->loadMissing(['vehicle', 'item', 'targetLocation']);
            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';
            $area = $this->getQcTargetArea($transaction);

            // Hanya aktif untuk WRM dan WPM
            if (!in_array($area, ['WRM', 'WPM'])) {
                return ['status' => false, 'message' => 'Notifikasi release QC hanya aktif untuk WRM dan WPM.'];
            }

            // Poin 4 & 3: Barang pengecualian ('Packaging', 'Import') tidak perlu notif release karena sudah diarahkan langsung ke dock
            if ($this->isQcExceptionItem($transaction)) {
                $itemName = $transaction->item ? $transaction->item->name : 'Packaging/Import';
                Log::info("[VehicleTracking] Skip notif QC released: Truk {$noPol} bermuatan {$itemName} (Pengecualian tidak perlu tunggu QC).");
                return ['status' => false, 'message' => 'Muatan Packaging/Import tidak memerlukan notif QC release.'];
            }

            // Poin 3: Tidak berjalan jika truk sudah melakukan bongkar atau bahkan sudah checkout timbangan
            $alreadyUnloadedOrOut = (
                !empty($transaction->start_loading_time) ||
                !empty($transaction->finish_loading_time) ||
                !empty($transaction->timbangan_out_time) ||
                !empty($transaction->check_out_time) ||
                in_array($transaction->unloading_status, ['process', 'completed']) ||
                in_array($transaction->status, ['completed', 'checkout', 'timbangan_out'])
            );

            if ($alreadyUnloadedOrOut) {
                Log::info("[VehicleTracking] Skip notif QC released: Truk {$noPol} sudah melakukan proses bongkar atau checkout timbangan.");
                return ['status' => false, 'message' => 'Truk sudah melakukan proses bongkar atau checkout timbangan.'];
            }

            // Tidak berjalan jika ada follow-up dari timbangan
            if (!empty($transaction->follow_up_time)) {
                Log::info("[VehicleTracking] Skip notif QC released: Truk {$noPol} dalam status follow up dari timbangan.");
                return ['status' => false, 'message' => 'Truk dalam status follow up dari timbangan.'];
            }

            // Anti-spam / Duplicate Lock via Database (kebal deploy / cache:clear)
            if (!empty($transaction->notif_qc_release_at)) {
                Log::info("[VehicleTracking] Notif QC released untuk truk {$noPol} sudah pernah tercatat pada {$transaction->notif_qc_release_at}. Skip duplicate WA.");
                return ['status' => false, 'message' => 'Notifikasi QC released sudah pernah dikirim.'];
            }

            $driver = $this->resolveDriverInfo($transaction->nama_driver, $transaction->no_hp_driver);
            if (empty($driver['phone'])) {
                Log::warning("[VehicleTracking] Nomor HP supir tidak tersedia untuk truk {$noPol}");
                return ['status' => false, 'message' => "Nomor HP supir untuk truk {$noPol} belum terdaftar."];
            }

            $greeting = $this->humanGreeting();
            $footer = $this->dynamicMessageFooter('QC-RELEASE');

            $rawMessage = "{$greeting} *{$driver['name']}*,\n\n"
                . "{Kabar baik|Informasi hasil QC|Pemberitahuan}, hasil sampling QC untuk kendaraan *{$noPol}* {sudah selesai dan dinyatakan lolos (released)|telah lulus (released) dan siap dibongkar/muat|sudah dirilis (released)}.\n\n"
                . "{Silakan|Dipersilakan|Bisa|Mohon} *segera merapat dan {menuju|masuk} ke area {$area}* untuk persiapan proses {bongkar/muat|pembongkaran} ya pak.\n\n"
                . "⚠️ *{Perhatian|Catatan penting|Info penting}:* {Mohon segera memposisikan truk ke area {$area} yaaa|Harap segera meluncur ke area {$area}|Pastikan kendaraan langsung bergerak ke area {$area}}. {Kalau terlambat merapat, antrian bongkar/muat berisiko dialihkan ke kendaraan lain|Jika telat hadir, slotnya bisa digantikan antrian berikutnya|Apabila lambat merapat, slot dock dapat dialihkan ke antrian lain}.\n\n"
                . "{Bisa tolong balas sebentar ya pak kalau sudah mulai jalan ke {$area}?|Tolong dikabari yaaa pak kalau sudah meluncur ke area {$area}|Bisa konfirmasi balas pesan ini ya pak kalau mobil sudah meluncur?|Mohon konfirmasinya ya pak jika sudah bergerak} 🙏"
                . $footer;
            $message = $this->parseSpintax($rawMessage);

            $cleanPhone = preg_replace('/[^0-9]/', '', $driver['phone']);
            $fonnte = app(FonnteService::class);
            $res = $fonnte->queueMessage($cleanPhone, $message, 120, [], "QC Released Truk {$noPol}");

            if (!empty($res['status'])) {
                $transaction->update(['notif_qc_release_at' => Carbon::now()]);
                Log::info("[VehicleTracking] Notif QC released berhasil dijadwalkan ke supir {$noPol} ({$cleanPhone})");
            }

            return $res;
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim WA QC released: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send manual WhatsApp notification directly to driver's personal phone.
     * Dilengkapi anti-spam / cooldown lock 20 menit per transaksi truk.
     */
    public function sendDriverManualNotification(VehicleTransaction $transaction, string $area = 'WRM'): array
    {
        try {
            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            // 1. Anti-spam / Cooldown Lock (20 menit per transaksi truk)
            $cooldownMinutes = 20;
            $cooldownKey = "wa_cooldown_manual_{$transaction->id}";
            if (Cache::has($cooldownKey)) {
                $cooldownData = Cache::get($cooldownKey);
                $sentBy = is_array($cooldownData) ? ($cooldownData['user_name'] ?? 'User lain') : 'Pengawas';
                $sentTime = is_array($cooldownData) ? ($cooldownData['time'] ?? 'baru saja') : 'baru saja';
                $sentTimestamp = is_array($cooldownData) ? ($cooldownData['timestamp'] ?? time()) : time();
                $remainingSeconds = max(0, ($cooldownMinutes * 60) - (time() - $sentTimestamp));
                $remainingMinutes = ceil($remainingSeconds / 60);

                Log::warning("[VehicleTracking] Anti-Spam: {$noPol} baru saja di-push WA oleh {$sentBy} pada {$sentTime}. Cooldown sisa {$remainingMinutes} menit.");

                return [
                    'status' => false,
                    'is_cooldown' => true,
                    'sent_by' => $sentBy,
                    'sent_time' => $sentTime,
                    'remaining_minutes' => $remainingMinutes,
                    'message' => "Panggilan WhatsApp untuk Truk {$noPol} baru saja dikirim oleh {$sentBy} pada pukul {$sentTime}. Harap tunggu {$remainingMinutes} menit lagi sebelum mengirim ulang."
                ];
            }

            $driver = $this->resolveDriverInfo($transaction->nama_driver, $transaction->no_hp_driver);
            $actionLabel = $transaction->jenis === 'bongkaran' ? 'Bongkar' : ($transaction->jenis === 'slipsheet' ? 'Muat' : ucfirst($transaction->jenis ?? 'Bongkar/Muat'));

            $areaUpper = strtoupper($area);
            $isWfgOrSmu = in_array($areaUpper, ['WFG', 'SMU']);
            $hasQueue = !empty($transaction->no_antrian);

            // Cek apakah panggilan manual ini adalah panggilan ulang (ke-2 kali atau lebih)
            $sendCountKey = "wa_manual_send_count_{$transaction->id}";
            $sendCount = (int) Cache::get($sendCountKey, 0);
            $isRepeat = ($sendCount >= 1);

            if ($isRepeat) {
                // Pengiriman ke-2 kali atau lebih: format santai seperti admin gudang menyapa supir langsung
                $hour = (int)Carbon::now()->format('H');
                if ($hour >= 5 && $hour < 11) {
                    $timeWord = 'pagi';
                } elseif ($hour >= 11 && $hour < 15) {
                    $timeWord = 'siang';
                } elseif ($hour >= 15 && $hour < 18) {
                    $timeWord = 'sore';
                } else {
                    $timeWord = 'malam';
                }

                $driverName = $driver['name'] ?: 'Supir';
                $casualGreeting = "{Selamat {$timeWord}|{$timeWord}|Halo {$timeWord}|Halo} {pak|bapak} *{$driverName}*";

                if ($isWfgOrSmu && !$hasQueue) {
                    $actionType = 'submit_document';
                    $footer = $this->dynamicMessageFooter('DOKUMEN');
                    $adminTarget = ($areaUpper === 'SMU') ? '{admin SMU / WFG|admin WFG}' : 'admin WFG';

                    $rawMessage = "{$casualGreeting},\n"
                        . "{kendaraan|truk|mobil} *{$noPol}* {surat jalan / dokumennya tolong segera dibawa ke {$adminTarget} ya pak biar bisa diproses antriannya|dokumen muatannya mohon segera diserahkan ke loket {$adminTarget} ya pakk supaya antriannya terbit|bisa tolong ke loket {$adminTarget} untuk serahkan berkas surat jalan ya pak}, dan {tolong konfirmasinya yaa|mohon konfirmasinya ya pak|ditunggu kabarnya ya pak}. 🙏"
                        . $footer;
                } else {
                    $actionType = 'dock_call';
                    $footer = $this->dynamicMessageFooter('PANGGILAN');
                    $antrianNote = $hasQueue ? " (Antrian #{$transaction->no_antrian})" : "";

                    $rawMessage = "{$casualGreeting},\n"
                        . "{kendaraan|truk|mobil} *{$noPol}*{$antrianNote} {mohon segera|bisa tolong segera|tolong segera|bisa langsung merapat} ke dock *{$areaUpper}* ya {pakk|pak|yaa pak}, dan {tolong konfirmasinya yaa|mohon konfirmasinya ya pak|bisa minta tolong konfirmasinya ya pak}. 🙏"
                        . $footer;
                }
            } else {
                // Pengiriman pertama kali: format standar lengkap
                $greeting = $this->humanGreeting();

                if ($isWfgOrSmu && !$hasQueue) {
                    $actionType = 'submit_document';
                    $footer = $this->dynamicMessageFooter('DOKUMEN');
                    $adminTarget = ($areaUpper === 'SMU') ? '{Admin SMU / WFG|Admin WFG|loket Admin WFG}' : '{Admin WFG|loket Admin WFG|kantor Admin WFG}';

                    $rawMessage = "{$greeting} *{$driver['name']}*,\n\n"
                        . "{Pemberitahuan|Sekadar mengingatkan|Informasi penting|Pengingat antrian|Info kendaraan} untuk truk *{$noPol}*:\n"
                        . "• {Data kendaraan Anda saat ini sudah tercatat masuk di area parkir|Status truk Anda sudah standby di kantong parkir|Kendaraan Anda terpantau sudah berada di lokasi}, {namun saat ini *belum memiliki nomor antrian dock*|tetapi *belum mengambil nomor antrian*|tetapi nomor antrian dock Anda belum terbit}.\n\n"
                        . "👉 {Mohon|Silakan|Dipersilakan|Bisa tolong|Harap} *{segera|langsung|bisa segera} {menyerahkan|membawa dan menyerahkan|mengantarkan} {dokumen perjalanan|surat jalan (SPB)|dokumen muatan / SPB|surat jalan dan berkas SPB} ke {$adminTarget}* ya pak, agar {nomor antrian dock bisa segera kami proses dan terbitkan|dapat segera diproses untuk penerbitan nomor antrian|bisa segera diinput dan masuk dalam antrian panggilan dock|petugas admin dapat segera menerbitkan antrian Anda}.\n\n"
                        . "⚠️ *{Perhatian|Catatan Penting|Info Penting}:*\n"
                        . "• {Sebelum dokumen diserahkan dan diverifikasi admin, nomor antrian dock belum dapat diproses|Nomor antrian dock baru akan terbit setelah berkas dokumen diterima dan dicek oleh admin|Pastikan dokumen sudah lengkap agar antrian segera diterbitkan|Antrian dock hanya akan berjalan setelah supir menyerahkan surat jalan ke admin}.\n"
                        . "• {Harap tidak meninggalkan kantong parkir tanpa menyerahkan dokumen ya pak|Mohon kerjasamanya agar antrian berjalan tertib dan tidak terlewat|Bila ada kendala mengenai dokumen perjalanan, harap segera koordinasikan dengan petugas admin}.\n\n"
                        . "{Bisa tolong balas chat ini sebentar ya pak kalau sudah meluncur ke loket admin?|Tolong konfirmasi ya pak kalau dokumennya sudah diserahkan ke admin|Bisa dikabari yaa pak jika sudah bersiap ke loket admin?|Mohon balas pesan ini singkat saja jika sudah bersiap ya pak|Bisa balas OKE sebentar kalau sudah menuju loket admin?} 🙏"
                        . $footer;
                } else {
                    $actionType = 'dock_call';
                    $footer = $this->dynamicMessageFooter('PANGGILAN');
                    $antrianText = $hasQueue ? " (Antrian *#{$transaction->no_antrian}*)" : "";

                    $rawMessage = "{$greeting} *{$driver['name']}*,\n\n"
                        . "Kendaraan *{$noPol}*{$antrianText} {dipersilakan|silakan|bisa|mohon} *segera {merapat dan masuk|menuju dan memposisikan mobil} ke Loading Dock {$area}* untuk proses {$actionLabel} ya pak.\n\n"
                        . "⚠️ *{Pemberitahuan Antrian|Catatan Penting|Info Antrian}:*\n"
                        . "• {Harap segera merapat ke area dock ya pak|Mohon segera memposisikan mobil ke dock|Pastikan kendaraan langsung standby di dock}.\n"
                        . "• *{Jika datang terlambat atau berhalangan|Apabila terlambat hadir tanpa konfirmasi|Kalau lambat atau tidak ada kabar}, giliran slot dock {akan dialihkan ke antrian kendaraan lain|terpaksa digantikan truk berikutnya|akan digeser ke antrian lain}.*\n\n"
                        . "{Bisa tolong balas sebentar ya pak apakah mobilnya sudah siap masuk?|Tolong dikabari ya pak kalau sudah mulai jalan ke dock|Bisa konfirmasi balas chat ini yaaa pak kalau mobil sudah meluncur?|Mohon konfirmasi singkatnya ya pak jika sudah siap} 🙏"
                        . $footer;
                }
            }

            $message = $this->parseSpintax($rawMessage);

            if (empty($driver['phone'])) {
                return [
                    'status' => false,
                    'message' => "Nomor HP supir untuk truk {$noPol} belum terdaftar di sistem."
                ];
            }

            $cleanPhone = preg_replace('/[^0-9]/', '', $driver['phone']);

            // Lempar ke Queue dengan Global Sequential Pacing (jeda 1 - 5 menit antar pesan di seluruh sistem)
            app(FonnteService::class)->queueMessage($cleanPhone, $message, 120, [], "Panggilan Manual Truk {$noPol}");

            $currentUser = Auth::user();
            $userName = $currentUser ? ($currentUser->nama_lengkap ?: $currentUser->username) : 'Pengawas';
            Cache::put($cooldownKey, [
                'user_name' => $userName,
                'time' => Carbon::now()->format('H:i'),
                'timestamp' => time()
            ], now()->addMinutes($cooldownMinutes));

            // Simpan riwayat hitungan pengiriman manual
            Cache::put($sendCountKey, $sendCount + 1, now()->addDays(7));

            $successLabel = ($actionType === 'submit_document') ? 'pengingat serah dokumen' : 'panggilan masuk dock';
            return [
                'status' => true,
                'action_type' => $actionType,
                'message' => "Notifikasi WhatsApp {$successLabel} untuk Truk {$noPol} berhasil dijadwalkan."
            ];
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim WA manual ({$area}): " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * AJAX Endpoint to trigger manual push notification to driver personal phone.
     */
    public function notifyDriverManual(Request $request, $id)
    {
        try {
            $transaction = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])->findOrFail($id);

            $area = strtoupper($request->input('area', ''));
            if (empty($area)) {
                if (str_contains($transaction->status, 'wrm') || ($transaction->targetLocation && $transaction->targetLocation->s_loc === 'B006')) {
                    $area = 'WRM';
                } elseif (str_contains($transaction->status, 'wpm') || ($transaction->targetLocation && $transaction->targetLocation->s_loc === 'C001')) {
                    $area = 'WPM';
                } elseif (str_contains($transaction->status, 'wfg') || ($transaction->targetLocation && $transaction->targetLocation->s_loc === 'A001')) {
                    $area = 'WFG';
                } elseif (str_contains($transaction->status, 'smu')) {
                    $area = 'SMU';
                } else {
                    $area = 'DOCK';
                }
            }

            $result = $this->sendDriverManualNotification($transaction, $area);

            if (!empty($result['status'])) {
                $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';
                return response()->json([
                    'success' => true,
                    'message' => $result['message'] ?? "Notifikasi WhatsApp untuk Truk {$noPol} berhasil dikirim ke nomor pribadi supir.",
                    'data' => $result
                ]);
            }

            $statusCode = !empty($result['is_cooldown']) ? 429 : 400;

            return response()->json([
                'success' => false,
                'is_cooldown' => !empty($result['is_cooldown']),
                'message' => $result['message'] ?? 'Gagal mengirim pesan WhatsApp.'
            ], $statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get management recipients: Dept Head WH, Supervisor WH, and Foreman according to bagian.
     */
    public function getWarehouseManagementRecipients(string $area): array
    {
        $areaUpper = strtoupper($area);

        // 1. Supervisor Warehouse
        $supervisors = User::where('departemen', 'warehouse')
            ->where('jabatan', 'supervisor')
            ->where('is_active', true)
            ->get();

        // 2. Foreman Warehouse disesuaikan dengan bagian
        $foremanQuery = User::where('departemen', 'warehouse')
            ->where('jabatan', 'foreman')
            ->where('is_active', true);

        if (in_array($areaUpper, ['WFG', 'SMU'])) {
            $foremanQuery->where(function ($q) {
                $q->where('bagian', 'warehouse_finish_goods');
            });
        } elseif (in_array($areaUpper, ['WRM', 'WPM'])) {
            $foremanQuery->where(function ($q) {
                $q->where('bagian', 'warehouse_raw_material');
            });
        }

        $foremen = $foremanQuery->get();

        return [
            'supervisors' => $supervisors,
            'foremen' => $foremen,
            'all' => $supervisors->concat($foremen)->unique('id'),
        ];
    }

    /**
     * 3. Notifikasi ke Supervisor dan Foreman untuk proses bongkar/muat melebihi 2 jam.
     */
    public function sendOverdueLoadingAlert(VehicleTransaction $tx, string $area, int $diffMinutes, Carbon $startTime): array
    {
        try {
            $noPol = $tx->vehicle ? $tx->vehicle->no_pol : 'N/A';
            $driver = $this->resolveDriverInfo($tx->nama_driver, $tx->no_hp_driver);
            $vendor = $tx->vendor ?: '-';
            $action = $tx->jenis === 'slipsheet' ? 'Muat' : ($tx->jenis === 'bongkaran' ? 'Bongkar' : 'Bongkar/Muat');

            $hours = floor($diffMinutes / 60);
            $mins = $diffMinutes % 60;
            $durationStr = "{$hours} Jam {$mins} Menit";

            $recipients = $this->getWarehouseManagementRecipients($area);
            $foremenNames = $recipients['foremen']->pluck('nama_lengkap')->filter()->implode(', ') ?: ($recipients['foremen']->pluck('username')->implode(', ') ?: 'Foreman WH');
            $spvNames = $recipients['supervisors']->pluck('nama_lengkap')->filter()->implode(', ') ?: ($recipients['supervisors']->pluck('username')->implode(', ') ?: 'SPV WH');

            $fonnte = app(FonnteService::class);
            $sentCount = 0;

            $delayAccumulative = 0;
            // Kirim ke nomor pribadi masing-masing PIC via Queue Job dengan delay bertingkat akumulatif
            foreach ($recipients['all'] as $u) {
                if (!empty($u->no_hp)) {
                    $recipientName = $u->nama_lengkap ?: ($u->name ?: $u->username);
                    $greeting = $this->humanGreeting();
                    $footer = $this->dynamicMessageFooter('OVERDUE-2H');

                    $rawMessage = "{$greeting} *{$recipientName}*,\n\n"
                        . "Izin menginfokan, aktivitas {$action} untuk truk *{$noPol}* di *Dock {$area}* saat ini durasinya sudah mencapai *{$durationStr}* (melebihi batas SLA 2 jam):\n\n"
                        . "• No. Polisi  : *{$noPol}*\n"
                        . "• Vendor      : {$vendor}\n"
                        . "• Supir       : {$driver['display']}\n"
                        . "• Mulai Masuk : " . $startTime->format('d/m/Y H:i') . "\n\n"
                        . "{Mohon bantuannya untuk rekan tim di {$area} dapat mengecek kendala di lapangan ya pak|Mohon bantuan atensinya untuk dicek apakah ada kendala muatan atau antrian di area dock yaa}.\n\n"
                        . "{Jika ada perkembangan kendala atau butuh bantuan koordinasi, tolong saling berkabar ya pak|Kalau ada kendala di lapangan mohon saling info yaaa}. {Terima kasih banyak atas perhatiannya|Terima kasih atas kerja samanya}. 🙏"
                        . $footer;

                    $message = $this->parseSpintax($rawMessage);
                    $cleanPhone = preg_replace('/[^0-9]/', '', $u->no_hp);

                    // Lempar ke Laravel Queue dengan Global Sequential Pacing (jeda 1 - 5 menit antar pesan)
                    $fonnte->queueMessage($cleanPhone, $message, 120, [], "Alert Overdue 2H - {$recipientName}");
                    $sentCount++;
                }
            }

            Log::warning("[VehicleTracking] Alert Overdue Loading (>2h) dikirim ke {$sentCount} PIC management untuk {$noPol} ({$durationStr})");
            return ['status' => true, 'sent_count' => $sentCount];
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim alert overdue loading: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 4. Daily Report: Overdue Truck Duration (> 7 Jam) untuk Dept Head Warehouse.
     * Dijalankan setiap jam 07:00 pagi untuk merekap kendaraan hari sebelumnya (atau yang masih Open).
     * Disertai attachment infografis gambar (JPEG) hasil generate otomatis.
     */
    public function sendDailyOverdueDurationReport(?string $date = null): array
    {
        $now = Carbon::now();
        $targetDate = $date ? Carbon::parse($date) : Carbon::yesterday();
        $targetDateStr = $targetDate->toDateString();
        $formattedDate = $targetDate->translatedFormat('d F Y');

        Log::info("[VehicleTracking] Memulai Daily Report Truck Duration (>7h) untuk periode: {$targetDateStr}");

        // Ambil transaksi yang beroperasi pada targetDate ATAU yang saat ini masih Open
        $txs = VehicleTransaction::with(['vehicle', 'targetLocation', 'currentLocation'])
            ->where(function ($q) use ($targetDateStr) {
                $q->whereDate('check_in_time', $targetDateStr)
                    ->orWhereDate('timbangan_out_time', $targetDateStr)
                    ->orWhereDate('check_out_time', $targetDateStr)
                    ->orWhere(function ($sub) {
                        $sub->whereNull('check_out_time')
                            ->whereNull('timbangan_out_time')
                            ->whereNotIn('status', ['completed', 'timbangan_out']);
                    });
            })
            ->get();

        $overdueList = [];
        $closedCount = 0;
        $openCount = 0;

        foreach ($txs as $tx) {
            if (!$tx->check_in_time) {
                continue;
            }

            $checkIn = Carbon::parse($tx->check_in_time);
            $isClosed = in_array($tx->status, ['completed', 'timbangan_out'])
                || !empty($tx->timbangan_out_time)
                || !empty($tx->check_out_time);

            if ($isClosed) {
                $endTime = $tx->timbangan_out_time
                    ? Carbon::parse($tx->timbangan_out_time)
                    : ($tx->check_out_time ? Carbon::parse($tx->check_out_time) : Carbon::parse($tx->updated_at));

                $durationMinutes = $checkIn->diffInMinutes($endTime);
                $statusLabel = 'Closed';
            } else {
                $durationMinutes = $checkIn->diffInMinutes($now);
                $statusLabel = 'Open';
            }

            if ($durationMinutes >= 420) { // >= 7.0 jam (420 menit)
                $hours = floor($durationMinutes / 60);
                $mins = (int) $durationMinutes % 60;

                // Tentukan area
                $area = 'WFG';
                if (str_contains($tx->status, 'wrm') || ($tx->targetLocation && $tx->targetLocation->s_loc === 'B006')) {
                    $area = 'WRM';
                } elseif (str_contains($tx->status, 'wpm') || ($tx->targetLocation && $tx->targetLocation->s_loc === 'C001')) {
                    $area = 'WPM';
                } elseif (str_contains($tx->status, 'smu')) {
                    $area = 'SMU';
                }

                $nopol = $tx->vehicle ? $tx->vehicle->no_pol : 'N/A';

                $overdueList[] = [
                    'nopol' => $nopol,
                    'status' => $statusLabel,
                    'duration_str' => "{$hours}j {$mins}m",
                    'duration_mins' => $durationMinutes,
                    'check_in' => $checkIn->format('d/m H:i'),
                    'area' => $area,
                ];

                if ($statusLabel === 'Closed') {
                    $closedCount++;
                } else {
                    $openCount++;
                }
            }
        }

        // Ambil Dept Head Warehouse yang aktif
        $deptHeads = User::where('departemen', 'warehouse')
            ->where('jabatan', 'dept_head')
            ->where('is_active', true)
            ->get();

        if ($deptHeads->isEmpty()) {
            Log::warning("[VehicleTracking] Daily Report Duration: Tidak ditemukan Dept Head Warehouse yang aktif.");
            return [
                'status' => false,
                'message' => 'Dept Head Warehouse tidak ditemukan',
                'overdue_count' => count($overdueList)
            ];
        }

        // Generate Infographic Image Report (JPEG)
        $imagePath = $this->generateDailyOverdueReportImage($overdueList, $formattedDate, $closedCount, $openCount, $targetDateStr);
        $imageUrl = $imagePath ? asset('storage/reports/overdue/' . basename($imagePath)) : null;

        $fonnte = app(FonnteService::class);
        $sentCount = 0;
        $greetingHour = (Carbon::now()->format('H') < 12) ? '{Selamat pagi|Pagi}' : '{Selamat siang|Siang}';

        foreach ($deptHeads as $dh) {
            if (!empty($dh->no_hp)) {
                $dhName = $dh->nama_lengkap ?: ($dh->name ?: $dh->username);
                $footer = $this->dynamicMessageFooter('REPORT-DH');

                $rawMessage = "{$greetingHour} Bapak/Ibu *{$dhName}*,\n\n"
                    . "Izin menyampaikan laporan ringkasan harian kendaraan dengan durasi di area pabrik melebihi 7 Jam untuk periode *{$formattedDate}*:\n\n";

                if (empty($overdueList)) {
                    $rawMessage .= "✅ *Kondisi Operasional Normal (SLA Aman)*\n"
                        . "Alhamdulillah tidak tercatat kendaraan yang melebihi batas durasi 7 Jam pada periode ini. Semua alur berjalan sesuai standar operasional.\n\n"
                        . "Infografis status terlampir pada gambar di atas.\n\n";
                } else {
                    $rawMessage .= "📊 *Ringkasan Evaluasi:*\n"
                        . "• Total Overdue : *" . count($overdueList) . " Truk* (> 7 Jam)\n"
                        . "• Status Closed : *{$closedCount} Truk* (Sudah Keluar)\n"
                        . "• Status Open   : *{$openCount} Truk* (Masih di Pabrik)\n\n"
                        . "📎 *Rincian unit kendaraan terlampir lengkap pada gambar di atas.*\n\n";
                }

                $rawMessage .= "{Demikian laporan ini kami sampaikan, terima kasih banyak atas arahan dan perhatiannya Bapak/Ibu|Laporan disampaikan untuk menjadi periksa, terima kasih banyak atas arahan dan bimbingannya}. 🙏"
                    . $footer;

                $message = $this->parseSpintax($rawMessage);
                $cleanPhone = preg_replace('/[^0-9]/', '', $dh->no_hp);

                $extraParams = [];
                if (!empty($imagePath) && file_exists($imagePath)) {
                    $extraParams['file'] = $imagePath;
                    $extraParams['filename'] = "Laporan_Overdue_7Jam_{$targetDateStr}.jpg";
                }

                // Dispatch via Global Queue Pacing (jeda 1 - 5 menit antar pesan)
                $fonnte->queueMessage($cleanPhone, $message, 120, $extraParams, "Daily Report Dept Head - {$dhName}");
                $sentCount++;
            }
        }

        Log::info("[VehicleTracking] Daily Report Overdue Duration berhasil dikirim ke {$sentCount} Dept Head. Total overdue: " . count($overdueList));

        return [
            'status' => true,
            'sent_count' => $sentCount,
            'overdue_count' => count($overdueList),
            'target_date' => $targetDateStr,
            'image_path' => $imagePath,
            'image_url' => $imageUrl
        ];
    }

    /**
     * Generate visual image report (JPEG) untuk Daily Report Overdue Duration (> 7 Jam).
     */
    public function generateDailyOverdueReportImage(array $overdueList, string $formattedDate, int $closedCount, int $openCount, string $targetDateStr): ?string
    {
        try {
            $width = 920;
            $hasData = !empty($overdueList);
            $rowCount = count($overdueList);
            $rowHeight = 44;
            $tableHeight = $hasData ? (45 + ($rowCount * $rowHeight)) : 140;
            $height = 250 + $tableHeight + 115;

            $im = imagecreatetruecolor($width, $height);

            // Colors
            $cBg = imagecolorallocate($im, 15, 23, 42);           // #0f172a slate-900
            $cCard = imagecolorallocate($im, 30, 41, 59);         // #1e293b slate-800
            $cCardBorder = imagecolorallocate($im, 51, 65, 85);   // #334155 slate-700
            $cWhite = imagecolorallocate($im, 255, 255, 255);
            $cTextMuted = imagecolorallocate($im, 148, 163, 184); // #94a3b8 slate-400
            $cGold = imagecolorallocate($im, 245, 158, 11);       // #f59e0b amber-500
            $cRed = imagecolorallocate($im, 239, 68, 68);         // #ef4444 red-500
            $cRedSoft = imagecolorallocate($im, 254, 202, 202);   // #fecaca
            $cGreen = imagecolorallocate($im, 16, 185, 129);      // #10b981 emerald-500
            $cGreenBg = imagecolorallocate($im, 6, 78, 59);       // #064e3b
            $cGreenText = imagecolorallocate($im, 110, 231, 183); // #6ee7b7
            $cBlue = imagecolorallocate($im, 59, 130, 246);       // #3b82f6 blue-500
            $cRowAlt = imagecolorallocate($im, 24, 34, 53);
            $cHeaderBg = imagecolorallocate($im, 39, 53, 77);

            // Fill background
            imagefilledrectangle($im, 0, 0, $width, $height, $cBg);

            // Determine fonts
            $fontBold = 'C:\\Windows\\Fonts\\arialbd.ttf';
            $fontReg = 'C:\\Windows\\Fonts\\arial.ttf';
            if (!file_exists($fontBold)) {
                $fontBold = public_path('material/assets/fonts/hkgrotesk-bold.ttf');
            }
            if (!file_exists($fontReg)) {
                $fontReg = $fontBold;
            }
            $useTtf = file_exists($fontBold);

            // Helper text renderer
            $drawText = function ($size, $x, $y, $color, $text, $isBold = false) use ($im, $useTtf, $fontBold, $fontReg) {
                if ($useTtf) {
                    $font = $isBold ? $fontBold : $fontReg;
                    imagettftext($im, $size, 0, $x, $y, $color, $font, $text);
                } else {
                    $gdFont = $isBold ? 5 : 4;
                    imagestring($im, $gdFont, $x, $y - 12, $text, $color);
                }
            };

            // 1. Top Header Banner
            imagefilledrectangle($im, 30, 25, $width - 30, 105, $cCard);
            imagerectangle($im, 30, 25, $width - 30, 105, $cCardBorder);
            imagefilledrectangle($im, 30, 25, $width - 30, 29, $hasData ? $cRed : $cGreen);

            $drawText(11, 50, 52, $cGold, "PT BUMI ALAM SEGAR • WAREHOUSE & LOGISTICS", true);
            $drawText(16, 50, 78, $cWhite, "DAILY REPORT: OVERDUE KENDARAAN (> 7 JAM)", true);
            $drawText(10, 50, 97, $cTextMuted, "Periode Evaluasi: " . $formattedDate . "  |  Dibuat: " . date('d/m/Y H:i') . " WIB", false);

            // 2. Three KPI Summary Cards
            $cardW = (int)(($width - 60 - 30) / 3);
            $kpiY = 120;
            $kpiH = 75;

            // Card 1: Total Overdue
            $x1 = 30;
            imagefilledrectangle($im, $x1, $kpiY, $x1 + $cardW, $kpiY + $kpiH, $cCard);
            imagerectangle($im, $x1, $kpiY, $x1 + $cardW, $kpiY + $kpiH, $cCardBorder);
            imagefilledrectangle($im, $x1, $kpiY, $x1 + 4, $kpiY + $kpiH, $hasData ? $cRed : $cGreen);
            $drawText(9, $x1 + 18, $kpiY + 24, $cTextMuted, "TOTAL OVERDUE (>7 JAM)", true);
            $drawText(22, $x1 + 18, $kpiY + 60, $hasData ? $cRed : $cGreen, (string)$rowCount . " Truk", true);

            // Card 2: Closed
            $x2 = $x1 + $cardW + 15;
            imagefilledrectangle($im, $x2, $kpiY, $x2 + $cardW, $kpiY + $kpiH, $cCard);
            imagerectangle($im, $x2, $kpiY, $x2 + $cardW, $kpiY + $kpiH, $cCardBorder);
            imagefilledrectangle($im, $x2, $kpiY, $x2 + 4, $kpiY + $kpiH, $cBlue);
            $drawText(9, $x2 + 18, $kpiY + 24, $cTextMuted, "STATUS CLOSED (KELUAR)", true);
            $drawText(22, $x2 + 18, $kpiY + 60, $cBlue, (string)$closedCount . " Truk", true);

            // Card 3: Open
            $x3 = $x2 + $cardW + 15;
            imagefilledrectangle($im, $x3, $kpiY, $x3 + $cardW, $kpiY + $kpiH, $cCard);
            imagerectangle($im, $x3, $kpiY, $x3 + $cardW, $kpiY + $kpiH, $cCardBorder);
            imagefilledrectangle($im, $x3, $kpiY, $x3 + 4, $kpiY + $kpiH, $cGold);
            $drawText(9, $x3 + 18, $kpiY + 24, $cTextMuted, "STATUS OPEN (DI PABRIK)", true);
            $drawText(22, $x3 + 18, $kpiY + 60, $cGold, (string)$openCount . " Truk", true);

            // 3. Table / Banner Container
            $tableY = 215;
            imagefilledrectangle($im, 30, $tableY, $width - 30, $tableY + $tableHeight, $cCard);
            imagerectangle($im, 30, $tableY, $width - 30, $tableY + $tableHeight, $cCardBorder);

            if (!$hasData) {
                // Normal State Banner
                imagefilledrectangle($im, 30, $tableY, $width - 30, $tableY + $tableHeight, $cGreenBg);
                imagerectangle($im, 30, $tableY, $width - 30, $tableY + $tableHeight, $cGreen);
                $drawText(15, 60, $tableY + 55, $cWhite, "✓ KONDISI OPERASIONAL NORMAL (SLA AMAN)", true);
                $drawText(11, 60, $tableY + 85, $cGreenText, "Alhamdulillah tidak tercatat kendaraan yang melebihi batas durasi 7 Jam pada periode ini.", false);
                $drawText(10, 60, $tableY + 110, $cGreenText, "Semua proses bongkar dan muat kendaraan berjalan sesuai standar operasional pabrik.", false);
                $currY = $tableY + $tableHeight;
            } else {
                // Table Header
                imagefilledrectangle($im, 30, $tableY, $width - 30, $tableY + 40, $cHeaderBg);
                imageline($im, 30, $tableY + 40, $width - 30, $tableY + 40, $cCardBorder);

                $drawText(10, 50, $tableY + 26, $cWhite, "NO", true);
                $drawText(10, 95, $tableY + 26, $cWhite, "NO. POLISI", true);
                $drawText(10, 260, $tableY + 26, $cWhite, "AREA DOCK", true);
                $drawText(10, 400, $tableY + 26, $cWhite, "JAM MASUK", true);
                $drawText(10, 560, $tableY + 26, $cWhite, "STATUS", true);
                $drawText(10, 725, $tableY + 26, $cWhite, "TOTAL DURASI", true);

                // Table Rows
                $currY = $tableY + 40;
                foreach ($overdueList as $idx => $row) {
                    if ($idx % 2 === 1) {
                        imagefilledrectangle($im, 31, $currY, $width - 31, $currY + $rowHeight - 1, $cRowAlt);
                    }
                    imageline($im, 30, $currY + $rowHeight, $width - 30, $currY + $rowHeight, $cCardBorder);

                    $textY = $currY + 27;

                    // Col 1: No
                    $drawText(10, 52, $textY, $cTextMuted, (string)($idx + 1), true);

                    // Col 2: Nopol
                    $drawText(12, 95, $textY, $cWhite, $row['nopol'], true);

                    // Col 3: Area Badge
                    $areaColor = match($row['area']) {
                        'WRM' => $cGreen,
                        'SMU' => $cGold,
                        'WPM' => $cBlue,
                        default => imagecolorallocate($im, 168, 85, 247), // WFG Purple
                    };
                    imagefilledrectangle($im, 258, $currY + 10, 335, $currY + 32, $cBg);
                    imagerectangle($im, 258, $currY + 10, 335, $currY + 32, $areaColor);
                    $drawText(10, 275, $textY - 2, $areaColor, $row['area'], true);

                    // Col 4: Jam Masuk
                    $drawText(10, 400, $textY, $cTextMuted, $row['check_in'], false);

                    // Col 5: Status Badge
                    $statusBg = ($row['status'] === 'Closed') ? imagecolorallocate($im, 22, 78, 99) : imagecolorallocate($im, 120, 53, 15);
                    $statusText = ($row['status'] === 'Closed') ? imagecolorallocate($im, 103, 232, 249) : imagecolorallocate($im, 253, 186, 116);
                    imagefilledrectangle($im, 558, $currY + 10, 645, $currY + 32, $statusBg);
                    imagerectangle($im, 558, $currY + 10, 645, $currY + 32, $statusText);
                    $drawText(10, 575, $textY - 2, $statusText, strtoupper($row['status']), true);

                    // Col 6: Durasi (Red bold)
                    $drawText(12, 725, $textY, $cRedSoft, "⏱ " . $row['duration_str'], true);

                    $currY += $rowHeight;
                }
            }

            // 4. Legend & Footer Notes
            $footerY = $currY + 28;
            $drawText(9, 40, $footerY, $cTextMuted, "Catatan: Standar SLA kendaraan di pabrik maksimal 7 Jam. Data di atas dilaporkan otomatis kepada Dept Head Warehouse.", false);
            $drawText(9, 40, $footerY + 22, $cGold, "PT BAS Warehouse Management System • Auto-Notification Engine", true);

            // Ensure directory exists
            $dir = storage_path('app/public/reports/overdue');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $fileName = "daily_overdue_{$targetDateStr}_" . time() . ".jpg";
            $fullPath = $dir . DIRECTORY_SEPARATOR . $fileName;

            imagejpeg($im, $fullPath, 82);
            imagedestroy($im);

            Log::info("[VehicleTracking] Report image berhasil digenerate: {$fullPath}");
            return $fullPath;
        } catch (\Throwable $e) {
            Log::error("[VehicleTracking] Gagal generate report image: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Kirim notifikasi WhatsApp Follow Up dari Timbangan ke Foreman Area terkait.
     * WFG / SMU -> Foreman Warehouse Finish Goods
     * WRM / WPM -> Foreman Warehouse Raw Material
     * Tindakan: Segera close sistem.
     */
    public function sendFollowUpForemanAlert(VehicleTransaction $transaction, string $targetArea, ?string $notes = null): array
    {
        try {
            $cacheKey = "fu_timbangan_wa_sent_{$transaction->id}";
            if (Cache::has($cacheKey)) {
                Log::info("[VehicleTracking] Skip duplicate WA follow-up timbangan for Truk ID {$transaction->id}: sudah pernah dikirim.");
                return ['status' => false, 'message' => 'WhatsApp follow-up sudah pernah dikirim untuk transaksi ini.'];
            }

            $areaUpper = strtoupper(trim($targetArea));

            // Jika 'ALL', tentukan area berdasarkan target lokasi atau status transaksi
            if ($areaUpper === 'ALL' || empty($areaUpper)) {
                $sloc = $transaction->targetLocation ? $transaction->targetLocation->s_loc : '';
                if (in_array($sloc, ['A001', 'SMU', 'A002']) || str_contains($transaction->status, 'wfg') || str_contains($transaction->status, 'smu')) {
                    $areaUpper = 'WFG';
                } elseif (in_array($sloc, ['B006', 'C001']) || str_contains($transaction->status, 'wrm') || str_contains($transaction->status, 'wpm')) {
                    $areaUpper = 'WRM';
                } else {
                    $areaUpper = 'WFG';
                }
            }

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';
            $driver = $this->resolveDriverInfo($transaction->nama_driver, $transaction->no_hp_driver);
            $vendor = $transaction->vendor ?: '-';
            $operator = Auth::user();
            $operatorName = $operator ? ($operator->nama_lengkap ?: ($operator->name ?: $operator->username)) : 'Operator Timbangan';
            $now = Carbon::now();

            // Tentukan foreman tujuan berdasarkan area
            $isFinishGoods = in_array($areaUpper, ['WFG', 'SMU']);
            $targetBagianName = $isFinishGoods ? 'Warehouse Finish Goods' : 'Warehouse Raw Material';

            $foremanQuery = User::where('departemen', 'warehouse')
                ->where('jabatan', 'foreman')
                ->where('is_active', true);

            if ($isFinishGoods) {
                $foremanQuery->where(function ($q) {
                    $q->where('bagian', 'warehouse_finish_goods');
                });
            } else {
                $foremanQuery->where(function ($q) {
                    $q->where('bagian', 'warehouse_raw_material');
                });
            }

            $foremen = $foremanQuery->get();

            if ($foremen->isEmpty()) {
                Log::warning("[VehicleTracking] Tidak ditemukan Foreman aktif untuk bagian {$targetBagianName}");
                return ['status' => false, 'message' => "Tidak ada Foreman aktif untuk {$targetBagianName}."];
            }

            $notesText = !empty($notes) ? "\n• Catatan    : {$notes}" : "";
            $fonnte = app(FonnteService::class);
            $sentCount = 0;
            $delayAccumulative = 0;

            foreach ($foremen as $f) {
                if (!empty($f->no_hp)) {
                    $foremanName = $f->nama_lengkap ?: ($f->name ?: $f->username);
                    $greeting = $this->humanGreeting();
                    $footer = $this->dynamicMessageFooter('FOLLOWUP-TIMBANGAN');

                    $rawMessage = "{$greeting} *{$foremanName}* (Foreman {$areaUpper}),\n\n"
                        . "{Ada titipan follow-up|Mohon bantuannya untuk follow-up|Pemberitahuan follow-up} dari Timbangan ({$operatorName}) untuk mobil *{$noPol}*:\n"
                        . "• No. Polisi : *{$noPol}* ({$vendor})\n"
                        . "• Supir      : {$driver['display']}\n"
                        . "• Waktu      : " . $now->format('d/m/Y H:i')
                        . $notesText . "\n\n"
                        . "Supir sudah bersiap di timbangan keluar ya pak. {Minta tolong bantuannya untuk segera close sistem transaksinya yaaa, supaya proses timbang out supir bisa dilanjutkan|Mohon bantuannya untuk dibantu close sistemnya ya pak agar truk bisa segera melanjutkan timbang out|Bisa tolong dibantu close transaksi di sistem ya pak agar supir tidak menunggu lama di timbangan out}.\n\n"
                        . "{Tolong dikabari kalau sudah di-close ya pak, terima kasih banyak|Bisa tolong balas sebentar yaaa pak kalau sudah selesai di-close? Terima kasih banyak atas bantuannya|Mohon konfirmasinya jika sudah di-close ya pak, terima kasih}. 🙏"
                        . $footer;

                    $message = $this->parseSpintax($rawMessage);
                    $cleanPhone = preg_replace('/[^0-9]/', '', $f->no_hp);

                    // Lempar ke Laravel Queue dengan Global Sequential Pacing (jeda 1 - 5 menit antar pesan)
                    $fonnte->queueMessage($cleanPhone, $message, 120, [], "Follow Up Timbangan - {$foremanName}");
                    $sentCount++;
                }
            }

            if ($sentCount > 0) {
                Cache::put($cacheKey, true, 86400 * 3);
            }

            Log::info("[VehicleTracking] Follow up dari Timbangan untuk {$noPol} berhasil dikirim ke {$sentCount} Foreman ({$targetBagianName})");
            return ['status' => true, 'sent_count' => $sentCount];
        } catch (\Exception $e) {
            Log::error("[VehicleTracking] Gagal kirim WA follow up ke foreman: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check and trigger SLA Overdue Alerts:
     * Loading/unloading exceeding 2 hours (120 mins).
     * (Catatan: Truck duration > 7 jam dilaporkan via daily report setiap 07:00 pagi ke Dept Head dengan infografis gambar).
     */
    public function checkOverdueVehicles(): array
    {
        $now = Carbon::now();

        // Hanya berjalan pada jam operasional shift (07:00 - 23:59). Jam 00:00 - 06:59 dijeda.
        $currentHour = (int) $now->format('H');
        if ($currentHour < 7) {
            return [
                'status' => false,
                'alerts_sent' => 0,
                'message' => 'Di luar jam operasional shift (00:00 - 06:59). Monitoring SLA dijeda.',
                'checked_at' => $now->toDateTimeString()
            ];
        }

        $alertsSent = 0;

        $activeTxs = VehicleTransaction::with(['vehicle', 'item', 'targetLocation', 'currentLocation'])
            ->whereNotIn('status', ['completed', 'timbangan_out'])
            ->whereNull('check_out_time')
            ->whereNull('timbangan_out_time')
            ->get();

        foreach ($activeTxs as $tx) {
            $area = 'WFG';
            if (str_contains($tx->status, 'wrm') || ($tx->targetLocation && $tx->targetLocation->s_loc === 'B006')) {
                $area = 'WRM';
            } elseif (str_contains($tx->status, 'wpm') || ($tx->targetLocation && $tx->targetLocation->s_loc === 'C001')) {
                $area = 'WPM';
            } elseif (str_contains($tx->status, 'smu')) {
                $area = 'SMU';
            } elseif (str_contains($tx->status, 'wfg')) {
                $area = 'WFG';
            }

            $today = $now->format('Y-m-d');

            // Alert Proses Bongkar/Muat > 2 Jam (120 Menit) - Dibatasi maksimal 1x sehari (Database & Cache Persisted)
            if ($tx->unloading_status === 'process' && !empty($tx->start_loading_time)) {
                $startTime = Carbon::parse($tx->start_loading_time);
                $diffMinutes = $startTime->diffInMinutes($now);

                if ($diffMinutes >= 120) {
                    $cacheKey = "alert_overdue_loading_2h_{$tx->id}_{$today}";
                    $alreadySentToday = Cache::has($cacheKey) ||
                        (!empty($tx->last_loading_alert_at) && Carbon::parse($tx->last_loading_alert_at)->isToday());

                    if (!$alreadySentToday) {
                        // Kunci status alert di Cache & DB sebelum kirim pesan untuk mencegah race condition
                        Cache::put($cacheKey, $now->toDateTimeString(), now()->endOfDay());
                        try {
                            $tx->update(['last_loading_alert_at' => $now]);
                        } catch (\Throwable $th) {
                            Log::warning("[VehicleTracking] Kolom last_loading_alert_at belum ada di DB. Harap jalankan migrasi: " . $th->getMessage());
                        }

                        $this->sendOverdueLoadingAlert($tx, $area, $diffMinutes, $startTime);
                        $alertsSent++;
                    }
                }
            }
        }

        return [
            'status' => true,
            'alerts_sent' => $alertsSent,
            'checked_at' => $now->toDateTimeString()
        ];
    }

    /**
     * Tampilan Antrian Kendaraan & Loading Dock untuk Supir & TV Monitor.
     * Dapat diakses secara publik (di pos satpam / TV display / supir via QR code).
     */
    public function queueDisplayIndex(Request $request)
    {
        $area = strtolower($request->get('area', 'wfg'));
        if (!in_array($area, ['wfg', 'wrm'])) {
            $area = 'wfg';
        }

        return view('vehicle.monitoring.queue_display', compact('area'));
    }

    /**
     * API Real-time Data untuk Display Antrian Supir & TV Monitor.
     * Mengembalikan data ketersediaan slot dock, truk aktif proses, dan antrian standby.
     */
    public function queueDisplayData(Request $request)
    {
        // Pastikan mobil menginap/sisa kemarin di-reset dan diurutkan dari nomor 01
        $this->syncAndResetDailyQueues();

        $area = strtolower($request->get('area', 'wfg'));
        $now = Carbon::now();

        if ($area === 'wfg') {
            // ========================================================
            // WFG & SMU (Finished Goods & SMU)
            // ========================================================
            $activeWfgTrucks = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])
                ->whereIn('status', ['wfg', 'smu'])
                ->where('unloading_status', 'process')
                ->whereNull('finish_loading_time')
                ->whereNull('check_out_time')
                ->whereNull('timbangan_out_time')
                ->orderBy('start_loading_time', 'asc')
                ->get();

            $activeSlipsheet = [];
            $activeCurah = [];
            $activeBongkaran = [];

            foreach ($activeWfgTrucks as $tx) {
                $cat = $this->resolveDockCategory($tx, $tx->status);
                $startTime = $tx->start_loading_time ? Carbon::parse($tx->start_loading_time) : null;
                $itemData = [
                    'id' => $tx->id,
                    'no_antrian' => $tx->no_antrian ?? '-',
                    'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                    'nama_driver' => $tx->nama_driver ?? '-',
                    'vendor' => $tx->vendor ?? '-',
                    'item_name' => $tx->item ? $tx->item->name : ($tx->jenis ?? '-'),
                    'jenis' => $tx->jenis ?? '-',
                    'status' => $tx->status,
                    'start_time' => $startTime ? $startTime->format('H:i') : '-',
                    'duration' => $startTime ? $startTime->diffForHumans(null, true) : '-',
                ];

                if ($cat === 'slipsheet') {
                    $activeSlipsheet[] = $itemData;
                } elseif ($cat === 'curah') {
                    $activeCurah[] = $itemData;
                } else {
                    $activeBongkaran[] = $itemData;
                }
            }

            // Fixed slots per dock quota
            $slotsSlipsheet = [];
            for ($i = 0; $i < 2; $i++) {
                $slotsSlipsheet[] = $activeSlipsheet[$i] ?? null;
            }

            $slotsCurah = [];
            for ($i = 0; $i < 4; $i++) {
                $slotsCurah[] = $activeCurah[$i] ?? null;
            }

            $slotsBongkaran = [];
            for ($i = 0; $i < 2; $i++) {
                $slotsBongkaran[] = $activeBongkaran[$i] ?? null;
            }

            // Antrian Menunggu (Standby Parkir / Dipanggil)
            $waitingWfg = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])
                ->whereIn('status', ['wfg', 'smu'])
                ->where(function ($q) {
                    $q->whereNull('unloading_status')
                        ->orWhereIn('unloading_status', ['pending', 'waiting', 'call', 'dipanggil']);
                })
                ->whereNull('start_loading_time')
                ->whereNull('finish_loading_time')
                ->whereNull('check_out_time')
                ->whereNull('timbangan_out_time')
                ->whereNull('follow_up_time')
                ->orderByRaw('CASE WHEN no_antrian IS NULL THEN 1 ELSE 0 END, COALESCE(DATE(queue_taken_time), DATE(created_at)) ASC, CAST(no_antrian AS UNSIGNED) ASC, check_in_time ASC')
                ->get();

            $queueSlipsheet = [];
            $queueCurah = [];
            $queueBongkaran = [];
            $callingList = [];

            foreach ($waitingWfg as $tx) {
                $cat = $this->resolveDockCategory($tx, $tx->status);
                $isOvernight = $tx->queue_taken_time ? $tx->queue_taken_time->lt(Carbon::today()) : ($tx->created_at ? $tx->created_at->lt(Carbon::today()) : false);
                $isCalled = !empty($tx->notif_prepare_at) && Carbon::parse($tx->notif_prepare_at)->diffInMinutes($now) <= 30;

                $queueItem = [
                    'id' => $tx->id,
                    'no_antrian' => $tx->no_antrian ?? '-',
                    'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                    'nama_driver' => $tx->nama_driver ?? '-',
                    'vendor' => $tx->vendor ?? '-',
                    'item_name' => $tx->item ? $tx->item->name : ($tx->jenis ?? '-'),
                    'jenis' => $tx->jenis ?? '-',
                    'category' => $cat,
                    'category_label' => match ($cat) {
                        'slipsheet' => 'Slipsheet',
                        'curah' => 'Curah',
                        default => 'Bongkaran / SMU',
                    },
                    'is_overnight' => $isOvernight,
                    'is_called' => $isCalled,
                    'called_at' => $tx->notif_prepare_at ? Carbon::parse($tx->notif_prepare_at)->format('H:i') : null,
                    'queue_time' => $tx->queue_taken_time ? Carbon::parse($tx->queue_taken_time)->format('H:i') : ($tx->check_in_time ? Carbon::parse($tx->check_in_time)->format('H:i') : '-'),
                ];

                if ($isCalled) {
                    $callingList[] = $queueItem;
                }

                if ($cat === 'slipsheet') {
                    $queueSlipsheet[] = $queueItem;
                } elseif ($cat === 'curah') {
                    $queueCurah[] = $queueItem;
                } else {
                    $queueBongkaran[] = $queueItem;
                }
            }

            return response()->json([
                'status' => true,
                'area' => 'wfg',
                'server_time' => $now->format('H:i:s'),
                'server_date' => $now->translatedFormat('l, d F Y'),
                'docks' => [
                    'slipsheet' => [
                        'label' => 'Dock Slipsheet',
                        'quota' => 2,
                        'occupied' => count($activeSlipsheet),
                        'available' => max(0, 2 - count($activeSlipsheet)),
                        'slots' => $slotsSlipsheet,
                        'queue_count' => count($queueSlipsheet),
                        'queue' => $queueSlipsheet,
                    ],
                    'curah' => [
                        'label' => 'Dock Curah',
                        'quota' => 4,
                        'occupied' => count($activeCurah),
                        'available' => max(0, 4 - count($activeCurah)),
                        'slots' => $slotsCurah,
                        'queue_count' => count($queueCurah),
                        'queue' => $queueCurah,
                    ],
                    'bongkaran' => [
                        'label' => 'Dock Bongkaran & SMU',
                        'quota' => 2,
                        'occupied' => count($activeBongkaran),
                        'available' => max(0, 2 - count($activeBongkaran)),
                        'slots' => $slotsBongkaran,
                        'queue_count' => count($queueBongkaran),
                        'queue' => $queueBongkaran,
                    ],
                ],
                'calling' => $callingList,
                'all_queue' => $waitingWfg->map(function ($tx) use ($now) {
                    $cat = $this->resolveDockCategory($tx, $tx->status);
                    $isOvernight = $tx->queue_taken_time ? $tx->queue_taken_time->lt(Carbon::today()) : ($tx->created_at ? $tx->created_at->lt(Carbon::today()) : false);
                    $isCalled = !empty($tx->notif_prepare_at) && Carbon::parse($tx->notif_prepare_at)->diffInMinutes($now) <= 30;

                    return [
                        'id' => $tx->id,
                        'no_antrian' => $tx->no_antrian ?? '-',
                        'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                        'nama_driver' => $tx->nama_driver ?? '-',
                        'vendor' => $tx->vendor ?? '-',
                        'item_name' => $tx->item ? $tx->item->name : ($tx->jenis ?? '-'),
                        'jenis' => $tx->jenis ?? '-',
                        'category' => $cat,
                        'category_label' => match ($cat) {
                            'slipsheet' => 'Slipsheet',
                            'curah' => 'Curah',
                            default => 'Bongkaran / SMU',
                        },
                        'is_overnight' => $isOvernight,
                        'is_called' => $isCalled,
                        'called_at' => $tx->notif_prepare_at ? Carbon::parse($tx->notif_prepare_at)->format('H:i') : null,
                        'queue_time' => $tx->queue_taken_time ? Carbon::parse($tx->queue_taken_time)->format('H:i') : ($tx->check_in_time ? Carbon::parse($tx->check_in_time)->format('H:i') : '-'),
                    ];
                }),
                'total_waiting' => count($waitingWfg),
                'total_in_dock' => count($activeWfgTrucks),
            ]);
        } else {
            // ========================================================
            // WRM & WPM (Bahan Baku & Kemasan)
            // ========================================================
            // 1. WRM Area
            $activeWrm = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])
                ->where(function ($q) {
                    $q->where('status', 'wrm_bongkar')
                        ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'B006'));
                })
                ->where('unloading_status', 'process')
                ->whereNull('finish_loading_time')
                ->whereNull('check_out_time')
                ->whereNull('timbangan_out_time')
                ->orderBy('start_loading_time', 'asc')
                ->get()
                ->map(function ($tx) {
                    $startTime = $tx->start_loading_time ? Carbon::parse($tx->start_loading_time) : null;
                    return [
                        'id' => $tx->id,
                        'no_antrian' => $tx->no_antrian ?? '-',
                        'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                        'nama_driver' => $tx->nama_driver ?? '-',
                        'vendor' => $tx->vendor ?? '-',
                        'item_name' => $tx->item ? $tx->item->name : ($tx->jenis ?? '-'),
                        'jenis' => $tx->jenis ?? 'Bongkar',
                        'start_time' => $startTime ? $startTime->format('H:i') : '-',
                        'duration' => $startTime ? $startTime->diffForHumans(null, true) : '-',
                    ];
                });

            $waitingWrm = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])
                ->where(function ($q) {
                    $q->where('status', 'wrm_bongkar')
                        ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'B006'));
                })
                ->where(function ($q) {
                    $q->whereNull('unloading_status')
                        ->orWhereIn('unloading_status', ['pending', 'waiting', 'call', 'dipanggil']);
                })
                ->whereNull('start_loading_time')
                ->whereNull('finish_loading_time')
                ->whereNull('check_out_time')
                ->whereNull('timbangan_out_time')
                ->whereNull('follow_up_time')
                ->orderBy('check_in_time', 'asc')
                ->get()
                ->map(function ($tx) use ($now) {
                    $isOvernight = $tx->check_in_time ? Carbon::parse($tx->check_in_time)->lt(Carbon::today()) : false;
                    $isCalled = !empty($tx->notif_prepare_at) && Carbon::parse($tx->notif_prepare_at)->diffInMinutes($now) <= 30;
                    return [
                        'id' => $tx->id,
                        'area' => 'WRM',
                        'no_antrian' => $tx->no_antrian ?? '-',
                        'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                        'nama_driver' => $tx->nama_driver ?? '-',
                        'vendor' => $tx->vendor ?? '-',
                        'item_name' => $tx->item ? $tx->item->name : ($tx->jenis ?? '-'),
                        'jenis' => $tx->jenis ?? 'Bongkar',
                        'is_overnight' => $isOvernight,
                        'is_called' => $isCalled,
                        'called_at' => $tx->notif_prepare_at ? Carbon::parse($tx->notif_prepare_at)->format('H:i') : null,
                        'queue_time' => $tx->check_in_time ? Carbon::parse($tx->check_in_time)->format('H:i') : '-',
                    ];
                });

            // 2. WPM Area
            $activeWpm = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])
                ->where(function ($q) {
                    $q->where('status', 'wpm')
                        ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'C001'));
                })
                ->where('unloading_status', 'process')
                ->whereNull('finish_loading_time')
                ->whereNull('check_out_time')
                ->whereNull('timbangan_out_time')
                ->orderBy('start_loading_time', 'asc')
                ->get()
                ->map(function ($tx) {
                    $startTime = $tx->start_loading_time ? Carbon::parse($tx->start_loading_time) : null;
                    return [
                        'id' => $tx->id,
                        'no_antrian' => $tx->no_antrian ?? '-',
                        'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                        'nama_driver' => $tx->nama_driver ?? '-',
                        'vendor' => $tx->vendor ?? '-',
                        'item_name' => $tx->item ? $tx->item->name : ($tx->jenis ?? '-'),
                        'jenis' => $tx->jenis ?? 'Muat',
                        'start_time' => $startTime ? $startTime->format('H:i') : '-',
                        'duration' => $startTime ? $startTime->diffForHumans(null, true) : '-',
                    ];
                });

            $waitingWpm = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])
                ->where(function ($q) {
                    $q->where('status', 'wpm')
                        ->orWhereHas('targetLocation', fn($tl) => $tl->where('s_loc', 'C001'));
                })
                ->where(function ($q) {
                    $q->whereNull('unloading_status')
                        ->orWhereIn('unloading_status', ['pending', 'waiting', 'call', 'dipanggil']);
                })
                ->whereNull('start_loading_time')
                ->whereNull('finish_loading_time')
                ->whereNull('check_out_time')
                ->whereNull('timbangan_out_time')
                ->whereNull('follow_up_time')
                ->orderBy('check_in_time', 'asc')
                ->get()
                ->map(function ($tx) use ($now) {
                    $isOvernight = $tx->check_in_time ? Carbon::parse($tx->check_in_time)->lt(Carbon::today()) : false;
                    $isCalled = !empty($tx->notif_prepare_at) && Carbon::parse($tx->notif_prepare_at)->diffInMinutes($now) <= 30;
                    return [
                        'id' => $tx->id,
                        'area' => 'WPM',
                        'no_antrian' => $tx->no_antrian ?? '-',
                        'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                        'nama_driver' => $tx->nama_driver ?? '-',
                        'vendor' => $tx->vendor ?? '-',
                        'item_name' => $tx->item ? $tx->item->name : ($tx->jenis ?? '-'),
                        'jenis' => $tx->jenis ?? 'Muat',
                        'is_overnight' => $isOvernight,
                        'is_called' => $isCalled,
                        'called_at' => $tx->notif_prepare_at ? Carbon::parse($tx->notif_prepare_at)->format('H:i') : null,
                        'queue_time' => $tx->check_in_time ? Carbon::parse($tx->check_in_time)->format('H:i') : '-',
                    ];
                });

            // 3. QC Lab Area
            $qcTransactions = VehicleTransaction::with(['vehicle', 'item'])
                ->whereIn('qc_status', ['waiting_dokumen', 'waiting_sampling', 'on_check'])
                ->orderByRaw('CASE WHEN no_antrian IS NULL THEN 1 ELSE 0 END, COALESCE(DATE(queue_taken_time), DATE(created_at)) ASC, CAST(no_antrian AS UNSIGNED) ASC, check_in_time ASC')
                ->get();

            $activeQc = $qcTransactions->filter(function ($tx) {
                return $tx->qc_status === 'on_check' || !empty($tx->start_sampling_time);
            })->values()->map(function ($tx) {
                $startTime = $tx->start_sampling_time ? Carbon::parse($tx->start_sampling_time) : null;
                return [
                    'id' => $tx->id,
                    'no_antrian' => $tx->no_antrian ?? '-',
                    'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                    'nama_driver' => $tx->nama_driver ?? '-',
                    'vendor' => $tx->vendor ?? '-',
                    'item_name' => $tx->item ? $tx->item->name : '-',
                    'qc_status' => 'Sedang Sampling',
                    'start_time' => $startTime ? $startTime->format('H:i') : '-',
                    'duration' => $startTime ? $startTime->diffForHumans(null, true) : '-',
                ];
            });

            $waitingQc = $qcTransactions->filter(function ($tx) {
                return $tx->qc_status !== 'on_check' && empty($tx->start_sampling_time);
            })->values()->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'no_antrian' => $tx->no_antrian ?? '-',
                    'no_pol' => $tx->vehicle ? $tx->vehicle->no_pol : 'N/A',
                    'nama_driver' => $tx->nama_driver ?? '-',
                    'vendor' => $tx->vendor ?? '-',
                    'item_name' => $tx->item ? $tx->item->name : '-',
                    'qc_status' => $tx->qc_status === 'waiting_dokumen' ? 'Tunggu Dokumen' : 'Tunggu Sampling',
                    'queue_time' => $tx->queue_taken_time ? Carbon::parse($tx->queue_taken_time)->format('H:i') : ($tx->check_in_time ? Carbon::parse($tx->check_in_time)->format('H:i') : '-'),
                ];
            });

            $callingWrmWpm = array_values(array_merge(
                $waitingWrm->where('is_called', true)->all(),
                $waitingWpm->where('is_called', true)->all()
            ));

            return response()->json([
                'status' => true,
                'area' => 'wrm',
                'server_time' => $now->format('H:i:s'),
                'server_date' => $now->translatedFormat('l, d F Y'),
                'wrm' => [
                    'active' => $activeWrm,
                    'queue' => $waitingWrm,
                    'active_count' => count($activeWrm),
                    'queue_count' => count($waitingWrm),
                ],
                'wpm' => [
                    'active' => $activeWpm,
                    'queue' => $waitingWpm,
                    'active_count' => count($activeWpm),
                    'queue_count' => count($waitingWpm),
                ],
                'qc' => [
                    'active' => $activeQc,
                    'queue' => $waitingQc,
                    'active_count' => count($activeQc),
                    'queue_count' => count($waitingQc),
                ],
                'calling' => $callingWrmWpm,
                'total_waiting' => count($waitingWrm) + count($waitingWpm),
                'total_in_dock' => count($activeWrm) + count($activeWpm),
            ]);
        }
    }
}
