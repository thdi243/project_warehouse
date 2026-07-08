<?php

namespace App\Http\Controllers\Wfg\stock_opname;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Wfg\stock_opname\WfgSopModel;
use Illuminate\Validation\ValidationException;
use App\Models\Wfg\BarangWfgModel;
use App\Models\Wfg\stock_opname\StockOnHandModel;
use App\Models\Wfg\stock_opname\WfgSopDetailModel;
use App\Models\Wfg\stock_opname\WfgSopStatusModel;
use App\Models\Wfg\stock_opname\WfgSopSummariesModel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Wsp\StockOnHandModel as WspStockOnHandModel;

class StockOnHandWfgController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:wfg_barang,id',
            'unrest' => 'nullable|integer|min:0',
            'qi' => 'nullable|integer|min:0',
            'block' => 'nullable|integer|min:0',
        ]);

        try {
            $barang = BarangWfgModel::find($request->barang_id);

            // Pastikan barang ditemukan
            if (!$barang) {
                return response()->json([
                    'status' => false,
                    'message' => 'Barang tidak ditemukan di master data.',
                ], 404);
            }

            // Ambil principal dari master barang
            $principal = $barang->principal;

            // Kalau principal kosong di master barang
            if (empty($principal)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Principal belum diisi di master barang. Harap lengkapi data barang terlebih dahulu.',
                ], 422);
            }

            // Cegah input ganda untuk hari yang sama
            $exists = StockOnHandModel::where('barang_id', $request->barang_id)
                ->whereDate('last_updated', now()->toDateString())
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data Mid Barang SOH untuk barang ini sudah diinput hari ini!',
                ], 409);
            }

            // Hitung total SOH
            $unrest = (int)($request->unrest ?? 0);
            $qi     = (int)($request->qi ?? 0);
            $block  = (int)($request->block ?? 0);
            $qty_soh = $unrest + $qi + $block;

            // Simpan ke database
            $soh = StockOnHandModel::create([
                'barang_id'    => $barang->id,
                'user_id'      => Auth::id() ?? 1,
                'qty_soh'      => $qty_soh,
                'qty_unrest'   => $unrest,
                'qty_qi'       => $qi,
                'qty_block'    => $block,
                'last_updated' => now(),
                'principal'    => $principal, // 🔹 langsung dari master barang
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Stock On Hand berhasil ditambahkan',
                'data'    => $soh,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menambahkan Stock On Hand',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $soh = StockOnHandModel::with('barang:id,mid_barang') // kalau kamu punya relasi ke tabel barang
            ->find($id);

        if (!$soh) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data SOH tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $soh
        ]);
    }

    public function getList(Request $request)
    {
        $searchTerm = $request->input('search');
        $principalFilter = $request->input('principal');
        $perPage = 100;
        $today = now()->toDateString();
        $user = Auth::user();

        $query = StockOnHandModel::query()
            ->select('wfg_soh.*')
            ->leftJoin('wfg_barang', 'wfg_soh.barang_id', '=', 'wfg_barang.id')
            ->leftJoin('users', 'wfg_soh.user_id', '=', 'users.id');

        // Filter tanggal
        $query->whereDate('wfg_soh.last_updated', $today);

        // Filter principal jika operator
        if ($user->jabatan === 'operator') {
            $userPrincipal = $user->principal?->principal;

            if ($userPrincipal === 'SMU') {
                $query->where('wfg_barang.principal', '!=', 'BAS');
            } elseif ($userPrincipal) {
                $query->where('wfg_barang.principal', $userPrincipal);
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            if ($principalFilter) {
                $query->where('wfg_barang.principal', $principalFilter);
            }
        }

        // Filter search
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('wfg_barang.mid_barang', 'like', '%' . $searchTerm . '%');
            });
        }

        // Ambil data relasi
        $query->with([
            'barang:id,mid_barang,qty_box,principal',
            'user:id,username'
        ]);

        $data = $query->orderBy('wfg_soh.id', 'desc')
            ->paginate($perPage);

        return response()->json($data);
    }


    public function getBarang()
    {
        $user = Auth::user();

        $query = BarangWfgModel::select('id', 'mid_barang', 'nama_barang', 'principal');

        // Jika user operator, filter berdasarkan principal mereka
        if ($user->jabatan === 'operator') {
            $userPrincipal = $user->principal?->principal;

            if ($userPrincipal === 'SMU') {
                // SMU bisa lihat semua principal kecuali BAS
                $query->where('wfg_barang.principal', '!=', 'BAS');
            } elseif ($userPrincipal) {
                // Operator biasa hanya lihat principalnya sendiri
                $query->where('wfg_barang.principal', $userPrincipal);
            } else {
                // Tidak punya principal → tidak ada data
                $query->whereRaw('1 = 0');
            }
        }

        $barang = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $soh = StockOnHandModel::findOrFail($id);
            $user = Auth::user();

            $request->validate([
                'unrest' => 'nullable|integer',
                'qi' => 'nullable|integer',
                'block' => 'nullable|integer',
            ]);

            if ($user->jabatan === 'operator') {
                $principal = $user->principal?->principal ?? null;

                if (empty($principal)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Akun operator belum memiliki principal. Hubungi admin untuk melengkapi data user.',
                    ], 422);
                }
            } else {
                // Non-operator bisa kirim principal lewat request, atau pakai existing
                $principal = $request->input('principal', $soh->principal ?? $user->principal ?? null);
            }

            $unrest = $request->unrest ?? 0;
            $qi = $request->qi ?? 0;
            $block = $request->block ?? 0;
            $qty_soh = $unrest + $qi + $block;

            $soh->update([
                'qty_soh' => $qty_soh ?? $soh->qty_soh,
                'qty_unrest' => $unrest ?? $soh->qty_unrest,
                'qty_qi' => $qi ?? $soh->qty_qi,
                'qty_block' => $block ?? $soh->qty_block,
                'user_id' => Auth::id() ?? $soh->user_id,
                'last_updated' => now()
            ]);

            $today = Carbon::today()->toDateString();

            // Cari SOP yang masih aktif
            $sop = WfgSopModel::whereDate('tgl_opname', $today)
                ->where('principal', $principal)
                ->first();

            if ($sop) {
                $summary = WfgSopSummariesModel::where('sop_id', $sop->id)
                    ->where('barang_id', $soh->barang_id)
                    ->first();

                if ($summary) {
                    $qtySistem = $qty_soh;
                    $qtyFisik = $summary->qty_fisik ?? 0;
                    $selisih = $qtyFisik - $qtySistem;
                    $status = $selisih > 0 ? 'kurang' : ($selisih < 0 ? 'lebih' : 'match');

                    $summary->update([
                        'qty_sistem' => $qtySistem,
                        'selisih'    => $selisih,
                        'status'     => $status,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Stock On Hand berhasil diperbarui',
                'data' => $soh
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memperbarui Stock On Hand',
                'error' => $e->getMessage() // optional, untuk debugging
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $soh = StockOnHandModel::findOrFail($id);
        $soh->delete();
        return response()->json([
            'status' => true,
            'message' => 'Stock On Hand berhasil dihapus'
        ]);
    }

    public function resetAll()
    {
        try {
            $user = Auth::user();
            $today = now()->toDateString();

            // Jika operator → hapus hanya data hari ini milik principal-nya
            if ($user->jabatan === 'operator') {
                $principal = $user->principal?->principal ?? null;

                if (!$principal) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Akun operator tidak memiliki principal. Tidak dapat menghapus data.'
                    ], 422);
                }

                $deleted = StockOnHandModel::where('principal', $principal)
                    ->whereDate('last_updated', $today)
                    ->delete();
            } else {
                // Admin atau non-operator bisa hapus semua principal untuk hari ini
                $deleted = StockOnHandModel::whereDate('last_updated', $today)->delete();
            }

            if ($deleted === 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'Tidak ada data SOH yang dihapus untuk hari ini.'
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => "Berhasil menghapus $deleted data SOH untuk tanggal $today."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data SOH hari ini.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Import dair Excel
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();

            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $header = [];
            $countSuccess = 0;
            $notFound = [];
            $incompletePrincipal = [];
            $validData = [];
            $today = now()->toDateString();

            foreach ($rows as $index => $row) {
                if ($index == 1) {
                    $header = array_map(fn($h) => strtolower(trim($h)), $row);
                    $requiredHeaders = ['mid_barang', 'unrest', 'qual_insp', 'blocked'];
                    $missing = array_diff($requiredHeaders, $header);

                    if (!empty($missing)) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Format file Excel tidak sesuai. Kolom berikut hilang: ' . implode(', ', $missing)
                        ], 422);
                    }
                    continue;
                }

                if (empty($row['A'])) continue;

                $data = array_combine($header, array_map('trim', $row));

                if (empty($data['mid_barang'])) continue;

                // Cari barang berdasarkan MID Barang
                $barang = BarangWfgModel::where('mid_barang', $data['mid_barang'])->first();

                if (!$barang) {
                    $notFound[] = $data['mid_barang'];
                    continue;
                }

                // Cek principal dari master barang
                if (empty($barang->principal)) {
                    $incompletePrincipal[] = $data['mid_barang'];
                    continue;
                }

                $validData[] = [
                    'barang' => $barang,
                    'data'   => $data,
                ];
            }

            // Jika ada barang yang tidak ditemukan
            if (!empty($notFound)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Beberapa MID Barang tidak ditemukan di master barang.',
                    'not_found' => $notFound
                ], 422);
            }

            // Jika ada barang tanpa principal
            if (!empty($incompletePrincipal)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Beberapa barang belum memiliki principal di master data. Lengkapi terlebih dahulu:',
                    'missing_principal' => $incompletePrincipal
                ], 422);
            }

            // Simpan semua data valid
            foreach ($validData as $item) {
                $barang = $item['barang'];
                $data = $item['data'];

                $exists = StockOnHandModel::where('barang_id', $barang->id)
                    ->whereDate('created_at', $today)
                    ->exists();

                if ($exists) continue;

                $unrest     = (float)($data['unrest'] ?? 0);
                $qual_insp  = (float)($data['qual_insp'] ?? 0);
                $blocked    = (float)($data['blocked'] ?? 0);
                $qty_soh    = $unrest + $qual_insp + $blocked;

                StockOnHandModel::create([
                    'barang_id'    => $barang->id,
                    'user_id'      => Auth::id() ?? 1,
                    'qty_soh'      => $qty_soh,
                    'qty_unrest'   => $unrest,
                    'qty_qi'       => $qual_insp,
                    'qty_block'    => $blocked,
                    'last_updated' => now(),
                    'principal'    => $barang->principal, // ambil langsung dari master barang
                ]);

                $countSuccess++;

                $sop = WfgSopModel::whereDate('tgl_opname', $today)
                    ->where('principal', $barang->principal)
                    ->first();

                if ($sop) {
                    $summary = WfgSopSummariesModel::where('sop_id', $sop->id)
                        ->where('barang_id', $barang->id)
                        ->first();

                    if ($summary) {
                        $qtySistem = $qty_soh;
                        $qtyFisik  = $summary->qty_fisik ?? 0;
                        $selisih   = $qtyFisik - $qtySistem;
                        $status    = $selisih > 0 ? 'kurang' : ($selisih < 0 ? 'lebih' : 'match');

                        $summary->update([
                            'qty_sistem' => $qtySistem,
                            'selisih'    => $selisih,
                            'status'     => $status,
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => "Berhasil import $countSuccess data Stock On Hand dari Excel. Data yang sudah ada hari ini tetap aman."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengimpor file Excel.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // download temlate
    public function downloadTemplate()
    {
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set judul kolom (header template)
        $headers = [
            'mid_barang',
            'unrest',
            'qual_insp',
            'blocked',
        ];

        // Isi header ke baris pertama
        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '1', strtoupper($header));
            $sheet->getStyle($columnIndex . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($columnIndex)->setAutoSize(true);
            $columnIndex++;
        }

        // Tambahkan contoh data (opsional)
        $sheet->setCellValue('A2', '1160825');
        $sheet->setCellValue('B2', 886);
        $sheet->setCellValue('C2', 0);
        $sheet->setCellValue('D2', 0);

        // Nama file
        $fileName = 'Template_Stock_On_Hand_' . date('Y-m-d') . '.xlsx';

        // Buat response untuk download langsung tanpa simpan file ke server
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=\"{$fileName}\"",
        ]);
    }
}
