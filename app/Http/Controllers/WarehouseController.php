<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\P2h\ForkliftModel;
use App\Models\Tkbm\bps\TkbmFeeModel;
use App\Models\P2h\PalletMoverModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\Wsp\stock_manage\StockOnHandWspModel;
use App\Models\Wfg\stock_opname\BarangWfgModel;
use App\Models\Wfg\stock_opname\StockOnHandModel;
use App\Models\Wfg\stock_opname\WfgSopModel;
use App\Models\Wfg\stock_opname\WfgSopStatusModel;
use App\Models\Wrm\StockOpname\WrmSohModel;
use App\Models\Wrm\StockOpname\WrmSoModel;
use App\Models\Wrm\StockOpname\WrmSoStatusModel;
use App\Models\Wrm\MasterBarangModel;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function maintenanceView()
    {

        return view('maintenance');
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

        return view('master.wsp.fees_taxes', compact('data'));
    }

    public function p2hData()
    {
        return view('wrm.p2h.data_p2h');
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
            ->select('id', 'username', 'nama_lengkap', 'nik')
            ->get();

        return view(
            'wrm.p2h.forklift_registration',
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

        return view('wrm.p2h.pallet_mover_registration', compact('data', 'operators'));
    }

    public function barangIndex()
    {
        return view('master.wsp.master_barang');
    }

    public function rakIndex()
    {
        return view('master.wsp.master_rak');
    }

    public function dashboardStockWsp()
    {
        return view('wsp.wsp_stock.home_stock');
        // return view('maintenance');
    }

    public function stockOnHandView()
    {
        return view('wsp.wsp_stock.stock.data_soh');
    }

    public function stockLocView()
    {
        return view('wsp.wsp_stock.stock.stock_location');
    }

    public function sohView()
    {
        return view('wsp.wsp_stock.stock.data_soh');
    }

    public function formSOWFG()
    {
        $user = Auth::user();

        $today = Carbon::today()->format('Y-m-d');

        // Ambil principal user kalau ada
        $userPrincipal = optional($user->principal)->principal
            ? strtoupper(trim($user->principal->principal))
            : null;

        $smuRelevantPrincipals = ['HAS', 'SMU', 'AMG', 'PAS', 'KAS', 'TAS', 'DAM', 'MDU', 'KIAS'];

        // --- LOGIKA UNTUK OPERATOR ---
        if ($user->jabatan === 'operator') {
            $allowedPrincipals = ['BAS', 'SMU'];

            if (!$userPrincipal) {
                return redirect()->route('dashboard')
                    ->with('error', 'Akun Anda belum terkait dengan principal. Hubungi administrator.');
            }

            if (!in_array($userPrincipal, $allowedPrincipals)) {
                return redirect()->route('dashboard')
                    ->with('error', "Principal Anda ({$userPrincipal}) tidak diizinkan mengakses fitur ini.");
            }

            $today = Carbon::today()->format('Y-m-d');

            // === KHUSUS OPERATOR SMU ===
            if ($userPrincipal === 'SMU') {
                // Cek apakah ADA SETIDAKNYA SATU principal dari list yang punya SOH hari ini
                $hasAnySoh = StockOnHandModel::whereDate('last_updated', $today)
                    ->whereHas('barang', function ($query) use ($smuRelevantPrincipals) {
                        $query->whereIn('principal', $smuRelevantPrincipals); // tanpa UPPER/TRIM karena sudah di-handle di upload
                        // Kalau mau lebih aman:
                        // $query->whereRaw('UPPER(TRIM(principal)) IN (' . implode(',', array_fill(0, count($smuRelevantPrincipals), '?')) . ')', $smuRelevantPrincipals);
                    })
                    ->exists();

                if (!$hasAnySoh) {
                    $listText = implode(', ', $smuRelevantPrincipals);
                    return redirect()->route('wfg.stock_opname.soh')
                        ->with('error', "Belum ada data Stock On Hand (SOH) hari ini ({$today}) dari principal manapun ({$listText}). Silakan unggah setidaknya satu data SOH terlebih dahulu sebelum melanjutkan.");
                }

                // Jika ADA minimal satu → boleh lanjut
            } else {
                // Untuk operator BAS → tetap cek hanya BAS saja
                $sohExists = StockOnHandModel::whereDate('last_updated', $today)
                    ->whereHas('barang', function ($query) use ($userPrincipal) {
                        $query->whereRaw('UPPER(TRIM(principal)) = ?', [$userPrincipal]);
                    })
                    ->exists();

                if (!$sohExists) {
                    return redirect()->route('wfg.stock_opname.soh')
                        ->with('error', "Data Stock On Hand (SOH) untuk principal {$userPrincipal} pada tanggal {$today} belum diunggah. Silakan unggah data Anda terlebih dahulu.");
                }
            }
        } else {
            $hasAnySoh = StockOnHandModel::whereDate('last_updated', $today)->exists();

            if (!$hasAnySoh) {
                return redirect()->route('wfg.stock_opname.soh')
                    ->with('error', "Belum ada data Stock On Hand (SOH) hari ini ({$today}). Silakan tunggu data diunggah.");
            }
        }

        $principals = BarangWfgModel::distinct()->pluck('principal');
        return view('wfg.stock_opname_wfg.form', compact('principals'));
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

            // Tambahan: Cek apakah sudah ada opname aktif untuk principal ini hari ini
            $opnameAktif = WfgSopStatusModel::whereDate('tgl_opname', now()->toDateString())
                ->where('principal', $userPrincipal)
                ->where('status', 'started')
                ->first();

            if ($opnameAktif) {
                return Redirect::route('wfg.stock_opname.form')
                    ->with('error', "Opname untuk principal '{$userPrincipal}' sedang berlangsung dan belum diselesaikan. Selesaikan opname terlebih dahulu sebelum melanjutkan.");
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
        // return view('stock_opname_wfg.upload_soh', compact('principals', 'barangCount'));
        return view('wfg.stock_opname_wfg.upload_soh', [
            'principals' => $principals,
            'barangCount' => $barangCount,
            'error_message' => session('error'), // ambil error dari session kalau ada
        ]);
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

        $principals = WfgSopModel::distinct()->pluck('principal');
        $hasNewBarang = BarangWfgModel::where('is_new', 1)->exists();

        // Cek apakah user saat ini adalah Foreman Robi
        $isRobiForeman = $user && $user->username === 'RobiForeman';

        if ($isRobiForeman && $hasNewBarang) {
            $warning_message = 'Terdapat barang baru yang perlu Anda konfirmasi terlebih dahulu sebelum laporan dapat dilanjutkan.';
            $url = route('wfg.master.barang.index');
            return view('stock_opname_wfg.report_sop', compact('principals', 'warning_message', 'url'));
        }
        return view('wfg.stock_opname_wfg.report_sop', compact('principals'));
    }

    public function uploadSOHWRM()
    {
        $barangCount = MasterBarangModel::count();
        $error_message = session('error');

        return view('wrm.stock_opname.upload_soh', compact('barangCount', 'error_message'));
    }

    public function formSOWRM()
    {
        $today = Carbon::today()->format('Y-m-d');
        $sohExists = WrmSohModel::whereDate('created_at', $today)->exists();

        if (!$sohExists) {
            return redirect()->route('wrm.stock_opname.soh')
                ->with('error', "Data Stock On Hand (SOH) WRM pada tanggal {$today} belum diunggah. Silakan unggah data Anda terlebih dahulu.");
        }

        return view('wrm.stock_opname.form');
    }

    public function reportSOWRM()
    {
        return view('wrm.stock_opname.report');
    }

    public function uploadSOHWPM()
    {
        $barangCount = \App\Models\Wpm\WpmMasterBarangModel::count();
        $error_message = session('error');

        return view('wpm.stock_opname.upload_soh', compact('barangCount', 'error_message'));
    }

    public function formSOWPM()
    {
        $today = Carbon::today()->format('Y-m-d');
        $sohExists = \App\Models\Wpm\StockOpname\WpmSohModel::whereDate('created_at', $today)->exists();

        if (!$sohExists) {
            return redirect()->route('wpm.stock_opname.soh')
                ->with('error', "Data Stock On Hand (SOH) WPM pada tanggal {$today} belum diunggah. Silakan unggah data Anda terlebih dahulu.");
        }

        return view('wpm.stock_opname.form');
    }

    public function reportSOWPM()
    {
        return view('wpm.stock_opname.report');
    }

    public function uploadSOHWCP()
    {
        $barangCount = \App\Models\Wcp\WcpMasterBarangModel::count();
        $error_message = session('error');

        return view('wcp.stock_opname.upload_soh', compact('barangCount', 'error_message'));
    }

    public function formSOWCP()
    {
        $today = Carbon::today()->format('Y-m-d');
        $sohExists = \App\Models\Wcp\StockOpname\WcpSohModel::whereDate('created_at', $today)->exists();

        if (!$sohExists) {
            return redirect()->route('wcp.stock_opname.soh')
                ->with('error', "Data Stock On Hand (SOH) WCP pada tanggal {$today} belum diunggah. Silakan unggah data Anda terlebih dahulu.");
        }

        return view('wcp.stock_opname.form');
    }

    public function reportSOWCP()
    {
        return view('wcp.stock_opname.report');
    }

    public function uploadSOHWSP()
    {
        $barangCount = \App\Models\Wsp\BarangModel::count();
        $error_message = session('error');

        return view('wsp.stock_opname.upload_soh', compact('barangCount', 'error_message'));
    }

    public function formSOWSP()
    {
        $today = Carbon::today()->format('Y-m-d');
        $sohExists = \App\Models\Wsp\StockOpname\WspSohModel::whereDate('created_at', $today)->exists();

        if (!$sohExists) {
            return redirect()->route('wsp.stock_opname.soh')
                ->with('error', "Data Stock On Hand (SOH) WSP pada tanggal {$today} belum diunggah. Silakan unggah data Anda terlebih dahulu.");
        }

        return view('wsp.stock_opname.form');
    }

    public function reportSOWSP()
    {
        return view('wsp.stock_opname.report');
    }

    public function viewStockMove()
    {
        return view('wsp.wsp_stock.home_stock_move');
    }
}
