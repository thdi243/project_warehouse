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
use Illuminate\Support\Facades\DB;

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
                $durationSeconds = Carbon::now()->diffInSeconds($currentTracking->arrival_time);
            }

            return [
                'id' => $tx->id,
                'no_transaction' => $tx->no_transaction,
                'no_pol' => $tx->vehicle->no_pol,
                'vendor' => $tx->vendor,
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
                'check_in_time' => $tx->check_in_time->format('Y-m-d H:i:s'),
                'arrival_time' => $currentTracking ? $currentTracking->arrival_time->format('Y-m-d H:i:s') : $tx->check_in_time->format('Y-m-d H:i:s'),
                'duration_seconds' => $durationSeconds,
                'limit_minutes' => $limitMinutes,
                'is_bottleneck' => $isBottleneck,
            ];
        });

        // Split into queues for the dashboard tables (WPM includes antri_sampling and wpm_qc)
        $queues = [
            'WPM' => $activeTransactions->whereIn('status', ['antri_sampling', 'wpm_qc'])->values(),
            'WRM' => $activeTransactions->where('status', 'wrm_bongkar')->values(),
            'WFG' => $activeTransactions->where('status', 'wfg_muat')->values(),
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
        $todayCompletedTransactions = VehicleTransaction::with(['targetLocation'])
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

        return response()->json([
            'queues' => $queues,
            'counts' => $counts,
            'activities' => $recentActivities
        ]);
    }

    /**
     * Timbangan (Scale) View.
     */
    public function timbanganIndex(Request $request)
    {
        $items = VehicleItem::orderBy('name')->get();
        $targetLocations = Location::where('s_loc', '!=', 'TMB')->get();
        $vendors = VehicleVendor::orderBy('name')->get();

        $filterDate = $request->input('date', Carbon::today()->format('Y-m-d'));

        $todayTransactions = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])
            ->whereDate('check_in_time', $filterDate)
            ->latest()
            ->get();

        return view('vehicle.monitoring.timbangan', compact('items', 'targetLocations', 'vendors', 'todayTransactions', 'filterDate'));
    }

    /**
     * Get Timbangan daily transaction data via AJAX.
     */
    public function timbanganData(Request $request)
    {
        $filterDate = $request->input('date', Carbon::today()->format('Y-m-d'));

        $transactions = VehicleTransaction::with(['vehicle', 'item', 'targetLocation'])
            ->whereDate('check_in_time', $filterDate)
            ->where('status', '!=', 'completed')
            ->latest()
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'no_transaction' => $tx->no_transaction,
                    'no_pol' => $tx->vehicle->no_pol,
                    'jenis' => ucfirst($tx->jenis),
                    'item_id' => $tx->item_id,
                    'vendor' => $tx->vendor,
                    'no_spb' => $tx->no_spb ?? '-',
                    'qty_spb' => $tx->qty_spb ?? '-',
                    'target_loc' => $tx->target_location_id,
                    'target_sloc' => $tx->targetLocation->s_loc,
                    'status' => $tx->status,
                    'check_in_time' => $tx->check_in_time ? $tx->check_in_time->format('H:i') : '-',
                    'check_out_time' => $tx->check_out_time ? $tx->check_out_time->format('H:i') : '-',
                ];
            });

        return response()->json($transactions);
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
     * Store Vehicle Check-In at scales.
     */
    public function timbanganCheckIn(Request $request)
    {
        $request->validate([
            'no_pol' => 'required|string|max:20',
            'vendor' => 'nullable|string|max:100',
            'jenis' => 'required|string|in:bongkaran,slipsheet,curah',
            'item_id' => 'required|exists:vehicle_items,id',
            'no_spb' => 'nullable|string|max:50',
            'qty_spb' => 'nullable|numeric|min:0',
            'target_location_id' => 'required|exists:locations,id',
        ]);

        try {
            DB::beginTransaction();

            $noPol = strtoupper(str_replace(' ', '', $request->no_pol));

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
            $todayCount = VehicleTransaction::where('no_transaction', 'LIKE', 'VHC-' . $datePrefix . '-%')->count();
            $sequence = str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);
            $noTransaction = 'VHC-' . $datePrefix . '-' . $sequence;

            $timbanganLoc = Location::where('s_loc', 'TMB')->first();
            if (!$timbanganLoc) {
                throw new \Exception('Lokasi TIMBANGAN tidak ditemukan di database.');
            }

            // 1. Create Transaction at TIMBANGAN
            $transaction = VehicleTransaction::create([
                'no_transaction' => $noTransaction,
                'vehicle_id' => $vehicle->id,
                'jenis' => $request->jenis,
                'vendor' => $request->vendor ?? $vehicle->vendor,
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
            $targetLoc = Location::findOrFail($request->target_location_id);

            // Conclude Timbangan tracking
            $timbanganTrack->update([
                'departure_time' => Carbon::now(),
                'duration_seconds' => Carbon::now()->diffInSeconds($timbanganTrack->arrival_time),
                'status_notes' => 'Timbangan Masuk Selesai. Menuju ' . $targetLoc->name
            ]);

            // Map target location to transaction status
            $newStatus = 'smu';
            $initialQcStatus = 'pending';
            if ($targetLoc->s_loc === 'C001') {
                $newStatus = 'antri_sampling';
                $initialQcStatus = 'waiting_dokumen';
            } elseif ($targetLoc->s_loc === 'B006') $newStatus = 'wrm_bongkar';
            elseif ($targetLoc->s_loc === 'A001') $newStatus = 'wfg_muat';

            // Update transaction to target location
            $transaction->update([
                'current_location_id' => $targetLoc->id,
                'status' => $newStatus,
                'qc_status' => $initialQcStatus
            ]);

            // Create new tracking log for target location
            VehicleTracking::create([
                'vehicle_transaction_id' => $transaction->id,
                'location_id' => $targetLoc->id,
                'arrival_time' => Carbon::now(),
                'created_by' => Auth::id(),
            ]);

            // Broadcast movement
            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => $targetLoc->s_loc,
                'status' => $newStatus,
                'message' => "Truk {$noPol} diarahkan dari Timbangan menuju {$targetLoc->name}.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

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
        return view('vehicle.monitoring.wpm');
    }

    /**
     * Get JSON data for WPM QC Area.
     */
    public function wpmData()
    {
        $antriSampling = VehicleTransaction::with(['vehicle', 'item', 'targetLocation', 'activeTracking'])
            ->where('status', 'antri_sampling')
            ->orderBy('check_in_time', 'asc')
            ->get()
            ->map(function ($tx) {
                $tracking = $tx->activeTracking;

                $arrivalTime = $tracking ? $tracking->arrival_time : $tx->check_in_time;

                return [
                    'id' => $tx->id,
                    'no_pol' => $tx->vehicle->no_pol,
                    'vendor' => $tx->vendor,
                    'no_spb' => $tx->no_spb,
                    'qty_spb' => $tx->qty_spb ? number_format($tx->qty_spb, 0, ',', '.') : '-',
                    'item_name' => $tx->item ? $tx->item->name : 'N/A',
                    'arrival_time' => $arrivalTime->format('d-m-Y H:i'),
                    'arrival_timestamp' => $arrivalTime->timestamp,
                ];
            });

        $prosesSample = VehicleTransaction::with(['vehicle', 'item', 'targetLocation', 'activeTracking'])
            ->where('status', 'wpm_qc')
            ->orderBy('no_antrian', 'asc')
            ->get()
            ->map(function ($tx) {
                $tracking = $tx->activeTracking;

                $arrivalTime = $tracking ? $tracking->arrival_time : $tx->check_in_time;

                return [
                    'id' => $tx->id,
                    'no_antrian' => $tx->no_antrian,
                    'no_pol' => $tx->vehicle->no_pol,
                    'vendor' => $tx->vendor,
                    'no_spb' => $tx->no_spb,
                    'qty_spb' => $tx->qty_spb ? number_format($tx->qty_spb, 0, ',', '.') : '-',
                    'item_name' => $tx->item ? $tx->item->name : 'N/A',
                    'arrival_time' => $arrivalTime->format('d-m-Y H:i'),
                    'arrival_timestamp' => $arrivalTime->timestamp,
                ];
            });

        return response()->json([
            'antriSampling' => $antriSampling,
            'prosesSample' => $prosesSample
        ]);
    }

    /**
     * Update Queue Number for WPM QC Area.
     */
    public function wpmUpdateQueueNumber(Request $request, $id)
    {
        $request->validate([
            'no_antrian' => 'required|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);

            // cek no antrian apakah sudah ada
            $existingAntrian = VehicleTransaction::where('no_antrian', $request->no_antrian)
                ->where('qc_status', 'pending')
                ->first();

            if ($existingAntrian) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Antrian sudah ada.'
                ], 422);
            }

            // Ambil no antrian terbesar yang masih pending
            $maxAntrian = VehicleTransaction::where('qc_status', 'pending')
                ->max('no_antrian');

            if ($maxAntrian && $request->no_antrian <= $maxAntrian) {
                return response()->json([
                    'success' => false,
                    'message' => "No Antrian harus lebih besar dari {$maxAntrian}."
                ], 422);
            }

            // Update transaction to Proses Sampling status
            $transaction->update([
                'no_antrian' => $request->no_antrian,
                'status' => 'wpm_qc',
                'qc_status' => 'pending',
                'updated_by' => Auth::id()
            ]);

            // Update tracking log status note
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            if ($activeTrack) {
                $activeTrack->update([
                    'status_notes' => 'Dokumen diterima. No Antrian: ' . $request->no_antrian . '. Masuk Proses Sampling.'
                ]);
            }

            // Broadcast movement/update
            $noPol = $transaction->vehicle->no_pol;
            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'C001',
                'status' => 'wpm_qc',
                'message' => "Truk {$noPol} dokumen telah diterima. No Antrian: {$request->no_antrian}. Masuk Proses Sampling.",
                'time' => Carbon::now()->format('H:i:s')
            ]));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'No Antrian berhasil diupdate. Kendaraan masuk proses sampling.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate No Antrian: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update QC Sample Status in WPM QC Area.
     */
    public function wpmUpdateQC(Request $request, $id)
    {
        $request->validate([
            'qc_status' => 'required|in:released,rejected',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);
            $wpmLoc = Location::where('s_loc', 'C001')->first();

            // Find active WPM tracking log
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            $now = Carbon::now();
            $duration = $activeTrack ? $now->diffInSeconds($activeTrack->arrival_time) : 0;

            // Conclude WPM tracking
            if ($activeTrack) {
                $activeTrack->update([
                    'departure_time' => $now,
                    'duration_seconds' => $duration,
                    'status_notes' => "QC Hasil: " . strtoupper($request->qc_status) . ". Catatan: " . ($request->notes ?? '-')
                ]);
            }

            $noPol = $transaction->vehicle->no_pol;

            if ($request->qc_status === 'released') {
                // Move to WRM Unloading Area
                $wrmLoc = Location::where('s_loc', 'B006')->first();
                if (!$wrmLoc) {
                    throw new \Exception('Lokasi WRM (Raw Material) tidak ditemukan.');
                }

                $transaction->update([
                    'qc_status' => 'released',
                    'current_location_id' => $wrmLoc->id,
                    'status' => 'wrm_bongkar',
                    'unloading_status' => 'process',
                    'updated_by' => Auth::id()
                ]);

                // Create new tracking log for WRM
                VehicleTracking::create([
                    'vehicle_transaction_id' => $transaction->id,
                    'location_id' => $wrmLoc->id,
                    'arrival_time' => $now,
                    'created_by' => Auth::id()
                ]);

                event(new VehicleStatusUpdated([
                    'transaction_id' => $transaction->id,
                    'no_pol' => $noPol,
                    'current_location' => 'B006',
                    'status' => 'wrm_bongkar',
                    'message' => "Truk {$noPol} lolos QC WPM (Released) -> Menuju WRM Bongkar.",
                    'time' => $now->format('H:i:s')
                ]));

                $msg = 'Status QC Truk ' . $noPol . ' diperbarui ke RELEASED. Truk diarahkan ke WRM Bongkar.';
            } else {
                // Rejected, direct completed check-out (exits premises)
                $transaction->update([
                    'qc_status' => 'rejected',
                    'status' => 'completed',
                    'check_out_time' => $now,
                    'updated_by' => Auth::id()
                ]);

                event(new VehicleStatusUpdated([
                    'transaction_id' => $transaction->id,
                    'no_pol' => $noPol,
                    'current_location' => 'OUT',
                    'status' => 'completed',
                    'message' => "Truk {$noPol} ditolak QC WPM (Rejected) -> Selesai dan keluar area.",
                    'time' => $now->format('H:i:s')
                ]));

                $msg = 'Status QC Truk ' . $noPol . ' diperbarui ke REJECTED. Truk telah check-out dan dipersilakan keluar.';
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
        return view('vehicle.monitoring.wrm');
    }

    /**
     * Get JSON data for WRM Unloading Area.
     */
    public function wrmData()
    {
        $queue = VehicleTransaction::with(['vehicle', 'item', 'activeTracking'])
            ->where('status', 'wrm_bongkar')
            ->orderBy('no_antrian', 'asc')
            ->get()
            ->map(function ($tx) {
                $tracking = $tx->activeTracking;

                $arrivalTime = $tracking ? $tracking->arrival_time : $tx->check_in_time;

                return [
                    'id' => $tx->id,
                    'no_pol' => $tx->vehicle->no_pol,
                    'vendor' => $tx->vendor,
                    'item_name' => $tx->item ? $tx->item->name : 'N/A',
                    'no_spb' => $tx->no_spb ?? '-',
                    'qty_spb' => $tx->qty_spb ? number_format($tx->qty_spb, 0, ',', '.') : '-',
                    'arrival_time' => $arrivalTime->format('d-m-Y H:i'),
                    'arrival_timestamp' => $arrivalTime->timestamp,
                ];
            });

        return response()->json([
            'queue' => $queue
        ]);
    }

    /**
     * Finish WRM Unloading.
     */
    public function wrmUpdateUnloading(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);

            // Conclude WRM tracking
            $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                ->where('location_id', $transaction->current_location_id)
                ->whereNull('departure_time')
                ->latest()
                ->first();

            $now = Carbon::now();
            $duration = $activeTrack ? $now->diffInSeconds($activeTrack->arrival_time) : 0;

            if ($activeTrack) {
                $activeTrack->update([
                    'departure_time' => $now,
                    'duration_seconds' => $duration,
                    'status_notes' => 'Pembongkaran Selesai. Transaksi selesai dan keluar area.'
                ]);
            }

            $noPol = $transaction->vehicle->no_pol;

            // Update transaction to completed
            $transaction->update([
                'unloading_status' => 'completed',
                'status' => 'completed',
                'check_out_time' => $now,
                'updated_by' => Auth::id()
            ]);

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'OUT',
                'status' => 'completed',
                'message' => "Proses bongkar Truk {$noPol} di WRM telah selesai. Selesai dan keluar area.",
                'time' => $now->format('H:i:s')
            ]));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Status bongkaran truk ' . $noPol . ' diperbarui ke Selesai. Transaksi selesai.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyelesaikan bongkaran: ' . $e->getMessage()], 500);
        }
    }

    /**
     * WFG Loading/Unloading View.
     */
    public function wfgIndex()
    {
        return view('vehicle.monitoring.wfg');
    }

    /**
     * Get JSON data for WFG Loading/Unloading Area.
     */
    public function wfgData()
    {
        $queue = VehicleTransaction::with(['vehicle', 'item', 'targetLocation', 'activeTracking'])
            ->where('status', 'wfg_muat')
            ->orderBy('check_in_time', 'asc')
            ->get()
            ->map(function ($tx) {
                $tracking = $tx->activeTracking;

                $arrivalTime = $tracking ? $tracking->arrival_time : $tx->check_in_time;

                return [
                    'id' => $tx->id,
                    'no_pol' => $tx->vehicle->no_pol,
                    'vendor' => $tx->vendor,
                    'item_name' => $tx->item ? $tx->item->name : 'N/A',
                    'no_spb' => $tx->no_spb ?? '-',
                    'qty_spb' => $tx->qty_spb ? number_format($tx->qty_spb, 2) : '-',
                    'arrival_time' => $arrivalTime->format('d-m-Y H:i'),
                    'arrival_timestamp' => $arrivalTime->timestamp,
                ];
            });

        return response()->json([
            'queue' => $queue
        ]);
    }

    /**
     * Update WFG loading/unloading progress.
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
            $duration = $activeTrack ? $now->diffInSeconds($activeTrack->arrival_time) : 0;

            if ($activeTrack) {
                $activeTrack->update([
                    'departure_time' => $now,
                    'duration_seconds' => $duration,
                    'status_notes' => 'Proses Bongkar/Muat WFG Selesai. Transaksi selesai dan keluar area.'
                ]);
            }

            $noPol = $transaction->vehicle->no_pol;

            // Update transaction to completed
            $transaction->update([
                'unloading_status' => 'completed',
                'status' => 'completed',
                'check_out_time' => $now,
                'updated_by' => Auth::id()
            ]);

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'OUT',
                'status' => 'completed',
                'message' => "Proses Bongkar/Muat Truk {$noPol} di WFG telah selesai. Selesai dan keluar area.",
                'time' => $now->format('H:i:s')
            ]));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Status bongkar/muat truk ' . $noPol . ' diperbarui ke Selesai. Transaksi selesai.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyelesaikan bongkar/muat: ' . $e->getMessage()], 500);
        }
    }

    /**
     * SMU Area View.
     */
    public function smuIndex()
    {
        return view('vehicle.monitoring.smu');
    }

    /**
     * Get JSON data for SMU Area.
     */
    public function smuData()
    {
        $queue = VehicleTransaction::with(['vehicle', 'item', 'activeTracking'])
            ->where('status', 'smu')
            ->orderBy('check_in_time', 'asc')
            ->get()
            ->map(function ($tx) {
                $tracking = $tx->activeTracking;

                $arrivalTime = $tracking ? $tracking->arrival_time : $tx->check_in_time;

                return [
                    'id' => $tx->id,
                    'no_pol' => $tx->vehicle->no_pol,
                    'vendor' => $tx->vendor,
                    'item_name' => $tx->item ? $tx->item->name : 'N/A',
                    'no_spb' => $tx->no_spb ?? '-',
                    'qty_spb' => $tx->qty_spb ? number_format($tx->qty_spb, 2) : '-',
                    'arrival_time' => $arrivalTime->format('H:i'),
                    'arrival_timestamp' => $arrivalTime->timestamp,
                ];
            });

        return response()->json([
            'queue' => $queue
        ]);
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
            $duration = $activeTrack ? $now->diffInSeconds($activeTrack->arrival_time) : 0;

            if ($activeTrack) {
                $activeTrack->update([
                    'departure_time' => $now,
                    'duration_seconds' => $duration,
                    'status_notes' => 'Aktivitas SMU Selesai. Transaksi selesai dan keluar area.'
                ]);
            }

            $noPol = $transaction->vehicle->no_pol;

            // Update transaction to completed
            $transaction->update([
                'unloading_status' => 'completed',
                'status' => 'completed',
                'check_out_time' => $now,
                'updated_by' => Auth::id()
            ]);

            event(new VehicleStatusUpdated([
                'transaction_id' => $transaction->id,
                'no_pol' => $noPol,
                'current_location' => 'OUT',
                'status' => 'completed',
                'message' => "Proses Truk {$noPol} di SMU selesai. Selesai dan keluar area.",
                'time' => $now->format('H:i:s')
            ]));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Truk ' . $noPol . ' selesai di SMU. Transaksi selesai.']);
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
            $duration = $activeTrack ? $now->diffInSeconds($activeTrack->arrival_time) : 0;

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
            return redirect()->route('vehicle.monitoring.timbangan')->with('success', 'Truk ' . $noPol . ' berhasil Timbang Keluar (Check-Out).');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('vehicle.monitoring.timbangan')->with('error', 'Gagal Check-Out: ' . $e->getMessage());
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
            'jenis' => 'required|string|in:bongkaran,slipsheet,curah',
            'item_id' => 'required|exists:vehicle_items,id',
            'no_spb' => 'nullable|string|max:50',
            'qty_spb' => 'nullable|numeric|min:0',
            'target_location_id' => 'required|exists:locations,id',
        ]);

        try {
            DB::beginTransaction();

            $transaction = VehicleTransaction::findOrFail($id);

            $noPol = strtoupper(str_replace(' ', '', $request->no_pol));

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

            $transaction->update([
                'vehicle_id' => $vehicle->id,
                'jenis' => $request->jenis,
                'vendor' => $request->vendor ?? $vehicle->vendor,
                'item_id' => $request->item_id,
                'no_spb' => $request->no_spb,
                'qty_spb' => $request->qty_spb,
                'target_location_id' => $request->target_location_id,
                'updated_by' => Auth::id(),
            ]);

            // If target location changed, update the active tracking location to match the new destination
            if ($oldTargetId != $request->target_location_id && $transaction->status !== 'completed') {
                $targetLoc = Location::findOrFail($request->target_location_id);

                // Conclude current target tracking log (if active is at target, or update the active tracking)
                $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                    ->whereNull('departure_time')
                    ->latest()
                    ->first();

                if ($activeTrack && $activeTrack->location_id == $oldTargetId) {
                    $activeTrack->update([
                        'location_id' => $targetLoc->id
                    ]);
                }

                // Map target location to transaction status
                $newStatus = 'smu';
                $initialQcStatus = $transaction->qc_status;
                if ($targetLoc->s_loc === 'C001') {
                    $newStatus = 'antri_sampling';
                    $initialQcStatus = 'waiting_dokumen';
                } elseif ($targetLoc->s_loc === 'B006') $newStatus = 'wrm_bongkar';
                elseif ($targetLoc->s_loc === 'A001') $newStatus = 'wfg_muat';

                $transaction->update([
                    'current_location_id' => $targetLoc->id,
                    'status' => $newStatus,
                    'qc_status' => $initialQcStatus
                ]);
            }

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
            // Delete related tracking logs
            VehicleTracking::where('vehicle_transaction_id', $transaction->id)->delete();
            $transaction->delete();
            DB::commit();

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
        $items = VehicleItem::orderBy('id')->get();
        $locations = Location::orderBy('id')->get();
        $vendors = VehicleVendor::orderBy('id')->get();
        return view('vehicle.monitoring.master_items', compact('items', 'locations', 'vendors'));
    }

    /**
     * Store Master SKU.
     */
    public function masterItemsStore(Request $request)
    {
        $request->validate([
            // 'sku' => 'required|string|max:50|unique:vehicle_items,sku',
            'name' => 'required|string|max:100',
        ]);

        VehicleItem::create([
            // 'sku' => strtoupper($request->sku),
            'name' => $request->name,
        ]);

        return redirect()->route('vehicle.monitoring.master.items')->with('success', 'Item SKU berhasil ditambahkan.');
    }

    /**
     * Update Master SKU.
     */
    public function masterItemsUpdate(Request $request, $id)
    {
        $request->validate([
            // 'sku' => 'required|string|max:50|unique:vehicle_items,sku,' . $id,
            'name' => 'required|string|max:100',
        ]);

        $item = VehicleItem::findOrFail($id);
        $item->update([
            // 'sku' => strtoupper($request->sku),
            'name' => $request->name,
        ]);

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
            return redirect()->route('vehicle.monitoring.master.items')->with('success', 'Item SKU berhasil dihapus.');
        } catch (\Exception $e) {
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

        Location::create([
            's_loc' => strtoupper($request->s_loc),
            'name' => $request->name,
            'description' => $request->description,
        ]);

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
            return redirect()->route('vehicle.monitoring.master.items')
                ->with('success', 'Sloc berhasil dihapus.')
                ->with('tab', 'sloc');
        } catch (\Exception $e) {
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

        VehicleVendor::create([
            'name' => $request->vendor_name,
            'description' => $request->description,
        ]);

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
            return redirect()->route('vehicle.monitoring.master.items')
                ->with('success', 'Vendor berhasil dihapus.')
                ->with('tab', 'vendor');
        } catch (\Exception $e) {
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
        return view('vehicle.monitoring.history');
    }

    /**
     * Get historical log data for reports.
     */
    public function historyData(Request $request)
    {
        $query = VehicleTransaction::with(['vehicle', 'item', 'targetLocation', 'tracking.location'])
            ->where('status', 'completed');

        if ($request->filled('start_date')) {
            $query->whereDate('check_in_time', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('check_in_time', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_transaction', 'LIKE', "%{$search}%")
                    ->orWhere('no_spb', 'LIKE', "%{$search}%")
                    ->orWhereHas('vehicle', function ($vQ) use ($search) {
                        $vQ->where('no_pol', 'LIKE', "%{$search}%");
                    });
            });
        }

        $transactions = $query->orderBy('check_in_time', 'desc')->get()->map(function ($tx) {
            $totalDurationSeconds = $tx->check_out_time ? $tx->check_out_time->diffInSeconds($tx->check_in_time) : 0;

            // Format movement history path
            $historyPath = $tx->tracking->map(function ($track) {
                $durMin = $track->duration_seconds ? round($track->duration_seconds / 60) . 'm' : 'aktif';
                return "{$track->location->name} ({$durMin})";
            })->implode(' ➔ ');

            return [
                'id' => $tx->id,
                'no_transaction' => $tx->no_transaction,
                'no_pol' => $tx->vehicle->no_pol,
                'vendor' => $tx->vendor,
                'jenis' => $tx->jenis,
                'item_name' => $tx->item ? $tx->item->name : 'N/A',
                'no_spb' => $tx->no_spb,
                'qty_spb' => $tx->qty_spb,
                'target_name' => $tx->targetLocation->name,
                'check_in' => $tx->check_in_time->format('Y-m-d H:i'),
                'check_out' => $tx->check_out_time ? $tx->check_out_time->format('Y-m-d H:i') : '-',
                'duration' => round($totalDurationSeconds / 60) . ' menit',
                'history_path' => $historyPath,
            ];
        });

        return response()->json($transactions);
    }
}
