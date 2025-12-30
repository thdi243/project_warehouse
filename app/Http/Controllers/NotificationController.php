<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\NotificationsModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Events\ShowPortalNotification;
use App\Models\Wfg\stock_opname\BarangWfgModel;
use App\Models\Wfg\stock_opname\WfgSopApprovalModel;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        $approvalotification = $this->getSopApprovalNotification($userId);
        $barangBaruNotifications = $this->getBarangBaruNotifications($user);
        $externalNotifications = $this->getExternalNotifications($userId);

        $notifications = NotificationsModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'url' => $n->url,
                    'type' => $n->type,
                    'created_at' => $n->created_at->format('d F Y, H:i'),
                    'is_read' => $n->is_read
                ];
            });

        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $notif = NotificationsModel::where('id', $id)
            // ->where('user_id', Auth::id())
            ->first();

        Log::info("MarkAsRead dipanggil untuk ID: {$id}");

        if (!$notif) {
            Log::warning("Notifikasi ID {$id} TIDAK ditemukan!");

            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        }

        $notif->update([
            'is_read' => true
        ]);

        Log::info("Notifikasi ID {$id} berhasil ditandai read.");

        return response()->json([
            'success' => true,
            'message' => 'Update is read'
        ]);
    }

    private function getSopApprovalNotification($userId)
    {
        $approvals = WfgSopApprovalModel::with(['sop'])
            ->where('approver_id', $userId)
            ->whereIn('status', ['pending'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($approvals as $a) {

            $title = 'Approval SO WFG';
            $message = 'SO' . $a->sop->principal . 'tanggal ' . $a->sop->tgl_opname . ' menunggu persetujuan Anda.';
            $url = route('wfg.stock_opname.report') . '?tanggal=' . $a->sop->tgl_opname .
                '&principal=' . urlencode($a->sop->principal ?? '');

            $existing = NotificationsModel::where('user_id', $userId)
                ->where('url', $url)
                ->first();

            if (!$existing) {
                NotificationsModel::create([
                    'user_id' => $userId,
                    'title' => $title,
                    'message' => $message,
                    'url' => $url,
                    'is_read' => false,
                ]);
            }
        }

        // 4) Ambil semua notifikasi milik user ini
        $notifications = NotificationsModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'url' => $n->url,
                    'created_at' => $n->created_at->diffForHumans(),
                    'is_read' => $n->is_read,
                ];
            });

        return $notifications;
    }

    private function getBarangBaruNotifications($user)
    {
        // Validasi user & role yg boleh menerima notifikasi
        if (!$user || $user->username !== 'RobiForeman') {
            return collect();
        }

        $hasNewBarang = BarangWfgModel::where('is_new', 1)->exists();
        if (!$hasNewBarang) {
            return collect();
        }

        $title = 'Konfirmasi Barang Baru Diperlukan';
        $message = 'Terdapat barang baru yang perlu Anda konfirmasi sebelum melanjutkan laporan SOP.';
        $url = route('wfg.master.barang.index');

        $existing = NotificationsModel::where('user_id', $user->id)
            ->where('url', $url)
            ->first();

        // 2) Jika belum ada → buat notifikasi baru
        if (!$existing) {
            NotificationsModel::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'is_read' => false,
            ]);
        }

        // 3) Ambil notifikasi ini dari database
        $notif = NotificationsModel::where('user_id', $user->id)
            ->where('url', $url)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => 'barang_baru',
                    'title' => $n->title,
                    'message' => $n->message,
                    'url' => $n->url,
                    'created_at' => $n->created_at->diffForHumans(),
                    'is_read' => $n->is_read,
                ];
            });

        return $notif;
    }

    private function getExternalNotifications($userId)
    {
        return NotificationsModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($n) {
                return collect([
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'url' => $n->url,
                    'created_at' => $n->created_at->diffForHumans(),
                    'is_read' => $n->is_read,
                ]);
            });
    }

    public function showNotification(Request $request)
    {
        // Validasi internal key
        if ($request->header('X-Internal-Key') !== env('INTERNAL_PORTAL_KEY')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $supervisors = User::where('jabatan', 'supervisor')->get();

        if ($supervisors->isEmpty()) {
            return response()->json(['error' => 'No supervisor found'], 404);
        }

        foreach ($supervisors as $user) {
            $notif = NotificationsModel::create([
                'user_id' => $user->id,
                'title'   => $request->input('title'),
                'message' => $request->input('message'),
                'url'     => $request->input('url'),
                'is_read' => false,
            ]);

            // Kirim realtime via Reverb (channel per user)
            event(new ShowPortalNotification([
                'id'      => $notif->id,
                'user_id' => $user->id,
                'title'   => $notif->title,
                'message' => $notif->message,
                'url'     => $notif->url,
            ]));
        }

        return response()->json(['status' => 'success']);
    }

    public function destroy($id)
    {
        $notif = NotificationsModel::find($id);

        if (!$notif) {
            return response()->json(['status' => 'error', 'message' => 'Notifikasi tidak ditemukan'], 404);
        }

        $notif->delete();

        return response()->json(['status' => 'success', 'message' => 'Notifikasi berhasil dihapus']);
    }

    public function destroyAll()
    {
        NotificationsModel::truncate();

        return response()->json(['status' => 'success', 'message' => 'Semua notifikasi berhasil dihapus']);
    }
}
