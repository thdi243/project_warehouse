<?php

namespace App\Http\Controllers\Wrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wrm\MasterLocationRequest;
use App\Models\Wrm\MasterLocationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MasterLocationController extends Controller
{
    public function index()
    {
        return view('master.wrm.master_location');
    }

    public function store(MasterLocationRequest $request)
    {
        $stock = MasterLocationModel::create([
            ...$request->validated(),
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Location berhasil disimpan',
            'data'    => $stock,
        ]);
    }

    public function getData()
    {
        $location = MasterLocationModel::all();

        return response()->json([
            'status' => true,
            'data' => $location
        ]);
    }

    public function update(MasterLocationRequest $request, $id)
    {
        $location = MasterLocationModel::findOrFail($id);

        $location->update([
            ...$request->validated(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Location berhasil diperbarui',
            'data'    => $location,
        ]);
    }

    public function destroy($id)
    {
        $location = MasterLocationModel::findOrFail($id);

        $location->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Location berhasil dihapus',
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        unset($rows[0]);

        $errors = [];
        $payload = [];
        $uniqueCheck = [];

        foreach ($rows as $index => $row) {

            $line = $index + 1;

            $plant  = $row[0] ?? '';
            $sLoc   = $row[1] ?? '';
            $gudang = $row[2] ?? '';
            $zona   = $row[3] ?? '';

            $key = "{$plant}|{$sLoc}|{$gudang}|{$zona}";

            // cek duplikat dalam file
            if (isset($uniqueCheck[$key])) {
                $errors[] = "Baris {$line} duplikat dengan baris {$uniqueCheck[$key]}";
                continue;
            }

            $uniqueCheck[$key] = $line;

            // cek duplikat di database
            $existing = MasterLocationModel::select('plant', 's_loc', 'gudang', 'zona')
                ->get()
                ->map(function ($item) {
                    return "{$item->plant}|{$item->s_loc}|{$item->gudang}|{$item->zona}";
                })
                ->toArray();

            if ($existing) {
                $errors[] = "Baris {$line} sudah tersimpan ({$plant}-{$sLoc}-{$gudang}-{$zona})";
                continue;
            }

            $payload[] = [
                'plant' => $plant,
                's_loc' => $sLoc,
                'gudang' => $gudang,
                'zona' => $zona,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // ADA ERROR → BATAL TOTAL
        if (!empty($errors)) {
            return response()->json([
                'status' => false,
                'message' => 'Upload dibatalkan, perbaiki data terlebih dahulu',
                'errors' => $errors
            ], 422);
        }

        // AMAN → INSERT SEKALIGUS
        DB::transaction(function () use ($payload) {
            MasterLocationModel::insert($payload);
        });

        return response()->json([
            'status' => true,
            'message' => 'Upload berhasil (' . count($payload) . ' data masuk)',
        ]);
    }
}
