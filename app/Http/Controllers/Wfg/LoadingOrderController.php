<?php

namespace App\Http\Controllers\Wfg;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wfg\LoadingOrder;
use App\Models\Wfg\LoadingOrderDetail;
use App\Models\Wrm\Inventory\StockOnHand;
use App\Models\Wfg\stock_opname\BarangWfgModel;
use App\Models\P2h\UserForkliftAssignmentModel;
use App\Models\Wfg\MasterDestinasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class LoadingOrderController extends Controller
{
    public function index()
    {
        return view('wfg.loading_order.data');
    }

    public function data(Request $request)
    {
        $perPage = 10;
        $search = $request->input('search');

        // Log data semuanya di index
        $query = LoadingOrder::with(['forkliftDriver', 'checker', 'destinasi', 'details.material']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('wavepick_smu', 'like', "%{$search}%")
                    ->orWhere('shipment_smu', 'like', "%{$search}%")
                    ->orWhere('wavepick_bas', 'like', "%{$search}%")
                    ->orWhere('shipment_bas', 'like', "%{$search}%");
            });
        }

        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $paginated
        ]);
    }

    public function approval()
    {
        return view('wfg.loading_order.approval');
    }

    public function approvalData(Request $request)
    {
        $perPage = 10;
        $search = $request->input('search');

        if (!auth()->user()->can('role', 'verificator')) {
            return response()->json([
                'status' => true,
                'data' => [
                    'current_page' => 1,
                    'data' => [],
                    'last_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0
                ]
            ]);
        }

        $query = LoadingOrder::with(['forkliftDriver:id,username,nama_lengkap', 'checker:id,username,nama_lengkap', 'destinasi:id,destinasi', 'details.material'])
            ->where('status', 'loaded');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('wavepick_smu', 'like', "%{$search}%")
                    ->orWhere('shipment_smu', 'like', "%{$search}%")
                    ->orWhere('wavepick_bas', 'like', "%{$search}%")
                    ->orWhere('shipment_bas', 'like', "%{$search}%");
            });
        }

        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $paginated
        ]);
    }

    public function create()
    {
        // Check for draft
        $draft = LoadingOrder::with('details.material')
            ->where('created_by', auth()->id())
            ->where('status', 'draft')
            ->first();

        // Check if user has incomplete loading order (submitted/approved)
        $incompleteOrder = LoadingOrder::where('created_by', auth()->id())
            ->whereIn('status', ['submitted', 'approved'])
            ->first();

        if ($incompleteOrder) {
            return redirect()->route('wfg.loading_order.show', $incompleteOrder->id)
                ->with('info', 'Anda memiliki Loading Order yang belum diselesaikan (Approval Checker/Driver). Silahkan selesaikan terlebih dahulu.');
        }

        $forkliftDrivers = UserForkliftAssignmentModel::with('user')
            ->where('is_active', true)
            ->get()
            ->pluck('user')
            ->unique('id');

        $checkers = User::role('checker')->get();
        $destinations = MasterDestinasi::select('id', 'destinasi')->get();

        // Gates currently in use (released when status is loaded, verified, or rejected)
        $bookedGates = LoadingOrder::whereIn('status', ['draft', 'submitted', 'approved'])
            ->whereNotNull('gate')
            ->pluck('gate')
            ->toArray();

        return view('wfg.loading_order.form', compact('forkliftDrivers', 'checkers', 'destinations', 'draft', 'bookedGates'));
    }

    private function generateNoDokumen()
    {
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $now = now();

        $lastOrder = LoadingOrder::whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = 0;

        if ($lastOrder && $lastOrder->no_dokumen) {
            $parts = explode('/', $lastOrder->no_dokumen);
            $lastNumber = (int) $parts[0];
        }

        $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        return $newNumber . '/Rev.00/WFG/' . $bulan[$now->month] . '/' . $now->year;
    }

    public function saveDraft(Request $request)
    {
        try {
            DB::beginTransaction();

            $existingDraft = LoadingOrder::where('created_by', auth()->id())
                ->where('status', 'draft')
                ->first();

            $noDok = $existingDraft?->no_dokumen ?? $this->generateNoDokumen();

            $order = LoadingOrder::updateOrCreate([
                'created_by' => auth()->id(),
                'status' => 'draft',
            ], [
                'tanggal' => $request->tanggal,
                'no_dokumen' => $noDok,
                'shipment_smu' => $request->shipment_smu,
                'wavepick_smu' => $request->wavepick_smu,
                'shipment_bas' => $request->shipment_bas,
                'wavepick_bas' => $request->wavepick_bas,
                'forklift_driver_id' => $request->forklift_driver_id,
                'destinasi_id' => $request->destinasi_id,
                'no_mobil' => $request->no_mobil,
                'gate' => $request->gate,
                'no_kontainer' => $request->no_kontainer,
                'no_segel_bas' => $request->no_segel_bas,
                'no_segel_vendor' => $request->no_segel_vendor,
                'jumlah_slipsheet' => $request->jumlah_slipsheet ?? 0,
                'updated_by' => auth()->id(),
            ]);

            // Jam muat otomatis ketika item pertama disimpan
            if (empty($order->jam_muat) && !empty($request->details)) {
                $order->jam_muat = Carbon::now()->format('H:i:s');
                $order->save();
            }

            // Sync details
            $order->details()->delete();
            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    LoadingOrderDetail::create([
                        'loading_order_id' => $order->id,
                        'barcode' => $detail['barcode'] ?? null,
                        'material_id' => $detail['material_id'],
                        'batch_number' => $detail['batch_number'] ?? null,
                        'jenis' => $detail['jenis'] ?? 'P',
                        'qty' => $detail['qty'] ?? 0,
                        'to_dummy' => $detail['to_dummy'] ?? null,
                        'to_sap' => $detail['to_sap'] ?? null,
                        'double_po' => isset($detail['double_po']) ? true : false,
                        'cancel_to' => isset($detail['cancel_to']) ? true : false,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Progress saved.', 'jam_muat' => $order->jam_muat]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'forklift_driver_id' => 'required|exists:users,id',
            'destinasi_id' => 'required|exists:wfg_master_destinasi,id',
            'no_mobil' => 'required',
            'gate' => 'required',
            'no_kontainer' => 'nullable',
            'no_segel_bas' => 'nullable',
            'jumlah_slipsheet' => 'nullable',
            'details' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Check for duplicate barcodes in the request
        $barcodes = collect($request->details)
            ->pluck('barcode')
            ->filter(function ($value) {
                return !is_null($value) && $value !== '';
            });

        if ($barcodes->duplicates()->count() > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Duplicate barcodes detected in your list.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Find existing draft or create new
            $order = LoadingOrder::where('created_by', auth()->id())->where('status', 'draft')->first();

            $orderData = [
                'tanggal' => $request->tanggal,
                'no_dokumen' => $order->no_dokumen,
                'shipment_smu' => $request->shipment_smu,
                'wavepick_smu' => $request->wavepick_smu,
                'shipment_bas' => $request->shipment_bas,
                'wavepick_bas' => $request->wavepick_bas,
                'forklift_driver_id' => $request->forklift_driver_id,
                'destinasi_id' => $request->destinasi_id,
                'no_mobil' => $request->no_mobil,
                'gate' => $request->gate,
                'no_kontainer' => $request->no_kontainer,
                'no_segel_bas' => $request->no_segel_bas,
                'no_segel_vendor' => $request->no_segel_vendor,
                'jumlah_slipsheet' => $request->jumlah_slipsheet ?? 0,
                'jam_muat' => $request->jam_muat ?? Carbon::now()->format('H:i:s'),
                'status' => 'submitted',
                'created_by' => auth()->id(),
            ];

            if ($order) {
                $order->update($orderData);
            } else {
                $order = LoadingOrder::create($orderData);
            }

            $order->details()->delete();
            foreach ($request->details as $detail) {
                LoadingOrderDetail::create([
                    'loading_order_id' => $order->id,
                    'material_id' => $detail['material_id'],
                    'batch_number' => $detail['batch_number'],
                    'jenis' => $detail['jenis'] ?? 'P',
                    'qty' => $detail['qty'] ?? 0,
                    'to_dummy' => $detail['to_dummy'] ?? null,
                    'to_sap' => $detail['to_sap'] ?? null,
                    'double_po' => $detail['double_po'] ?? false,
                    'cancel_to' => $detail['cancel_to'] ?? false,
                ]);
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Loading Order submitted successfully.', 'redirect' => route('wfg.loading_order.show', $order->id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $order = LoadingOrder::with(['details.material', 'forkliftDriver', 'checker', 'validator', 'destinasi'])->findOrFail($id);
        $checkers = User::role('checker')->get();
        return view('wfg.loading_order.show', compact('order', 'checkers'));
    }

    public function approveChecker(Request $request, $id)
    {
        $request->validate([
            'checker_id' => 'required|exists:users,id',
            'signature' => 'required|string', // Base64 signature
        ]);

        $order = LoadingOrder::findOrFail($id);

        if ($order->status !== 'submitted') {
            return back()->with('error', 'Order is not in submitted status.');
        }

        // Save signature to storage
        $signatureData = $request->signature;
        $signaturePath = $this->saveSignature($signatureData, '/checker/checker_' . $id);

        $order->update([
            'checker_id' => $request->checker_id,
            'checker_signature' => $signaturePath,
            'approved_at' => Carbon::now(),
            'status' => 'approved' // Moves to next step: Driver Approval
        ]);

        return back()->with('success', 'Checker approved successfully.');
    }

    public function approveDriver(Request $request, $id)
    {
        $request->validate([
            'driver_name' => 'required|string|max:255',
            'signature' => 'required|string', // Base64 signature
        ]);

        $order = LoadingOrder::findOrFail($id);

        if ($order->status !== 'approved') {
            return back()->with('error', 'Order is not in approved status.');
        }

        // Save signature to storage
        $signatureData = $request->signature;
        $signaturePath = $this->saveSignature($signatureData, '/driver/driver_' . $id);

        $order->update([
            'driver_name' => $request->driver_name,
            'driver_signature' => $signaturePath,
            'driver_approved_at' => Carbon::now(),
            'status' => 'loaded' // Final status before verification
        ]);

        return back()->with('success', 'Driver approved successfully.');
    }

    private function saveSignature($base64Data, $prefix)
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $type = strtolower($type[1]); // png, jpg, etc

                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    throw new \Exception('invalid image type');
                }

                $base64Data = base64_decode($base64Data);

                if ($base64Data === false) {
                    throw new \Exception('base64_decode failed');
                }
            } else {
                throw new \Exception('did not match data URI with image data');
            }

            $fileName = $prefix . '_' . time() . '_' . Str::random(10) . '.' . $type;
            $path = 'signatures/loading_order/' . $fileName;

            Storage::disk('public')->put($path, $base64Data);

            return 'storage/' . $path;
        } catch (\Exception $e) {
            \Log::error('Signature save error: ' . $e->getMessage());
            return null;
        }
    }

    public function validateOrder(Request $request, $id)
    {
        if (!auth()->user()->can('role', 'verificator')) {
            return back()->with('error', 'Unauthorized. Anda tidak memiliki role verificator.');
        }

        $order = LoadingOrder::with('details')->findOrFail($id);

        if ($order->status !== 'loaded') {
            return back()->with('error', 'Order belum siap untuk diverifikasi.');
        }

        // Mock Validation Logic: 
        // Check if all items belong to the wavepick.
        // In real scenario, we would query a wavepick_details table.
        $isValid = true; // Assume true for now

        if ($isValid) {
            $order->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => Carbon::now()
            ]);
            return back()->with('success', 'Order verified successfully.');
        } else {
            $order->update([
                'status' => 'rejected',
                'rejection_note' => 'Items do not match wavepick records.'
            ]);
            return back()->with('error', 'Order rejected. Material mismatch.');
        }
    }

    public function scanBarcode(Request $request)
    {
        $barcode = $request->barcode;

        // Try to find in SOH
        $soh = StockOnHand::with('barang')->where('barcode', $barcode)->first();

        if ($soh) {
            // Map to WFG Master Barang
            $wfgBarang = BarangWfgModel::where('mid_barang', $soh->barang->mid)->first();

            if (!$wfgBarang) {
                return response()->json(['status' => false, 'message' => 'Material [' . $soh->barang->mid . '] not registered in WFG Master Barang.']);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'material_id' => $wfgBarang->id,
                    'mid' => $wfgBarang->mid_barang,
                    'nama_barang' => $wfgBarang->nama_barang,
                    'batch' => $soh->no_spb,
                    'jenis' => 'P',
                    'qty' => $soh->qty
                ]
            ]);
        }

        return response()->json(['status' => false, 'message' => 'Barcode not found in inventory.']);
    }

    public function searchMaterials(Request $request)
    {
        $search = $request->q;
        $materials = BarangWfgModel::where('nama_barang', 'like', "%$search%")
            ->orWhere('mid_barang', 'like', "%$search%")
            ->limit(20)
            ->get();

        return response()->json($materials->map(function ($m) {
            return [
                'id' => $m->id,
                'text' => "[{$m->mid_barang}] {$m->nama_barang}",
                'mid' => $m->mid_barang,
                'nama' => $m->nama_barang,
                'qty_box' => $m->qty_box
            ];
        }));
    }

    public function destroy($id)
    {
        $order = LoadingOrder::findOrFail($id);

        // Only allow deletion if not yet verified or heavily processed
        if (in_array($order->status, ['draft', 'submitted', 'rejected'])) {
            $order->delete();
            return response()->json(['status' => true, 'message' => 'Loading Order successfully deleted.']);
        }

        return response()->json(['status' => false, 'message' => 'Cannot delete Loading Order with status ' . $order->status], 403);
    }

    public function download($id)
    {
        $order = LoadingOrder::with([
            'details.material',
            'forkliftDriver',
            'checker',
            'validator',
            'destinasi'
        ])->findOrFail($id);

        // Total pallet & receh
        $totalFullPallet = $order->details
            ->where('jenis', 'P')
            ->sum('qty');

        $totalReceh = $order->details
            ->where('jenis', 'R')
            ->sum('qty');

        // Summary SMU & BAS
        $summarySMU = [];
        $summaryBAS = [];

        $grouped = $order->details->groupBy('material_id');

        foreach ($grouped as $materialId => $details) {

            $material = $details->first()->material;

            if (!$material) {
                continue;
            }

            $data = [
                'mid' => $material->mid_barang,
                'qty' => $details->sum('qty'),
            ];

            // Jika principal BAS => BAS
            // selain itu => SMU
            if ($material->principal === 'BAS') {
                $summaryBAS[] = $data;
            } else {
                $summarySMU[] = $data;
            }
        }

        $pdf = PDF::loadView('pdf.wfg_loading_order', compact(
            'order',
            'totalFullPallet',
            'totalReceh',
            'summarySMU',
            'summaryBAS'
        ));

        $filename = preg_replace('/[\/\\\\]/', '-', $order->no_dokumen) . '.pdf';

        return $pdf->stream($filename);
    }
}
