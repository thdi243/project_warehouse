<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\P2HForklfitModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class P2hDashboardController extends Controller
{
    // 1. Total pemeriksaan dan status
    public function summary()
    {
        $today = Carbon::today();

        // Total semua entri
        $total = P2HForklfitModel::count();

        // Total entri hari ini
        $todayCount = P2HForklfitModel::whereDate('tanggal', $today)->count();

        // Completed: nomor_unit + tanggal yang sudah ada 3 shift
        $completed = P2HForklfitModel::select('nomor_unit', 'tanggal')
            ->groupBy('nomor_unit', 'tanggal')
            ->havingRaw('COUNT(DISTINCT shift) = 3')
            ->get()
            ->count();

        // Pending: nomor_unit + tanggal yang belum lengkap 3 shift
        $pending = P2HForklfitModel::select('nomor_unit', 'tanggal')
            ->groupBy('nomor_unit', 'tanggal')
            ->havingRaw('COUNT(DISTINCT shift) < 3')
            ->get()
            ->count();

        return response()->json([
            'total' => $total,
            'today' => $todayCount,
            'completed' => $completed,
            'pending' => $pending,
        ]);
    }

    // 2. Persentase kelayakan rata-rata dan kategori
    public function kelayakanSummary()
    {
        $data = P2HForklfitModel::all();
        $total = $data->count();

        $kategori = [
            'layak' => 0,
            'perlu_perhatian' => 0,
            'tidak_layak' => 0,

        ];

        if ($total > 0) {
            $totalPersen = 0;

            foreach ($data as $item) {
                $result = $item->calculateKelayakan();
                $persen = $result['persentase'];
                $totalPersen += $persen;

                if ($persen >= 95) $kategori['layak']++;
                elseif ($persen >= 85) $kategori['perlu_perhatian']++;
                else $kategori['tidak_layak']++;
            }
        }

        return response()->json($kategori);
    }

    // 3. Komponen paling sering rusak (nilai ≠ OK)
    public function topMasalah()
    {
        $komponen = [
            'cek_baterai',
            'cek_fork',
            'kondisi_body_kebersihan',
            'lampu_kiri',
            'lampu_kanan',
            'lampu_sorot',
            'lampu_sign_depan_kanan',
            'lampu_sign_depan_kiri',
            'kipas_belakang',
            'rantai_lift',
            'sistem_hidrolik',
            'kondisi_axle',
            'sistem_kemudi',
            'panel_display',
            'air_aki',
            'klakson',
            'buzzer_mundur',
            'kaca_spion',
            'kondisi_ban',
            'fungsi_rem'
        ];

        $rusak = [];

        foreach ($komponen as $item) {
            $rusak[$item] = P2HForklfitModel::where($item, '!=', 'OK')->count();
        }

        arsort($rusak); // urutkan dari terbanyak
        $top = array_slice($rusak, 0, 5); // ambil 5 teratas

        return response()->json($top);
    }

    // 4. Operator terbanyak + avg kelayakan
    public function operatorStat()
    {
        $data = P2HForklfitModel::select('operator_name', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('operator_name')
            ->orderByDesc('jumlah')
            ->take(5)
            ->get();

        $hasil = $data->map(function ($item) {
            $records = P2HForklfitModel::where('operator_name', $item->operator_name)->get();
            $avg = $records->avg(fn($r) => $r->calculateKelayakan()['persentase']);

            return [
                'operator' => $item->operator_name,
                'jumlah' => $item->jumlah,
                'rata_kelayakan' => round($avg, 2)
            ];
        });

        return response()->json($hasil);
    }

    // 5. Distribusi shift
    public function shiftDistribusi()
    {
        $data = P2HForklfitModel::select('shift', DB::raw('COUNT(*) as total'))
            ->groupBy('shift')
            ->orderBy('shift')
            ->get();

        return response()->json($data);
    }
}
