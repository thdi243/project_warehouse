<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $notifications = collect()
            ->merge($approvalotification)
            ->merge($barangBaruNotifications)
            ->sortByDesc('created_at')
            ->values();

        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $notif = WfgSopApprovalModel::find($id);
        if (!$notif) {
            return response()->json(['status' => 'error', 'message' => 'Notifikasi tidak ditemukan'], 404);
        }

        $notif->update(['status' => 'read']);

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
}
