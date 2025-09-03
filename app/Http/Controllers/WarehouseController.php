<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ForkliftModel;
use App\Models\PalletMoverModel;
use App\Models\Tkbm\TkbmFeeModel;

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
}
