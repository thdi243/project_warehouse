<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\P2h\ForkliftModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\P2h\P2HForklfitModel;
use App\Models\P2h\PalletMoverModel;
use App\Models\P2h\P2HPalletMoverModel;
use App\Models\P2h\PalletAssignmentModel;
use App\Models\P2h\UserForkliftAssignmentModel;

class P2hDashboardController extends Controller
{
    // 1. Total pemeriksaan dan status
    public function summary()
    {
        $forkliftAktif = ForkliftModel::where('status', 'active')->count();
        $palletMoverAktif = PalletMoverModel::where('status', 'active')->count();

        $operatorForkliftAktif = UserForkliftAssignmentModel::where('is_active', 1)
            ->distinct('user_id')
            ->count('user_id');

        $operatorPalletAktif = PalletAssignmentModel::where('is_active', 1)
            ->distinct('user_id')
            ->count('user_id');

        return response()->json([
            'forklift_aktif' => $forkliftAktif,
            'pallet_mover_aktif' => $palletMoverAktif,
            'operator_forklift_aktif' => $operatorForkliftAktif,
            'operator_pallet_mover_aktif' => $operatorPalletAktif,
        ]);
    }

    // 2. Persentase kelayakan rata-rata dan kategori
    public function kelayakanSummary(Request $request)
    {
        // Ambil bulan dari request (format: YYYY-MM)
        $bulan = $request->get('bulan') ?? Carbon::now()->format('Y-m');

        $query = P2HForklfitModel::query();

        if ($bulan) {
            $tanggal = Carbon::createFromFormat('Y-m', $bulan);

            $query->whereYear('tanggal', $tanggal->year)
                ->whereMonth('tanggal', $tanggal->month);
        }

        $data = $query->get();
        $total = $data->count();

        $kategori = [
            'layak' => 0,
            'perlu_perhatian' => 0,
            'tidak_layak' => 0,
        ];

        if ($total > 0) {
            foreach ($data as $item) {
                $result = $item->calculateKelayakan();
                $persen = $result['persentase'];

                if ($persen >= 95) {
                    $kategori['layak']++;
                } elseif ($persen >= 85) {
                    $kategori['perlu_perhatian']++;
                } else {
                    $kategori['tidak_layak']++;
                }
            }
        }

        return response()->json([
            'bulan' => $bulan,
            'total_data' => $total,
            'kategori' => $kategori
        ]);
    }

    // 3. Komponen paling sering rusak (nilai ≠ OK)
    public function topMasalah(Request $request)
    {
        $bulan = $request->get('bulan') ?? Carbon::now()->format('Y-m');

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

        $queryBase = P2HForklfitModel::query();

        // 🔹 Filter bulan (jika ada)
        if ($bulan) {
            $tanggal = Carbon::createFromFormat('Y-m', $bulan);
            $queryBase->whereYear('tanggal', $tanggal->year)
                ->whereMonth('tanggal', $tanggal->month);
        }

        $result = [];

        foreach ($komponen as $item) {
            $result[$item] = (clone $queryBase)
                ->where($item, 0)
                ->count();
        }

        return response()->json([
            'bulan' => $bulan,
            'data' => $result
        ]);
    }

    // 4. Operator terbanyak + avg kelayakan
    public function operatorStat(Request $request)
    {
        $bulan = $request->get('bulan') ?? Carbon::now()->format('Y-m');

        $query = P2HForklfitModel::query();

        if ($bulan) {
            $tanggal = Carbon::createFromFormat('Y-m', $bulan);
            $query->whereYear('tanggal', $tanggal->year)
                ->whereMonth('tanggal', $tanggal->month);
        }

        // Ambil top operator berdasarkan jumlah pemeriksaan
        $data = $query
            ->select('operator_name', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('operator_name')
            ->orderByDesc('jumlah')
            ->limit(15)
            ->get();

        // Hitung rata-rata kelayakan per operator
        $hasil = $data->map(function ($item) use ($bulan) {

            $recordsQuery = P2HForklfitModel::where('operator_name', $item->operator_name);

            if ($bulan) {
                $tanggal = Carbon::createFromFormat('Y-m', $bulan);
                $recordsQuery->whereYear('tanggal', $tanggal->year)
                    ->whereMonth('tanggal', $tanggal->month);
            }

            $records = $recordsQuery->get();

            $avg = $records->avg(function ($r) {
                return $r->calculateKelayakan()['persentase'];
            });

            return [
                'operator'        => $item->operator_name,
                'jumlah'          => $item->jumlah,
                'rata_kelayakan'  => round($avg ?? 0, 2),
            ];
        });

        return response()->json([
            'bulan' => $bulan,
            'data'  => $hasil
        ]);
    }

    public function unitForkliftMasalah(Request $request)
    {
        $bulan = $request->get('bulan') ?? Carbon::now()->format('Y-m');

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
            'fungsi_rem',
        ];

        $query = P2HForklfitModel::query();

        // filter bulan (YYYY-MM)
        if ($bulan) {
            $tanggal = Carbon::createFromFormat('Y-m', $bulan);
            $query->whereYear('tanggal', $tanggal->year)
                ->whereMonth('tanggal', $tanggal->month);
        }

        $data = $query->get();

        $hasil = [];

        foreach ($data as $item) {
            $jumlahMasalah = 0;

            foreach ($komponen as $field) {
                // asumsi: 1 = OK, 0 = bermasalah
                if (isset($item->$field) && $item->$field == 0) {
                    $jumlahMasalah++;
                }
            }

            if ($jumlahMasalah > 0) {
                if (!isset($hasil[$item->nomor_unit])) {
                    $hasil[$item->nomor_unit] = 0;
                }

                $hasil[$item->nomor_unit] += $jumlahMasalah;
            }
        }

        // urutkan dari yang paling bermasalah
        arsort($hasil);

        // ambil top 10
        $top = collect($hasil)->take(10)->map(function ($val, $key) {
            return [
                'nomor_unit' => $key,
                'jumlah_masalah' => $val
            ];
        })->values();

        return response()->json([
            'bulan' => $bulan,
            'total_unit' => $top->count(),
            'data' => $top
        ]);
    }

    // 5. Distribusi shift
    // public function shiftDistribusi()
    // {
    //     $data = P2HForklfitModel::select('shift', DB::raw('COUNT(*) as total'))
    //         ->groupBy('shift')
    //         ->orderBy('shift')
    //         ->get();

    //     return response()->json($data);
    // }

    // Pallet Mover
    public function kelayakanSummaryPalletMover(Request $request)
    {
        $bulan = $request->get('bulan') ?? now()->format('Y-m');

        $query = P2HPalletMoverModel::query();

        if ($bulan) {
            $tanggal = Carbon::createFromFormat('Y-m', $bulan);
            $query->whereYear('tanggal', $tanggal->year)
                ->whereMonth('tanggal', $tanggal->month);
        }

        $data = $query->get();

        $kategori = [
            'layak' => 0,
            'perlu_perhatian' => 0,
            'tidak_layak' => 0,
        ];

        foreach ($data as $item) {
            $status = $item->calculateKelayakan()['status'];

            match ($status) {
                'Layak' => $kategori['layak']++,
                'Perlu Perhatian' => $kategori['perlu_perhatian']++,
                default => $kategori['tidak_layak']++,
            };
        }

        return response()->json([
            'bulan' => $bulan,
            'total_data' => $data->count(),
            'kategori' => $kategori
        ]);
    }

    public function topMasalahPalletMover(Request $request)
    {
        $bulan = $request->get('bulan') ?? Carbon::now()->format('Y-m');

        $komponen = [
            'check_air_accu',
            'check_battery',
            'check_body_unit',
            'check_klakson',
            'check_roda',
            'check_sistem_kemudi',
            'check_kebersihan_unit',
            'check_kunci_pm',
            'check_hydraulic',
        ];

        $queryBase = P2HPalletMoverModel::query();

        // 🔹 Filter bulan
        if ($bulan) {
            $tanggal = Carbon::createFromFormat('Y-m', $bulan);
            $queryBase->whereYear('tanggal', $tanggal->year)
                ->whereMonth('tanggal', $tanggal->month);
        }

        $result = [];

        foreach ($komponen as $item) {
            $result[$item] = (clone $queryBase)
                ->where($item, 0) // 0 = bermasalah
                ->count();
        }

        return response()->json([
            'bulan' => $bulan,
            'data'  => $result
        ]);
    }

    public function operatorStatPalletMover(Request $request)
    {
        $bulan = $request->get('bulan') ?? Carbon::now()->format('Y-m');

        $query = P2HPalletMoverModel::query();

        if ($bulan) {
            $tanggal = Carbon::createFromFormat('Y-m', $bulan);
            $query->whereYear('tanggal', $tanggal->year)
                ->whereMonth('tanggal', $tanggal->month);
        }

        // Ambil top operator berdasarkan jumlah pemeriksaan
        $data = $query
            ->select('operator_name', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('operator_name')
            ->orderByDesc('jumlah')
            ->limit(15)
            ->get();

        // Hitung rata-rata kelayakan per operator
        $hasil = $data->map(function ($item) use ($bulan) {

            $recordsQuery = P2HPalletMoverModel::where('operator_name', $item->operator_name);

            if ($bulan) {
                $tanggal = Carbon::createFromFormat('Y-m', $bulan);
                $recordsQuery->whereYear('tanggal', $tanggal->year)
                    ->whereMonth('tanggal', $tanggal->month);
            }

            $records = $recordsQuery->get();

            $avg = $records->avg(function ($r) {
                return $r->calculateKelayakan()['persentase'];
            });

            return [
                'operator'        => $item->operator_name,
                'jumlah'          => $item->jumlah,
                'rata_kelayakan'  => round($avg ?? 0, 2),
            ];
        });

        return response()->json([
            'bulan' => $bulan,
            'data'  => $hasil
        ]);
    }

    public function unitPalletMoverMasalah(Request $request)
    {
        $bulan = $request->get('bulan') ?? Carbon::now()->format('Y-m');

        $komponen = [
            'check_air_accu',
            'check_battery',
            'check_body_unit',
            'check_klakson',
            'check_roda',
            'check_sistem_kemudi',
            'check_kebersihan_unit',
            'check_kunci_pm',
            'check_hydraulic',
        ];

        $query = P2HPalletMoverModel::query();

        // 🔹 Filter bulan (YYYY-MM)
        if ($bulan) {
            $tanggal = Carbon::createFromFormat('Y-m', $bulan);
            $query->whereYear('tanggal', $tanggal->year)
                ->whereMonth('tanggal', $tanggal->month);
        }

        $data = $query->get();

        $hasil = [];

        foreach ($data as $item) {
            $jumlahMasalah = 0;

            foreach ($komponen as $field) {
                if (isset($item->$field) && $item->$field == 0) {
                    $jumlahMasalah++;
                }
            }

            if ($jumlahMasalah > 0) {
                $hasil[$item->nomor_unit] = ($hasil[$item->nomor_unit] ?? 0) + $jumlahMasalah;
            }
        }

        // 🔥 Urutkan dari yang paling bermasalah
        arsort($hasil);

        // 🔹 Ambil top 10 unit
        $top = collect($hasil)->take(10)->map(function ($val, $key) {
            return [
                'nomor_unit' => $key,
                'jumlah_masalah' => $val
            ];
        })->values();

        return response()->json([
            'bulan' => $bulan,
            'total_unit' => $top->count(),
            'data' => $top
        ]);
    }

    public function getDailyStatusTable(Request $request)
    {
        // 1. Get Month (YYYY-MM)
        $bulan = $request->get('bulan') ?? Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::parse($bulan)->startOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        // 2. Generate all dates for the month
        $dates = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dates[] = $startOfMonth->copy()->day($d)->format('Y-m-d');
        }

        // 3. Fetch all active units
        $forklifts = ForkliftModel::where('status', 'active')->orderBy('nomor_unit')->get(['nomor_unit']);
        $palletMovers = PalletMoverModel::where('status', 'active')->orderBy('nomor_unit')->get(['nomor_unit']);

        // 4. Fetch P2H records for the entire month
        $forkliftRecords = P2HForklfitModel::whereYear('tanggal', $startOfMonth->year)
            ->whereMonth('tanggal', $startOfMonth->month)
            ->select('nomor_unit', 'tanggal')
            ->distinct()
            ->get()
            ->groupBy('nomor_unit');

        $palletRecords = P2HPalletMoverModel::whereYear('tanggal', $startOfMonth->year)
            ->whereMonth('tanggal', $startOfMonth->month)
            ->select('nomor_unit', 'tanggal')
            ->distinct()
            ->get()
            ->groupBy('nomor_unit');

        // 5. Build status matrix
        $forkliftData = $forklifts->map(function ($unit) use ($dates, $forkliftRecords) {
            $unitStatus = [];
            $records = $forkliftRecords->get($unit->nomor_unit, collect());

            foreach ($dates as $date) {
                $unitStatus[$date] = $records->contains('tanggal', $date);
            }

            return [
                'nomor_unit' => $unit->nomor_unit,
                'status'     => $unitStatus
            ];
        });

        $palletData = $palletMovers->map(function ($unit) use ($dates, $palletRecords) {
            $unitStatus = [];
            $records = $palletRecords->get($unit->nomor_unit, collect());

            foreach ($dates as $date) {
                $unitStatus[$date] = $records->contains('tanggal', $date);
            }

            return [
                'nomor_unit' => $unit->nomor_unit,
                'status'     => $unitStatus
            ];
        });

        return response()->json([
            'bulan'         => $bulan,
            'days_in_month' => $daysInMonth,
            'dates'         => $dates,
            'forklifts'     => $forkliftData,
            'pallet_movers' => $palletData
        ]);
    }
}
