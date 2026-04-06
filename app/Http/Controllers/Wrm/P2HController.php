<?php

namespace App\Http\Controllers\Wrm;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\P2h\ForkliftModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\P2h\P2HForklfitModel;
use App\Models\P2h\PalletMoverModel;
use Illuminate\Support\Facades\Auth;
use App\Models\P2h\P2HPalletMoverModel;
use Illuminate\Support\Facades\Session;
use App\Models\P2h\PalletAssignmentModel;
use Illuminate\Support\Facades\Validator;
use App\Models\P2h\UserForkliftAssignmentModel;

class P2HController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // if (Session::get('jabatan') === 'operator') {
        $userId = Auth::user()->id;

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

        // dd($palletAssignments);


        // Ambil departemen & nomor unit pertama untuk default tampilan
        $departemen = $forklifts->first()['departemen'] ?? '';
        $nomorUnit = $forklifts->first()['nomor_unit'] ?? '';

        $departemenpallet = $pallets->first()['departemen'] ?? '';
        $nomorUnitpallet = $pallets->first()['nomor_unit'] ?? '';

        $data_forklift = ForkliftModel::all();
        $data_pallet = PalletMoverModel::all();

        return view('wrm.p2h.index', compact('forklifts', 'pallets', 'departemen', 'nomorUnit', 'departemenpallet', 'nomorUnitpallet', 'data_forklift', 'data_pallet'));
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
            'tanggal'         => 'required|date',
            'nomor_unit'      => 'required|string|max:50',
            'jenis_p2h'       => 'required|string|in:Forklift,Pallet Mover',
            'shift'           => 'required|integer|in:1,2,3',
            'operator_name'   => 'required|string|max:100',
            'jam_operasional' => 'required|numeric|min:0',
            'catatan'         => 'nullable|string',

            // Checklist fields
            'cek_baterai'               => 'required|in:0,1',
            'cek_fork'                  => 'required|in:0,1',
            'kondisi_body_kebersihan'   => 'required|in:0,1',
            'lampu_kiri'                => 'required|in:0,1',
            'lampu_kanan'               => 'required|in:0,1',
            'lampu_sorot'               => 'required|in:0,1',
            'lampu_sign_depan_kanan'    => 'required|in:0,1',
            'lampu_sign_depan_kiri'     => 'required|in:0,1',
            'kipas_belakang'            => 'required|in:0,1',
            'rantai_lift'               => 'required|in:0,1',
            'sistem_hidrolik'           => 'required|in:0,1',
            'kondisi_axle'              => 'required|in:0,1',
            'sistem_kemudi'             => 'required|in:0,1',
            'panel_display'             => 'required|in:0,1',
            'air_aki'                   => 'required|in:0,1',
            'klakson'                   => 'required|in:0,1',
            'buzzer_mundur'             => 'required|in:0,1',
            'kaca_spion'                => 'required|in:0,1',
            'kondisi_ban'               => 'required|in:0,1',
            'fungsi_rem'                => 'required|in:0,1',
        ]);

        // Cek duplikasi
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

        $checklistFields = [
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

        $labelMap = [
            'cek_baterai' => 'Cek Baterai',
            'cek_fork' => 'Cek Fork',
            'kondisi_body_kebersihan' => 'Kondisi Body & Kebersihan',
            'lampu_kiri' => 'Lampu Kiri',
            'lampu_kanan' => 'Lampu Kanan',
            'lampu_sorot' => 'Lampu Sorot',
            'lampu_sign_depan_kanan' => 'Lampu Sign Depan Kanan',
            'lampu_sign_depan_kiri' => 'Lampu Sign Depan Kiri',
            'kipas_belakang' => 'Kipas Belakang',
            'rantai_lift' => 'Rantai Lift',
            'sistem_hidrolik' => 'Sistem Hidrolik',
            'kondisi_axle' => 'Kondisi Axle',
            'sistem_kemudi' => 'Sistem Kemudi',
            'panel_display' => 'Panel Display',
            'air_aki' => 'Air Aki',
            'klakson' => 'Klakson',
            'buzzer_mundur' => 'Buzzer Mundur',
            'kaca_spion' => 'Kaca Spion',
            'kondisi_ban' => 'Kondisi Ban',
            'fungsi_rem' => 'Fungsi Rem'
        ];

        // === LOGIC CATATAN: SAMA DENGAN UPDATE (tapi lebih sederhana) ===
        $hasNok = false;
        $nokFields = [];
        $notes = [];

        foreach ($checklistFields as $field) {
            if ($request->$field == 0) {
                $hasNok = true;
                $nokFields[] = $labelMap[$field];

                // Ambil note per item jika ada
                $noteField = $field . '_note';
                $note = trim($request->$noteField ?? '');
                if ($note !== '') {
                    $notes[] = $labelMap[$field] . ': ' . $note;
                }
            }
        }

        // Tambahkan catatan umum jika ada
        $generalNote = trim($request->catatan ?? '');
        if ($generalNote !== '') {
            $notes[] = $generalNote;
        }

        // Validasi: kalau ada NOK tapi tidak ada keterangan sama sekali
        if ($hasNok && empty($notes)) {
            return response()->json([
                'success' => false,
                'message' => 'Ada item NOK (' . implode(', ', $nokFields) . ') tapi tidak ada keterangan'
            ], 422);
        }

        $finalCatatan = implode(' | ', array_filter($notes));

        // === VALIDASI JAM OPERASIONAL (sama persis dengan update) ===
        $jamSekarang = $request->jam_operasional;

        // // Vs kemarin
        // $jamKemarin = P2HForklfitModel::where('nomor_unit', $request->nomor_unit)
        //     ->whereDate('tanggal', '<', $request->tanggal)
        //     ->max('jam_operasional') ?? 0;

        // if ($jamSekarang < $jamKemarin) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => "Jam operasional ({$jamSekarang}) mundur dari kemarin ({$jamKemarin})"
        //     ], 422);
        // }

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

        // Vs shift sebelumnya hari ini
        // $prevShift = $request->shift - 1;
        // if ($prevShift >= 1) {
        //     $jamPrev = P2HForklfitModel::where('nomor_unit', $request->nomor_unit)
        //         ->where('tanggal', $request->tanggal)
        //         ->where('shift', $prevShift)
        //         ->value('jam_operasional') ?? 0;

        //     if ($jamSekarang < $jamPrev) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => "Jam operasional ({$jamSekarang}) mundur dari Shift {$prevShift} ({$jamPrev})"
        //         ], 422);
        //     }
        // }

        // Hitung persentase
        $groups = [
            20 => ['cek_baterai', 'cek_fork', 'kondisi_body_kebersihan', 'lampu_kiri', 'lampu_kanan', 'lampu_sorot', 'lampu_sign_depan_kanan', 'lampu_sign_depan_kiri', 'kipas_belakang'],
            50 => ['rantai_lift', 'sistem_hidrolik', 'kondisi_axle', 'sistem_kemudi', 'panel_display', 'jam_operasional', 'air_aki'],
            30 => ['klakson', 'buzzer_mundur', 'kaca_spion', 'kondisi_ban', 'fungsi_rem'],
        ];

        $totalPoin = 0;
        foreach ($groups as $point => $fields) {
            foreach ($fields as $f) {
                $totalPoin += ($request->$f ?? 0) ? $point : 0;
            }
        }

        $maxPoin = count($groups[20]) * 20 + count($groups[50]) * 50 + count($groups[30]) * 30;
        $persentase = $maxPoin ? round($totalPoin / $maxPoin * 100, 2) : 0;

        $critical = ['cek_baterai', 'kipas_belakang', 'rantai_lift', 'sistem_hidrolik', 'kondisi_axle', 'sistem_kemudi', 'panel_display', 'air_aki', 'fungsi_rem'];
        $isRusakBerat = collect($critical)->contains(fn($f) => $request->$f == 0);
        if ($isRusakBerat) {
            $persentase = 50.00;
        }
        $statusUnit = $isRusakBerat ? 'Rusak Berat' : 'Normal';

        try {
            P2HForklfitModel::create([
                'tanggal'         => $request->tanggal,
                'nomor_unit'      => $request->nomor_unit,
                'jenis_p2h'       => $request->jenis_p2h,
                'shift'           => $request->shift,
                'operator_name'   => $request->operator_name,
                'jam_operasional' => $request->jam_operasional,
                'dept'            => 'Warehouse',
                'catatan'         => $finalCatatan,
                'persentase'      => $persentase,
                // Checklist fields
                'cek_baterai'               => $request->cek_baterai,
                'cek_fork'                  => $request->cek_fork,
                'kondisi_body_kebersihan'   => $request->kondisi_body_kebersihan,
                'lampu_kiri'                => $request->lampu_kiri,
                'lampu_kanan'               => $request->lampu_kanan,
                'lampu_sorot'               => $request->lampu_sorot,
                'lampu_sign_depan_kanan'    => $request->lampu_sign_depan_kanan,
                'lampu_sign_depan_kiri'     => $request->lampu_sign_depan_kiri,
                'kipas_belakang'            => $request->kipas_belakang,
                'rantai_lift'               => $request->rantai_lift,
                'sistem_hidrolik'           => $request->sistem_hidrolik,
                'kondisi_axle'              => $request->kondisi_axle,
                'sistem_kemudi'             => $request->sistem_kemudi,
                'panel_display'             => $request->panel_display,
                'air_aki'                   => $request->air_aki,
                'klakson'                   => $request->klakson,
                'buzzer_mundur'             => $request->buzzer_mundur,
                'kaca_spion'                => $request->kaca_spion,
                'kondisi_ban'               => $request->kondisi_ban,
                'fungsi_rem'                => $request->fungsi_rem,
            ]);

            return response()->json([
                'success'     => true,
                'message'     => 'P2H berhasil disimpan!',
                'persentase'  => $persentase,
                'status_unit' => $statusUnit
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
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
        $validator = Validator::make($request->all(), [
            'nomor_unit' => 'required|string|max:10|unique:forklifts,nomor_unit',
            'departemen' => 'required|in:warehouse,produksi',
            'section' => 'required|in:warehouse_raw_material,warehouse_finish_goods,warehouse_co_product', // Match form options
            'status' => 'required|in:active,maintenance,inactive',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $validator->errors() // optional, buat debug
            ], 422);
        }

        DB::beginTransaction();
        try {
            $forklift = ForkliftModel::create([
                'nomor_unit' => strtoupper(trim($request->nomor_unit)),
                'departemen' => $request->departemen,
                'section' => $request->section,
                'status' => $request->status,
                'description' => $request->description
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Forklift ' . $forklift->nomor_unit . ' berhasil didaftarkan',
                'data' => $forklift
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Forklift error: ' . $e->getMessage(), $request->all());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeForkliftAssignment(Request $request)
    {
        // Pastikan user login (tambahkan middleware auth:web atau auth:sanctum di route)
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'forklift_id'  => 'required|exists:forklifts,id',
            'operator'     => 'required|in:1,2,3', // ini yang dari form: Operator 1/2/3
            'notes'        => 'nullable|string|max:255'
        ]);

        $user = User::find($request->user_id);

        // Validasi: hanya operator warehouse yang boleh
        if (!$user || $user->jabatan !== 'operator' || $user->departemen !== 'warehouse') {
            return response()->json([
                'success' => false,
                'message' => 'User harus operator dari departemen warehouse'
            ], 422);
        }

        $forklift = ForkliftModel::find($request->forklift_id);
        if (!$forklift) {
            return response()->json([
                'success' => false,
                'message' => 'Forklift tidak ditemukan'
            ], 422);
        }

        // Cek apakah user sudah di-assign ke forklift ini (aktif)
        $existing = UserForkliftAssignmentModel::where('user_id', $request->user_id)
            ->where('forklift_id', $request->forklift_id)
            ->where('is_active', true)
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Operator ini sudah di-assign ke forklift ini'
            ], 422);
        }

        $alreadyAssignedElsewhere = UserForkliftAssignmentModel::where('user_id', $request->user_id)
            ->where('is_active', true)
            ->exists();

        if ($alreadyAssignedElsewhere) {
            return response()->json([
                'success' => false,
                'message' => 'Operator ini sudah di-assign aktif ke forklift lain. Satu operator hanya boleh aktif di satu unit forklift saja.'
            ], 422);
        }

        try {
            UserForkliftAssignmentModel::insert([
                'user_id'       => $request->user_id,
                'forklift_id'   => $request->forklift_id,
                'operator_type' => $request->operator, // pakai operator_type (1,2,3)
                'assigned_date' => now(),
                'assigned_by'   => Auth::id(),
                'notes'         => $request->notes,
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $typeText = match ($request->operator) {
                '1' => 'Operator 1',
                '2' => 'Operator 2',
                '3' => 'Operator 3',
            };

            return response()->json([
                'success' => true,
                'message' => "{$user->nama_lengkap} berhasil di-assign sebagai {$typeText} untuk forklift {$forklift->nomor_unit}"
            ]);
        } catch (\Exception $e) {
            Log::error('Assignment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan assignment'
            ], 500);
        }
    }

    public function storePallMovReg(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_unit' => 'required|string|max:10|unique:pallet_movers,nomor_unit',
            'departemen' => 'required|in:warehouse,produksi',
            'section' => 'required|in:warehouse_raw_material,warehouse_finish_goods,warehouse_co_product', // Match form options
            'status' => 'required|in:active,maintenance,inactive',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $validator->errors() // optional, buat debug
            ], 422);
        }

        DB::beginTransaction();
        try {
            $palletMover = PalletMoverModel::create([
                'nomor_unit' => strtoupper(trim($request->nomor_unit)),
                'departemen' => $request->departemen,
                'section' => $request->section,
                'status' => $request->status,
                'description' => $request->description
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pallet Mover ' . $palletMover->nomor_unit . ' berhasil didaftarkan',
                'data' => $palletMover
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pallet Mover error: ' . $e->getMessage(), $request->all());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storePallMovAssignment(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'user_id'         => 'required|exists:users,id',
            'pallet_mover_id' => 'required|exists:pallet_movers,id',
            'operator'        => 'required|in:1,2,3', // Operator 1/2/3
            'notes'           => 'nullable|string|max:255'
        ]);

        $user = User::find($request->user_id);

        // Validasi user adalah operator warehouse
        if (!$user || $user->jabatan !== 'operator' || $user->departemen !== 'warehouse') {
            return response()->json([
                'success' => false,
                'message' => 'User harus memiliki jabatan operator dan departemen warehouse'
            ], 422);
        }

        $palletMover = PalletMoverModel::find($request->pallet_mover_id);
        if (!$palletMover) {
            return response()->json([
                'success' => false,
                'message' => 'Pallet Mover tidak ditemukan'
            ], 422);
        }

        // Cek apakah user sudah di-assign ke pallet mover ini (aktif)
        $existing = PalletAssignmentModel::where('user_id', $request->user_id)
            ->where('pallet_mover_id', $request->pallet_mover_id)
            ->where('is_active', true)
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Operator ini sudah di-assign ke Pallet Mover ini'
            ], 422);
        }

        // === VALIDASI KHUSUS: Operator 1 hanya boleh di SATU Pallet Mover ===
        $alreadyAssignedElsewhere = PalletAssignmentModel::where('user_id', $request->user_id)
            ->where('is_active', true)
            ->exists();

        if ($alreadyAssignedElsewhere) {
            return response()->json([
                'success' => false,
                'message' => 'Operator ini sudah di-assign aktif ke pallet mover lain. Satu operator hanya boleh aktif di satu unit saja.'
            ], 422);
        }

        try {
            PalletAssignmentModel::insert([
                'user_id'         => $request->user_id,
                'pallet_mover_id' => $request->pallet_mover_id,
                'operator_type'   => $request->operator,
                'assigned_date'   => now(),
                'assigned_by'     => Auth::id(),
                'notes'           => $request->notes,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $typeText = match ((int)$request->operator) {
                1 => 'Operator 1 (Utama)',
                2 => 'Operator 2 (Backup 1)',
                3 => 'Operator 3 (Backup 2)',
            };

            return response()->json([
                'success' => true,
                'message' => "{$user->nama_lengkap} berhasil di-assign sebagai {$typeText} untuk Pallet Mover {$palletMover->nomor_unit}"
            ]);
        } catch (\Exception $e) {
            Log::error('Pallet Mover Assignment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan assignment'
            ], 500);
        }
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

            $forklift = ForkliftModel::where('nomor_unit', $nomor_unit)->first();
            $section = $forklift ? $this->formatSection($forklift->section) : '-';

            $result[] = [
                // 'id' => $id,
                'tanggal' => $tanggal,
                'nomor_unit' => $nomor_unit,
                'jenis_p2h' => $jenis_p2h,
                'section' => $section,
                'shifts' => $shiftData
            ];
        }

        return response()->json($result);
    }

    public function showRegForklift()
    {
        $forklifts = ForkliftModel::with(['assignedOperators' => function ($query) {
            $query->wherePivot('is_active', true)
                ->orderByPivot('operator_type', 'asc');
        }])->orderBy('nomor_unit')->get();

        $data = $forklifts->map(function ($forklift) {
            $operators = $forklift->assignedOperators;

            // Ambil primary (type = 1)
            $primary = $operators->where('pivot.operator_type', 1)->first();
            $operator2 = $operators->where('pivot.operator_type', 2)->first();
            $operator3 = $operators->where('pivot.operator_type', 3)->first();


            // Ambil notes dari primary jika ada, atau '-' 
            $notes = $primary?->pivot?->notes
                ? ucfirst($primary->pivot->notes)
                : '-';

            $sectionDisplay = match ($forklift->section) {
                'warehouse_raw_material' => 'Warehouse Raw Material',
                'warehouse_finish_goods' => 'Warehouse Finish Goods',
                'warehouse_co_product' => 'Warehouse Co Product',
                default => ucwords(str_replace('_', ' ', $forklift->section))
            };

            return [
                'id' => $forklift->id,
                'nomor_unit' => strtoupper($forklift->nomor_unit),
                'status' => ucfirst($forklift->status),
                'departemen' => ucfirst($forklift->departemen),
                'section' => $sectionDisplay,
                'notes' => $notes,
                'operator_1' => $primary ? $primary->nama_lengkap ?? $primary->username : '-',
                'operator_2' => $operator2 ? $operator2->nama_lengkap ?? $operator2->username : '-',
                'operator_3' => $operator3 ? $operator3->nama_lengkap ?? $operator3->username : '-',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function showForkliftDetail($id)
    {
        try {
            $forklift = ForkliftModel::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $forklift->id,
                    'nomor_unit' => $forklift->nomor_unit,
                    'departemen' => $forklift->departemen,
                    'section' => $forklift->section,
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

        $operators = $forklift->assignedOperators;

        // Ambil primary (type = 1)
        $primary = $operators->where('pivot.operator_type', 1)->first();
        $operator2 = $operators->where('pivot.operator_type', 2)->first();
        $operator3 = $operators->where('pivot.operator_type', 3)->first();

        $operators = User::where('jabatan', 'operator')
            ->where('departemen', 'warehouse')
            ->select('id', 'username', 'nama_lengkap', 'nik')->get();

        return response()->json([
            'operators' => $operators,
            'operator_1' => $primary ? $primary->id : null,
            'operator_2' => $operator2 ? $operator2->id : null,
            'operator_3' => $operator3 ? $operator3->id : null,
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

            $pm = PalletMoverModel::where('nomor_unit', $nomor_unit)->first();
            $section = $pm ? $this->formatSection($pm->section) : '-';

            $result[] = [
                'tanggal' => $tanggal,
                'nomor_unit' => $nomor_unit,
                'jenis_p2h' => $jenis_p2h,
                'section' => $section,
                'shifts' => $shiftData
            ];
        }

        return response()->json($result);
    }

    public function getPalletData()
    {
        $palletMover = PalletMoverModel::with(['assignedOperators' => function ($query) {
            $query->wherePivot('is_active', true)
                ->orderByPivot('operator_type', 'asc');
        }])->orderBy('nomor_unit')->get();

        $data = $palletMover->map(function ($pm) {
            $operators = $pm->assignedOperators;

            // Ambil primary (type = 1)
            $primary = $operators->where('pivot.operator_type', 1)->first();
            $operator2 = $operators->where('pivot.operator_type', 2)->first();
            $operator3 = $operators->where('pivot.operator_type', 3)->first();


            // Ambil notes dari primary jika ada, atau '-' 
            $notes = $primary?->pivot?->notes
                ? ucfirst($primary->pivot->notes)
                : '-';

            $sectionDisplay = match ($pm->section) {
                'warehouse_raw_material' => 'Warehouse Raw Material',
                'warehouse_finish_goods' => 'Warehouse Finish Goods',
                'warehouse_co_product' => 'Warehouse Co Product',
                default => ucwords(str_replace('_', ' ', $pm->section))
            };

            return [
                'id' => $pm->id,
                'nomor_unit' => strtoupper($pm->nomor_unit),
                'status' => ucfirst($pm->status),
                'departemen' => ucfirst($pm->departemen),
                'section' => $sectionDisplay,
                'notes' => $notes,
                'operator_1' => $primary ? $primary->nama_lengkap ?? $primary->username : '-',
                'operator_2' => $operator2 ? $operator2->nama_lengkap ?? $operator2->username : '-',
                'operator_3' => $operator3 ? $operator3->nama_lengkap ?? $operator3->username : '-',
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
                'section' => $pallet->section,
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
        $forklift = PalletMoverModel::with('assignedOperators')->findOrFail($id);

        $operators = $forklift->assignedOperators;

        // Ambil primary (type = 1)
        $primary = $operators->where('pivot.operator_type', 1)->first();
        $operator2 = $operators->where('pivot.operator_type', 2)->first();
        $operator3 = $operators->where('pivot.operator_type', 3)->first();

        $operators = User::where('jabatan', 'operator')
            ->where('departemen', 'warehouse')
            ->select('id', 'username', 'nama_lengkap', 'nik')->get();

        return response()->json([
            'operators' => $operators,
            'operator_1' => $primary ? $primary->id : null,
            'operator_2' => $operator2 ? $operator2->id : null,
            'operator_3' => $operator3 ? $operator3->id : null,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateForklift(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nomor_unit'  => 'required|string|max:10|unique:forklifts,nomor_unit,' . $id . ',id',
            'departemen'  => 'required|in:warehouse,produksi',
            'section'     => 'required|in:warehouse_raw_material,warehouse_finish_goods,warehouse_co_product',
            'status'      => 'required|in:active,maintenance,inactive',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(), // pesan error pertama
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $forklift = ForkliftModel::findOrFail($id);

            $forklift->update([
                'nomor_unit'  => strtoupper(trim($request->nomor_unit)),
                'departemen'  => $request->departemen,
                'section'     => $request->section,        // tambah ini
                'status'      => $request->status,
                'description' => $request->description ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data forklift ' . $forklift->nomor_unit . ' berhasil diupdate',
                'data'    => $forklift
            ]);
        } catch (\Exception $e) {
            Log::error('Update forklift error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal update forklift'
            ], 500);
        }
    }

    public function updateMultiShiftP2H(Request $request)
    {
        $request->validate([
            'tanggal'    => 'nullable|date',
            'nomor_unit' => 'nullable|string|max:50',
            'jenis_p2h'  => 'nullable|string|in:Forklift,Pallet Mover',
            'shifts'     => 'required|array',
        ]);

        $tanggal   = $request->tanggal;
        $nomorUnit = $request->nomor_unit;
        $jenisP2H  = $request->jenis_p2h;
        $shifts    = $request->shifts;

        DB::beginTransaction();
        try {
            $checklistFields = [
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

            $labelMap = [
                'cek_baterai' => 'Cek Baterai',
                'cek_fork' => 'Cek Fork',
                'kondisi_body_kebersihan' => 'Kondisi Body & Kebersihan',
                'lampu_kiri' => 'Lampu Kiri',
                'lampu_kanan' => 'Lampu Kanan',
                'lampu_sorot' => 'Lampu Sorot',
                'lampu_sign_depan_kanan' => 'Lampu Sign Depan Kanan',
                'lampu_sign_depan_kiri' => 'Lampu Sign Depan Kiri',
                'kipas_belakang' => 'Kipas Belakang',
                'rantai_lift' => 'Rantai Lift',
                'sistem_hidrolik' => 'Sistem Hidrolik',
                'kondisi_axle' => 'Kondisi Axle',
                'sistem_kemudi' => 'Sistem Kemudi',
                'panel_display' => 'Panel Display',
                'air_aki' => 'Air Aki',
                'klakson' => 'Klakson',
                'buzzer_mundur' => 'Buzzer Mundur',
                'kaca_spion' => 'Kaca Spion',
                'kondisi_ban' => 'Kondisi Ban',
                'fungsi_rem' => 'Fungsi Rem'
            ];

            $allowedFields = array_merge(
                ['operator_name', 'jam_operasional', 'catatan', 'persentase', 'updated_by'],
                $checklistFields
            );

            // **HANYA PROSES SHIFT YANG ADA DATA**
            foreach ($shifts as $shiftNumber => $data) {
                if (!is_array($data)) {
                    continue;
                }

                // dd($shift);
                $id = $data['id'] ?? null;
                $shift = $shiftNumber;

                // Validasi field wajib HANYA jika ada data
                $validator = Validator::make($data, [
                    'operator_name'   => 'required|string|max:100',
                    'jam_operasional' => 'nullable|numeric|min:0',
                    'catatan'         => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Shift {$shift}: " . $validator->errors()->first()
                    ], 422);
                }

                // **Validasi checklist HANYA field yang dikirim**
                foreach ($checklistFields as $field) {
                    if (!isset($data[$field])) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Checklist {$labelMap[$field]} Shift {$shift} wajib diisi"
                        ], 422);
                    }

                    if (!in_array($data[$field], ['0', '1'])) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Checklist {$labelMap[$field]} Shift {$shift} harus 0 atau 1"
                        ], 422);
                    }
                }

                // === KETERANGAN WAJIB KALAU NOK ===
                $hasNok = false;
                $nokFields = [];
                $notes = [];

                // Kumpulin dulu semua note per item yang terisi
                $perItemNotes = [];
                foreach ($checklistFields as $field) {
                    if (($data[$field] ?? 1) == 0) {
                        $hasNok = true;
                        $nokFields[] = $labelMap[$field];

                        $note = trim($data[$field . '_note'] ?? '');
                        if ($note !== '') {
                            $perItemNotes[$field] = $note; // Simpan untuk prioritas
                            $notes[] = $labelMap[$field] . ': ' . $note;
                        }
                    }
                }

                // Jika ada NOK tapi belum ada note per item, coba ekstrak dari catatan umum
                $generalNote = trim($data['catatan'] ?? '');
                $usedGeneralParts = [];

                if ($hasNok && count($notes) === 0 && $generalNote !== '') {
                    foreach ($checklistFields as $field) {
                        if (($data[$field] ?? 1) == 0 && empty($perItemNotes[$field])) {
                            $label = $labelMap[$field];
                            // Regex lebih fleksibel: tangkap "Label: teks" atau "Label - teks" dll
                            $regex = '/' . preg_quote($label, '/') . '\s*[:\-\–]\s*(.+?)(?=\s*[\|\n]|$)/i';
                            if (preg_match($regex, $generalNote, $match)) {
                                $extracted = trim($match[1]);
                                $notes[] = $label . ': ' . $extracted;
                                $usedGeneralParts[] = $match[0]; // Simpan bagian yang dipakai
                            }
                        }
                    }
                }

                // Sisanya dari catatan umum yang BUKAN berupa "Label: xxx" tetap ditambahkan sebagai catatan bebas
                $remainingGeneral = $generalNote;
                if (!empty($usedGeneralParts)) {
                    foreach ($usedGeneralParts as $part) {
                        $remainingGeneral = str_ireplace($part, '', $remainingGeneral);
                    }
                }
                $remainingGeneral = trim(preg_replace('/\|\s*\|/', '|', $remainingGeneral)); // Bersihkan pipe ganda
                $remainingGeneral = trim($remainingGeneral, ' |');

                if ($remainingGeneral !== '') {
                    $notes[] = $remainingGeneral; // Bukan prefixed "Umum:", langsung aja
                }

                // Validasi: kalau ada NOK tapi notes masih kosong → error
                if ($hasNok && empty($notes)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Shift {$shift}: Ada NOK (" . implode(', ', $nokFields) . ") tapi tidak ada keterangan"
                    ], 422);
                }

                $finalCatatan = implode(' | ', array_filter($notes));

                // === VALIDASI JAM OPERASIONAL ===
                $jamSekarang = (float) $data['jam_operasional']; // pastikan numeric/float

                // Cari record "sebelumnya" untuk unit ini
                $lastRecord = P2HForklfitModel::where('nomor_unit', $nomorUnit)
                    ->where(function ($query) use ($tanggal, $shift) {
                        $query->where('tanggal', '<', $tanggal)
                            ->orWhere(function ($q) use ($tanggal, $shift) {
                                $q->where('tanggal', $tanggal)
                                    ->where('shift', '<', $shift);
                            });
                    })
                    ->orderByDesc('tanggal')
                    ->orderByDesc('shift')
                    ->first();

                // Jika ada record sebelumnya DAN jam sekarang lebih kecil → tolak
                if ($lastRecord && $jamSekarang < $lastRecord->jam_operasional) {
                    return response()->json([
                        'success' => false,
                        'message' => "Shift {$shift} ({$tanggal}): Jam operasional {$jamSekarang} tidak boleh lebih kecil dari data sebelumnya "
                            . "({$lastRecord->tanggal} shift {$lastRecord->shift}: {$lastRecord->jam_operasional}). Cek kembali!"
                    ], 422);
                }

                // Hitung persentase (sama seperti sebelumnya)
                $groups = [
                    20 => ['cek_baterai', 'cek_fork', 'kondisi_body_kebersihan', 'lampu_kiri', 'lampu_kanan', 'lampu_sorot', 'lampu_sign_depan_kanan', 'lampu_sign_depan_kiri', 'kipas_belakang'],
                    50 => ['rantai_lift', 'sistem_hidrolik', 'kondisi_axle', 'sistem_kemudi', 'panel_display', 'jam_operasional', 'air_aki'],
                    30 => ['klakson', 'buzzer_mundur', 'kaca_spion', 'kondisi_ban', 'fungsi_rem'],
                ];

                $totalPoin = 0;
                foreach ($groups as $point => $fields) {
                    foreach ($fields as $f) {
                        $totalPoin += ($data[$f] ?? 0) ? $point : 0;
                    }
                }

                $maxPoin = count($groups[20]) * 20 + count($groups[50]) * 50 + count($groups[30]) * 30;
                $persentase = $maxPoin ? round($totalPoin / $maxPoin * 100, 2) : 0;

                $critical = ['cek_baterai', 'kipas_belakang', 'rantai_lift', 'sistem_hidrolik', 'kondisi_axle', 'sistem_kemudi', 'panel_display', 'air_aki', 'fungsi_rem'];
                if (collect($critical)->contains(fn($f) => ($data[$f] ?? 0) == 0)) {
                    $persentase = 50.00;
                }

                $payload = collect($data)
                    ->only($allowedFields)
                    ->merge([
                        'catatan'    => $finalCatatan,
                        'persentase' => $persentase,
                        'updated_by' => Auth::id() ?? 53,
                    ])
                    ->toArray();

                if ($id) {
                    P2HForklfitModel::where('id', $id)->update($payload);
                } else {
                    P2HForklfitModel::create($payload);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'P2H berhasil disimpan!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('P2H Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateForkliftAssignment(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'forklift_id' => 'required|exists:forklifts,id',
            'operator_1'  => 'required|exists:users,id',
            'operator_2'  => 'nullable|exists:users,id',
            'operator_3'  => 'nullable|exists:users,id',
        ]);

        $forkliftId = $request->forklift_id;

        // Ambil input yang diisi
        $inputs = [
            1 => $request->operator_1,
            2 => $request->operator_2,
            3 => $request->operator_3,
        ];

        // Validasi jabatan & departemen + cek duplikat antar posisi
        $usedUserIds = [];
        foreach ($inputs as $type => $userId) {
            if ($userId) {
                $user = User::find($userId);
                if (!$user || $user->jabatan !== 'operator' || $user->departemen !== 'warehouse') {
                    return response()->json([
                        'success' => false,
                        'message' => "Operator posisi {$type} harus dari warehouse dengan jabatan operator"
                    ], 422);
                }

                if (in_array($userId, $usedUserIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Satu operator tidak boleh dipilih di lebih dari satu posisi di forklift yang sama'
                    ], 422);
                }
                $usedUserIds[] = $userId;
            }
        }

        DB::beginTransaction();
        try {
            foreach ([1, 2, 3] as $type) {
                $newUserId = $inputs[$type] ?? null;

                if ($newUserId) {
                    $alreadyAssignedElsewhere = UserForkliftAssignmentModel::where('user_id', $newUserId)
                        ->where('is_active', true)
                        ->where('forklift_id', '!=', $forkliftId)
                        ->exists();

                    if ($alreadyAssignedElsewhere) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Operator ini sudah di-assign ke Forklift lain. Satu operator hanya boleh aktif di satu unit saja.'
                        ], 422);
                    }
                }
                // Cari assignment existing untuk type ini
                $existing = UserForkliftAssignmentModel::where('forklift_id', $forkliftId)
                    ->where('operator_type', $type)
                    ->where('is_active', true)
                    ->first();

                if ($newUserId) {
                    // Ada user baru dipilih
                    if ($existing) {
                        // Update yang lama
                        if ($existing->user_id != $newUserId) {
                            UserForkliftAssignmentModel::where('id', $existing->id)
                                ->update([
                                    'user_id'       => $newUserId,
                                    'assigned_by'   => Auth::id(),
                                    'assigned_date' => now(),
                                    'updated_at'    => now(),
                                ]);
                        }
                        // Jika user_id sama → tidak perlu apa-apa
                    } else {
                        // Tidak ada sebelumnya → create baru
                        UserForkliftAssignmentModel::insert([
                            'user_id'       => $newUserId,
                            'forklift_id'   => $forkliftId,
                            'operator_type' => $type,
                            'assigned_date' => now(),
                            'assigned_by'   => Auth::id(),
                            'notes'         => null,
                            'is_active'     => true,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                } else {
                    // Kosong → hapus assignment untuk type ini (jika ada)
                    if ($existing) {
                        UserForkliftAssignmentModel::where('id', $existing->id)
                            ->update(['is_active' => false, 'updated_at' => now()]);
                        // atau delete() kalau mau hard delete
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment operator berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update assignment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePallMov(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nomor_unit'  => 'required|string|max:10|unique:pallet_movers,nomor_unit,' . $id . ',id',
            'departemen'  => 'required|in:warehouse,produksi',
            'section'     => 'required|in:warehouse_raw_material,warehouse_finish_goods,warehouse_co_product',
            'status'      => 'required|in:active,maintenance,inactive',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(), // pesan error pertama
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $palletMover = PalletMoverModel::findOrFail($id);

            $palletMover->update([
                'nomor_unit'  => strtoupper(trim($request->nomor_unit)),
                'departemen'  => $request->departemen,
                'section'     => $request->section,        // tambah ini
                'status'      => $request->status,
                'description' => $request->description ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Pallet Mover ' . $palletMover->nomor_unit . ' berhasil diupdate',
                'data'    => $palletMover
            ]);
        } catch (\Exception $e) {
            Log::error('Update pallet mover error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal update pallet mover'
            ], 500);
        }
    }

    public function updatePallMovAssignment(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'pallet_mover_id' => 'required|exists:pallet_movers,id',
            'operator_1'      => 'nullable|exists:users,id',
            'operator_2'      => 'nullable|exists:users,id',
            'operator_3'      => 'nullable|exists:users,id',
        ]);

        $palletMoverId = $request->pallet_mover_id;

        // Ambil input yang diisi
        $inputs = [
            1 => $request->operator_1,
            2 => $request->operator_2,
            3 => $request->operator_3,
        ];

        // Validasi jabatan & departemen + cek duplikat antar posisi
        $usedUserIds = [];
        foreach ($inputs as $type => $userId) {
            if ($userId) {
                $user = User::find($userId);
                if (!$user || $user->jabatan !== 'operator' || $user->departemen !== 'warehouse') {
                    return response()->json([
                        'success' => false,
                        'message' => "Operator posisi {$type} harus dari departemen warehouse dengan jabatan operator"
                    ], 422);
                }

                if (in_array($userId, $usedUserIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Satu operator tidak boleh dipilih di lebih dari satu posisi di Pallet Mover yang sama'
                    ], 422);
                }
                $usedUserIds[] = $userId;
            }
        }

        DB::beginTransaction();
        try {
            foreach ([1, 2, 3] as $type) {
                $newUserId = $inputs[$type] ?? null;

                // === VALIDASI KHUSUS: Operator 1 hanya boleh di SATU Pallet Mover ===
                if ($newUserId) {
                    $alreadyAssignedElsewhere = PalletAssignmentModel::where('user_id', $newUserId)
                        ->where('is_active', true)
                        ->where('pallet_mover_id', '!=', $palletMoverId)
                        ->exists();

                    if ($alreadyAssignedElsewhere) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Operator ini sudah di-assign ke Pallet Mover lain. Satu operator hanya boleh aktif di satu unit saja.'
                        ], 422);
                    }
                }

                // Cari assignment existing untuk type ini di Pallet Mover ini
                $existing = PalletAssignmentModel::where('pallet_mover_id', $palletMoverId)
                    ->where('operator_type', $type)
                    ->where('is_active', true)
                    ->first();

                if ($newUserId) {
                    // Ada user dipilih
                    if ($existing) {
                        // Update jika user berbeda
                        if ($existing->user_id != $newUserId) {
                            PalletAssignmentModel::where('id', $existing->id)
                                ->update([
                                    'user_id'       => $newUserId,
                                    'assigned_by'   => Auth::id(),
                                    'assigned_date' => now(),
                                    'updated_at'    => now(),
                                ]);
                        }
                        // Jika sama → skip
                    } else {
                        // Create baru
                        PalletAssignmentModel::insert([
                            'user_id'         => $newUserId,
                            'pallet_mover_id' => $palletMoverId,
                            'operator_type'   => $type,
                            'assigned_date'   => now(),
                            'assigned_by'     => Auth::id(),
                            'notes'           => null, // bisa ditambah field notes nanti
                            'is_active'       => true,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                } else {
                    // Kosong → soft delete assignment untuk type ini
                    if ($existing) {
                        PalletAssignmentModel::where('id', $existing->id)
                            ->update(['is_active' => false, 'updated_at' => now()]);
                    }
                }
            }

            DB::commit();

            $pallet = PalletMoverModel::find($palletMoverId);

            return response()->json([
                'success' => true,
                'message' => 'Assignment operator Pallet Mover ' . $pallet->nomor_unit . ' berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Pallet Mover assignment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui assignment'
            ], 500);
        }
    }

    public function updateMultiShiftPalletMover(Request $request)
    {
        $request->validate([
            'tanggal'    => 'nullable|date',
            'nomor_unit' => 'nullable|string|max:50',
            'jenis_p2h'  => 'nullable|string|in:Pallet Mover',
            'shifts'     => 'required|array',
        ]);

        $shifts    = $request->shifts;

        DB::beginTransaction();
        try {
            $checklistFields = [
                'check_air_accu',
                'check_battery',
                'check_body_unit',
                'check_klakson',
                'check_roda',
                'check_sistem_kemudi',
                'check_kebersihan_unit',
                'check_kunci_pm',
                'check_hydraulic'
            ];

            $labelMap = [
                'check_air_accu'        => 'Air Accu',
                'check_battery'         => 'Battery',
                'check_body_unit'       => 'Body Unit',
                'check_klakson'         => 'Klakson',
                'check_roda'            => 'Roda',
                'check_sistem_kemudi'   => 'Sistem Kemudi',
                'check_kebersihan_unit' => 'Kebersihan Unit',
                'check_kunci_pm'        => 'Kunci PM',
                'check_hydraulic'       => 'Hydraulic',
            ];

            $allowedFields = array_merge(
                ['operator_name', 'catatan', 'updated_by'],
                $checklistFields
            );

            // **HANYA PROSES SHIFT YANG ADA DATA**
            foreach ($shifts as $shiftNumber => $data) {
                $id = $data['id'] ?? null;

                $validator = Validator::make($data, [
                    'operator_name' => 'required|string|max:100',
                ]);

                if ($validator->fails()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Shift {$shiftNumber}: " . $validator->errors()->first()
                    ], 422);
                }

                foreach ($checklistFields as $field) {
                    if (!isset($data[$field]) || !in_array($data[$field], ['0', '1'])) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Checklist {$labelMap[$field]} Shift {$shiftNumber} wajib diisi"
                        ], 422);
                    }
                }

                // Logic catatan sama persis
                $hasNok = false;
                $nokFields = [];
                $notes = [];
                $perItemNotes = [];

                foreach ($checklistFields as $field) {
                    if (($data[$field] ?? 1) == 0) {
                        $hasNok = true;
                        $nokFields[] = $labelMap[$field];

                        $note = trim($data[$field . '_note'] ?? '');
                        if ($note !== '') {
                            $perItemNotes[$field] = $note;
                            $notes[] = $labelMap[$field] . ': ' . $note;
                        }
                    }
                }

                $generalNote = trim($data['catatan'] ?? '');
                $usedGeneralParts = [];

                if ($hasNok && count($notes) === 0 && $generalNote !== '') {
                    foreach ($checklistFields as $field) {
                        if (($data[$field] ?? 1) == 0 && empty($perItemNotes[$field])) {
                            $label = $labelMap[$field];
                            $regex = '/' . preg_quote($label, '/') . '\s*[:\-\–]\s*(.+?)(?=\s*[\|\n]|$)/i';
                            if (preg_match($regex, $generalNote, $match)) {
                                $extracted = trim($match[1]);
                                $notes[] = $label . ': ' . $extracted;
                                $usedGeneralParts[] = $match[0];
                            }
                        }
                    }
                }

                $remainingGeneral = $generalNote;
                if (!empty($usedGeneralParts)) {
                    foreach ($usedGeneralParts as $part) {
                        $remainingGeneral = str_ireplace($part, '', $remainingGeneral);
                    }
                }
                $remainingGeneral = trim(preg_replace('/\|\s*\|/', '|', $remainingGeneral));
                $remainingGeneral = trim($remainingGeneral, ' |');

                if ($remainingGeneral !== '') {
                    $notes[] = $remainingGeneral;
                }

                if ($hasNok && empty($notes)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Shift {$shiftNumber}: Ada NOK tapi tidak ada keterangan"
                    ], 422);
                }

                $finalCatatan = implode(' | ', array_filter($notes));

                $payload = collect($data)
                    ->only($allowedFields)
                    ->merge([
                        'catatan'    => $finalCatatan,
                        'updated_by' => Auth::id() ?? 53,
                    ])
                    ->toArray();

                if ($id) {
                    P2HForklfitModel::where('id', $id)->update($payload);
                } else {
                    P2HForklfitModel::create($payload);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'P2H Pallet Mover berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
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

    private function formatSection($section)
    {
        return match ($section) {
            'warehouse_raw_material' => 'Warehouse Raw Material',
            'warehouse_finish_goods' => 'Warehouse Finish Goods',
            'warehouse_co_product'   => 'Warehouse Co Product',
            default => ucwords(str_replace('_', ' ', (string)$section))
        };
    }
}
