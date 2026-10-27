<?php

namespace App\Http\Controllers\Kempu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kempu\MasterKempuRequest;
use App\Models\Kempu\KempuCycleFillingModel;
use App\Models\Kempu\KempuTrackingHistoryModel;
use App\Models\Kempu\MasterKempuModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterKempuController extends Controller
{
    public function index()
    {
        return view('kempu.master.index');
    }

    public function getData(Request $request)
    {
        $status = $request->input('status', 'all');
        $query = MasterKempuModel::with([
            'main',
            'createdBy:id,username,nama_lengkap',
            'updatedBy:id,username,nama_lengkap',
            'printedBy:id,username,nama_lengkap',
        ]);

        if ($status === 'trashed') {
            $query->withTrashed()->where(function ($q) {
                $q->whereNotNull('kempu_master.deleted_at')
                    ->orWhere('kempu_master.status', 'nonaktif');
            });
        } elseif ($status === 'scrap') {
            $query->where(function ($q) {
                $q->where('status', 'scrap')
                    ->orWhere('status', 'damaged')
                    ->orWhere('status', 'nonaktif')
                    ->orWhereHas('main', function ($mq) {
                        $mq->where('current_status', MasterKempuModel::STATUS_SCRAPPED);
                    });
            });
        } elseif ($status === 'active') {
            $query->where('status', 'active')
                ->where('status', '!=', 'nonaktif')
                ->whereDoesntHave('main', function ($mq) {
                    $mq->where('current_status', MasterKempuModel::STATUS_SCRAPPED);
                });
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        $kempuList = $query->latest('id')->get();

        return response()->json([
            'status' => true,
            'data'   => $kempuList,
        ]);
    }

    public function generateId(Request $request)
    {
        $grDate = $request->input('gr_date', date('Y-m-d'));
        $qty    = max(1, (int) $request->input('qty', 1));

        $idKempu = $this->getNextIdKempu($grDate);

        $endId = $idKempu;
        if ($qty > 1 && strlen($idKempu) === 10 && ctype_digit($idKempu)) {
            $prefix = substr($idKempu, 0, 6);
            $startSeq = (int) substr($idKempu, 6);
            $endSeq = $startSeq + $qty - 1;
            $endId = $prefix . str_pad($endSeq, 4, '0', STR_PAD_LEFT);
        }

        return response()->json([
            'status'   => true,
            'gr_date'  => $grDate,
            'id_kempu' => $idKempu,
            'start_id' => $idKempu,
            'qty'      => $qty,
            'count'    => $qty,
            'end_id'   => $endId,
        ]);
    }

    public function getNextIdKempu($grDate)
    {
        try {
            $prefix = Carbon::parse($grDate)->format('ymd'); // Format YYMMDD
        } catch (\Throwable $e) {
            $prefix = Carbon::now()->format('ymd');
        }

        // Ambil nomor urut (4 digit terakhir) tertinggi dari SEMUA kempu standar 10 digit (global increment)
        $existing = MasterKempuModel::withTrashed()
            ->whereRaw('LENGTH(id_kempu) = 10')
            ->pluck('id_kempu');

        $maxSeq = 0;
        foreach ($existing as $id) {
            $cleanId = trim($id);
            if (strlen($cleanId) === 10 && ctype_digit($cleanId)) {
                $seq = (int) substr($cleanId, 6);
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        $nextSeq = $maxSeq + 1;

        // Pastikan tidak terjadi duplikasi jika ada id yang sebelumnya diisi secara manual
        do {
            $candidate = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
            $nextSeq++;
        } while (MasterKempuModel::withTrashed()->where('id_kempu', $candidate)->exists());

        return $candidate;
    }

    public function store(MasterKempuRequest $request)
    {
        try {
            DB::beginTransaction();

            $grDate = $request->filled('gr_date') ? $request->gr_date : date('Y-m-d');
            $noSpb  = $request->filled('no_spb') ? strtoupper(trim($request->no_spb)) : null;

            // 1. JIKA MODE INCOMING BANYAK (BULK GENERATE)
            if ($request->boolean('is_bulk')) {
                $qty = (int) $request->input('qty', 1);
                if ($qty < 1) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Jumlah kempu minimal 1.',
                    ], 422);
                }
                if ($qty > 200) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Jumlah kempu maksimal 200 per satu kali generate.',
                    ], 422);
                }

                try {
                    $prefix = Carbon::parse($grDate)->format('ymd');
                } catch (\Throwable $e) {
                    $prefix = Carbon::now()->format('ymd');
                }

                // Ambil nomor urut tertinggi saat ini dari database
                $existing = MasterKempuModel::withTrashed()
                    ->whereRaw('LENGTH(id_kempu) = 10')
                    ->pluck('id_kempu');

                $maxSeq = 0;
                foreach ($existing as $id) {
                    $cleanId = trim($id);
                    if (strlen($cleanId) === 10 && ctype_digit($cleanId)) {
                        $seq = (int) substr($cleanId, 6);
                        if ($seq > $maxSeq) {
                            $maxSeq = $seq;
                        }
                    }
                }

                $createdKempus = [];
                $keterangan = $request->keterangan;

                for ($i = 0; $i < $qty; $i++) {
                    $nextSeq = $maxSeq + 1;
                    do {
                        $candidate = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
                        $nextSeq++;
                    } while (MasterKempuModel::withTrashed()->where('id_kempu', $candidate)->exists());

                    $maxSeq = $nextSeq - 1;

                    $kempu = MasterKempuModel::create([
                        'id_kempu'   => $candidate,
                        'gr_date'    => $grDate,
                        'no_spb'     => $noSpb,
                        'rfid'       => null,
                        'status'     => $request->status ?? 'active',
                        'keterangan' => $keterangan,
                        'created_by' => Auth::id(),
                    ]);

                    $kempu->main()->updateOrCreate(
                        ['kempu_master_id' => $kempu->id],
                        [
                            'id_kempu'         => $candidate,
                            'current_location' => MasterKempuModel::LOC_WPM,
                            'current_status'   => MasterKempuModel::STATUS_QC_PM_PENDING,
                            'reused_count'     => 0,
                            'max_reused'       => 21,
                            'condition'        => 'OK',
                            'last_action'      => 'Pendaftaran GR Master Kempu (Incoming' . ($noSpb ? " SPB: {$noSpb}" : '') . ')',
                        ]
                    );

                    KempuTrackingHistoryModel::create([
                        'kempu_master_id' => $kempu->id,
                        'id_kempu'        => $kempu->id_kempu,
                        'stage'           => 'MASTER_REGISTRATION',
                        'action'          => 'Pendaftaran GR Master Kempu',
                        'action_result'   => 'REGISTERED',
                        'from_location'   => null,
                        'to_location'     => MasterKempuModel::LOC_WPM,
                        'reused_count'    => 0,
                        'condition'       => 'OK',
                        'notes'           => $keterangan ?? ('Pendaftaran incoming kempu via Master Kempu' . ($noSpb ? " (No SPB: {$noSpb})" : '')),
                        'created_by'      => Auth::id(),
                    ]);

                    $createdKempus[] = $kempu;
                }

                DB::commit();

                $firstId = $createdKempus[0]->id_kempu;
                $lastId  = end($createdKempus)->id_kempu;
                $createdIds = array_map(fn($k) => $k->id, $createdKempus);

                return response()->json([
                    'status'          => true,
                    'is_bulk'         => true,
                    'message'         => "Berhasil men-generate {$qty} Master Kempu ({$firstId} s/d {$lastId})" . ($noSpb ? " untuk No SPB: {$noSpb}." : "."),
                    'generated_count' => $qty,
                    'created_ids'     => $createdIds,
                    'first_id'        => $firstId,
                    'last_id'         => $lastId,
                ], 201);
            }

            // 2. JIKA MODE SATUAN (SINGLE)
            if ($request->filled('id_kempu')) {
                $idKempu = strtoupper(trim($request->id_kempu));
                if (MasterKempuModel::withTrashed()->where('id_kempu', $idKempu)->exists()) {
                    return response()->json([
                        'status'  => false,
                        'message' => "ID Kempu '{$idKempu}' sudah terdaftar dalam sistem. Gunakan ID lain.",
                    ], 422);
                }
            } else {
                $idKempu = $this->getNextIdKempu($grDate);
            }

            $reusedCount = $request->filled('reused_count') ? max(0, min(21, (int) $request->reused_count)) : 0;

            $kempu = MasterKempuModel::create([
                'id_kempu'   => $idKempu,
                'gr_date'    => $grDate,
                'no_spb'     => $noSpb,
                'rfid'       => $request->filled('rfid') ? strtoupper(trim($request->rfid)) : null,
                'status'     => $request->status ?? 'active',
                'keterangan' => $request->keterangan,
                'created_by' => Auth::id(),
            ]);

            // Catat / pastikan state operasional di kempu_main
            $kempu->main()->updateOrCreate(
                ['kempu_master_id' => $kempu->id],
                [
                    'id_kempu'         => $idKempu,
                    'current_location' => MasterKempuModel::LOC_WPM,
                    'current_status'   => MasterKempuModel::STATUS_QC_PM_PENDING,
                    'reused_count'     => $reusedCount,
                    'max_reused'       => 21,
                    'condition'        => 'OK',
                    'last_action'      => 'Pendaftaran GR Master Kempu' . ($noSpb ? " (SPB: {$noSpb})" : ''),
                ]
            );

            // Catat log awal GR
            KempuTrackingHistoryModel::create([
                'kempu_master_id' => $kempu->id,
                'id_kempu'        => $kempu->id_kempu,
                'stage'           => 'MASTER_REGISTRATION',
                'action'          => 'Pendaftaran GR Master Kempu',
                'action_result'   => 'REGISTERED',
                'from_location'   => null,
                'to_location'     => MasterKempuModel::LOC_WPM,
                'reused_count'    => $reusedCount,
                'condition'       => 'OK',
                'notes'           => $request->keterangan ?? ('Pendaftaran kempu baru via Master Kempu' . ($noSpb ? " (No SPB: {$noSpb})" : '')),
                'created_by'      => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'status'      => true,
                'is_bulk'     => false,
                'message'     => "Master Kempu '{$kempu->id_kempu}' berhasil ditambahkan.",
                'data'        => $kempu->load('main'),
                'created_ids' => [$kempu->id],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menyimpan master kempu: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(MasterKempuRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $kempu = MasterKempuModel::withTrashed()->findOrFail($id);

            $updateData = [
                'rfid'       => $request->filled('rfid') ? strtoupper(trim($request->rfid)) : null,
                'no_spb'     => $request->filled('no_spb') ? strtoupper(trim($request->no_spb)) : null,
                'status'     => $request->status ?? 'active',
                'keterangan' => $request->keterangan,
                'updated_by' => Auth::id(),
            ];

            if ($request->filled('gr_date')) {
                $updateData['gr_date'] = $request->gr_date;
            }

            if ($request->filled('id_kempu')) {
                $updateData['id_kempu'] = strtoupper(trim($request->id_kempu));
            }

            $kempu->update($updateData);

            // Update reused_count di kempu_main jika dikirim
            if ($request->has('reused_count') && $request->reused_count !== null) {
                $newReused = max(0, min(21, (int) $request->reused_count));
                $oldReused = $kempu->main?->reused_count ?? 0;

                $cycleFilling = KempuCycleFillingModel::where('kempu_master_id', $kempu->id)
                    ->where('reused_count', $newReused)
                    ->first();

                $mainData = [
                    'reused_count' => $newReused,
                    'has_barcode'  => (bool) ($cycleFilling?->has_barcode ?? true),
                    'has_rfid'     => (bool) ($cycleFilling?->has_rfid ?? true),
                    'has_nti'      => (bool) ($cycleFilling?->has_nti ?? true),
                    'no_po'        => $cycleFilling?->no_po ?? null,
                    'foto_1'       => $cycleFilling?->foto_1 ?? null,
                    'foto_2'       => $cycleFilling?->foto_2 ?? null,
                    'foto_3'       => $cycleFilling?->foto_3 ?? null,
                    'foto_4'       => $cycleFilling?->foto_4 ?? null,
                    'last_action'  => 'Koreksi Reused via Master Kempu oleh ' . (Auth::user()->nama_lengkap ?? Auth::user()->username),
                ];

                if (!$kempu->main) {
                    $mainData['id_kempu']         = $kempu->id_kempu;
                    $mainData['current_location'] = MasterKempuModel::LOC_WPM;
                    $mainData['current_status']   = MasterKempuModel::STATUS_QC_PM_PENDING;
                    $mainData['max_reused']       = 21;
                    $mainData['condition']        = 'OK';
                    $kempu->main()->create($mainData);
                } else {
                    $kempu->main->update($mainData);
                }

                if ($oldReused !== $newReused) {
                    $kempu->recordTracking(
                        stage: 'MASTER_KEMPU',
                        action: 'Koreksi Reused Master',
                        actionResult: 'UPDATED',
                        fromLocation: $kempu->main?->current_location ?? MasterKempuModel::LOC_WPM,
                        toLocation: $kempu->main?->current_location ?? MasterKempuModel::LOC_WPM,
                        condition: $kempu->main?->condition ?? 'OK',
                        notes: "Siklus Reused disesuaikan dari {$oldReused}x menjadi {$newReused}x via Master Kempu",
                        userId: Auth::id()
                    );
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Master Kempu (' . $kempu->id_kempu . ') berhasil diperbarui.',
                'data'    => $kempu->load('main'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memperbarui Master Kempu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Reused Kempu secara cepat / inline
     */
    public function updateReused(Request $request)
    {
        $request->validate([
            'id'           => 'required_without:id_kempu',
            'id_kempu'     => 'required_without:id',
            'reused_count' => 'required|integer|min:0|max:21',
        ], [
            'reused_count.required' => 'Nilai Reused wajib diisi.',
            'reused_count.integer'  => 'Nilai Reused harus berupa bilangan bulat.',
            'reused_count.min'      => 'Nilai Reused minimal 0x.',
            'reused_count.max'      => 'Nilai Reused maksimal 21x.',
        ]);

        try {
            DB::beginTransaction();

            $query = MasterKempuModel::with('main');
            if ($request->filled('id')) {
                $kempu = $query->find($request->id);
            } else {
                $kempu = $query->where('id_kempu', strtoupper(trim($request->id_kempu)))->first();
            }

            if (!$kempu) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data kempu tidak ditemukan.',
                ], 404);
            }

            $newReused = (int) $request->reused_count;
            $oldReused = $kempu->main?->reused_count ?? 0;

            $cycleFilling = KempuCycleFillingModel::where('kempu_master_id', $kempu->id)
                ->where('reused_count', $newReused)
                ->first();

            $mainData = [
                'reused_count' => $newReused,
                'has_barcode'  => (bool) ($cycleFilling?->has_barcode ?? true),
                'has_rfid'     => (bool) ($cycleFilling?->has_rfid ?? true),
                'has_nti'      => (bool) ($cycleFilling?->has_nti ?? true),
                'no_po'        => $cycleFilling?->no_po ?? null,
                'foto_1'       => $cycleFilling?->foto_1 ?? null,
                'foto_2'       => $cycleFilling?->foto_2 ?? null,
                'foto_3'       => $cycleFilling?->foto_3 ?? null,
                'foto_4'       => $cycleFilling?->foto_4 ?? null,
                'last_action'  => 'Koreksi Reused via Master Kempu oleh ' . (Auth::user()->nama_lengkap ?? Auth::user()->username),
            ];

            if (!$kempu->main) {
                $mainData['id_kempu']         = $kempu->id_kempu;
                $mainData['current_location'] = MasterKempuModel::LOC_WPM;
                $mainData['current_status']   = MasterKempuModel::STATUS_QC_PM_PENDING;
                $mainData['max_reused']       = 21;
                $mainData['condition']        = 'OK';
                $kempu->main()->create($mainData);
            } else {
                $kempu->main->update($mainData);
            }

            if ($oldReused !== $newReused) {
                $kempu->recordTracking(
                    stage: 'MASTER_KEMPU',
                    action: 'Koreksi Reused Master',
                    actionResult: 'UPDATED',
                    fromLocation: $kempu->main?->current_location ?? MasterKempuModel::LOC_WPM,
                    toLocation: $kempu->main?->current_location ?? MasterKempuModel::LOC_WPM,
                    condition: $kempu->main?->condition ?? 'OK',
                    notes: "Siklus Reused disesuaikan dari {$oldReused}x menjadi {$newReused}x via Master Kempu",
                    userId: Auth::id()
                );
            }

            DB::commit();

            return response()->json([
                'status'       => true,
                'message'      => "Nilai Reused kempu {$kempu->id_kempu} berhasil diperbarui menjadi {$newReused}x.",
                'reused_count' => $newReused,
                'data'         => [
                    'id'           => $kempu->id,
                    'id_kempu'     => $kempu->id_kempu,
                    'reused_count' => $newReused,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memperbarui nilai reused: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $kempu = MasterKempuModel::findOrFail($id);
            $kempu->update(['status' => 'nonaktif']);
            $kempu->delete();

            // Catat log pelacakan penonaktifan kempu
            KempuTrackingHistoryModel::create([
                'kempu_master_id' => $kempu->id,
                'id_kempu'        => $kempu->id_kempu,
                'stage'           => 'MASTER_REGISTRATION',
                'action'          => 'Penonaktifan Master Kempu',
                'action_result'   => 'DEACTIVATED',
                'from_location'   => $kempu->main?->current_location ?? MasterKempuModel::LOC_WPM,
                'to_location'     => MasterKempuModel::LOC_SCRAP,
                'notes'           => 'Master kempu dinonaktifkan (dihapus).',
                'created_by'      => Auth::id(),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Master Kempu berhasil dinonaktifkan (dihapus).',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $kempu = MasterKempuModel::withTrashed()->findOrFail($id);
            if ($kempu->trashed()) {
                $kempu->restore();
            }
            $kempu->update(['status' => 'active']);

            if ($kempu->main && $kempu->main->current_status === MasterKempuModel::STATUS_SCRAPPED) {
                $kempu->main->update([
                    'current_status'   => MasterKempuModel::STATUS_QC_PM_PENDING,
                    'current_location' => MasterKempuModel::LOC_WPM,
                    'condition'        => 'OK',
                    'last_action'      => 'Dipulihkan dari Nonaktif',
                ]);
            }

            // Catat log pelacakan pemulihan kempu
            KempuTrackingHistoryModel::create([
                'kempu_master_id' => $kempu->id,
                'id_kempu'        => $kempu->id_kempu,
                'stage'           => 'MASTER_REGISTRATION',
                'action'          => 'Pemulihan Master Kempu',
                'action_result'   => 'RESTORED',
                'from_location'   => MasterKempuModel::LOC_SCRAP,
                'to_location'     => MasterKempuModel::LOC_WPM,
                'notes'           => 'Master kempu dipulihkan ke status aktif.',
                'created_by'      => Auth::id(),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Master Kempu berhasil dipulihkan.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memulihkan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function forceDelete($id)
    {
        try {
            $kempu = MasterKempuModel::withTrashed()->findOrFail($id);
            $kempu->forceDelete();

            return response()->json([
                'status'  => true,
                'message' => 'Master Kempu berhasil dihapus permanen.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menghapus data permanen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Nonaktifkan (Soft Delete) banyak kempu sekaligus
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'status'  => false,
                'message' => 'Pilih minimal satu kempu untuk dinonaktifkan.',
            ], 422);
        }

        try {
            DB::beginTransaction();
            $kempus = MasterKempuModel::whereIn('id', $ids)->get();
            $count = 0;

            foreach ($kempus as $kempu) {
                $kempu->update(['status' => 'nonaktif']);
                $kempu->delete();

                KempuTrackingHistoryModel::create([
                    'kempu_master_id' => $kempu->id,
                    'id_kempu'        => $kempu->id_kempu,
                    'stage'           => 'MASTER_REGISTRATION',
                    'action'          => 'Penonaktifan Master Kempu (Massal)',
                    'action_result'   => 'DEACTIVATED',
                    'from_location'   => $kempu->main?->current_location ?? MasterKempuModel::LOC_WPM,
                    'to_location'     => MasterKempuModel::LOC_SCRAP,
                    'notes'           => 'Master kempu dinonaktifkan secara massal.',
                    'created_by'      => Auth::id(),
                ]);
                $count++;
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Berhasil menonaktifkan {$count} Master Kempu.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menonaktifkan data kempu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pulihkan banyak kempu sekaligus
     */
    public function bulkRestore(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'status'  => false,
                'message' => 'Pilih minimal satu kempu untuk dipulihkan.',
            ], 422);
        }

        try {
            DB::beginTransaction();
            $kempus = MasterKempuModel::withTrashed()->whereIn('id', $ids)->get();
            $count = 0;

            foreach ($kempus as $kempu) {
                if ($kempu->trashed()) {
                    $kempu->restore();
                }
                $kempu->update(['status' => 'active']);

                if ($kempu->main && $kempu->main->current_status === MasterKempuModel::STATUS_SCRAPPED) {
                    $kempu->main->update([
                        'current_status'   => MasterKempuModel::STATUS_QC_PM_PENDING,
                        'current_location' => MasterKempuModel::LOC_WPM,
                        'condition'        => 'OK',
                        'last_action'      => 'Dipulihkan dari Nonaktif (Massal)',
                    ]);
                }

                KempuTrackingHistoryModel::create([
                    'kempu_master_id' => $kempu->id,
                    'id_kempu'        => $kempu->id_kempu,
                    'stage'           => 'MASTER_REGISTRATION',
                    'action'          => 'Pemulihan Master Kempu (Massal)',
                    'action_result'   => 'RESTORED',
                    'from_location'   => MasterKempuModel::LOC_SCRAP,
                    'to_location'     => MasterKempuModel::LOC_WPM,
                    'notes'           => 'Master kempu dipulihkan ke status aktif secara massal.',
                    'created_by'      => Auth::id(),
                ]);
                $count++;
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Berhasil memulihkan {$count} Master Kempu.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memulihkan data kempu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus permanen banyak kempu sekaligus
     */
    public function bulkForceDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'status'  => false,
                'message' => 'Pilih minimal satu kempu untuk dihapus permanen.',
            ], 422);
        }

        try {
            DB::beginTransaction();
            $kempus = MasterKempuModel::withTrashed()->whereIn('id', $ids)->get();
            $count = $kempus->count();

            foreach ($kempus as $kempu) {
                $kempu->main()?->delete();
                $kempu->forceDelete();
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Berhasil menghapus permanen {$count} Master Kempu.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menghapus data kempu secara permanen: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function printQr(Request $request)
    {
        $ids = $request->input('ids');
        $all = $request->boolean('all');

        $query = MasterKempuModel::with('printedBy');

        if (!$all && !empty($ids)) {
            $idArray = is_array($ids) ? $ids : explode(',', $ids);
            $query->whereIn('id', $idArray);
        }

        $kempuList = $query->orderBy('id_kempu')->get();

        if ($kempuList->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data kempu yang dipilih untuk dicetak.');
        }

        return view('kempu.master.print_qr', compact('kempuList'));
    }

    public function recordPrint(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return response()->json([
                'status'  => false,
                'message' => 'Tidak ada data kempu yang dipilih.',
            ], 422);
        }

        $idArray = is_array($ids) ? $ids : explode(',', $ids);
        $kempuList = MasterKempuModel::whereIn('id', $idArray)->get();

        $currentUser = Auth::user();
        $now = Carbon::now();
        $updatedItems = [];

        foreach ($kempuList as $kempu) {
            $newCount = ($kempu->print_count ?? 0) + 1;
            $kempu->update([
                'print_count' => $newCount,
                'printed_at'  => $now,
                'printed_by'  => $currentUser?->id,
            ]);

            // Catat log cetak label ke riwayat tracking kempu
            $kempu->recordTracking(
                'MASTER',
                'PRINT_LABEL_QR',
                'OK',
                null,
                $kempu->current_location,
                $kempu->condition,
                "Cetak Label QR Kempu ke-{$newCount} oleh " . ($currentUser?->nama_lengkap ?? $currentUser?->username ?? 'System'),
                $currentUser?->id,
                [
                    'print_count'  => $newCount,
                    'printed_at'   => $now->toDateTimeString(),
                    'printed_by'   => $currentUser?->id,
                    'printer_name' => $currentUser?->nama_lengkap ?? $currentUser?->username ?? 'System',
                ]
            );

            $updatedItems[] = [
                'id'          => $kempu->id,
                'print_count' => $newCount,
                'printed_at'  => $now->format('d/m/Y H:i:s'),
                'printed_by'  => $currentUser?->nama_lengkap ?? $currentUser?->username ?? 'SYSTEM',
            ];
        }

        return response()->json([
            'status'  => true,
            'message' => 'Riwayat cetak berhasil dicatat.',
            'items'   => $updatedItems,
        ]);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Master Kempu');

        // Header
        $headers = [
            'A1' => 'ID Kempu (Opsional)',
            'B1' => 'GR Date (YYYY-MM-DD)',
            'C1' => 'No SPB (Opsional)',
            'D1' => 'RFID (Opsional)',
            'E1' => 'Status',
            'F1' => 'Keterangan',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style Header
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0');

        // Contoh Data Baris 2, 3, 4
        $today = date('Y-m-d');
        $sheet->setCellValue('A2', '');
        $sheet->setCellValue('B2', $today);
        $sheet->setCellValue('C2', '9000087877');
        $sheet->setCellValue('D2', 'RFID-882190');
        $sheet->setCellValue('E2', 'active');
        $sheet->setCellValue('F2', 'Kempu Baru (Format YYMMDD####)');

        $sheet->setCellValue('A3', '10599');
        $sheet->setCellValue('B3', '2025-12-19');
        $sheet->setCellValue('C3', '900087878');
        $sheet->setCellValue('D3', '');
        $sheet->setCellValue('E3', 'active');
        $sheet->setCellValue('F3', 'Kempu Lama (Bisa isi ID kempu lama di kolom A)');

        $sheet->setCellValue('A4', '');
        $sheet->setCellValue('B4', $today);
        $sheet->setCellValue('C4', '9000087879');
        $sheet->setCellValue('D4', '');
        $sheet->setCellValue('E4', 'active');
        $sheet->setCellValue('F4', 'Kosongkan ID Kempu jika ingin auto-generate format YYMMDD');

        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_master_kempu.xlsx"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:5120',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes'    => 'Format file harus berupa Excel (.xls / .xlsx).',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('file'));
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Hapus baris header
            unset($rows[0]);

            $errors = [];
            $payload = [];
            $kodesInExcel = [];

            // Ambil semua id_kempu yang sudah ada di database dan cari nomor urut global tertinggi
            $existingIds = MasterKempuModel::withTrashed()->pluck('id_kempu')->map(fn($k) => strtoupper(trim($k)))->toArray();
            $globalMaxSeq = 0;
            foreach ($existingIds as $exId) {
                if (strlen($exId) === 10 && ctype_digit($exId)) {
                    $suf = (int) substr($exId, 6);
                    if ($suf > $globalMaxSeq) {
                        $globalMaxSeq = $suf;
                    }
                }
            }

            foreach ($rows as $i => $row) {
                $line = $i + 1;

                // Cek apakah template 6 kolom (ada No SPB) atau template lama 5 kolom
                if (count($row) >= 6) {
                    $idKempuRaw = strtoupper(trim((string)($row[0] ?? '')));
                    $grDateRaw  = trim((string)($row[1] ?? ''));
                    $noSpbRaw   = strtoupper(trim((string)($row[2] ?? '')));
                    $rfidRaw    = strtoupper(trim((string)($row[3] ?? '')));
                    $status     = strtolower(trim((string)($row[4] ?? '')));
                    $keterangan = trim((string)($row[5] ?? ''));
                } else {
                    $idKempuRaw = strtoupper(trim((string)($row[0] ?? '')));
                    $grDateRaw  = trim((string)($row[1] ?? ''));
                    $noSpbRaw   = null;
                    $rfidRaw    = strtoupper(trim((string)($row[2] ?? '')));
                    $status     = strtolower(trim((string)($row[3] ?? '')));
                    $keterangan = trim((string)($row[4] ?? ''));
                }

                // Lewati baris kosong
                if ($idKempuRaw === '' && $grDateRaw === '' && $rfidRaw === '' && ($noSpbRaw ?? '') === '') {
                    continue;
                }

                // Parse GR Date
                $grDate = null;
                if ($grDateRaw !== '') {
                    try {
                        if (is_numeric($grDateRaw)) {
                            $grDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($grDateRaw)->format('Y-m-d');
                        } else {
                            $grDate = Carbon::parse($grDateRaw)->format('Y-m-d');
                        }
                    } catch (\Throwable $e) {
                        $grDate = date('Y-m-d');
                    }
                } else {
                    $grDate = date('Y-m-d');
                }

                $datePrefix = Carbon::parse($grDate)->format('ymd'); // YYMMDD

                // Tentukan ID Kempu
                if ($idKempuRaw !== '') {
                    $idKempu = $idKempuRaw;

                    if (in_array($idKempu, $kodesInExcel)) {
                        $errors[] = "Baris {$line}: ID Kempu '{$idKempu}' duplikat di dalam file Excel.";
                        continue;
                    }
                    $kodesInExcel[] = $idKempu;

                    if (in_array($idKempu, $existingIds)) {
                        $errors[] = "Baris {$line}: ID Kempu '{$idKempu}' sudah terdaftar di sistem.";
                        continue;
                    }

                    if (strlen($idKempu) === 10 && ctype_digit($idKempu)) {
                        $suf = (int) substr($idKempu, 6);
                        $globalMaxSeq = max($globalMaxSeq, $suf);
                    }
                } else {
                    // Auto-generate ID Kempu dengan format YYMMDD#### dengan nomor urut yang selalu increment
                    do {
                        $globalMaxSeq++;
                        $candidate = $datePrefix . str_pad($globalMaxSeq, 4, '0', STR_PAD_LEFT);
                    } while (in_array($candidate, $existingIds) || in_array($candidate, $kodesInExcel));

                    $idKempu = $candidate;
                    $kodesInExcel[] = $idKempu;
                }

                // Validasi Status
                $validStatuses = ['active', 'in_use', 'maintenance', 'damaged', 'scrap'];
                if (!in_array($status, $validStatuses)) {
                    $status = 'active';
                }

                $payload[] = [
                    'id_kempu'   => $idKempu,
                    'gr_date'    => $grDate,
                    'no_spb'     => $noSpbRaw ?: null,
                    'rfid'       => $rfidRaw ?: null,
                    'status'     => $status,
                    'keterangan' => $keterangan ?: null,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($payload) && empty($errors)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'File Excel kosong atau tidak memiliki data yang valid.',
                ], 422);
            }

            if (!empty($errors)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Ditemukan kesalahan pada file Excel. Silakan periksa kembali.',
                    'errors'  => $errors,
                ], 422);
            }

            DB::transaction(function () use ($payload) {
                foreach ($payload as $item) {
                    $k = MasterKempuModel::create($item);
                    $k->main()->updateOrCreate(
                        ['kempu_master_id' => $k->id],
                        [
                            'id_kempu'         => $k->id_kempu,
                            'current_location' => MasterKempuModel::LOC_WPM,
                            'current_status'   => MasterKempuModel::STATUS_QC_PM_PENDING,
                            'reused_count'     => 0,
                            'max_reused'       => 21,
                            'condition'        => 'OK',
                            'last_action'      => 'Pendaftaran GR (Import Excel)',
                        ]
                    );
                    $k->recordTracking(
                        'GR',
                        'PENDAFTARAN_GR_EXCEL',
                        'OK',
                        null,
                        MasterKempuModel::LOC_WPM,
                        'OK',
                        'Pendaftaran Good Receipt Master Kempu via Import Excel.',
                        Auth::id()
                    );
                }
            });

            return response()->json([
                'status'  => true,
                'message' => 'Upload berhasil! ' . count($payload) . ' data kempu berhasil disimpan.',
                'total'   => count($payload),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memproses file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download Template Excel untuk Update Nilai Reused
     */
    public function downloadTemplateReused()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Update Reused');

        // Header
        $headers = [
            'A1' => 'ID Kempu (Wajib)',
            'B1' => 'Nilai Reused (0 - 21)',
            'C1' => 'Catatan / Alasan Koreksi (Opsional)',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style Header
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD1E7DD');

        $sheet->getStyle('A1:C1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Contoh Data
        $sheet->setCellValueExplicit('A2', '2609220001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B2', 5);
        $sheet->setCellValue('C2', 'Penyesuaian stok opname fisik kempu');

        $sheet->setCellValueExplicit('A3', '2609220002', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B3', 12);
        $sheet->setCellValue('C3', 'Koreksi siklus pemakaian');

        $sheet->setCellValueExplicit('A4', '10599', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B4', 18);
        $sheet->setCellValue('C4', 'Kempu lama sinkronisasi fisik');

        // Auto size columns
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_update_reused_kempu.xlsx"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Upload File Excel / CSV untuk Memperbarui Nilai Reused dengan mencocokkan ID Kempu
     */
    public function uploadReused(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv|max:5120',
        ], [
            'file.required' => 'File Excel / CSV wajib diunggah.',
            'file.mimes'    => 'Format file harus berupa Excel (.xls / .xlsx) atau CSV (.csv).',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('file'));
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Hapus baris header
            unset($rows[0]);

            $errors = [];
            $kodesInExcel = [];
            $cleanRows = [];

            foreach ($rows as $i => $row) {
                $line = $i + 1;
                $idKempuRaw   = strtoupper(trim((string)($row[0] ?? '')));
                $reusedRaw    = trim((string)($row[1] ?? ''));
                $keterangan   = trim((string)($row[2] ?? ''));

                // Lewati jika seluruh baris kosong
                if ($idKempuRaw === '' && $reusedRaw === '' && $keterangan === '') {
                    continue;
                }

                if ($idKempuRaw === '') {
                    $errors[] = "Baris {$line}: Kolom ID Kempu tidak boleh kosong.";
                    continue;
                }

                if ($reusedRaw === '' || !is_numeric($reusedRaw)) {
                    $errors[] = "Baris {$line}: Nilai Reused untuk kempu '{$idKempuRaw}' harus berupa bilangan bulat antara 0 dan 21x.";
                    continue;
                }

                $reusedInt = (int) $reusedRaw;
                if ($reusedInt < 0 || $reusedInt > 21) {
                    $errors[] = "Baris {$line}: Nilai Reused ({$reusedRaw}x) pada kempu '{$idKempuRaw}' tidak valid (harus 0 - 21x).";
                    continue;
                }

                if (in_array($idKempuRaw, $kodesInExcel)) {
                    $errors[] = "Baris {$line}: ID Kempu '{$idKempuRaw}' terduplikasi di dalam file.";
                    continue;
                }
                $kodesInExcel[] = $idKempuRaw;

                $cleanRows[] = [
                    'line'       => $line,
                    'id_kempu'   => $idKempuRaw,
                    'reused'     => $reusedInt,
                    'keterangan' => $keterangan,
                ];
            }

            if (empty($cleanRows) && empty($errors)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'File Excel / CSV kosong atau tidak memiliki data yang valid.',
                ], 422);
            }

            if (!empty($errors)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Ditemukan kesalahan pada file. Silakan periksa data berikut:',
                    'errors'  => $errors,
                ], 422);
            }

            // Ambil data kempu yang cocok dari database
            $ids = array_column($cleanRows, 'id_kempu');
            $kempuList = MasterKempuModel::withTrashed()
                ->with('main')
                ->whereIn('id_kempu', $ids)
                ->get()
                ->keyBy(fn($k) => strtoupper(trim($k->id_kempu)));

            // Validasi apakah setiap ID Kempu ada di database
            foreach ($cleanRows as $item) {
                if (!$kempuList->has($item['id_kempu'])) {
                    $errors[] = "Baris {$item['line']}: ID Kempu '{$item['id_kempu']}' tidak ditemukan di sistem master.";
                }
            }

            if (!empty($errors)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Beberapa ID Kempu tidak ditemukan di sistem master. Pastikan ID Kempu sudah terdaftar.',
                    'errors'  => $errors,
                ], 422);
            }

            // Eksekusi update dalam transaksi database
            $userName = Auth::user()->nama_lengkap ?? Auth::user()->username ?? 'User';
            $successCount = 0;

            DB::transaction(function () use ($cleanRows, $kempuList, $userName, &$successCount) {
                foreach ($cleanRows as $item) {
                    $kempu = $kempuList->get($item['id_kempu']);
                    $newReused = $item['reused'];
                    $oldReused = $kempu->main?->reused_count ?? 0;

                    $cycleFilling = KempuCycleFillingModel::where('kempu_master_id', $kempu->id)
                        ->where('reused_count', $newReused)
                        ->first();

                    $mainData = [
                        'reused_count' => $newReused,
                        'has_barcode'  => (bool) ($cycleFilling?->has_barcode ?? true),
                        'has_rfid'     => (bool) ($cycleFilling?->has_rfid ?? true),
                        'has_nti'      => (bool) ($cycleFilling?->has_nti ?? true),
                        'no_po'        => $cycleFilling?->no_po ?? null,
                        'foto_1'       => $cycleFilling?->foto_1 ?? null,
                        'foto_2'       => $cycleFilling?->foto_2 ?? null,
                        'foto_3'       => $cycleFilling?->foto_3 ?? null,
                        'foto_4'       => $cycleFilling?->foto_4 ?? null,
                        'last_action'  => 'Upload Massal Reused oleh ' . $userName,
                    ];

                    if (!$kempu->main) {
                        $mainData['id_kempu']         = $kempu->id_kempu;
                        $mainData['current_location'] = MasterKempuModel::LOC_WPM;
                        $mainData['current_status']   = MasterKempuModel::STATUS_QC_PM_PENDING;
                        $mainData['max_reused']       = 21;
                        $mainData['condition']        = 'OK';
                        $kempu->main()->create($mainData);
                    } else {
                        $kempu->main->update($mainData);
                    }

                    if ($oldReused !== $newReused) {
                        $notesDesc = !empty($item['keterangan']) ? " ({$item['keterangan']})" : "";
                        $kempu->recordTracking(
                            stage: 'MASTER_KEMPU',
                            action: 'Koreksi Reused via Upload Excel',
                            actionResult: 'UPDATED',
                            fromLocation: $kempu->main?->current_location ?? MasterKempuModel::LOC_WPM,
                            toLocation: $kempu->main?->current_location ?? MasterKempuModel::LOC_WPM,
                            condition: $kempu->main?->condition ?? 'OK',
                            notes: "Siklus Reused disesuaikan dari {$oldReused}x menjadi {$newReused}x via Upload Massal Excel{$notesDesc}",
                            userId: Auth::id()
                        );
                    }

                    $successCount++;
                }
            });

            return response()->json([
                'status'  => true,
                'message' => "Upload berhasil! Sebanyak {$successCount} data kempu berhasil diperbarui nilai reused-nya.",
                'total'   => $successCount,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memproses file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export Data Master Kempu ke Excel (.xlsx)
     * Kolom: ID Kempu, No SPB, Tanggal GR, RFID, Reused, Status
     */
    public function export(Request $request)
    {
        $status = $request->input('status', 'all');
        $ids    = $request->input('ids');

        $query = MasterKempuModel::with('main');

        if (!empty($ids)) {
            $idArray = is_array($ids) ? $ids : explode(',', $ids);
            $query->withTrashed()->whereIn('id', $idArray);
        } else {
            if ($status === 'trashed') {
                $query->withTrashed()->where(function ($q) {
                    $q->whereNotNull('kempu_master.deleted_at')
                        ->orWhere('kempu_master.status', 'nonaktif');
                });
            } elseif ($status === 'scrap') {
                $query->where(function ($q) {
                    $q->where('status', 'scrap')
                        ->orWhere('status', 'damaged')
                        ->orWhere('status', 'nonaktif')
                        ->orWhereHas('main', function ($mq) {
                            $mq->where('current_status', MasterKempuModel::STATUS_SCRAPPED);
                        });
                });
            } elseif ($status === 'active') {
                $query->where('status', 'active')
                    ->where('status', '!=', 'nonaktif')
                    ->whereDoesntHave('main', function ($mq) {
                        $mq->where('current_status', MasterKempuModel::STATUS_SCRAPPED);
                    });
            } elseif ($status === 'all') {
                $query->withTrashed();
            }
        }

        $records = $query->orderBy('id_kempu')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Master Kempu');

        // Headers: id kempu, spb, tgl gr, rfid, reused, status
        $headers = [
            'A1' => 'ID Kempu',
            'B1' => 'No SPB',
            'C1' => 'Tanggal GR',
            'D1' => 'RFID',
            'E1' => 'Reused',
            'F1' => 'Status',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Header Style
        $sheet->getStyle('A1:F1')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1E3A8A'); // Primary Dark Blue
        $sheet->getStyle('A1:F1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $rowIdx = 2;
        foreach ($records as $item) {
            $isTrashed = $item->trashed() || $item->status === 'nonaktif';
            $statusLabel = $isTrashed ? 'Nonaktif' : ucfirst($item->status ?: 'Active');
            if (!$isTrashed && ($item->status === 'scrap' || $item->main?->current_status === MasterKempuModel::STATUS_SCRAPPED)) {
                $statusLabel = 'Scrap';
            }

            $grDate = $item->gr_date ? Carbon::parse($item->gr_date)->format('Y-m-d') : '-';
            $reused = (int) ($item->main?->reused_count ?? $item->reused_count ?? 0);

            $sheet->setCellValueExplicit('A' . $rowIdx, (string) $item->id_kempu, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B' . $rowIdx, (string) ($item->no_spb ?: '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowIdx, $grDate);
            $sheet->setCellValueExplicit('D' . $rowIdx, (string) ($item->rfid ?: '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowIdx, $reused);
            $sheet->setCellValue('F' . $rowIdx, $statusLabel);

            // Alignment
            $sheet->getStyle("A{$rowIdx}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("B{$rowIdx}:F{$rowIdx}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $rowIdx++;
        }

        $lastRow = max(2, $rowIdx - 1);
        $sheet->getStyle("A1:F{$lastRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setARGB('FFCBD5E1');

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'export_master_kempu_' . date('Ymd_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
