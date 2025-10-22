<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\P2h\ForkliftModel;
use App\Models\Tkbm\TkbmFeeModel;
use App\Models\P2h\PalletMoverModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\Wfg\stock_opname\BarangWfgModel;
use App\Models\Wfg\stock_opname\StockOnHandModel;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function show($id)
    {
        return view('warehouse.show', ['id' => $id]);
    }

    public function stock()
    {
        return view('tkbm.index');
    }

    public function feeTkbm()
    {
        $data = TkbmFeeModel::orderBy('created_at', 'desc')->get();

        return view('tkbm.fees_taxes', compact('data'));
    }

    public function p2hData()
    {
        return view('p2h.data_p2h');
    }

    public function showRegForklift()
    {
        // Ambil semua forklift beserta relasi assignedOperators
        $forkliftRaw = ForkliftModel::with('assignedOperators')->orderBy('nomor_unit')->get();

        // Format forklifts untuk DataTable atau view blade
        $forklifts = $forkliftRaw->map(function ($forklift) {
            $primaryOperator = $forklift->assignedOperators
                ->where('pivot.is_primary', true)
                ->first();

            $backupOperators = $forklift->assignedOperators
                ->where('pivot.is_primary', false)
                ->map(function ($user) {
                    return $user->username;
                });

            return [
                'id' => $forklift->id,
                'nomor_unit' => $forklift->nomor_unit,
                'departemen' => $forklift->departemen,
                'status' => $forklift->status,
                'description' => $forklift->description,
                'primary_operator' => $primaryOperator ? $primaryOperator->username : '-',
                'backup_operators' => $backupOperators,
                'created_at' => $forklift->created_at->format('d/m/Y H:i')
            ];
        });

        // Ambil daftar operator warehouse untuk dropdown assignment
        $operators = User::where('jabatan', 'operator')
            ->where('departemen', 'warehouse')
            ->select('id', 'username', 'nik')
            ->get();

        return view(
            'p2h.forklift_registration',
            compact('forklifts', 'operators')
        );
    }

    public function showRegPalletMover()
    {
        $pallets = PalletMoverModel::with('assignedOperators')->orderBy('nomor_unit')->get();

        $data = $pallets->map(function ($pallet) {
            $primary = $pallet->assignedOperators->where('pivot.is_primary', true)->first();
            $backup = $pallet->assignedOperators->where('pivot.is_primary', false)->pluck('username');

            return [
                'id' => $pallet->id,
                'nomor_unit' => $pallet->nomor_unit,
                'departemen' => $pallet->departemen,
                'status' => $pallet->status,
                'description' => $pallet->description,
                'primary_operator' => $primary ? $primary->username : '-',
                'backup_operators' => $backup,
                'created_at' => $pallet->created_at->format('d/m/Y H:i')
            ];
        });

        $operators = User::where('jabatan', 'operator')
            ->where('departemen', 'warehouse')
            ->select('id', 'username', 'nik')
            ->get();

        return view('p2h.pallet_mover_registration', compact('data', 'operators'));
    }

    public function barangIndex()
    {
        return view('manajemen_rak.master_barang');
    }

    public function rakIndex()
    {
        return view('manajemen_rak.master_rak');
    }

    public function onHandIndex()
    {
        return view('manajemen_rak.stock_on_hand');
    }

    public function opnameIndex()
    {
        return view('manajemen_rak.stock_opname');
    }

    public function formSOWFG()
    {
        $user = Auth::user();

        if ($user->jabatan === 'operator') {
            $allowedPrincipals = ['BAS', 'SMU'];

            $userPrincipal = optional($user->principal)->principal
                ? strtoupper(trim($user->principal->principal))
                : null;

            // Jika operator tidak punya principal
            if (!$userPrincipal) {
                return Redirect::route('dashboard')->with('error', "Akun Anda belum memiliki principal yang valid untuk mengakses fitur ini.");
            }

            // Jika principal operator tidak termasuk yang diizinkan
            if (!in_array($userPrincipal, $allowedPrincipals)) {
                return Redirect::route('dashboard')->with('error', "Anda tidak memiliki akses untuk fitur ini.");
            }
        }

        $today = Carbon::today()->toDateString();

        $dataExists = StockOnHandModel::whereDate('last_updated', $today)->exists();

        if (!$dataExists) {
            return Redirect::route('wfg.stock_opname.soh')
                ->with('error', 'Data Stock On Hand (SOH) untuk tanggal ' . $today . ' belum diunggah. Silakan unggah data SOH terlebih dahulu sebelum mengakses Form Stock Opname.');
        }

        $principals = BarangWfgModel::distinct()->pluck('principal');

        return view('stock_opname_wfg.form', compact('principals'));
    }

    public function uploadSOHWFG()
    {
        $user = Auth::user();

        if ($user->jabatan === 'operator') {
            $allowedPrincipals = ['BAS', 'SMU'];

            $userPrincipal = optional($user->principal)->principal
                ? strtoupper(trim($user->principal->principal))
                : null;

            // Jika operator tidak punya principal
            if (!$userPrincipal) {
                return Redirect::route('dashboard')->with('error', "Akun Anda belum memiliki principal yang valid untuk mengakses fitur ini.");
            }

            // Jika principal operator tidak termasuk yang diizinkan
            if (!in_array($userPrincipal, $allowedPrincipals)) {
                return Redirect::route('dashboard')->with('error', "Anda tidak memiliki akses untuk fitur ini.");
            }
        }

        $barangCount = BarangWfgModel::count();
        $principals = BarangWfgModel::distinct()->pluck('principal')->filter()->values();

        $error_message = null;

        // 🔹 Validasi utama
        if ($barangCount === 0) {
            $error_message = 'Master Data Barang WFG belum tersedia. Hubungi Foreman Anda untuk mengunggah master barang.';
        } elseif ($user->jabatan === 'operator' && $userPrincipal) {
            $barangByPrincipal = BarangWfgModel::whereRaw('UPPER(TRIM(principal)) = ?', [$userPrincipal])->count();

            if ($barangByPrincipal === 0) {
                $error_message = "Principal '{$userPrincipal}' belum memiliki data barang di Master WFG. Hubungi Foreman untuk menambahkan principal tersebut.";
            }
        }

        // 🔹 Jika ada error
        if ($error_message) {
            if ($user->jabatan === 'operator') {
                return view('stock_opname_wfg.upload_soh', compact('principals', 'barangCount', 'error_message'))
                    ->with('error', $error_message);
            } else {
                return Redirect::route('wfg.master.barang.index')->with('error', $error_message);
            }
        }

        // 🔹 Jika tidak ada error, tampilkan view
        return view('stock_opname_wfg.upload_soh', compact('principals', 'barangCount'));
    }

    public function reportSOPWFG()
    {
        $user = Auth::user();

        if ($user->jabatan === 'operator') {
            $allowedPrincipals = ['BAS', 'SMU'];

            $userPrincipal = optional($user->principal)->principal
                ? strtoupper(trim($user->principal->principal))
                : null;

            // Jika operator tidak punya principal
            if (!$userPrincipal) {
                return Redirect::route('dashboard')->with('error', "Akun Anda belum memiliki principal yang valid untuk mengakses fitur ini.");
            }

            // Jika principal operator tidak termasuk yang diizinkan
            if (!in_array($userPrincipal, $allowedPrincipals)) {
                return Redirect::route('dashboard')->with('error', "Anda tidak memiliki akses untuk fitur ini.");
            }
        }

        $principals = BarangWfgModel::distinct()->pluck('principal');
        $hasNewBarang = BarangWfgModel::where('is_new', 1)->exists();

        // Cek apakah user saat ini adalah Foreman Robi
        $isRobiForeman = $user && $user->username === 'RobiForeman';

        if ($isRobiForeman && $hasNewBarang) {
            $warning_message = 'Terdapat barang baru yang perlu Anda konfirmasi terlebih dahulu sebelum laporan dapat dilanjutkan.';
            $url = route('wfg.master.barang.index');
            return view('stock_opname_wfg.report_sop', compact('principals', 'warning_message', 'url'));
        }
        return view('stock_opname_wfg.report_sop', compact('principals'));
    }
}
