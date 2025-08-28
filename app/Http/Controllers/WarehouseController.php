<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
}
