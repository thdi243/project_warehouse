<?php

namespace App\Models\Wfg\stock_opname;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockOnHandModel extends Model
{
    use HasFactory;

    protected $table = 'wfg_soh';

    protected $fillable = [
        'barang_id',
        'user_id',
        'qty_soh',
        'qty_unrest',
        'qty_qi',
        'qty_block',
        'last_updated',
        'principal'
    ];

    public function barang()
    {
        return $this->belongsTo(BarangWfgModel::class, 'barang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
