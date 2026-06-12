<?php

namespace App\Models\Wrm\Inventory;

use App\Models\Wrm\MasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferDetail extends Model
{
    use HasFactory;

    protected $table = 'wrm_stock_transfer_details';

    protected $fillable = [
        'transfer_id',
        'matdoc_scrup',
        'matdoc_year',
        'no_spb',
        'plant',
        'sloc',
        'barang_id',
        'no_barcode',
        'pallet_id',
        'grade',
        'qty_barcode',
        'qty_actual',
        'qty_susut_simpan',
        'uom',
        'lama_simpan',
        'persen_susut',
        'created_by',
        'updated_by',
    ];


    public function header()
    {
        return $this->belongsTo(StockTransfer::class, 'transfer_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
