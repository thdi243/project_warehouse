<?php

namespace App\Http\Controllers\Wfg;

use App\Http\Controllers\Controller;
use App\Models\NotificationsModel;
use App\Models\P2h\UserForkliftAssignmentModel;
use App\Models\User;
use App\Models\UserSignatureModel;
use App\Models\Wfg\BarangWfgModel;
use App\Models\Wfg\BongkarMuat;
use App\Models\Wfg\BongkarMuatDetail;
use App\Models\Wfg\MasterDestinasi;
use App\Models\Wrm\Inventory\StockOnHand;
use App\Models\Vehicle\Location;
use App\Models\Vehicle\VehicleTracking;
use App\Models\Vehicle\VehicleTransaction;
use App\Events\VehicleStatusUpdated;
use Barryvdh\DomPDF\Facade\Pdf;
// use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BongkarMuatController extends Controller
{
    public function index()
    {
        return view('wfg.bongkar_muat.data');
    }

    public function data(Request $request)
    {
        $perPage = 25;
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');
        $flags = $request->input('flags');

        $query = BongkarMuat::with(['forkliftDriver', 'checker', 'destinasi', 'details.material', 'verificator'])
            ->whereNotNull('jam_muat');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('wavepick_smu', 'like', "%{$search}%")
                    ->orWhere('shipment_smu', 'like', "%{$search}%")
                    ->orWhere('wavepick_bas', 'like', "%{$search}%")
                    ->orWhere('shipment_bas', 'like', "%{$search}%")
                    ->orWhere('no_dokumen', 'like', "%{$search}%")
                    ->orWhere('no_mobil', 'like', "%{$search}%");
            });
        }

        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($flags) {
            $query->whereHas('details', function ($q) use ($flags) {
                $q->where($flags, true);
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
        return view('wfg.bongkar_muat.approval');
    }

    public function approvalData(Request $request)
    {
        $perPage = 10;
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!auth()->user()->can('role', 'verificator-bongkar-muat-wfg')) {
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

        $query = BongkarMuat::with(['forkliftDriver:id,username,nama_lengkap', 'checker:id,username,nama_lengkap', 'destinasi:id,destinasi', 'details.material'])
            ->where('status', 'finished');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('wavepick_smu', 'like', "%{$search}%")
                    ->orWhere('shipment_smu', 'like', "%{$search}%")
                    ->orWhere('wavepick_bas', 'like', "%{$search}%")
                    ->orWhere('shipment_bas', 'like', "%{$search}%")
                    ->orWhere('no_dokumen', 'like', "%{$search}%")
                    ->orWhere('no_mobil', 'like', "%{$search}%");
            });
        }

        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $paginated
        ]);
    }

    public function create(Request $request)
    {
        // Check if create_new is requested
        if ($request->query('create_new')) {
            $draft = BongkarMuat::create([
                'created_by' => Auth::id(),
                'status' => 'draft',
                'tanggal' => date('Y-m-d'),
            ]);
            return redirect()->route('wfg.bongkar_muat.form', ['draft_id' => $draft->id]);
        }

        $draftId = $request->query('draft_id');

        if (!$draftId) {
            // Find latest active/unfinished record for this user
            $latestActive = BongkarMuat::where('created_by', Auth::id())
                ->whereIn('status', ['draft', 'submitted', 'approved'])
                ->latest()
                ->first();

            if ($latestActive) {
                if ($latestActive->status === 'draft') {
                    return redirect()->route('wfg.bongkar_muat.form', ['draft_id' => $latestActive->id]);
                } else {
                    return redirect()->route('wfg.bongkar_muat.show', $latestActive->id);
                }
            }

            // If no active records exist at all, create a new draft
            $newDraft = BongkarMuat::create([
                'created_by' => Auth::id(),
                'status' => 'draft',
                'tanggal' => date('Y-m-d'),
            ]);
            return redirect()->route('wfg.bongkar_muat.form', ['draft_id' => $newDraft->id]);
        }

        // We have a draft_id, retrieve it
        $draft = BongkarMuat::with('details.material')
            ->where('id', $draftId)
            ->where('created_by', Auth::id())
            ->where('status', 'draft')
            ->first();

        if (!$draft) {
            return redirect()->route('wfg.bongkar_muat.form')
                ->with('error', 'Draft tidak ditemukan atau bukan milik Anda.');
        }

        // Jika row item (details) masih kosong, tanggal otomatis selalu ngikutin today saja
        $hasItems = $draft->details->contains(function ($detail) {
            return !empty($detail->material_id);
        });
        if (!$hasItems) {
            $draft->update(['tanggal' => date('Y-m-d')]);
        }

        // Load all active drafts for tabs
        $allDrafts = BongkarMuat::where('created_by', Auth::id())
            ->whereIn('status', ['draft', 'submitted', 'approved'])
            ->latest()
            ->get();

        $forkliftDrivers = UserForkliftAssignmentModel::with('user')
            ->where('is_active', true)
            ->get()
            ->pluck('user')
            ->unique('id');

        $checkers = User::role('checker')->get();
        $destinations = MasterDestinasi::select('id', 'destinasi')
            ->where('active', true)
            ->get();

        // Gates currently in use (released when status, verified, or rejected)
        $bookedGates = BongkarMuat::whereIn('status', ['draft', 'submitted', 'approved'])
            ->where('id', '!=', $draftId)
            ->whereNotNull('gate')
            ->pluck('gate')
            ->toArray();

        return view('wfg.bongkar_muat.form', compact('forkliftDrivers', 'checkers', 'destinations', 'draft', 'bookedGates', 'allDrafts'));
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

        // $lastOrder = BongkarMuat::whereNotNull('no_dokumen')
        //     ->whereYear('created_at', $now->year)
        //     ->whereMonth('created_at', $now->month)
        //     ->orderBy('id', 'desc')
        //     ->first();

        $lastNumber = BongkarMuat::whereNotNull('no_dokumen')
            ->selectRaw('MAX(CAST(SUBSTRING_INDEX(no_dokumen, "/", 1) AS UNSIGNED)) as max_no')
            ->value('max_no');

        $lastNumber = $lastNumber ?: 3489;

        $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        return $newNumber . '/Rev.00/WFG/' . $bulan[$now->month] . '/' . $now->year;
    }

    public function saveDraft(Request $request)
    {
        try {
            DB::beginTransaction();

            $draftId = $request->input('id');
            if (!$draftId) {
                return response()->json(['status' => false, 'message' => 'Draft ID is required.'], 400);
            }

            $order = BongkarMuat::where('id', $draftId)
                ->where('created_by', Auth::id())
                ->where('status', 'draft')
                ->first();

            if (!$order) {
                return response()->json(['status' => false, 'message' => 'Draft tidak ditemukan atau bukan milik Anda.'], 404);
            }

            // Validasi gate bentrok
            // if ($request->filled('gate')) {
            //     $gateCollision = BongkarMuat::whereIn('status', ['draft', 'submitted', 'approved'])
            //         ->where('id', '!=', $draftId)
            //         ->where('gate', $request->gate)
            //         ->exists();

            //     if ($gateCollision) {
            //         return response()->json([
            //             'status' => false,
            //             'message' => 'Gate ' . $request->gate . ' sudah digunakan oleh draft/order lain.'
            //         ], 422);
            //     }
            // }

            $order->update([
                'tanggal' => $request->tanggal,
                'no_dokumen' => null,
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
                'updated_by' => Auth::id(),
                'checker_id' => Auth::id(),
            ]);

            // Jam muat otomatis ketika item pertama disimpan, reset jika kosong
            $hasMaterialDetails = collect($request->details ?? [])->contains(function ($detail) {
                return !empty($detail['material_id']);
            });

            if ($hasMaterialDetails) {
                if (empty($order->jam_muat)) {
                    $order->jam_muat = Carbon::now()->format('H:i:s');
                    $order->save();
                }
            } else {
                $order->jam_muat = null;
                $order->tanggal = date('Y-m-d');
                $order->save();
            }

            // Sync details
            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    if (empty($detail['material_id'])) {
                        continue;
                    }

                    $jenis = $detail['jenis'] ?? 'P';
                    if ($jenis === 'R') {
                        $material = BarangWfgModel::find($detail['material_id']);
                        if ($material) {
                            $qtyBox = (int) $material->qty_box;
                            if (($detail['qty'] ?? 0) > $qtyBox) {
                                return response()->json([
                                    'status' => false,
                                    'message' => "Kuantitas untuk Receh (R) pada material {$material->nama_barang} tidak boleh melebihi Qty Box Master ({$qtyBox})."
                                ], 422);
                            }
                        }
                    }

                    // Validasi: cancel_to tidak boleh dipilih bersamaan dengan double_po atau manual_picking
                    $cancelTo = isset($detail['cancel_to']) && $detail['cancel_to'];
                    $doublePo = isset($detail['double_po']) && $detail['double_po'];
                    $manualPicking = isset($detail['manual_picking']) && $detail['manual_picking'];

                    if ($cancelTo && ($doublePo || $manualPicking)) {
                        return response()->json([
                            'status' => false,
                            'message' => "Cancel TO tidak boleh dipilih bersamaan dengan Double PO atau Manual Picking."
                        ], 422);
                    }
                }
            }

            $order->details()->delete();
            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    BongkarMuatDetail::create([
                        'bongkar_muat_id' => $order->id,
                        'barcode' => $detail['barcode'] ?? null,
                        'material_id' => $this->cleanNull($detail['material_id'] ?? null),
                        'batch_number' => $detail['batch_number'] ?? null,
                        'jenis' => $detail['jenis'] ?? 'P',
                        'qty' => $detail['qty'] ?? 0,
                        'to_dummy' => $detail['to_dummy'] ?? null,
                        'to_sap' => $detail['to_sap'] ?? null,
                        'double_po' => isset($detail['double_po']) ? true : false,
                        'cancel_to' => isset($detail['cancel_to']) ? true : false,
                        'manual_picking' => isset($detail['manual_picking']) ? true : false,
                    ]);
                }
            }

            DB::commit();

            // Sinkronisasi status proses muat ke Vehicle Monitoring (WFG & SMU - slipsheet & curah)
            if (!empty($order->no_mobil)) {
                $this->syncVehicleDraftLoading($order->no_mobil);
            }

            $formattedTanggal = $order->tanggal;
            if ($formattedTanggal instanceof \Carbon\Carbon) {
                $formattedTanggal = $formattedTanggal->format('Y-m-d');
            } elseif ($formattedTanggal instanceof \DateTimeInterface) {
                $formattedTanggal = $formattedTanggal->format('Y-m-d');
            } elseif (is_string($formattedTanggal)) {
                $formattedTanggal = substr($formattedTanggal, 0, 10);
            } else {
                $formattedTanggal = date('Y-m-d');
            }

            return response()->json([
                'status' => true,
                'message' => 'Progress saved.',
                'jam_muat' => $order->jam_muat,
                'tanggal' => $formattedTanggal
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function cancelDraft(Request $request)
    {
        try {
            DB::beginTransaction();

            $draftId = $request->input('id');
            if (!$draftId) {
                return response()->json(['status' => false, 'message' => 'Draft ID is required.'], 400);
            }

            $existingDraft = BongkarMuat::where('id', $draftId)
                ->where('created_by', Auth::id())
                ->where('status', 'draft')
                ->first();

            if ($existingDraft) {
                $existingDraft->details()->delete();
                $existingDraft->delete();
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Draft cancelled and form reset.']);
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

        // Validasi gate bentrok
        $draftId = $request->input('id');
        if ($request->filled('gate')) {
            $gateCollision = BongkarMuat::whereIn('status', ['draft', 'submitted', 'approved'])
                ->where('id', '!=', $draftId)
                ->where('gate', $request->gate)
                ->exists();

            if ($gateCollision) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gate ' . $request->gate . ' sudah digunakan oleh draft/order lain.'
                ], 422);
            }
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

            $query = BongkarMuat::where('status', '!=', 'draft')
                ->where('created_by', '!=', Auth::id());

            $query->where(function ($q) use ($request) {

                $hasCondition = false;

                if (!in_array($request->wavepick_smu, [null, '', '0', 0], true)) {
                    $q->where('wavepick_smu', $request->wavepick_smu);
                    $hasCondition = true;
                }

                if (!in_array($request->wavepick_bas, [null, '', '0', 0], true)) {
                    if ($hasCondition) {
                        $q->orWhere('wavepick_bas', $request->wavepick_bas);
                    } else {
                        $q->where('wavepick_bas', $request->wavepick_bas);
                    }
                }
            });

            $existWavepick = $query->exists();

            if ($existWavepick) {
                return response()->json([
                    'status' => false,
                    'message' => 'Wavepick SMU atau BAS sudah pernah digunakan, silahkan koordinasi dengan admin.'
                ], 422);
            }

            // Find existing draft or create new
            $draftId = $request->input('id');
            $order = null;
            if ($draftId) {
                $order = BongkarMuat::where('id', $draftId)
                    ->where('created_by', Auth::id())
                    ->where('status', 'draft')
                    ->first();
            }

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Draft tidak ditemukan atau bukan milik Anda.'
                ], 404);
            }

            if ($order->no_dokumen === null || $order->no_dokumen === '') {
                $noDok = $this->generateNoDokumen();
            } else {
                $noDok = $order->no_dokumen;
            }

            $orderData = [
                'tanggal' => $request->tanggal,
                'no_dokumen' => $noDok,
                'shipment_smu' => $request->shipment_smu,
                'wavepick_smu' => $request->wavepick_smu,
                'shipment_bas' => $request->shipment_bas,
                'wavepick_bas' => $request->wavepick_bas,
                'forklift_driver_id' => $request->forklift_driver_id,
                'checker_id' => $order->checker_id ?? Auth::id(),
                'destinasi_id' => $request->destinasi_id,
                'no_mobil' => $request->no_mobil,
                'gate' => $request->gate,
                'no_kontainer' => $request->no_kontainer,
                'no_segel_bas' => $request->no_segel_bas,
                'no_segel_vendor' => $request->no_segel_vendor,
                'jumlah_slipsheet' => $request->jumlah_slipsheet ?? 0,
                'jam_muat' => $request->jam_muat ?? Carbon::now()->format('H:i:s'),
                'jam_selesai' => Carbon::now()->format('H:i:s'),
                'status' => 'submitted',
                'created_by' => Auth::id(),
            ];

            if ($order) {
                $order->update($orderData);
            } else {
                $order = BongkarMuat::create($orderData);
            }

            // Validasi qty box untuk detail dengan jenis R dan validasi flags mutual exclusion
            foreach ($request->details as $detail) {
                $jenis = $detail['jenis'] ?? 'P';
                if ($jenis === 'R') {
                    $material = BarangWfgModel::find($detail['material_id']);
                    if ($material) {
                        $qtyBox = (int) $material->qty_box;
                        if (($detail['qty'] ?? 0) > $qtyBox) {
                            return response()->json([
                                'status' => false,
                                'message' => "Kuantitas untuk Receh (R) pada material {$material->nama_barang} tidak boleh melebihi Qty Box Master ({$qtyBox})."
                            ], 422);
                        }
                    }
                }

                // Validasi: cancel_to tidak boleh dipilih bersamaan dengan double_po atau manual_picking
                $cancelTo = isset($detail['cancel_to']) && $detail['cancel_to'];
                $doublePo = isset($detail['double_po']) && $detail['double_po'];
                $manualPicking = isset($detail['manual_picking']) && $detail['manual_picking'];

                if ($cancelTo && ($doublePo || $manualPicking)) {
                    return response()->json([
                        'status' => false,
                        'message' => "Cancel TO tidak boleh dipilih bersamaan dengan Double PO atau Manual Picking."
                    ], 422);
                }
            }

            $order->details()->delete();
            foreach ($request->details as $detail) {
                BongkarMuatDetail::create([
                    'bongkar_muat_id' => $order->id,
                    'material_id' => $detail['material_id'],
                    'batch_number' => $this->cleanNull($detail['batch_number'] ?? null),
                    'jenis' => $detail['jenis'] ?? 'P',
                    'qty' => $detail['qty'] ?? 0,
                    'to_dummy' => $this->cleanNull($detail['to_dummy'] ?? null),
                    'to_sap' => $this->cleanNull($detail['to_sap'] ?? null),
                    'double_po' => $detail['double_po'] ?? false,
                    'cancel_to' => $detail['cancel_to'] ?? false,
                    'manual_picking' => $detail['manual_picking'] ?? false,
                ]);
            }

            DB::commit();

            // Sinkronisasi status selesai muat ke Vehicle Monitoring (WFG & SMU - slipsheet & curah)
            if (!empty($order->no_mobil)) {
                $this->syncVehicleFinishLoading($order->no_mobil);
            }

            return response()->json(['status' => true, 'message' => 'Bongkar Muat submitted successfully.', 'redirect' => route('wfg.bongkar_muat.show', $order->id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $order = BongkarMuat::with(['details.material', 'forkliftDriver', 'checker', 'verificator', 'destinasi'])->findOrFail($id);
        $checkers = User::role('checker')->get();
        return view('wfg.bongkar_muat.show', compact('order', 'checkers'));
    }

    public function approveChecker(Request $request, $id)
    {
        $request->validate([
            'checker_id' => 'required|exists:users,id',
            'signature' => 'required|string', // Base64 signature
        ]);

        $order = BongkarMuat::findOrFail($id);

        if ($order->status !== 'submitted') {
            return back()->with('error', 'Order is not in submitted status.');
        }

        if ($order->checker_id && (int) $order->checker_id !== (int) Auth::id()) {
            return back()->with('error', 'Anda bukan checker yang ditugaskan untuk Bongkar Muat ini.');
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

    public function followUpChecker($id)
    {
        $order = BongkarMuat::with('checker')->findOrFail($id);

        if (!in_array($order->status, ['submitted', 'draft'])) {
            return response()->json([
                'status' => false,
                'message' => 'Follow up hanya bisa dikirim untuk status submitted atau draft.'
            ], 422);
        }

        if (!$order->checker_id) {
            return response()->json([
                'status' => false,
                'message' => 'Checker belum ditentukan untuk Bongkar Muat ini.'
            ], 422);
        }

        $url = $order->status === 'draft'
            ? route('wfg.bongkar_muat.form', ['draft_id' => $order->id])
            : route('wfg.bongkar_muat.show', $order->id);

        $message = $order->status === 'draft'
            ? 'Form Bongkar Muat Anda belum disubmit, tolong segera disubmit, dari Admin.'
            : 'Bongkar Muat ' . ($order->no_mobil ?? '') . ' menunggu approval Checker Anda.';

        NotificationsModel::updateOrCreate(
            [
                'user_id' => $order->checker_id,
                'notifiable_type' => BongkarMuat::class,
                'notifiable_id' => $order->id,
                'title' => 'Info Bongkar Muat',
            ],
            [
                'message' => $message,
                'url' => $url,
                'is_read' => false,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Follow up berhasil dikirim ke checker ' . ($order->checker->username ?? '') . '.'
        ]);
    }

    public function approveDriver(Request $request, $id)
    {
        $request->validate([
            'driver_name' => 'required|string|max:255',
            'signature' => 'required|string', // Base64 signature
        ]);

        $order = BongkarMuat::findOrFail($id);

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
            'status' => 'finished' // Final status before verification
        ]);

        // Delete checker follow-up notifications
        NotificationsModel::where('notifiable_type', BongkarMuat::class)
            ->where('notifiable_id', $order->id)
            ->whereIn('title', ['Info Bongkar Muat', 'Follow Up Checker Bongkar Muat'])
            ->delete();

        // Kirim notifikasi ke semua verificator
        $verificators = User::role('verificator-bongkar-muat-wfg')->get();
        foreach ($verificators as $verificator) {
            NotificationsModel::create([
                'user_id' => $verificator->id,
                'notifiable_type' => BongkarMuat::class,
                'notifiable_id' => $order->id,
                'title' => 'Bongkar Muat Menunggu Verifikasi',
                'message' => "Bongkar Muat {$order->no_dokumen} telah diselesaikan oleh checker dan menunggu verifikasi Anda.",
                'url' => route('wfg.bongkar_muat.show', $order->id),
                'is_read' => false,
            ]);
        }

        // Auto finish vehicle tracking if active transaction exists matching no_mobil (WFG & SMU, slipsheet & curah)
        if (!empty($order->no_mobil)) {
            $this->syncVehicleFinishLoading($order->no_mobil);
        }

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
            $path = 'signatures/bongkar_muat/' . $fileName;

            Storage::disk('public')->put($path, $base64Data);

            return 'storage/' . $path;
        } catch (\Exception $e) {
            Log::error('Signature save error: ' . $e->getMessage());
            return null;
        }
    }

    public function validateOrder(Request $request, $id)
    {
        if (!auth()->user()->can('role', 'verificator-bongkar-muat-wfg')) {
            return back()->with('error', 'Unauthorized. Anda tidak memiliki role verificator.');
        }

        $order = BongkarMuat::with('details')->findOrFail($id);

        if ($order->status !== 'finished') {
            return back()->with('error', 'Order belum siap untuk diverifikasi.');
        }

        // Signature Validation and Storage Logic
        $signaturePath = null;
        if ($request->boolean('use_stored_signature')) {
            $userSig = auth()->user()->signature;
            if ($userSig && $userSig->signature) {
                $signaturePath = $userSig->signature;
            } else {
                return back()->with('error', 'Anda mencentang gunakan TTD tersimpan, tetapi profil Anda belum memiliki TTD tersimpan.');
            }
        } else {
            $request->validate([
                'signature' => 'required|string',
            ]);
            $signaturePath = $this->saveSignature($request->signature, '/verificator/verificator_' . $id);
            if (!$signaturePath) {
                return back()->with('error', 'Gagal menyimpan tanda tangan.');
            }

            // Save signature as profile default if not already exists
            \App\Models\UserSignatureModel::firstOrCreate(
                ['user_id' => Auth::id()],
                ['signature' => $signaturePath]
            );
        }

        // Hapus semua notifikasi terkait order ini untuk semua verificator
        NotificationsModel::where('notifiable_type', BongkarMuat::class)
            ->where('notifiable_id', $order->id)
            ->delete();

        // Save additional verification details
        if ($request->has('details')) {
            foreach ($request->details as $detailData) {
                if (isset($detailData['id'])) {
                    $detail = $order->details()->find($detailData['id']);
                    if ($detail) {
                        $updateFields = [];
                        if ($detail->double_po || $detail->cancel_to || $detail->manual_picking) {
                            $updateFields['no_to'] = $this->cleanNull($detailData['no_to'] ?? null);
                        }
                        if ($detail->cancel_to) {
                            $updateFields['qty_to'] = $this->cleanNull($detailData['qty_to'] ?? null);
                        }
                        if (!empty($updateFields)) {
                            $detail->update($updateFields);
                        }
                    }
                }
            }
        }

        // Mock Validation Logic: 
        // Check if all items belong to the wavepick.
        // In real scenario, we would query a wavepick_details table.
        $isValid = true; // Assume true for now

        if ($isValid) {
            $order->update([
                'status' => 'verified',
                'verified_by' => Auth::id(),
                'verified_at' => Carbon::now(),
                'verified_signature' => $signaturePath,
                'verified_note' => $request->verified_note ?? null
            ]);
            return back()->with('success', 'Order verified successfully.');
        } else {
            $order->update([
                'status' => 'rejected',
                'verified_note' => 'Items do not match wavepick records.'
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
                    'qty' => $soh->qty,
                    'qty_box' => $wfgBarang->qty_box,
                    'principal' => $wfgBarang->principal
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
                'qty_box' => $m->qty_box,
                'principal' => $m->principal
            ];
        }));
    }

    public function destroy($id)
    {
        $order = BongkarMuat::findOrFail($id);

        // Only allow deletion if not yet verified or heavily processed
        if (in_array($order->status, ['draft', 'submitted', 'rejected', 'approved', 'finished'])) {
            // Hapus notifikasi jika ada
            NotificationsModel::where('notifiable_type', BongkarMuat::class)
                ->where('notifiable_id', $order->id)
                ->delete();

            $order->delete();
            return response()->json(['status' => true, 'message' => 'Bongkar Muat successfully deleted.']);
        }

        return response()->json(['status' => false, 'message' => 'Cannot delete Bongkar Muat with status ' . $order->status], 403);
    }

    public function download($id)
    {
        $order = BongkarMuat::with([
            'details.material',
            'forkliftDriver',
            'checker',
            'verificator',
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

        $pdf = Pdf::loadView('pdf.wfg_bongkar_muat', compact('order', 'totalFullPallet', 'totalReceh', 'summarySMU', 'summaryBAS'));

        $filename = preg_replace('/[\/\\\\]/', '-', $order->no_dokumen) . '.pdf';

        return $pdf->stream($filename);
    }

    public function updateItem(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'batch_number' => 'nullable|string',
            'jenis' => 'required|in:P,R',
            'qty' => 'required|numeric|min:0',
            'to_dummy' => 'nullable|string',
            'to_sap' => 'nullable|string',
            'double_po' => 'boolean',
            'cancel_to' => 'boolean',
            'manual_picking' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $item = BongkarMuatDetail::findOrFail($id);

            // Validasi qty box untuk detail dengan jenis R
            $jenis = $request->jenis;
            if ($jenis === 'R') {
                $material = BarangWfgModel::find($item->material_id);
                if ($material) {
                    $qtyBox = (int) $material->qty_box;
                    if ($request->qty > $qtyBox) {
                        return response()->json([
                            'status' => false,
                            'message' => "Kuantitas untuk Receh (R) tidak boleh melebihi Qty Box Master ({$qtyBox})."
                        ], 422);
                    }
                }
            }

            // Validasi: cancel_to tidak boleh dipilih bersamaan dengan double_po atau manual_picking
            $cancelTo = $request->cancel_to ?? false;
            $doublePo = $request->double_po ?? false;
            $manualPicking = $request->manual_picking ?? false;

            if ($cancelTo && ($doublePo || $manualPicking)) {
                return response()->json([
                    'status' => false,
                    'message' => "Cancel TO tidak boleh dipilih bersamaan dengan Double PO atau Manual Picking."
                ], 422);
            }

            $item->update([
                'batch_number' => $this->cleanNull($request->batch_number),
                'jenis' => $request->jenis,
                'qty' => $request->qty,
                'to_dummy' => $this->cleanNull($request->to_dummy),
                'to_sap' => $this->cleanNull($request->to_sap),
                'double_po' => $request->double_po ?? false,
                'cancel_to' => $request->cancel_to ?? false,
                'manual_picking' => $request->manual_picking ?? false,
            ]);

            return response()->json(['status' => true, 'message' => 'Item updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function deleteItem($id)
    {
        try {
            $item = BongkarMuatDetail::findOrFail($id);
            $item->delete();

            return response()->json(['status' => true, 'message' => 'Item deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'shipment_smu' => 'nullable|string',
            'wavepick_smu' => 'nullable|string',
            'shipment_bas' => 'nullable|string',
            'wavepick_bas' => 'nullable|string',
            'no_mobil' => 'nullable|string',
            'no_kontainer' => 'nullable|string',
            'no_segel_bas' => 'nullable|string',
            'no_segel_vendor' => 'nullable|string',
            'jumlah_slipsheet' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $order = BongkarMuat::findOrFail($id);
            $order->update($request->all());

            return response()->json(['status' => true, 'message' => 'Bongkar Muat updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    private function cleanNull($val)
    {
        if (is_null($val)) return null;
        $val = trim($val);
        if ($val === '0' || $val === 0 || preg_match('/^0+$/', $val)) {
            return $val;
        }
        if ($val === '' || strtolower($val) === 'null' || strtolower($val) === 'undefined') {
            return null;
        }
        return $val;
    }

    /**
     * Sinkronisasi status proses muat ke Vehicle Monitoring saat draft disimpan di form Bongkar Muat.
     * Hanya berlaku untuk kendaraan WFG & SMU dengan jenis slipsheet atau curah.
     */
    private function syncVehicleDraftLoading($noMobil)
    {
        try {
            if (!$noMobil) return;

            $cleanNoMobil = strtoupper(str_replace([' ', '-', '.', '_'], '', $noMobil));

            // Cari transaksi aktif kendaraan WFG atau SMU dengan jenis slipsheet / curah
            $transaction = VehicleTransaction::whereIn('status', ['wfg', 'smu'])
                ->whereIn('jenis', ['bongkaran', 'slipsheet', 'curah'])
                ->whereHas('vehicle', function ($q) use ($cleanNoMobil) {
                    $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_pol, ' ', ''), '-', ''), '.', ''), '_', '') = ?", [$cleanNoMobil]);
                })
                ->where('unloading_status', '!=', 'completed')
                ->first();

            if (!$transaction) {
                $transaction = VehicleTransaction::whereNotIn('status', ['completed', 'timbangan_out'])
                    ->whereIn('jenis', ['bongkaran', 'slipsheet', 'curah'])
                    ->whereHas('targetLocation', function ($tl) {
                        $tl->whereIn('s_loc', ['A001', 'SMU', 'A002']);
                    })
                    ->whereHas('vehicle', function ($q) use ($cleanNoMobil) {
                        $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_pol, ' ', ''), '-', ''), '.', ''), '_', '') = ?", [$cleanNoMobil]);
                    })
                    ->where('unloading_status', '!=', 'completed')
                    ->first();
            }

            if ($transaction && ($transaction->unloading_status !== 'process' || empty($transaction->no_antrian))) {
                DB::beginTransaction();

                $now = Carbon::now();
                $isSmu = ($transaction->status === 'smu') ||
                         ($transaction->targetLocation && in_array($transaction->targetLocation->s_loc, ['SMU', 'A002']));
                $areaName = $isSmu ? 'SMU' : 'WFG';
                $newStatus = $isSmu ? 'smu' : 'wfg';
                $targetSloc = $transaction->targetLocation ? $transaction->targetLocation->s_loc : ($isSmu ? 'A002' : 'A001');

                // Isi nomor antrian urut otomatis jika belum ada nomor antrian
                $assignedAntrian = $transaction->no_antrian;
                if (empty($assignedAntrian)) {
                    $txJenis = strtolower(trim($transaction->jenis ?? ''));
                    $maxAntrianQuery = VehicleTransaction::where(function ($q) use ($newStatus, $isSmu) {
                        $q->where('status', $newStatus)
                            ->orWhereHas('targetLocation', function ($tl) use ($isSmu) {
                                if ($isSmu) {
                                    $tl->whereIn('s_loc', ['SMU', 'A002']);
                                } else {
                                    $tl->where('s_loc', 'A001');
                                }
                            });
                    })
                        ->whereNotNull('no_antrian');

                    if ($newStatus === 'wfg' && !empty($txJenis)) {
                        $maxAntrianQuery->where('jenis', $txJenis);
                    }

                    $maxAntrian = $maxAntrianQuery->get()
                        ->map(function ($tx) {
                            return (int)$tx->no_antrian;
                        })
                        ->max();

                    $nextAntrian = $maxAntrian ? $maxAntrian + 1 : 1;
                    $assignedAntrian = str_pad($nextAntrian, 2, '0', STR_PAD_LEFT);
                }

                $updateData = [
                    'status' => $newStatus,
                    'no_antrian' => $assignedAntrian,
                    'queue_taken_time' => $transaction->queue_taken_time ?? $now,
                    'queue_taken_by' => $transaction->queue_taken_by ?? Auth::id(),
                    'unloading_status' => 'process',
                    'start_loading_time' => $transaction->start_loading_time ?? $now,
                    'start_loading_by' => $transaction->start_loading_by ?? Auth::id(),
                    'updated_by' => Auth::id()
                ];

                if ($transaction->target_location_id && $transaction->current_location_id !== $transaction->target_location_id) {
                    $updateData['current_location_id'] = $transaction->target_location_id;
                }

                $transaction->update($updateData);

                $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                    ->where('location_id', $transaction->current_location_id)
                    ->whereNull('departure_time')
                    ->latest()
                    ->first();

                if ($activeTrack) {
                    $activeTrack->update([
                        'status_notes' => "Mulai Proses Muat di {$areaName} - Antrian #{$assignedAntrian} (Sinkron dari Form Bongkar Muat Draft)."
                    ]);
                }

                $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : $noMobil;

                // Release dari kantong parkir jika masih menempati slot
                $this->releaseKantongParkirSlot($noPol, "Mulai proses Muat di {$areaName} (Form Bongkar Muat Draft)");

                event(new VehicleStatusUpdated([
                    'transaction_id' => $transaction->id,
                    'no_pol' => $noPol,
                    'current_location' => $targetSloc,
                    'status' => $newStatus,
                    'no_antrian' => $assignedAntrian,
                    'message' => "Truk {$noPol} mulai proses Muat di {$areaName} (Antrian #{$assignedAntrian} - Sinkron dari Form Bongkar Muat Draft).",
                    'time' => $now->format('H:i:s')
                ]));

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::warning("Gagal sinkronisasi draft ke Vehicle Monitoring untuk {$noMobil}: " . $e->getMessage());
        }
    }

    /**
     * Sinkronisasi selesai muat ke Vehicle Monitoring saat form Bongkar Muat submitted atau driver approve.
     * Truk diarahkan kembali ke Timbangan Out.
     */
    private function syncVehicleFinishLoading($noMobil)
    {
        try {
            if (!$noMobil) return;

            $cleanNoMobil = strtoupper(str_replace([' ', '-', '.', '_'], '', $noMobil));

            // Cari transaksi aktif kendaraan WFG atau SMU dengan jenis slipsheet / curah
            $transaction = VehicleTransaction::whereIn('status', ['wfg', 'smu'])
                ->whereIn('jenis', ['bongkaran', 'slipsheet', 'curah'])
                ->whereHas('vehicle', function ($q) use ($cleanNoMobil) {
                    $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_pol, ' ', ''), '-', ''), '.', ''), '_', '') = ?", [$cleanNoMobil]);
                })
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'timbangan_out')
                ->first();

            if (!$transaction) {
                $transaction = VehicleTransaction::whereNotIn('status', ['completed', 'timbangan_out'])
                    ->whereIn('jenis', ['bongkaran', 'slipsheet', 'curah'])
                    ->whereHas('targetLocation', function ($tl) {
                        $tl->whereIn('s_loc', ['A001', 'SMU', 'A002']);
                    })
                    ->whereHas('vehicle', function ($q) use ($cleanNoMobil) {
                        $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_pol, ' ', ''), '-', ''), '.', ''), '_', '') = ?", [$cleanNoMobil]);
                    })
                    ->first();
            }

            if ($transaction) {
                DB::beginTransaction();

                $now = Carbon::now();
                $isSmu = ($transaction->status === 'smu') ||
                         ($transaction->targetLocation && in_array($transaction->targetLocation->s_loc, ['SMU', 'A002']));
                $currentStatus = $isSmu ? 'smu' : 'wfg';
                $areaName = $isSmu ? 'SMU' : 'WFG';
                $targetSloc = $transaction->targetLocation ? $transaction->targetLocation->s_loc : ($isSmu ? 'A002' : 'A001');

                // Conclude active tracking di WFG / SMU
                $activeTrack = VehicleTracking::where('vehicle_transaction_id', $transaction->id)
                    ->where('location_id', $transaction->current_location_id)
                    ->whereNull('departure_time')
                    ->latest()
                    ->first();

                $duration = $activeTrack ? abs($now->diffInSeconds($activeTrack->arrival_time, false)) : 0;

                if ($activeTrack) {
                    $activeTrack->update([
                        'departure_time' => $now,
                        'duration_seconds' => $duration,
                        'status_notes' => "Proses Muat di {$areaName} Selesai (Otomatis dari Form Bongkar Muat Selesai). Truk kembali ke Timbangan."
                    ]);
                }

                $noPol = $transaction->vehicle ? $transaction->vehicle->no_pol : $noMobil;

                $timbanganLoc = Location::where('s_loc', 'TMB')->first();
                if ($timbanganLoc) {
                    $completedAntrian = $transaction->no_antrian ? (int)$transaction->no_antrian : 0;

                    // Update transaction to timbangan_out beserta timestamps lifecycle
                    $transaction->update([
                        'unloading_status' => 'completed',
                        'queue_taken_time' => $transaction->queue_taken_time ?? $now,
                        'queue_taken_by' => $transaction->queue_taken_by ?? Auth::id(),
                        'start_loading_time' => $transaction->start_loading_time ?? $now,
                        'start_loading_by' => $transaction->start_loading_by ?? Auth::id(),
                        'finish_loading_time' => $transaction->finish_loading_time ?? $now,
                        'finish_loading_by' => $transaction->finish_loading_by ?? Auth::id(),
                        'timbangan_out_time' => $transaction->timbangan_out_time ?? $now,
                        'timbangan_out_by' => $transaction->timbangan_out_by ?? Auth::id(),
                        'current_location_id' => $timbanganLoc->id,
                        'status' => 'timbangan_out',
                        'no_antrian' => null, // Clear queue
                        'updated_by' => Auth::id()
                    ]);

                    // Shift remaining active queues in area tersebut (jika WFG, shift per jenis)
                    if ($completedAntrian > 0) {
                        $txJenis = strtolower(trim($transaction->jenis ?? ''));
                        $otherActiveQuery = VehicleTransaction::where(function ($q) use ($currentStatus, $isSmu) {
                            $q->where('status', $currentStatus)
                                ->orWhereHas('targetLocation', function ($tl) use ($isSmu) {
                                    if ($isSmu) {
                                        $tl->whereIn('s_loc', ['SMU', 'A002']);
                                    } else {
                                        $tl->where('s_loc', 'A001');
                                    }
                                });
                        })
                            ->whereNotNull('no_antrian');

                        if ($currentStatus === 'wfg' && !empty($txJenis)) {
                            $otherActiveQuery->where('jenis', $txJenis);
                        }

                        $otherActive = $otherActiveQuery->get();
                        foreach ($otherActive as $tx) {
                            $currAntrian = (int)$tx->no_antrian;
                            if ($currAntrian > $completedAntrian) {
                                $tx->update(['no_antrian' => str_pad($currAntrian - 1, 2, '0', STR_PAD_LEFT)]);
                            }
                        }
                    }

                    // Create new tracking log for Timbangan Out
                    VehicleTracking::create([
                        'vehicle_transaction_id' => $transaction->id,
                        'location_id' => $timbanganLoc->id,
                        'arrival_time' => $now,
                        'status_notes' => "Selesai dari {$areaName}. Menunggu Timbang Keluar di Timbangan.",
                        'created_by' => Auth::id(),
                    ]);

                    event(new VehicleStatusUpdated([
                        'transaction_id' => $transaction->id,
                        'no_pol' => $noPol,
                        'current_location' => 'TIMBANGAN',
                        'status' => 'timbangan_out',
                        'message' => "Proses Muat Truk {$noPol} di {$areaName} telah selesai (Otomatis dari Form Bongkar Muat Selesai). Truk kembali ke Timbangan untuk Check-Out.",
                        'time' => $now->format('H:i:s')
                    ]));
                }

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::warning("Gagal sinkronisasi finish ke Vehicle Monitoring untuk {$noMobil}: " . $e->getMessage());
        }
    }

    /**
     * Internal helper to release vehicle from external Kantong Parkir slot in MyBAS.
     */
    private function releaseKantongParkirSlot($noPol, $keterangan = null)
    {
        try {
            if (!$noPol) return;
            $baseUrl = env('MYBAS_API_URL', 'http://127.0.0.1:8081');
            $url = rtrim($baseUrl, '/') . '/api/kantong-parkir/release';

            Http::timeout(3)->post($url, [
                'no_polisi' => $noPol,
                'keterangan' => $keterangan ?? 'Dipanggil ke dock / antrian warehouse'
            ]);
        } catch (\Throwable $th) {
            Log::warning("Gagal mengirim release parkir ke MyBAS untuk {$noPol}: " . $th->getMessage());
        }
    }
}
