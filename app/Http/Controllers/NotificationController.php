<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificationsModel;
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
        $externalNotifications = $this->getExternalNotifications();

        $notifications = collect()
            ->merge($approvalotification)
            ->merge($barangBaruNotifications)
            ->merge($externalNotifications)
            ->sortByDesc('created_at')
            ->values();

        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $notif = WfgSopApprovalModel::find($id);

        if ($notif) {
            $notif->update(['status' => 'read']);
            return response()->json(['status' => 'error', 'message' => 'Notifikasi tidak ditemukan'], 404);
        }

        $externalNotif = NotificationsModel::find($id);

        if ($externalNotif) {
            $externalNotif->update(['is_read' => true]);
            return response()->json(['success' => true, 'source' => 'external']);
        }


        return response()->json(['success' => true]);
    }

    private function getSopApprovalNotification($userId)
    {
        $approvals = WfgSopApprovalModel::with(['sop'])
            ->where('approver_id', $userId)
            ->whereIn('status', ['pending', 'read'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Format agar bisa dikonsumsi oleh frontend
        $notifications = $approvals->map(function ($a) {
            return collect([
                'id' => $a->id,
                'title' => 'Approval Diperlukan',
                'message' => 'SOP tanggal ' . $a->sop->tgl_opname . ' menunggu persetujuan Anda.',
                'sop_id' => $a->sop->id,
                'url' => route('wfg.stock_opname.report') . '?tanggal=' . $a->sop->tgl_opname .
                    '&principal=' . urlencode($a->sop->principal ?? ''),
                'created_at' => $a->created_at->diffForHumans(),
                'is_read' => $a->status === 'read',
            ]);
        });

        return $notifications;
    }

    private function getBarangBaruNotifications($user)
    {
        // Pastikan user valid dan adalah RobiForeman
        if (!$user || $user->username !== 'RobiForeman') {
            return collect();
        }

        // Cek apakah ada barang baru
        $hasNewBarang = BarangWfgModel::where('is_new', 1)->exists();

        if (!$hasNewBarang) {
            return collect();
        }

        return collect([[
            'id' => 'barang_baru_warning',
            'type' => 'barang_baru',
            'title' => 'Konfirmasi Barang Baru Diperlukan',
            'message' => 'Terdapat barang baru yang perlu Anda konfirmasi sebelum melanjutkan laporan SOP.',
            'url' => route('wfg.master.barang.index'),
            'created_at' => now()->diffForHumans(),
            'is_read' => false,
        ]]);
    }

    private function getExternalNotifications()
    {
        return NotificationsModel::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($n) {
                return collect([
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'url' => $n->url,
                    'created_at' => $n->created_at->diffForHumans(),
                    'is_read' => $n->is_read ?? false,
                ]);
            });
    }

    public function showNotification(Request $request)
    {
        // Validasi internal key
        if ($request->header('X-Internal-Key') !== env('INTERNAL_PORTAL_KEY')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Simpan ke database
        $notif = NotificationsModel::create([
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'url' => $request->input('url'),
        ]);

        // Broadcast ke Reverb biar realtime muncul
        event(new ShowPortalNotification([
            'id' => $notif->id,
            'title' => $notif->title,
            'message' => $notif->message,
            'url' => $notif->url,
        ]));

        return response()->json(['status' => 'success']);
    }
}
