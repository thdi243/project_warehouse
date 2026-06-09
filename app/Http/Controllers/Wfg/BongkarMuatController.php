<?php

namespace App\Http\Controllers\Wfg;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wfg\BongkarMuat;
use App\Models\Wfg\BongkarMuatDetail;
use App\Models\Wrm\Inventory\StockOnHand;
use App\Models\Wfg\stock_opname\BarangWfgModel;
use App\Models\P2h\UserForkliftAssignmentModel;
use App\Models\Wfg\MasterDestinasi;
use App\Models\NotificationsModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class BongkarMuatController extends Controller
{
    public function index()
    {
        return view('wfg.bongkar_muat.data');
    }

    public function data(Request $request)
    {
        $perPage = 10;
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');
        $flags = $request->input('flags');

        $query = BongkarMuat::with(['forkliftDriver', 'checker', 'destinasi', 'details.material', 'verificator']);

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
                'created_by' => auth()->id(),
                'status' => 'draft',
                'tanggal' => date('Y-m-d'),
            ]);
            return redirect()->route('wfg.bongkar_muat.form', ['draft_id' => $draft->id]);
        }

        $draftId = $request->query('draft_id');

        if (!$draftId) {
            // Find latest active draft for this user
            $latestDraft = BongkarMuat::where('created_by', auth()->id())
                ->where('status', 'draft')
                ->latest()
                ->first();

            if (!$latestDraft) {
                // If no drafts exist at all, create one
                $latestDraft = BongkarMuat::create([
                    'created_by' => auth()->id(),
                    'status' => 'draft',
                    'tanggal' => date('Y-m-d'),
                ]);
            }
            return redirect()->route('wfg.bongkar_muat.form', ['draft_id' => $latestDraft->id]);
        }

        // We have a draft_id, retrieve it
        $draft = BongkarMuat::with('details.material')
            ->where('id', $draftId)
            ->where('created_by', auth()->id())
            ->where('status', 'draft')
            ->first();

        if (!$draft) {
            return redirect()->route('wfg.bongkar_muat.form')
                ->with('error', 'Draft tidak ditemukan atau bukan milik Anda.');
        }

        // Load all active drafts for tabs
        $allDrafts = BongkarMuat::where('created_by', auth()->id())
            ->where('status', 'draft')
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
                ->where('created_by', auth()->id())
                ->where('status', 'draft')
                ->first();

            if (!$order) {
                return response()->json(['status' => false, 'message' => 'Draft tidak ditemukan atau bukan milik Anda.'], 404);
            }

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
                'updated_by' => auth()->id(),
                'checker_id' => auth()->id(),
            ]);

            // Jam muat otomatis ketika item pertama disimpan
            $hasMaterialDetails = collect($request->details ?? [])->contains(function ($detail) {
                return !empty($detail['material_id']);
            });

            if (empty($order->jam_muat) && $hasMaterialDetails) {
                $order->jam_muat = Carbon::now()->format('H:i:s');
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
            return response()->json(['status' => true, 'message' => 'Progress saved.', 'jam_muat' => $order->jam_muat]);
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
                ->where('created_by', auth()->id())
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
                ->where('created_by', '!=', auth()->id());

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
                    ->where('created_by', auth()->id())
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
                'checker_id' => $order->checker_id ?? auth()->id(),
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
                'created_by' => auth()->id(),
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

        if ($order->checker_id && (int) $order->checker_id !== (int) auth()->id()) {
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

        NotificationsModel::where('user_id', $request->checker_id)
            ->where('notifiable_type', BongkarMuat::class)
            ->where('notifiable_id', $order->id)
            ->where('title', 'Follow Up Checker Bongkar Muat')
            ->delete();

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

        NotificationsModel::updateOrCreate(
            [
                'user_id' => $order->checker_id,
                'notifiable_type' => BongkarMuat::class,
                'notifiable_id' => $order->id,
                'title' => 'Info Bongkar Muat',
            ],
            [
                'message' => 'Form Bongkar Muat Anda belum disubmit, tolong segera disubmit, dari Admin.',
                'url' => route('wfg.bongkar_muat.form', ['draft_id' => $order->id]),
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
            \Log::error('Signature save error: ' . $e->getMessage());
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
                ['user_id' => auth()->id()],
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
                'verified_by' => auth()->id(),
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

        $pdf = PDF::loadView('pdf.wfg_bongkar_muat', compact(
            'order',
            'totalFullPallet',
            'totalReceh',
            'summarySMU',
            'summaryBAS'
        ));

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
}
