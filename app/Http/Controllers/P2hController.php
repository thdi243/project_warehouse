<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\P2HForklfitModel;
use App\Models\P2HPalletMoverModel;
use App\Models\PalletAssignmentModel;
use Illuminate\Support\Facades\Session;
use App\Models\UserForkliftAssignmentModel;

class P2hController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // if (Session::get('jabatan') === 'operator') {
        $userId = Session::get('user_id');

        $assignments = UserForkliftAssignmentModel::with('forklift')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        $forklifts = $assignments->filter(fn($a) => $a->forklift)->map(function ($a) {
            return [
                'nomor_unit' => $a->forklift->nomor_unit,
                'departemen' => $a->forklift->departemen,
                'is_primary' => $a->is_primary,
            ];
        });

        $palletAssignments = PalletAssignmentModel::with('palletMover')
            ->where('user_id', $userId)
            ->get();

        $pallets = $palletAssignments->filter(fn($a) => $a->palletMover)->map(function ($a) {
            return [
                'nomor_unit' => $a->palletMover->nomor_unit,
                'departemen' => $a->palletMover->departemen,
                'is_primary' => $a->is_primary,
                'tipe' => 'Pallet Mover'
            ];
        });


        // Ambil departemen & nomor unit pertama untuk default tampilan
        $departemen = $forklifts->first()['departemen'] ?? '';
        $nomorUnit = $forklifts->first()['nomor_unit'] ?? '';

        $departemenpallet = $pallets->first()['departemen'] ?? '';
        $nomorUnitpallet = $pallets->first()['nomor_unit'] ?? '';
        return view('p2h.index', compact('forklifts', 'pallets', 'departemen', 'nomorUnit', 'departemenpallet', 'nomorUnitpallet'));
        // }
        // return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'tanggal' => 'required|date',
            'jenis_p2h' => 'nullable|string|max:50',
            'nomor_unit' => 'required|string|max:50',
            'dept'  => 'required|string|max:50',
            'shift' => 'required',
            'operator_name' => 'required|string|max:100',
            'catatan' => 'nullable|string',
            'jam_operasional' => 'required',

            // Validasi status komponen
            'cek_baterai' => 'required|in:1,0',
            'cek_fork' => 'required|in:1,0',
            'kondisi_body_kebersihan' => 'required|in:1,0',
            'lampu_kiri' => 'required|in:1,0',
            'lampu_kanan' => 'required|in:1,0',
            'lampu_sorot' => 'required|in:1,0',
            'lampu_sign_depan_kanan' => 'required|in:1,0',
            'lampu_sign_depan_kiri' => 'required|in:1,0',
            'kipas_belakang' => 'required|in:1,0',
            'rantai_lift' => 'required|in:1,0',
            'sistem_hidrolik' => 'required|in:1,0',
            'kondisi_axle' => 'required|in:1,0',
            'sistem_kemudi' => 'required|in:1,0',
            'panel_display' => 'required|in:1,0',
            'air_aki' => 'required|in:1,0',
            'klakson' => 'required|in:1,0',
            'buzzer_mundur' => 'required|in:1,0',
            'kaca_spion' => 'required|in:1,0',
            'kondisi_ban' => 'required|in:1,0',
            'fungsi_rem' => 'required|in:1,0',
        ]);

        // Cek duplikasi data
        $exists = P2HForklfitModel::whereDate('tanggal', $request->tanggal)
            ->where('shift', $request->shift)
            ->where('nomor_unit', $request->nomor_unit)
            ->where('jenis_p2h', $request->jenis_p2h)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data untuk tanggal, shift, jenis P2H, dan nomor unit ini sudah ada.'
            ], 422);
        }

        // Validasi jam operasional
        $lastRecord = P2HForklfitModel::where('nomor_unit', $request->nomor_unit)
            ->orderByDesc('created_at')
            ->first();

        if ($lastRecord && $request->jam_operasional < $lastRecord->jam_operasional) {
            return response()->json([
                'success' => false,
                'message' => 'Hours Meter unit ini tidak boleh lebih kecil dari data sebelumnya (' . $lastRecord->jam_operasional . '). Cek kembali!'
            ], 422);
        }

        // Perhitungan persentase
        $group20 = ['cek_baterai', 'cek_fork', 'kondisi_body_kebersihan', 'lampu_kiri', 'lampu_kanan', 'lampu_sorot', 'lampu_sign_depan_kanan', 'lampu_sign_depan_kiri', 'kipas_belakang'];
        $group50 = ['rantai_lift', 'sistem_hidrolik', 'kondisi_axle', 'sistem_kemudi', 'panel_display', 'jam_operasional', 'air_aki'];
        $group30 = ['klakson', 'buzzer_mundur', 'kaca_spion', 'kondisi_ban', 'fungsi_rem'];

        $totalPoin = 0;
        foreach ($group20 as $field) {
            $totalPoin += $request->$field ? 20 : 0;
        }
        foreach ($group50 as $field) {
            $totalPoin += $request->$field ? 50 : 0;
        }
        foreach ($group30 as $field) {
            $totalPoin += $request->$field ? 30 : 0;
        }

        $maxPoin = (count($group20) * 20) + (count($group50) * 50) + (count($group30) * 30); // Total bobot ideal
        $persentase = round(($totalPoin / $maxPoin) * 100, 2);

        // Deteksi rusak berat
        $criticalNok = ['cek_baterai', 'kipas_belakang', 'rantai_lift', 'sistem_hidrolik', 'kondisi_axle', 'sistem_kemudi', 'panel_display', 'air_aki', 'fungsi_rem'];
        $isRusakBerat = collect($criticalNok)->contains(fn($f) => $request->$f == 0);
        $statusUnit = $isRusakBerat ? 'Rusak Berat' : 'Normal';
        if ($isRusakBerat) {
            $persentase = 50.00; // Tetapkan nilai default jika rusak berat
        }
        try {
            $batch = P2HForklfitModel::create([
                'tanggal' => $request->tanggal,
                'jenis_p2h' => $request->jenis_p2h,
                'nomor_unit' => $request->nomor_unit,
                'dept' => $request->dept,
                'shift' => $request->shift,
                'operator_name' => $request->operator_name,
                'catatan' => $request->catatan,
                'cek_baterai' => $request->cek_baterai,
                'cek_fork' => $request->cek_fork,
                'kondisi_body_kebersihan' => $request->kondisi_body_kebersihan,
                'lampu_kiri' => $request->lampu_kiri,
                'lampu_kanan' => $request->lampu_kanan,
                'lampu_sorot' => $request->lampu_sorot,
                'lampu_sign_depan_kanan' => $request->lampu_sign_depan_kanan,
                'lampu_sign_depan_kiri' => $request->lampu_sign_depan_kiri,
                'kipas_belakang' => $request->kipas_belakang,
                'rantai_lift' => $request->rantai_lift,
                'sistem_hidrolik' => $request->sistem_hidrolik,
                'kondisi_axle' => $request->kondisi_axle,
                'sistem_kemudi' => $request->sistem_kemudi,
                'panel_display' => $request->panel_display,
                'jam_operasional' => $request->jam_operasional,
                'air_aki' => $request->air_aki,
                'klakson' => $request->klakson,
                'buzzer_mundur' => $request->buzzer_mundur,
                'kaca_spion' => $request->kaca_spion,
                'kondisi_ban' => $request->kondisi_ban,
                'fungsi_rem' => $request->fungsi_rem,
                'persentase' => $persentase
            ]);

            return response()->json([
                'success' => true,
                'data' => $batch,
                'persentase' => $persentase,
                'status_unit' => $statusUnit
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storePalletMover(Request $request)
    {
        // Validasi input
        $request->validate([
            'tanggal' => 'required|date',
            'jenis_p2h' => 'required|string',
            'nomor_unit' => 'required|string',
            'dept' => 'required|string',
            'shift' => 'required|string',
            'operator_name' => 'required|string',
            'catatan' => 'nullable|string',
            'check_air_accu' => 'required|in:0,1',
            'check_battery' => 'required|in:0,1',
            'check_body_unit' => 'required|in:0,1',
            'check_klakson' => 'required|in:0,1',
            'check_roda' => 'required|in:0,1',
            'check_sistem_kemudi' => 'required|in:0,1',
            'check_kebersihan_unit' => 'required|in:0,1',
            'check_kunci_pm' => 'required|in:0,1',
            'check_hydraulic' => 'required|in:0,1',
        ]);

        // Cek apakah data dengan kombinasi unik sudah ada
        $exists = P2HPalletMoverModel::whereDate(
            'tanggal',
            $request->tanggal
        )
            ->where('shift', $request->shift)
            ->where('nomor_unit', $request->nomor_unit)
            ->where('jenis_p2h', $request->jenis_p2h)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data untuk tanggal, shift, jenis p2h, dan nomor unit ini sudah ada.'
            ], 422);
        }

        try {
            $batch = P2HPalletMoverModel::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $batch
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function showForklift()
    {
        $data = P2HForklfitModel::orderBy('tanggal', 'desc')->get()
            ->groupBy(fn($item) => $item->jenis_p2h . '|' . $item->tanggal . '|' . $item->nomor_unit);

        $result = [];

        foreach ($data as $groupKey => $items) {
            [$jenis_p2h, $tanggal, $nomor_unit] = explode('|', $groupKey);

            $shiftData = [];

            foreach ($items as $item) {
                $shiftData[$item->shift] = $item;
            }

            $result[] = [
                'tanggal' => $tanggal,
                'nomor_unit' => $nomor_unit,
                'jenis_p2h' => $jenis_p2h,
                'shifts' => $shiftData
            ];
        }

        return response()->json($result);
    }

    public function showPalletMover()
    {
        $data = P2HPalletMoverModel::orderBy('tanggal', 'desc')->get()
            ->groupBy(fn($item) => $item->jenis_p2h . '|' . $item->tanggal . '|' . $item->nomor_unit);

        $result = [];

        foreach ($data as $groupKey => $items) {
            [$jenis_p2h, $tanggal, $nomor_unit] = explode('|', $groupKey);

            $shiftData = [];

            foreach ($items as $item) {
                $shiftData[$item->shift] = $item;
            }

            $result[] = [
                'tanggal' => $tanggal,
                'nomor_unit' => $nomor_unit,
                'jenis_p2h' => $jenis_p2h,
                'shifts' => $shiftData
            ];
        }

        return response()->json($result);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
