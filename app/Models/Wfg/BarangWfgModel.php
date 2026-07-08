<?php

namespace App\Models\Wfg;

use App\Models\User;
use App\Models\Wfg\stock_opname\StockOnHandModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarangWfgModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'wfg_barang';

    protected $fillable = [
        'mid_barang',
        'nama_barang',
        'qty_box',
        'principal',
        'status',
        'uom',
        'is_new',
        'created_by',
    ];

    public function stockOnHand()
    {
        return $this->hasOne(StockOnHandModel::class, 'barang_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
