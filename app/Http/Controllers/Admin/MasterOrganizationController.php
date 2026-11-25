<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterBagianModel;
use App\Models\Master\MasterDepartemenModel;
use App\Models\Master\MasterJabatanModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterOrganizationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getData();
        }

        $departemens = MasterDepartemenModel::withCount('bagians')->orderBy('nama', 'asc')->get();
        $bagians = MasterBagianModel::with('departemen')->orderBy('nama', 'asc')->get();
        $jabatans = MasterJabatanModel::orderBy('nama', 'asc')->get();

        return view('admin.organization.index', compact('departemens', 'bagians', 'jabatans'));
    }

    public function getData()
    {
        $departemens = MasterDepartemenModel::withCount('bagians')->orderBy('nama', 'asc')->get();
        $bagians = MasterBagianModel::with('departemen')->orderBy('nama', 'asc')->get();
        $jabatans = MasterJabatanModel::orderBy('nama', 'asc')->get();

        return response()->json([
            'success' => true,
            'departemens' => $departemens,
            'bagians' => $bagians,
            'jabatans' => $jabatans,
        ]);
    }

    // ==========================================
    // 1. CRUD DEPARTEMEN
    // ==========================================

    public function storeDepartemen(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:20|unique:master_departemens,kode',
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $dept = MasterDepartemenModel::create([
            'kode' => trim($request->kode),
            'nama' => trim($request->nama),
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Departemen <b>{$dept->nama} ({$dept->kode})</b> berhasil ditambahkan.",
            'data' => $dept
        ]);
    }

    public function updateDepartemen(Request $request, $id)
    {
        $dept = MasterDepartemenModel::findOrFail($id);

        $request->validate([
            'kode' => "required|string|max:20|unique:master_departemens,kode,{$id}",
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $dept->update([
            'kode' => trim($request->kode),
            'nama' => trim($request->nama),
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : $dept->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Departemen <b>{$dept->nama}</b> berhasil diperbarui.",
            'data' => $dept
        ]);
    }

    public function deleteDepartemen($id)
    {
        $dept = MasterDepartemenModel::findOrFail($id);
        $name = $dept->nama;
        $dept->delete();

        return response()->json([
            'success' => true,
            'message' => "Departemen <b>{$name}</b> beserta bagian terkait berhasil dihapus."
        ]);
    }

    // ==========================================
    // 2. CRUD BAGIAN
    // ==========================================

    public function storeBagian(Request $request)
    {
        $request->validate([
            'departemen_id' => 'required|exists:master_departemens,id',
            'kode' => 'nullable|string|max:30',
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $bagian = MasterBagianModel::create([
            'departemen_id' => $request->departemen_id,
            'kode' => $request->filled('kode') ? trim($request->kode) : null,
            'nama' => trim($request->nama),
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Bagian <b>{$bagian->nama}</b> berhasil ditambahkan.",
            'data' => $bagian
        ]);
    }

    public function updateBagian(Request $request, $id)
    {
        $bagian = MasterBagianModel::findOrFail($id);

        $request->validate([
            'departemen_id' => 'required|exists:master_departemens,id',
            'kode' => 'nullable|string|max:30',
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $bagian->update([
            'departemen_id' => $request->departemen_id,
            'kode' => $request->filled('kode') ? trim($request->kode) : null,
            'nama' => trim($request->nama),
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : $bagian->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Bagian <b>{$bagian->nama}</b> berhasil diperbarui.",
            'data' => $bagian
        ]);
    }

    public function deleteBagian($id)
    {
        $bagian = MasterBagianModel::findOrFail($id);
        $name = $bagian->nama;
        $bagian->delete();

        return response()->json([
            'success' => true,
            'message' => "Bagian <b>{$name}</b> berhasil dihapus."
        ]);
    }

    // ==========================================
    // 3. CRUD JABATAN
    // ==========================================

    public function storeJabatan(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:20|unique:master_jabatans,kode',
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $jabatan = MasterJabatanModel::create([
            'kode' => trim($request->kode),
            'nama' => trim($request->nama),
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Jabatan <b>{$jabatan->nama}</b> berhasil ditambahkan.",
            'data' => $jabatan
        ]);
    }

    public function updateJabatan(Request $request, $id)
    {
        $jabatan = MasterJabatanModel::findOrFail($id);

        $request->validate([
            'kode' => "required|string|max:20|unique:master_jabatans,kode,{$id}",
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $jabatan->update([
            'kode' => trim($request->kode),
            'nama' => trim($request->nama),
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : $jabatan->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Jabatan <b>{$jabatan->nama}</b> berhasil diperbarui.",
            'data' => $jabatan
        ]);
    }

    public function deleteJabatan($id)
    {
        $jabatan = MasterJabatanModel::findOrFail($id);
        $name = $jabatan->nama;
        $jabatan->delete();

        return response()->json([
            'success' => true,
            'message' => "Jabatan <b>{$name}</b> berhasil dihapus."
        ]);
    }

    // ==========================================
    // 4. API JSON FOR AJAX DROPDOWN
    // ==========================================

    public function getBagiansByDept(Request $request)
    {
        $deptId = $request->departemen_id;
        $deptKode = $request->departemen_kode;

        $query = MasterBagianModel::where('is_active', true);

        if ($deptId) {
            $query->where('departemen_id', $deptId);
        } elseif ($deptKode) {
            $dept = MasterDepartemenModel::where('kode', $deptKode)->orWhere('nama', $deptKode)->first();
            if ($dept) {
                $query->where('departemen_id', $dept->id);
            }
        }

        $bagians = $query->orderBy('nama', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $bagians
        ]);
    }
}
