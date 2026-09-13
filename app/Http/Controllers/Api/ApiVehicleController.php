<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle\Vehicle;
use App\Models\Vehicle\VehicleTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiVehicleController extends Controller
{
    /**
     * Get list of vehicle transactions with optional search and filter.
     */
    public function index(Request $request)
    {
        try {
            $query = VehicleTransaction::with([
                'vehicle',
                'targetLocation',
                'currentLocation',
                'item',
            ])->orderBy('id', 'desc');

            if ($request->filled('nopol')) {
                $cleanNopol = strtoupper(str_replace([' ', '-'], '', $request->nopol));
                $query->whereHas('vehicle', function ($q) use ($cleanNopol) {
                    $q->whereRaw("REPLACE(REPLACE(UPPER(no_pol), ' ', ''), '-', '') LIKE ?", ["%{$cleanNopol}%"]);
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('unloading_status')) {
                $query->where('unloading_status', $request->unloading_status);
            }

            $perPage = $request->input('per_page', 25);
            $data = $query->paginate($perPage);

            return response()->json([
                'status'  => 'success',
                'message' => 'Vehicle transactions retrieved successfully.',
                'data'    => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data transaksi kendaraan.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get latest vehicle transaction by license plate (no_pol).
     */
    public function showByNopol(Request $request, $nopol)
    {
        try {
            if (empty($nopol)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Nomor polisi tidak boleh kosong.',
                    'found'   => false,
                    'data'    => null,
                ], 400);
            }

            $cleanNopol = strtoupper(str_replace([' ', '-'], '', $nopol));

            $transaction = VehicleTransaction::with([
                'vehicle',
                'targetLocation',
                'currentLocation',
                'item',
            ])
                ->whereHas('vehicle', function ($q) use ($cleanNopol) {
                    $q->whereRaw("REPLACE(REPLACE(UPPER(no_pol), ' ', ''), '-', '') = ?", [$cleanNopol]);
                })
                ->orderBy('id', 'desc')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'status'  => 'success',
                    'found'   => false,
                    'message' => 'Transaksi kendaraan tidak ditemukan untuk nomor polisi: ' . $nopol,
                    'data'    => null,
                ]);
            }

            $formatted = $this->formatTransaction($transaction, $nopol);

            return response()->json([
                'status'  => 'success',
                'found'   => true,
                'message' => 'Transaksi kendaraan ditemukan.',
                'data'    => $formatted,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'found'   => false,
                'message' => 'Terjadi kesalahan saat memproses data kendaraan.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch query vehicle transactions by an array of nopols.
     */
    public function batch(Request $request)
    {
        try {
            $nopols = $request->input('nopols', []);
            if (is_string($nopols)) {
                $nopols = array_filter(explode(',', $nopols));
            }

            if (empty($nopols) || !is_array($nopols)) {
                return response()->json([
                    'status' => 'success',
                    'data'   => (object)[],
                ]);
            }

            $cleanMap = [];
            foreach ($nopols as $np) {
                $clean = strtoupper(str_replace([' ', '-'], '', trim($np)));
                if ($clean !== '') {
                    $cleanMap[$clean] = $clean;
                }
            }

            if (empty($cleanMap)) {
                return response()->json([
                    'status' => 'success',
                    'data'   => (object)[],
                ]);
            }

            $transactions = VehicleTransaction::with([
                'vehicle',
                'targetLocation',
                'currentLocation',
                'item',
            ])
                ->whereHas('vehicle', function ($q) use ($cleanMap) {
                    $q->whereIn(DB::raw("REPLACE(REPLACE(UPPER(no_pol), ' ', ''), '-', '')"), array_values($cleanMap));
                })
                ->orderBy('id', 'desc')
                ->get();

            $result = [];
            foreach ($transactions as $txn) {
                $nopolVal = $txn->vehicle ? $txn->vehicle->no_pol : '';
                $cleanKey = strtoupper(str_replace([' ', '-'], '', $nopolVal));
                if ($cleanKey && !isset($result[$cleanKey])) {
                    $result[$cleanKey] = $this->formatTransaction($txn);
                }
            }

            return response()->json([
                'status' => 'success',
                'data'   => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil batch transaksi kendaraan.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to format transaction output consistently.
     */
    private function formatTransaction($transaction, $fallbackNopol = null)
    {
        $queueTime = $transaction->queue_taken_time;
        $queueTakenFormatted = null;
        $queueTakenHuman = null;

        if ($queueTime) {
            $queueTakenFormatted = is_object($queueTime) ? $queueTime->format('Y-m-d H:i:s') : (string)$queueTime;
            $queueTakenHuman = is_object($queueTime) ? $queueTime->format('H:i') : substr((string)$queueTime, 11, 5);
        }

        $vehicleNoPol = $transaction->vehicle ? $transaction->vehicle->no_pol : $fallbackNopol;
        $targetLoc = $transaction->targetLocation;
        $currentLoc = $transaction->currentLocation;
        $item = $transaction->item;

        $startLoading = $transaction->start_loading_time;
        $finishLoading = $transaction->finish_loading_time;
        $startSampling = $transaction->start_sampling_time;
        $finishSampling = $transaction->finish_sampling_time;
        $checkIn = $transaction->check_in_time;
        $checkOut = $transaction->check_out_time;
        $createdAt = $transaction->created_at;

        return [
            'id'                  => $transaction->id,
            'no_transaction'      => $transaction->no_transaction,
            'no_pol'              => $vehicleNoPol,
            'vendor'              => $transaction->vendor,
            'nama_driver'         => $transaction->nama_driver,
            'no_hp_driver'        => $transaction->no_hp_driver,
            'jenis'               => $transaction->jenis,
            'item_id'             => $transaction->item_id,
            'item_name'           => $item ? $item->name : null,
            'no_spb'              => $transaction->no_spb,
            'qty_spb'             => $transaction->qty_spb,
            'target_area'         => $targetLoc ? $targetLoc->name : '-',
            'target_area_code'    => $targetLoc ? $targetLoc->s_loc : '-',
            'target_location'     => $targetLoc ? [
                'id'          => $targetLoc->id,
                's_loc'       => $targetLoc->s_loc,
                'name'        => $targetLoc->name,
                'description' => $targetLoc->description,
            ] : null,
            'current_location'    => $currentLoc ? [
                'id'    => $currentLoc->id,
                's_loc' => $currentLoc->s_loc,
                'name'  => $currentLoc->name,
            ] : null,
            'status'              => $transaction->status,
            'qc_status'           => $transaction->qc_status,
            'unloading_status'    => $transaction->unloading_status ? $transaction->unloading_status : 'pending',
            'no_antrian'          => $transaction->no_antrian,
            'queue_taken_time'    => $queueTakenFormatted,
            'queue_taken_human'   => $queueTakenHuman,
            'start_sampling_time' => (is_object($startSampling) ? $startSampling->format('Y-m-d H:i:s') : $startSampling),
            'finish_sampling_time' => (is_object($finishSampling) ? $finishSampling->format('Y-m-d H:i:s') : $finishSampling),
            'start_loading_time'  => (is_object($startLoading) ? $startLoading->format('Y-m-d H:i:s') : $startLoading),
            'finish_loading_time' => (is_object($finishLoading) ? $finishLoading->format('Y-m-d H:i:s') : $finishLoading),
            'check_in_time'       => (is_object($checkIn) ? $checkIn->format('Y-m-d H:i:s') : $checkIn),
            'check_out_time'      => (is_object($checkOut) ? $checkOut->format('Y-m-d H:i:s') : $checkOut),
            'created_at'          => (is_object($createdAt) ? $createdAt->format('Y-m-d H:i:s') : $createdAt),
        ];
    }
}
