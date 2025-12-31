<?php

namespace App\Http\Controllers\Wsp\purchase_requesition;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Jobs\SendPrApprovalEmail;
use App\Models\NotificationsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Wsp\stock_manage\StockOnHandWspModel;
use App\Models\Wsp\purchase_requesition\WspStockReservations;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionModel;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionApprovalModel;

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

            $prNumber = '50000' . random_int(10000, 99999);

            $pr = WspPurchaseRequesitionModel::create([
                'pr_number'     => $prNumber,
                'pr_date'       => $request->pr_date,
                'hal'           => $request->hal,
                'no_doc'        => $request->no_doc,
                'requested_by'  => $request->requested_by,
                'department'    => strtolower(trim($request->department)),
                'jenis'         => $request->jenis,
                'detail_jenis'  => $request->detail_jenis,
                'no_io'         => $request->no_io,
                'status'        => 'pending',
                'user_id'       => Auth::id() ?? 1,
            ]);

            // 5. Create PR Items & Confirm Reservations
            foreach ($request->items as $item) {
                $barang = BarangModel::where('mid_barang', $item['mid'])->first();

                if (!$barang) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => false,
                        'message' => "MID {$item['mid']} tidak ditemukan"
                    ], 422);
                }

                $pr->items()->create([
                    'pr_id'      => $pr->id,
                    'barang_id'  => $barang->id,
                    'qty'        => $item['qty'],
                    'keterangan' => $item['keterangan'] ?? null,
                ]);

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

                $currentReserved = WspStockReservations::where('mid_barang', $item['mid'])
                    ->where('status', 'booked')
                    ->where('expired_at', '>', now())
                    ->where('id', '!=', $item['reservation_id'])
                    ->sum('qty');

                $availableStock = $stock->unrest - $currentReserved;

                $stock->unrest -= $item['qty'];
                if ($stock->unrest < 0) {
                    $stock->unrest = 0;
                }

                $stock->qty_soh = $stock->unrest + $stock->qual_insp + $stock->blocked + $stock->transf;
                $stock->last_update = now();
                $stock->save();

                $reservation = WspStockReservations::find($item['reservation_id']);

                $reservation->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
            }

            $this->createApprovalFlow($pr, $request->ttd);

            $pendingApprovals = $pr->approval()
                ->where('status', 'pending')
                ->get();

            foreach ($pendingApprovals as $approval) {
                if (!$approval->approver_id) continue;

                NotificationsModel::create([
                    'user_id' => $approval->approver_id,
                    'title'   => 'Approval PR Baru',
                    'message' => "PR {$pr->pr_number} dari {$pr->requested_by} dept. {$pr->department} menunggu persetujuan Anda",
                    'url'     => "/app/approval-pr/{$pr->id}",
                    'is_read' => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'      => true,
                'message' => 'Purchase Requisition berhasil dibuat.',
                'data'    => $pr->load('items.barang')
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
        $pr = WspPurchaseRequesitionModel::with('user', 'items.barang')
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
        $currentUserId = Auth::id();
        $currentSessionId = $request->header('X-Session-Id'); // kirim dari frontend
        $today = now()->toDateString();

        // Cek apakah ada data hari ini
        $todayDataExists = StockOnHandWspModel::whereDate('last_update', $today)->exists();

        // Tentukan query dasar
        $query = StockOnHandWspModel::with(['barang:id,mid_barang,nama_barang,uom'])
            ->whereHas('barang', function ($q) use ($keyword) {
                $q->where('mid_barang', 'LIKE', "%{$keyword}%")
                    ->orWhere('nama_barang', 'LIKE', "%{$keyword}%");
            });

        if ($todayDataExists) {
            $query->whereDate('last_update', $today);
        } else {
            $latestDate = StockOnHandWspModel::max('last_update');
            $query->where('last_update', $latestDate);
        }

        $data = $query
            ->orderBy('last_update', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) use ($currentSessionId) {
                $mid = $item->barang->mid_barang;

                // Cek apakah ada ACTIVE reservation dari user LAIN
                $activeReservations = WspStockReservations::where('mid_barang', $mid)
                    ->where('status', 'booked')
                    ->where('expired_at', '>', now())
                    ->get();

                // Pisahkan antara reservasi user ini vs user lain
                $otherUsersReservations = $activeReservations->where('session_id', '!=', $currentSessionId);
                $myReservations = $activeReservations->where('session_id', '=', $currentSessionId);

                $reservedByOthers = $otherUsersReservations->sum('qty');
                $reservedByMe = $myReservations->sum('qty');
                $totalReserved = $reservedByOthers + $reservedByMe;

                $hasStock = $item->qty_soh > 0;
                $hasActiveReservation = $activeReservations->count() > 0;

                $isBeingBooked = $hasActiveReservation;

                // Informasi saja (untuk badge / tooltip)
                $availableQty = $item->qty_soh - $totalReserved;
                if ($availableQty < 0) $availableQty = 0;

                // LOGIKA FINAL
                $isAvailable = $hasStock && !$hasActiveReservation;

                return [
                    'id' => $item->id,
                    'barang' => $item->barang,
                    'qty_soh' => $item->qty_soh,
                    'reserved_by_others' => $reservedByOthers,
                    'reserved_by_me' => $reservedByMe,
                    'total_reserved' => $totalReserved,
                    'available_qty' => $availableQty,
                    'is_being_booked' => $isBeingBooked,
                    'is_available' => $isAvailable,
                    'last_update' => $item->last_update,

                    // Info untuk ditampilkan
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

        Log::info('PR APPROVERS RIWAYAT', $approvers);

        $pdf = Pdf::loadView('pdf.wsp_pr_riwayat', [
            'pr'        => $pr,
            'approvers' => $approvers
        ])->setPaper('A5', 'landscape');

        return $pdf->stream("PR-{$pr->pr_number}.pdf");
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

        DB::beginTransaction();
        try {
            $mid = $request->mid;
            $requestedQty = $request->qty;
            $sessionId = $request->session_id;

            $alreadyBooked = WspStockReservations::where('mid_barang', $mid)
                ->where('status', 'booked')
                ->where('expired_at', '>', now())
                ->exists();

            if ($alreadyBooked) {
                return response()->json([
                    'message' => 'Barang sedang dibooking. Silakan tunggu hingga expired.',
                ], 423);
            }

            // Cek SOH
            $barang = BarangModel::where('mid_barang', $mid)->first();
            if (!$barang) {
                DB::rollBack();
                return response()->json(['message' => 'Barang tidak ditemukan'], 404);
            }

            $stock = StockOnHandWspModel::where('barang_id', $barang->id)->first();
            if (!$stock) {
                DB::rollBack();
                return response()->json(['message' => 'Data stok tidak ditemukan'], 404);
            }

            $totalReserved = WspStockReservations::where('mid_barang', $mid)
                ->where('status', 'booked')
                ->where('expired_at', '>', now())
                ->sum('qty');

            $availableStock = $stock->qty_soh - $totalReserved;

            // if ($availableStock < $requestedQty) {
            //     DB::rollBack();
            //     return response()->json([
            //         'message' => "Stok tidak cukup. Tersedia: {$availableStock}, diminta: {$requestedQty}",
            //     ], 400);
            // }

            // Buat reservasi (expired dalam 30 menit)
            $reservation = WspStockReservations::create([
                'mid_barang' => $mid,
                'qty' => $requestedQty,
                'session_id' => $sessionId,
                'user_id' => Auth::id(),
                'status' => 'booked',
                'reserved_at' => now(),
                'expired_at' => now()->addMinutes(15),
            ]);

            DB::commit();

            return response()->json([
                'reservation_id' => $reservation->id,
                'message' => 'Barang berhasil dibooking',
                'expires_in_minutes' => 15,
                'expired_at' => $reservation->expired_at->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reserve error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
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
            'it'  => 'engineering',
            'eng' => 'engineering',
        ];

        $approvalDept = $deptMap[$pr->department] ?? $pr->department;

        // Dept Head User
        $deptHead = User::where('departemen', $approvalDept)
            ->where('jabatan', 'dept_head')
            ->first();

        if (!$deptHead) {
            throw new \Exception("Dept Head untuk departemen {$approvalDept} tidak ditemukan");
        }

        // Dept Head Warehouse
        $warehouseHead = User::where('departemen', 'warehouse')
            ->where('jabatan', 'dept_head')
            ->first();

        if (!$warehouseHead) {
            throw new \Exception("Dept Head warehouse tidak ditemukan");
        }

        // Foreman WSP
        $foreman = User::where('jabatan', 'foreman')
            ->where('bagian', 'warehouse_sparepart')
            ->first();

        if (!$foreman) {
            throw new \Exception("Foreman WSP tidak ditemukan");
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
                'role'        => 'manager_user',
                'approver_id' => $deptHead->id,
                'status'      => 'pending',
            ],
            [
                'level'       => 3,
                'role'        => 'manager_warehouse',
                'approver_id' => $warehouseHead->id,
                'status'      => 'pending',
            ],
            [
                'level'       => 4,
                'role'        => 'foreman_wsp',
                'approver_id' => $foreman->id,
                'status'      => 'pending',
            ],
        ];

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
    }

    public function getDataApproval($id)
    {
        $pr = WspPurchaseRequesitionModel::with(['items.barang', 'approval.approver'])
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
        ]);

        try {
            DB::beginTransaction();

            $pr = WspPurchaseRequesitionModel::with('approval')->findOrFail($id);
            $currentUserId = Auth::id();

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
                throw new \Exception("Belum di-approve oleh {$pendingRole}. Harap tunggu urutan.");
            }

            // Jika ada level sebelumnya yang sudah rejected → langsung tolak PR
            $rejectedPrevious = $previousApprovals->where('status', 'rejected');
            if ($rejectedPrevious->isNotEmpty()) {
                $pr->update(['status' => 'rejected']);
                throw new \Exception("PR sudah ditolak oleh approver sebelumnya.");
            }

            // Simpan TTD jika status approved
            if ($request->status === 'approved' && $request->filled('ttd')) {
                $base64 = $request->ttd;
                $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
                $image = base64_decode($base64);

                if ($image === false) {
                    throw new \Exception('Format TTD tidak valid');
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

            // Refresh approvals
            $allApprovals = $pr->approval()->get();

            // Cek status keseluruhan PR
            $pending = $allApprovals->where('status', 'pending');
            $rejected = $allApprovals->where('status', 'rejected');

            if ($rejected->isNotEmpty()) {
                $pr->update(['status' => 'rejected']);
            } elseif ($pending->isEmpty()) {
                $pr->update(['status' => 'approved']);
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
