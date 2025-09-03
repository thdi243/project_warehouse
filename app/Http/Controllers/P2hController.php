<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ForkliftModel;
use App\Models\P2HForklfitModel;
use App\Models\PalletMoverModel;
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

        $data_forklift = ForkliftModel::all();
        $data_pallet = PalletMoverModel::all();

        return view('p2h.index', compact('forklifts', 'pallets', 'departemen', 'nomorUnit', 'departemenpallet', 'nomorUnitpallet', 'data_forklift', 'data_pallet'));
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

    public function storeForkliftRegistration(Request $request)
    {
        // if (!in_array(Session::get('jabatan'), ['supervisor', 'dept_head', 'foreman'])) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $request->validate([
            'nomor_unit' => 'required|string|max:10|unique:forklifts,nomor_unit',
            'departemen' => 'required|in:warehouse,produksi',
            'status' => 'required|in:active,maintenance,inactive',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $forklift = ForkliftModel::create([
                'nomor_unit' => strtoupper(trim($request->nomor_unit)),
                'departemen' => $request->departemen,
                'status' => $request->status,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Forklift berhasil didaftarkan',
                'data' => $forklift
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeForkliftAssignment(Request $request)
    {
        // if (!in_array(Session::get('jabatan'), ['supervisor', 'dept_head', 'foreman'])) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'forklift_id' => 'required|exists:forklifts,id',
            'is_primary' => 'required|boolean',
            'notes' => 'nullable|string|max:255'
        ]);

        // Validasi user adalah operator warehouse
        $user = User::find($request->user_id);
        if ($user->jabatan !== 'operator' || $user->departemen !== 'warehouse') {
            return response()->json([
                'success' => false,
                'message' => 'User harus memiliki jabatan operator dan departemen warehouse'
            ], 422);
        }

        try {
            // Check if assignment already exists and is active
            $existingAssignment = UserForkliftAssignmentModel::where('user_id', $request->user_id)
                ->where('forklift_id', $request->forklift_id)
                ->where('is_active', true)
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'User sudah di-assign ke forklift ini'
                ], 422);
            }

            // Create assignment - menggunakan model method untuk handle primary logic
            $assignment = UserForkliftAssignmentModel::create([
                'user_id' => $request->user_id,
                'forklift_id' => $request->forklift_id,
                'is_primary' => $request->is_primary,
                'assigned_date' => now(),
                'assigned_by' => Session::get('user_id'),
                'notes' => $request->notes,
                'is_active' => true
            ]);

            // Jika set sebagai primary, model boot event akan handle logic
            // atau bisa dipanggil manual jika menggunakan method model
            if ($request->is_primary) {
                $assignment->setPrimary();
            }

            $forklift = ForkliftModel::find($request->forklift_id);
            $assignmentType = $request->is_primary ? 'Primary' : 'Backup';

            return response()->json([
                'success' => true,
                'message' => "User {$user->username} berhasil di-assign sebagai {$assignmentType} operator untuk {$forklift->nomor_unit}",
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storePallMovReg(Request $request)
    {
        $request->validate([
            'nomor_unit' => 'required|string|max:10|unique:pallet_mover,nomor_unit',
            'departemen' => 'required|in:warehouse,produksi',
            'status' => 'required|in:active,maintenance,inactive',
            'description' => 'nullable|string|max:255'
        ]);

        $pallet = PalletMoverModel::create([
            'nomor_unit' => strtoupper(trim($request->nomor_unit)),
            'departemen' => $request->departemen,
            'status' => $request->status,
            'description' => $request->description
        ]);

        return response()->json(['success' => true, 'data' => $pallet]);
    }

    public function storePallMovAssignment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'pallet_mover_id' => 'required|exists:pallet_mover,id',
            'is_primary' => 'required|boolean'
        ]);

        $user = User::findOrFail($request->user_id);
        if ($user->jabatan !== 'operator' || $user->departemen !== 'warehouse') {
            return response()->json(['success' => false, 'message' => 'User bukan operator warehouse'], 422);
        }

        $exists = PalletMoverModel::findOrFail($request->pallet_mover_id)
            ->assignedOperators()
            ->wherePivot('user_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'User sudah di-assign ke pallet mover ini'], 422);
        }

        $pallet = PalletMoverModel::findOrFail($request->pallet_mover_id);
        $pallet->assignedOperators()->attach($user->id, ['is_primary' => $request->is_primary]);

        return response()->json(['success' => true, 'message' => 'Assignment berhasil']);
    }

    /**
     * Display the forklift resource.
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

    public function showRegForklift()
    {
        $forklifts = ForkliftModel::with('assignedOperators')->orderBy('nomor_unit')->get();

        $data = $forklifts->map(function ($forklift) {
            $primary = $forklift->assignedOperators->where('pivot.is_primary', true)->first();
            $backup = $forklift->assignedOperators->where('pivot.is_primary', false);

            return [
                'id' => $forklift->id,
                'nomor_unit' => $forklift->nomor_unit,
                'status' => ucfirst($forklift->status),
                'notes' => ucfirst($forklift->notes),
                'departemen' => ucfirst($forklift->departemen),
                'primary_operator' => $primary ? $primary->username : '-',
                'backup_count' => $backup->count(),
                'created_at' => $forklift->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function showForkliftDetail($id)
    {
        // if (!in_array(Session::get('jabatan'), ['supervisor', 'dept_head', 'foreman'])) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        try {
            $forklift = ForkliftModel::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $forklift->id,
                    'nomor_unit' => $forklift->nomor_unit,
                    'departemen' => $forklift->departemen,
                    'status' => $forklift->status,
                    'description' => $forklift->description
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function showForkliftAssignment($id)
    {
        $forklift = ForkliftModel::with('assignedOperators')->findOrFail($id);

        $primary = $forklift->assignedOperators->where('pivot.is_primary', true)->first();
        $backups = $forklift->assignedOperators->where('pivot.is_primary', false)->pluck('id')->toArray();

        $operators = User::where('jabatan', 'operator')
            ->where('departemen', 'warehouse')
            ->select('id', 'username', 'nik')->get();

        return response()->json([
            'primary_operator_id' => $primary ? $primary->id : null,
            'backup_operator_ids' => $backups,
            'operators' => $operators
        ]);
    }

    /**
     * Display the pallet mover resource.
     */
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

    public function getPalletData()
    {
        $pallets = PalletMoverModel::with('assignedOperators')->orderBy('nomor_unit')->get();

        $data = $pallets->map(function ($pallet) {
            $primary = $pallet->assignedOperators->where('pivot.is_primary', true)->first();
            $backup = $pallet->assignedOperators->where('pivot.is_primary', false);

            return [
                'id' => $pallet->id,
                'nomor_unit' => $pallet->nomor_unit,
                'status' => ucfirst($pallet->status),
                'notes' => ucfirst($pallet->notes),
                'departemen' => ucfirst($pallet->departemen),
                'primary_operator' => $primary ? $primary->username : '-',
                'backup_count' => $backup->count(),
                'created_at' => $pallet->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function showPallMovDetail($id)
    {
        $pallet = PalletMoverModel::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pallet->id,
                'nomor_unit' => $pallet->nomor_unit,
                'departemen' => $pallet->departemen,
                'status' => $pallet->status,
                'description' => $pallet->description
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editPallMovAssignment(string $id)
    {
        $pallet = PalletMoverModel::with('assignedOperators')->findOrFail($id);

        $primary = $pallet->assignedOperators->where('pivot.is_primary', true)->first();
        $backups = $pallet->assignedOperators->where('pivot.is_primary', false)->pluck('id')->toArray();

        $operators = User::where('jabatan', 'operator')
            ->where('departemen', 'warehouse')
            ->select('id', 'username', 'nik')
            ->get();

        return response()->json([
            'primary_operator_id' => $primary?->id,
            'backup_operator_ids' => $backups,
            'operators' => $operators
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateForklift(Request $request, string $id)
    {
        // if (!in_array(Session::get('jabatan'), ['supervisor', 'dept_head', 'foreman'])) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $request->validate([
            'nomor_unit' => 'required|string|max:10|unique:forklifts,nomor_unit,' . $id,
            'departemen' => 'required|in:warehouse,produksi',
            'status' => 'required|in:active,maintenance,inactive',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $forklift = ForkliftModel::findOrFail($id);

            $forklift->update([
                'nomor_unit' => strtoupper(trim($request->nomor_unit)),
                'departemen' => $request->departemen,
                'status' => $request->status,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data forklift berhasil diupdate',
                'data' => $forklift
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateForkliftAssignment(Request $request)
    {
        $request->validate([
            'forklift_id' => 'required|exists:forklifts,id',
            'primary_operator_id' => 'nullable|exists:users,id',
            'backup_operator_ids' => 'array'
        ]);

        $forklift = ForkliftModel::findOrFail($request->forklift_id);

        // Reset assignment
        $forklift->assignedOperators()->detach();

        // Assign operator utama
        if ($request->primary_operator_id) {
            $forklift->assignedOperators()->attach($request->primary_operator_id, ['is_primary' => true]);
        }

        // Assign backup
        if ($request->has('backup_operator_ids')) {
            foreach ($request->backup_operator_ids as $id) {
                $forklift->assignedOperators()->attach($id, ['is_primary' => false]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Assignment berhasil diupdate']);
    }

    public function updatePallMov(Request $request, $id)
    {
        $request->validate([
            'nomor_unit' => 'required|string|max:10|unique:pallet_movers,nomor_unit,' . $id,
            'departemen' => 'required|in:warehouse,produksi',
            'status' => 'required|in:active,maintenance,inactive',
            'description' => 'nullable|string|max:255'
        ]);

        $pallet = PalletMoverModel::findOrFail($id);
        $pallet->update($request->only(['nomor_unit', 'departemen', 'status', 'description']));

        return response()->json(['success' => true, 'data' => $pallet]);
    }

    public function updatePallMovAssignment(Request $request)
    {
        $request->validate([
            'pallet_mover_id' => 'required|exists:pallet_mover,id',
            'primary_operator_id' => 'nullable|exists:users,id',
            'backup_operator_ids' => 'array'
        ]);

        $pallet = PalletMoverModel::findOrFail($request->pallet_mover_id);
        $pallet->assignedOperators()->detach();

        if ($request->primary_operator_id) {
            $pallet->assignedOperators()->attach($request->primary_operator_id, ['is_primary' => true]);
        }

        if ($request->backup_operator_ids) {
            foreach ($request->backup_operator_ids as $id) {
                $pallet->assignedOperators()->attach($id, ['is_primary' => false]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Assignment berhasil diperbarui']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyForklift(string $id)
    {
        // if (!in_array(Session::get('jabatan'), ['supervisor', 'dept_head','foreman'])) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        try {
            $forklift = ForkliftModel::findOrFail($id);

            // Hapus semua assignment terlebih dahulu
            $forklift->userAssignments()->delete();

            // Lanjut hapus forklift
            $forklift->delete();

            return response()->json([
                'success' => true,
                'message' => 'Forklift dan semua assignment berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroyPallMov($id)
    {
        $pallet = PalletMoverModel::findOrFail($id);
        $pallet->assignedOperators()->detach();
        $pallet->delete();

        return response()->json(['success' => true, 'message' => 'Pallet mover berhasil dihapus.']);
    }


    /**
     * Backcup Data
     */
    public function getBackupForklift($id)
    {
        $forklift = ForkliftModel::with('assignedOperators')->findOrFail($id);

        $backups = $forklift->assignedOperators
            ->where('pivot.is_primary', false)
            ->map(function ($user) {
                return [
                    'username' => $user->username,
                    'nik' => $user->nik
                ];
            });

        return response()->json(['backups' => $backups]);
    }

    public function getBackupPallMov($id)
    {
        $pallet = PalletMoverModel::with('assignedOperators')->findOrFail($id);
        $backups = $pallet->assignedOperators
            ->where('pivot.is_primary', false)
            ->map(fn($u) => ['username' => $u->username, 'nik' => $u->nik]);

        return response()->json(['backups' => $backups]);
    }
}
