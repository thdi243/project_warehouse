<?php

namespace App\Http\Controllers\Wrm\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Wrm\Inventory\StockBalance;
use App\Models\Wrm\Inventory\StockInboundDetail;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\Inventory\StockOutboundDetail;
use App\Models\Wrm\Inventory\StockTransfer;
use App\Models\Wrm\Inventory\StockTransferDetail;
use App\Models\Wrm\MasterBarangModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class StockTransferController extends Controller
{
    public function index()
    {
        return view('wrm.inventory.transfer_history');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048'
        ]);

        DB::beginTransaction();

        try {
            $sheet = IOFactory::load($request->file('file'))->getActiveSheet();
            $rows = $sheet->toArray();

            unset($rows[0]); // remove header

            $errors = [];
            $headerTracker = []; // To keep track of created headers by No. Reservasi
            $processedCount = 0;

            foreach ($rows as $i => $row) {
                $line = $i + 1;

                // Template Mapping (17 columns, 0-indexed)
                $tglGrRaw      = trim($row[0] ?? '');
                $tglResRaw     = trim($row[1] ?? '');
                $noReservasi   = trim($row[2] ?? '');
                $tglGiRaw      = trim($row[3] ?? '');
                $matdocGi      = trim($row[4] ?? '');
                $plant         = trim($row[5] ?? '');
                $sloc          = trim($row[6] ?? '');
                $matId         = trim($row[7] ?? '');
                // $matDesc    = trim($row[8] ?? '');
                $noBarcode     = trim($row[9] ?? '');
                $grade         = trim($row[10] ?? '');
                $qtyBarcode    = $row[11];
                $qtyActual     = $row[12];
                $qtySusut      = $row[13];
                $uom           = trim($row[14] ?? '');
                $lamaSimpan    = (int) ($row[15] ?? 0);
                $persenSusut   = $row[16];

                if (empty($noReservasi) || empty($matId)) {
                    continue; // skip empty rows
                }

                // Parse Dates
                $tglGr  = $this->parseDate($tglGrRaw);
                $tglRes = $this->parseDate($tglResRaw);
                $tglGi  = $this->parseDate($tglGiRaw);

                // Parse Numbers (handle comma as decimal separator if needed)
                $qtyBarcode = $this->parseNumber($qtyBarcode);
                $qtyActual  = $this->parseNumber($qtyActual);
                $qtySusut   = $this->parseNumber($qtySusut);
                $persenSusut = $this->parseNumber($persenSusut);

                // Find Material
                $barang = MasterBarangModel::where('mid', $matId)->first();
                if (!$barang) {
                    $errors[] = "Baris {$line}: Material ID {$matId} tidak ditemukan";
                    continue;
                }

                // 0. Check if this barcode already exists in History
                $exists = StockTransferDetail::where('no_barcode', $noBarcode)->exists();
                if ($exists) {
                    $errors[] = "Baris {$line}: No. Barcode {$noBarcode} sudah pernah diproses/disimpan dalam history transfer.";
                    continue;
                }

                // --- INTEGRATION WITH DRAFT OUTBOUND ---
                // 1. Find the Inbound Detail to see current status
                $inboundDetail = StockInboundDetail::where('barcode', $noBarcode)
                    ->where('barang_id', $barang->id)
                    ->first();

                // 2. Link with Draft (RESERVED)
                $draftDetail = StockOutboundDetail::where('barcode', $noBarcode)
                    ->where('status', 'RESERVED')
                    ->first();

                if ($draftDetail) {
                    // Update Draft status
                    $draftDetail->update(['status' => 'ISSUED', 'updated_by' => Auth::id()]);

                    // Update Inbound status if linked
                    if ($inboundDetail && $inboundDetail->status === 'RESERVED') {
                        $inboundDetail->update(['status' => 'ISSUED', 'updated_by' => Auth::id()]);
                    }
                }

                // --- HEADER AND DERIVATIONS ---
                // Derive no_spb from barcode (first 10 chars)
                $noSpbFromBarcode = substr($noBarcode, 0, 10);

                // 1. Get or Create Header
                if (!isset($headerTracker[$noReservasi])) {
                    $headerTracker[$noReservasi] = StockTransfer::create([
                        'tgl_gr'        => $tglGr,
                        'no_reservasi'  => $noReservasi,
                        'tgl_reservasi' => $tglRes,
                        'created_by'    => Auth::id(),
                    ]);
                }
                $header = $headerTracker[$noReservasi];

                // 2. Create Detail
                $detail = StockTransferDetail::create([
                    'transfer_id'      => $header->id,
                    'tgl_gi'           => $tglGi,
                    'matdoc_gi'        => $matdocGi,
                    'no_spb'           => $noSpbFromBarcode,
                    'plant'            => $plant,
                    'sloc'             => $sloc,
                    'barang_id'        => $barang->id,
                    'no_barcode'       => $noBarcode,
                    'grade'            => $grade,
                    'qty_barcode'      => $qtyBarcode,
                    'qty_actual'       => $qtyActual,
                    'qty_susut_simpan' => $qtySusut,
                    'uom'              => $uom,
                    'lama_simpan'      => $lamaSimpan,
                    'persen_susut'     => $persenSusut,
                    'created_by'       => Auth::id(),
                ]);

                // --- INVENTORY UPDATES (MOVEMENT & BALANCE) ---
                if ($inboundDetail) {
                    $bin = $inboundDetail->bin;
                    if ($bin) {
                        $locId = $bin->loc_id;

                        // Decrement Stock Balance
                        $balance = StockBalance::where('barang_id', $barang->id)
                            ->where('loc_id', $locId)
                            ->first();

                        if ($balance) {
                            $balance->decrement('qty', $qtyActual);
                            $balance->update(['updated_by' => Auth::id()]);
                        }

                        // Record Stock Movement (out)
                        StockMovement::create([
                            'barang_id'  => $barang->id,
                            'loc_id'     => $locId,
                            'tanggal'    => $tglGi ?? now(),
                            'qty'        => $qtyActual, // Dashboard uses positive sums
                            'jenis'      => 'out',
                            'ref_type'   => 'stock_transfer',
                            'ref_id'     => $detail->id,
                            'created_by' => Auth::id(),
                        ]);

                        // Update Inbound Status and Qty
                        $inboundDetail->update([
                            'status' => 'ISSUED',
                            'qty'    => 0, // Mark as empty for capacity charts
                            'updated_by' => Auth::id()
                        ]);
                    }
                }
                // ----------------------------------------------


                $processedCount++;
            }


            if (!empty($errors)) {
                throw new \Exception(implode("\n", $errors));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Stock Transfer berhasil diunggah. {$processedCount} data diproses.",
                'total' => $processedCount
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses upload: ' . $e->getMessage(),
                'errors' => explode("\n", $e->getMessage())
            ], 422);
        }
    }

    public function getData(Request $request)
    {
        $query = StockTransferDetail::with([
            'header',
            'barang:id,mid,nama_barang,uom'
        ]);

        if ($request->no_reservasi) {
            $query->whereHas('header', function ($q) use ($request) {
                $q->where('no_reservasi', 'like', '%' . $request->no_reservasi . '%');
            });
        }

        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', 'like', '%' . $request->mid . '%');
            });
        }

        if ($request->date) {
            $query->whereHas('header', function ($q) use ($request) {
                $q->whereDate('tgl_reservasi', $request->date);
            });
        }

        $data = $query->latest()->paginate(25);

        return response()->json([
            'status' => true,
            'message' => 'Data transfer detail berhasil diambil',
            'data' => $data
        ]);
    }

    public function destroyDetail($id)
    {
        DB::beginTransaction();
        try {
            $detail = StockTransferDetail::findOrFail($id);
            $transferId = $detail->transfer_id;

            // 1. Find the associated StockMovement (if any)
            $movement = StockMovement::where('ref_type', 'stock_transfer')
                ->where('ref_id', $detail->id)
                ->first();

            if ($movement) {
                // 2. Reverse Stock Balance
                $balance = StockBalance::where('barang_id', $movement->barang_id)
                    ->where('loc_id', $movement->loc_id)
                    ->first();

                if ($balance) {
                    // Qty in movement was stored as absolute positive value for 'out'
                    $balance->increment('qty', abs($movement->qty));
                    $balance->update(['updated_by' => Auth::id()]);
                }

                // 3. Restore Stock Inbound Detail
                $inbound = StockInboundDetail::where('barcode', $detail->no_barcode)
                    ->where('barang_id', $detail->barang_id)
                    ->first();

                if ($inbound) {
                    $inbound->update([
                        'qty' => abs($movement->qty), // Restore original qty
                        'status' => 'RESERVED',       // Usually reserved for outbound draft
                        'updated_by' => Auth::id()
                    ]);
                }

                // 4. Restore Draft Outbound (if exists)
                $draft = StockOutboundDetail::where('barcode', $detail->no_barcode)
                    ->where('status', 'ISSUED')
                    ->first();

                if ($draft) {
                    $draft->update([
                        'status' => 'RESERVED',
                        'updated_by' => Auth::id()
                    ]);
                }

                // 5. Delete Movement
                $movement->delete();
            }

            // 6. Delete Transfer Detail
            $detail->delete();

            // 7. Cleanup Header if empty
            $header = StockTransfer::find($transferId);
            if ($header && $header->details()->count() === 0) {
                $header->delete();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Data transfer berhasil dihapus dan inventory telah dikembalikan.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }


    private function parseDate($value)
    {
        if (empty($value)) return null;
        try {
            // Usually Excel dates come as DD.MM.YYYY or MM/DD/YYYY
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            }

            // Try common formats
            $formats = ['d.m.Y', 'd/m/Y', 'Y-m-d', 'm/d/Y'];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $value)->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseNumber($value)
    {
        if ($value === null || $value === '') return 0;

        // Remove thousand separator dots, then replace comma with dot for decimal
        $val = str_replace('.', '', $value);
        $val = str_replace(',', '.', $val);

        return (float) $val;
    }
}
