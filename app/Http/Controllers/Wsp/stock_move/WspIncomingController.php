<?php

namespace App\Http\Controllers\Wsp\stock_move;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
use App\Mail\IncomingMaterialMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Jobs\SendIncomingMaterialEmailJob;
use App\Models\Wsp\stock_move\WspIncomingModel;

class WspIncomingController extends Controller
{
    public function viewIncoming()
    {
        return view('wsp.wsp_stock.stock_move.incoming');
    }

    public function getDataIncoming(Request $request)
    {
        try {
            $query = WspIncomingModel::with([
                'user:id,nama_lengkap',
            ]);

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            } elseif ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            } else {
                $query->whereDate('created_at', Carbon::today());
            }

            $data = $query->orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data Incoming berhasil diambil.',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data Incoming.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_date' => 'required|date',
            'pr_number'    => 'required',
            'mid'          => 'required',
            'nama_barang'  => 'required',
            'text'         => 'nullable|string',
            'requisitio'   => 'nullable|string',
            'recipient'    => 'nullable|string',
            'cc_email'     => 'nullable|string',
            'po_number'    => 'nullable',
            'po_date'      => 'nullable|date',
            'gr_qty'       => 'nullable|numeric',
            'gr_date'      => 'nullable|date',
            'material_doc' => 'nullable',
        ]);

        try {
            $barang = BarangModel::where('mid', $validated['mid'])->first();

            if (!$barang) {
                return response()->json([
                    'success' => false,
                    'message' => 'MID tidak ditemukan di master barang'
                ], 422);
            }

            $validated['user_id'] = Auth::id() ?? 1;

            $incoming = WspIncomingModel::create($validated);

            $emails = [];
            if (!empty($validated['recipient'])) {
                $emails = array_map('trim', explode(',', $validated['recipient']));
            }

            // Jika ada CC email
            if (!empty($validated['cc_email'])) {
                $ccs = array_map('trim', explode(',', $validated['cc_email']));
                $emails = array_merge($emails, $ccs);
            }

            $emails = array_unique($emails);

            if (count($emails) > 0) {
                $emailGroup = [
                    ($incoming->material_doc ?? '-') => [
                        [
                            'emails'      => $emails,
                            'mid'         => $incoming->mid,
                            'nama_barang' => $incoming->nama_barang,
                            'po_number'   => $incoming->po_number,
                            'gr_qty'      => $incoming->gr_qty,
                        ]
                    ]
                ];

                SendIncomingMaterialEmailJob::dispatch($emailGroup)->afterResponse();
            }

            return response()->json([
                'message' => 'Incoming berhasil ditambahkan',
                'data'    => $incoming
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan incoming',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $data = WspIncomingModel::with(['user:id,nama_lengkap'])->find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diambil',
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'request_date' => 'required|date',
            'pr_number'    => 'required',
            'mid'          => 'required',
            'nama_barang'  => 'required',
            'text'         => 'nullable|string',
            'requisitio'   => 'nullable|string',
            'recipient'    => 'nullable|string',
            'cc_email'     => 'nullable|string',
            'po_number'    => 'nullable',
            'po_date'      => 'nullable|date',
            'gr_qty'       => 'nullable|numeric',
            'gr_date'      => 'nullable|date',
            'material_doc' => 'nullable',
        ]);

        try {
            $incoming = WspIncomingModel::findOrFail($id);

            $incoming->update($validated);

            return response()->json([
                'message' => 'Incoming berhasil diperbarui',
                'data'    => $incoming
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengupdate incoming',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:4096',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (!is_array($rows) || count($rows) <= 1) {
                return response()->json([
                    'message' => 'File kosong atau tidak memiliki data.',
                ], 422);
            }

            DB::beginTransaction();

            $inserted = 0;
            $skipped  = [];
            $invalid_mid = [];
            $emailGroup = [];

            foreach ($rows as $index => $row) {

                // Skip header
                if ($index == 1) continue;

                // Ambil data dari kolom Excel
                $request_date = $row['A'] ?? null;
                $pr_number    = $row['B'] ?? null;
                $mid          = $row['C'] ?? null;
                $nama_barang  = $row['D'] ?? null;
                $text         = $row['E'] ?? null;
                $requisitio   = $row['F'] ?? null;
                $recipient    = $row['G'] ?? null;
                $cc_email     = $row['H'] ?? null;
                $po_number    = $row['I'] ?? null;
                $po_date      = $row['J'] ?? null;
                $gr_qty       = $row['K'] ?? null;
                $gr_date      = $row['L'] ?? null;
                $material_doc = $row['M'] ?? null;

                // Jika semua kolom kosong → skip
                if (
                    empty($request_date) && empty($pr_number) && empty($mid) &&
                    empty($nama_barang)
                ) {
                    continue;
                }

                // Validasi kolom wajib
                if (
                    empty($request_date) ||
                    empty($pr_number) ||
                    empty($mid) ||
                    empty($nama_barang)
                ) {
                    $skipped[] = "Baris {$index}: Kolom wajib tidak lengkap.";
                    continue;
                }

                $cekMid = BarangModel::where('mid_barang', $mid)->first();

                if (!$cekMid) {
                    $invalid_mid[] = "Baris {$index}: MID '{$mid}' tidak ada di master barang.";
                    continue;
                }

                // Insert data
                WspIncomingModel::create([
                    'user_id'      => Auth::id() ?? null,
                    'request_date' => $this->excelDate($request_date),
                    'pr_number'    => $pr_number,
                    'mid'          => $mid,
                    'nama_barang'  => $nama_barang,
                    'text'         => $text,
                    'requisitio'   => $requisitio,
                    'recipient'    => $recipient,
                    'cc_email'     => $cc_email,
                    'po_number'    => $po_number ?: 0,
                    'po_date'      => $this->excelDate($po_date),
                    'gr_qty'       => $gr_qty ?: 0,
                    'gr_date'      => $this->excelDate($gr_date),
                    'material_doc' => $material_doc ?: 0,
                ]);

                $inserted++;

                $emails = array_map('trim', explode(',', $recipient));

                $emailGroup[$material_doc][] = [
                    'emails'     => $emails,
                    'mid'        => $mid,
                    'nama_barang' => $nama_barang,
                    'po_number'  => $po_number,
                    'gr_qty'     => $gr_qty,
                ];
            }

            if (count($invalid_mid) > 0) {
                DB::rollBack();
                return response()->json([
                    "message" => "Upload ditolak. Terdapat MID yang belum terdaftar di master barang.",
                    "error_mid" => $invalid_mid
                ], 422);
            }

            DB::commit();

            SendIncomingMaterialEmailJob::dispatch($emailGroup)->afterResponse();

            return response()->json([
                "message" => "Upload berhasil. {$inserted} data ditambahkan.",
                "skipped" => $skipped,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengimpor file.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function excelDate($value)
    {
        if (empty($value)) return null;

        // Jika Excel format numeric → convert
        if (is_numeric($value)) {
            return date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value));
        }

        // Jika sudah berupa tanggal string → langsung return
        return date('Y-m-d', strtotime($value));
    }

    public function downloadTemplate()
    {
        $filePath = public_path('assets/templates/excel/Template_Incoming_Wsp.xlsx');

        // cek kalau file memang ada
        if (!file_exists($filePath)) {
            abort(404, 'Template tidak ditemukan.');
        }

        $fileName = 'Template_Incoming_Wsp_' . date('Y-m-d') . '.xlsx';

        return response()->download($filePath, $fileName);
    }

    public function destroy($id)
    {
        $data = WspIncomingModel::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $data->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);
    }
}
