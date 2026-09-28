<?php

namespace App\Http\Controllers\Kempu;

use App\Http\Controllers\Controller;
use App\Models\Kempu\MasterKempuModel;
use Illuminate\Http\Request;

class KempuTraceabilityController extends Controller
{
    /**
     * Halaman Dashboard / Monitoring Traceability Kempu
     */
    public function index()
    {
        $locations = [
            MasterKempuModel::LOC_WPM          => 'WPM (Packaging Material)',
            MasterKempuModel::LOC_QC_PM        => 'QC Packaging Material',
            MasterKempuModel::LOC_ENG          => 'Engineering Workshop',
            MasterKempuModel::LOC_PRODUKSI     => 'Produksi',
            MasterKempuModel::LOC_QC_PROSES    => 'QC Proses',
            MasterKempuModel::LOC_WFG          => 'WFG (Finished Goods)',
            MasterKempuModel::LOC_PAS          => 'Warehouse PT PAS',
            MasterKempuModel::LOC_SCRAP        => 'Scrap / Afkir',
        ];

        return view('kempu.traceability.index', compact('locations'));
    }

    /**
     * Mengambil data list kempu untuk datatable/monitoring
     */
    public function getData(Request $request)
    {
        $query = MasterKempuModel::query()->with([
            'main',
            'createdBy:id,username,nama_lengkap',
        ]);

        // Filter Lokasi
        if ($request->filled('location')) {
            $query->whereHas('main', function ($q) use ($request) {
                $q->where('current_location', $request->location);
            });
        }

        // Filter Status Siklus
        if ($request->filled('status_siklus')) {
            if ($request->status_siklus === 'scrap') {
                $query->whereHas('main', function ($q) {
                    $q->where('current_status', MasterKempuModel::STATUS_SCRAPPED);
                });
            } elseif ($request->status_siklus === 'active') {
                $query->whereDoesntHave('main', function ($q) {
                    $q->where('current_status', MasterKempuModel::STATUS_SCRAPPED);
                });
            }
        }

        // Filter Reused Count
        if ($request->filled('reused_status')) {
            $query->whereHas('main', function ($q) use ($request) {
                if ($request->reused_status === 'max') {
                    $q->where('reused_count', '>=', 21);
                } elseif ($request->reused_status === 'warning') {
                    $q->whereBetween('reused_count', [18, 20]);
                } elseif ($request->reused_status === 'normal') {
                    $q->where('reused_count', '<', 18);
                }
            });
        }

        $list = $query->latest('updated_at')->get();

        return response()->json([
            'status' => true,
            'data'   => $list,
        ]);
    }

    /**
     * Mengambil riwayat lengkap tracking per kempu (Timeline)
     */
    public function history($id)
    {
        $kempu = MasterKempuModel::with([
            'trackingHistories.createdBy:id,username,nama_lengkap',
        ])->findOrFail($id);

        return response()->json([
            'status'    => true,
            'kempu'     => $kempu,
            'histories' => $kempu->trackingHistories,
        ]);
    }
}
