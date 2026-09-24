<?php

namespace App\Http\Controllers\Kempu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kempu\MasterKempuRequest;
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
            $query->onlyTrashed();
        } elseif ($status === 'scrap') {
            $query->where(function ($q) {
                $q->where('status', 'scrap')
                    ->orWhere('status', 'damaged')
                    ->orWhereHas('main', function ($mq) {
                        $mq->where('current_status', MasterKempuModel::STATUS_SCRAPPED);
                    });
            });
        } elseif ($status === 'active') {
            $query->where('status', 'active')
                ->whereDoesntHave('main', function ($mq) {
                    $mq->where('current_status', MasterKempuModel::STATUS_SCRAPPED);
                });
        }
        // 'all' loads all non-deleted records

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
                    'reused_count'     => 0,
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
                'reused_count'    => 0,
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

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Master Kempu (' . $kempu->id_kempu . ') berhasil diperbarui.',
                'data'    => $kempu,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memperbarui Master Kempu: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $kempu = MasterKempuModel::findOrFail($id);
            $kempu->delete();

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
            $kempu = MasterKempuModel::onlyTrashed()->findOrFail($id);
            $kempu->restore();

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
            $kempu = MasterKempuModel::onlyTrashed()->findOrFail($id);
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
}
