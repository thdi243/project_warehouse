<?php

namespace App\Http\Controllers\Wfg\stock_opname;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Validation\ValidationException;
use App\Models\Wfg\stock_opname\BarangWfgModel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarangWfgController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('master.wfg.barang_so');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function data(Request $request)
    {
        try {
            $status = $request->input('status', 'active');
            $searchTerm = $request->input('search');
            $principal = $request->input('principal'); // 👈 ambil dari request
            $perPage = 20;

            $query = BarangWfgModel::query();

            $query->where('is_new', 0);

            // Filter status
            if ($status === 'trashed') {
                $query->onlyTrashed();
            } elseif ($status === 'all') {
                $query->withTrashed();
            } else {
                $query->whereNull('deleted_at');
            }

            // Filter principal
            if (!empty($principal)) {
                $query->where('principal', $principal);
            }

            // Filter pencarian
            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('nama_barang', 'like', "%{$searchTerm}%")
                        ->orWhere('mid_barang', 'like', "%{$searchTerm}%");
                });
            }

            $barangPaginated = $query->paginate($perPage);

            $barangData = $barangPaginated->getCollection()->map(function ($item) {
                $currentStatus = $item->deleted_at ? 'nonaktif' : ($item->status ?? 'aktif');

                return [
                    'id'          => $item->id,
                    'mid_barang'  => $item->mid_barang,
                    'nama_barang' => $item->nama_barang,
                    'qty_box'     => $item->qty_box,
                    'principal'   => $item->principal,
                    'uom'         => $item->uom,
                    'status'      => $currentStatus,
                ];
            });

            $paginatedResponse = $barangPaginated->setCollection($barangData);

            return response()->json([
                'status' => true,
                'data'   => $paginatedResponse
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil data barang',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getNewItems()
    {
        $items = BarangWfgModel::where('is_new', 1)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'nama_barang', 'mid_barang', 'principal']);

        return response()->json($items);
    }

    public function approve($id)
    {
        $item = BarangWfgModel::findOrFail($id);
        $item->update(['is_new' => 0]);

        return response()->json(['success' => true]);
    }

    public function reject($id)
    {
        $item = BarangWfgModel::findOrFail($id);
        $item->delete(); // atau set kolom lain jika ingin soft delete
        return response()->json(['success' => true]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'mid_barang'   => 'integer|digits_between:1,8',
                'nama_barang'  => 'required|string|max:255',
                'qty_box'      => 'required|integer|min:1', // Ditambahkan: qty_box harus diisi dan berupa integer
                'principal' => 'nullable|string|max:100',
                'uom'       => 'nullable|string|max:50',
            ]);

            // Cek duplikat MID barang
            $midSoftDeleted = BarangWfgModel::withTrashed()
                ->where('mid_barang', $request->mid_barang)
                ->whereNotNull('deleted_at')
                ->first();

            if ($midSoftDeleted) {
                return response()->json([
                    'status'  => false,
                    'message' => 'MID Barang sudah ada pada data nonaktif. Aktifkan kembali barang tersebut jika ingin digunakan.',
                ], 400);
            }


            $barang = BarangWfgModel::create([
                'mid_barang'    => $request->mid_barang,
                'nama_barang'   => strtoupper($request->nama_barang),
                'qty_box'       => $request->qty_box,
                'principal'     => strtoupper($request->principal),
                'status'        => 'aktif',
                'uom'           => strtoupper($request->uom),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Barang berhasil ditambahkan',
                'data'    => $barang
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menambahkan barang',
                'error'   => $e->getMessage(), // optional, untuk debugging
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Cari item berdasarkan ID, termasuk yang di-trashed jika diperlukan
            $item = BarangWfgModel::withTrashed()->find($id);

            if (!$item) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data barang tidak ditemukan.'
                ], 404);
            }

            // Terapkan transformasi data yang sama
            $currentStatus = $item->deleted_at ? 'nonaktif' : ($item->status ?? 'aktif');

            $barangDetail = [
                'id'            => $item->id,
                'mid_barang'    => $item->mid_barang,
                'nama_barang'   => $item->nama_barang,
                'qty_box'       => $item->qty_box,
                'principal'     => $item->principal,
                'uom'           => $item->uom,
                'status'        => $currentStatus,
                'keterangan'    => $item->keterangan ?? '',
                'created_at'    => $item->created_at,
                'updated_at'    => $item->updated_at,
            ];

            return response()->json([
                'status' => true,
                'data'   => $barangDetail
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil data detail barang',
                'error'   => $e->getMessage()
            ], 500);
        }
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
        try {
            $barang = BarangWfgModel::findOrFail($id);

            $validated = $request->validate([
                'mid_barang' => [
                    'required',
                    'integer',
                    'digits_between:1,8',
                    Rule::unique('wfg_barang', 'mid_barang')->ignore($barang->id),
                ],

                'nama_barang' => 'required|string|max:255',
                'qty_box' => 'required|integer|min:1',
                'principal'   => 'nullable|string|max:100',
                'uom'  => 'nullable|string|max:50',
            ]);

            // 3. Update data
            $barang->update([
                'mid_barang'    => $validated['mid_barang'],
                'nama_barang'   => strtoupper($validated['nama_barang']),
                'qty_box'       => $validated['qty_box'],
                'principal'     => strtoupper($validated['principal']),
                'uom'           => strtoupper($validated['uom']),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Barang berhasil diupdate',
                'data' => $barang
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat update barang',
                'error'   => $e->getMessage(), // untuk debug
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $barang = BarangWfgModel::findOrFail($id);

        $barang->status = 'nonaktif';
        $barang->save();

        $barang->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Barang berhasil dihapus (soft delete)'
        ]);
    }

    public function restore($id)
    {
        $barang = BarangWfgModel::withTrashed()->findOrFail($id);
        $barang->restore();
        $barang->status = 'aktif';
        $barang->save();

        return response()->json([
            'status'  => true,
            'message' => 'Barang berhasil direstore',
            'data' => $barang
        ]);
    }

    public function forceDelete($id)
    {
        $barang = BarangWfgModel::withTrashed()->findOrFail($id);
        $barang->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Barang berhasil dihapus permanen'
        ]);
    }

    // Upload Handle
    public function import(Request $request)
    {
        // 1. Validasi File
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120', // Maks 5MB
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        // Inisialisasi koleksi untuk menampung data valid dan error
        $dataToInsert = [];
        $errors = [];
        $midBarangList = []; // Untuk cek duplikat dalam file

        // Header yang diharapkan (mengambil dari method downloadTemplate)
        $expectedHeaders = [
            'MID_BARANG',
            'NAMA_BARANG',
            'QTY_BOX_PALLET',
            'PRINCIPAL',
            'UOM'
        ];

        // Ambil header aktual (Baris 1)
        $actualHeaders = [];
        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $actualHeaders[] = strtoupper($sheet->getCell($column . '1')->getValue());
        }

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = [
                'mid_barang'   => trim($sheet->getCell('A' . $row)->getValue()),
                'nama_barang'  => trim($sheet->getCell('B' . $row)->getValue()),
                'qty_box'      => trim($sheet->getCell('C' . $row)->getValue()), // Asumsi kolom C adalah qty_box
                'principal'    => trim($sheet->getCell('D' . $row)->getValue()), // Asumsi kolom D adalah tipe_kemasan/principal
                'uom'          => trim($sheet->getCell('E' . $row)->getValue()), // Asumsi kolom E adalah satuan/uom
                'baris'        => $row,
            ];

            $mid = $rowData['mid_barang'];
            $qtyBox = (int) $rowData['qty_box'];
            $namaBarang = $rowData['nama_barang'];

            // Cek Validasi Baris (mirip store logic)
            $rowErrors = [];

            // 1. Validasi MID Barang (angka & panjang)
            if (!is_numeric($mid) || strlen($mid) > 8 || strlen($mid) < 1) {
                $rowErrors[] = 'MID Barang harus angka dan panjang 1-8 digit.';
            }

            // 2. Validasi QTY BOX
            if (empty($qtyBox) || !is_numeric($qtyBox) || $qtyBox < 1) {
                $rowErrors[] = 'Qty Box harus diisi dan berupa angka positif.';
            }

            // 3. Validasi Nama Barang
            if (empty($namaBarang)) {
                $rowErrors[] = 'Nama Barang wajib diisi.';
            }

            // 4. Cek Duplikat di Database
            if (BarangWfgModel::where('mid_barang', $mid)->exists()) {
                $rowErrors[] = 'MID Barang sudah ada di database.';
            }

            // 5. Cek Duplikat dalam File Import (MID harus unik di file)
            if (in_array($mid, $midBarangList)) {
                $rowErrors[] = 'MID Barang duplikat dalam file import ini.';
            } else {
                $midBarangList[] = $mid;
            }


            if (empty($rowErrors)) {
                // Data valid, masukkan ke koleksi
                $dataToInsert[] = [
                    'mid_barang'   => $mid,
                    'nama_barang'  => strtoupper($namaBarang),
                    'qty_box'      => $qtyBox,
                    'principal'    => strtoupper($rowData['principal']),
                    'uom'          => strtoupper($rowData['uom']),
                    'status'       => 'aktif',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            } else {
                // Data tidak valid, masukkan ke array errors
                $errors[] = [
                    'baris' => $row,
                    'data'  => $rowData,
                    'error' => implode(', ', $rowErrors),
                ];
            }
        }

        // 3. Eksekusi Penyimpanan
        // if (!empty($errors)) {
        //     return response()->json([
        //         'status'  => false,
        //         'message' => 'Terdapat ' . count($errors) . ' baris data yang bermasalah. Perbaiki data dan coba lagi.',
        //         'errors'  => $errors,
        //     ]);
        // }

        if (empty($dataToInsert)) {
            return response()->json([
                'status'  => false,
                'message' => 'Tidak ada data valid yang ditemukan untuk diimpor.',
            ], 400);
        }

        // Gunakan transaksi untuk memastikan semua data tersimpan atau tidak sama sekali
        DB::beginTransaction();
        try {
            BarangWfgModel::insert($dataToInsert);
            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => count($dataToInsert) . ' barang berhasil diimpor, '
                    . count($errors) . ' baris gagal.',
                'errors'  => $errors, // tetap kirim baris error ke frontend
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menyimpan data ke database.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set judul kolom (header template)
        $headers = [
            'mid_barang', // hanya ini diisi user
            'nama_barang',
            'qty_box_pallet',
            'principal',
            'uom'
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
        $sheet->setCellValue('A2', 'MID001');
        $sheet->setCellValue('B2', 'SEDAAP KECAP MANIS');
        $sheet->setCellValue('C2', 91);
        $sheet->setCellValue('D2', 'SMU');
        $sheet->setCellValue('E2', 'BOX');

        // Nama file
        $fileName = 'Template_Master_Barang_SO.xlsx';

        // Buat response untuk download langsung tanpa simpan file ke server
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=\"Template_Master_Barang_SO.xlsx\"",
        ]);
    }
}
