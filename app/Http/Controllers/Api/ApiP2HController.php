<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\P2h\P2HForklfitModel;
use App\Models\P2h\P2HPalletMoverModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ApiP2HController extends Controller
{
    /**
     * Get all P2H data (Forklift & Pallet Mover).
     *
     * Supports:
     * - type / jenis_p2h: 'all', 'forklift', 'pallet_mover'
     * - format: 'combined' (default) or 'separate'
     * - tanggal: YYYY-MM-DD
     * - start_date & end_date: date range
     * - nomor_unit: filter unit
     * - shift: filter shift
     * - dept: filter department
     * - operator_name: filter operator
     * - search: search keyword across unit, operator, notes, etc.
     * - status_kelayakan: 'Layak', 'Perlu Perhatian', 'Tidak Layak'
     * - paginate: true/false
     * - per_page: items per page if paginated (default: 25)
     * - limit: max records if not paginated
     */
    public function index(Request $request)
    {
        try {
            $type = strtolower($request->input('type', $request->input('jenis_p2h', 'all')));
            $format = strtolower($request->input('format', 'combined'));

            $forkliftData = collect();
            $palletData = collect();

            // Fetch Forklift if requested
            if (in_array($type, ['all', 'forklift', 'fork_lift'])) {
                $forkliftData = $this->queryForklift($request)->get()->map(function ($item) {
                    $itemArray = $item->toArray();
                    $itemArray['jenis_p2h'] = $item->jenis_p2h ?: 'Forklift';
                    $itemArray['kelayakan'] = $this->safelyCalculateKelayakan($item);
                    return $itemArray;
                });
            }

            // Fetch Pallet Mover if requested
            if (in_array($type, ['all', 'pallet_mover', 'pallet-mover', 'pallet', 'pallet mover'])) {
                $palletData = $this->queryPalletMover($request)->get()->map(function ($item) {
                    $itemArray = $item->toArray();
                    $itemArray['jenis_p2h'] = $item->jenis_p2h ?: 'Pallet Mover';
                    $itemArray['kelayakan'] = $this->safelyCalculateKelayakan($item);
                    return $itemArray;
                });
            }

            // Filter by status_kelayakan if provided
            if ($request->filled('status_kelayakan')) {
                $statusTarget = strtolower($request->status_kelayakan);
                $forkliftData = $forkliftData->filter(function ($item) use ($statusTarget) {
                    return strtolower($item['kelayakan']['status'] ?? '') === $statusTarget;
                })->values();

                $palletData = $palletData->filter(function ($item) use ($statusTarget) {
                    return strtolower($item['kelayakan']['status'] ?? '') === $statusTarget;
                })->values();
            }

            $countForklift = $forkliftData->count();
            $countPallet = $palletData->count();
            $totalCount = $countForklift + $countPallet;

            // Return format: separate
            if ($format === 'separate') {
                return response()->json([
                    'success' => true,
                    'status'  => 'success',
                    'message' => 'Data P2H Forklift dan Pallet Mover berhasil diambil.',
                    'meta'    => [
                        'total'              => $totalCount,
                        'total_forklift'     => $countForklift,
                        'total_pallet_mover' => $countPallet,
                    ],
                    'data' => [
                        'forklift'     => $request->filled('limit') ? $forkliftData->take($request->limit)->values() : $forkliftData,
                        'pallet_mover' => $request->filled('limit') ? $palletData->take($request->limit)->values() : $palletData,
                    ]
                ], 200);
            }

            // Default format: combined
            $combined = $forkliftData->concat($palletData)->sortByDesc(function ($item) {
                return ($item['tanggal'] ?? '') . ' ' . ($item['created_at'] ?? '');
            })->values();

            // Pagination support
            if ($request->boolean('paginate')) {
                $perPage = (int) $request->input('per_page', 25);
                $page = (int) $request->input('page', 1);
                $slice = $combined->slice(($page - 1) * $perPage, $perPage)->values();

                $paginator = new LengthAwarePaginator(
                    $slice,
                    $combined->count(),
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );

                return response()->json([
                    'success' => true,
                    'status'  => 'success',
                    'message' => 'Data P2H berhasil diambil (paginated).',
                    'meta'    => [
                        'total'              => $totalCount,
                        'total_forklift'     => $countForklift,
                        'total_pallet_mover' => $countPallet,
                    ],
                    'data'    => $paginator
                ], 200);
            }

            // Optional limit
            if ($request->filled('limit')) {
                $combined = $combined->take((int) $request->limit)->values();
            }

            return response()->json([
                'success' => true,
                'status'  => 'success',
                'message' => 'Semua data P2H berhasil diambil.',
                'total'   => $totalCount,
                'meta'    => [
                    'total_all'          => $totalCount,
                    'total_forklift'     => $countForklift,
                    'total_pallet_mover' => $countPallet,
                ],
                'data'    => $combined
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'Gagal mengambil data P2H.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show detail of a single P2H record.
     * $type: 'forklift' or 'pallet-mover'
     */
    public function show($type, $id)
    {
        try {
            $normalizedType = strtolower(str_replace(['_', ' '], '-', $type));

            if ($normalizedType === 'forklift') {
                $record = P2HForklfitModel::find($id);
                if (!$record) {
                    return response()->json([
                        'success' => false,
                        'status'  => 'error',
                        'message' => "Data P2H Forklift dengan ID {$id} tidak ditemukan."
                    ], 404);
                }

                $data = $record->toArray();
                $data['jenis_p2h'] = $record->jenis_p2h ?: 'Forklift';
                $data['kelayakan'] = $this->safelyCalculateKelayakan($record);

                return response()->json([
                    'success' => true,
                    'status'  => 'success',
                    'message' => 'Detail P2H Forklift berhasil diambil.',
                    'data'    => $data
                ], 200);
            }

            if (in_array($normalizedType, ['pallet-mover', 'pallet', 'pm'])) {
                $record = P2HPalletMoverModel::find($id);
                if (!$record) {
                    return response()->json([
                        'success' => false,
                        'status'  => 'error',
                        'message' => "Data P2H Pallet Mover dengan ID {$id} tidak ditemukan."
                    ], 404);
                }

                $data = $record->toArray();
                $data['jenis_p2h'] = $record->jenis_p2h ?: 'Pallet Mover';
                $data['kelayakan'] = $this->safelyCalculateKelayakan($record);

                return response()->json([
                    'success' => true,
                    'status'  => 'success',
                    'message' => 'Detail P2H Pallet Mover berhasil diambil.',
                    'data'    => $data
                ], 200);
            }

            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => "Tipe unit '{$type}' tidak valid. Gunakan 'forklift' atau 'pallet-mover'."
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'Gagal mengambil detail data P2H.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get summary counts and statistics of P2H.
     */
    public function summary(Request $request)
    {
        try {
            $totalForklift = P2HForklfitModel::count();
            $totalPallet = P2HPalletMoverModel::count();
            $totalAll = $totalForklift + $totalPallet;

            $latestForklift = P2HForklfitModel::latest('tanggal')->first();
            $latestPallet = P2HPalletMoverModel::latest('tanggal')->first();

            return response()->json([
                'success' => true,
                'status'  => 'success',
                'message' => 'Summary P2H berhasil diambil.',
                'data'    => [
                    'total_p2h'            => $totalAll,
                    'total_forklift'       => $totalForklift,
                    'total_pallet_mover'   => $totalPallet,
                    'latest_p2h_forklift'  => $latestForklift ? $latestForklift->tanggal : null,
                    'latest_p2h_pallet'    => $latestPallet ? $latestPallet->tanggal : null,
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'Gagal mengambil summary P2H.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Query builder for P2H Forklift with filter rules.
     */
    private function queryForklift(Request $request)
    {
        $query = P2HForklfitModel::query()->orderBy('tanggal', 'desc')->orderBy('id', 'desc');

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        if ($request->filled('nomor_unit')) {
            $query->where('nomor_unit', 'like', "%{$request->nomor_unit}%");
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        if ($request->filled('dept')) {
            $query->where('dept', 'like', "%{$request->dept}%");
        }

        if ($request->filled('operator_name')) {
            $query->where('operator_name', 'like', "%{$request->operator_name}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_unit', 'like', "%{$search}%")
                    ->orWhere('operator_name', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%")
                    ->orWhere('dept', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Query builder for P2H Pallet Mover with filter rules.
     */
    private function queryPalletMover(Request $request)
    {
        $query = P2HPalletMoverModel::query()->orderBy('tanggal', 'desc')->orderBy('id', 'desc');

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        if ($request->filled('nomor_unit')) {
            $query->where('nomor_unit', 'like', "%{$request->nomor_unit}%");
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        if ($request->filled('dept')) {
            $query->where('dept', 'like', "%{$request->dept}%");
        }

        if ($request->filled('operator_name')) {
            $query->where('operator_name', 'like', "%{$request->operator_name}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_unit', 'like', "%{$search}%")
                    ->orWhere('operator_name', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%")
                    ->orWhere('dept', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Safely calculate kelayakan without breaking if errors occur.
     */
    private function safelyCalculateKelayakan($item)
    {
        try {
            if (method_exists($item, 'calculateKelayakan')) {
                return $item->calculateKelayakan();
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return [
            'persentase' => (float) ($item->persentase ?? 0),
            'status'     => 'Unknown'
        ];
    }
}
