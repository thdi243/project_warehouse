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
use App\Models\Wrm\MasterSupplierModel;
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
                'start_loading_time' => $tx->start_loading_time ? $tx->start_loading_time->format('Y-m-d H:i:s') : null,
                'finish_loading_time' => $tx->finish_loading_time ? $tx->finish_loading_time->format('Y-m-d H:i:s') : null,
                'check_in_time' => $tx->check_in_time->format('Y-m-d H:i:s'),
                'arrival_time' => $currentTracking ? $currentTracking->arrival_time->format('Y-m-d H:i:s') : $tx->check_in_time->format('Y-m-d H:i:s'),
                'duration_seconds' => $durationSeconds,
                'limit_minutes' => $limitMinutes,
                'is_bottleneck' => $isBottleneck,
            ];
        });

        // Split into queues for the dashboard tables
        $queues = [
            'WPM' => $activeTransactions->filter(function ($tx) {
                return $tx['status'] === 'wpm' || $tx['target_location_code'] === 'C001';
            })->values(),
            'WRM' => $activeTransactions->filter(function ($tx) {
                return $tx['status'] === 'wrm_bongkar' || $tx['target_location_code'] === 'B006';
            })->values(),
            'WFG' => $activeTransactions->where('status', 'wfg')->values(),
            'SMU' => $activeTransactions->where('status', 'smu')->values(),
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
            if ($tx->item) {
                $itemName = strtoupper(trim($tx->item->name));
                if ($itemName === 'GULA TEBU') {
                    $itemKPIs['gula_tebu']['ton'] += floatval($tx->qty_spb);
                    $itemKPIs['gula_tebu']['truck']++;
                } elseif ($itemName === 'GULA KELAPA') {
                    $itemKPIs['gula_kelapa']['ton'] += floatval($tx->qty_spb);
                    $itemKPIs['gula_kelapa']['truck']++;
                } elseif ($itemName === 'GULA KELAPA GRADE B') {
                    $itemKPIs['gula_kelapa_grade_b']['ton'] += floatval($tx->qty_spb);
                    $itemKPIs['gula_kelapa_grade_b']['truck']++;
                } elseif ($itemName === 'GULA PASIR') {
                    $itemKPIs['gula_pasir']['ton'] += floatval($tx->qty_spb);
                    $itemKPIs['gula_pasir']['truck']++;
                } elseif ($itemName === 'IMPORT') {
                    $itemKPIs['import']['ton'] += floatval($tx->qty_spb);
                    $itemKPIs['import']['truck']++;
                }
            }
        }

        // Completed transactions today (for "Out" counters)
        $todayCompletedTransactions = VehicleTransaction::with(['targetLocation', 'vehicle', 'item'])
            ->where('status', 'completed')
            ->whereDate('check_out_time', Carbon::today())
            ->get();

        $slipsheetIn = $txCollection->where('jenis', 'slipsheet')->count();
        $slipsheetOut = $todayCompletedTransactions->where('jenis', 'slipsheet')->count();

        $curahIn = $txCollection->where('jenis', 'curah')->count();
        $curahOut = $todayCompletedTransactions->where('jenis', 'curah')->count();

        $smuIn = $txCollection->filter(function ($tx) {
            return $tx->targetLocation && $tx->targetLocation->s_loc === 'SMU';
        })->count();
        $smuOut = $todayCompletedTransactions->filter(function ($tx) {
            return $tx->targetLocation && $tx->targetLocation->s_loc === 'SMU';
        })->count();

        $wpmIn = $txCollection->filter(function ($tx) {
            return $tx->targetLocation && $tx->targetLocation->s_loc === 'C001';
        })->count();
        $wpmOut = $todayCompletedTransactions->filter(function ($tx) {
            return $tx->targetLocation && $tx->targetLocation->s_loc === 'C001';
        })->count();

        $wrmIn = $txCollection->filter(function ($tx) {
            return $tx->targetLocation && $tx->targetLocation->s_loc === 'B006';
        })->count();
        $wrmOut = $todayCompletedTransactions->filter(function ($tx) {
            return $tx->targetLocation && $tx->targetLocation->s_loc === 'B006';
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

        try {
            DB::beginTransaction();

            $noPol = strtoupper(str_replace(' ', '', $request->no_pol));

            // Validasi truk tidak boleh duplikasi jika masih ada transaksi aktif
            $activeTransaction = VehicleTransaction::whereHas('vehicle', function ($q) use ($noPol) {
                $q->where(DB::raw("REPLACE(UPPER(no_pol), ' ', '')"), $noPol);
            })
            ->where(function ($q) {
                $q->whereNull('check_out_time')
                  ->orWhere('status', '!=', 'completed');
            })
            ->with(['currentLocation', 'targetLocation'])
            ->latest()
            ->first();

            if ($activeTransaction) {
                $currentLocName = $activeTransaction->currentLocation ? $activeTransaction->currentLocation->name : ($activeTransaction->targetLocation ? $activeTransaction->targetLocation->name : 'Warehouse');
                throw new \Exception("Kendaraan dengan No. Polisi {$request->no_pol} masih aktif dalam sistem (No. Transaksi: {$activeTransaction->no_transaction}, Posisi: {$currentLocName}, Status: {$activeTransaction->status}). Harap selesaikan transaksi sebelumnya terlebih dahulu.");
            }

            // Validate target area based on jenis
            $targetLoc = Location::findOrFail($request->target_location_id);
            $jenis = $request->jenis;
            if ($jenis === 'bongkaran') {
                if ($targetLoc->s_loc === 'A001') {
                    throw new \Exception('Untuk jenis bongkaran, tidak boleh memilih tujuan area WFG (A001).');
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
                [
                    'vendor' => $request->vendor
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
                'trnvisitorid' => $request->trnvisitorid,
                'vehicle_id' => $vehicle->id,
                'jenis' => $request->jenis,
                'vendor' => $request->vendor ?? $vehicle->vendor,
                'nama_driver' => $request->nama_driver,
                'no_hp_driver' => $request->no_hp_driver,
                'checkin_pos1' => $request->checkin_pos1,
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

                return [
                    'id' => $tx->id,
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

            // Update transaction to timbangan_out
            $transaction->update([
                'unloading_status' => 'completed',
                'finish_loading_time' => $now,
                'finish_loading_by' => Auth::id(),
                'timbangan_out_time' => $now,
                'timbangan_out_by' => Auth::id(),
                'current_location_id' => $timbanganLoc->id,
                'status' => 'timbangan_out',
                'updated_by' => Auth::id()
            ]);

            // Create new tracking log for Timbangan
            VehicleTracking::create([
                'vehicle_transaction_id' => $transaction->id,
                'location_id' => $timbanganLoc->id,
                'arrival_time' => $now,
                'created_by' => Auth::id(),
            ]);

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'TIMBANGAN',
                'status' => 'timbangan_out',
                'message' => "Proses {$actionName} Truk {$noPol} di WPM telah selesai. Truk kembali ke Timbangan untuk Check-Out.",
                'time' => $now->format('H:i:s')
            ]));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Truk ' . $noPol . ' selesai di WPM. Diarahkan kembali ke Timbangan untuk Check-Out.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyelesaikan proses di WPM: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper to reorder and compact QC queue numbers sequentially (#01, #02, ...).
     */
    private function reorderQcQueue()
    {
        $activeQc = VehicleTransaction::whereIn('qc_status', ['waiting_sampling', 'on_check'])
            ->whereNotNull('no_antrian')
            ->orderByRaw('CASE WHEN queue_taken_time IS NULL THEN 1 ELSE 0 END, queue_taken_time ASC, CAST(no_antrian AS UNSIGNED) ASC, id ASC')
            ->get();

        $i = 1;
        foreach ($activeQc as $tx) {
            $expected = str_pad($i, 2, '0', STR_PAD_LEFT);
            if ($tx->no_antrian !== $expected) {
                $tx->update(['no_antrian' => $expected]);
            }
            $i++;
        }
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
        // Reorder & compact active QC queues to ensure sequential ordering
        $this->reorderQcQueue();

        $queue = VehicleTransaction::with(['vehicle', 'item', 'targetLocation', 'activeTracking'])
            ->whereIn('qc_status', ['waiting_dokumen', 'waiting_sampling', 'on_check'])
            ->orderByRaw('CASE WHEN no_antrian IS NULL THEN 1 ELSE 0 END, CAST(no_antrian AS UNSIGNED) ASC, check_in_time ASC')
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

                return [
                    'id' => $tx->id,
                    'no_antrian' => $tx->no_antrian,
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

            // Reorder existing first to maintain clean sequences
            $this->reorderQcQueue();

            $maxAntrian = VehicleTransaction::whereIn('qc_status', ['waiting_sampling', 'on_check'])
                ->whereNotNull('no_antrian')
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

            // Ensure tight continuous ordering
            $this->reorderQcQueue();

            $transaction->refresh();
            $formattedAntrian = $transaction->no_antrian ?? $formattedAntrian;

            // Update tracking log status note
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

            // Reorder remaining active queues to maintain continuous sequence
            $this->reorderQcQueue();

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
            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            $transaction->update([
                'qc_status' => 'on_check',
                'start_sampling_time' => $transaction->start_sampling_time ?? Carbon::now(),
                'start_sampling_by' => $transaction->start_sampling_by ?? Auth::id(),
                'status' => 'sampling',
                'updated_by' => Auth::id()
            ]);

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
                'status' => 'sampling',
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
            $now = Carbon::now();

            // Clear antrian from transaction and reorder remaining active QC queues
            $transaction->update(['no_antrian' => null]);
            $this->reorderQcQueue();

            $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : 'N/A';

            // Check if vehicle is currently still in sampling / antri_sampling
            $isStillInQC = in_array($transaction->status, ['antri_sampling', 'sampling']);

            if ($request->qc_status === 'released') {
                $this->releaseKantongParkirSlot($noPol, 'QC Lolos (Released)');

                $targetLoc = Location::find($transaction->target_location_id);
                $targetCode = $targetLoc ? $targetLoc->s_loc : 'B006';
                $destinationStatus = ($targetCode === 'C001') ? 'wpm' : 'wrm_bongkar';

                $updateData = [
                    'qc_status' => 'released',
                    'start_sampling_time' => $transaction->start_sampling_time ?? $now,
                    'start_sampling_by' => $transaction->start_sampling_by ?? Auth::id(),
                    'finish_sampling_time' => $transaction->finish_sampling_time ?? $now,
                    'finish_sampling_by' => Auth::id(),
                    'no_antrian' => null,
                    'updated_by' => Auth::id()
                ];

                // Only change vehicle location/status if it was still in QC stages
                if ($isStillInQC) {
                    $updateData['status'] = $destinationStatus;
                }

                $transaction->update($updateData);

                // Conclude QC tracking if vehicle was in QC
                $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                    ->where('location_id', $transaction->current_location_id)
                    ->whereNull('departure_time')
                    ->latest()
                    ->first();

                if ($activeTrack && $isStillInQC) {
                    $duration = abs($now->diffInSeconds($activeTrack->arrival_time, false));
                    $activeTrack->update([
                        'departure_time' => $now,
                        'duration_seconds' => $duration,
                        'status_notes' => "QC Hasil: RELEASED. Catatan: " . ($request->notes ?? '-')
                    ]);
                }

                event(new VehicleStatusUpdated([
                    'transaction_id' => $transaction->id,
                    'no_pol' => $noPol,
                    'current_location' => $isStillInQC ? $targetCode : ($transaction->currentLocation ? $transaction->currentLocation->s_loc : 'QC'),
                    'status' => $transaction->status,
                    'message' => "Truk {$noPol} lolos QC (Released).",
                    'time' => $now->format('H:i:s')
                ]));

                $msg = 'Status QC Truk ' . $noPol . ' diperbarui ke RELEASED.';
            } else {
                $updateData = [
                    'qc_status' => 'rejected',
                    'start_sampling_time' => $transaction->start_sampling_time ?? $now,
                    'start_sampling_by' => $transaction->start_sampling_by ?? Auth::id(),
                    'finish_sampling_time' => $transaction->finish_sampling_time ?? $now,
                    'finish_sampling_by' => Auth::id(),
                    'no_antrian' => null,
                    'updated_by' => Auth::id()
                ];

                if ($isStillInQC) {
                    $timbanganLoc = Location::where('s_loc', 'TMB')->first();
                    if (!$timbanganLoc) {
                        throw new \Exception('Lokasi TIMBANGAN tidak ditemukan di database.');
                    }

                    $updateData['status'] = 'timbangan_out';
                    $updateData['timbangan_out_time'] = $now;
                    $updateData['timbangan_out_by'] = Auth::id();
                    $updateData['current_location_id'] = $timbanganLoc->id;

                    $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                        ->where('location_id', $transaction->current_location_id)
                        ->whereNull('departure_time')
                        ->latest()
                        ->first();

                    if ($activeTrack) {
                        $duration = abs($now->diffInSeconds($activeTrack->arrival_time, false));
                        $activeTrack->update([
                            'departure_time' => $now,
                            'duration_seconds' => $duration,
                            'status_notes' => "QC Hasil: REJECTED. Catatan: " . ($request->notes ?? '-')
                        ]);
                    }

                    // Create tracking log for Timbangan
                    VehicleTracking::create([
                        'vehicle_transaction_id' => $transaction->id,
                        'location_id' => $timbanganLoc->id,
                        'arrival_time' => $now,
                        'created_by' => Auth::id(),
                    ]);

                    $transaction->update($updateData);

                    event(new VehicleStatusUpdated([
                        'transaction_id' => $transaction->id,
                        'no_pol' => $noPol,
                        'current_location' => 'TIMBANGAN',
                        'status' => 'timbangan_out',
                        'message' => "Truk {$noPol} ditolak QC (Rejected) -> Diarahkan kembali ke Timbangan untuk Check-Out.",
                        'time' => $now->format('H:i:s')
                    ]));

                    $msg = 'Status QC Truk ' . $noPol . ' diperbarui ke REJECTED. Truk diarahkan kembali ke Timbangan untuk Check-Out.';
                } else {
                    $transaction->update($updateData);

                    event(new VehicleStatusUpdated([
                        'transaction_id' => $transaction->id,
                        'no_pol' => $noPol,
                        'current_location' => $transaction->currentLocation ? $transaction->currentLocation->s_loc : 'QC',
                        'status' => $transaction->status,
                        'message' => "Hasil QC Truk {$noPol} dinyatakan REJECTED.",
                        'time' => $now->format('H:i:s')
                    ]));

                    $msg = 'Status QC Truk ' . $noPol . ' diperbarui ke REJECTED.';
                }
            }

            DB::commit();
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

                return [
                    'id' => $tx->id,
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

            // Update transaction to timbangan_out
            $transaction->update([
                'unloading_status' => 'completed',
                'finish_loading_time' => $now,
                'finish_loading_by' => Auth::id(),
                'timbangan_out_time' => $now,
                'timbangan_out_by' => Auth::id(),
                'current_location_id' => $timbanganLoc->id,
                'status' => 'timbangan_out',
                'updated_by' => Auth::id()
            ]);

            // Create new tracking log for Timbangan
            VehicleTracking::create([
                'vehicle_transaction_id' => $transaction->id,
                'location_id' => $timbanganLoc->id,
                'arrival_time' => $now,
                'created_by' => Auth::id(),
            ]);

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'TIMBANGAN',
                'status' => 'timbangan_out',
                'message' => "Proses {$actionName} Truk {$noPol} di WRM telah selesai. Truk kembali ke Timbangan untuk Check-Out.",
                'time' => $now->format('H:i:s')
            ]));

            DB::commit();
            return response()->json(['success' => true, 'message' => "Status {$actionName} truk {$noPol} diperbarui ke Selesai. Diarahkan kembali ke Timbangan untuk Check-Out."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyelesaikan proses: ' . $e->getMessage()], 500);
        }
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
            ->orderByRaw('CASE WHEN no_antrian IS NULL THEN 1 ELSE 0 END, CAST(no_antrian AS UNSIGNED) ASC, check_in_time ASC')
            ->get()
            ->map(function ($tx) {
                $tracking = $tx->activeTracking;

                $arrivalTime = $tracking ? $tracking->arrival_time : $tx->check_in_time;

                return [
                    'id' => $tx->id,
                    'no_antrian' => $tx->no_antrian,
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

            // Update transaction to timbangan_out
            $transaction->update([
                'unloading_status' => 'completed',
                'finish_loading_time' => $now,
                'finish_loading_by' => Auth::id(),
                'timbangan_out_time' => $now,
                'current_location_id' => $timbanganLoc->id,
                'status' => 'timbangan_out',
                'no_antrian' => null, // Reset no antrian agar slot nomor antrian kembali bersih
                'updated_by' => Auth::id()
            ]);

            // Shift nomor antrian yang tersisa agar tetap urut (hanya pada jenis muatan yang sama di WFG)
            if ($completedAntrian > 0) {
                $shiftQuery = VehicleTransaction::where('status', 'wfg')
                    ->whereNotNull('no_antrian');

                if (!empty($transactionJenis)) {
                    $shiftQuery->where('jenis', $transactionJenis);
                }

                $remainingQueue = $shiftQuery->get();

                foreach ($remainingQueue as $remainingTx) {
                    $currentNum = (int)$remainingTx->no_antrian;
                    if ($currentNum > $completedAntrian) {
                        $newNum = str_pad($currentNum - 1, 2, '0', STR_PAD_LEFT);
                        $remainingTx->update(['no_antrian' => $newNum]);
                    }
                }
            }

            // Create new tracking log for Timbangan (Menunggu Timbang Keluar)
            VehicleTracking::create([
                'vehicle_transaction_id' => $transaction->id,
                'location_id' => $timbanganLoc->id,
                'arrival_time' => $now,
                'status_notes' => 'Selesai dari WFG. Menunggu Timbang Keluar di Timbangan.',
                'created_by' => Auth::id(),
            ]);

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'TIMBANGAN',
                'status' => 'timbangan_out',
                'message' => "Truk {$noPol} selesai di WFG dan diarahkan kembali ke Timbangan untuk Timbang Keluar.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Proses Bongkar/Muat di WFG selesai. Truk diarahkan ke Timbangan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyelesaikan bongkar/muat: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper to reorder and compact SMU queue numbers sequentially (#01, #02, ...).
     */
    private function reorderSmuQueue()
    {
        $activeSmu = VehicleTransaction::where('status', 'smu')
            ->whereNotNull('no_antrian')
            ->orderByRaw('CASE WHEN queue_taken_time IS NULL THEN 1 ELSE 0 END, queue_taken_time ASC, CAST(no_antrian AS UNSIGNED) ASC, id ASC')
            ->get();

        $i = 1;
        foreach ($activeSmu as $tx) {
            $expected = str_pad($i, 2, '0', STR_PAD_LEFT);
            if ($tx->no_antrian !== $expected) {
                $tx->update(['no_antrian' => $expected]);
            }
            $i++;
        }
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
        // Reorder & compact active SMU queues to ensure sequential ordering
        $this->reorderSmuQueue();

        $queue = VehicleTransaction::with(['vehicle', 'item', 'activeTracking'])
            ->where('status', 'smu')
            ->orderByRaw('CASE WHEN no_antrian IS NULL THEN 1 ELSE 0 END, CAST(no_antrian AS UNSIGNED) ASC, check_in_time ASC')
            ->get()
            ->map(function ($tx) {
                $tracking = $tx->activeTracking;

                $arrivalTime = $tracking ? $tracking->arrival_time : $tx->check_in_time;

                return [
                    'id' => $tx->id,
                    'no_antrian' => $tx->no_antrian,
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

            // Auto-assign queue number if not yet set
            if (empty($transaction->no_antrian)) {
                $this->reorderSmuQueue();
                $maxAntrian = VehicleTransaction::where('status', 'smu')
                    ->whereNotNull('no_antrian')
                    ->where('id', '!=', $transaction->id)
                    ->get()
                    ->map(function ($tx) {
                        return (int)$tx->no_antrian;
                    })
                    ->max();

                $nextAntrian = $maxAntrian ? $maxAntrian + 1 : 1;
                $formattedAntrian = str_pad($nextAntrian, 2, '0', STR_PAD_LEFT);
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

            // Update transaction to timbangan_out
            $transaction->update([
                'unloading_status' => 'completed',
                'finish_loading_time' => $now,
                'finish_loading_by' => Auth::id(),
                'timbangan_out_time' => $now,
                'timbangan_out_by' => Auth::id(),
                'current_location_id' => $timbanganLoc->id,
                'status' => 'timbangan_out',
                'no_antrian' => null, // Clear its own queue
                'updated_by' => Auth::id()
            ]);

            // Reorder remaining active queues in SMU
            $this->reorderSmuQueue();

            // Create new tracking log for Timbangan
            VehicleTracking::create([
                'vehicle_transaction_id' => $transaction->id,
                'location_id' => $timbanganLoc->id,
                'arrival_time' => $now,
                'created_by' => Auth::id(),
            ]);

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'TIMBANGAN',
                'status' => 'timbangan_out',
                'message' => "Proses Truk {$noPol} di SMU selesai. Truk kembali ke Timbangan untuk Check-Out.",
                'time' => $now->format('H:i:s')
            ]));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Truk ' . $noPol . ' selesai di SMU. Diarahkan kembali ke Timbangan untuk Check-Out.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memproses SMU: ' . $e->getMessage()], 500);
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

            // Broadcast Event for Realtime Alert
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

            return response()->json([
                'success' => true,
                'message' => "Notifikasi follow up berhasil dikirim ke area {$targetArea} untuk Truk {$noPol}."
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
     * Update queue number for a transaction (generic).
     */
    public function updateQueueNumber(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);

            // Auto-assign queue number: find max in current status (dan per jenis jika di WFG)
            $status = $transaction->status;
            $jenis = strtolower(trim($transaction->jenis ?? ''));

            $query = VehicleTransaction::where('status', $status)
                ->whereNotNull('no_antrian');

            // Pisahkan antrian berdasarkan jenis jika di WFG (slipsheet vs curah)
            if ($status === 'wfg' && !empty($jenis)) {
                $query->where('jenis', $jenis);
            }

            $maxAntrian = $query->get()
                ->map(function ($tx) {
                    return (int)$tx->no_antrian;
                })
                ->max();

            $nextAntrian = $maxAntrian ? $maxAntrian + 1 : 1;
            $formattedAntrian = str_pad($nextAntrian, 2, '0', STR_PAD_LEFT);

            // Update queue number
            $transaction->update([
                'no_antrian' => $formattedAntrian,
                'queue_taken_time' => $transaction->queue_taken_time ?? Carbon::now(),
                'queue_taken_by' => $transaction->queue_taken_by ?? Auth::id(),
                'updated_by' => Auth::id()
            ]);

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

            // Jika di WFG, shift antrian yang tersisa agar tetap urut berdasarkan jenisnya
            if ($status === 'wfg' && $oldAntrian) {
                $oldAntrianInt = (int)$oldAntrian;
                $shiftQuery = VehicleTransaction::where('status', 'wfg')
                    ->whereNotNull('no_antrian');

                if (!empty($jenis)) {
                    $shiftQuery->where('jenis', $jenis);
                }

                $remaining = $shiftQuery->get();
                foreach ($remaining as $tx) {
                    $curr = (int)$tx->no_antrian;
                    if ($curr > $oldAntrianInt) {
                        $tx->update(['no_antrian' => str_pad($curr - 1, 2, '0', STR_PAD_LEFT)]);
                    }
                }
            }

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

        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);

            $noPol = strtoupper(str_replace(' ', '', $request->no_pol));

            // Validate target area based on jenis
            $targetLoc = Location::findOrFail($request->target_location_id);
            $jenis = $request->jenis;
            if ($jenis === 'bongkaran') {
                if ($targetLoc->s_loc === 'A001') {
                    throw new \Exception('Untuk jenis bongkaran, tidak boleh memilih tujuan area WFG (A001).');
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
                'nama_driver' => $request->nama_driver,
                'no_hp_driver' => $request->no_hp_driver,
                'item_id' => $request->item_id,
                'no_spb' => $request->no_spb,
                'qty_spb' => $request->qty_spb,
                'target_location_id' => $request->target_location_id,
                'updated_by' => Auth::id(),
            ];

            if ($request->jenis === 'retur') {
                $updateData['qc_status'] = 'not_required';
            }

            if ($request->filled('checkin_pos1')) {
                $updateData['checkin_pos1'] = $request->checkin_pos1;
            }
            if ($request->filled('trnvisitorid')) {
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
        $query = VehicleTransaction::with([
            'vehicle',
            'item',
            'targetLocation',
            'tracking.location',
            'creator',
            'queueTakenBy',
            'startSamplingBy',
            'finishSamplingBy',
            'startLoadingBy',
            'finishLoadingBy',
            'timbanganOutBy',
            'checkOutBy',
        ])->where('status', 'completed');

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

        $perPage = $request->input('per_page', 10);
        if ($perPage === 'all') {
            $totalCount = (clone $query)->count();
            $perPage = $totalCount > 0 ? $totalCount : 10;
        } else {
            $perPage = max(1, (int) $perPage);
        }

        $paginated = $query->orderBy('check_in_time', 'desc')->paginate($perPage);

        $paginated->getCollection()->transform(function ($tx) use ($formatDuration) {
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
                            ];
                        }
                    }
                }
            }

            // 5. Tracking Steps (Perpindahan Lokasi & Catatan)
            $trackingSteps = $tx->tracking->map(function ($track) use ($formatDuration) {
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
                    'check_in_by' => $tx->creator ? $tx->creator->name : '-',
                    'queue_taken' => $tx->queue_taken_time ? $tx->queue_taken_time->format('d-m-Y H:i:s') : '-',
                    'queue_taken_by' => $tx->queueTakenBy ? $tx->queueTakenBy->name : '-',
                    'start_sampling' => $tx->start_sampling_time ? $tx->start_sampling_time->format('d-m-Y H:i:s') : '-',
                    'start_sampling_by' => $tx->startSamplingBy ? $tx->startSamplingBy->name : '-',
                    'finish_sampling' => $tx->finish_sampling_time ? $tx->finish_sampling_time->format('d-m-Y H:i:s') : '-',
                    'finish_sampling_by' => $tx->finishSamplingBy ? $tx->finishSamplingBy->name : '-',
                    'start_loading' => $tx->start_loading_time ? $tx->start_loading_time->format('d-m-Y H:i:s') : '-',
                    'start_loading_by' => $tx->startLoadingBy ? $tx->startLoadingBy->name : '-',
                    'finish_loading' => $tx->finish_loading_time ? $tx->finish_loading_time->format('d-m-Y H:i:s') : '-',
                    'finish_loading_by' => $tx->finishLoadingBy ? $tx->finishLoadingBy->name : '-',
                    'timbangan_out' => $tx->timbangan_out_time ? $tx->timbangan_out_time->format('d-m-Y H:i:s') : '-',
                    'timbangan_out_by' => $tx->timbanganOutBy ? $tx->timbanganOutBy->name : '-',
                    'check_out' => $checkOut ? $checkOut->format('d-m-Y H:i:s') : '-',
                    'check_out_by' => $tx->checkOutBy ? $tx->checkOutBy->name : '-',
                ],
            ];
        });

        return response()->json($paginated);
    }
}
