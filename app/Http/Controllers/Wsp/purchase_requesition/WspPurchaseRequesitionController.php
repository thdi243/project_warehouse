<?php

namespace App\Http\Controllers\Wsp\purchase_requesition;

use App\Http\Controllers\Controller;
use App\Jobs\SendPrApprovalEmail;
use App\Models\NotificationsModel;
use App\Models\User;
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

class WspPurchaseRequesitionController extends Controller
{
    public function index()
    {
        return view('wsp.purchase_requesition.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pr_date'       => 'required|date',
            'hal'           => 'nullable|string|max:255',
            'no_doc'        => 'nullable|string|max:100',
            'requested_by'  => 'required|string|max:255',
            'department'    => 'required|string|max:255',
            'jenis'         => 'required|string|max:255',
            'detail_jenis'  => 'nullable|string|max:255',
            'no_io'         => 'nullable|string|max:255',
            'ttd'           => 'required|string',

            'items'                 => 'required|array|min:1',
            'items.*.mid'           => 'required|string',
            'items.*.qty'           => 'required|numeric|min:1',
            'items.*.keterangan'    => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $sessionId = $request->session_id;
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

            // $noDoc = 
            // $prNumber = '50000' . random_int(10000, 99999);

            $no = WspPurchaseRequesitionModel::count() + 1;

            // Ambil department
            $dept = strtoupper(trim($request->department));

            // Ambil bulan dan tahun
            $bulan = date('m');
            $tahun = date('Y');

            // Format no_doc
            $noDoc = $no . '/' . $dept . '/' . $bulan . '/' . $tahun;

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
                $barang = BarangModel::where('mid_barang', $item['mid'])->first();

                if (!$barang) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => false,
                        'message' => "MID {$item['mid']} tidak ditemukan"
                    ], 422);
                }

                $prItem = $pr->items()->create([
                    'pr_id'      => $pr->id,
                    'barang_id'  => $barang->id,
                    'qty'        => $item['qty'],
                    'keterangan' => $item['keterangan'] ?? null,
                ]);

                $createdItems[] = $prItem;

                $stock = StockOnHandWspModel::where('barang_id', $barang->id)
                    ->orderByDesc('last_update')
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => false,
                        'message' => "Stock tidak ditemukan untuk MID {$item['mid']}"
                    ], 422);
                }

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

                // UPDATE LANGSUNG (tanpa hitung2 lagi)
                $stock->update([
                    'unrest'      => 0,
                    'qual_insp'   => 0,
                    'blocked'     => 0,
                    'transf'      => 0,
                    'qty_soh'     => 0,
                    'last_update' => now(),
                ]);

                $reservation = WspStockReservations::find($item['reservation_id']);

                $reservation->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
            }

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

            // Notification
            $pendingApprovals = $pr->approval()
                ->where('status', 'pending')
                ->get();

            foreach ($pendingApprovals as $approval) {
                if (!$approval->approver_id) continue;

                $user = User::find($approval->approver_id);

                if ($user && $user->jabatan == 'foreman') {
                    $url = "/purchase-requesition/index";
                } else {
                    $url = "/app/approval-pr/{$pr->id}";
                }

                NotificationsModel::create([
                    'user_id' => $approval->approver_id,
                    'title'   => 'Approval PR',
                    'message' => "PR dari {$pr->requested_by} dept. {$pr->department} menunggu persetujuan Anda",
                    'url'     => $url,
                    'is_read' => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'      => true,
                'message' => 'Purchase Requisition berhasil dibuat.',
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

    public function getDataPR()
    {
        $pr = WspPurchaseRequesitionModel::with('user', 'items.barang', 'items.approval', 'approval')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'   => $pr
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

        // START DARI MASTER BARANG
        $query = BarangModel::query()
            ->leftJoinSub($latestSOH, 'latest', function ($join) {
                $join->on('wsp_barang.id', '=', 'latest.barang_id');
            })
            ->leftJoin('wsp_stock_on_hand as soh', function ($join) {
                $join->on('wsp_barang.id', '=', 'soh.barang_id')
                    ->on('soh.last_update', '=', 'latest.last_update');
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
                DB::raw('COALESCE(soh.qty_soh, 0) as qty_soh'),
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
                ->sum('qty');

            $reservedByMe = $activeReservations
                ->where('session_id', '=', $currentSessionId)
                ->sum('qty');

            $totalReserved = $reservedByOthers + $reservedByMe;
            $availableQty = max(0, $item->qty_soh - $totalReserved);
            $isBeingBooked = $activeReservations->count() > 0;
            // $isAvailable = $item->qty_soh > 0 && !$isBeingBooked;
            $isAvailable = !$isBeingBooked;

            return [
                // ID TETAP ADA (pakai SOH ID kalau ada, fallback barang_id)
                'id' => $item->soh_id ?? $item->barang_id,
                'barang' => [
                    'id' => $item->barang_id,
                    'mid_barang' => $item->mid_barang,
                    'nama_barang' => $item->nama_barang,
                    'uom' => $item->uom,
                ],

                'qty_soh' => (int) $item->qty_soh,
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
                    $reservedByMe,
                    $availableQty,
                    $item->qty_soh
                ),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    private function getBookingInfo($isBeingBooked, $reservedByOthers, $reservedByMe, $available, $soh)
    {
        if ($reservedByMe > 0) {
            return "Anda sedang booking {$reservedByMe} qty dari total {$soh}";
        } else if ($isBeingBooked) {
            $info = "Sedang dibooking orang lain ({$reservedByOthers} qty)";
            return $info;
        }

        if ($available <= 0) {
            return "Stok habis";
        }

        return "✓ Tersedia {$available} dari {$soh} qty";
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
        $user = $request->user(); // pasti ada karena auth middleware

        $pr = WspPurchaseRequesitionModel::with('items.barang')
            ->where('user_id', $user->id)
            ->latest()
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
            'items.barang',
            'items.latestStock',
            'approval.approver.signature'
        ])->findOrFail($id);

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

        $prNumber = $pr->pr_number ?? 'Waiting';

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

            // Lock query cek existing booked (ini pencegah race condition utama)
            $existingBooked = WspStockReservations::where('mid_barang', $mid)
                ->where('status', 'booked')
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

        $user = User::whereRaw('LOWER(nama_lengkap) LIKE ?', ["%{$requestedName}%"])
            ->orWhereRaw('? LIKE CONCAT("%", LOWER(nama_lengkap), "%")', [$requestedName])
            ->first();

        $userId = $user->id ?? $pr->user_id;

        $deptMap = [
            'ite' => 'engineering',
        ];

        $approvalDept = $deptMap[$pr->department] ?? $pr->department;

        // Dept Head User
        $deptHead = User::where('departemen', $approvalDept)
            ->where('jabatan', 'dept_head')
            ->first();

        if (!$deptHead) {
            return response()->json([
                'success' => false,
                'message' => 'Dept Head untuk departemen' . $approvalDept . 'tidak ditemukan',
            ], 422);
        }

        // Dept Head Warehouse
        $warehouseHead = User::where('departemen', 'warehouse')
            ->where('jabatan', 'dept_head')
            ->first();

        if (!$warehouseHead) {
            return response()->json([
                'success' => false,
                'message' => 'Dept Head warehouse tidak ditemukan',
            ], 422);
        }

        // Foreman WSP
        $foreman = User::where('jabatan', 'foreman')
            ->where('bagian', 'warehouse_sparepart')
            ->first();

        if (!$foreman) {
            return response()->json([
                'success' => false,
                'message' => 'Foreman WSP tidak ditemukan',
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
                'role'        => 'Manager User',
                'approver_id' => $deptHead->id,
                'status'      => 'pending',
            ],
            [
                'level'       => 3,
                'role'        => 'Manager Warehouse',
                'approver_id' => $warehouseHead->id,
                'status'      => 'pending',
            ],
            [
                'level'       => 4,
                'role'        => 'Foreman Wsp',
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

            if ($approval->status === 'pending') {
                $approver = User::find($approval->approver_id);

                if ($approver && $approver->email) {
                    SendPrApprovalEmail::dispatch(
                        $pr,
                        $approval,
                        $approver->email
                    );
                }
            }
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
            'comment' => 'nullable|string|max:500',
            'ttd'     => 'required_if:status,approved|nullable',
            'no_pr'   => 'nullable',
            'items'   => 'nullable|array',
            'items.*' => 'exists:wsp_purchase_requesition_items,id'
        ]);

        try {
            DB::beginTransaction();

            $pr = WspPurchaseRequesitionModel::with('approval')->findOrFail($id);
            $currentUserId = Auth::id();

            $approval = $pr->approval()
                ->where('approver_id', $currentUserId)
                ->where('status', 'pending')
                ->first();

            if (!$approval) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak untuk melakukan approval PR ini.'
                ], 403);
            }

            // update item yang dipilih
            if (!empty($request->items)) {

                WspPurchaseRequesitionItemApprovalModel::where('approval_id', $approval->id)
                    ->whereIn('pr_item_id', $request->items)
                    ->update([
                        'status' => $request->status,
                        'catatan' => $request->comment
                    ]);
            }

            $approval = $pr->approval()
                ->where('approver_id', $currentUserId)
                ->where('status', 'pending')
                ->firstOrFail();

            $currentLevel = $approval->level;
            $previousApprovals = $pr->approval()
                ->where('level', '<', $currentLevel)
                ->get();

            $pendingPrevious = $previousApprovals->where('status', 'pending');
            if ($pendingPrevious->isNotEmpty()) {
                $pendingRole = $pendingPrevious->first()->role;

                return response()->json([
                    'success' => false,
                    'message' => 'Belum di approve oleh ' . $pendingRole . '. Harap tunggu urutan.',
                ], 422);
            }

            // Jika ada level sebelumnya yang sudah rejected → langsung tolak PR
            $rejectedPrevious = $previousApprovals->where('status', 'rejected');
            if ($rejectedPrevious->isNotEmpty()) {
                $pr->update(['status' => 'rejected']);

                return response()->json([
                    'success' => false,
                    'message' => 'PR sudah ditolak oleh approver sebelumnya.',
                ], 422);
            }

            // Simpan TTD jika status approved
            if ($request->status === 'approved' && $request->filled('ttd')) {
                $base64 = $request->ttd;
                $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
                $image = base64_decode($base64);

                if ($image === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format TTD tidak valid.',
                    ], 422);
                }

                $fileName = "approval_{$approval->id}_" . Auth::user()->username . '_' . time() . '.png';
                $path = 'uploads/signatures_approval_pr/' . $fileName;

                Storage::disk('public')->put($path, $image);

                $approval->ttd = $path;
            }

            $approval->update([
                'status'  => $request->status,
                'catatan' => $request->comment,
                'action_at' => now(),
                'action_by' => Auth::id(),
            ]);

            if ($request->filled('no_pr')) {
                $pr->update([
                    'pr_number' => $request->no_pr
                ]);
            }

            // Refresh approvals
            $currentLevel = $approval->level;

            // jika ada yang reject
            $rejected = $pr->approval()->where('status', 'rejected')->exists();
            if ($rejected) {
                $pr->update(['status' => 'rejected']);
            }

            // jika level 3 approve → PR approved
            elseif ($request->status === 'approved' && $currentLevel == 3) {
                $pr->update(['status' => 'approved']);
            }

            // jika level 4 approve → PR finished
            elseif ($request->status === 'approved' && $currentLevel == 4) {
                $pr->update(['status' => 'finished']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Aksi berhasil diproses',
                'data' => $pr->fresh(['items.barang'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
