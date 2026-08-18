<?php

namespace App\Http\Controllers\Wsp\purchase_requesition;

use App\Http\Controllers\Controller;
use App\Jobs\SendPrApprovalEmail;
use App\Jobs\SendPrRejectedEmail;
use App\Models\NotificationsModel;
use App\Models\User;
use App\Models\UserSignatureModel;
use App\Models\Wsp\BarangModel;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionApprovalModel;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionItemApprovalModel;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionModel;
use App\Models\Wsp\purchase_requesition\WspStockReservations;
use App\Models\Wsp\stock_manage\StockOnHandWspModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WspPurchaseRequesitionController extends Controller
{
    public function index()
    {
        $signature = Auth::user()->signature;
        $departemen = WspPurchaseRequesitionModel::distinct()->pluck('department')->toArray();

        return view('wsp.purchase_requesition.index', compact('signature', 'departemen'));
    }

    public function approvalIndex()
    {
        $signature = Auth::user()->signature;
        return view('wsp.purchase_requesition.approval', compact('signature'));
    }

    public function history()
    {
        return view('wsp.purchase_requesition.history');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pr_date'       => 'required|date',
            'hal'           => 'nullable|string|max:255',
            'no_doc'        => 'nullable|string|max:100',
            'requested_by'  => 'required|string|max:255',
            'department'    => 'required|string|max:255',
            'jenis'         => 'required|string|max:255',
            'detail_jenis'  => 'nullable|string|max:255',
            'no_io'         => 'nullable|string|max:255',
            'ttd'           => 'nullable|string',

            'items'                 => 'required|array|min:1',
            'items.*.mid'           => 'nullable|string',
            'items.*.qty'           => 'required|numeric|min:1',
            'items.*.keterangan'    => 'nullable|string|max:255',
            'items.*.desc'          => 'nullable|string|max:500',
        ], [
            'pr_date.required'      => 'Tanggal PR wajib diisi.',
            'pr_date.date'          => 'Format tanggal PR tidak valid.',
            'requested_by.required' => 'Nama pengaju (User) wajib diisi.',
            'department.required'   => 'Departemen wajib diisi.',
            'jenis.required'        => 'Jenis PR wajib diisi.',
            'ttd.required'          => 'Tanda tangan wajib diisi.',
            'items.required'        => 'Daftar barang/jasa tidak boleh kosong.',
            'items.array'           => 'Format daftar barang/jasa tidak valid.',
            'items.min'             => 'Minimal harus menambahkan 1 barang/jasa.',
            'items.*.qty.required'  => 'Jumlah (Qty) barang/jasa wajib diisi.',
            'items.*.qty.numeric'   => 'Jumlah (Qty) barang/jasa harus berupa angka.',
            'items.*.qty.min'       => 'Jumlah (Qty) barang/jasa minimal 1.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $sessionId = $request->session_id;
            $reservations = collect();

            if ($request->jenis !== 'Jasa') {
                $reservations = WspStockReservations::where('session_id', $sessionId)
                    ->where('status', 'booked')
                    ->where('expired_at', '>', now())
                    ->lockForUpdate()
                    ->get();

                if ($reservations->isEmpty()) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => false,
                        'message' => 'Booking telah expired atau tidak valid. Silakan booking ulang.'
                    ], 422);
                }

                // 2. Validasi qty masih sesuai dengan yang direserve
                foreach ($request->items as $index => $item) {
                    $reservation = $reservations->firstWhere('id', $item['reservation_id']);

                    if (!$reservation) {
                        DB::rollBack();
                        return response()->json([
                            'status'  => false,
                            'message' => "Reservasi untuk MID {$item['mid']} tidak ditemukan"
                        ], 422);
                    }

                    if ($reservation->qty != $item['qty']) {
                        DB::rollBack();
                        return response()->json([
                            'status'  => false,
                            'message' => "Qty tidak sesuai dengan booking untuk MID {$item['mid']}"
                        ], 422);
                    }

                    if ($reservation->mid_barang != $item['mid']) {
                        DB::rollBack();
                        return response()->json([
                            'status'  => false,
                            'message' => "MID tidak sesuai dengan booking"
                        ], 422);
                    }
                }
            }

            // $noDoc = 
            // $prNumber = '50000' . random_int(10000, 99999);

            $no = WspPurchaseRequesitionModel::count() + 1;
            $formattedNo = sprintf('%03d', $no);

            // Ambil department code
            $rawDept = strtolower(trim($request->department));
            $deptMap = [
                'engineering'     => 'ENG',
                'ite'             => 'ENG',
                'warehouse'       => 'WRH',
                'produksi'        => 'PRD',
                'quality_control' => 'QC',
                'qc'              => 'QC',
                'expedisi'        => 'EXP',
                'timbangan'       => 'EXP',
                'hrga'            => 'HRGA',
            ];
            $deptCode = $deptMap[$rawDept] ?? strtoupper(substr($rawDept, 0, 3));

            // Ambil bulan romawi dan tahun
            $romanMonths = [
                1 => 'I',
                2 => 'II',
                3 => 'III',
                4 => 'IV',
                5 => 'V',
                6 => 'VI',
                7 => 'VII',
                8 => 'VIII',
                9 => 'IX',
                10 => 'X',
                11 => 'XI',
                12 => 'XII'
            ];
            $bulanRomawi = $romanMonths[(int)date('m')] ?? 'I';
            $tahun = date('Y');

            // Format no_doc (contoh: 001/ENG/VII/2026)
            $noDoc = $formattedNo . '/' . $deptCode . '/' . $bulanRomawi . '/' . $tahun;

            $pr = WspPurchaseRequesitionModel::create([
                'pr_number'     => null,
                'pr_date'       => $request->pr_date,
                'hal'           => $request->hal,
                'no_doc'        => $noDoc,
                'requested_by'  => $request->requested_by,
                'department'    => strtolower(trim($request->department)),
                'jenis'         => $request->jenis,
                'detail_jenis'  => $request->detail_jenis,
                'no_io'         => $request->no_io,
                'status'        => 'pending',
                'user_id'       => Auth::id() ?? 1,
            ]);

            // 5. Create PR Items & Confirm Reservations
            $createdItems = [];

            foreach ($request->items as $item) {
                $barang = null;
                $reservation = null;

                if ($request->jenis !== 'Jasa') {
                    $barang = BarangModel::where('mid_barang', $item['mid'])->first();

                    if (!$barang) {
                        DB::rollBack();
                        return response()->json([
                            'status'  => false,
                            'message' => "MID {$item['mid']} tidak ditemukan"
                        ], 422);
                    }

                    $reservation = WspStockReservations::find($item['reservation_id']);

                    $stock = StockOnHandWspModel::where('barang_id', $barang->id)
                        ->orderBy('last_update', 'desc')
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        DB::rollBack();
                        return response()->json([
                            'status'  => false,
                            'message' => "Stock tidak ditemukan untuk MID {$item['mid']}"
                        ], 422);
                    }
                }

                $prItem = $pr->items()->create([
                    'pr_id'        => $pr->id,
                    'barang_id'    => $barang ? $barang->id : null,
                    'jenis'        => $item['jenis'] ?? 'pr',
                    'qty'          => $item['qty'],
                    'alasan'       => $item['alasan'] ?? null,
                    'keterangan'   => $item['keterangan'] ?? null,
                    'desc'         => $item['desc'] ?? null,
                ]);

                $createdItems[] = $prItem;

                if ($reservation) {
                    $reservation->update([
                        'pr_id' => $pr->id,
                        'status' => 'confirmed',
                        'confirmed_at' => now(),
                    ]);
                }
            }

            // Confirm any remaining reservation items (jenis = blocked) in this session
            WspStockReservations::where('session_id', $sessionId)
                ->where('status', 'booked')
                ->update([
                    'pr_id' => $pr->id,
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

            $approvals = $this->createApprovalFlow($pr, $request->ttd);

            // Item Approval
            $filteredApprovals = collect($approvals)->whereIn('role', [
                'Manager User',
                'Manager Warehouse'
            ]);

            $bulkInsert = [];

            foreach ($createdItems as $item) {

                foreach ($filteredApprovals as $approval) {

                    $bulkInsert[] = [
                        'pr_item_id'  => $item->id,
                        'approval_id' => $approval->id,
                        'status'      => 'pending',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }

            WspPurchaseRequesitionItemApprovalModel::insert($bulkInsert);

            // Notification: Send only to level 2 initially
            $firstPending = $pr->approval()
                ->where('status', 'pending')
                ->where('level', 2)
                ->first();

            if ($firstPending) {
                $this->sendNotification($pr, $firstPending);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Purchase Requisition berhasil dibuat.',
                'no_doc'  => $noDoc,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status'      => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $data = WspPurchaseRequesitionModel::with([
            'user:id,nama_lengkap',
            'items.barang'
        ])->find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diambil',
            'data' => $data
        ]);
    }

    public function getDataPR(Request $request)
    {
        $query = WspPurchaseRequesitionModel::with(
            'user',
            'items.barang:id,mid_barang,nama_barang,uom',
            'items.approval.approval',
            'approval.approver',
            'items.barang.activeStockLocation.rak:id,plant,s_loc,area_rak,nama_rak,kolom_rak,level_rak,box_rak'
        );

        if ($request->filled('start_date')) {
            $query->whereDate('pr_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('pr_date', '<=', $request->end_date);
        }

        if ($request->filled('departemen') && $request->departemen !== 'all') {
            $query->where('department', $request->departemen);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('requested_by', 'like', "%{$search}%")
                    ->orWhere('no_doc', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis') && $request->jenis !== 'all') {
            $jenis = $request->jenis;
            $query->whereHas('items', function ($q) use ($jenis) {
                $q->where('jenis', $jenis);
            });
        }

        // Calculate summary statistics
        $subquery = (clone $query)->select('wsp_purchase_requesition.id');
        $totalDocs = (clone $query)->count();
        $totalPendingDocs = (clone $query)->where('status', 'pending')->count();
        $totalItemPR = DB::table('wsp_purchase_requesition_items')->whereIn('pr_id', $subquery)->where('jenis', 'pr')->count('id') ?? 0;
        $totalItemReservasi = DB::table('wsp_stock_reservations')->whereIn('pr_id', $subquery)->where('status', 'confirmed')->where('type', 'reservation')->count('id') ?? 0;

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        if ($sortBy === 'status_approved') {
            $query->orderBy(DB::raw("
                CASE 
                    WHEN wsp_purchase_requesition.status = 'rejected' THEN 0
                    ELSE COALESCE(
                        (SELECT MAX(level) 
                         FROM wsp_purchase_requesition_approval 
                         WHERE wsp_purchase_requesition_approval.pr_id = wsp_purchase_requesition.id 
                           AND wsp_purchase_requesition_approval.status = 'approved'
                        ), 
                        1
                    )
                END
            "), $sortDir);
        } else {
            $validSortColumns = ['created_at', 'pr_date', 'no_doc', 'pr_number', 'requested_by', 'department', 'jenis'];
            if (in_array($sortBy, $validSortColumns)) {
                $query->orderBy($sortBy, $sortDir);
            } else {
                $query->orderBy('created_at', 'desc');
            }
        }

        $pr = $query->paginate(15);

        $pr->getCollection()->each(function ($requisition) {
            if ($requisition->items) {
                $requisition->items->each(function ($item) {
                    if ($item->barang) {
                        $rak = $item->barang->activeStockLocation->rak ?? null;
                        $item->barang->setRelation('rak', $rak);
                        $item->barang->unsetRelation('activeStockLocation');
                    }
                });
            }
        });

        return response()->json([
            'success' => true,
            'data'   => $pr,
            'summary' => [
                'total_docs' => $totalDocs,
                'total_pending_docs' => $totalPendingDocs,
                'total_item_pr' => (int)$totalItemPR,
                'total_item_reservasi' => (int)$totalItemReservasi,
            ]
        ], 200);
    }

    public function searchSOH(Request $request)
    {
        $keyword = $request->keyword;
        $currentSessionId = $request->header('X-Session-Id');

        // subquery: last SOH per barang
        $latestSOH = StockOnHandWspModel::select('barang_id')
            ->selectRaw('MAX(last_update) as last_update')
            ->groupBy('barang_id');

        // subquery: total qty_book_soh per barang (dari wsp_stock_reservations yang statusnya 'confirmed' dan tipenya 'reservation' dan PR nya pending/approved)
        $bookedSohSub = DB::table('wsp_stock_reservations as res')
            ->join('wsp_purchase_requesition as pr', 'res.pr_id', '=', 'pr.id')
            ->select('res.mid_barang')
            ->selectRaw('SUM(res.qty) as total_book_soh')
            ->where('res.status', 'confirmed')
            ->where('res.type', 'reservation')
            ->whereIn('pr.status', ['pending', 'approved'])
            ->groupBy('res.mid_barang');

        // START DARI MASTER BARANG
        $query = BarangModel::query()
            ->leftJoinSub($latestSOH, 'latest', function ($join) {
                $join->on('wsp_barang.id', '=', 'latest.barang_id');
            })
            ->leftJoin('wsp_stock_on_hand as soh', function ($join) {
                $join->on('wsp_barang.id', '=', 'soh.barang_id')
                    ->on('soh.last_update', '=', 'latest.last_update');
            })
            ->leftJoinSub($bookedSohSub, 'booked', function ($join) {
                $join->on('wsp_barang.mid_barang', '=', 'booked.mid_barang');
            })
            ->where(function ($q) use ($keyword) {
                $q->where('wsp_barang.mid_barang', 'LIKE', "%{$keyword}%")
                    ->orWhere('wsp_barang.nama_barang', 'LIKE', "%{$keyword}%");
            })
            ->select([
                'soh.id as soh_id',
                'wsp_barang.id as barang_id',
                'wsp_barang.mid_barang',
                'wsp_barang.nama_barang',
                'wsp_barang.uom',
                DB::raw('COALESCE(soh.unrest, 0) as unrest'),
                DB::raw('COALESCE(booked.total_book_soh, 0) as total_book_soh'),
                'soh.last_update',
            ])
            ->orderBy('soh.last_update', 'desc')
            ->limit(10)
            ->get();

        $data = $query->map(function ($item) use ($currentSessionId) {

            // reservasi (TETAP SAMA)
            $activeReservations = WspStockReservations::where('mid_barang', $item->mid_barang)
                ->where('status', 'booked')
                ->where('expired_at', '>', now())
                ->get();

            $reservedByOthers = $activeReservations
                ->where('session_id', '!=', $currentSessionId)
                ->where('type', 'reservation')
                ->sum('qty');

            $prByOthers = $activeReservations
                ->where('session_id', '!=', $currentSessionId)
                ->where('type', 'pr')
                ->sum('qty');

            $reservedByMe = $activeReservations
                ->where('session_id', '=', $currentSessionId)
                ->where('type', 'reservation')
                ->sum('qty');

            $prByMe = $activeReservations
                ->where('session_id', '=', $currentSessionId)
                ->where('type', 'pr')
                ->sum('qty');

            $totalReserved = $reservedByOthers + $reservedByMe;
            $qtySoh = max(0, (int)$item->unrest - (int)$item->total_book_soh);
            $availableQty = max(0, $qtySoh - $totalReserved);

            $isBeingBooked = $activeReservations->where('session_id', '!=', $currentSessionId)->count() > 0;
            // $isAvailable = $item->qty_soh > 0 && !$isBeingBooked;
            $isAvailable = !$isBeingBooked;

            $byUsers = '-';
            if ((int)$item->total_book_soh > 0) {
                $userNames = DB::table('wsp_stock_reservations as res')
                    ->join('wsp_purchase_requesition as pr', 'res.pr_id', '=', 'pr.id')
                    ->join('users as u', 'res.user_id', '=', 'u.id')
                    ->where('res.mid_barang', $item->mid_barang)
                    ->where('res.status', 'confirmed')
                    ->where('res.type', 'reservation')
                    ->whereIn('pr.status', ['pending', 'approved'])
                    ->distinct()
                    ->pluck('u.username')
                    ->toArray();

                if (!empty($userNames)) {
                    $byUsers = implode(', ', $userNames);
                }
            }

            return [
                // ID TETAP ADA (pakai SOH ID kalau ada, fallback barang_id)
                'id' => $item->soh_id ?? $item->barang_id,
                'barang' => [
                    'id' => $item->barang_id,
                    'mid_barang' => $item->mid_barang,
                    'nama_barang' => $item->nama_barang,
                    'uom' => $item->uom,
                ],

                'qty_soh' => $qtySoh,
                'total_book_soh' => (int) $item->total_book_soh,
                'reservasi_oleh' => $byUsers,
                'reserved_by_others' => $reservedByOthers,
                'reserved_by_me' => $reservedByMe,
                'total_reserved' => $totalReserved,
                'available_qty' => $availableQty,
                'is_being_booked' => $isBeingBooked,
                'is_available' => $isAvailable,
                'last_update' => $item->last_update,

                // booking_info TETAP
                'booking_info' => $this->getBookingInfo(
                    $isBeingBooked,
                    $reservedByOthers,
                    $prByOthers,
                    $reservedByMe,
                    $prByMe,
                    $availableQty,
                    $qtySoh,
                    (int) $item->total_book_soh,
                    $byUsers
                ),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5000'
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $items = [];
            $headerSkipped = false;

            foreach ($rows as $row) {
                $mid = trim($row['A'] ?? '');
                $qty = trim($row['B'] ?? '');
                $keterangan = trim($row['C'] ?? '');

                if (empty($mid) && empty($qty)) {
                    continue;
                }

                // Skip header row: if A starts with "mid" or Qty is not numeric
                if (!$headerSkipped) {
                    $headerSkipped = true;
                    if (strtolower($mid) === 'mid' || strtolower($mid) === 'mid barang' || !is_numeric($qty)) {
                        continue;
                    }
                }

                $qty = (int)$qty;
                if ($qty <= 0) {
                    continue;
                }

                $barang = BarangModel::where('mid_barang', $mid)->first();
                if (!$barang) {
                    $items[] = [
                        'mid' => $mid,
                        'nama_barang' => 'Barang tidak ditemukan',
                        'qty' => $qty,
                        'keterangan' => $keterangan,
                        'uom' => 'PCS',
                        'available_qty' => 0,
                        'is_available' => false,
                        'error' => 'MID tidak terdaftar di sistem'
                    ];
                    continue;
                }

                // Hitung stock
                $latestSOH = StockOnHandWspModel::where('barang_id', $barang->id)
                    ->orderBy('last_update', 'desc')
                    ->first();

                $unrest = $latestSOH ? (int)$latestSOH->unrest : 0;

                $totalBookSoh = DB::table('wsp_stock_reservations as res')
                    ->join('wsp_purchase_requesition as pr', 'res.pr_id', '=', 'pr.id')
                    ->where('res.mid_barang', $mid)
                    ->where('res.status', 'confirmed')
                    ->where('res.type', 'reservation')
                    ->whereIn('pr.status', ['pending', 'approved'])
                    ->sum('res.qty') ?? 0;

                $activeReservations = WspStockReservations::where('mid_barang', $mid)
                    ->where('status', 'booked')
                    ->where('expired_at', '>', now())
                    ->get();

                $currentSessionId = $request->header('X-Session-Id');

                $reservedByOthers = $activeReservations
                    ->where('session_id', '!=', $currentSessionId)
                    ->where('type', 'reservation')
                    ->sum('qty');

                $reservedByMe = $activeReservations
                    ->where('session_id', '=', $currentSessionId)
                    ->where('type', 'reservation')
                    ->sum('qty');

                $totalReserved = $reservedByOthers + $reservedByMe;
                $qtySoh = max(0, $unrest - (int)$totalBookSoh);
                $availableQty = max(0, $qtySoh - $totalReserved);

                $isBeingBooked = $activeReservations->where('session_id', '!=', $currentSessionId)->count() > 0;

                $items[] = [
                    'mid' => $mid,
                    'nama_barang' => $barang->nama_barang,
                    'qty' => $qty,
                    'keterangan' => $keterangan,
                    'uom' => $barang->uom,
                    'available_qty' => $availableQty,
                    'is_being_booked' => $isBeingBooked,
                    'is_available' => !$isBeingBooked,
                ];
            }

            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getBookingInfo($isBeingBooked, $reservedByOthers, $prByOthers, $reservedByMe, $prByMe, $available, $soh, $totalBookSoh = 0, $byUsers = '-')
    {
        $info = "";
        if ($reservedByMe > 0 || $prByMe > 0) {
            $parts = [];
            if ($reservedByMe > 0) {
                $parts[] = "booking {$reservedByMe} qty";
            }
            if ($prByMe > 0) {
                $parts[] = "PR {$prByMe} qty";
            }
            $info = "Anda sedang " . implode(" & ", $parts) . " dari total {$soh} qty";
        } else if ($isBeingBooked) {
            $parts = [];
            if ($reservedByOthers > 0) {
                $parts[] = "booking {$reservedByOthers} qty";
            }
            if ($prByOthers > 0) {
                $parts[] = "PR {$prByOthers} qty";
            }
            $info = "Sedang dibooking/PR orang lain (" . implode(" & ", $parts) . ")";
        } else if ($available <= 0) {
            $info = "Stok habis";
        } else {
            $info = "✓ Tersedia {$available} dari {$soh} qty";
        }

        return $info;
    }

    public function destroy($id)
    {
        $data = WspPurchaseRequesitionModel::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $data->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function getRiwayatPR(Request $request)
    {
        $user = Auth::user();
        $dept = strtolower(trim($user->departemen));

        $pr = WspPurchaseRequesitionModel::with('user', 'items.barang', 'items.approval.approval', 'approval.approver')
            ->where(function ($q) use ($user, $dept) {
                $q->where('user_id', $user->id)
                    ->orWhere('department', $dept);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'   => $pr
        ], 200);
    }

    public function printRiwayat($id)
    {
        $pr = WspPurchaseRequesitionModel::with([
            'user',
            'items' => function ($query) {
                $query->where('jenis', 'pr');
            },
            'items.barang',
            'items.latestStock',
            'approval.approver.signature'
        ])
            ->findOrFail($id);

        // dd($pr);

        // Dummy TTD berdasarkan status
        $dummyApproved = public_path('storage/images/ttd/approved_sticker.png');
        $dummyRejected = public_path('storage/images/ttd/rejected_sticker.png');

        // Cek apakah file dummy ada, kalau tidak fallback ke path kosong atau log warning
        $dummyApproved = file_exists($dummyApproved) ? $dummyApproved : null;
        $dummyRejected = file_exists($dummyRejected) ? $dummyRejected : null;

        $approvers = [];

        foreach ($pr->approval->sortBy('level') as $approval) {
            $status = strtolower($approval->status ?? 'pending');

            // Khusus level 1 (requester) — biasanya tidak punya status approved/rejected
            if ((int)$approval->level === 1) {
                $approvers[] = [
                    'nama'      => $pr->requested_by ?? '-',
                    'dept'      => $pr->department ?? '-',
                    'action_at' => $approval->action_at
                        ? \Carbon\Carbon::parse($approval->action_at)->format('d-m-Y')
                        : '-',
                    'ttd'       => $approval->ttd && file_exists(public_path('storage/' . $approval->ttd))
                        ? public_path('storage/' . $approval->ttd)
                        : null, // requester jarang punya TTD, boleh null atau pakai sticker khusus kalau mau
                ];
                continue;
            }

            $user = $approval->approver;
            $isApproved = $status === 'approved';
            $isRejected = $status === 'rejected';

            // Tentukan path TTD
            $ttdPath = null;

            if ($approval->ttd && file_exists(public_path('storage/' . $approval->ttd))) {
                // Prioritas utama: TTD digital yang di-upload saat approve
                $ttdPath = public_path('storage/' . $approval->ttd);
            } elseif ($isApproved && $dummyApproved) {
                // Kalau approved tapi tidak ada TTD digital → pakai sticker approved
                $ttdPath = $dummyApproved;
            } elseif ($isRejected && $dummyRejected) {
                // Kalau rejected → pakai sticker rejected
                $ttdPath = $dummyRejected;
            }
            // Kalau pending atau tidak ada dummy → null (tidak tampil TTD)

            $approvers[] = [
                'nama'      => $user->nama_lengkap ?? '-',
                'dept'      => $user->departemen ?? '-',
                'action_at' => ($isApproved || $isRejected) && $approval->action_at
                    ? \Carbon\Carbon::parse($approval->action_at)->format('d-m-Y')
                    : '-',
                'status'    => ucfirst($status), // optional: kirim status ke view kalau butuh
                'ttd'       => $ttdPath,
            ];
        }

        // Log::info('PR APPROVERS RIWAYAT', $pr[]);

        $pdf = Pdf::loadView('pdf.wsp_pr_riwayat', [
            'pr'        => $pr,
            'approvers' => $approvers
        ])->setPaper('A5', 'landscape');

        $prNumber = $pr->pr_number ?? $pr->department;

        return $pdf->stream("PR-{$prNumber}.pdf");
    }

    public function reserved(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mid' => 'required',
            'qty' => 'required|numeric|min:1',
            'session_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            $mid = $request->mid;
            $requestedQty = $request->qty;
            $sessionId = $request->session_id;

            $type = $request->type ?? 'pr';
            if ($type === 'blocked') {
                $type = 'reservation';
            }

            // Lock query cek existing booked (ini pencegah race condition utama)
            $existingBooked = WspStockReservations::where('mid_barang', $mid)
                ->where('status', 'booked')
                ->where('session_id', '!=', $sessionId)
                ->where('expired_at', '>', now())
                ->lockForUpdate()  // <--- LOCK DI SINI, agar request lain tunggu
                ->exists();  // Atau gunakan ->count() > 0 untuk lebih aman

            if ($existingBooked) {
                return response()->json([
                    'message' => 'Barang sedang dibooking. Silakan tunggu hingga expired.',
                ], 423);
            }

            // Cek barang
            $barang = BarangModel::where('mid_barang', $mid)->first();
            if (!$barang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang tidak ditemukan',
                ], 422);
            }

            // Lock stok juga (untuk cek available stock akurat)
            $stock = StockOnHandWspModel::where('barang_id', $barang->id)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data stok tidak ditemukan',
                ], 422);
            }

            // Hitung total reserved (fresh setelah lock)
            $totalReserved = WspStockReservations::where('mid_barang', $mid)
                ->where('status', 'booked')
                ->where('expired_at', '>', now())
                ->sum('qty');

            // $availableStock = $stock->qty_soh - $totalReserved;

            // if ($availableStock < $requestedQty) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Stok tidak cukup',
            //         'available_stock' => $availableStock,
            //     ], 422);
            // }

            // Buat reservasi baru (hanya satu yang bisa sampai sini)
            $reservation = WspStockReservations::create([
                'mid_barang' => $mid,
                'qty' => $requestedQty,
                'keterangan' => $request->keterangan,
                'type' => $type,
                'session_id' => $sessionId,
                'user_id' => Auth::id(),
                'status' => 'booked',
                'reserved_at' => now(),
                'expired_at' => now()->addMinutes(15),
            ]);

            return response()->json([
                'reservation_id' => $reservation->id,
                'message' => 'Barang berhasil dibooking',
                'expires_in_minutes' => 15,
                'expired_at' => $reservation->expired_at->format('Y-m-d H:i:s'),
            ]);
        }, 5);
    }

    public function release($reservationId)
    {
        $reservation = WspStockReservations::findOrFail($reservationId);

        $reservation->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Booking dibatalkan'
        ]);
    }

    public function releaseSession($sessionId)
    {
        WspStockReservations::where('session_id', $sessionId)
            ->where('status', 'booked')
            ->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Semua booking dibatalkan']);
    }

    public function myReservations(Request $request)
    {
        $request->validate([
            'session_id' => 'required'
        ]);

        $reservations = WspStockReservations::with('barang')
            ->where('user_id', Auth::id())
            ->where('session_id', $request->session_id)
            ->where('status', 'booked')
            ->where('expired_at', '>', now())
            ->orderBy('reserved_at')
            ->get();

        return response()->json([
            'items' => $reservations,
            'expired_at' => optional($reservations->first())->expired_at,
        ]);
    }

    private function createApprovalFlow(WspPurchaseRequesitionModel $pr, $signatureBase64 = null)
    {
        $requestedName = strtolower(trim($pr->requested_by));

        $user = User::where('is_active', true)
            ->where(function ($q) use ($requestedName) {
                $q->whereRaw('LOWER(nama_lengkap) LIKE ?', ["%{$requestedName}%"])
                    ->orWhereRaw('? LIKE CONCAT("%", LOWER(nama_lengkap), "%")', [$requestedName]);
            })
            ->first();

        $userId = $user->id ?? $pr->user_id;

        $deptMap = [
            'ite' => 'engineering',
            'it' => 'engineering',
            'quality control' => 'quality_control',
            'qc' => 'quality_control',
            'production' => 'produksi',
            'timbangan' => 'expedisi',
        ];

        $approvalDept = $deptMap[$pr->department] ?? $pr->department;

        // Supervisor User
        $requesterBagian = '';
        if ($user) {
            $requesterBagian = strtolower(trim($user->bagian));
        } else {
            $reqUser = User::find($userId);
            if ($reqUser) {
                $requesterBagian = strtolower(trim($reqUser->bagian));
            }
        }

        if ($approvalDept === 'engineering') {
            if (str_contains($requesterBagian, 'engineering_utility')) {
                $supervisor = User::where('departemen', 'engineering')
                    ->where('jabatan', 'supervisor')
                    ->where('bagian', 'engineering_utility')
                    ->where('is_active', true)
                    ->first();
            } elseif (str_contains($requesterBagian, 'engineering_production')) {
                $supervisor = User::where('departemen', 'engineering')
                    ->where('jabatan', 'supervisor')
                    ->where('bagian', 'engineering_production')
                    ->where('is_active', true)
                    ->first();
            } elseif (str_contains($requesterBagian, 'engineering_project')) {
                $supervisor = User::where('departemen', 'engineering')
                    ->where('jabatan', 'supervisor')
                    ->where('bagian', 'engineering_project')
                    ->where('is_active', true)
                    ->first();
            } else {
                $supervisor = User::where('departemen', 'engineering')
                    ->where('jabatan', 'supervisor')
                    ->where('bagian', 'engineering')
                    ->where('is_active', true)
                    ->first();
            }
        } elseif ($approvalDept === 'quality_control') {
            if (str_contains($requesterBagian, 'quality_control_rmpm')) {
                $supervisor = User::where('departemen', 'quality_control')
                    ->where('jabatan', 'supervisor')
                    ->where('bagian', 'quality_control_rmpm')
                    ->where('is_active', true)
                    ->first();
            } elseif (str_contains($requesterBagian, 'quality_control_proses')) {
                $supervisor = User::where('departemen', 'quality_control')
                    ->where('jabatan', 'supervisor')
                    ->where('bagian', 'quality_control_proses')
                    ->where('is_active', true)
                    ->first();
            } else {
                $supervisor = User::where('departemen', 'quality_control')
                    ->where('jabatan', 'supervisor')
                    ->where('bagian', 'quality_control')
                    ->where('is_active', true)
                    ->first();
            }
        } else {
            $supervisor = User::where('departemen', $approvalDept)
                ->where('jabatan', 'supervisor')
                ->where('bagian', $approvalDept)
                ->where('is_active', true)
                ->first();
        }

        if (!$supervisor) {
            $detail = '';
            if ($approvalDept === 'engineering') {
                if (str_contains($requesterBagian, 'utility')) {
                    $detail = ' (Utility)';
                } elseif (str_contains($requesterBagian, 'produksi') || str_contains($requesterBagian, 'production')) {
                    $detail = ' (Produksi)';
                }
            } elseif ($approvalDept === 'quality_control') {
                if (str_contains($requesterBagian, 'rmpm')) {
                    $detail = ' (RMPM)';
                } elseif (str_contains($requesterBagian, 'proses')) {
                    $detail = ' (Proses)';
                }
            }
            return response()->json([
                'success' => false,
                'message' => 'Supervisor untuk departemen ' . $approvalDept . $detail . ' tidak ditemukan',
            ], 422);
        }

        // Dept Head User
        $deptHead = User::where('departemen', $approvalDept)
            ->where('jabatan', 'dept_head')
            ->where('is_active', true)
            ->first();

        if (!$deptHead) {
            return response()->json([
                'success' => false,
                'message' => 'Dept Head untuk departemen ' . $approvalDept . ' tidak ditemukan',
            ], 422);
        }

        // Dept Head Warehouse
        $warehouseHead = User::where('departemen', 'warehouse')
            ->where('jabatan', 'dept_head')
            ->where('is_active', true)
            ->first();

        if (!$warehouseHead) {
            return response()->json([
                'success' => false,
                'message' => 'Dept Head warehouse tidak ditemukan',
            ], 422);
        }

        // Admin WSP / User with role 'level_5_pr'
        $foreman = User::role('level_5_pr')
            ->where('is_active', true)
            ->first();

        if (!$foreman) {
            $foreman = User::where('jabatan', 'foreman')
                ->where('bagian', 'warehouse_sparepart')
                ->where('is_active', true)
                ->first();
        }

        if (!$foreman) {
            return response()->json([
                'success' => false,
                'message' => 'Approver Level 5 (Admin WSP / Role level_5_pr) tidak ditemukan',
            ], 422);
        }

        $ttdPath = null;

        if ($signatureBase64) {
            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $signatureBase64);
            $image = base64_decode($base64);

            if ($image !== false) {
                $fileName = "pengaju_{$pr->id}_" . Str::slug($pr->requested_by) . '.png';
                $ttdPath = 'uploads/signatures_approval_pr/' . $fileName;
                Storage::disk('public')->put($ttdPath, $image);
            }
        }

        $approvals = [
            [
                'level'       => 1,
                'role'        => 'user',
                'approver_id' => $userId,
                'status'      => 'approved',
                'action_at'   => now(),
                'action_by'   => $userId,
                'ttd'         => $ttdPath,
            ],
            [
                'level'       => 2,
                'role'        => 'Supervisor User',
                'approver_id' => $supervisor->id,
                'status'      => 'pending',
            ],
            [
                'level'       => 3,
                'role'        => 'Manager User',
                'approver_id' => $deptHead->id,
                'status'      => 'pending',
            ],
            [
                'level'       => 4,
                'role'        => 'Manager Warehouse',
                'approver_id' => $warehouseHead->id,
                'status'      => 'pending',
            ],
            [
                'level'       => 5,
                'role'        => 'Admin Wsp',
                'approver_id' => $foreman->id,
                'status'      => 'pending',
            ],
        ];

        $createdApprovals = [];

        foreach ($approvals as $approvalData) {

            $approval = WspPurchaseRequesitionApprovalModel::create([
                'pr_id'       => $pr->id,
                'level'       => $approvalData['level'],
                'role'        => $approvalData['role'],
                'approver_id' => $approvalData['approver_id'],
                'status'      => $approvalData['status'],
                'action_at'   => $approvalData['action_at'] ?? null,
                'action_by'   => $approvalData['action_by'] ?? null,
                'ttd'         => $approvalData['ttd'] ?? null,
            ]);

            $createdApprovals[] = $approval;
        }

        return $createdApprovals;
    }

    public function getDataApproval($id)
    {
        $pr = WspPurchaseRequesitionModel::with(['items.barang.stock', 'approval.approver'])
            ->findOrFail($id);

        $currentUserId = Auth::id();

        // 1. Apakah user ini masih bisa approve?
        $canApprove = $pr->approval()
            ->where('approver_id', $currentUserId)
            ->where('status', 'pending')
            ->exists();

        // 2. Apakah user ini sudah pernah action (approve/reject)?
        $userApproval = $pr->approval()
            ->where('approver_id', $currentUserId)
            ->whereIn('status', ['approved', 'rejected'])
            ->with('approver') // pastikan approver di-load
            ->first(); // ambil satu record (harusnya cuma satu)

        return response()->json([
            'status' => true,
            'data' => array_merge($pr->toArray(), [
                'can_approve'     => $canApprove,
                'user_has_acted'  => $userApproval ? true : false,
                'user_action'     => $userApproval ? $userApproval->toArray() : null,
                // 'user_action' ini berisi: status, action_at, catatan, approver{nama_lengkap, ...}
            ])
        ]);
    }

    public function action(Request $request, $id)
    {
        $request->validate([
            'status'  => 'required|in:approved,rejected',
            'comment' => $request->status === 'rejected' ? 'required|string|max:500' : 'nullable|string|max:500',
            'ttd'     => 'required_if:status,approved|nullable',
            'no_pr'   => 'nullable',
            'items'   => 'nullable|array',
            'items.*' => 'exists:wsp_purchase_requesition_items,id',
            'update_signature' => 'nullable'
        ]);

        try {
            DB::beginTransaction();

            $res = $this->processApproval(
                $id,
                Auth::id(),
                $request->status,
                $request->comment,
                $request->ttd,
                $request->no_pr,
                $request->items,
                $request->boolean('use_stored_signature'),
                $request->boolean('update_signature')
            );

            if (!$res['status']) {
                DB::rollBack();
                return response()->json($res, 422);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Aksi berhasil diproses'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    private function processApproval($prId, $userId, $status, $comment = null, $ttd = null, $noPr = null, $itemIds = [], $useStoredSignature = false, $updateSignature = false)
    {
        $pr = WspPurchaseRequesitionModel::with('approval')->findOrFail($prId);

        $approval = $pr->approval()
            ->where('approver_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            return [
                'status' => false,
                'message' => 'Anda tidak memiliki hak untuk melakukan approval PR ini atau sudah diproses.'
            ];
        }

        // Hapus notifikasi untuk user ini terkait PR ini (Role spesifik)
        NotificationsModel::where('user_id', $userId)
            ->where('notifiable_id', $prId)
            ->where('notifiable_type', WspPurchaseRequesitionModel::class)
            ->where('message', 'like', '%' . $approval->role . '%')
            ->delete();

        $currentLevel = $approval->level;

        // Check sequence
        $previousPending = $pr->approval()
            ->where('level', '<', $currentLevel)
            ->where('status', 'pending')
            ->exists();

        if ($previousPending) {
            return [
                'status' => false,
                'message' => 'Harap tunggu urutan approval sebelumnya.'
            ];
        }

        // Update items if provided
        if (!empty($itemIds)) {
            // First, update all 'blocked' (reservasi) items automatically since they are not checkable on the frontend
            WspPurchaseRequesitionItemApprovalModel::where('approval_id', $approval->id)
                ->whereHas('prItem', function ($q) {
                    $q->where('jenis', 'blocked');
                })
                ->update([
                    'status' => $status,
                    'catatan' => $comment
                ]);

            // Then, update the selected 'pr' items
            WspPurchaseRequesitionItemApprovalModel::where('approval_id', $approval->id)
                ->whereIn('pr_item_id', $itemIds)
                ->update([
                    'status' => $status,
                    'catatan' => $comment
                ]);
        } else {
            // Default: update all items for this approval
            WspPurchaseRequesitionItemApprovalModel::where('approval_id', $approval->id)
                ->update([
                    'status' => $status,
                    'catatan' => $comment
                ]);
        }

        // Simpan TTD jika status approved
        if ($status === 'approved') {
            if ($useStoredSignature) {
                $userSig = UserSignatureModel::where('user_id', $userId)->first();
                if ($userSig) {
                    $approval->ttd = $userSig->signature;
                }
            } elseif ($ttd) {
                $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $ttd);
                $image = base64_decode($base64);

                if ($image !== false) {
                    $fileName = "approval_{$approval->id}_" . Auth::user()->username . '_' . time() . '.png';
                    $path = 'uploads/signatures_approval_pr/' . $fileName;
                    Storage::disk('public')->put($path, $image);
                    $approval->ttd = $path;

                    // Simpan atau update signature ke user_signature jika dipilih/belum punya
                    if ($updateSignature || !UserSignatureModel::where('user_id', $userId)->exists()) {
                        UserSignatureModel::updateOrCreate(
                            ['user_id' => $userId],
                            ['signature' => $path]
                        );
                    }
                }
            }
        }

        $approval->update([
            'status'  => $status,
            'catatan' => $comment,
            'action_at' => now(),
            'action_by' => $userId,
        ]);

        if ($noPr) {
            $pr->update(['pr_number' => $noPr]);
        }

        // Refresh status PR
        if ($status === 'rejected') {
            $pr->update(['status' => 'rejected']);
            if ($currentLevel == 5) {
                $pr->items()->update(['status' => false]);
            }

            // Send notification and email to the creator of the PR
            $this->sendRejectionNotification($pr, $approval);
        } else if ($status === 'approved') {
            if ($currentLevel == 4) {
                $pr->update(['status' => 'approved']);
            } else if ($currentLevel == 5) {
                $pr->update(['status' => 'finished']);

                // Set all 'blocked' (reservasi) items to true because they are confirmed automatically
                $pr->items()->where('jenis', 'blocked')->update(['status' => true]);

                if (!empty($itemIds)) {
                    $pr->items()->where('jenis', 'pr')->whereIn('id', $itemIds)->update(['status' => true]);
                    $pr->items()->where('jenis', 'pr')->whereNotIn('id', $itemIds)->update(['status' => false]);
                } else {
                    $pr->items()->where('jenis', 'pr')->update(['status' => false]);
                }
            }

            // Notify next level
            $nextApproval = $pr->approval()
                ->where('status', 'pending')
                ->where('level', $currentLevel + 1)
                ->first();

            if ($nextApproval) {
                $this->sendNotification($pr, $nextApproval);
            }
        }

        return ['status' => true];
    }

    public function getPendingApprovals()
    {
        $userId = Auth::id();

        // Ambil PR yang user terlibat sebagai approver pending
        $prs = WspPurchaseRequesitionModel::whereHas('approval', function ($q) use ($userId) {
            $q->where('approver_id', $userId)
                ->where('status', 'pending');
        })
            ->with(['approval', 'items.barang', 'user'])
            ->latest()
            ->get();

        // Filter: hanya yang level sebelumnya sudah approved
        $filtered = $prs->filter(function ($pr) use ($userId) {
            $myApproval = $pr->approval->where('approver_id', $userId)->where('status', 'pending')->first();
            if (!$myApproval) return false;

            $currentLevel = $myApproval->level;
            $previousPending = $pr->approval->where('level', '<', $currentLevel)->where('status', '!=', 'approved');

            return $previousPending->isEmpty();
        });

        return response()->json([
            'success' => true,
            'data' => array_values($filtered->toArray())
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:wsp_purchase_requesition,id',
            'status' => 'required|in:approved,rejected',
            'comment' => $request->status === 'rejected' ? 'required|string|max:500' : 'nullable|string|max:500',
            'ttd' => 'nullable|string', // bulk approval might share one signature
            'no_pr' => 'nullable|string',
            'update_signature' => 'nullable'
        ]);

        $userId = Auth::id();
        $successCount = 0;
        $errors = [];

        foreach ($request->ids as $id) {
            try {
                DB::beginTransaction();
                $res = $this->processApproval(
                    $id,
                    $userId,
                    $request->status,
                    $request->comment,
                    $request->ttd,
                    $request->no_pr,
                    $request->items ?? [],
                    $request->boolean('use_stored_signature'),
                    $request->boolean('update_signature')
                );
                if ($res['status']) {
                    DB::commit();
                    $successCount++;
                } else {
                    DB::rollBack();
                    $errors[] = "PR ID $id: " . $res['message'];
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "PR ID $id: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil memproses $successCount data." . (count($errors) > 0 ? " Terjadi " . count($errors) . " kesalahan." : ""),
            'errors' => $errors
        ]);
    }

    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'qty' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $item = \App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionItemsModel::findOrFail($itemId);

            // Check if PR is still pending
            $pr = WspPurchaseRequesitionModel::findOrFail($item->pr_id);
            if ($pr->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'PR sudah diproses dan tidak dapat diedit.'
                ], 422);
            }

            // Check if current user is allowed to approve (Supervisor/Manager Level 2/3)
            $currentUserId = Auth::id();
            $allowed = $pr->approval()
                ->where('approver_id', $currentUserId)
                ->whereIn('level', [2, 3])
                ->where('status', 'pending')
                ->exists();

            if (!$allowed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki wewenang untuk mengedit item PR ini.'
                ], 403);
            }

            // Update item
            $item->update([
                'qty' => $request->qty,
                'keterangan' => $request->keterangan,
            ]);

            // If it's a blocked (reservation) item, update the associated reservation as well
            if ($item->jenis === 'blocked') {
                $barang = BarangModel::find($item->barang_id);
                if ($barang) {
                    $reservation = WspStockReservations::where('pr_id', $item->pr_id)
                        ->where('mid_barang', $barang->mid_barang)
                        ->where('status', 'confirmed') // since it is confirmed when stored
                        ->first();
                    if ($reservation) {
                        $reservation->update([
                            'qty' => $request->qty,
                            'keterangan' => $request->keterangan,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function sendNotification($pr, $approval)
    {

        if (!$approval->approver_id) return;

        $user = User::find($approval->approver_id);
        if (!$user || !$user->is_active) return;

        $url = "/purchase-requesition/approval?level=" . $approval->level;

        NotificationsModel::create([
            'user_id' => $approval->approver_id,
            'notifiable_type' => WspPurchaseRequesitionModel::class,
            'notifiable_id' => $pr->id,
            'title'   => "Approval PR - {$pr->no_doc}",
            'message' => "Anda approve sebagai {$approval->role}. PR dari {$pr->requested_by} dept. {$pr->department} menunggu persetujuan Anda",
            'url'     => $url,
            'is_read' => false,
        ]);

        if ($user->email) {
            SendPrApprovalEmail::dispatch(
                $pr->id,
                $approval->id,
                $user->email
            )->afterCommit();
        }

        return;
    }

    private function sendRejectionNotification($pr, $approval)
    {
        if (!$pr->user_id) return;

        $user = User::find($pr->user_id);
        if (!$user || !$user->is_active) return;

        $url = "/purchase-requesition/history";

        // Ambil nama approver yang menolak
        $approverName = $approval->approver ? $approval->approver->nama_lengkap : 'Approver';
        $reason = $approval->catatan ? " dengan alasan: \"{$approval->catatan}\"" : "";

        NotificationsModel::create([
            'user_id' => $pr->user_id,
            'notifiable_type' => WspPurchaseRequesitionModel::class,
            'notifiable_id' => $pr->id,
            'title'   => "PR Ditolak - {$pr->no_doc}",
            'message' => "Permintaan PR Anda dengan No. Doc {$pr->no_doc} ditolak oleh {$approverName} ({$approval->role}){$reason}.",
            'url'     => $url,
            'is_read' => false,
        ]);

        if ($user->email) {
            SendPrRejectedEmail::dispatch(
                $pr->id,
                $approval->id,
                $user->email
            )->afterCommit();
        }

        return;
    }
}
