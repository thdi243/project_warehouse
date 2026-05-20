<?php

namespace App\Http\Controllers\Wrm\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Wrm\Inventory\StockBalance;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\Inventory\StockOnHand;
use App\Models\Wrm\Inventory\StockOutboundDetail;
use App\Models\Wrm\Inventory\StockTransfer;
use App\Models\Wrm\Inventory\StockTransferDetail;
use App\Models\Wrm\MasterBarangModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StockTransferController extends Controller
{
    public function index()
    {
        return view('wrm.inventory.transfer_history');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048',
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

                // Template Mapping (22 columns)
                $noUrut = trim($row[0] ?? '');
                $noBa = trim($row[1] ?? '');
                $tglBaRaw = trim($row[2] ?? '');
                $matdocScrup = trim($row[3] ?? '');
                $matdocYear = trim($row[4] ?? '');
                $tglGrRaw = trim($row[5] ?? '');
                $tglResRaw = trim($row[6] ?? '');
                $noReservasi = trim($row[7] ?? '');
                $tglGiRaw = trim($row[8] ?? '');
                $matdocGi = trim($row[9] ?? '');
                $plant = trim($row[10] ?? '');
                $sloc = trim($row[11] ?? '');
                $matId = trim($row[12] ?? '');
                // $matDesc  = trim($row[13] ?? '');
                $noBarcode = trim($row[14] ?? '');
                $grade = trim($row[15] ?? '');
                $qtyBarcode = $row[16] ?? 0;
                $qtyActual = $row[17] ?? 0;
                $qtySusut = $row[18] ?? 0;
                $uom = trim($row[19] ?? '');
                $lamaSimpan = (int) ($row[20] ?? 0);
                $persenSusut = $row[21] ?? 0;

                if (empty($noBarcode) || empty($matId)) {
                    continue; // skip empty rows
                }

                // Parse Dates
                $tglBa = $this->parseDate($tglBaRaw);
                $tglGr = $this->parseDate($tglGrRaw);
                $tglRes = $this->parseDate($tglResRaw);
                $tglGi = $this->parseDate($tglGiRaw);

                // Parse Numbers
                $qtyBarcode = $this->parseNumber($qtyBarcode);
                $qtyActual = $this->parseNumber($qtyActual);
                $qtySusut = $this->parseNumber($qtySusut);
                $persenSusut = $this->parseNumber($persenSusut);

                // Find Material
                $barang = MasterBarangModel::where('mid', $matId)->first();
                if (! $barang) {
                    $errors[] = "Baris {$line}: Material ID {$matId} tidak ditemukan";

                    continue;
                }

                // 0. Check if this barcode already exists in History
                $exists = StockTransferDetail::where('no_barcode', $noBarcode)->exists();
                if ($exists) {
                    $errors[] = "Baris {$line}: No. Barcode {$noBarcode} sudah pernah diproses/disimpan dalam history transfer.";

                    continue;
                }

                // --- INTEGRATION WITH SOH (INBOUND) ---
                // 1. Find the Inbound Detail to see current status
                $stockOnHand = StockOnHand::where('barcode', $noBarcode)
                    ->where('barang_id', $barang->id)
                    ->first();

                if (! $stockOnHand) {
                    $errors[] = "Baris {$line}: Barcode {$noBarcode} tidak ditemukan di Stock On Hand.";

                    continue;
                }

                // --- HEADER AND DERIVATIONS ---
                // Derive no_spb from barcode (first 10 chars)
                $noSpbFromBarcode = substr($noBarcode, 0, 10);

                $headerKey = "{$matdocGi}|{$noBa}|{$noReservasi}";

                // 1. Get or Create Header
                if (! isset($headerTracker[$headerKey])) {
                    $headerTracker[$headerKey] = StockTransfer::create([
                        'no_ba' => $noBa,
                        'tgl_ba' => $tglBa,
                        'tgl_gr' => $tglGr,
                        'no_reservasi' => $noReservasi,
                        'tgl_reservasi' => $tglRes,
                        'tgl_gi' => $tglGi,
                        'matdoc_gi' => $matdocGi,
                        'created_by' => Auth::id(),
                    ]);
                }
                $header = $headerTracker[$headerKey];

                // 2. Create Detail
                $detail = StockTransferDetail::create([
                    'transfer_id' => $header->id,
                    'matdoc_scrup' => $matdocScrup,
                    'matdoc_year' => $matdocYear,
                    'no_spb' => $noSpbFromBarcode,
                    'plant' => $plant,
                    'sloc' => $sloc,
                    'barang_id' => $barang->id,
                    'no_barcode' => $noBarcode,
                    'grade' => $grade,
                    'qty_barcode' => $qtyBarcode,
                    'qty_actual' => $qtyActual,
                    'qty_susut_simpan' => $qtySusut,
                    'uom' => $uom,
                    'lama_simpan' => $lamaSimpan,
                    'persen_susut' => $persenSusut,
                    'created_by' => Auth::id(),
                ]);

                // --- INVENTORY UPDATES (MOVEMENT & BALANCE) ---
                if ($stockOnHand) {
                    $bin = $stockOnHand->bin;
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
                            'barang_id' => $barang->id,
                            'loc_id' => $locId,
                            'tanggal' => $tglGi ?? now(),
                            'qty' => $qtyActual, // Dashboard uses positive sums
                            'jenis' => 'out',
                            'ref_type' => 'stock_transfer',
                            'ref_id' => $detail->id,
                            'created_by' => Auth::id(),
                        ]);

                        // Update Inbound Status and Qty
                        $stockOnHand->update([
                            'status' => 'ISSUED',
                            'updated_by' => Auth::id(),
                        ]);
                    }
                }
                // ----------------------------------------------

                // --- INTEGRATION WITH DRAFT OUTBOUND ---
                // Find matching draft outbound detail
                $draftDetail = StockOutboundDetail::where('barcode', $noBarcode)
                    ->where('barang_id', $barang->id)
                    ->whereHas('outbound', function ($q) use ($noReservasi, $tglRes) {
                        $q->where('no_reservasi', $noReservasi);
                        if ($tglRes) {
                            $q->whereDate('reservasi_date', $tglRes);
                        }
                    })
                    ->first();

                if ($draftDetail) {
                    $draftDetail->update([
                        'status' => 'ISSUED',
                        'updated_by' => Auth::id(),
                    ]);
                }
                // ----------------------------------------------

                $processedCount++;
            }

            if (! empty($errors)) {
                throw new \Exception(implode("\n", $errors));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Stock Transfer berhasil diunggah. {$processedCount} data diproses.",
                'total' => $processedCount,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses upload: '.$e->getMessage(),
                'errors' => explode("\n", $e->getMessage()),
            ], 422);
        }
    }

    public function getData(Request $request)
    {
        $query = StockTransferDetail::with([
            'header',
            'barang:id,mid,nama_barang,uom',
        ]);

        if ($request->no_reservasi) {
            $query->whereHas('header', function ($q) use ($request) {
                $q->where('no_reservasi', 'like', '%'.$request->no_reservasi.'%')
                    ->orWhere('no_ba', 'like', '%'.$request->no_reservasi.'%');
            });
        }

        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', 'like', '%'.$request->mid.'%');
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
            'data' => $data,
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
                $inbound = StockOnHand::where('barcode', $detail->no_barcode)
                    ->where('barang_id', $detail->barang_id)
                    ->first();

                if ($inbound) {
                    $inbound->update([
                        'qty' => abs($movement->qty), // Restore original qty
                        'status' => 'IN_STOCK',       // Return to stock
                        'updated_by' => Auth::id(),
                    ]);
                }

                // 4. Delete Movement
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
                'message' => 'Data transfer berhasil dihapus dan inventory telah dikembalikan.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data: '.$e->getMessage(),
            ], 500);
        }
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }
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
        if ($value === null || $value === '') {
            return 0;
        }

        // Remove thousand separator dots, then replace comma with dot for decimal
        $val = str_replace('.', '', $value);
        $val = str_replace(',', '.', $val);

        return (float) $val;
    }
}
