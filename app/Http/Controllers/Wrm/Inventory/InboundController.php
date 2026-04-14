<?php

namespace App\Http\Controllers\Wrm\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wrm\StockGulaRequest;
use App\Http\Requests\Wrm\StockGulaUploadRequest;
use App\Models\Wrm\Inventory\StockBalance;
use App\Models\Wrm\Inventory\StockInbound;
use App\Models\Wrm\Inventory\StockInboundDetail;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\Inventory\TempUploadModel;
use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\MasterBinModel;
use App\Models\Wrm\MasterLocationModel;
use App\Models\Wrm\MasterPalletModel;
use App\Models\Wrm\MasterSupplierModel;
use App\Models\Wrm\StockGula\StockGulaModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InboundController extends Controller
{
    private function allocateBins($data, $availableBinsGrouped, $dbColumnOwners)
    {
        // First sort data by no_spb then mid, to minimize column switches
        $sortedData = $data->sortBy([
            ['no_spb', 'asc'],
            ['mid', 'asc'],
        ])->values();

        $allocated = [];
        $colKeys = array_keys($availableBinsGrouped);
        $currentColIndex = 0;

        $activeOwner = null;
        $currentColKey = null;
        $currentColBins = [];
        $binPointer = 0;

        foreach ($sortedData as $item) {
            $itemOwner = $item->no_spb . '-' . $item->mid;

            if ($activeOwner !== $itemOwner) {
                $activeOwner = $itemOwner;
                $currentColKey = null;
                $currentColBins = [];
                $binPointer = 0;
            }

            while ($currentColKey === null || $binPointer >= count($currentColBins)) {
                if ($currentColIndex >= count($colKeys)) {
                    // Out of space!
                    return $allocated;
                }

                $colKey = $colKeys[$currentColIndex];
                $currentColIndex++;

                // If column is partially owned in DB, check if owner matches
                if (isset($dbColumnOwners[$colKey]) && $dbColumnOwners[$colKey] !== $itemOwner) {
                    continue; // Skip column
                }

                $currentColKey = $colKey;
                $currentColBins = $availableBinsGrouped[$colKey];
                $binPointer = 0;
            }

            $bin = $currentColBins[$binPointer];
            $binPointer++;

            $allocated[] = [
                'item' => $item,
                'bin' => $bin
            ];
        }

        return $allocated;
    }

    public function index()
    {
        $today = Carbon::today();

        $barang = MasterBarangModel::select('id', 'mid', 'nama_barang')
            ->whereRaw('LOWER(nama_barang) LIKE ?', ['%gula%'])
            ->get();

        $suppliers = MasterSupplierModel::orderBy('nama')->get();

        return view('wrm.inventory.stock-on-hand', compact('barang', 'suppliers'));
    }

    public function indexUpload()
    {
        $hasTemp = TempUploadModel::whereDate('incoming_date', now())->exists();

        if ($hasTemp) {
            return redirect()->route('wrm.inventory.select-location');
        }

        $barang = MasterBarangModel::select('id', 'mid', 'nama_barang')->get();

        return view('wrm.inventory.upload', compact('barang'));
    }

    public function selectLocationView()
    {
        $today = Carbon::now();

        // Get the first/oldest unique no_spb per incoming_date
        $firstNoSpb = TempUploadModel::whereDate('incoming_date', $today)
            ->orderBy('id')
            ->value('no_spb');

        if (!$firstNoSpb) {
            return redirect()->route('wrm.inventory.index-upload');
        }

        // Get ONLY data with this no_spb
        $data = TempUploadModel::whereDate('incoming_date', $today)
            ->where('no_spb', $firstNoSpb)
            ->get();

        if ($data->isEmpty()) {
            return redirect()->route('wrm.inventory.index-upload');
        }

        // Count remaining no_spb (excluding current one)
        $remainingCount = TempUploadModel::whereDate('incoming_date', $today)
            ->where('no_spb', '!=', $firstNoSpb)
            ->distinct('no_spb')
            ->count('no_spb');

        $usedBinIds = StockInboundDetail::whereNotIn('status', ['ISSUED', 'RESERVED'])->pluck('loc_id')->toArray();

        // 1. Get database column owners to prevent mixing
        $usedDetails = StockInboundDetail::with(['inbound:id,no_spb', 'barang:id,mid', 'bin:id,loc_id,kolom'])
            ->whereNotIn('status', ['ISSUED', 'RESERVED'])
            ->get();
        $dbColumnOwners = [];
        foreach ($usedDetails as $d) {
            if ($d->bin && $d->inbound && $d->barang) {
                $colKey = $d->bin->loc_id . '-' . $d->bin->kolom;
                $ownerKey = $d->inbound->no_spb . '-' . $d->barang->mid;
                $dbColumnOwners[$colKey] = $ownerKey;
            }
        }

        // FILTER: Only use pristine columns (Rule 1)
        $occupiedColumnKeys = $this->getOccupiedColumnKeys();

        // Get all available bins grouped by zona
        $availableBins = MasterBinModel::with('location')
            ->whereNotIn('id', $usedBinIds)
            ->get()
            ->filter(function ($bin) use ($occupiedColumnKeys) {
                $key = $bin->loc_id . '-' . $bin->kolom;
                return !in_array($key, $occupiedColumnKeys);
            });

        $locationsGrouped = $availableBins->groupBy('loc_id');

        $availableLocations = [];
        $locationError = null;
        $errorDetails = [];

        // Check each location (physical rack) whether it can accommodate the SPB pallet
        foreach ($locationsGrouped as $locId => $bins) {
            $first = $bins->first();
            $location = $first->location;

            // ATURAN BARU (Rule 3): Hanya tampilkan jika kapasitas mencukupi untuk SEMUA barang dalam SPB
            if (count($bins) > 0) {
                $availableLocations[] = [
                    'location_id' => $location->id,
                    'plant' => $location->plant,
                    's_loc' => $location->s_loc,
                    'gudang' => $location->gudang,
                    'zona' => $location->zona,
                    'bin' => $location->bin, // This is the Rack ID / Bin name
                ];
            }
        }

        // Global check: do we have enough bins OVERALL across all zones?
        $allAvailableBinsGrouped = [];
        foreach ($availableBins as $bin) {
            $colKey = $bin->loc_id . '-' . $bin->kolom;
            if (!isset($allAvailableBinsGrouped[$colKey])) {
                $allAvailableBinsGrouped[$colKey] = [];
            }
            $allAvailableBinsGrouped[$colKey][] = $bin;
        }

        $globalAllocationResult = $this->allocateBins($data, $allAvailableBinsGrouped, $dbColumnOwners);

        if (count($globalAllocationResult) < count($data)) {
            $locationError = 'Kapasitas gudang (berdasarkan Aturan Pristine Column) tidak mencukupi untuk menampung ' . count($data) . ' pallet no_spb ' . $firstNoSpb . '. (Tersedia hanya untuk ' . count($globalAllocationResult) . ' pallet)';
        }

        $pallet = MasterPalletModel::get();
        $suppliers = MasterSupplierModel::orderBy('nama')->get();

        return view('wrm.inventory.after_upload', [
            'data' => $data,
            'currentNoSpb' => $firstNoSpb,
            'remainingCount' => $remainingCount,
            'zones' => $availableLocations, // Keep variable name 'zones' to minimize blade changes or rename to locations
            'pallet' => $pallet,
            'suppliers' => $suppliers,
            'locationError' => $locationError
        ]);
    }

    public function getBarang(Request $request)
    {
        $q = $request->q;

        $query = MasterBarangModel::select('id', 'mid', 'nama_barang');

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('mid', 'like', "%{$q}%")
                    ->orWhere('nama_barang', 'like', "%{$q}%");
            });
        } else {
            $query->latest()->limit(5);
        }

        $barang = $query->limit(20)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'mid' => $item->mid,
                'nama_barang' => $item->nama_barang,
                'text' => "{$item->mid} - {$item->nama_barang}"
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $barang
        ]);
    }

    public function getLocationAjax(Request $request)
    {
        $q = $request->q;
        $exclude = (array) ($request->exclude ?? []);

        // Get IDs of bins that are currently occupied
        $occupiedBinIds = StockInboundDetail::whereNotIn('status', ['ISSUED', 'RESERVED'])->pluck('loc_id');

        $query = MasterBinModel::with('location')
            ->join('wrm_master_location', 'wrm_master_bin.loc_id', '=', 'wrm_master_location.id')
            ->select('wrm_master_bin.*')
            ->whereNotIn('wrm_master_bin.id', $occupiedBinIds) // Individual check
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('wrm_stock_inbound_details')
                    ->join('wrm_master_bin as b_occ', 'wrm_stock_inbound_details.loc_id', '=', 'b_occ.id')
                    ->whereNotIn('wrm_stock_inbound_details.status', ['ISSUED', 'RESERVED'])
                    ->whereColumn('b_occ.loc_id', 'wrm_master_bin.loc_id')
                    ->whereColumn('b_occ.kolom', 'wrm_master_bin.kolom');
            })
            ->when(!empty($exclude), function ($q) use ($exclude) {
                $q->whereNotIn('wrm_master_bin.id', $exclude);
            });

        if ($q) {
            $parts = explode('-', $q);
            foreach ($parts as $part) {
                $part = trim($part);
                if (empty($part)) continue;

                $query->where(DB::raw("CONCAT_WS(' ', plant, s_loc, gudang, zona, bin, kolom, level)"), 'like', "%{$part}%");
            }
        }

        $locations = $query->limit(200)->get()->map(function ($bin) {
            $loc = $bin->location;
            $text = "{$loc->plant} - {$loc->s_loc} - {$loc->gudang} - {$loc->zona} - {$loc->bin} - ({$bin->kolom}.{$bin->level})";

            return [
                'id' => $bin->id,
                'text' => $text,
                'details' => [
                    'plant' => $loc->plant,
                    's_loc' => $loc->s_loc,
                    'gudang' => $loc->gudang,
                    'zona' => $loc->zona,
                    'bin' => $loc->bin,
                    'kolom' => $bin->kolom,
                    'level' => $bin->level,
                ]
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $locations
        ]);
    }

    public function storeUpload(StockGulaUploadRequest $request)
    {
        DB::beginTransaction();

        try {

            // Validasi duplikasi bin di dalam satu request upload
            $locIds = array_filter($request->loc_id);
            if (count($locIds) !== count(array_unique($locIds))) {
                throw new \Exception("Ada bin/lokasi yang dipilih lebih dari satu kali untuk pallet berbeda. Silahkan periksa kembali.");
            }

            $temps = TempUploadModel::whereIn('id', array_keys($request->loc_id))->get();

            if ($temps->isEmpty()) {
                throw new \Exception("Data tidak ditemukan");
            }

            // Get the no_spb dari data yang akan disimpan
            $currentNoSpb = $temps->first()->no_spb;

            // Combine selected date with current time to avoid 00:00:00
            $incomingDateWithTime = $request->incoming_date . ' ' . date('H:i:s');

            // Validasi apakah semua temp_id punya loc_id dan loc_id tidak kosong
            foreach ($request->loc_id as $tempId => $locId) {
                if (empty($locId)) {
                    throw new \Exception("Ada pallet yang belum ditentukan lokasinya. Silahkan pilih zona terlebih dahulu.");
                }

                // Cek apakah lokasi ini sudah terpakai
                $isOccupied = StockInboundDetail::where('loc_id', $locId)
                    ->whereNotIn('status', ['ISSUED', 'RESERVED'])
                    ->exists();

                if ($isOccupied) {
                    $bin = MasterBinModel::with('location')->find($locId);
                    $locText = $bin ? "{$bin->location->plant} - {$bin->location->bin}" : "dengan ID #{$locId}";
                    throw new \Exception("Lokasi {$locText} sudah terpakai oleh stok lain.");
                }
            }

            $barangs = MasterBarangModel::whereIn('mid', $temps->pluck('mid'))
                ->get()
                ->keyBy('mid');

            // Collect all bin IDs and fetch them once
            $binIds = array_values($request->loc_id);
            $bins = MasterBinModel::whereIn('id', $binIds)->get()->keyBy('id');

            $headers = [];

            foreach ($request->loc_id as $tempId => $binId) {

                $temp = $temps->firstWhere('id', $tempId);

                $barang = $barangs[$temp->mid] ?? null;

                if (!$barang) {
                    throw new \Exception("MID {$temp->mid} tidak ditemukan");
                }

                // Get bin and location
                $bin = $bins[$binId] ?? null;
                if (!$bin) {
                    throw new \Exception("Bin tidak ditemukan");
                }

                $locationId = $bin->loc_id;

                if (!isset($headers[$temp->no_spb])) {
                    $headers[$temp->no_spb] = StockInbound::create([
                        'no_spb'        => $temp->no_spb,
                        'incoming_date' => $incomingDateWithTime,
                        'expired_date'  => $temp->expired_date ?? null,
                        'supplier'      => $request->supplier ?? $temp->supplier,
                        'created_by'    => Auth::id(),
                    ]);
                }

                $header = $headers[$temp->no_spb];

                // Store with bin_id in loc_id field (as per new FK)
                $detail = StockInboundDetail::create([
                    'inbound_id' => $header->id,
                    'barang_id'  => $barang->id,
                    'barcode'    => $temp->barcode,
                    'pallet_id'  => $temp->pallet_id,
                    'group'      => $temp->group,
                    'qty'        => $temp->qty,
                    'status'     => $request->status[$tempId] ?? 'UNREST',
                    'loc_id'     => $binId,
                    'pallet'     => $request->pallet ?? $temp->pallet,
                    'catatan'    => $temp->catatan,
                    'created_by' => Auth::id(),
                ]);

                // Use location_id for StockMovement (still refers to location)
                StockMovement::create([
                    'barang_id'  => $barang->id,
                    'loc_id'     => $locationId,
                    'qty'        => $temp->qty,
                    'tanggal'    => $incomingDateWithTime,
                    'jenis'      => 'in',
                    'ref_type'   => 'inbound',
                    'ref_id'     => $detail->id,
                    'catatan'    => $temp->catatan,
                    'created_by' => Auth::id(),
                ]);

                // Use location_id for StockBalance (still refers to location)
                $balance = StockBalance::where('barang_id', $barang->id)
                    ->where('loc_id', $locationId)
                    ->first();

                if ($balance) {

                    $balance->increment('qty', $temp->qty);
                } else {

                    StockBalance::create([
                        'barang_id'  => $barang->id,
                        'loc_id'     => $locationId,
                        'qty'        => $temp->qty,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Delete ONLY temp data untuk no_spb ini
            TempUploadModel::whereIn('id', array_keys($request->loc_id))->delete();

            // Check apakah ada no_spb lain yang belum diproses (untuk today)
            $today = Carbon::now();
            $nextNoSpb = TempUploadModel::whereDate('incoming_date', $today)
                ->orderBy('id')
                ->value('no_spb');

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Inventory No SPB {$currentNoSpb} berhasil disimpan",
                'hasNext' => !is_null($nextNoSpb),
                'nextNoSpb' => $nextNoSpb
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        $query = StockInboundDetail::with([
            'barang:id,mid,nama_barang,uom',
            'bin:id,loc_id,kolom,level',
            'bin.location:id,plant,s_loc,gudang,zona,bin',
            'inbound:id,no_spb,incoming_date,supplier'
        ])
            ->select('wrm_stock_inbound_details.*')
            ->join('wrm_stock_inbound', 'wrm_stock_inbound_details.inbound_id', '=', 'wrm_stock_inbound.id')
            ->whereNotIn('status', ['ISSUED', 'RESERVED']);

        // Mapping filters
        $filters = [
            'group' => $request->group,
            'status' => $request->status,
        ];

        foreach ($filters as $field => $values) {
            if ($values) {
                // Prepend table name to field to avoid ambiguity if needed, but 'group' and 'status' are expected in detail table
                $query->whereIn('wrm_stock_inbound_details.' . $field, (array)$values);
            }
        }

        if ($request->jenis_bahan) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->whereIn('nama_barang', (array)$request->jenis_bahan);
            });
        }

        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->whereIn('mid', (array)$request->mid);
            });
        }

        if ($request->date) {
            $query->whereDate('wrm_stock_inbound.incoming_date', $request->date);
        }

        if ($request->supplier) {
            $query->whereIn('wrm_stock_inbound.supplier', (array)$request->supplier);
        }

        if ($request->no_spb) {
            $query->whereIn('wrm_stock_inbound.no_spb', (array)$request->no_spb);
        }

        if ($request->catatan) {
            $query->where('wrm_stock_inbound_details.catatan', 'like', '%' . $request->catatan . '%');
        }

        // Clone query for summary calculation (before sorting)
        $summaryQuery = clone $query;

        // Apply Sorting
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $query->orderBy('wrm_stock_inbound.incoming_date', $sortDir);

        $statusBreakdown = $summaryQuery->select('status', DB::raw('count(*) as count'), DB::raw('sum(qty) as total_qty'))
            ->groupBy('status')
            ->reorder() // Clear any existing order for aggregation
            ->get()
            ->keyBy('status');

        $activeStatuses = ['UNREST', 'QI', 'BLOCKED'];
        $totalPallet = 0;
        $totalQty = 0;

        foreach ($activeStatuses as $st) {
            if (isset($statusBreakdown[$st])) {
                $totalPallet += $statusBreakdown[$st]->count;
                $totalQty += $statusBreakdown[$st]->total_qty;
            }
        }

        $summary = [
            'total_pallet' => $totalPallet,
            'total_qty' => $totalQty,
            'status_breakdown' => $statusBreakdown
        ];

        $data = $query->paginate(25);

        return response()->json([
            'status' => true,
            'message' => 'Data stock inventory berhasil diambil',
            'data' => $data,
            'summary' => $summary
        ]);
    }

    public function getFilter(Request $request)
    {
        // Get all active records once to process in memory (small dataset ~75-200 records)
        // If dataset grows large (>5000), we should switch back to individual DB queries.
        $all = StockInboundDetail::with([
            'barang:id,mid,nama_barang',
            'inbound:id,no_spb,supplier'
        ])
            ->where('status', '!=', 'ISSUED')
            ->get();

        // Helper to filter collection
        $filterCollection = function ($items, $excludeField = null) use ($request) {
            return $items->filter(function ($item) use ($request, $excludeField) {
                $match = true;

                if ($excludeField !== 'group' && $request->group) {
                    $match = $match && in_array($item->group, (array)$request->group);
                }
                if ($excludeField !== 'status' && $request->status) {
                    $match = $match && in_array($item->status, (array)$request->status);
                }
                if ($excludeField !== 'jenis_bahan' && $request->jenis_bahan) {
                    $match = $match && in_array($item->barang->nama_barang, (array)$request->jenis_bahan);
                }
                if ($excludeField !== 'mid' && $request->mid) {
                    $match = $match && in_array($item->barang->mid, (array)$request->mid);
                }
                if ($excludeField !== 'supplier' && $request->supplier) {
                    $match = $match && in_array($item->inbound->supplier, (array)$request->supplier);
                }
                if ($excludeField !== 'no_spb' && $request->no_spb) {
                    $match = $match && in_array($item->inbound->no_spb, (array)$request->no_spb);
                }

                return $match;
            });
        };

        // Extract options for each field
        // For each field, we apply all OTHER filters to see what values are still available
        $groups = $filterCollection($all, 'group')->pluck('group')->unique()->sort()->values();
        $jenisBahan = $filterCollection($all, 'jenis_bahan')->pluck('barang.nama_barang')->unique()->sort()->values();
        $mids = $filterCollection($all, 'mid')->map(function ($item) {
            return [
                'mid' => $item->barang->mid,
                'nama' => $item->barang->nama_barang,
                'text' => "{$item->barang->mid} - {$item->barang->nama_barang}"
            ];
        })->unique('mid')->sortBy('mid')->values();

        $noSpbs = $filterCollection($all, 'no_spb')->pluck('inbound.no_spb')->unique()->sort()->values();
        $suppliers = $filterCollection($all, 'supplier')->pluck('inbound.supplier')->whereNotNull()->unique()->sort()->values();
        $statuses = $filterCollection($all, 'status')->pluck('status')->unique()->sort()->values();

        return response()->json([
            'groups' => $groups,
            'jenis_bahan' => $jenisBahan,
            'mids' => $mids,
            'no_spbs' => $noSpbs,
            'suppliers' => $suppliers,
            'statuses' => $statuses
        ]);
    }

    public function update(StockGulaRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $detail = StockInboundDetail::findOrFail($id);

            $oldQty = $detail->qty;
            $oldBinId = $detail->loc_id;
            $barangId = $detail->barang_id;

            // Get old and new bin to extract location_ids
            $oldBin = MasterBinModel::find($oldBinId);
            $newBin = MasterBinModel::find($request->loc_id);

            if (!$oldBin || !$newBin) {
                throw new \Exception("Bin tidak ditemukan");
            }

            $oldLocationId = $oldBin->loc_id;
            $newLocationId = $newBin->loc_id;

            // Cek apakah lokasi baru sudah terpakai oleh ID lain
            $isOccupied = StockInboundDetail::where('loc_id', $request->loc_id)
                ->where('id', '!=', $id) // Kecuali dirinya sendiri
                ->whereNotIn('status', ['ISSUED', 'RESERVED'])
                ->exists();

            if ($isOccupied) {
                throw new \Exception("Lokasi baru sudah terpakai oleh stok lain.");
            }

            // Cek apakah pallet_id baru bentrok dengan pallet lain di SPB yang sama
            if ($detail->inbound) {
                $isPalletDuplicate = StockInboundDetail::where('inbound_id', $detail->inbound_id)
                    ->where('pallet_id', $request->pallet_id)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($isPalletDuplicate) {
                    throw new \Exception("Pallet ID {$request->pallet_id} sudah digunakan dalam No SPB ini.");
                }
            }

            // Combine selected date with current time
            $incomingDateWithTime = ($request->incoming_date ?? date('Y-m-d')) . ' ' . date('H:i:s');

            $detail->update([
                'pallet_id' => $request->pallet_id,
                'group'     => $request->group,
                'qty'       => $request->qty,
                'status'    => $request->status,
                'loc_id'    => $request->loc_id,
                'catatan'   => $request->catatan,
                'updated_by' => Auth::id(),
            ]);

            // Update Header data (Incoming Date and Supplier)
            if ($detail->inbound) {
                $detail->inbound->update([
                    'incoming_date' => $incomingDateWithTime,
                    'supplier'      => $request->supplier,
                ]);
            }

            $movement = StockMovement::where('ref_type', 'inbound')
                ->where('ref_id', $detail->id)
                ->first();

            if ($movement) {
                $movement->update([
                    'qty'     => $request->qty,
                    'loc_id'  => $newLocationId,
                    'tanggal' => $incomingDateWithTime,
                    'catatan' => $request->catatan
                ]);
            }

            // Sync Balances
            if ($oldLocationId == $newLocationId) {
                $qtyDiff = $request->qty - $oldQty;
                $balance = StockBalance::where('barang_id', $barangId)
                    ->where('loc_id', $oldLocationId)
                    ->first();

                if ($balance) {
                    $balance->increment('qty', $qtyDiff);
                } else {
                    StockBalance::create([
                        'barang_id'  => $barangId,
                        'loc_id'     => $oldLocationId,
                        'qty'        => $request->qty,
                        'created_by' => Auth::id(),
                    ]);
                }
            } else {
                // Location changed: decrease old, increase new
                $oldBalance = StockBalance::where('barang_id', $barangId)
                    ->where('loc_id', $oldLocationId)
                    ->first();
                if ($oldBalance) {
                    $oldBalance->decrement('qty', $oldQty);
                }

                $newBalance = StockBalance::where('barang_id', $barangId)
                    ->where('loc_id', $newLocationId)
                    ->first();
                if ($newBalance) {
                    $newBalance->increment('qty', $request->qty);
                } else {
                    StockBalance::create([
                        'barang_id'  => $barangId,
                        'loc_id'     => $newLocationId,
                        'qty'        => $request->qty,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Stock berhasil diperbarui',
                'data'    => $detail
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $detail = StockInboundDetail::findOrFail($id);

        $inboundId = $detail->inbound_id;

        $detail->delete();

        $remainingDetail = StockInboundDetail::where('inbound_id', $inboundId)->exists();

        if (!$remainingDetail) {
            StockInbound::where('id', $inboundId)->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => 'Data inventory berhasil dihapus',
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048'
        ]);

        DB::beginTransaction();

        try {

            $sheet = IOFactory::load($request->file('file'))->getActiveSheet();
            $rows = $sheet->toArray();

            unset($rows[0]); // hapus header

            $errors = [];
            $mappedRows = [];

            $today = now()->toDateString();
            $prefixTracker = [];

            foreach ($rows as $i => $row) {

                $line = $i + 1;

                $barcode    = trim($row[0] ?? '');
                $mid        = trim($row[1] ?? '');
                $group      = $row[3] ?? null;
                // $status     = strtoupper(trim($row[8] ?? ''));
                // $supplier   = $row[9] ?? null;
                // $pallet     = $row[10] ?? null;
                $catatan    = $row[8] ?? null;
                $expired     = $row[9] ?? null;

                $qty = $row[6] ?? 0;
                if (!is_numeric($qty)) {
                    // Handle common Indonesian/European format (dot = thousand, comma = decimal)
                    // If both exist, or only comma exists, we treat comma as decimal.
                    // If only dot exists and it's followed by 3 digits, it's ambiguous.
                    // However, standardizing to stripping dot and replacing comma with dot is common.
                    // BUT, if the user meant 1.007 as decimal, stripping dot is what caused the bug.

                    // IMPROVED LOGIC: If it's a string like "1.007" and we want 1.007, we shouldn't strip the dot.
                    // Most Excel importers return floats for numeric cells anyway.
                    $qty = str_replace(',', '.', $qty); // convert comma to dot (decimal)

                    // If we still have multiple dots, it might be thousand separators.
                    if (substr_count($qty, '.') > 1) {
                        $qty = str_replace('.', '', substr($qty, 0, strrpos($qty, '.'))) . substr($qty, strrpos($qty, '.'));
                    }
                }
                $qty = (float) $qty;

                if ($mid === '') {
                    $errors[] = "Baris {$line}: MID kosong";
                    continue;
                }

                if ($qty <= 0) {
                    $errors[] = "Baris {$line}: Qty harus lebih dari 0";
                    continue;
                }

                $barcodePrefix = substr($barcode, 0, 10);

                if (strlen($barcode) > 10) {
                    // Take last 2 characters as pallet ID
                    $palletId = substr($barcode, -2);
                } else {
                    // Generate sequential pallet ID if barcode is only the prefix
                    if (!isset($prefixTracker[$barcodePrefix])) {
                        $prefixTracker[$barcodePrefix] = 1;
                    } else {
                        $prefixTracker[$barcodePrefix]++;
                    }
                    $palletId = $prefixTracker[$barcodePrefix];
                }

                // Find Material ID
                $barang = MasterBarangModel::where('mid', $mid)->first();
                if (!$barang) {
                    $errors[] = "Baris {$line}: Material ID {$mid} tidak ditemukan dalam Master Barang";
                    continue;
                }

                // CEK DUPLICATE DI TEMPUPLOAD
                $existCombination = TempUploadModel::where('barcode', $barcode)
                    ->where('mid', $mid)
                    ->exists();

                if ($existCombination) {
                    $errors[] = "Baris {$line}: Kombinasi Barcode {$barcode} dan MID {$mid} sudah ada di antrian upload";
                    continue;
                }

                // CEK DUPLICATE DI INBOUND (barcode, barang_id)
                $existInbound = StockInboundDetail::where('barcode', $barcode)
                    ->where('barang_id', $barang->id)
                    ->exists();

                if ($existInbound) {
                    $errors[] = "Baris {$line}: Barcode {$barcode} dengan MID {$mid} sudah ada di data SOH";
                    continue;
                }

                // Pallet ID is already determined at the beginning of the loop

                $mappedRows[] = [
                    'barcode'     => $barcode,
                    'no_spb'      => $barcodePrefix,
                    'mid'         => $mid,
                    'pallet_id'   => $palletId,
                    'qty'         => $qty,
                    'group'       => $group,
                    'incoming_date' => now(),
                    'expired_date' => $expired ?? null,
                    // 'supplier'    => null,
                    // 'status'      => null,
                    // 'pallet'      => $pallet ?? null,
                    'catatan'     => $catatan ?? null,

                    'created_by'  => Auth::id(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            if ($errors) {
                throw new \Exception(implode("\n", $errors));
            }

            // VALIDASI MID BARANG DI DATABASE
            $uniqueMids = array_unique(array_column($mappedRows, 'mid'));
            $existingMids = MasterBarangModel::whereIn('mid', $uniqueMids)
                ->pluck('mid')
                ->toArray();

            $missingMids = array_diff($uniqueMids, $existingMids);
            if (!empty($missingMids)) {
                throw new \Exception("MID tidak ditemukan di master barang: " . implode(", ", $missingMids));
            }

            TempUploadModel::insert($mappedRows);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Upload stock gula berhasil',
                'total'   => count($mappedRows),
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Upload dibatalkan',
                'errors' => explode("\n", $e->getMessage())
            ], 422);
        }
    }

    public function cancelUpload()
    {
        try {
            $today = Carbon::now();

            TempUploadModel::whereDate('incoming_date', $today)->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Upload berhasil dibatalkan, data dihapus'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function plotLocation(Request $request)
    {
        $locIdInput = $request->loc_id;
        $noSpb = $request->no_spb;

        $today = Carbon::now();

        $data = TempUploadModel::whereDate('incoming_date', $today)
            ->when($noSpb, function ($q) use ($noSpb) {
                $q->where('no_spb', $noSpb);
            })
            ->get();

        $usedBinIds = StockInboundDetail::whereNotIn('status', ['ISSUED', 'RESERVED'])->pluck('loc_id')->toArray();

        $usedDetails = StockInboundDetail::with(['inbound:id,no_spb', 'barang:id,mid', 'bin:id,loc_id,kolom'])
            ->whereNotIn('status', ['ISSUED', 'RESERVED'])
            ->get();
        $dbColumnOwners = [];
        foreach ($usedDetails as $d) {
            if ($d->bin && $d->inbound && $d->barang) {
                $colKey = $d->bin->loc_id . '-' . $d->bin->kolom;
                $ownerKey = $d->inbound->no_spb . '-' . $d->barang->mid;
                $dbColumnOwners[$colKey] = $ownerKey;
            }
        }

        // ATURAN LAMA: HANYA MENCARI DI SATU ZONA
        /*
        $bins = MasterBinModel::with('location')
            ->where('loc_id', $locIdInput)
            ->whereNotIn('id', $usedBinIds)
            ->orderBy('loc_id')
            ->orderBy('kolom')
            ->orderBy('level')
            ->get();
        */

        // ATURAN BARU (Rule 1 & Rule 3): Cari hanya di zona yang dipilih DAN kolom harus murni kosong
        $bins = MasterBinModel::with('location')
            ->whereNotIn('id', $usedBinIds)
            ->where('loc_id', $locIdInput)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('wrm_stock_inbound_details')
                    ->join('wrm_master_bin as b_occ', 'wrm_stock_inbound_details.loc_id', '=', 'b_occ.id')
                    ->whereNotIn('wrm_stock_inbound_details.status', ['ISSUED', 'RESERVED'])
                    ->whereColumn('b_occ.loc_id', 'wrm_master_bin.loc_id')
                    ->whereColumn('b_occ.kolom', 'wrm_master_bin.kolom');
            })
            ->orderBy('kolom')
            ->orderBy('level')
            ->get();

        $availableBinsGrouped = [];
        foreach ($bins as $bin) {
            $colKey = $bin->loc_id . '-' . $bin->kolom;
            if (!isset($availableBinsGrouped[$colKey])) {
                $availableBinsGrouped[$colKey] = [];
            }
            $availableBinsGrouped[$colKey][] = $bin;
        }

        $allocated = $this->allocateBins($data, $availableBinsGrouped, $dbColumnOwners);

        $result = [];

        foreach ($allocated as $alloc) {
            $item = $alloc['item'];
            $bin = $alloc['bin'];
            $location = $bin->location;

            $result[] = [
                'temp_id' => $item->id,
                'no_spb' => $item->no_spb,
                'pallet_id' => $item->pallet_id,
                'loc_id' => $bin->id,
                'plant' => $location->plant,
                's_loc' => $location->s_loc,
                'gudang' => $location->gudang,
                'zona' => $location->zona,
                'bin_id' => $location->bin,
                'bin_coordinate' => "$bin->kolom.$bin->level",
            ];
        }

        return response()->json([
            'data' => $result
        ]);
    }

    private function getOccupiedColumnKeys()
    {
        // Get all bins that are currently occupied
        $occupiedBins = StockInboundDetail::whereNotIn('status', ['ISSUED', 'RESERVED'])
            ->join('wrm_master_bin', 'wrm_stock_inbound_details.loc_id', '=', 'wrm_master_bin.id')
            ->select('wrm_master_bin.loc_id as rack_id', 'wrm_master_bin.kolom')
            ->distinct()
            ->get();

        $keys = [];
        foreach ($occupiedBins as $ob) {
            $keys[] = $ob->rack_id . '-' . $ob->kolom;
        }
        return $keys;
    }
}
