<?php

namespace App\Http\Controllers\Wfg\stock_opname;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Wfg\stock_opname\WfgSopModel;
use Illuminate\Validation\ValidationException;
use App\Models\Wfg\stock_opname\BarangWfgModel;
use App\Models\Wfg\stock_opname\StockOnHandModel;
use App\Models\Wfg\stock_opname\WfgSopDetailModel;
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
            'unrest' => 'nullable|integer',
            'qi' => 'nullable|integer',
            'block' => 'nullable|integer',
        ]);

        try {
            $user = Auth::user();

            // Tentukan principal berdasarkan role user
            if ($user->jabatan === 'operator') {
                $principal = $user->principal?->principal ?? null;

                if (empty($principal)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Akun operator belum memiliki principal. Hubungi admin untuk melengkapi data user.',
                    ], 422);
                }
            } else {
                // Non-operator boleh input manual atau kosong
                $principal = $request->input('principal', $user->principal ?? null);
            }

            $exists = StockOnHandModel::where('barang_id', $request->barang_id)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data Mid Barang SOH untuk barang ini sudah diinput hari ini!',
                ], 409);
            }

            $unrest = $request->unrest ?? 0;
            $qi = $request->qi ?? 0;
            $block = $request->block ?? 0;
            $qty_soh = $unrest + $qi + $block;

            $soh = StockOnHandModel::create([
                'barang_id' => $request->barang_id,
                'user_id' => Auth::id() ?? 1,
                'qty_soh' => $qty_soh ?? 0,
                'qty_unrest' => $unrest,
                'qty_qi' => $qi,
                'qty_block' => $block,
                'last_updated' => now(),
                'principal' => $principal
            ]);


            return response()->json([
                'status' => true,
                'message' => 'Stock On Hand berhasil ditambahkan',
                'data' => $soh
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
                'error' => $e->getMessage()
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
        $perPage = 20;
        $today = now()->toDateString();
        $user = Auth::user();

        Log::info('SOH Filter Check', [
            'user' => $user->username,
            'jabatan' => $user->jabatan,
            'relasi_principal' => $user->principal,
            'user_principal_value' => $user->principal?->principal,
        ]);

        $query = StockOnHandModel::query()
            ->select('wfg_soh.*')
            ->leftJoin('wfg_barang', 'wfg_soh.barang_id', '=', 'wfg_barang.id')
            ->leftJoin('users', 'wfg_soh.user_id', '=', 'users.id');

        // Filter tanggal
        $query->whereDate('wfg_soh.last_updated', $today);

        // Filter principal jika operator
        if ($user->jabatan === 'operator') {
            $userPrincipal = $user->principal?->principal;
            if ($userPrincipal) {
                $query->where('wfg_barang.principal', $userPrincipal);
            } else {
                $query->whereRaw('1 = 0'); // tidak ada data
            }
        } else {
            if ($principalFilter) {
                $query->where('wfg_barang.principal', $principalFilter);
            }
        }

        // Filter search
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('wfg_barang.nama_barang', 'like', '%' . $searchTerm . '%')
                    ->orWhere('wfg_barang.mid_barang', 'like', '%' . $searchTerm . '%');
            });
        }

        // Ambil data relasi
        $query->with([
            'barang:id,mid_barang,nama_barang,qty_box,principal',
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
            if ($userPrincipal) {
                $query->where('principal', $userPrincipal);
            } else {
                // Jika operator tapi tidak punya principal, tidak tampilkan data
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

    // Import dair Excel
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $user = Auth::user();

            // 🔹 Tentukan principal berdasarkan jabatan
            if ($user->jabatan === 'operator') {
                $principal = $user->principal?->principal ?? null;

                if (empty($principal)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Akun operator belum memiliki principal. Hubungi admin untuk melengkapi data user.',
                    ], 422);
                }
            } else {
                $principal = $request->input('principal', $user->principal ?? null);
            }

            $file = $request->file('file');
            $path = $file->getRealPath();

            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $header = [];
            $countSuccess = 0;
            $notFound = [];
            $today = now()->toDateString();

            foreach ($rows as $index => $row) {
                if ($index == 1) {
                    $header = array_map(fn($h) => strtolower(trim($h)), $row);
                    $requiredHeaders = ['mid_barang', 'nama_barang', 'unrest', 'qual_insp', 'blocked'];
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

                $barang = BarangWfgModel::where('mid_barang', $data['mid_barang'])->first();

                if (!$barang) {
                    $notFound[] = $data['mid_barang'];
                    continue;
                }

                // Cek apakah sudah ada hari ini
                $soh = StockOnHandModel::where('barang_id', $barang->id)
                    ->whereDate('created_at', $today)
                    ->first();

                if ($soh) {
                    // Kalau sudah ada, skip (atau update jika diinginkan)
                    continue;
                }

                // 🔹 Ambil nilai numeric (jaga-jaga kalau kosong atau null)
                $unrest  = (float)($data['unrest'] ?? 0);
                $qual_insp = (float)($data['qual_insp'] ?? 0);
                $blocked = (float)($data['blocked'] ?? 0);

                // 🔹 Hitung total qty_soh
                $qty_soh = $unrest + $qual_insp + $blocked;

                // 🔹 Simpan data baru ke database
                StockOnHandModel::create([
                    'barang_id'   => $barang->id,
                    'user_id'     => Auth::id() ?? 1,
                    'qty_soh'     => $qty_soh,
                    'qty_unrest'  => $unrest,
                    'qty_qi'      => $qual_insp,
                    'qty_block'   => $blocked,
                    'last_updated' => now(),
                    'principal'   => $principal,
                ]);

                $countSuccess++;
            }

            if (!empty($notFound)) {
                return response()->json([
                    'status' => true,
                    'message' => "Terdapat " . count($notFound) . " MID Barang yang tidak ditemukan di master barang.",
                    'not_found' => $notFound
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => "Berhasil import $countSuccess data Stock On Hand dari Excel. Data yang sudah ada hari ini tetap aman."
            ]);
        } catch (\Exception $e) {
            return response()->json([
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
            'nama_barang',
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
        $sheet->setCellValue('B2', 'FOOD KECAP MANIS SEDAAP JERIGEN 25KG');
        $sheet->setCellValue('C2', 886);
        $sheet->setCellValue('D2', 0);
        $sheet->setCellValue('E2', 0);

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
