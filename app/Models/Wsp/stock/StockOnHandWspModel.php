<?php

namespace App\Models\Wsp\stock;

use App\Models\User;
use App\Models\Wsp\BarangModel;
use App\Models\Wsp\TransaksiModel;
use Illuminate\Database\Eloquent\Model;

class StockOnHandWspModel extends Model
{
    protected $table = 'wsp_stock_on_hand';

    protected $fillable = [
        'barang_id',
        'qty_soh',
        'unrest',
        'qual_insp',
        'blocked',
        'transf',
        'last_update',
        'created_by',
    ];

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
